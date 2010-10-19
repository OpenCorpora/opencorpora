package OpenCorpora::AOT::Dict::Paradigm;

use strict;
use warnings;
use utf8;
use Encode;

our $VERSION = "0.01";

 
sub new {
  my ($class, $line) = @_;
  my $self = {};

  my ($paradigm_text, $other) = split(/\#/, Decode("windows-1251", $line));
  if ( length( $other ) > 0 )
  { die "mrd paring error: $_"; } 

  while ( $paradigm_text =~ /%([А-ЯЁ]*)\*([А-ЯЁа-яё]+)(\*([А-ЯЁ]*))?/g )
  {

  bless($self, $class);


  return $self;
#  bless($self, $class);
} 

