package OpenCorpora::AOT::Dict;

use strict;
use warnings;
use utf8;
use Encode;

use Dict::Gramtab;
use Dict::Paradigm;
use Dict::AccentParadigm;
use Dict::Lemma;

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
#  bless($self, $class);
}

sub Lemmata {
  my $self = shift;
  return $self->{aLemma};
} 

sub Ancode2Grammems {
  my ($self, $ancode) = @_;
  return $self->{Gramtab}->Ancode2Grammems($ancode);
}

sub build_forms {
  my ($self) = @_;
  
  foreach my $l (@{$self->{aLemma}}) {
    my ($stem, $pid) = ($l->{stem}, $l->{pid});

    scalar @{$self->{aParadigm}} >= $pid or die "Paradigm id ($pid) is wrong for lemma ($stem).";
    my $rp = $self->{aParadigm}->[$pid];

    my $prefix = "";
    if (defined $l->{prefid}) {
      scalar @{$self->{aPrefix}} >= $l->{prefid} or die "Prefix id (" . $l->{prefid} . ") is wrong for lemma ($stem).";
      $prefix = $self->{aPrefix}->[$l->{prefid}];
    }

    foreach my $f (@{$rp->{forms}}) {
      my $form_prefix = "";
      if (defined $f->{prefix}) {
        $form_prefix = $f->{prefix};
      }
      my $text = $prefix . $form_prefix . $l->{stem} . $f->{flex};
      print STDERR "F $text " . $l->{ancode} . " " . $f->{ancode} . "\n";
    }
  } 
  print STDERR "\n";
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
  load_mrd_section(\*FH, sub { push @{$self->{aLemma}}, new OpenCorpora::AOT::Dict::Lemma($self, shift); });
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
