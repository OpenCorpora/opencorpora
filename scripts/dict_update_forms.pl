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
my $prescan = $dbh->prepare("SELECT tf_id, tf_text FROM text_forms WHERE tf_id NOT IN (SELECT tf_id FROM form2tf) LIMIT 1");
$prescan->execute();
if($prescan->fetchrow_hashref()) {
    die "form2tf isn't up to date";
}

my $scan = $dbh->prepare("SELECT form_text FROM updated_forms LIMIT ?");
my $scan_f2tf = $dbh->prepare("SELECT tf_id FROM form2tf WHERE form_text=?");
my $del = $dbh->prepare("DELETE FROM updated_forms WHERE form_text=?");
my $upd = $dbh->prepare("UPDATE text_forms SET dict_updated='1' WHERE tf_id=?");

$scan->execute(2);
while(my $ref = $scan->fetchrow_hashref()) {
    $scan_f2tf->execute($ref->{'form_text'});
    while(my $ref1 = $scan_f2tf->fetchrow_hashref()) {
        $upd->execute($ref1->{'tf_id'});
    }
    $del->execute($ref->{'form_text'});
}

$dbh->commit();
