#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Config::INI::Reader;

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
$dbh->{'AutoCommit'} = 0;
if ($dbh->{'AutoCommit'}) {
    die "Setting AutoCommit failed";
}

update_annot_stats();

sub update_annot_stats {
    my $user_ins = $dbh->prepare("INSERT INTO user_stats VALUES(?, ?, ?, ?)");
    my $user_del = $dbh->prepare("DELETE FROM user_stats WHERE param_id=?");

    my $inst_count = $dbh->prepare("
        SELECT user_id, answer, sample_id
        FROM morph_annot_instances
        WHERE sample_id IN
            (SELECT sample_id
            FROM morph_annot_samples
            WHERE pool_id IN
                (SELECT pool_id
                FROM morph_annot_pools
                WHERE pool_id NOT IN
                    (SELECT DISTINCT pool_id
                    FROM morph_annot_samples
                    WHERE sample_id IN
                        (SELECT DISTINCT sample_id
                        FROM morph_annot_instances
                        WHERE answer = 0)
                    )
                )
            )
        ORDER BY sample_id
    ");
    $inst_count->execute();
    print STDERR "query ok\n";
    my $last_sample_id = 0;
    my $last_answer = 0;
    my $same_answer = 1;
    my @users = ();
    my %total_count;
    my %diverg_count;
    while (my $r = $inst_count->fetchrow_hashref()) {
        if ($last_sample_id != $r->{'sample_id'}) {
            # new sample
            if ($last_sample_id) {
                # flush
                for my $uid(@users) {
                    ++$total_count{$uid};
                    if (!$same_answer) {
                        ++$diverg_count{$uid};
                    }
                }
            }
            @users = ($r->{'user_id'});
            $same_answer = 1;
        }
        else {
            # same sample
            push @users, $r->{'user_id'};
            if ($last_answer != $r->{'answer'}) {
                $same_answer = 0;
            }
        }
        $last_sample_id = $r->{'sample_id'};
        $last_answer = $r->{'answer'};
    }

    $user_del->execute(33);
    $user_del->execute(34);

    for my $uid(keys %total_count) {
        $user_ins->execute($uid, time(), 33, $total_count{$uid});
        $user_ins->execute($uid, time(), 34, int($diverg_count{$uid}));
    }
}
