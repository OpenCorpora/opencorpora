package OpenCorpora::AOT::Dict::Paradigm;

use strict;
use warnings;
use utf8;

use Dict::FormSpec;

our $VERSION = "0.01";

 
sub new {
  my ($class, $line) = @_;
  my $self = {};

  my ($paradigm_text, $other) = split(/\#/, $line);
  if (defined($other) && length($other) > 0) { 
    die "mrd paring error: $_"; 
  } 
#print STDERR $paradigm_text;
  while ($paradigm_text =~ /%([А-ЯЁ]*)\*([А-ЯЁа-яё]+)(\*([А-ЯЁ]*))?/g) {
    push @{$self->{forms}}, new OpenCorpora::AOT::Dict::FormSpec($1, $2, $4);
  }

  bless($self, $class);

  return $self;
} 

sub FormSpecs {
  my $self = shift;
  return $self->{forms};
}
