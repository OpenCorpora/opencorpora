#!/usr/bin/env perl

use utf8;
use strict;
use warnings;

use DBI;
use Pod::Usage;
use Getopt::Long;
use Config::INI::Reader;
use List::MoreUtils qw(any);
use Lingua::RU::OpenCorpora::Tokenizer;

GetOptions(
    \my %opts,
    'help',
    'config=s',
    'data_dir=s',
    'threshold=f',
);
usage(2) if $opts{help};
usage() if not defined $opts{config}
           or not defined $opts{threshold};

my $conf = Config::INI::Reader->read_file($opts{config});
$conf    = $conf->{mysql};

my $dbh = DBI->connect(
    "dbi:mysql:$conf->{dbname}:$conf->{host}",
    $conf->{user},
    $conf->{passwd},
    {
        mysql_enable_utf8 => 1,
    },
) or die DBI->errstr;

my $tokenizer = Lingua::RU::OpenCorpora::Tokenizer->new(
    (data_dir => $opts{data_dir}) x !!$opts{data_dir},
);

my $_separator = 'º';
my $ethalons   = $dbh->selectall_arrayref("
    select
        source,
        group_concat(tf_text order by text_forms.pos separator '$_separator')
    from
        sentences
    join
        text_forms
    using
        (sent_id)
    group by
        source
");
$_->[1] = all_occurences($_->[1], $_separator) for @$ethalons;

my %tokens = (
    expected => 0,
    good     => 0,
    total    => 0,
);

for my $ethalon (@$ethalons) {
    my @tokenized = map  $_->[0],
                    grep $_->[1] >= $opts{threshold},
                    @{ $tokenizer->tokens_bounds($ethalon->[0]) };

    $tokens{total}    += @tokenized;
    $tokens{expected} += @{ $ethalon->[1] };
    for my $pos (@tokenized) {
        $tokens{good}++ if any { $pos == $_ } @{ $ethalon->[1] };
    }
}

my $precision = $tokens{good} / $tokens{total};
my $recall    = $tokens{good} / $tokens{expected};
printf "Threshold: %s, Precision: %.4f, Recall: %.4f, F1: %.4f\n",
    $opts{threshold},
    $precision,
    $recall,
    F_score(1, $precision, $recall);

sub F_score {
    my($B, $P, $R) = @_;

    ((1 + $B ** 2) * ($P * $R)) / ($B ** 2 * $P + $R)
}

sub all_occurences {
    my($str, $substr) = @_;

    my @occurences;
    my $offset = 0;
    while($offset < length $str) {
        my $pos = index $str, $substr, $offset;
        last if $pos < 0;

        push @occurences, $pos + 1;
        $offset = $pos + 1;
    }

    \@occurences;
}

sub usage { pod2usage({-verbose => $_[0]}) }

__END__

=head1 SYNOPSIS

perl calculate_metrics.pl --options

=head1 DESCRIPTION

QA tool for Perl tokenizer.

Calculates IR-related metrics for what Perl tokenizer produces. This includes precision, recall and F-score.

=head1 OPTIONS

=over 4

=item --config

Required.

Path to opencorpora config file.

=item --threshold

Required.

Tokenizer's threshold.

=item --data_dir

Optional.

Path to tokenizer's data directory. Defaults to distribution directory.

=item --help

Show this message.

=back
