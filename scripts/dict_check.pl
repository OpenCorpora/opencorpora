#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;
use Data::Dump qw/dump/;

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
my %must;
my %may;

my %objtype = (
    0 => 'll',
    1 => 'lf',
    2 => 'fl',
    3 => 'ff'
);

my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $clear = $dbh->prepare("DELETE FROM dict_errata WHERE rev_id IN (SELECT rev_id FROM dict_revisions WHERE lemma_id=?)");
my $update = $dbh->prepare("UPDATE dict_revisions SET dict_check='1' WHERE rev_id=? LIMIT 100");

get_gram_info();
#print STDERR dump(%must)."\n";
my @revisions = @{get_new_revisions()};
while(my $ref = shift @revisions) {
    $clear->execute($ref->{'lemma_id'});
    check($ref);
    $update->execute($ref->{'id'});
}

unlink ($lock_path);

##### SUBROUTINES #####
sub get_new_revisions {
    my $scan = $dbh->prepare("SELECT rev_id, lemma_id, rev_text FROM dict_revisions WHERE dict_check=0 ORDER BY rev_id LIMIT 1");
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
    #bad pairs, all valid grammems
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
    #must
    my $scan1 = $dbh->prepare("SELECT g0.inner_id if_id, g1.inner_id then_id, g2.inner_id gram1, g3.inner_id gram2, r.restr_id, r.restr_type, r.obj_type
        FROM gram_restrictions r
        LEFT JOIN gram g0 ON (r.if_id = g0.gram_id)
        LEFT JOIN gram g1 ON (r.then_id = g1.gram_id)
        LEFT JOIN gram g2 ON (r.then_id = g2.parent_id)
        LEFT JOIN gram g3 ON (g2.gram_id = g3.parent_id)
        WHERE r.restr_type = ? OR r.restr_type = ?
        ORDER BY r.restr_type");
    $scan1->execute(0, 1);
    my $last_id = 0;
    my @real = ();
    while(my $ref = $scan1->fetchrow_hashref()) {
        @real = ($ref->{'then_id'});
        push @real, $ref->{'gram1'} if $ref->{'gram1'};
        push @real, $ref->{'gram2'} if $ref->{'gram2'};
        if ($ref->{'restr_type'} == 1) {
            #grammem must be there in some cases
            if ($ref->{'restr_id'} != $last_id) {
                my %t;
                $t{$_} = 1 for (@real);
                push @{$must{$objtype{$ref->{'obj_type'}}}{$ref->{'if_id'}}}, \%t;
            }
            else {
                $must{$objtype{$ref->{'obj_type'}}}{$ref->{'if_id'}}[-1]{$_} = 1 for (@real);
            }
        }
        else {
            #grammem is allowed in some cases
            $may{swap2($objtype{$ref->{'obj_type'}})}{$_}{$ref->{'if_id'}} = 1 for (@real);
        }
        $last_id = $ref->{'restr_id'};
    }

    # deleting what is forbidden
    $scan1->execute(2, 2);
    while(my $ref = $scan1->fetchrow_hashref()) {
        @real = ($ref->{'then_id'});
        push @real, $ref->{'gram1'} if $ref->{'gram1'};
        push @real, $ref->{'gram2'} if $ref->{'gram2'};
        delete $may{swap2($objtype{$ref->{'obj_type'}})}{$_}{$ref->{'if_id'}} for (@real);
    }
}
sub check {
    my $ref = shift;
    my $newerr = $dbh->prepare("INSERT INTO dict_errata VALUES(NULL, ?, ?, ?, ?)");
    $ref->{'text'} =~ /<l t="(.+)">(.+)<\/l>/;
    my ($lt, $lg_str) = ($1, $2);
    my @lemma_gram = ();
    while($lg_str =~ /<g v="([^"]+)"\/>/g) {
        push @lemma_gram, $1;
    }

    my %lemma_flags = ();
    
    if (my $err = is_incompatible(\@lemma_gram)) {
        $newerr->execute(time(), $ref->{'id'}, 1, "<$lt> ($err)");
        $lemma_flags{1} = 1;
    }
    if (my $err = has_unknown_grammems(\@lemma_gram)) {
        $newerr->execute(time(), $ref->{'id'}, 2, "<$lt> ($err)");
        $lemma_flags{2} = 1;
    }
    if (my $err = misses_oblig_grammems('ll', \@lemma_gram)) {
        $newerr->execute(time(), $ref->{'id'}, 4, "<$lt> ($err)");
    }
    if (my $err = has_disallowed_grammems('ll', \@lemma_gram)) {
        $newerr->execute(time(), $ref->{'id'}, 5, "<$lt> ($err)");
    }

    my @form_gram = ();
    my $form_gram_str;
    my @all_gram = ();
    my %form_gram_hash = ();

    while($ref->{'text'} =~ /<f t="([^"]+)">(.+?)<\/f>/g) {
        my ($ft, $fg_str) = ($1, $2);
        @form_gram = ();
        while($fg_str =~ /<g v="([^"]+)"\/>/g) {
            push @form_gram, $1;
        }
        @all_gram = (@lemma_gram, @form_gram);
        if (!$lemma_flags{1} && (my $err = is_incompatible(\@all_gram))) {
            $newerr->execute(time(), $ref->{'id'}, 1, "<$ft> ($err)");
        }
        if (!$lemma_flags{2} && (my $err = has_unknown_grammems(\@all_gram))) {
            $newerr->execute(time(), $ref->{'id'}, 2, "<$ft> ($err)");
        }
        if (my $err = misses_oblig_grammems('lf', \@lemma_gram, \@form_gram)) {
            $newerr->execute(time(), $ref->{'id'}, 4, "<$ft> ($err)");
        }
        if (my $err = misses_oblig_grammems('ff', \@form_gram)) {
            $newerr->execute(time(), $ref->{'id'}, 4, "<$ft> ($err)");
        }
        if (my $err = has_disallowed_grammems('fl', \@form_gram, \@lemma_gram)) {
            if (my $err0 = has_disallowed_grammems('ff', \@form_gram)) {
                $newerr->execute(time(), $ref->{'id'}, 5, "<$ft> ($err0)");
            }
        }
        $form_gram_str = join('|', sort @form_gram);
        if (my $f = $form_gram_hash{$form_gram_str}) {
            $newerr->execute(time(), $ref->{'id'}, 3, "<$ft>, <$f> ($form_gram_str)");
            return;
        } else {
            $form_gram_hash{$form_gram_str} = $ft;
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
sub misses_oblig_grammems {
    my $type = shift;
    my @gram = @{shift()};
    my $ref = shift;
    my @gram2 = $ref ? @$ref : @gram;

    if (exists $must{$type}{''}) {
        for my $cl(@{$must{$type}{''}}) {
            if (!has_any_grammem(\@gram2, $cl)) {
                return join('|', keys %{$must{$type}{''}});
            }
        }
    }

    for my $gr(@gram) {
        if (exists $must{$type}{$gr}) {
            for my $cl(@{$must{$type}{$gr}}) {
                if (!has_any_grammem(\@gram2, $cl)) {
                    return join('|', keys %{$must{$type}{$gr}});
                }
            }
        }
    }

    return 0;
}
sub has_disallowed_grammems {
    my $type = shift;
    my @gram = @{shift()};
    my $ref = shift;
    my @gram2 = $ref ? @$ref : @gram;

    for my $gr(@gram) {
        next if exists $may{$type}{$gr}{''};
        if (exists $may{$type}{$gr}) {
            if (!has_any_grammem(\@gram2, $may{$type}{$gr})) {
                return $gr;
            }
        }
    }

    return 0;
}
sub has_any_grammem {
    my $haystack_ref = shift;
    my $needle_ref = shift;
    my @haystack;
    my @needle;

    if (ref($haystack_ref) eq 'ARRAY') {
        @haystack = @$haystack_ref;
    }
    elsif (ref($haystack_ref) eq 'HASH') {
        @haystack = keys %$haystack_ref;
    }
    if (ref($needle_ref) eq 'ARRAY') {
        @needle = @$needle_ref;
    }
    elsif (ref($needle_ref) eq 'HASH') {
        @needle = keys %$needle_ref;
    }

    for my $h(@haystack) {
        for my $n(@needle) {
            $h eq $n && return 1;
        }
    }
    return 0;
}
sub swap2 {
    my $s = shift;
    $s =~ s/(.)(.)/$2$1/;
    return $s;
}
