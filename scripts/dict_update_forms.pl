#!/usr/bin/perl
use strict;
use utf8;
use DBI;
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

#if there are any words still not checked by form2tf, we should do nothing
my $max1 = $dbh->prepare("SELECT MAX(tf_id) AS max1 FROM tokens");
my $max2 = $dbh->prepare("SELECT MAX(tf_id) AS max2 FROM form2tf");
$max1->execute();
$max2->execute();
if ($max1->fetchrow_hashref()->{'max1'} != $max2->fetchrow_hashref()->{'max2'}) {
    $dbh->commit();
    exit 0;
}

my $scan = $dbh->prepare("SELECT form_text, rev_id FROM updated_forms LIMIT ?");
my $scan_f2tf = $dbh->prepare("SELECT tf_id FROM form2tf WHERE form_text=?");
my $del = $dbh->prepare("DELETE FROM updated_forms WHERE form_text=? AND rev_id=?");
my $ins = $dbh->prepare("INSERT INTO updated_tokens VALUES (?, ?)");

$scan->execute(10);
while(my $ref = $scan->fetchrow_hashref()) {
    $scan_f2tf->execute($ref->{'form_text'});
    while(my $ref1 = $scan_f2tf->fetchrow_hashref()) {
        $ins->execute($ref1->{'tf_id'}, $ref->{'rev_id'});
    }
    $del->execute($ref->{'form_text'}, $ref->{'rev_id'});
}

$dbh->commit();
