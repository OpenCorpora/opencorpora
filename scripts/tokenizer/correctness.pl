#!/usr/bin/env perl

use utf8;
use strict;
use warnings;

use DBI;
use Getopt::Long;
use Config::INI::Reader;
use Lingua::RU::OpenCorpora::Tokenizer;

GetOptions(
    \my %opts,
    'hard',
    'wrong',
    'config=s',
    'data_dir=s',
);
exit print "Usage: $0 --config=config [--data_dir=<path> --hard --wrong]"
    unless $opts{config};

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

my @wrong;
my $seen = my $good = my $confident = my $bounds = 0;

while(my($id, $data) = each %$sent) {
    my @ethalon = map @$_, @{ $dbh->selectall_arrayref($sth, undef, $id) };

    my $tokenized = $tokenizer->tokens(
        lc $data->{source},
        {
            threshold => $opts{hard} ? 1 : 0.001,
        },
    );

    $seen++;
    if(join('º', @$tokenized) eq join('º', @ethalon)) {
        $good++;
    }
    elsif($opts{wrong}) {
        push @wrong, [[@$tokenized], \@ethalon];
    }

    $tokenized = $tokenizer->tokens_bounds(lc $data->{source});

    $bounds    += @$tokenized;
    $confident += grep { $_->[1] == 1 or $_->[1] == 0 } @$tokenized;
}

if($opts{wrong}) {
    open my $fh, '>:utf8', 'wrong.log';
    for(@wrong) {
        print $fh join "\n", map { join 'º', @$_ } @$_;
        print $fh "\n" x 2;
    }
    close $fh;
}

printf "%i/%i, %.2f%% correctness, %.2f%% confidence\n",
    $good,
    $seen,
    $good / $seen * 100,
    $confident / $bounds * 100;
