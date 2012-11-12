#!/usr/bin/env perl

use strict;
use utf8;
use DBI;
use Config::INI::Reader;

$ARGV[0] or die "Usage: $0 <path_to_config.ini>";
#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

#main
my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'});
if (!$dbh) {
    die $DBI::errstr;
}

my $scan = $dbh->prepare("
    SELECT pool_id, status, grammemes
    FROM morph_annot_pools p
    LEFT JOIN morph_annot_pool_types t
    ON (p.pool_type = t.type_id)
    ORDER BY pool_id
");
$scan->execute();
while (my $r = $scan->fetchrow_hashref()) {
    printf "%d\t%s\t%d\n",
        $r->{'pool_id'}, $r->{'grammemes'}, $r->{'status'};
}
