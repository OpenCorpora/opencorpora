# первоначальная обработка данных
package raw_parse;

use utf8;
use util;

1;

# распарсить словарь OpenCorpora
# на вход подавать путь к XML словаря
# на выходе будет ассоциативный массив вида (лемма, кол-во копий леммы)
sub parse_corpora_dictionary {
    my($fname) = @_;

    open(FILE,"<:utf8",$fname) or die "fail load corpora dictionary: $!\n";
    
    print("start parse corpora\n");
    
    my %lemmas = ();
    
    my $i = 0;  my $time = time;
    while(my $line = <FILE>) {
        $i++; if ($i % 1000 == 0 && ((time - $time) > 1)) { print("  corpora: ".$i." lines\n"); $time = time; }
        
        if ( $line =~ /<l t="(.+?)">/ ) {   # ищем подстроку вида <l t="(текст)"> и изымаем из неё (текст)
            $lemmas{lc $1}++;               # если нашли, то увеличиваем счетчик кол-ва данной леммы
        }
    }
    print("parse corpora done!\n");
    
    close(FILE);
    
    return %lemmas;
}

# распарсить словарь RuWiktionary
# на вход подавать путь к XML словаря
# на выходе будет ассоциативный массив вида (лемма, кол-во копий леммы)
sub parse_wiki_dictionary {
    my($fname) = @_;
 
    open (FILE,"<:utf8",$fname) or die "fail load wiki dictionary: $!\n";
    
    print("start parse wiki\n");
    
    my %lemmas = ();
        
    my $title;                      # page title    
    my $success = 0;                # страница удовлетворяет требованиям (не служебная и т.п.)
    my $count = 0;                  # счетчик количества лемм(?) на странице
    my $skip = 0;                   # служебная, чтобы не учитывать кривое форматирование страницы
    my $ru_segment = 0;             # читается сейчас русский сегмент или нет    
    my $i = 0;  my $time = time;
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
        
        # ищем подзаголовки вида(==текст==)
        if ($skip != 1 && $ru_segment && $line =~ /^==([^=]+)==$/ ) {              # если форматирование не нарушено И русский сегмент И есть "==текст==", то
            $tag = lc $1;
            if ($tag =~ /произношение/ || $tag =~ /значение/ || $tag =~ /морфология/ || $tag =~ /морфологические\sи\sсинтаксические\sсвойства/) {  # проверка корректности форматирования
                $skip = 1;      # bad counter;
            }
            $count++;           # увеличиваем счетчик леммы
        }
        
        if ( $line =~ /<\/page>/ ) {            # если </page>, значит страница кончилась и пора подводить итоги
            if ($success) { 
                if ($count == 0) {$count = 1;}  # если не было подзаголовков (== ==), то считается, что лемма была 1 раз
                if ($skip == 1) {$count = 1;}   # при битом форматировании считается, что лемма была 1 раз
                $lemmas{$title} += $count;      # накидываем счетчик количества лемм, т.е. суммируем например "Ворону" с "вороной"
            }
            
            # обнуляем переменные для новой страницы
            $success = 0;
            $count = 0;
            $ru_segment = 0;
            $skip = 0;
        }
    }
    print("parse wiki done!\n");
    close(FILE);
    
    return %lemmas;
}

# распарсить тексты OpenCorpora
# на вход подавать путь к XML словаря
# на выходе будет массив "UNKN" слов
sub parse_corpora_texts {
    my($fname) = @_;
    
    open(FILE,"<:utf8",$fname) or die "fail load corpora texts: $!\n";
    
    print("start parse corpora texts\n");
    
    my %words = ();
    
    my $i = 0; my $time = time;
    while(my $line = <FILE>) {
        $i++; if ($i % 1000 == 0 && ((time - $time) > 1)) { print("  texts: ".$i." lines\n"); $time = time; }
        
        if ( $line =~ /"UNKN"/ ) {             # ищем строку с текстом "UNKN"
            $line =~ /text="(.+?)"/;           # а в ней подстроку text="текст"
            my $word = lc $1;
            $words{$word}++;                      # и изымаем результат в массив
        }
    }
    print("parse corpora texts done!\n");
    
    close(FILE);
    
    return %words;
}
