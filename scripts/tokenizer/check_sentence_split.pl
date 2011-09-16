#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

my @exc = ("им", "мин", "тыс", "англ", "нем", "фр", "рус", "(англ", "(нем");
my %exc = map {$_ => 1} @exc;
#reading config
my %mysql;
open F, $ARGV[0] or die "Failed to open $ARGV[0]";
while(<F>) {
    if (/\$config\['mysql_(\w+)'\]\s*=\s*'([^']+)'/) {
        $mysql{$1} = $2;
    }
}
close F;

my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $sent = $dbh->prepare("SELECT `sent_id`, `source` FROM sentences");
my $str_drop = $dbh->prepare("TRUNCATE TABLE sentences_strange");
my $str_ins = $dbh->prepare("INSERT INTO sentences_strange VALUES(?)");
my $str;
$str_drop->execute();
$sent->execute();

while (my $ref = $sent->fetchrow_hashref()) {
    $str = decode('utf8', $ref->{'source'});
    if ($str =~ /\s([^А-ЯЁA-Z0-9\s]+)[\.\!\?]\s+[А-ЯЁA-Z]/) {
        next if exists $exc{$1};
        $str_ins->execute($ref->{'sent_id'});
    }
}
