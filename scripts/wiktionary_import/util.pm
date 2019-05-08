package util;

use utf8; # чтобы регулярные выражения работали с кириллицей

1;

# --------------------------------------------------------

# сохраняет хэш в файл
sub save_to_file {
    my($fname, %hash) = @_;
    
    open(FILE,">:utf8",$fname) or die "$!\n";
    print "save to '$fname'\n";
    
    foreach $key (sort keys %hash) {                      # сортировка ключей по алфавиту в порядке возрастания
        print FILE "$key ($hash{$key})\n";
    }
    
    close(FILE);
    print "saved\n";
}

# сохраняет хэш в файл, сортируя ключи как числа в порядке убывания
sub save_to_file_decimal {
    my($fname, %hash) = @_;
    
    open(FILE,">:utf8",$fname) or die "$!\n";
    print "save to '$fname'\n";
    
    foreach $key (sort { $b <=> $a } keys %hash) {       # сортировка ключей как чисел в порядке убывания
        print FILE "$key ($hash{$key})\n";
    }
    
    close(FILE);
    print "saved\n";
}

# загружает хэш из файла
sub load_from_file {
    my($fname) = @_;
    
    open(FILE,"<:utf8",$fname) or die "$!\n";
    print "load from '$fname'\n";
    
    %hash = ();
    while(my $line = <FILE>) {
        if ($line =~ /(.+)\s\((.+)\)/) {
            $hash{$1} = $2;
        }
    }
    
    close(FILE);
    print "loaded\n";
    
    return %hash;
}

# выбрасывает из вики бесполезные для распознавания словоформ данные
#   + отсекает страницы без русского {{-ru-}} сегмента 
# в результате дамп сжимается примерно в 10 раз как по объёму, так и по строкам
# что очень благоприятно сказывается на дальнейших вычислениях
sub chop_wiki {
    my ($wiki_name, $result_name) = @_;
    
    open (FILE,"<:utf8",$wiki_name) or die "$!\n";
    open (FILE2,">:utf8",$result_name) or die "$!\n";
    
    print("start chop wiki '$wiki_name'\n");
        
    my $skip = 0;
    my $ru_segment = 0;
    my @page = ();
    my $i = 0;  my $time = time;
    while(my $line = <FILE>) {
        $i++; if ($i % 1000 == 0 && ((time - $time) > 1)) { print("  read: ".$i." lines\n"); $time = time; }
        
        if  ( 
                $line =~ /^\{\{перев-блок/ || 
                $line =~ /^\{\{родств-блок/ || 
                $line =~ /^\{\{phrase/                
            )  { $skip = 1; }        
            
        if ( $line =~ /\{\{-(.+?)-\}\}/  && $1 eq "ru") { $ru_segment = 1; } # check ru_segment
            
        unless (    $skip  ||                                   # check skip block or skip-at-once condition    
                    $line =~ /^\s*<id>/ ||
                    $line =~ /^\s*<ns>/ ||
                    $line =~ /^\s*<parentid>/ ||
                    $line =~ /^\s*<timestamp>/ ||
                    $line =~ /^\s*<contributor>/ ||
                    $line =~ /^\s*<\/contributor>/ ||
                    $line =~ /^\s*<minor \/>/ ||
                    $line =~ /^\s*<comment>.*<\/comment>/ ||
                    $line =~ /^\s*<sha1>/ ||
                    $line =~ /^\s*<model>/ ||
                    $line =~ /^\s*<format>/ ||
                    $line =~ /^\s*<redirect/ ||
                    $line =~ /^\s*<username>/ ||
                    $line eq "\n" ||
                    $line =~ /^#\s*/
               ) { push (@page, $line); }        
        
        if ($line eq "}}\n") { $skip = 0; }
        
        if ( $line =~ /<\/page>/ ) {  # если </page>, значит страница кончилась и пора подводить итоги
            if ($ru_segment) {
                foreach $p (@page) { print FILE2 $p; }
            }
        
            $skip = 0; 
            $ru_segment = 0;
            @page = ();
        }
    }
    close(FILE);
    close(FILE2);
    print("chop wiki done!\n");
}
