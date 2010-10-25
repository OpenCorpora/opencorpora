package Lingua::AOT::MorphDict::FormSpec;

use strict;
use warnings;
use utf8;
use Encode;

our $VERSION = "0.01";

 
sub new {
  my $self = {};
  my $class;
  ($class, $self->{flex}, $self->{ancode}, $self->{prefix}) = @_;

  bless($self, $class);

  return $self;
} 
 
