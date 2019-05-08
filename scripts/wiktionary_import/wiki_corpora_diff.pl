#!/usr/bin/env perl

# ------------ IMPORT  ------------------------------------

use utf8; # чтобы регулярные выражения работали с кириллицей
use util;
use raw_parse;
use get_form_noun;

# ------------  VARIABLES  --------------------------------

        # путь к исходым данным
$corpora_dictionary_file = "dict.opcorpora.xml";    # путь к словарю OpenCorpora
$wiki_dictionary_file = "ruwiktionary-latest-pages-articles.xml";  # путь к дампу википедии
$corpora_text_file = "annot.opcorpora.xml";     # путь к размеченному корпусу

$wiki_dictionary_file_chop = "ruwiktionary-latest-pages-articles_chop.xml"; # куда и как сохранять/брать усеченную версию вики
util::chop_wiki($wiki_dictionary_file, $wiki_dictionary_file_chop);  # (желательно запустить 1 раз)
$wiki_dictionary_file = $wiki_dictionary_file_chop;    # советую отчопать исходный дамп и работать именно с выжимкой

        # а здесь прописываем, где хотим видеть новые данные
$dir = "./data/";
$diff_wiki_corpora = "./data/diff.txt"; # слова, которые есть в вики, но нет в OpenCorpora
$diff_wiki = "./data/wiki_of_diff.xml"; # дамп, в котором есть только неизвестные слова

# ------------  EXECUTING  -------------------------------

prepare_raw_data();

calc_diff();
create_diff_wiki();

get_top_word_form_wiki();

# get_all_word_form($diff_wiki, $dir);

die("the end :)\n");

# -------------------------------------------------
# -              ------ END ------                -
# -------------------------------------------------

sub get_all_word_form {
    my ($diff_wiki, $dir) = @_;

    #get_form_noun::get_word_form_noun_m_ina_1a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_n_ina_7a($diff_wiki, $dir, $dir."corpora_texts.txt");
        
    #get_form_noun::get_word_form_noun_f_ina_1a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_f_ina_7a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_f_ina_8a($diff_wiki, $dir, $dir."corpora_texts.txt");
    
    #-------------------- ^^^ DONE AND SENDED ^^^
    
    #get_form_noun::get_word_form_noun_m_a_5xa($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_f_ina_3xa($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_m_a_1a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_f_a_3xa($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_m_ina_3a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_m_a_3a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_m_ina_0($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_f_ina_3a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_n_ina_1a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_m_a_1oa($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_m_ina_2a($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_n_ina_0($diff_wiki, $dir, $dir."corpora_texts.txt");
    #get_form_noun::get_word_form_noun_f_a_1a($diff_wiki, $dir, $dir."corpora_texts.txt");
	
	#-------------------- ^^^ NEW ^^^
	
	#get_form_noun::get_word_form_noun_f_ina_0($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_ina_2a2($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_ina_2a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_ina_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_ina_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_ina_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_a_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_a_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_a_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_a_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_ina_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_ina_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_ina_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_ina_6a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_ina_6a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_ina_6a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_6a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_a_6a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_7a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_a_7a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_a_7a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_ina_7a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_8a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_ina_3a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_3a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_0($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_a_0($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_a_0($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_a_2a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_f_ina_2a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_m_a_2a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_noun_n_a_1a($diff_wiki, $dir, $dir."corpora_texts.txt");

	### adverbs
	#get_form_noun::get_word_form_adv($diff_wiki, $dir, $dir."corpora_texts.txt");

	### adjectives
	#get_form_noun::get_word_form_adj_1a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_adj_1xa($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_adj_2a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_adj_3a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_adj_3aX($diff_wiki, $dir, $dir."corpora_texts.txt");  # the same as 3a
	#get_form_noun::get_word_form_adj_4a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_adj_5a($diff_wiki, $dir, $dir."corpora_texts.txt");
	#get_form_noun::get_word_form_adj_6a($diff_wiki, $dir, $dir."corpora_texts.txt");
	
}

