#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

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
my $drop = $dbh->prepare("DELETE FROM `tokenizer_learn_data`");
my $insert = $dbh->prepare("INSERT INTO `tokenizer_learn_data` VALUES(CONV(?,2,10), ?)");

my $str;
my @tokens;
my $pos;
my %border;
my @vector;

$sent->execute();
$drop->execute();
while(my $ref = $sent->fetchrow_hashref()) {
    $str = decode('utf8', $ref->{'source'});
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

    for my $i(0..length($str)-2) {
        @vector = @{calc($str, $i)};
        $pos = exists $border{$i} ? 1 : 0;
        $insert->execute(join('', @vector), $pos);
    }
}

# subroutines

sub calc {
    my $str = shift;
    my $i = shift;

    my @out = ();
    push @out, F1($str, $i);
    push @out, F2($str, $i);
    push @out, F3($str, $i);
    push @out, F4($str, $i);
    push @out, F5($str, $i);

    return \@out;
}
sub F1 {
    my ($str, $i) = @_;
    my $char = substr($str, $i, 1);
    if ($char =~ /[А-ЯЁа-яё]/) {
        return 1;
    }
    return 0;
}
sub F2 {
    my ($str, $i) = @_;
    my $char = substr($str, $i+1, 1);
    return 1 if $char eq ' ';
    return 0;
}
sub F3 {
    my ($str, $i) = @_;
    my $char = substr($str, $i+1, 1);
    return is_pmark($char);
}
sub F4 {
    my ($str, $i) = @_;
    my $char = substr($str, $i, 1);
    return is_pmark($char);
}
sub F5 {
    my ($str, $i) = @_;
    my $char = substr($str, $i, 1);
    if ($char =~ /[A-Za-z]/) {
        return 1;
    }
    return 0;
}
sub is_pmark {
    my $char = shift;
    if ($char =~ /^[\.,\?!"\(\)\:;\[\]\/\xAB\xBB]$/) {
        return 1;
    }
    return 0;
}
