#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;
use POSIX qw/strftime/;
use Config::INI::Reader;

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
my $mysql = $conf->{mysql};

my $dbh = DBI->connect('DBI:mysql:'.$mysql->{'dbname'}.':'.$mysql->{'host'}, $mysql->{'user'}, $mysql->{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
$dbh->{'AutoCommit'} = 0;
if ($dbh->{'AutoCommit'}) {
    die "Setting AutoCommit failed";
}

my %source2id = (
    'chaskor' => 1,
    'wikipedia' => 8,
    'wikinews' => 56,
    'blogs' => 184,
    'chaskor_news' => 226,
    'fiction' => 806,
    'misc' => 1651,
    'law' => 1675
);

my $scan = $dbh->prepare("SELECT * FROM `stats_param` WHERE is_active=1 ORDER BY param_id");
my $insert = $dbh->prepare("INSERT INTO `stats_values` VALUES(?, ?, ?)");
my $scan_author = $dbh->prepare("SELECT user_id, timestamp FROM sentence_authors WHERE sent_id=? LIMIT 1");
my $insert_author = $dbh->prepare("INSERT INTO sentence_authors VALUES(?, ?, ?)");

my $user_ins = $dbh->prepare("INSERT INTO user_stats VALUES(?, ?, ?, ?)");
my $user_del = $dbh->prepare("DELETE FROM user_stats WHERE param_id=?");


$scan->execute();

# SUBROUTINES

my $func;

sub books_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM books WHERE parent_id IN (SELECT book_id FROM books WHERE parent_id=$pid)");
    $sc->execute();
    my $cnt = $sc->fetchrow_hashref()->{'cnt'};
    return $cnt if $cnt;

    $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM books WHERE parent_id=$pid");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub sentences_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id IN (SELECT book_id FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id=$pid) OR parent_id IN (SELECT book_id FROM books WHERE parent_id IN(SELECT book_id FROM books WHERE parent_id = $pid))))");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub tokens_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE sent_id IN (SELECT sent_id FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id IN (SELECT book_id FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id = $pid) OR parent_id IN (SELECT book_id FROM books WHERE parent_id IN(SELECT book_id FROM books WHERE parent_id = $pid)))))");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub words_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE sent_id IN (SELECT sent_id FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id IN (SELECT book_id FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id = $pid) OR parent_id IN (SELECT book_id FROM books WHERE parent_id IN(SELECT book_id FROM books WHERE parent_id = $pid))))) AND tf_text REGEXP '[А-Яа-яЁё]'");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub get_sentence_adder {
    my $sid = shift;
    my $r;

    $scan_author->execute($sid);
    if ($r = $scan_author->fetchrow_hashref()) {
        return $r;
    }

    my $sc = $dbh->prepare("
        SELECT user_id, timestamp
        FROM rev_sets
        WHERE set_id = (
            SELECT set_id
            FROM tf_revisions
            WHERE tf_id IN (
                SELECT tf_id
                FROM text_forms
                WHERE sent_id=?
            )
            ORDER BY rev_id
            LIMIT 1
        )
        LIMIT 1
    ");
    $sc->execute($sid);
    $r = $sc->fetchrow_hashref();
    $insert_author->execute($sid, $r->{'user_id'}, $r->{'timestamp'});
    return $r;
}

sub sentences_in_file {
    return `bzcat $_[0] | grep -c '<sentence '`
}
sub tokens_in_file {
    return `bzcat $_[0] | grep -c '<token '`
}
sub words_in_file {
    return `bzcat $_[0] | grep '<token ' | grep -Eo 'text="[^\"]+"' | cut -d\\" -f2 | grep -v '[[:punct:]]' | grep -civ '[a-z]'`
}

$func->{'total_books'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(DISTINCT book_id) AS cnt FROM paragraphs");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'total_sentences'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM sentences");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'total_tokens'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'total_lemmata'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM dict_lemmata");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'total_words'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE tf_text REGEXP '[А-Яа-яЁё]'");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};

for my $source(keys %source2id) {
    $func->{$source.'_books'} = sub { return books_by_source($source2id{$source}) };
    $func->{$source.'_sentences'} = sub { return sentences_by_source($source2id{$source}) };
    $func->{$source.'_tokens'} = sub { return tokens_by_source($source2id{$source}) };
    $func->{$source.'_words'} = sub { return words_by_source($source2id{$source}) };
}

