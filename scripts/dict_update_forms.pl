#!/usr/bin/perl
use strict;
use utf8;
use DBI;

my $pwd = $ENV{'_'};
$pwd =~ s/\/[^\/]+$//;

my $lock_path = "$pwd/dict_uf.lock";
if (-f $lock_path) {
    die ("lock exists, exiting");
}

open my $lock, ">$lock_path";
print $lock 'lock';
close $lock;

#looking for the config file
my %mysql;
open C, $pwd.'/../lib/config.php' or die "Cannot open config file";
while(<C>) {
    if (/\$config\['mysql_(\w+)'\]\s*=\s*'([^']+)'/) {
        $mysql{$1} = $2;
    }
}
close C;

#main
my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");

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
