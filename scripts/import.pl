#!/usr/bin/perl
use strict;
use warnings;
use utf8;

use lib '/home/grand/corpus/perl/lib';
use OpenCorpora::Dict::Importer;

binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

my $importer = new OpenCorpora::Dict::Importer;
$importer->read_rules('/home/grand/corpus/scripts/import_rules.txt');
$importer->read_bad_lemma_grammems('/home/grand/corpus/scripts/bad_lemma_grammems.txt');
$importer->preload_list('anim0', '/home/grand/corpus/scripts/lists/Del_anim-inan&Add_ANim.txt');
$importer->preload_list('numr0', '/home/grand/corpus/scripts/lists/list_numr_dupl_gent.txt');
$importer->read_aot('/home/grand/aot_dump.2');
