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
push @$_, [split $_separator, $_->[1]] for @$ethalons;

my %sentences = (
    good  => 0,
    total => 0,
);
my %tokens = (
    expected => 0,
    good     => 0,
    total    => 0,
);

for my $ethalon (@$ethalons) {
    my $tokenized = $tokenizer->tokens(
        $ethalon->[0],
        {
            threshold => $opts{threshold},
        },
    );

    $sentences{total}++;
    if(join($_separator, @$tokenized) eq $ethalon->[1]) {
        $sentences{good}++;
    }

    $tokens{total}    += @$tokenized;
    $tokens{expected} += @{ $ethalon->[2] };
    for(0 .. max(scalar @{ $ethalon->[2] }, scalar @$tokenized)) {
        no warnings;
        $tokens{good}++ if $ethalon->[2][$_] eq $tokenized->[$_];
    }
}

my $precision   = $tokens{good} / $tokens{total};
my $recall      = $tokens{good} / $tokens{expected};
my $correctness = $sentences{good} / $sentences{total};
printf "Threshold: %s, Correctness: %.4f, Precision: %.4f, Recall: %.4f, F1: %.4f\n",
    $opts{threshold},
    $correctness, # how much what's produced by Perl module is similar to what's in database
    $precision,
    $recall,
    F_score(1, $precision, $recall);

sub F_score {
    my($B, $P, $R) = @_;

    ((1 + $B ** 2) * ($P * $R)) / ($B ** 2 * $P + $R)
}

sub max { $_[0] > $_[1] ? $_[0] : $_[1] }

sub usage { pod2usage({-verbose => $_[0]}) }

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

=item --threshold

Required.

Tokenizer's threshold.

=item --data_dir

Optional.

Path to tokenizer's data directory. Defaults to distribution directory.

=item --help

Show this message.

=back
