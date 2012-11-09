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

update_annot_stats();
$dbh->do("COMMIT");

sub count_finished {
    my $dbh = shift;
    my $start_time = shift;
    my $total_count = shift;
    my $diverg_count = shift;

    my $last_sample_id = 0;
    my $last_answer = 0;
    my $same_answer = 1;
    my @users = ();

    my $inst_count = $dbh->prepare("
        SELECT user_id, answer, sample_id
        FROM morph_annot_instances
        LEFT JOIN morph_annot_samples s USING (sample_id)
        LEFT JOIN morph_annot_pools USING(pool_id)
        WHERE status > 3
        AND answer > 0
        AND ts_finish > $start_time
        AND user_id > 0
        ORDER BY s.sample_id
    ");
    $inst_count->execute();

    while (my $r = $inst_count->fetchrow_hashref()) {
        if ($last_sample_id != $r->{'sample_id'}) {
            # new sample
            if ($last_sample_id) {
                # flush
                for my $uid(@users) {
                    ++$total_count->{$uid};
                    if (!$same_answer) {
                        ++$diverg_count->{$uid};
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
}
sub count_correct {
    my $dbh = shift;
    my $start_time = shift;
    my $total = shift;
    my $correct = shift;

    my $user_answers = $dbh->prepare("
        SELECT user_id, answer, sample_id
        FROM morph_annot_instances
        LEFT JOIN morph_annot_samples USING (sample_id)
        LEFT JOIN morph_annot_pools USING (pool_id)
        WHERE answer > 0
        AND status > 5
        AND ts_finish > $start_time
    ");
    $user_answers->execute();

    my $moder_answers = $dbh->prepare("
        SELECT ms.sample_id, answer
        FROM morph_annot_moderated_samples ms
        LEFT JOIN morph_annot_samples USING (sample_id)
        LEFT JOIN morph_annot_pools p USING (pool_id)
        WHERE p.status > 5
    ");
    $moder_answers->execute();

    my %moder_answers;
    # accumulate moderator answers
    while (my $r = $moder_answers->fetchrow_hashref()) {
        $moder_answers{$r->{'sample_id'}} = $r->{'answer'};
    }

    while (my $r = $user_answers->fetchrow_hashref()) {
        next unless exists $moder_answers{$r->{'sample_id'}};
        $total->{$r->{'user_id'}}++;
        if ($r->{'answer'} == $moder_answers{$r->{'sample_id'}}) {
            $correct->{$r->{'user_id'}}++;
        }
    }
}
sub update_annot_stats {
    my $user_ins = $dbh->prepare("INSERT INTO user_stats VALUES(?, ?, ?, ?)");
    my $user_del = $dbh->prepare("DELETE FROM user_stats WHERE param_id=?");

    my %total_count;
    my %diverg_count;
    my %total_moderated_count;
    my %correct_moderated_count;
    count_finished($dbh, 0, \%total_count, \%diverg_count);
    count_correct($dbh, 0, \%total_moderated_count, \%correct_moderated_count);

    my %total_count_week;
    my %diverg_count_week;
    my %total_moderated_count_week;
    my %correct_moderated_count_week;
    my $week_ago = time() - 7 * 24 * 60 * 60;
    count_finished($dbh, $week_ago, \%total_count_week, \%diverg_count_week);
    count_correct($dbh, $week_ago, \%total_moderated_count_week, \%correct_moderated_count_week);

    $dbh->do("START TRANSACTION");
    $user_del->execute(33);
    $user_del->execute(34);
    $user_del->execute(38);
    $user_del->execute(39);
    $user_del->execute(58);
    $user_del->execute(59);
    $user_del->execute(60);
    $user_del->execute(61);

    for my $uid(keys %total_count) {
        $user_ins->execute($uid, time(), 33, $total_count{$uid});
        $user_ins->execute($uid, time(), 34, int($diverg_count{$uid}));
    }
    for my $uid(keys %total_moderated_count) {
        $user_ins->execute($uid, time(), 38, $total_moderated_count{$uid});
        $user_ins->execute($uid, time(), 39, int($correct_moderated_count{$uid}));
    }
    for my $uid(keys %total_count_week) {
        $user_ins->execute($uid, time(), 58, $total_count_week{$uid});
        $user_ins->execute($uid, time(), 59, int($diverg_count_week{$uid}));
    }
    for my $uid(keys %total_moderated_count_week) {
        $user_ins->execute($uid, time(), 60, $total_moderated_count_week{$uid});
        $user_ins->execute($uid, time(), 61, int($correct_moderated_count_week{$uid}));
    }
}
