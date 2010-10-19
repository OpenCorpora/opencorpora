package OpenCorpora::AOT::Dict::Lemma;

use strict;
use warnings;
use utf8;

our $VERSION = "0.01";

 
sub new {
  my ($class, $line) = @_;
  my $self = {};

  my ($stem, $pid, $aid, $hid, $ancode, $prefid) = split(/\s+/, $line);
  ($self->{stem}, $self->{pid}) = ($stem, $pid);
  $self->{aid} = $aid unless ("-" eq $aid);
  $self->{hid} = $hid unless ("-" eq $hid);
  $self->{ancode} = $ancode unless ("-" eq $ancode);
  $self->{prefid} = $prefid unless ("-" eq $prefid);

  bless($self, $class);
  return $self;
} 

