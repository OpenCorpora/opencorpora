#!/usr/bin/perl

use strict;
use utf8;
use XML::Parser;
use Getopt::Std;

binmode(STDIN, ':encoding(utf8)');
binmode(STDOUT, ':encoding(utf8)');

my %options;
my %dict;
my @context = ();

getopts('Ccf:ln:s', \%options);
# C - count only n-grams having at least one cyrillic letter in each token
# c - skip tokens without cyrillic symbols
# f - path to xml with annotation
# l - whether we should lowercase
# n - make n-grams
# s - whether we should include sentence borders as tokens

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

# output
for my $k(sort {$dict{$b} <=> $dict{$a}} keys %dict) {
    printf "%s\t%d\n", $k, $dict{$k};
}

# subroutines

sub tag_start {
    my ($expat, $tag_name, %attr) = @_;
    
    if ($tag_name eq 'token') {
        if ($options{'c'} && $attr{'text'} !~ /[А-ЯЁа-яё]/) {
            return;
        }

        my $tt = $options{'l'} ? to_lower($attr{'text'}) : $attr{'text'};
        if ($options{'n'} == 1) {
            $dict{$tt}++;
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
            if ($t !~ /[А-ЯЁа-яё]/ && $t ne '@border@') {
                shift @$aref;
                return;
            }
        }
    }
    
    $dict{join(' ', @$aref)}++;
    shift @$aref;
}
