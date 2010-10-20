package OpenCorpora::AOT::Dict::Gramtab;

use strict;
use warnings;
use utf8;
use Encode;

our $VERSION = "0.01";

 
sub new {
  my ($class, $fn) = @_;
  my $self = {};

  #my ($paradigm_text, $other) = split(/\#/, Decode("windows-1251", $line));

  bless($self, $class);

  return $self;
} 

sub Ancode2Grammems {
  my ($self, $ancode) = @_;
  return $self->{ancodes}->{$ancode};
}
