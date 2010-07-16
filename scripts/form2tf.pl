#!/usr/bin/perl
use strict;
use utf8;
use Encode;
use DBI;

binmode(STDERR, ':utf8');

my $pwd = $ENV{'_'};
$pwd =~ s/\/[^\/]+$//;

my $lock_path = "$pwd/form2tf.lock";
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
