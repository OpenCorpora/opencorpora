#!/usr/bin/env perl
use strict;
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

my $add = $dbh->prepare("INSERT INTO morph_annot_candidate_samples VALUES(?, ?)");
my $update_pool = $dbh->prepare("UPDATE morph_annot_pools SET `status`='1' WHERE pool_id=? LIMIT 1");
my $find_pools = $dbh->prepare("SELECT pool_id, t.grammemes FROM morph_annot_pools p LEFT JOIN morph_annot_pool_types t ON (p.pool_type = t.type_id) WHERE status=0");
$find_pools->execute();
while (my $ref = $find_pools->fetchrow_hashref()) {
    process_pool($ref->{'pool_id'}, $ref->{'grammemes'});
}
$dbh->commit();

sub process_pool {
    my $pool_id = shift;
    my @gram_strings = split /@/, shift;
    printf STDERR "processing pool #%d: <%s>\n", $pool_id, join('>, <', @gram_strings);

    my @gram_sets;
    my @gramset_types;
    my $var = [];

    for my $i(0..$#gram_strings) {
        if ($gram_strings[$i] =~ /\|/) {
            push @gram_sets, [split /\|/, $gram_strings[$i]];
            push @gramset_types, 'or';
        } else {
            push @gram_sets, [split /\&/, $gram_strings[$i]];
            push @gramset_types, 'and';
        }
    }

    for my $i(0..$#gram_sets) {
        if ($gramset_types[$i] eq 'or') {
            $var = combine_or($var, $gram_sets[$i]);
        } else {
            $var = combine_and($var, $gram_sets[$i]);
        }
    }

    my @q;
    my @qt;
    for my $v(@$var) {
        @qt = ();
        for my $g(@$v) {
            push @qt, "rev_text LIKE '%v=\"$g\"%'";
        }
        push @q, "(".join(' AND ', @qt).")";
    }
    # rough filter
    my $q = "
        SELECT tfr.tf_id, tfr.rev_id, tfr.rev_text
        FROM tf_revisions tfr
        LEFT JOIN morph_annot_samples s USING (tf_id)
        LEFT JOIN morph_annot_moderated_samples ms USING (sample_id)
        LEFT JOIN morph_annot_pools p USING (pool_id)
        WHERE is_last = 1
        AND (p.status IS NULL OR p.status < 2 OR p.status = 9)
        AND (ms.status IS NULL OR p.status < 7 OR ms.status NOT IN (3, 4))
        AND (".join(' OR ', @q).")
    ";
    print STDERR $q."\n";
    my $s = $dbh->prepare($q);
    $s->execute();
    while (my $ref = $s->fetchrow_hashref()) {
        # finer check
        check_revision($pool_id, $ref->{'tf_id'}, $ref->{'rev_id'}, $ref->{'rev_text'}, \@gram_sets, \@gramset_types);
    }
    $update_pool->execute($pool_id);
}
sub combine_or {
    my $var= shift;
    my @gramset = @{shift()};

    my @new_var;

    if (!@$var) {
        for my $gr(@gramset) {
            push @new_var, [$gr];
        }
        return \@new_var;
    }

    for my $v(@$var) {
        for my $gr(@gramset) {
            push @new_var, [@$v, $gr];
        }
    }
    return \@new_var;
}
sub combine_and {
    my $var = shift;
    my @gramset = @{shift()};

    my @new_var;

    if (!@$var) {
        push @new_var, [@gramset];
        return \@new_var;
    }

    for my $v(@$var) {
        push @new_var, [@$v, @gramset];
    }
    return \@new_var;
}
sub check_revision {
    my ($pool_id, $tf_id, $rev_id, $rev_text, $gram_sets, $gramset_types) = @_;
    print STDERR "will check revision $rev_id, ";

    # are the "and"-restrictions really satisfied?
    for my $i(0..scalar(@$gram_sets)-1) {
        next unless $gramset_types->[$i] eq 'and';
        unless (var_has_all_gram($rev_text, $gram_sets->[$i])) {
            print STDERR "failed\n";
            return 0;
        }
    }

    # are there any variants that don't match any of the grammeme sets?

    if (has_extra_variants($rev_text, $gram_sets, $gramset_types)) {
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
    my ($rev_text, $gram_sets, $gramset_types) = @_;

    # we shall check whether there are variants that do not satisfy any grammem sets
    MW:while ($rev_text =~ /<v(.+?)<\/v>/g) {
        my $v = $1;
        for my $i(0..scalar(@$gram_sets)-1) {
            if ($gramset_types->[$i] eq 'and') {
                #all grammemes must be there
                my $flag_ok = 1;
                for my $gg(@{$gram_sets->[$i]}) {
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
                for my $gg(@{$gram_sets->[$i]}) {
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
