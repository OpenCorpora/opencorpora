#!/usr/bin/perl
use strict;
use warnings;
use utf8;

use FindBin;
use lib "$FindBin::Bin/../../perl/lib";

use Config::INI::Reader;
use OpenCorpora::Dict::Importer;

@ARGV or die "Usage: $0 <config>";

my $conf      = Config::INI::Reader->read_file($ARGV[0]);
my $import_path = $conf->{project}{root}."/scripts/aot_import";

binmode(STDOUT, ":utf8");
binmode(STDERR, ":utf8");

my $importer = new OpenCorpora::Dict::Importer;
$importer->read_rules("$import_path/import_rules.txt");
$importer->read_bad_lemma_grammems("$import_path/bad_lemma_grammems.txt");
$importer->preload_list('anim0', "$import_path/lists/Del_anim-inan&Add_ANim.txt");
$importer->preload_list('anim1', "$import_path/lists/remove_ANim.txt");
$importer->preload_list('numr0', "$import_path/lists/list_numr_dupl_gent.txt");
$importer->preload_list('adjf_fixd_del', "$import_path/lists/list_adjf_fixd_delete.txt");
$importer->preload_list('adjf_fixd_advb', "$import_path/lists/list_adjf_fixd_ADVB.txt");
$importer->preload_list('adjf_fixd_noun', "$import_path/lists/list_adjf_fixd_NOUN.txt");
$importer->preload_list('nouns_subst', "$import_path/lists/nouns_subst.txt");
$importer->preload_list('arch_adj', "$import_path/lists/add_Arch_ADJF.txt");
$importer->preload_list('litr', "$import_path/lists/add_Litr.txt");
$importer->preload_list('dist_prts', "$import_path/lists/add_Dist_PRTS.txt");
$importer->preload_list('dist_aux', "$import_path/lists/add_Dist_aux.txt");
$importer->preload_list('infr0', "$import_path/lists/add_Infr_nomn_plur.txt");
$importer->preload_list('infr1', "$import_path/lists/add_Infr_gent_plur.txt");
$importer->preload_list('infr_abl_sg', "$import_path/lists/add_Infr_ablt_sing.txt");
$importer->preload_list('infr_abl_pl', "$import_path/lists/add_Infr_ablt_plur.txt");
$importer->preload_list('infr3', "$import_path/lists/add_Infr_ADJS.txt");
$importer->preload_list('infr_comp', "$import_path/lists/add_Infr_COMP.txt");
$importer->preload_list('pred_del', "$import_path/lists/pred_del.txt");
$importer->preload_list('adjs_del', "$import_path/lists/adjs_forms_del.txt");
$importer->preload_list('pred_intj', "$import_path/lists/pred_to_intj.txt");
$importer->preload_list('erro_adjs', "$import_path/lists/add_Erro_ADJS.txt");
$importer->preload_list('erro_prts', "$import_path/lists/add_Erro_PRTS.txt");
$importer->preload_list('count', "$import_path/lists/add_Coun_gent_plur.txt");
$importer->preload_list('abbr_del', "$import_path/lists/abbr_del.txt");
$importer->read_aot("bzcat /data/files/aot_dump.7.bz2 |");
