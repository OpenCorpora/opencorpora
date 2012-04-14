#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;
use Config::INI::Reader;

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

#main
my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
$dbh->{'AutoCommit'} = 0;
if ($dbh->{'AutoCommit'}) {
    die "Setting AutoCommit failed";
}

my $scan = $dbh->prepare("SELECT rev_id, lemma_id, rev_text FROM dict_revisions WHERE f2l_check=0 ORDER BY rev_id LIMIT 2000");
my $del = $dbh->prepare("DELETE FROM form2lemma WHERE lemma_id=?");
my $ins = $dbh->prepare("INSERT INTO form2lemma VALUES(?, ?, ?, ?)");
my $upd = $dbh->prepare("UPDATE dict_revisions SET f2l_check=1 WHERE rev_id=? LIMIT 1");

$scan->execute();
while(my $ref = $scan->fetchrow_hashref()) {
    my $txt = decode('utf8', $ref->{'rev_text'});
    $txt =~ /<l t="([^"]+)">(.*?)<\/l>/;
    my ($lemma, $lemma_gr) = ($1, $2);
    $del->execute($ref->{'lemma_id'});
    while ($txt =~ /<f t="([^"]+)">(.*?)<\/f>/g) {
        my ($f, $g) = ($1, $2);
        #print STDERR "$f\t".$ref->{'lemma_id'}."\t$lemma\t$g\n";
        $ins->execute($f, $ref->{'lemma_id'}, $lemma, $lemma_gr.$g);
    }
    $upd->execute($ref->{'rev_id'});
    #print STDERR 'At revision '.$ref->{'rev_id'}."\n";
}

$dbh->commit();
