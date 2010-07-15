#!/usr/bin/perl
use strict;
use utf8;
use Encode;
use DBI;

binmode(STDERR, ':utf8');

if (-f "form2tf.lock") {
    die "lock exists, exiting";
}

open my $lock, ">form2tf.lock";
print $lock 'lock';
close $lock;

my $dbh = DBI->connect('DBI:mysql:corpora:127.0.0.1', 'corpora', 'corpora') or die $DBI::errstr;
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

unlink ("form2tf.lock");
