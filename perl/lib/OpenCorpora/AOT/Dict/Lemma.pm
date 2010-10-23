package OpenCorpora::AOT::Dict::Lemma;

use strict;
use warnings;
use utf8;
use Data::Dumper;
use Dict::FormSpec;
use Dict::Form;
use Dict::Paradigm;

our $VERSION = "0.01";

 
sub new {
  my ($class, $ref_dic, $line) = @_;

  if (!defined $ref_dic) {
    die "ref dic undefined!";
  }
  my $self = {};

  my ($stem, $pid, $aid, $hid, $ancode, $prefid) = split(/\s+/, $line);
  $stem = "" unless ("#" ne $stem);
  ($self->{stem}, $self->{pid}) = ($stem, $pid);
  $self->{aid} = $aid unless ("-" eq $aid);
  $self->{hid} = $hid unless ("-" eq $hid);
  $self->{ancode} = $ancode unless ("-" eq $ancode);
  $self->{prefid} = $prefid unless ("-" eq $prefid);

  scalar @{$ref_dic->{aParadigm}} >= $pid or die "Paradigm id ($pid) is wrong for lemma ($stem).";
  $self->{ref_paradigm} = $ref_dic->{aParadigm}->[$pid];
  if (!defined($self->{ref_paradigm})) {
    die "ref_paradigm isn't defined for $stem / $pid";
  }

  if (defined $self->{prefid} && scalar @{$ref_dic->{aPrefix}} <= $self->{prefid}) {
    die "Prefix id (" . $self->{prefid} . ") is wrong for lemma (" . $self->{stem} . ").";
  }

  if (defined $self->{prefid}) {
    $self->{prefix} = $ref_dic->{aPrefix}->[$self->{prefid}];
  } else {
    $self->{prefix} = "";
  }

  bless($self, $class);
  #$self->build_forms($ref_dic);

  return $self;
} 

sub build_forms {
  die "can't call this";
  my ($self, $ref_dic) = @_;
  my $rp = $self->{ref_paradigm};

  if (!defined $rp) {
    die "rp not defined for " . $self->{stem} . " / " . $self->{pid};
  }

  if (defined $self->{prefid} && scalar @{$ref_dic->{aPrefix}} <= $self->{prefid}) {
    die "Prefix id (" . $self->{prefid} . ") is wrong for lemma (" . $self->{stem} . ").";
  }

  my $prefix = "";
  $prefix = $ref_dic->{aPrefix}->[$self->{prefid}] if (defined $self->{prefid});

  foreach my $rfs (@{$rp->FormSpecs()}) {
    my $form_prefix = "";

    if (defined $rfs->{prefix}) {
      $form_prefix = $rfs->{prefix};
    }

    my $text = $prefix . $form_prefix . $self->{stem} . $rfs->{flex};
    push @{$self->{forms}}, new OpenCorpora::AOT::Dict::Form($ref_dic, $text, $rfs->{ancode}, $self->{ancode});
  }
}

sub ParadigmId {
  my $self = shift;
  return $self->{pid};
}

sub MaxFormNo {
  my $self = shift;
  #return scalar @{$self->{forms}};
  return scalar @{$self->{ref_paradigm}->FormSpecs()};
}

sub GetForm {
  my ($self, $n) = @_;
  #return $self->{forms}->[$n];
  my $rfs = $self->{ref_paradigm}->FormSpecs()->[$n];
  my $form_prefix = "";
  if (defined $rfs->{prefix}) {
    $form_prefix = $rfs->{prefix};
  }

  my $text = $self->{prefix} . $form_prefix . $self->{stem} . $rfs->{flex};
  
  return new OpenCorpora::AOT::Dict::Form("", $text, $rfs->{ancode}, $self->{ancode});
}

sub GetDefForm {
  my $self = shift;
  return $self->GetForm(0);
}

sub Ancode {
  my $self = shift;
  return $self->{ancode};
}

sub Stem {
  my $self = shift;
  return $self->{stem};
}
