#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

my $lock_path = "/var/lock/opcorpora_updstats.lock";
if (-f $lock_path) {
    die ("lock exists, exiting");
}

#reading config
my %mysql;
while(<>) {
    if (/\$config\['mysql_(\w+)'\]\s*=\s*'([^']+)'/) {
        $mysql{$1} = $2;
    }
}

open my $lock, ">$lock_path";
print $lock 'lock';
close $lock;

my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $scan = $dbh->prepare("SELECT * FROM `stats_param` WHERE is_active=1 ORDER BY param_id");
my $insert = $dbh->prepare("INSERT INTO `stats_values` VALUES(?, ?, ?)");

$scan->execute();

# SUBROUTINES

my $func;

sub books_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id=$pid)");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub sentences_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id IN (SELECT book_id FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id=$pid)))");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub tokens_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE sent_id IN (SELECT sent_id FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id IN (SELECT book_id FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id = $pid))))");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub words_by_source {
    my $pid = shift;
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE sent_id IN (SELECT sent_id FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id IN (SELECT book_id FROM books WHERE parent_id = $pid OR parent_id IN (SELECT book_id FROM books WHERE parent_id = $pid)))) AND tf_text REGEXP '[А-Яа-яЁё]'");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
}

sub get_sentence_adder {
    my $sid = shift;
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
    ");
    $sc->execute($sid);
    return $sc->fetchrow_hashref();
}

$func->{'total_books'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM books");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'chaskor_books'} = sub {
    return books_by_source(1);
};
$func->{'wikipedia_books'} = sub {
    return books_by_source(8);
};
$func->{'wikinews_books'} = sub {
    return books_by_source(56);
};
$func->{'blogs_books'} = sub {
    return books_by_source(184);
};
$func->{'total_sentences'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM sentences");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'chaskor_sentences'} = sub {
    return sentences_by_source(1);
};
$func->{'wikipedia_sentences'} = sub {
    return sentences_by_source(8);
};
$func->{'wikinews_sentences'} = sub {
    return sentences_by_source(56);
};
$func->{'blogs_sentences'} = sub {
    return sentences_by_source(184);
};
$func->{'total_tokens'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'chaskor_tokens'} = sub {
    return tokens_by_source(1);
};
$func->{'wikipedia_tokens'} = sub {
    return tokens_by_source(8);
};
$func->{'wikinews_tokens'} = sub {
    return tokens_by_source(56);
};
$func->{'blogs_tokens'} = sub {
    return tokens_by_source(184);
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
$func->{'chaskor_words'} = sub {
    return words_by_source(1);
};
$func->{'wikipedia_words'} = sub {
    return words_by_source(8);
};
$func->{'wikinews_words'} = sub {
    return words_by_source(56);
};
$func->{'blogs_words'} = sub {
    return words_by_source(184);
};
$func->{'added_sentences'} = sub {

    #LAST WEEK
    #we'll use param_id = 7, though it's used in different sense in stats_param
    my %user_cnt;
    my $ins = $dbh->prepare("INSERT INTO user_stats VALUES(?, ?, ?, ?)");
    my $del = $dbh->prepare("DELETE FROM user_stats WHERE param_id=?");
    my $sent_max = $dbh->prepare("SELECT MAX(sent_id) AS m FROM sentences");
    $sent_max->execute();
    my $sm = $sent_max->fetchrow_hashref()->{'m'};
    my $basic_ts = time();
    my $bad_counter = 0;
    while(1) {
        print STDERR "last week sentence $sm\n";
        my $a = get_sentence_adder($sm--);
        next unless $a;
        if ($basic_ts - $a->{'timestamp'} > 60*60*24*7) {
            ++$bad_counter < 10 ? next : last;
        }
        $bad_counter = 0;
        print STDERR "ts = $a->{timestamp}\n";
        $user_cnt{$a->{'user_id'}}++;
    }
    $del->execute(7);
    for my $k(keys %user_cnt) {
        $ins->execute($k, time(), 7, $user_cnt{$k});
    }

    #GLOBAL
    #save the old data
    %user_cnt = ();
    my $sc = $dbh->prepare("SELECT user_id, param_value FROM user_stats WHERE param_id=6");
    $sc->execute();
    while (my $r = $sc->fetchrow_hashref()) {
        $user_cnt{$r->{'user_id'}} = $r->{'param_value'};
    }
    $del->execute(6);
    #updating the data
    my $curr_max = $dbh->prepare("SELECT MAX(param_value) AS m FROM stats_values WHERE param_id=6");
    my $pre = $dbh->prepare("SELECT sent_id FROM sentences WHERE sent_id>? ORDER BY sent_id");
    $curr_max->execute();
    my $r = $curr_max->fetchrow_hashref();
    if (!$r->{'m'}) {
        $r->{'m'} = 0;
    }
    $pre->execute($r->{'m'});
    my $new_max = $r->{'m'};
    while ($r = $pre->fetchrow_hashref()) {
        $new_max = $r->{'sent_id'};
        my $u = get_sentence_adder($r->{'sent_id'})->{'user_id'};
        print STDERR "sentence $new_max by user #$u\n";
        ++$user_cnt{$u};
    }
    for my $k(keys %user_cnt) {
        $ins->execute($k, time(), 6, $user_cnt{$k});
    }
    return $new_max;
};

# /SUBROUTINES

my $value;
while (my $ref = $scan->fetchrow_hashref()) {
    if (exists $func->{$ref->{'param_name'}}) {
        $value = $func->{$ref->{'param_name'}}->();
        $insert->execute(time(), $ref->{'param_id'}, $value) unless $value == -1;
    }
}

unlink ($lock_path);
