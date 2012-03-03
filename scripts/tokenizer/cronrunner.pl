#!/usr/bin/env perl

use strict;
use warnings;

my $cmd = shift or exit print qq{Usage: cronrunner.pl "cmd --args"};

my @pieces = grep defined, (split / /, $cmd)[0, 1];
my $file   = (grep -e $_, @pieces)[0];

my $lock;
if(defined $file) {
    $file = (split '/', $file)[-1];
    $lock = "/var/lock/$file.lock";
}
else {
    $lock = "/var/lock/$pieces[0].lock";
}


system qq{flock --exclusive --non-block $lock --command "$cmd"}
    and exit print "Failed to acquire run lock: $!";

exit 0;
