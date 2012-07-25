#!/usr/bin/env perl

use utf8;
use strict;
use warnings;

use DBI;
use Pod::Usage;
use Getopt::Long;
use Config::INI::Reader;
use Lingua::RU::OpenCorpora::Tokenizer;

GetOptions(
    \my %opts,
    'help',
    'skip=s',
    'limit=i',
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

my %skip;
if(defined $opts{skip}) {
    %skip = do {
        open my $fh, '<', $opts{skip} or die $!;

        map { chomp; ($_,undef) }
        grep { not /^#/ and not /^\s*$/ }
        <$fh>;
    }
}

my %stats = (
    tp => 0,
    fp => 0,
    fn => 0,
);

my($count) = @{ $dbh->selectrow_arrayref('select count(sent_id) from sentences') };
my $limit  = $opts{limit} ? $opts{limit}             : $count;
my $offset = $limit       ? int rand $count - $limit : 0;
$offset = 0 if $offset < 0;

my $sth = $dbh->prepare("select sent_id, source from sentences limit $offset, $limit");
$sth->execute;
while(my($id, $text) = $sth->fetchrow_array) {
    next if exists $skip{$id};

    my $ethalon = get_bounds($text, get_tokens($id));
    my %bounds = map  +($_->[0], undef),
                 grep $_->[1] >= $opts{threshold},
                 @{ $tokenizer->tokens_bounds($text) };

    for my $pos (keys %$ethalon) {
        if(exists $bounds{$pos}) { $stats{tp}++ }
        else { $stats{fn}++ }
    }
    for my $pos (keys %bounds) {
        $stats{fp}++ unless exists $ethalon->{$pos};
    }
}

my $precision = $stats{tp} / ($stats{tp} + $stats{fp});
my $recall    = $stats{tp} / ($stats{tp} + $stats{fn});
printf "Threshold: %s, Precision: %.4f, Recall: %.4f, F1: %.4f, Corpus size: %i\n",
    $opts{threshold},
    $precision,
    $recall,
    F_measure(1, $precision, $recall),
    $limit;

sub F_measure {
    my($B, $P, $R) = @_;

    ((1 + $B ** 2) * ($P * $R)) / ($B ** 2 * $P + $R)
}

sub get_tokens {
    my $id = shift;

    my $sth = $dbh->prepare_cached("
        select
            tf_text
        from
            text_forms
        WHERE
            sent_id = ?
        order by
            pos
    ", undef, 3);

    $dbh->selectcol_arrayref($sth, undef, $id);
}

sub get_bounds {
    my($text, $tokens) = @_;

    my %bounds;
    my $pos = 0;
    for my $token (@$tokens) {
        while(substr($text, $pos, length $token) ne $token) {
            $pos++;

            die "Weird token [$token] in sentence [$text]"
                if $pos > length $text;
        }
        $bounds{$pos + length($token)-1} = undef;
        $pos += length $token;
    }

    return \%bounds;
}

sub usage { pod2usage({-verbose => $_[0]}) }

__END__

=head1 SYNOPSIS

perl calculate_metrics.pl --options

=head1 DESCRIPTION

QA tool for Perl tokenizer.

Calculates IR-related metrics for what Perl tokenizer produces. This includes precision, recall and F-measure.

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

=item --skip

Optional.

Path to a list of sentences to skip.

=item --limit

Optional.

Limit corpus to given number of randomly chosen sentences.

=item --help

Show this message.

=back
