package OpenCorpora::AOT::Dict::Form;

use strict;
use warnings;
use utf8;
use Encode;

our $VERSION = "0.01";

 
sub new {
  my $self = {};
  my ($class, $ref_dic, $text, $ancode, $lemma_ancode) = @_;
  ($self->{text}, $self->{ancode}) = ($text, $ancode);

  bless($self, $class);
  return $self;
} 

sub Text {
  my $self = shift;
  return $self->{text};
}

sub Ancode {
  my $self = shift;
  return $self->{ancode};
} 
