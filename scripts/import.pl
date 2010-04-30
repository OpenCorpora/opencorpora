#!/usr/bin/perl
#TODO: должна ли sg tm отменять граммему ед.ч.?
use constant DEBUG => 1;

use strict;
use utf8;
use DBI;

binmode(STDIN, ':utf8');
binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

my ($form, $pos, $gram);
my ($dbh, $newset, $set_id,  $newlemma, $newlemma_id, $newrev);
my @stack;

#возвращаемые типы (для дебага)
my @ret_types = qw/9/;
my %ret_types;
$ret_types{$_}=1 for (@ret_types);

#граммемы, одинаковые в АОТ и у нас
my @gram_aot = qw/од ед мн мр жр ср им рд дт вн тв пр зв 2 имя отч фам арх аббр разг жарг орг опч кач сравн притяж прев/;
#неодинаковые
my %gram_map = (
    'но' => 'неод',
    'лок' => 'гео',
    'дфст' => 'sg',
    'pl' => 'pl', #так надо
);
#добавляемые нами
my @gram_adhoc = qw/общ мест порядк/;
#части речи
my %pos_map = (
    'С' => 'СУЩ',
    #'П' => 'ПРИЛ',
    #'КР_ПРИЛ' => 'КР_ПРИЛ',
    #'МС-П' => 'ПРИЛ',
    #'ЧИСЛ-П' => 'ПРИЛ'
);
$gram_map{$_} = $_ for (@gram_aot);
$gram_map{$_} = $pos_map{$_} for (keys %pos_map);
$gram_map{$_} = $_ for (@gram_adhoc);
my @gram_order = (
    'од', 'но',
    'мр', 'жр', 'ср', 'общ',
    'ед', 'мн',
    'sg', 'pl',
    'им', 'рд', 'дт', 'вн', 'тв', 'пр', 'зв',
    '2',
    'имя', 'отч', 'фам',
    'кач', 'порядк', 'притяж', 'мест',
    'сравн', 'прев',
    'гео', 'орг',
    'арх', 'аббр', 'разг', 'жарг', 'опч'
);
unshift @gram_order, keys (%pos_map);
my %gram_ord;
for my $i(0..$#gram_order) {
    $gram_ord{$gram_order[$i]} = $i;
}
unless(DEBUG) {
    $dbh = DBI->connect('DBI:mysql:corpora:127.0.0.1', 'corpora', 'corpora') or die $DBI::errstr;
    $dbh->do("SET NAMES utf8");
    $newset = $dbh->prepare("INSERT INTO `rev_sets` VALUES(NULL, ?, ?)");
    $newset->execute(time(), 0);
    $set_id = $dbh->{'mysql_insertid'};
    $newlemma = $dbh->prepare("INSERT INTO `dict_lemmata` VALUES(NULL, ?)");
    $newrev = $dbh->prepare("INSERT INTO `dict_revisions` VALUES(NULL, ?, ?, ?, '0')"); #null, set, lemma, text, null
}

while(<>) {
    if (/(\S+)\t(\S+),\s?(\S+)?,?\s?(\S+)?/) {
        #print STDERR "form is $1\n";
        my @t = (to_lower($1), $2, $2.','.$3.','.$4);
        push @stack, \@t;
    } else {
        process_stack(\@stack);
        @stack = ();
    }
}

# SUBROUTINES

