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

prepare_raw_data();                  # подготовка исходных данных - изъятие целевых слов

calc_diff();                         # поиск слов из Wiki, которых нет в словаре
create_diff_wiki();                  # генерация усеченной версии дампа Wiki - только для слов из предыдущего строки

get_top_word_form_wiki();            # (необязательно) - генерация сортированного по популярности списка шаблонов из вики

get_all_word_form($diff_wiki, $dir);  # генерация словоформ для неизвестных слов

die("the end :)\n");

# -------------------------------------------------
# -              ------ END ------                -
# -------------------------------------------------