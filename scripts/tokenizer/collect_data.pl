#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

binmode(STDOUT, ':utf8');
binmode(STDERR, ':utf8');

#reading config
my %mysql;
open F, $ARGV[0] or die "Failed to open $ARGV[0]";
while(<F>) {
    if (/\$config\['mysql_(\w+)'\]\s*=\s*'([^']+)'/) {
        $mysql{$1} = $2;
    }
}
close F;

my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $sent = $dbh->prepare("SELECT `sent_id`, `source` FROM sentences");
my $tok = $dbh->prepare("SELECT tf_text FROM text_forms WHERE sent_id=? ORDER BY `pos`");
my $drop = $dbh->prepare("DELETE FROM `tokenizer_coeff`");
my $drop2 = $dbh->prepare("DELETE FROM `tokenizer_strange`");
my $insert = $dbh->prepare("INSERT INTO `tokenizer_coeff` VALUES(?,?)");
my $ins2 = $dbh->prepare("INSERT INTO `tokenizer_strange` VALUES(?,?,?,?)");
my $check = $dbh->prepare("SELECT lemma_id FROM form2lemma WHERE form_text=? LIMIT 1");
my $stat = $dbh->prepare("INSERT INTO stats_values VALUES(?,'7',?)");

my $str;
my @tokens;
my %border;
my %total;
my %good;
my $vector;
my $pos;
my %strange;

my $stat_sure, my $stat_total;

$drop->execute();

#first pass
$sent->execute();
while(my $ref = $sent->fetchrow_hashref()) {
    $str = decode('utf8', $ref->{'source'}).'  ';
    @tokens = ();
    $tok->execute($ref->{'sent_id'});
    #print STDERR $ref->{'sent_id'}."\n";
    while(my $r = $tok->fetchrow_hashref()) {
        push @tokens, decode('utf8', $r->{'tf_text'});
    }

    $pos = 0;
    %border = ();
    for my $token(@tokens) {
        while(substr($str, $pos, length($token)) ne $token) {
            $pos++;
            if ($pos > length($str)) {
                die "Too long";
            }
        }
        my $t = $pos + length($token) - 1;
        $border{$t} = 1;
        $pos += length($token);
    }

    for my $i(0..length($str)-1) {
        $vector = oct('0b'.join('', @{calc($str, $i)}));
        $total{$vector}++;
        $good{$vector}++ if exists $border{$i} ? 1 : 0;
    }
}

my $coef;
for my $k(sort {$a <=> $b} keys %total) {
    $coef = $good{$k}/$total{$k};
    printf("%d\t%.3f\t%d\t%025s\n", $k, $coef, $total{$k}, sprintf("%b",$k));

    #how strange it is
    if (0 < $coef && $coef < 1) {
        $strange{$k.'#'.($coef > 0.5 ? '0' : '1')} = $coef > 0.5 ? $coef : 1-$coef;
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
$sent->execute();
while(my $ref = $sent->fetchrow_hashref()) {
    $str = decode('utf8', $ref->{'source'}).'  ';
    @tokens = ();
    $tok->execute($ref->{'sent_id'});
    while(my $r = $tok->fetchrow_hashref()) {
        push @tokens, decode('utf8', $r->{'tf_text'});
    }

    $pos = 0;
    %border = ();
    for my $token(@tokens) {
        while(substr($str, $pos, length($token)) ne $token) {
            $pos++;
            if ($pos > length($str)) {
                die "Too long";
            }
        }
        my $t = $pos + length($token) - 1;
        $border{$t} = 1;
        $pos += length($token);
    }

    for my $i(0..length($str)-1) {
        $vector = oct('0b'.join('', @{calc($str, $i)}));
        my $q = $vector.'#'.(exists $border{$i} ? 1 : 0);
        if (exists $strange{$q}) {
            #printf STDERR "strange: sentence %d, position %d, coef %.3f\n",
            #    $ref->{'sent_id'}, $i, $strange{$q};
            $ins2->execute($ref->{'sent_id'}, $i, (exists $border{$i} ? 1 : 0), $strange{$q});
        }
    }
}

#for my $k(sort {$b <=> $a} keys %strange) {
#    printf "%s\t%d\t%d\n", $k, $strange{$k}[0], $strange{$k}[1];
#}

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
    if (is_hyphen($next) || is_hyphen($current)) {
        my $t;
        for (my $j = $i; $j >= 0; --$j) {
            $t = substr($str, $j, 1);
            if (is_cyr($t) || is_hyphen($t) || $t eq "'") {
                $chain_left = $t.$chain_left;
            } else {
                last;
            }
            $chain_left =~ s/\-$//;
        }
        for (my $j = $i+1; $j < length($str); ++$j) {
            $t = substr($str, $j, 1);
            if (is_cyr($t) || is_hyphen($t) || $t eq "'") {
                $chain_right .= $t;
            } else {
                last;
            }
            $chain_right =~ s/^\-//;
        }
        $chain = $chain_left.'-'.$chain_right;
        #print "left <$chain_left>, right <$chain_right>, full <$chain>\n";
    }

    my @out = ();
    push @out, is_space($current);
    push @out, is_space($next);
    push @out, is_pmark($current);
    push @out, is_pmark($next);
    push @out, is_latin($current);
    push @out, is_latin($next);
    push @out, is_cyr($current);
    push @out, is_cyr($next);
    push @out, is_hyphen($current);
    push @out, is_hyphen($next);
    push @out, is_number($previous);
    push @out, is_number($current);
    push @out, is_number($next);
    push @out, is_number($nnext);
    push @out, is_dict_chain($chain);
    push @out, is_dot($current);
    push @out, is_dot($next);
    push @out, is_bracket1($current);
    push @out, is_bracket1($next);
    push @out, is_bracket2($current);
    push @out, is_bracket2($next);
    push @out, is_single_quote($current);
    push @out, is_single_quote($next);
    push @out, is_suffix($chain_right);
    push @out, is_same_pm($current, $next);

    return \@out;
}
sub is_pmark {
    my $char = shift;
    if ($char =~ /^[,\?!"\:;\/\xAB\xBB]$/) {
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
    if ($s eq 'то') {
        return 1;
    }
    return 0;
}
sub is_same_pm {
    return $_[0] eq $_[1] ? 1 : 0;
}
