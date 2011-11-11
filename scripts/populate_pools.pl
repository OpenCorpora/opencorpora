#!/usr/bin/env perl
use strict;
use DBI;
use Config::INI::Reader;

my $lock_path = "/var/lock/opcorpora_pools.lock";
if (-f $lock_path) {
    die ("lock exists, exiting");
}

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

open my $lock, ">$lock_path";
print $lock 'lock';
close $lock;

my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $last_rev = $dbh->prepare("SELECT rev_id FROM tf_revisions WHERE tf_id=? AND rev_id>? LIMIT 1");
my $add = $dbh->prepare("INSERT INTO morph_annot_candidate_samples VALUES(?, ?)");
my $update_pool = $dbh->prepare("UPDATE morph_annot_pools SET `status`='1' WHERE pool_id=? LIMIT 1");
my $find_pools = $dbh->prepare("SELECT pool_id, grammemes FROM morph_annot_pools WHERE status=0");
$find_pools->execute();
while (my $ref = $find_pools->fetchrow_hashref()) {
    process_pool($ref->{'pool_id'}, $ref->{'grammemes'});
}
unlink $lock_path;


sub process_pool {
    my $pool_id = shift;
    my ($gr1, $gr2) = split /@/, shift;
    print STDERR "processing pool #$pool_id: <$gr1>, <$gr2>\n";

    my @gr1, my @gr2;
    my @var;

    if ($gr1 =~ /\|/) {
        @gr1 = split /\|/, $gr1;
    } else {
        @gr1 = split /\&/, $gr1;
    }

    if ($gr2 =~ /\|/) {
        @gr2 = split /\|/, $gr2;
    } else {
        @gr2= split /\&/, $gr2;
    }

    # OR + AND
    if ($gr1 =~ /\|/ && $gr2 !~ /\|/) {
        for my $g(@gr1) {
            push @var, [$g, @gr2];
        }
    }
    # AND + OR
    elsif ($gr2 =~ /\|/ && $gr1 !~ /\|/) {
        for my $g(@gr2) {
            push @var, [$g, @gr1];
        }
    }
    # OR + OR
    elsif ($gr1 =~ /\|/ && $gr2 =~ /\|/) {
        for my $g1(@gr1) {
            for my $g2(@gr2) {
                push @var, [$g1, $g2];
            }
        }
    }
    # AND + AND
    else {
        push @var, [@gr1, @gr2];
    }

    my @q;
    my @qt;
    for my $v(@var) {
        @qt = ();
        for my $g(@$v) {
            push @qt, "rev_text LIKE '%v=\"$g\"%'";
        }
        push @q, "(".join(' AND ', @qt).")";
    }
    # rough filter
    my $q = "SELECT tf_id, rev_id, rev_text FROM tf_revisions WHERE ".join(' OR ', @q);
    print STDERR $q."\n";
    my $s = $dbh->prepare($q);
    $s->execute();
    while (my $ref = $s->fetchrow_hashref()) {
        # finer check
        check_revision($pool_id, $ref->{'tf_id'}, $ref->{'rev_id'}, $ref->{'rev_text'}, $gr1, $gr2);
    }
    $update_pool->execute($pool_id);
}
sub check_revision {
    my ($pool_id, $tf_id, $rev_id, $rev_text, $gr1, $gr2) = @_;
    print STDERR "will check revision $rev_id, ";

    # is the current revision this token's latest?
    $last_rev->execute($tf_id, $rev_id);
    if ($last_rev->rows > 0) {
        print STDERR "failed: old revision\n";
        return 0;
    }

    # are the restrictions really satisfied?
    if ($gr1 =~ /\&/) {
        my @t = split /\&/, $gr1;
        unless (var_has_all_gram($rev_text, \@t)) {
            print STDERR "failed\n";
            return 0;
        }
    }

    if ($gr2 =~ /\&/) {
        my @t = split /\&/, $gr2;
        unless (var_has_all_gram($rev_text, \@t)) {
            print STDERR "failed\n";
            return 0;
        }
    }

    # are there any variants that don't match any of the grammeme sets?

    if (has_extra_variants($rev_text, $gr1, $gr2)) {
        print STDERR "failed: extra variants\n";
        return 0;
    }

    print STDERR "ok\n";
    $add->execute($pool_id, $tf_id);
}
sub var_has_all_gram {
    my ($rev_text, $aref) = @_;

    my $cnt;
    my $v;
    my $goal = scalar @$aref;

    while ($rev_text =~ /<v(.+?)<\/v>/g) {
        $cnt = 0;
        $v = $1;
        for my $gr(@$aref) {
            if ($v =~ /g v="$gr"/) {
                ++$cnt;
            }
        }
        if ($cnt == $goal) {
            return 1;
        }
    }
    return 0;
}
sub has_extra_variants {
    my ($rev_text, $gr1, $gr2) = @_;

    # we shall check whether there are variants that do not satisfy any grammem sets
    MW:while ($rev_text =~ /<v(.+?)<\/v>/g) {
        my $v = $1;
        for my $g($gr1, $gr2) {
            my @gr = ();
            my $flag_and = 0;

            if ($g =~ /\|/) {
                @gr = split /\|/, $g;
            }
            else {
                @gr = split /\&/, $g;
                $flag_and = 1;
            }
            
            if ($flag_and) {
                #all grammemes must be there
                my $flag_ok = 1;
                for my $gg(@gr) {
                    if ($v !~ /g v="$gg"/) {
                        $flag_ok = 0;
                        last;
                    }
                }
                if ($flag_ok) {
                    #variant is ok, check next variant
                    next MW;
                }
            }
            else {
                #any grammeme will suffice
                for my $gg(@gr) {
                    if ($v =~ /g v="$gg"/) {
                        #found grammeme, check next variant
                        next MW;
                    }
                }
            }
        }
        return 1;
    }
    return 0;
}
