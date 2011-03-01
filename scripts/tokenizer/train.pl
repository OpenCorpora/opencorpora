#!/usr/bin/perl
use strict;
use DBI;
use Statistics::Regression;

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
my $scan = $dbh->prepare("SELECT * FROM tokenizer_learn_data");
my $coeff_drop = $dbh->prepare("DELETE FROM tokenizer_coeff");
my $coeff_ins = $dbh->prepare("INSERT INTO tokenizer_coeff VALUES(?,?)");
$scan->execute();
my $reg = Statistics::Regression->new("Our regression", ["const", "F1", "F2", "F3", "F4", "F5"]);
while(my $ref = $scan->fetchrow_hashref()) {
    my @vector = split(//, sprintf("%.5b", $ref->{'vector'}));
    @vector = (1, @vector);
    $reg->include($ref->{'border'}, \@vector);
}

$reg->print();

my @coeff = $reg->theta();
$coeff_drop->execute();
for my $i(0..$#coeff) {
    $coeff_ins->execute($i, $coeff[$i]);
}
