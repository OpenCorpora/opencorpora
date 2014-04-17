#!/usr/bin/perl
use strict;
use utf8;
use Encode;
use DBI;
use Config::INI::Reader;

binmode(STDERR, ':utf8');

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

my $max1 = $dbh->prepare("SELECT MAX(tf_id) AS max1 FROM tokens");
my $max2 = $dbh->prepare("SELECT MAX(tf_id) AS max2 FROM form2tf");
my $scan = $dbh->prepare("SELECT tf_id, tf_text FROM tokens WHERE tf_id NOT IN (SELECT tf_id FROM form2tf) ORDER BY tf_id LIMIT ?");
my $ins = $dbh->prepare("INSERT INTO form2tf VALUES(?, ?)");

$max1->execute();
$max2->execute();
if ($max1->fetchrow_hashref()->{'max1'} == $max2->fetchrow_hashref()->{'max2'}) {
    $dbh->commit();
    exit 0;
}

$scan->execute(500);
while (my $ref = $scan->fetchrow_hashref()) {
    my $txt = $ref->{'tf_text'};
    $txt = decode('utf-8', $txt);
    $txt =~ tr/А-Я/а-я/;
    $txt =~ s/[Ёё]/е/g;
    $ins->execute($txt, $ref->{'tf_id'});
}

$dbh->commit();
