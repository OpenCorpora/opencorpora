package OpenCorpora::ISO::LMF::EntityBase;

use strict;
use warnings;
use utf8;

our $VERSION = "0.01";




sub new {
  my($class, %args) = @_;
 
  my $self = bless({}, $class);
 
  $self->{xmlatt} = {};
  $self->{feat} = {};
  $self->{fsr} = {};
   
  return $self;
}


1;
