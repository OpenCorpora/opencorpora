package Lingua::AOT::MorphDict;

use strict;
use warnings;
use utf8;
use Encode;

use Lingua::AOT::MorphDict::Gramtab;
use Lingua::AOT::MorphDict::Paradigm;
use Lingua::AOT::MorphDict::AccentParadigm;
use Lingua::AOT::MorphDict::Lemma;
use Lingua::AOT::MorphDict::MorphVariant;

our $VERSION = "0.01";

 
sub new {
  my ($class, %args) = @_;
  my $self = {};

  $self->{fnMrd} = $args{Mrd} if exists($args{Mrd});
  $self->{fnGramtab} = $args{Gramtab} if exists($args{Gramtab});
  $self->{optRemoveAccentDoublicates} = 1;

  bless($self, $class);

  $self->load($self->{fnGramtab}, $self->{fnMrd}) if exists($self->{fnMrd}) && exists($self->{fnGramtab});
  $self->build_forms();

  return $self;
}

sub MaxLemmaNo {
  my $self = shift;
  return scalar @{$self->{aLemma}};
}

sub GetLemma {
  my ($self, $n) = @_;
  return $self->{aLemma}->[$n];
} 

sub Ancode2Grammems {
  my ($self, $ancode) = @_;
  return $self->{Gramtab}->Ancode2Grammems($ancode);
}

sub build_forms {
  my ($self) = @_;
  for (my $lid = 0; $lid < $self->MaxLemmaNo(); $lid++) {
    my $l = $self->{aLemma}->[$lid]; 
    for (my $fid = 0; $fid < $l->MaxFormNo(); $fid++) {
      my $f = $l->GetForm($fid);
      push @{$self->{aLookupIndex}->{$f->Text()}}, new Lingua::AOT::MorphDict::MorphVariant($lid, $f->Ancode());
    }
  }
}

sub Lookup {
  my ($self, $w) = @_;
  $w =~ tr/а-яёa-z/А-ЯЁA-Z/;
  if (!exists($self->{aLookupIndex}->{$w})) {
    return undef;
  }
  return $self->{aLookupIndex}->{$w};
}

sub load {
  my ($self, $fnGramtab, $fnMrd) = @_;

  $self->{Gramtab} = new Lingua::AOT::MorphDict::Gramtab($fnGramtab);
  $self->load_mrd($fnMrd);
}

sub load_mrd {
  my ($self, $fnMrd) = @_;

  open(FH, "<", $fnMrd) or die $!;                                                                           # optRemoveAccentDoublicates
  load_mrd_section(\*FH, sub { push @{$self->{aParadigm}}, new Lingua::AOT::MorphDict::Paradigm(shift, $self->{optRemoveAccentDoublicates}); });
  load_mrd_section(\*FH, sub { push @{$self->{aAccentParadigm}}, new Lingua::AOT::MorphDict::AccentParadigm(shift); });
  load_mrd_section(\*FH, sub { push @{$self->{aHistory}}, shift });
  load_mrd_section(\*FH, sub { push @{$self->{aPrefix}}, shift });
  load_mrd_section(\*FH, sub { my $l = new Lingua::AOT::MorphDict::Lemma($self, shift); push @{$self->{aLemma}}, $l if defined $l; });
  close(FH);
}

sub load_mrd_section {
  my ($fh, $rsub) = @_;
  my $n = <$fh>;
  while (<$fh>) {
    chomp $_;
    $_ = decode("windows-1251", $_);
    $_ =~ s/[\n\r]+$//;
    $rsub->($_);
    if (--$n <= 0) {
      last;
    }
  }
}
