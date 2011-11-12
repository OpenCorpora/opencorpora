#!/usr/bin/env perl

use utf8;
use strict;
use warnings;

use DBI;
use Digest::MD5;
use Getopt::Long;
use FindBin qw($Bin);
use Encode qw(_utf8_off);
use Config::INI::Reader;
use IO::Compress::Gzip qw($GzipError);
use IO::Uncompress::Gunzip qw($GunzipError);

GetOptions(
    \my %opts,
    'config=s',
    'output_dir=s',
);
die "Usage: $0 --config=<config> --output_dir=<path>"
    if not defined $opts{config}
    or not defined $opts{output_dir};

my $conf = Config::INI::Reader->read_file($opts{config});
$conf = $conf->{mysql};

my $dbh = DBI->connect(
    "DBI:mysql:database=$conf->{dbname};host=$conf->{host}",
    $conf->{user},
    $conf->{passwd},
    {
        mysql_enable_utf8 => 1,
    },
) or die DBI->errstr;

my $vectors_data = $dbh->selectall_arrayref("
    select
        vector,
        coeff
    from
        tokenizer_coeff
");
$vectors_data = join "\n", map { join ' ', @$_ } @$vectors_data;
update_file('vectors', $vectors_data, $opts{output_dir});

my $hyphens_data = $dbh->selectall_arrayref("
    select
        distinct form_text
    from
        form2lemma
    where
        form_text like '%-%'
");
$hyphens_data = join "\n", map @$_, @$hyphens_data;
update_file('hyphens', $hyphens_data, $opts{output_dir});

open my $fh, '<', "$Bin/../lists/tokenizer_exceptions.txt" or die "exceptions: $!";
my $exceptions_data = do { <$fh>; local $/; <$fh> };
close $fh;
update_file('exceptions', $exceptions_data, $opts{output_dir});

open $fh, '<', "$Bin/../lists/tokenizer_prefixes.txt" or die "prefixes: $!";
my $prefixes_data = do { <$fh>; local $/; <$fh> };
close $fh;
update_file('prefixes', $prefixes_data, $opts{output_dir});

sub update_file {
    my($mode, $data, $path) = @_;

    my $version = time;

    print "$mode: ";

    mkdir $path, 0755 unless -d $path;
    my $fn = "$path/$mode.gz";
    if(-e $fn and -s $fn) {
        my $hash_old = Digest::MD5->new;
        my $fh = IO::Uncompress::Gunzip->new($fn) or die "$fn: $GunzipError";
        $fh->getline; # skip version
        $hash_old->add(join '', $fh->getlines);
        $fh->close;

        _utf8_off($data);
        my $hash_new = Digest::MD5->new;
        $hash_new->add($data);

        if($hash_new->hexdigest eq $hash_old->hexdigest) {
            print "no update needed\n";
            return;
        }
    }

    my $fh = IO::Compress::Gzip->new($fn) or die "$fn: $GzipError";
    $fh->print(join "\n", $version, $data);
    $fh->close;

    $fn =~ s/\.gz$/.latest/;
    open $fh, '>', $fn or die $!;
    print $fh $version;
    close $fh;

    print "updated\n";

    return;
}
