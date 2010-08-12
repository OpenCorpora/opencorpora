package OpenCorpora::Dict::SimpleReader;

use strict;
use warnings;
use utf8;

our $VERSION = "0.01";




sub new {
  my($class, %args) = @_;
 
  my $self = bless({}, $class);
 
  if (exists $args{handlers}) { 
    my $handlers = $args{handlers};
    $self->{handler_lemma} = exists $handlers->{lemma} ? $handlers->{lemma} : \&nop_function;
  }
 
  return $self;
}

sub nop_function { }
