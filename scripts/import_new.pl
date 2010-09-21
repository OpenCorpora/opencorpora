#!/usr/bin/perl
use strict;
use warnings;
use utf8;

use OpenCorpora::Dict::Importer;
use Data::Dump qw/dump/;

binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

my $importer = new OpenCorpora::Dict::Importer;
$importer->read_rules('/home/grand/corpus/scripts/import_rules.txt');
#my $dump = dump($importer);
#$dump =~ s/\\x{([0-9a-f]+)}/X/gi;
#print $dump;
$importer->read_aot('/home/grand/corpus/morphs.mrd.dump.utf8');
#$importer->read_aot('/home/grand/corpus/scripts/test.txt');