sub process_stack($) {
    my $aref = shift;
    my $out;
    my $lemma;
    my @t;
    if (my $t = split_lemma($aref)) {
        @t = @$t;
    } else {
        return;
    }
    for my $ref(@t) {
        #print STDERR $ref."\n".join(', ',@$ref)."\n";
        $lemma = get_lemma($ref);
        $out = make_revtext(sort_forms($ref));
        if (DEBUG) {
            print $out."\n";
        } else {
            $newlemma->execute($lemma);
            $newlemma_id = $dbh->{'mysql_insertid'};
            $newrev->execute($set_id, $newlemma_id, $out);
            print STDERR "Committed revision ".$dbh->{'mysql_insertid'}."\r";
        }
    }
}
sub split_lemma($) {
    my $aref = shift;
    my @arr = @$aref;
    my @newarr;
    my $lemma = get_lemma($aref);
    #print STDERR "lemma <$lemma>\n";
    if (!exists $pos_map{$arr[0][1]}) {
        #print STDERR $lemma.": unknown POS\n";
        return 0;
    }
    if (has_diff_pos($aref)) {
        #print STDERR $lemma.": stack has various POS\n";
        # TYPE 12
        if (has_gram($aref, 'П')) {
            my @full = ();
            my @short = ();
            for my $el(@arr) {
                if ($$el[2]=~/КР_ПРИЛ/) {
                    push @short, $el;
                } else {
                    push @full, $el;
                }
            }
            @short = @{split_lemma(\@short)};
            @full = @{split_lemma(\@full)};
            @short = @{$short[0]};
            @full = @{$full[0]};
            return 0 unless exists $ret_types{12};
            return [\@full, \@short];
        }
        return 0;
    }
    my $type = is_special($aref);
    if ($type == 10) {
        for my $el(@arr) {
            my @t = ();
            push @t, 'порядк' if $$el[1] eq 'ЧИСЛ-П';
            push @t, 'мест' if $$el[1] eq 'МС-П';
            for my $g(split /,/, $$el[2]) {
                if ($g eq 'од' || $g eq 'но') {
                    push @t, $g if $$el[2] =~ /,вн/ && $$el[2] =~ /м[рн]/;
                } else {
                    push @t, $g;
                }                    
            }
            push @newarr, [$$el[0], $$el[1], join(',', @t)];
        }
        return 0 unless exists $ret_types{10};
        return [\@newarr];
    }
    if ($type == 11) {
        #print STDERR "$lemma: is PrnAdj-0\n";
        for my $num(qw/ед мн/) {
            for my $gen(qw/мр жр ср/) {
                for my $case(qw/им рд дт вн тв пр/) {
                    my @t;
                    if ($num eq 'ед') {
                        @t = ($lemma, 'МС-П', "МС-П,$case,$gen,$num,мест");
                    } else {
                        @t = ($lemma, 'МС-П', "МС-П,$case,$num,мест");
                    }
                    push @newarr, \@t;
                }
            }
        }
        return 0 unless exists $ret_types{11};
        return [\@newarr];
    }
    if ($type == 1) {
        #print STDERR $lemma.": is a name\n";
        return 0 unless exists $ret_types{1};
        return 0;
    }
    if ($type == 6) {
        #print STDERR $lemma.": is a surname\n";
        my @masc;
        my @fem;
        my @pl;
        for my $el(@arr) {
            if ($$el[2]=~/мр/) {
                $$el[2].=',дфст';
                push @masc, $el;
            }
            elsif ($$el[2]=~/жр/) {
                $$el[2].=',дфст';
                push @fem, $el;
            }
            elsif ($$el[2]=~/мн/) {
                $$el[2].=',pl';
                push @pl, $el;
            }
        }
        return 0 unless exists $ret_types{6};
        return [\@masc, \@fem, \@pl];
    }
    if ($type == 8) {
        #print STDERR $lemma.": is a surname (0)\n";
        my $el = $arr[0];
        $$el[2] =~ s/(0|мр-жр)//;
        for my $num(qw/ед мн/) {
            for my $case(qw/им рд дт вн тв пр/) {
                my @t = ($$el[0], $$el[1], $$el[2].",$num,$case".($num eq 'ед'?',общ':''));
                push @newarr, \@t;
            }
        }
        return 0 unless exists $ret_types{8};
        return [\@newarr];
    }
    if ($type == 9) {
        print STDERR $lemma.": is a surname (with 0)\n";
        for my $el(@arr) {
            push @newarr, $el;
        }
        return 0 unless exists $ret_types{9};
        return [\@newarr];
    }
    if ($type == 2) {
        #print STDERR $lemma.": has common gender\n";
        for my $el(@arr) {
            my @t = ();
            for my $g(split /,/, $$el[2]) {
                if ($g eq 'мр-жр') {
                    push @t, 'мр' if $$el[2] !~ /мн/;
                    push @t, 'общ';
                } else {
                    push @t, $g;
                }
            }
            my @t1 = ($$el[0], $$el[1], join(',',@t));
            push @newarr, \@t1;
            if ($$el[2] !~ /мн/) {
                my @t = ();
                for my $g(split /,/, $$el[2]) {
                    if ($g eq 'мр-жр') {
                        push @t, 'жр';
                        push @t, 'общ';
                    } else {
                        push @t, $g;
                    }
                }
                my @t1 = ($$el[0], $$el[1], join(',',@t));
                push @newarr, \@t1;
            }
        }
        return 0 unless exists $ret_types{2};
        #return [\@newarr];
    }
    if ($type == 7) {
        #print STDERR $lemma.": is type 0 (abbr)\n";
        return 0 unless exists $ret_types{7};
        return 0;
    }
    if ($type == 3) {
        #print STDERR $lemma.": is type 0\n";
        return 0 unless exists $ret_types{3};
        return 0;
    }
    if ($type == 5) {
        #print STDERR $lemma.": is type 0 (bad)\n";
        return 0 unless exists $ret_types{5};
        return 0;
    }
    if ($type == 4) {
        #print STDERR $lemma.": is pl tm\n";
        my $mn;
        for my $el(@arr) {
            my @t = ();
            $mn = 0;
            for my $g(split /,/, $$el[2]) {
                if ($g eq 'мн') {
                    push @t, $g if !$mn;
                    $mn = 1;
                } else {
                    push @t, $g;
                }
            }
            push @t, 'pl';
            my @t1 = ($$el[0], $$el[1], join(',',@t));
            push @newarr, \@t1;
        }
        return 0 unless exists $ret_types{4};
        return [\@newarr];
    }
    # TYPE 0
    for my $el(@arr) {
        push @newarr, $el;
    }
    return 0 unless exists $ret_types{0};
    return [\@newarr];
}
sub make_revtext($) {
    my $aref = shift;
    my @arr = @$aref;
    my @t, my @g;
    my $lemma = get_lemma($aref);
    my $text = "<dict_rev><lemma text=\"$lemma\"/>".(DEBUG?"\n":'');
    for my $el(@arr) {
        @t = @$el;
        @g = @{$t[2]};
        $text .= (DEBUG?'    ':'')."<form text=\"".$t[0]."\">";
        for my $g(@g) {
            $text .= "<grm val=\"".$gram_map{$g}."\"/>";
        }
        $text .= '</form>'.(DEBUG?"\n":'');
    }
    $text .= "</dict_rev>";
    return $text;
}
sub has_gram($$) {
    my $aref = shift;
    my $search = shift;
    for my $el(@$aref) {
        for my $g(split /,/, $$el[2]) {
            if ($g eq $search) {
                return 1;
            }
        }
    }
    return 0;
}
sub has_gram2($$) {
    my $aref = shift;
    my $search = shift;
    my $flag;
    for my $el(@$aref) {
        $flag = 0;
        for my $g(split /,/, $$el[2]) {
            if ($g eq $search) {
                return 1 if $flag;
                $flag = 1;
            }
        }
    }
    return 0;
}
sub is_special($) {
    my $aref = shift;
    if (has_gram($aref, 'имя') || has_gram($aref, 'отч')) {
        return 1;
    }
    if (has_gram($aref, 'фам')) {
        if (scalar @$aref == 1) {
            return 8;
        }
        if (has_gram($aref, '0')) {
            return 9;
        }
        return 6;
    }
    if (has_gram ($aref, 'мр-жр')) {
        return 2;
    }
    if (has_gram($aref, '0')) {
        if (has_gram($aref, 'МС-П')) {
            return 11;
        }
        if (scalar @$aref > 1) {
            if (has_gram($aref, 'аббр')) {
                return 7;
            } else {
                return 5;
            }
        } else {
            return 3;
        }
    }
    if (has_gram($aref, 'П') || has_gram($aref, 'МС-П') || has_gram($aref, 'ЧИСЛ-П') || has_gram($aref, 'КР_ПРИЛ')) {
        return 10;
    }
    if (has_gram2($aref, 'мн')) {
        return 4;
    }
    return 0;
}
sub has_diff_pos($) {
    my $aref = shift;
    my @arr = @$aref;
    my %pos = ();
    for my $el(@$aref) {
        $pos{$$el[1]} = 1;
        return 1 if (scalar(keys %pos) > 1);
    }
    return 0;
}
sub get_lemma($) {
    #suppose the first form is the lemma
    my @arr = @{shift()};
    return $arr[0][0];
}
sub to_lower($) {
    my $s = shift;
    $s =~ tr/[А-ЯЁ]/[а-яё]/;
    return $s;
}
sub sort_gram {
    $gram_ord{$gram_map{$a}} <=> $gram_ord{$gram_map{$b}}
}
sub sort_forms($) {
    my @arr = @{shift()};
    my @t;
    my @newarr;
    my %uniq, my $hash;
    for my $el(@arr) {
        @t = @$el;
        my @newgram;
        for my $gr(split /,/, $t[2]) {
            if ($gr !~ /^\s*$/) {
                if (!exists $gram_map{$gr}) {
                    print STDERR "unknown grammem: <$gr>\n" if DEBUG;
                } else {
                    push @newgram, $gr;
                }
            }
        }
        @newgram = sort sort_gram @newgram;
        $hash = $t[0].' '.join(',', @newgram);
        if (exists $uniq{$hash}) {
            #print STDERR "repeat: $hash\n";
        } else {
            $uniq{$hash} = 1;
            push @newarr, [$t[0], $t[1], \@newgram];
        }
    }
    return \@newarr;
}
