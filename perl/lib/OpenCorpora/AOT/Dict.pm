package OpenCorpora::AOT::Dict;

use strict;
use warnings;
use utf8;
use Encode;

use Dict::Gramtab;
use Dict::Paradigm;
use Dict::AccentParadigm;
use Dict::Lemma;
use Dict::MorphVariant;

our $VERSION = "0.01";

 
sub new {
  my ($class, %args) = @_;
  my $self = {};

  $self->{fnMrd} = $args{Mrd} if exists($args{Mrd});
  $self->{fnGramtab} = $args{Gramtab} if exists($args{Gramtab});

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
      push @{$self->{aLookupIndex}->{$f->Text()}}, new OpenCorpora::AOT::Dict::MorphVariant($lid, $f->Ancode());
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

  $self->{Gramtab} = new OpenCorpora::AOT::Dict::Gramtab($fnGramtab);
  $self->load_mrd($fnMrd);
}

sub load_mrd {
  my ($self, $fnMrd) = @_;

  open(FH, "<", $fnMrd) or die $!;
  load_mrd_section(\*FH, sub { push @{$self->{aParadigm}}, new OpenCorpora::AOT::Dict::Paradigm(shift); });
  load_mrd_section(\*FH, sub { push @{$self->{aAccentParadigm}}, new OpenCorpora::AOT::Dict::AccentParadigm(shift); });
  load_mrd_section(\*FH, sub { push @{$self->{aHistory}}, shift });
  load_mrd_section(\*FH, sub { push @{$self->{aPrefix}}, shift });
  load_mrd_section(\*FH, sub { my $l = new OpenCorpora::AOT::Dict::Lemma($self, shift); push @{$self->{aLemma}}, $l if defined $l; });
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
