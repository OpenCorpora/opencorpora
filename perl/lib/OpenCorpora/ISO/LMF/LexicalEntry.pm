package OpenCorpora::ISO::LMF::LexicalEntry;

use strict;
use warnings;
use utf8;

our $VERSION = "0.01";




sub new {
  my($class, %args) = @_;
 
  my $self = bless({}, $class);
 
# if (exists $args{handlers}) { 
#   my $handlers = $args{handlers};
#   $self->{handler_lemma} = exists $handlers->{lemma} ? $handlers->{lemma} : \&nop_function;
# }

  $self->{lemma}->{text} = "";
  $self->{lemma}->{gram} = {};
  $self->{forms} = {};
   
  return $self;
}

sub lemma_text {
  my $self = shift;
  if (@_) {
    my $text = shift;
    $self->{lemma}->{text} = $text_;
  }

  return $self->{lemma}->{text};
}

