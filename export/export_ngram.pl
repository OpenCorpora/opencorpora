#!/usr/bin/perl

use strict;
use utf8;
use XML::Parser;
use Getopt::Std;

binmode(STDIN, ':encoding(utf8)');
binmode(STDOUT, ':encoding(utf8)');

my %options;
my %dict;
my $unit_counter = 0;
my @context = ();

getopts('Ccf:iln:opsL', \%options);
# C - count only n-grams having at least one cyrillic letter in each token
# c - skip tokens without cyrillic symbols
# f - path to xml with annotation
# i - show ipm (items per million) as well as absolute frequency
# l - whether we should lowercase
# n - make n-grams
# o - ignore order of terms
# p - show kind of a progress bar
# s - whether we should include sentence borders as tokens
# L - take first lemma instead of form

if (!$options{'n'}) {
    $options{'n'} = 1;
    print STDERR "-n argument not specified, presume 1\n";
}
if (!$options{'f'}) {
    print STDERR "-f argument not specified, presume standard input as file\n";
}

# xml parsing

my $parser = XML::Parser->new(Handlers=> {Start => \&tag_start, End => \&tag_end});
if ($options{'f'}) {
    $parser->parsefile($options{'f'});
} else {
    $parser->parse(*STDIN);
}
if ($options{'p'}) {
    print STDERR "\n";
}

# output
for my $k(sort {$dict{$b} <=> $dict{$a}} keys %dict) {
    if ($options{'i'}) {
        printf "%s\t%d\t%d\n", $k, $dict{$k}, $dict{$k} / $unit_counter * 1000000;
    } else {
        printf "%s\t%d\n", $k, $dict{$k};
    }
}

# subroutines

sub tag_start {
    my ($expat, $tag_name, %attr) = @_;
    
    if ($tag_name eq 'token' && !exists($options{'L'})) {
        if ($options{'c'} && $attr{'text'} !~ /[А-ЯЁа-яё0-9]/) {
            return;
        }

        my $tt = $options{'l'} ? to_lower($attr{'text'}) : $attr{'text'};
        $tt =~ tr/[Ёё]/[Ее]/;
        if ($options{'n'} == 1) {
            $dict{$tt}++;
            $unit_counter++;
            if ($options{'p'} && $unit_counter % 10000 == 0) {
                print STDERR '.';
            }
            return;
        }

        push @context, $tt;
        flush_buffer(\@context);
    } elsif ($tag_name eq 'l' && exists($options{'L'})) {
        if ($options{'c'} && $attr{'t'} !~ /[А-ЯЁа-яё0-9]/) {
            return;
        }

        my $tt = $options{'l'} ? to_lower($attr{'t'}) : $attr{'t'};
        $tt =~ tr/[Ёё]/[Ее]/;
        if ($options{'n'} == 1) {
            $dict{$tt}++;
            $unit_counter++;
            if ($options{'p'} && $unit_counter % 10000 == 0) {
                print STDERR '.';
            }
            return;
        }

        push @context, $tt;
        flush_buffer(\@context);
    }
    elsif ($tag_name eq 'sentence' && $options{'s'}) {
        push @context, '@border@';
    }
}
sub tag_end {
    my ($expat, $tag_name) = @_;

    if ($tag_name eq 'sentence') {
        if ($options{'s'}) {
            push @context, '@border@';
            flush_buffer(\@context);
        }
        @context = ();
    }
}

sub to_lower {
    my $s = lc(shift);
    $s =~ tr/А-ЯЁ/а-яё/;
    return $s;
}

sub flush_buffer {
    my $aref = shift;

    #the context may be yet too short
    if (scalar @$aref < $options{'n'}) {
        return;
    }

    #perhaps (if -c is set) we shouldn't include this n-gram if there are non-cyr tokens
    if ($options{'C'}) {
        for my $t(@$aref) {
            if ($t !~ /[А-ЯЁа-яё0-9]/ && $t ne '@border@') {
                shift @$aref;
                return;
            }
        }
    }
    
    if ($options{'o'}) {
        $dict{join(' ', sort @$aref)}++;
    } else {
        $dict{join(' ', @$aref)}++;
    }

    $unit_counter++;
    if ($options{'p'} && $unit_counter % 10000 == 0) {
        print STDERR '.';
    }
    shift @$aref;
}
