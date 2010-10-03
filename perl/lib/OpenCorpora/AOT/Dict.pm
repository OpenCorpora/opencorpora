package OpenCorpora::AOT::Dict;

use strict;
use warnings;
use utf8;

our $VERSION = "0.01";

 
sub new {
  my ($class, %args) = @_;
  my $self = {};

  $self->{fnMrd} = $args{Mrd} if exists($args{Mrd});
  $self->{fnGramtab} = $args{Gramtab} if exists($args{Gramtab});

  bless($self, $class);

  $self->load($self->{fnGramtab}, $self->{fnMrd}) if exists($self->{fnMrd}) && exists($self->{fnGramtab});

  return $self;
#  bless($self, $class);
} 

sub load {
  my ($self, $fnGramtab, $fnMrd) = @_;

  $self->{Gramtab} = new OpenCorpora::AOT::Dict::Gramtab($fnGramtab);
  $self->load_mrd($fnMrd);
}

sub load_mrd {
  my ($self, $fnMrd) = @_;

  open(my $fh, "<", $fnMrd) or die $!;
  load_mrd_section($fh, 
  close($fh);
}
