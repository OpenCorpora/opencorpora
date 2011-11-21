#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;
use Config::INI::Reader;

binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
my $root_path = $conf->{project}{root};
my $mysql     = $conf->{mysql};

my $dbh = DBI->connect('DBI:mysql:'.$mysql->{'dbname'}.':'.$mysql->{'host'}, $mysql->{'user'}, $mysql->{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $sent = $dbh->prepare("SELECT `sent_id`, `source` FROM sentences WHERE sent_id NOT IN(?)");
my $tok = $dbh->prepare("SELECT tf_id, tf_text FROM text_forms WHERE sent_id=? ORDER BY `pos`");
my $drop = $dbh->prepare("TRUNCATE TABLE `tokenizer_coeff`");
my $drop2 = $dbh->prepare("TRUNCATE TABLE `tokenizer_strange`");
my $insert = $dbh->prepare("INSERT INTO `tokenizer_coeff` VALUES(?,?)");
my $ins2 = $dbh->prepare("INSERT INTO `tokenizer_strange` VALUES(?,?,?,?)");
my $check = $dbh->prepare("SELECT lemma_id FROM form2lemma WHERE form_text=? LIMIT 1");
my $stat = $dbh->prepare("INSERT INTO stats_values VALUES(?,'7',?)");
my $broken_token = $dbh->prepare("INSERT INTO stats_values VALUES(?,'28',?)");
my $drop_broken = $dbh->prepare("DELETE FROM stats_values WHERE param_id=28");

my $str;
my @tokens;
my %border;
my %total;
my %good;
my $vector;
my $pos;
my %strange;
my %exceptions;
my %prefixes;
my %bad_sentences;

my @thresholds = qw/0.0 0.01 0.05 0.10 0.15 0.20 0.25 0.30 0.35 0.40 0.45 0.50 0.55 0.60 0.65 0.70 0.75 0.80 0.85 0.90 0.95 0.99 1.0/;
my %vector2coeff;
my %stats_correct;
my %stats_falsepos;
my %stats_total;

my $stat_sure, my $stat_total;


#first pass
read_instances("$root_path/scripts/lists/tokenizer_exceptions.txt", \%exceptions);
read_instances("$root_path/scripts/lists/tokenizer_prefixes.txt", \%prefixes);
read_instances("$root_path/scripts/tokenizer/bad_sentences.txt", \%bad_sentences);
$sent->execute(join(',', keys %bad_sentences));
while(my $ref = $sent->fetchrow_hashref()) {
    $str = decode('utf8', $ref->{'source'}).'  ';
    @tokens = ();
    $tok->execute($ref->{'sent_id'});
    #print STDERR $ref->{'sent_id'}."\n";
    while(my $r = $tok->fetchrow_hashref()) {
        push @tokens, [$r->{'tf_id'}, decode('utf8', $r->{'tf_text'})];
    }

    $pos = 0;
    %border = ();
    for my $token(@tokens) {
        while(substr($str, $pos, length($token->[1])) ne $token->[1]) {
            $pos++;
            if ($pos > length($str)) {
                $broken_token->execute(time(), $token->[0]);
                printf STDERR "Too long, sentence %d, failed token is <%s>\n",
                    $ref->{'sent_id'}, $token->[1];
                exit;
            }
        }
        my $t = $pos + length($token->[1]) - 1;
        $border{$t} = 1;
        $pos += length($token->[1]);
    }

    for my $i(0..length($str)-1) {
        $vector = oct('0b'.join('', @{calc($str, $i)}));
        #print $i.' <'.substr($str, $i, 1).'> '.$vector."\n";
        $total{$vector}++;
        $good{$vector}++ if exists $border{$i} ? 1 : 0;
    }
}

my $coef;
$drop->execute();
for my $k(sort {$a <=> $b} keys %total) {
    $coef = $good{$k}/$total{$k};
    $vector2coeff{$k} = $coef;
    printf("%6s\t%.3f\t%d\t%017s\n", $k, $coef, $total{$k}, sprintf("%b",$k));


    #how strange it is
    if (0 < $coef && $coef < 1) {
        $strange{$k.'#'.($coef > 0.5 ? '0' : '1')} = [$coef > 0.5 ? $coef : 1-$coef, $total{$k}];
    } else {
        $stat_sure += $total{$k};
    }
    $stat_total += $total{$k};
    $insert->execute($k, $coef);
}
printf "Total %d different vectors; predictor is sure in %.3f%% cases\n", scalar(keys %total), $stat_sure/$stat_total * 100;
$stat->execute(time(), int($stat_sure/$stat_total * 100000));

#second pass
$drop2->execute();
$sent->execute(join(',', keys %bad_sentences));
while(my $ref = $sent->fetchrow_hashref()) {
    $str = decode('utf8', $ref->{'source'}).'  ';
    @tokens = ();
    $tok->execute($ref->{'sent_id'});
    while(my $r = $tok->fetchrow_hashref()) {
        push @tokens, [$r->{'tf_id'}, decode('utf8', $r->{'tf_text'})];
    }

    $pos = 0;
    %border = ();
    for my $token(@tokens) {
        while(substr($str, $pos, length($token->[1])) ne $token->[1]) {
            $pos++;
            if ($pos > length($str)) {
                $broken_token->execute(time(), $token->[0]);
                die "Too long";
            }
        }
        my $t = $pos + length($token->[1]) - 1;
        $border{$t} = 1;
        $pos += length($token->[1]);
    }
    $drop_broken->execute();

    for my $i(0..length($str)-1) {
        my $s = calc($str, $i);
        $vector = oct('0b'.join('', @$s));
        my $flag_space = 0; # whether the following character is a space
        if ($$s[4] == 0 && $$s[5] == 0 && $$s[6] == 1 && $$s[7] == 0) {
            $flag_space = 1;
        }

        #to calculate Pre and Rec
        for my $t(@thresholds) {
            if (exists $border{$i}) {
                if ($vector2coeff{$vector} >= $t) {
                    $stats_correct{0}{$t}++;
                    $stats_correct{1}{$t}++ unless $flag_space;
                }
            }
            elsif ($vector2coeff{$vector} >= $t) {
                $stats_falsepos{0}{$t}++;
                $stats_falsepos{1}{$t}++ unless $flag_space;
            }
        }
        if (exists $border{$i}) {
            $stats_total{0}++;
            $stats_total{1}++ unless $flag_space;
        }

        my $q = $vector.'#'.(exists $border{$i} ? 1 : 0);
        if (exists $strange{$q}) {
            $ins2->execute($ref->{'sent_id'}, $i, (exists $border{$i} ? 1 : 0), $strange{$q}->[0]);
        }
    }
}

for my $k(sort {$strange{$b}->[1] <=> $strange{$a}->[1]} keys %strange) {
    printf "%s\t%.3f\t%d\n", $k, $strange{$k}[0], $strange{$k}[1];
}

for my $type(0..1) {
    for my $t(@thresholds) {
        my $pr = $stats_correct{$type}{$t}/($stats_correct{$type}{$t} + $stats_falsepos{$type}{$t});
        my $re = $stats_correct{$type}{$t}/$stats_total{$type};
        printf "Threshold: %.2f, total borders: %8s, correct: %8s, false pos: %8s, precision: %.2f%%, recall: %.2f%%, F1: %.2f%%\n",
            $t, $stats_total{$type}, $stats_correct{$type}{$t}, int($stats_falsepos{$type}{$t}), 100*$pr, 100*$re, 50*($pr + $re);
    }
    print "\n";
}

# subroutines

sub calc {
    my $str = shift;
    my $i = shift;

    my $previous = ($i > 0 ? substr($str, $i-1, 1) : '');
    my $current = substr($str, $i, 1);
    my $next = substr($str, $i+1, 1);
    my $nnext = substr($str, $i+2, 1);

    # $chain is the current hyphenated word which we will perhaps need to check in the dictionary
    my $chain = '';
    my $chain_left = '';
    my $chain_right = '';
    my $odd_symbol = '';
    if (is_hyphen($current) || is_hyphen($next)) {
        $odd_symbol = '-';
    }
    elsif ($current =~ /([\.\/\?\=\:&"!\+\(\)])/ || $next =~ /([\.\/\?\=\:&"!\+\(\)])/) {
        $odd_symbol = $1;
    }
    if ($odd_symbol ne '') {
        my $t;
        for (my $j = $i; $j >= 0; --$j) {
            $t = substr($str, $j, 1);
            if (($odd_symbol eq '-' && (is_cyr($t) || is_hyphen($t) || $t eq "'")) ||
                ($odd_symbol ne '-' && !is_space($t))) {
                $chain_left = $t.$chain_left;
            } else {
                last;
            }
            $chain_left =~ s/\Q$odd_symbol\E$//;
        }
        for (my $j = $i+1; $j < length($str); ++$j) {
            $t = substr($str, $j, 1);
            if (($odd_symbol eq '-' && (is_cyr($t) || is_hyphen($t) || $t eq "'")) ||
                ($odd_symbol ne '-' && !is_space($t))) {
                $chain_right .= $t;
            } else {
                last;
            }
            $chain_right =~ s/^\Q$odd_symbol\E//;
        }
        $chain = $chain_left.$odd_symbol.$chain_right;
        #print "left <$chain_left>, right <$chain_right>, full <$chain>\n";
    }
    #print "prev=<$previous>, current=<$current>, next=<$next>, nnext=<$nnext>, odds=<$odd_symbol>\n";

    my @out = ();
    push @out, char_class($current);
    push @out, char_class($next);
    push @out, is_number($previous);
    push @out, is_number($nnext);
    push @out, $odd_symbol eq '-' ? is_dict_chain($chain) : 0;
    push @out, $odd_symbol eq '-' ? is_suffix($chain_right) : 0;
    push @out, is_same_pm($current, $next);
    push @out, ($odd_symbol && $odd_symbol ne '-') ? looks_like_url($chain, $chain_right): 0;
    push @out, ($odd_symbol && $odd_symbol ne '-') ? is_exception($chain): 0;
    push @out, ($odd_symbol eq '-') ? is_prefix($chain_left) : 0;
    push @out, ($odd_symbol eq ':' && $chain_right ne '') ? looks_like_time($chain_left, $chain_right) : 0;

    #print "will return out = ".join('', @out)."\n";

    return \@out;
}
sub char_class {
    my $char = shift;
    my $ret = 
        is_cyr($char)          ? '0001' :
        is_space($char)        ? '0010' :
        is_dot($char)          ? '0011' :
        is_pmark($char)        ? '0100' :
        is_hyphen($char)       ? '0101' :
        is_number($char)       ? '0110' :
        is_latin($char)        ? '0111' :
        is_bracket1($char)     ? '1000' :
        is_bracket2($char)     ? '1001' :
        is_single_quote($char) ? '1010' :
        is_slash($char)        ? '1011' :
        is_colon($char)        ? '1100' : '0000';
    return split //, $ret;
}
sub is_pmark {
    my $char = shift;
    if ($char =~ /^[,\?!";\xAB\xBB]$/) {
        return 1;
    }
    return 0;
}
sub is_space {
    my $char = shift;
    if ($char =~ /^\s$/) {
        return 1;
    }
    return 0;
}
sub is_latin {
    my $char = shift;
    if ($char =~ /^[A-Za-z]$/) {
        return 1;
    }
    return 0;
}
sub is_cyr {
    my $char = shift;
    if ($char =~ /^[А-Яа-яЁё]$/) {
        return 1;
    }
    return 0;
}
sub is_hyphen {
    my $char = shift;
    return $char eq '-' ? 1 : 0;
}
sub is_dot {
    my $char = shift;
    return $char eq '.' ? 1 : 0;
}
sub is_single_quote {
    my $char = shift;
    return $char eq "'" ? 1 : 0;
}
sub is_slash {
    my $char = shift;
    return $char eq '/' ? 1 : 0;
}
sub is_colon {
    my $char = shift;
    return $char eq ':' ? 1 : 0;
}
sub is_number {
    my $char = shift;
    if ($char =~ /^\d$/) {
        return 1;
    }
    return 0;
}
sub is_bracket1 {
    my $char = shift;
    if ($char =~ /^[\(\[\{\<]$/) {
        return 1;
    }
    return 0;
}
sub is_bracket2 {
    my $char = shift;
    if ($char =~ /^[\)\]\}\>]$/) {
        return 1;
    }
    return 0;
}
sub is_dict_chain {
    my $chain = shift;

    if (!$chain || $chain =~ /^\-/) {
        return 0;
    }

    $check->execute(lc($chain));
    if ($check->fetchrow_hashref()) {
        return 1;
    }
    return 0;
}
sub is_suffix {
    my $s = shift;
    if ($s eq 'то' || $s eq 'таки' || $s eq 'с' || $s eq 'ка' || $s eq 'де') {
        return 1;
    }
    return 0;
}
sub is_same_pm {
    return $_[0] eq $_[1] ? 1 : 0;
}
sub looks_like_url {
    my $s = shift;
    my $suffix = shift;
    return 0 if $suffix eq '';
    return 0 if $s =~ /^\./;
    return 0 if length($s) < 5; 
    if ($s =~ /^\W*https?\:\/\// || $s =~ /^\W*www\./ || $s =~/.\.(?:[a-z]{2,3}|ру|рф)\W*$/i) {
        return 1;
    }
    return 0;
}
sub looks_like_time {
    my $left = shift;
    my $right = shift;

    $left =~ s/^\D+//;
    $right =~ s/\D+$//;

    unless ($left =~ /^\d\d?$/ && $right =~ /^\d\d$/) {
        return 0;
    }

    if ($left < 24 && $right < 60) {
        return 1;
    }

    return 0;
}
sub is_exception {
    my $s = lc(shift());
    return 1 if exists $exceptions{$s};
    if ($s !~ /^\W|\W$/) {
        return 0;
    }
    $s =~ s/^\W+//;
    return 1 if exists $exceptions{$s};
    while ($s =~ s/\W$//) {
        return 1 if exists $exceptions{$s};
    }
    return 0;
}
sub is_prefix {
    my $s = shift;
    return exists $prefixes{$s} ? 1 : 0;
}
sub read_instances {
    open F, $_[0] or warn "Failed to open $_[0]: $!";
    binmode(F, ':encoding(utf8)');
    while(<F>) {
        next unless /\S/;
        next if /^\s*#/;
        chomp;
        $_[1]->{lc($_)} = 1;
    }
    close F;
}
