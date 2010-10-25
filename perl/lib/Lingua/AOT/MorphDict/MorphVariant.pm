package Lingua::AOT::MorphDict::MorphVariant;

use strict;
use warnings;
use utf8;

use Lingua::AOT::MorphDict::FormSpec;
use Lingua::AOT::MorphDict::Form;
use Lingua::AOT::MorphDict::Paradigm;

our $VERSION = "0.01";

 
sub new {
  my ($class, $lemma_id, $ancode) = @_;
  my $self = {};
  ($self->{lid}, $self->{ancode}) = ($lemma_id, $ancode);
  bless($self, $class);
}

sub LemmaId {
  my $self = shift;
  return $self->{lid};
}

sub Ancode {
  my $self = shift;
  return $self->{ancode};
}
