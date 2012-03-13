package Lingua::AOT::MorphDict::Paradigm;

use strict;
use warnings;
use utf8;

use Lingua::AOT::MorphDict::FormSpec;

our $VERSION = "0.01";

 
sub new {
  my ($class, $line, $optRemoveAccentDoublicates) = @_;
  my $self = {};

  my ($paradigm_text, $other) = split(/\#/, $line);
  if (defined($other) && length($other) > 0) { 
    die "mrd paring error: $_"; 
  } 
 
  my %h_known_fs;
  while ($paradigm_text =~ /%(([\-А-ЯЁ]*)\*([А-ЯЁа-яё]+)(\*([А-ЯЁ]*))?)/g) {
    if (!exists($h_known_fs{$1}) || 0 == $optRemoveAccentDoublicates) {
      push @{$self->{forms}}, new Lingua::AOT::MorphDict::FormSpec($2, $3, $5);
      $h_known_fs{$1} = 1;
    }
  }

  if (!defined($self->{forms})) {
    die "can't parse paradigm \"$paradigm_text\"\n";
  }

  bless($self, $class);

  return $self;
} 

sub FormSpecs {
  my $self = shift;
  return $self->{forms};
}

sub GetLastFormNo {
  my $self = shift;
  return $#{$self->{forms}};
}
