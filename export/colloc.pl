#!/usr/bin/perl

use strict;
use utf8;
use Getopt::Std;

binmode(STDOUT, ':encoding(utf8)');

my %unigram_freq;
my %main_dict;
my $sum_freq;
my %options;
getopts('b:m:tu:', \%options);
# b - file with bigram frequencies
# m - metric to use (currently only MI)
# t - use threshold (4th root for MI)
# u - file with unigram frequencies

if (!$options{'m'} || !$options{'b'} || !$options{'u'}) {
    print STDERR "Usage: $0 -m MI -u unigram_freq_file -b bigram_freq_file\n";
    exit;
}
if (!-f $options{'b'} || !-f $options{'u'}) {
    printf STDERR "%s or %s is not a file!\n", $options{'b'}, $options{'u'};
    exit;
}

open F, $options{'u'} or die "Error opening unigram file: $!";
binmode(F, ':encoding(utf8)');
while(<F>) {
    chomp;
    my ($token, $abs, $ipm) = split /\t/;
    $sum_freq += $abs;
    $unigram_freq{$token} = [$abs, $ipm];
}
close F;

my $MI_freq_threshold = $sum_freq ** 0.25;

open F, $options{'b'} or die "Error opening bigram file: $!";
binmode(F, ':encoding(utf8)');
while(<F>) {
    chomp;
    my ($bigram, $abs) = split /\t/;
    my ($n, $c) = split / /, $bigram;

    if ($options{'m'} eq 'MI') {
        if ($options{'t'} && $abs < $MI_freq_threshold) {
            next;
        }
        $main_dict{$bigram} = [$abs, MI($unigram_freq{$n}[0], $unigram_freq{$c}[0], $abs, $sum_freq)];
    }
}
close F;

for my $colloc(sort {$main_dict{$b}->[1] <=> $main_dict{$a}->[1]} keys %main_dict) {
    my ($part1, $part2) = split / /, $colloc;
    printf "%s\t%s\t%d\t%d\t%d\t%.3f\n", $part1, $part2, $unigram_freq{$part1}[1], $unigram_freq{$part2}[1], $main_dict{$colloc}->[0], $main_dict{$colloc}->[1];
}

# subroutines

sub log2 {
    return log($_[0])/log(2);
}
sub MI {
    my ($n, $c, $nc, $N) = @_;
    return log2($nc * $N / ($n * $c));
}
