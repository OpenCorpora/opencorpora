#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

if (-f "f2l.lock") {
    die ("lock exists, exiting");
}

open my $lock, ">f2l.lock";
print $lock 'lock';
close $lock;

my $dbh = DBI->connect('DBI:mysql:corpora:127.0.0.1', 'corpora', 'corpora') or die $DBI::errstr;
$dbh->do("SET NAMES utf8");

my $scan = $dbh->prepare("SELECT rev_id, lemma_id, rev_text FROM dict_revisions WHERE f2l_check=0 ORDER BY rev_id LIMIT 1000");
my $del = $dbh->prepare("DELETE FROM form2lemma WHERE lemma_id=?");
my $ins = $dbh->prepare("INSERT INTO form2lemma VALUES(?, ?, ?, ?)");
my $upd = $dbh->prepare("UPDATE dict_revisions SET f2l_check=1 WHERE rev_id=? LIMIT 1");

$scan->execute();
while(my $ref = $scan->fetchrow_hashref()) {
    my $txt = decode('utf8', $ref->{'rev_text'});
    $txt =~ /<lemma text="([^"]+)"/;
    my $lemma = $1;
    $del->execute($ref->{'lemma_id'});
    while ($txt =~ /<form text="([^"]+)">(.+?)<\/form>/g) {
        my ($f, $g) = ($1, $2);
        #print STDERR "$f\t".$ref->{'lemma_id'}."\t$lemma\t$g\n";
        $ins->execute($f, $ref->{'lemma_id'}, $lemma, $g);
        if ($f =~ /ё/) {
            $f =~ s/ё/е/g;
            $ins->execute($f, $ref->{'lemma_id'}, $lemma, $g);
        }
    }
    $upd->execute($ref->{'rev_id'});
    #print STDERR 'At revision '.$ref->{'rev_id'}."\n";
}

unlink ("f2l.lock");
