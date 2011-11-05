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
    'strict',
    'config=s',
    'data_dir=s',
);
usage(2) if $opts{help};
usage() unless defined $opts{config};

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

my $sent = $dbh->selectall_hashref("
    select
        sent_id,
        source
    from
        sentences
", "sent_id");

my $sth = $dbh->prepare("
    select
        lower(tf_text)
    from
        text_forms
    where
        sent_id = ?
    order by
        pos
");

my($sentences_seen, $sentences_good) = (0, 0);
my($tokens_total, $tokens_good, $tokens_expected) = (0, 0, 0);

while(my($id, $data) = each %$sent) {
    my @ethalon = map @$_, @{ $dbh->selectall_arrayref($sth, undef, $id) };

    my $tokenized = $tokenizer->tokens(
        lc $data->{source},
        {
            threshold => $opts{strict} ? 1 : 0.01,
        },
    );

    $sentences_seen++;
    if(join('º', @$tokenized) eq join('º', @ethalon)) {
        $sentences_good++;
    }

    $tokens_total    += @$tokenized;
    $tokens_expected += @ethalon;
    for(0 .. max(scalar @ethalon, scalar @$tokenized)) {
        my $got = $tokenized->[$_];
        next unless defined $got;

        my $expected = $ethalon[$_];
        next unless defined $expected;

        $tokens_good++ if $got eq $expected;
    }
}

my $precision = $tokens_good / $tokens_total;
my $recall    = $tokens_good / $tokens_expected;
printf "%i/%i, Correctness: %.2f%%, Precision: %.4f, Recall: %.4f, F1: %.4f\n",
    $sentences_good,
    $sentences_seen,
    $sentences_good / $sentences_seen * 100,
    $precision,
    $recall,
    F_score(1, $precision, $recall);

sub F_score {
    my($B, $P, $R) = @_;

    ((1 + $B ** 2) * ($P * $R)) / ($B ** 2 * $P + $R)
}

sub max { $_[0] > $_[1] ? $_[0] : $_[1] }

sub usage {
    pod2usage({-verbose => $_[0]});
}

__END__

=head1 SYNOPSIS

perl correctness.pl --options

=head1 DESCRIPTION

This script is to be used to compare results produced by Perl tokenizer against what's currently in database.

=head1 OPTIONS

=over 4

=item --config

Required.

Path to opencorpora config file.

=item --strict

Optional.

Set tokenizer threshold to 1.

=item --data_dir

Optional.

Path to tokenizer's data directory. Defaults to distribution directory.

=item --help

Show this message.

=back