$func->{'total_parses'} = sub {
    my $scan = $dbh->prepare("SELECT rev_text FROM tf_revisions WHERE is_last = 1 AND tf_id IN (SELECT tf_id FROM text_forms WHERE tf_text REGEXP '[А-Яа-яЁё]')");
    $scan->execute();
    my $total = 0;
    while (my $r = $scan->fetchrow_hashref()) {
        while ($r->{'rev_text'} =~ /<v>/g) {
            $total++;
        }
    }
    return $total;
};
$func->{'unknown_words'} = sub {
    my $scan = $dbh->prepare("SELECT COUNT(*) as cnt FROM tf_revisions WHERE is_last = 1 AND rev_text LIKE '%g v=\"UNKN\"%'");
    $scan->execute();
    return $scan->fetchrow_hashref()->{'cnt'};
};
$func->{'unambiguous_parses'} = sub {
    my $scan = $dbh->prepare("SELECT rev_text FROM tf_revisions WHERE is_last = 1 AND rev_text NOT LIKE '%g v=\"UNKN\"%' AND tf_id IN (SELECT tf_id FROM text_forms WHERE tf_text REGEXP '[А-Яа-яЁё]')");
    $scan->execute();
    my $parses = 0;
    my $total = 0;
    W:while (my $r = $scan->fetchrow_hashref()) {
        $parses = 0;
        while ($r->{'rev_text'} =~ /<v>/g) {
            $parses++;
            if ($parses > 1) {
                next W;
            }
        }
        $total++;
    }
    return $total;
};
$func->{'added_sentences'} = sub {

    #find the authors of all sentences which haven't been found yet
    my $absent = $dbh->prepare("SELECT sent_id FROM sentences WHERE sent_id NOT IN (SELECT sent_id FROM sentence_authors)");
    $absent->execute();
    my $r;

    while ($r = $absent->fetchrow_hashref()) {
        get_sentence_adder($r->{'sent_id'});
    }

    #LAST WEEK
    #we'll use param_id = 7, though it's used in different sense in stats_param
    my $cnt = $dbh->prepare("SELECT user_id, COUNT(sent_id) as cnt FROM sentence_authors WHERE timestamp>=? GROUP BY user_id");

    my $basic_ts = time()-60*60*24*7;

    $cnt->execute($basic_ts);
    $user_del->execute(7);
    while ($r = $cnt->fetchrow_hashref()) {
        $user_ins->execute($r->{'user_id'}, time(), 7, $r->{'cnt'});
    }

    #GLOBAL
    $cnt->execute(0);
    $user_del->execute(6);
    while ($r = $cnt->fetchrow_hashref()) {
        $user_ins->execute($r->{'user_id'}, time(), 6, $r->{'cnt'});
    }
    return -1;
};
$func->{'dump_full_sentences'} = sub {
    return sentences_in_file($conf->{'project'}->{'root'}.'/files/export/annot/annot.opcorpora.xml.bz2');
};
$func->{'dump_disamb_sentences'} = sub {
    return sentences_in_file($conf->{'project'}->{'root'}.'/files/export/annot/annot.opcorpora.no_ambig.xml.bz2');
};
$func->{'dump_full_tokens'} = sub {
    return tokens_in_file($conf->{'project'}->{'root'}.'/files/export/annot/annot.opcorpora.xml.bz2');
};
$func->{'dump_disamb_tokens'} = sub {
    return tokens_in_file($conf->{'project'}->{'root'}.'/files/export/annot/annot.opcorpora.no_ambig.xml.bz2');
};
$func->{'dump_full_words'} = sub {
    return words_in_file($conf->{'project'}->{'root'}.'/files/export/annot/annot.opcorpora.xml.bz2');
};
$func->{'dump_disamb_words'} = sub {
    return words_in_file($conf->{'project'}->{'root'}.'/files/export/annot/annot.opcorpora.no_ambig.xml.bz2');
};

# /SUBROUTINES

my $value;
while (my $ref = $scan->fetchrow_hashref()) {
    if (exists $func->{$ref->{'param_name'}}) {
        $value = $func->{$ref->{'param_name'}}->();
        $insert->execute(time(), $ref->{'param_id'}, $value) unless $value == -1;
    }
}

$dbh->commit();
