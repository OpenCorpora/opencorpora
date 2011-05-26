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

$func->{'total_books'} = sub {
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM books");
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
    my $sc = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE tf_text REGEXP '[А-Яа-я]'");
    $sc->execute();
    return $sc->fetchrow_hashref()->{'cnt'};
};
$func->{'added_sentences'} = sub {
    my %user_cnt;
    my $del = $dbh->prepare("DELETE FROM user_stats WHERE param_id=6");
    $del->execute();
    my $curr_max = $dbh->prepare("SELECT MAX(param_value) AS m FROM stats_values WHERE param_id=6");
    my $ins = $dbh->prepare("INSERT INTO user_stats VALUES(?, ?, '6', ?)");
    my $pre = $dbh->prepare("SELECT sent_id FROM sentences WHERE sent_id>? ORDER BY sent_id");
    my $sc = $dbh->prepare("
        SELECT user_id
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
    $curr_max->execute();
    my $r = $curr_max->fetchrow_hashref();
    if (!$r->{'m'}) {
        $r->{'m'} = 0;
    }
    $pre->execute($r->{'m'});
    my $new_max = 0;
    while ($r = $pre->fetchrow_hashref()) {
        $sc->execute($r->{'sent_id'});
        $new_max = $r->{'sent_id'};
        print STDERR "sentence $new_max\n";
        my $u = $sc->fetchrow_hashref();
        ++$user_cnt{$u->{'user_id'}};
    }
    for my $k(keys %user_cnt) {
        $ins->execute($k, time(), $user_cnt{$k});
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
