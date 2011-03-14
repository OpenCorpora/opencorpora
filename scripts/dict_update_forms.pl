#!/usr/bin/perl
use strict;
use utf8;
use DBI;

my $lock_path = "/var/lock/opcorpora_dict_uf.lock";
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
my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");

#if there are any words still not checked by form2tf, we should do nothing
my $prescan = $dbh->prepare("SELECT tf_id, tf_text FROM text_forms WHERE tf_id NOT IN (SELECT tf_id FROM form2tf) LIMIT 1");
$prescan->execute();
if($prescan->fetchrow_hashref()) {
    unlink ($lock_path);
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

unlink ($lock_path);
