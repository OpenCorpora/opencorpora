#!/usr/bin/env perl

use utf8;
use strict;
use warnings;

use DBI;
use Pod::Usage;
use Getopt::Long;
use Config::INI::Reader;
use List::MoreUtils qw(firstidx);
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

if(
    $opts{threshold} < 0
    or $opts{threshold} > 1
)
{
    die "--threshold value '$opts{threshold}' doesn't make sense";
}

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

my $sentences = $dbh->selectall_arrayref("
    select
        sent_id,
        source
    from
        sentences
");
my $vectors = $dbh->selectall_hashref("
    select
        vector,
        coeff
    from
        tokenizer_coeff
", 'vector');
my $tokens_sth = $dbh->prepare("
    select
        tf_text
    from
        text_forms
    where
        sent_id = ?
    order by
        pos
");

my @ethalons;
for(@$sentences) {
    my $tokens = $dbh->selectcol_arrayref($tokens_sth, undef, $_->[0]);
    my $bounds = all_bounds($_->[1], $tokens);

    push @ethalons, [$_->[1], $bounds];
}

my($true_positive, $false_positive, $total) = (0, 0, 0);

for my $ethalon (@ethalons) {
    my $bounds = $tokenizer->tokens_bounds($ethalon->[0]);
    my $vectorz = $tokenizer->vectors($ethalon->[0]);

    for my $i (0 .. length($ethalon->[0]) - 1) {
        my $found = firstidx { $_->[0] == $i } @$bounds;
        if($found >= 0) {
            $total++;
            $true_positive++ if $bounds->[$found][1] >= $opts{threshold};
        }
        else {
            no warnings;
            $false_positive++ if $vectors->{$vectorz->[$i]}{coeff} >= $opts{threshold};
        }
    }
}

my $precision = $true_positive / ($true_positive + $false_positive);
my $recall    = $true_positive / $total;
printf "Threshold: %s, Precision: %.4f, Recall: %.4f, F1: %.4f\n",
    $opts{threshold},
    $precision,
    $recall,
    F_score(1, $precision, $recall);

sub F_score {
    my($B, $P, $R) = @_;

    ((1 + $B ** 2) * ($P * $R)) / ($B ** 2 * $P + $R)
}

sub all_bounds {
    my($text, $tokens) = @_;

    my @bounds;

    my $pos = 0;
    for my $token (@$tokens) {
        while(substr($text, $pos, length $token) ne $token) {
            $pos++;
            if($pos > length $text) {
                die "Weird token [$token] in [$text]";
            }
        }
        push @bounds, $pos + length($token) - 1;
        $pos += length $token;
    }

    \@bounds;
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
