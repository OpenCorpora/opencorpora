#!/usr/bin/env perl

use utf8;
use strict;
use warnings;

use DBI;
use Digest::MD5;
use Config::INI::Reader;
use IO::Compress::Gzip qw($GzipError);
use IO::Uncompress::Gunzip qw($GunzipError);

@ARGV == 2 or die "Usage: $@ <config> <path>";

my $config_file = shift;
my $path        = shift;

my $conf = Config::INI::Reader->read_file($config_file);
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
update_file('vectors', $vectors_data);

my $hyphens_data = $dbh->selectall_arrayref("
    select
        form_text
    from
        form2lemma
    where
        form_text like '%-%'
");
$hyphens_data = join "\n", map @$_, @$hyphens_data;
update_file('hyphens', $hyphens_data);

sub update_file {
    my($mode, $data) = @_;

    my $version = time;

    my $fn = "$path/$mode.gz";
    if(-e $fn and -s $fn) {
        my $fh = IO::Uncompress::Gunzip->new($fn) or die "$fn: $GunzipError";
        my $hash_old = Digest::MD5->new;
        $hash_old->add($_) while $_ = $fh->getline; # stupid Digest::MD5 won't take FileHandle instance as input
        $hash_old = $hash_old->hexdigest;
        $fh->close;

        my $hash_new = Digest::MD5->new->add($data)->hexdigest;

        return if $hash_new eq $hash_old;
    }

    my $fh = IO::Compress::Gzip->new($fn) or die "$fn: $GzipError";
    $fh->print(join "\n", $version, $data);
    $fh->close;

    $fn = "$path/$mode.latest";
    open $fh, '>', $fn or die $!;
    print $fh $version;
    close $fh;

    return;
}
