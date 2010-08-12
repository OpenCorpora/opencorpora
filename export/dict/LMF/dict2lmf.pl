use strict;
use utf8;

use OpenCorpora::Dict::SimpleReader;
use OpenCorpora::Dict::Entry;
use OpenCorpora::ISO::LMF;

binmode(STDIN, ":encoding(utf-8)");
binmode(STDOUT, ":encoding(utf-8)");
binmode(STDERR, ":encoding(utf-8)");

my $dictReader = OpenCorpora::Dict::SimpleReader->new(handlers => {lemma => \&processLemma});

while(<STDIN>) {
  
}

sub processLemma {
  my $rEntry = shift;
  my $
}