sub get_top_word_form_wiki {
    open (FILE,"<:utf8",$diff_wiki) or die "fail $!\n";
    
    print("start parse wiki\n");
    
    my %forms = ();
    
    my $i = 0;                      # line counter
    my $title;                      # page title    
    my $success = 0;                # страница удовлетворяет требованиям (не служебная и т.п.)
    my $ru_segment = 0;             # читается сейчас русский сегмент или нет
    my $time = time;                # счетчик времени
    while(my $line = <FILE>) {
        $i++; if ($i % 1000 == 0 && ((time - $time) > 1)) { print("  wiki: ".$i." lines\n"); $time = time; }
        
        if ( $line =~ /<title>(.+?)<\/title>/ ) {   # изымаем из "<title>текст</title>" текст в нижнем регистре
            $title = lc ($1);
        }
        if ( $line =~ /\{\{-(.+?)-\}\}/ ) {         # search for {{-ru-}} segment
            if ($1 eq "ru") {
                $success = 1;
                $ru_segment = 1;
            } else {
                $ru_segment = 0;
            }
        }
        
        if ($ru_segment && $line =~ /^[{|]{0,3}(.*)/ ) { # может начинаться с {{| или | (до 3 символов)
            if ( $1 =~ /^сущ ru/ || $1 =~ /^прил ru/ || $1 =~ /^гл ru/ || $1 =~ /^мест ru/ || $1 =~ /^числ ru/) { 
                my $a = $line;
                chomp($a);
                $forms{$a}++; 
            }        
        }
        
        if ( $line =~ /<\/page>/ ) {            # если </page>, значит страница кончилась и пора подводить итоги            
            # обнуляем переменные для новой страницы
            $success = 0;
            $ru_segment = 0;
        }
    }
    print("parse wiki done!\n");
    close(FILE);
    
    # меняем в хэше ключ со значением местами
    my %forms2 = ();
    foreach $key (keys %forms) {    
        my $value = $forms{$key};
        $forms2{$value} = $key;
    }
    
    util::save_to_file_decimal($dir."wiki2forms_top.txt", %forms2);
}

sub prepare_raw_data {
        # т.к. файлы немаленькие, то советую один раз распарсить их и сохранить в файлы
        # а потом только загружать готовый результат и крутить его как душе угодно
    unless (-d $dir) { mkdir $dir or die "$!\n"; }
   
    my %corpora_lemmas = raw_parse::parse_corpora_dictionary($corpora_dictionary_file);
        util::save_to_file($dir."corpora_lemmas.txt", %corpora_lemmas);
    my %wiki_lemmas = raw_parse::parse_wiki_dictionary($wiki_dictionary_file);
        util::save_to_file($dir."wiki_lemmas.txt", %wiki_lemmas);
    my %corpora_texts = raw_parse::parse_corpora_texts($corpora_text_file);
        util::save_to_file($dir."corpora_texts.txt", %corpora_texts);
}

sub calc_diff {
    my %corpora_lemmas = util::load_from_file($dir."corpora_lemmas.txt");
    my %wiki_lemmas = util::load_from_file($dir."wiki_lemmas.txt");
    my %corpora_texts = util::load_from_file($dir."corpora_texts.txt");

    # ищем пересечения слов из викисловаря и словаря OpenCorpora (и кладем их в хеш %diff)
    %diff = ();
    print("start calculating diff\n");
    open(FILE,">:utf8",$diff_wiki_corpora) or die "$!\n";
    foreach $key (sort keys %wiki_lemmas) {
        if (not exists $corpora_lemmas{$key}) {
            print FILE "$key ($wiki_lemmas{$key})\n";
            $diff{$key}++;
        }
    }
    close(FILE);
    print("end calculating diff\n");
}

# выделяет из вики только страницы, относящиеся к новым (diff) словам
sub create_diff_wiki {
    my %diff = util::load_from_file($diff_wiki_corpora);
    
    open (FILE,"<:utf8",$wiki_dictionary_file) or die "fail load wiki dictionary: $!\n";
    open (FILE2,">:utf8",$diff_wiki) or die "$!\n";
    
    print("start parse wiki to diff part\n");
        
    my $i = 0;                      # line counter
    my $title;                      # page title
    my $success = 0;                   
    my $time = time;                # счетчик времени
    while(my $line = <FILE>) {
        $i++; if ($i % 1000 == 0 && ((time - $time) > 1)) { print("  wiki: ".$i." lines\n"); $time = time; }
        
        if ( $line =~ /<title>(.+?)<\/title>/ ) {   # изымаем из "<title>текст</title>" текст в нижнем регистре
            $title = lc ($1);
            if (exists $diff{$title}) {
                print FILE2 "  <page>\n";
                $success = 1;
            }
        }
        
        if ($success) { print FILE2 $line; }
        
        if ( $line =~ /<\/page>/ ) {  $success = 0; }
    }
    close(FILE);
    close(FILE2);
    print("parse wiki for diff part is done!\n");
}
