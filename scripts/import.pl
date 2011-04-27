#!/usr/bin/perl
use strict;
use warnings;
use utf8;

use lib '/home/grand/corpus/perl/lib';
use OpenCorpora::Dict::Importer;

binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

my $importer = new OpenCorpora::Dict::Importer;
$importer->read_rules('/corpus/scripts/import_rules.txt');
$importer->read_bad_lemma_grammems('/corpus/scripts/bad_lemma_grammems.txt');
$importer->preload_list('anim0', '/corpus/scripts/lists/Del_anim-inan&Add_ANim.txt');
$importer->preload_list('anim1', '/corpus/scripts/lists/remove_ANim.txt');
$importer->preload_list('numr0', '/corpus/scripts/lists/list_numr_dupl_gent.txt');
$importer->preload_list('adjf_fixd_del', '/corpus/scripts/lists/list_adjf_fixd_delete.txt');
$importer->preload_list('adjf_fixd_advb', '/corpus/scripts/lists/list_adjf_fixd_ADVB.txt');
$importer->preload_list('adjf_fixd_noun', '/corpus/scripts/lists/list_adjf_fixd_NOUN.txt');
$importer->preload_list('nouns_subst', '/corpus/scripts/lists/nouns_subst.txt');
$importer->preload_list('arch', '/corpus/scripts/lists/add_Arch.txt');
$importer->preload_list('arch0', '/corpus/scripts/lists/add_Arch_nomn_plur.txt');
$importer->preload_list('infr0', '/corpus/scripts/lists/add_Infr_nomn_plur.txt');
$importer->preload_list('infr1', '/corpus/scripts/lists/add_Infr_gent_plur.txt');
$importer->preload_list('infr2', '/corpus/scripts/lists/add_Infr_VERB.txt');
$importer->preload_list('pred_del', '/corpus/scripts/lists/pred_del.txt');
$importer->preload_list('pred_intj', '/corpus/scripts/lists/pred_to_intj.txt');
$importer->preload_list('count', '/corpus/scripts/lists/add_Coun_gent_plur.txt');
$importer->read_aot('/home/grand/aot_dump.3');
