use XML::Parser;
use utf8;

use strict;

my %ini;
my ($input_file, $ini_file) = @ARGV;
if (!$input_file || !$ini_file) {
    die "Usage: perl convert.pl XML_FILE INI_FILE\n";
}
my $noclose = 0;
my %our_pos = (
    с => 'сущ',
    п => 'прил',
    мс => 'мест_сущ',
    г => 'гл',
    причастие => 'гл',
    деепричастие => 'гл',
    инфинитив => 'гл',
    'мс-предк' => 'мест_предк',
    'мс-п' => 'мест_прил',
    числ => 'числ',
    'числ-п' => 'числ_пор',
    н => 'нареч',
    предк => 'предк',
    предл => 'предл',
    союз => 'союз',
    межд => 'межд',
    част => 'част',
    вводн => 'вводн',
    кр_прил => 'прил',
    кр_причастие => 'гл'
);
my %our_gram = (
    мр => 'р=м',
    жр => 'р=ж',
    ср => 'р=с',
    'мр-жр' => 'р=о',
    ед => 'ч=ед',
    мн => 'ч=мн',
    им => 'п=им',
    рд => 'п=рд',
    дт => 'п=дт',
    вн => 'п=вн',
    тв => 'п=тв',
    пр => 'п=пр',
    зв => 'п=зв',
    нст => 'в=н',
    прш => 'в=п',
    буд => 'в=б',
    '1л' => 'л=1',
    '2л' => 'л=2',
    '3л' => 'л=3',
    дст => 'з=д',
    стр => 'з=с',
    пвл => 'н=п',
    сравн => 'сс=да'
);
my @null_gram = qw /од но св нс пе нп 0 кр имя фам отч лок орг кач вопр относ дфст опч жарг арх проф аббр безл указат/;
my %null_gram;
$null_gram{$_}=1 for (@null_gram);

my $parser = new XML::Parser(Handlers => {Start => \&start_tag, End => \&end_tag});
binmode (STDOUT, 'utf8');
parse_ini ($ini_file);
$parser -> parsefile($input_file);

sub start_tag {
    my ($xml_obj, $tag_name, %attr) = @_;
    if ($tag_name eq 'sentence') {
        my $text = $attr{'text'};
        $text =~ s/\s([\.,:?])/$1/g;
        $text =~ s/\|/\{\{!\}\}/g;
        print "{{Пр|$ini{opus}|".$text."|\n";
    }
    elsif ($tag_name eq 'clause') {
        print "{{Кл|\n";
    }
    elsif ($tag_name eq 'synvar') {
        print "{{СинВар|";
    }
    elsif ($tag_name eq 'word') {
        if ($attr{'lemma'}=~ /^(\.|:|,)$/) {
            print $attr{'lemma'}.' ';
            $noclose = 1;
            return;
        }
        my $pos = lc $attr{'pos'};
        my $gram = $attr{'grm'};
        $gram =~ s/;\s*$//;
        my @gram = split /,/, $gram;
        printf "{{Слово|форма=%s|лемма=%s", $attr{'form'}, lc $attr{'lemma'};
        print "|чр=$our_pos{$pos}";
        for (@gram) {
            next if exists $null_gram{$_};
            if (exists $our_gram{$_}) {
                print "|$our_gram{$_}";
            } else {
                print STDERR "unknown grammem $_\n";
                die "aborted: unknown grammem (see STDERR)\n";
            }
        }
        #additions for verbal forms
        if ($pos eq 'г') {
            print "|г=л";
        } elsif ($pos eq 'причастие' || $pos eq 'кр_причастие') {
            print "|г=прич";
        } elsif ($pos eq 'деепричастие') {
            print "|г=деепр";
        } elsif ($pos eq 'инфинитив') {
            print "|г=инф";
        }
        #additions for short adj & part
        if ($pos =~ /^кр_/) {
            print "|кр=да";
        }
    }
}
sub end_tag {
    my ($xml_obj, $tag_name, %attr) = @_;
    if ($tag_name eq 'sentence') {
        print "}}\n";
    }
    elsif ($tag_name eq 'clause') {
        print "}}\n";
    }
    elsif ($tag_name eq 'synvar') {
        print " }}\n";
    }
    elsif ($tag_name eq 'word') {
        if ($noclose) {
            $noclose = 0;
        } else {
            print "}}";
        }
    }
}
sub parse_ini {
    my $file = shift;
    open my $f,"<$file";
    binmode ($f, 'utf8');
    while (<$f>) {
        s/[\n\r]//g;
        if (/^\W*(\w+)\s*=\s*(.+)\s*$/) {
            $ini{$1} = $2;
        }
    }
    close $f;
}