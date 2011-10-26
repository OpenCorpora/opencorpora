#!/usr/bin/perl
use strict;
use utf8;
use Encode;
use DBI;
use Config::INI::Reader;

binmode(STDERR, ':utf8');

my $lock_path = "/var/lock/opcorpora_f2tf.lock";
if (-f $lock_path) {
    die ("lock exists, exiting");
}

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

open my $lock, ">$lock_path";
print $lock 'lock';
close $lock;

#main
my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");

my $scan = $dbh->prepare("SELECT tf_id, tf_text FROM text_forms WHERE tf_id NOT IN (SELECT tf_id FROM form2tf) LIMIT ?");
my $ins = $dbh->prepare("INSERT INTO form2tf VALUES(?, ?)");

$scan->execute(10);
while(my $ref = $scan->fetchrow_hashref()) {
    my $txt = $ref->{'tf_text'};
    $txt = decode('utf-8', $txt);
    #print STDERR "got text <$txt>";
    $txt =~ tr/А-Я/а-я/;
    $txt =~ s/[Ёё]/е/g;
    #print STDERR ", translated to <$txt>\n";
    $ins->execute($txt, $ref->{'tf_id'});
}

unlink ($lock_path);
