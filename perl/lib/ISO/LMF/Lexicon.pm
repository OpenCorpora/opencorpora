package OpenCorpora::ISO::LMF::Lexicon;

use strict;
use warnings;
use utf8;

use OpenCorpora::ISO::LMF::EntityBase;

our $VERSION = "0.01";


sub new {
  my($class, %args) = @_;
  my $base = OpenCorpora::ISO::LMF::EntityBase->new();
  my $self = bless($base, $class);
 
  $self->{lexical_entries} = ();

  return $self;
}

sub add_lexical_entry {
  my ($self, @lexical_entries) = @_;
  foreach my $le (@lexical_entries) {
    push @{$self->{lexical_entries}}, $le;
  }
}

1;
