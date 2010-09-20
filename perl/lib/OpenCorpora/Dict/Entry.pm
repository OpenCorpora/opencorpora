package OpenCorpora::Dict::Entry;

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

  $self->{lemma}->{text} = undef;
  $self->{lemma}->{gram} = {};
  $self->{forms} = [];
   
  return $self;
}

sub lemma_text {
  my $self = shift;
  if (@_) {
    my $text = shift;
    $self->{lemma}->{text} = $text;
  }

  return $self->{lemma}->{text};
}

sub lemma_gram_add {
  my $self = shift;
  my $gram = shift;
  $self->{lemma}->{gram}->{$gram} = 1;
}

sub lemma_gram {
  my $self = shift;
  my $gram = shift;
  if (exists $self->{lemma}->{gram}->{$gram}) {
    return 1;
  }
  return 0;
}

sub add_form {
  my $self = shift;
  my $text = shift;
  push @{$self->{forms}}, {form => $text};
  return $#{$self->{forms}};
}

sub add_form_gram {
  my $self = shift;
  my ($fid, $gram) = @_;
  $self->{forms}->[$fid]->{gram}->{$gram} = 1;
}

sub get_form_ids {
  my $self = shift;
  return map {$_} 0..$#{$self->{forms}};
}

sub get_form_text {
  my ($self, $fid) = @_;
  return $self->{forms}->[$fid]->{form};
}

sub get_form_grams {
  my ($self, $fid) = @_;
  return sort keys %{$self->{forms}->[$fid]->{gram}};
}

1;
