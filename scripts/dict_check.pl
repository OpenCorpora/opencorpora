#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

my $lock_path = "/var/lock/opcorpora_dictcheck.lock";
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

#main
my %bad_pairs;
my %all_grammems;

my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $clear = $dbh->prepare("DELETE FROM dict_errata WHERE rev_id IN (SELECT rev_id FROM dict_revisions WHERE lemma_id=?)");
my $update = $dbh->prepare("UPDATE dict_revisions SET dict_check='1' WHERE rev_id=? LIMIT 1");

get_gram_info();
my @revisions = @{get_new_revisions()};
while(my $ref = shift @revisions) {
    $clear->execute($ref->{'lemma_id'});
    check($ref);
    $update->execute($ref->{'id'});
}

unlink ($lock_path);

##### SUBROUTINES #####
sub get_new_revisions {
    my $scan = $dbh->prepare("SELECT rev_id, lemma_id, rev_text FROM dict_revisions WHERE dict_check=0 ORDER BY rev_id LIMIT 1000");
    $scan->execute();
    my $txt;
    my @revs;
    while(my $ref = $scan->fetchrow_hashref()) {
        $txt = decode('utf8', $ref->{'rev_text'});
        push @revs, {'id' => $ref->{'rev_id'}, 'lemma_id' => $ref->{'lemma_id'}, 'text' => $txt};
    }
    return \@revs;
}
sub get_gram_info {
    my $scan0 = $dbh->prepare("SELECT gram_id, inner_id FROM gram WHERE parent_id=0");
    $scan0->execute();
    my %h;
    while(my $ref = $scan0->fetchrow_hashref()) {
        %h = ();
        $h{$ref->{'inner_id'}} = 0;
        $all_grammems{$ref->{'inner_id'}} = 0;
        my $scan1 = $dbh->prepare("SELECT gram_id, inner_id FROM gram WHERE parent_id=".$ref->{'gram_id'});
        $scan1->execute();
        while(my $ref1 = $scan1->fetchrow_hashref()) {
            $h{$ref1->{'inner_id'}} = 0;
            $all_grammems{$ref1->{'inner_id'}} = 0;
            my $scan2 = $dbh->prepare("SELECT gram_id, inner_id FROM gram WHERE parent_id=".$ref1->{'gram_id'});
            $scan2->execute();
            while (my $ref2 = $scan2->fetchrow_hashref()) {
                $all_grammems{$ref2->{'inner_id'}} = 0;
                $h{$ref2->{'inner_id'}} = 0;
            }
        }
        if (scalar keys %h > 1) {
            #this is a cluster
            for my $k1(keys %h) {
                for my $k2(keys %h) {
                    next if $k1 eq $k2;
                    $bad_pairs{"$k1|$k2"} = $bad_pairs{"$k2|$k1"} = 0;
                }
            }
        }
    }
}
sub check {
    my $ref = shift;
    my $newerr = $dbh->prepare("INSERT INTO dict_errata VALUES(NULL, ?, ?, ?, ?)");
    $ref->{'text'} =~ /<l t=".+">(.+)<\/l>/;
    my $lg_str = $1;
    my @lemma_gram = ();
    while($lg_str =~ /<g v="([^"]+)"\/>/g) {
        push @lemma_gram, $1;
    }
    my @form_gram = ();
    my @all_gram = ();
    while($ref->{'text'} =~ /<f t="([^"]+)">(.+?)<\/f>/g) {
        my ($ft, $fg_str) = ($1, $2);
        @form_gram = ();
        while($fg_str =~ /<g v="([^"]+)"\/>/g) {
            push @form_gram, $1;
        }
        @all_gram = (@lemma_gram, @form_gram);
        if (my $err = is_incompatible(\@all_gram)) {
            $newerr->execute(time(), $ref->{'id'}, 1, "<$ft> has incompatible grammems: $err");
        }
        elsif (my $err = has_unknown_grammems(\@all_gram)) {
            $newerr->execute(time(), $ref->{'id'}, 2, "<$ft> has unknown grammem: $err");
        }
    }
}
sub is_incompatible {
    my @gram = @{shift()};
    for my $i(0..$#gram) {
        for my $j($i+1..$#gram) {
            exists $bad_pairs{$gram[$i].'|'.$gram[$j]} && return $gram[$i].'|'.$gram[$j];
        }
    }
    return 0;
}
sub has_unknown_grammems {
    my @gram = @{shift()};
    for my $g(@gram) {
        exists $all_grammems{$g} || return $g;
    }
    return 0;
}
