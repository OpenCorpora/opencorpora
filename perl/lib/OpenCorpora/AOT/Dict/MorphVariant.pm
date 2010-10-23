package OpenCorpora::AOT::Dict::MorphVariant;

use strict;
use warnings;
use utf8;

use OpenCorpora::AOT::Dict::FormSpec;
use OpenCorpora::AOT::Dict::Form;
use OpenCorpora::AOT::Dict::Paradigm;

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
