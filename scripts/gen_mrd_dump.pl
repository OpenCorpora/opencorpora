use strict;
use utf8;

use Lingua::AOT::MorphDict;

my $d = new Lingua::AOT::MorphDict(Mrd=>"morphs.mrd", Gramtab=>"rgramtab.tab");

binmode(STDOUT, ":encoding(utf-8)");
binmode(STDERR, ":encoding(utf-8)");

for (my $l = 0; $l < $d->MaxLemmaNo(); $l++) {
  my $lemma = $d->GetLemma($l);
  if ($lemma->GetDefForm()->Text() =~ /^([А-ЯЁа-яёA-Za-z]+)\-([А-ЯЁа-яёA-Za-z]+)$/
      && $lemma->GetPOS() eq "П" ) {
    #print STDERR "form with dash found: " . $lemma->GetDefForm()->Text() . " POS=" . $lemma->GetPOS() . "\n";
    my ($p1, $p2) = ($1, $2);
    if ($p2 !~ /[Ёё]/ && $p2 =~ /[Ее]/) {
      while ($p2 =~ /^([А-ЯЁа-яё]*)[Ее]([А-ЯЁа-яё]*)$/g) {
        my $w = $1 . "Ё" . $2;
        my $related_lemma = $d->Lookup($w);
        if (defined $related_lemma) {
          $lemma->SetStem($p1 . "-" . $w);
          print STDERR "SetStem from $p1-$p2 to $w\n";
          last;
        }
      }
    }
  }

  print "PARA " . $lemma->ParadigmId() . "\n";

  for (my $f = 0; $f < $lemma->MaxFormNo(); $f++) {
    my $form = $lemma->GetForm($f);

    print $form->Text() . "\t". $d->Ancode2Grammems($form->Ancode());
    if (defined $lemma->Ancode()) {
      print ", " . $d->Ancode2Grammems($lemma->Ancode()) . "\n";
    } else {
      print "\n";
    }
  }

  print "\n";
} 

