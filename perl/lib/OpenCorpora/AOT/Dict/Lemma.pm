package OpenCorpora::AOT::Dict::Lemma;

use strict;
use warnings;
use utf8;

use Dict::FormSpec;
use Dict::Form;
use Dict::Paradigm;

our $VERSION = "0.01";

 
sub new {
  my ($class, $ref_dic, $line) = @_;
  my $self = {};

  my ($stem, $pid, $aid, $hid, $ancode, $prefid) = split(/\s+/, $line);
  ($self->{stem}, $self->{pid}) = ($stem, $pid);
  $self->{aid} = $aid unless ("-" eq $aid);
  $self->{hid} = $hid unless ("-" eq $hid);
  $self->{ancode} = $ancode unless ("-" eq $ancode);
  $self->{prefid} = $prefid unless ("-" eq $prefid);

  $self->{ref_dic} = $ref_dic;
  $self->{ref_paradigm} = $ref_dic->{aParadigm}->[$pid];
  #$self->{ref_accent_paradigm} = $ref_dic->{aAccentParadigm}->{$aid};

  bless($self, $class);
  return $self;
} 

sub build_forms {
  my $self = shift;

  my $rp = $self->{ref_dic}->{aParadigm}->{$self->{pid}};

  my $prefix = "";
  $prefix = $self->{ref_dic}->{aPrefix}->{$self->{prefid}} if (defined $self->{prefid});

  foreach my $rfs (@{$rp->FormSpecs()}) {
    my $form_prefix = "";
    if (defined $rfs->{prefix}) {
      $form_prefix = $rfs->{prefix};
    }
    my $text = $prefix . $form_prefix . $self->{stem} . $rfs->{flex};
    push @{$self->{forms}}, new OpenCorpora::AOT::Dict::Form($self->{ref_dic}, $text, $rfs->{ancode}, $self->{ancode});
  } 
}

sub ParadigmId {
  my $self = shift;
  return $self->{pid};
}

sub Forms {
  my $self = shift;
  return $self->{forms};
}

sub Ancode {
  my $self = shift;
  return $self->{ancode};
}

