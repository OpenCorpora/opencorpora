package OpenCorpora::ISO::LMF::LexicalResource;

use strict;
use warnings;
use utf8;

our $VERSION = "0.01";

sub new {
  my($class, %args) = @_;
  my $base = OpenCorpora::ISO::LMF::EntityBase->new();
  my $self = bless($base, $class);
 
  $self->{lexicon} = ();

  return $self;
} 

sub add_lexicon {
  my ($self, @lexicon) = @_;
  foreach my $l (@lexicon) {
    push @{$self->{lexicon}}, $l;
  }
}

1;
