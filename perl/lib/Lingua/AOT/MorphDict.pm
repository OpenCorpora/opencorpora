package Lingua::AOT::MorphDict;

use strict;
use warnings;
use utf8;
use Encode;
use Fcntl;
use SDBM_File;
use DB_File;
use NDBM_File;
use DBM_Filter;

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
  #$self->{tempDictFile} = "somefile_db.txt";
  #$self->{db} = undef;

  bless($self, $class);

  $self->load($self->{fnGramtab}, $self->{fnMrd}) if exists($self->{fnMrd}) && exists($self->{fnGramtab});
  $self->build_forms();

  return $self;
}

sub DESTROY {
  my $self = shift;
  #unlink $self->{tempDictFile};
  #print STDERR $self->{tempDictFile} . " unlinked\n";
}

sub MaxLemmaNo {
  my $self = shift;
  return scalar @{$self->{aLemma}};
}

sub GetLemma {
  my ($self, $n) = @_;
  return new Lingua::AOT::MorphDict::Lemma($self, $self->{aLemma}->[$n]);
} 

sub Ancode2Grammems {
  my ($self, $ancode) = @_;
  return $self->{Gramtab}->Ancode2Grammems($ancode);
}

sub build_forms {
  my ($self) = @_;
  
  #$self->{db} = tie %{$self->{aLookupIndex}}, 'SDBM_File', $self->{tempDictFile}, O_RDWR|O_CREAT, 0666
  #  or die "Couldn't tie SDBM file: $!";
  #$self->{db}->Filter_Push("utf8");
  for (my $lid = 0; $lid < $self->MaxLemmaNo(); $lid++) {
    my %h;
    my $l = $self->GetLemma($lid); 
    for (my $fid = 0; $fid < $l->MaxFormNo(); $fid++) {
      my $f = $l->GetForm($fid);
      #push @{$self->{aLookupIndex}->{$f->Text()}}, new Lingua::AOT::MorphDict::MorphVariant($lid, $f->Ancode());
      push @{$h{$f->Text()}}, $lid . " " . $f->Ancode();
      #push @{$self->{aLookupIndex}->{$f->Text()}}, $lid . " " . $f->Ancode();
    }
 
    foreach my $f (keys %h) {
      #push @{$self->{aLookupIndex}->{$f}}, $lid . " " . $h{$f};
      $self->{aLookupIndex}->{$f} = join("\t", @{$h{$f}});
    }
  }
}

sub Lookup {
  my ($self, $w) = @_;
  $w =~ tr/а-яёa-z/А-ЯЁA-Z/;
  if (!exists($self->{aLookupIndex}->{$w})) {
    return undef;
  }
  my $a = $self->{aLookupIndex}->{$w};
  my $retval;
  foreach my $i (split(/\t/, $a)) {
    my ($lid, $anex) = split(/\s+/, $i);
    while ($anex =~ /(..)/g) {
      push @{$retval}, new Lingua::AOT::MorphDict::MorphVariant($lid, $1);
    }
  }
  return $retval;
  #return $self->{aLookupIndex}->{$w};
}

sub GetParadigm {
  my ($self, $pid) = @_;
  if ($pid > scalar @{$self->{aParadigm}}) {
    die "wrong paradigm id ($pid) at MorphDict::GetParadigm";
  }
  return $self->{aParadigm}->[$pid];
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
  load_mrd_section(\*FH, sub { #push @{$self->{aHistory}}, shift
                             });
  load_mrd_section(\*FH, sub { push @{$self->{aPrefix}}, shift });
  load_mrd_section(\*FH, sub { push @{$self->{aLemma}}, shift; });
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
