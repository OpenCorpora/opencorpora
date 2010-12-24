package Lingua::AOT::MorphDict::Gramtab;

use strict;
use warnings;
use utf8;
use Encode;

our $VERSION = "0.01";

 
sub new {
  my ($class, $fn) = @_;
  my $self = {};
  bless($self, $class);

  $self->load_gramtab($fn);

  return $self;
} 

sub load_gramtab {
  my ($self, $fn) = @_;

  open(FH, "< $fn") || die "can't open gramtab file \"$fn\"";
  binmode(FH, ":encoding(windows-1251)");
  while(<FH>) {
    chomp $_;
    if ($_ =~ /^([А-ЯЁа-яё]{2,2})\s+(\w)\s+([А-ЯЁа-яё_\-\*]+)\s+([а-яё\-0-9\,]*)/) {
      my ($ancode, $pos, $gram_line) = ($1, $3, $4);
      $gram_line =~ s/,\s*$//;
      $gram_line =~ s/,,/,/g;
      $self->{ancodes}->{$ancode} = ("*" ne $pos ? $pos : "");
      if (length($self->{ancodes}->{$ancode}) > 0 && length($gram_line) > 0) {
        $self->{ancodes}->{$ancode} .= ", ";
      }
      $self->{ancodes}->{$ancode} .= $gram_line;
    } 
  }
  close(FH);
}

sub Ancode2Grammems {
  my ($self, $ancode) = @_;
  if (!exists $self->{ancodes}->{$ancode}) {
    die "can't find ancode\"$ancode\"";
  }
  return $self->{ancodes}->{$ancode};
}
