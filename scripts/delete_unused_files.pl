#!/usr/bin/perl
use strict;
use DBI;
use Config::INI::Reader;

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'}) or die $DBI::errstr;
my $scan = $dbh->prepare("SELECT url FROM downloaded_urls WHERE filename=?");

my $GO = $ARGV[1] eq 'go' ? 1 : 0;
my $count_all = 0;
my $count_to_delete = 0;
my $total_size = 0;

opendir D, '../files/saved' or die "Failed to open dir";
while (my $f = readdir D) {
    next unless -f "../files/saved/$f";
    my $ff = $f;
    $ff =~ s/\.html?$//;
    $scan->execute($ff);
    ++$count_all;
    if (!$scan->fetchrow_hashref()) {
        $count_to_delete++;
        $total_size += (stat("../files/saved/$f"))[7];
        if ($GO) {
            print "deleting $f\n";
            unlink "../files/saved/$f" or warn "Failed to delete $f";
        } else {
            print "should delete $f\n";
        }
    }
}
close D;

printf "Total files: %d, %s: %d (%.2f Mb)\n", $count_all, ($GO ? 'deleted' : 'to delete'), $count_to_delete, $total_size / (1024 * 1024);

if (!$GO && $count_to_delete) {
    print "Now run '$0 $ARGV[0] go' to delete the files\n";
}
