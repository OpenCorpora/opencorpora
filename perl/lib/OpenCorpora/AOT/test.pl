use strict;
use utf8;

use Data::Dumper;
use Dict;

my $d = new OpenCorpora::AOT::Dict(Mrd=>"morphs.mrd", Gramtab=>"rgramtab.tab");

binmode(STDOUT, ":encoding(utf-8)");

my $text = "Человек человеку волк , а зомби зомби зомби . Эти типы стали есть в цехе . Мама мыла раму и стекло .";
my @words = split(/\s+/, $text);

foreach my $w (@words) {
  my $i = $d->Lookup($w); 
  print "$w\t";
  if (!defined($i)) {
    print "UNKNOWN_WORD";
  } else {
    foreach my $mw (@{$i}) {
      my $lemma = $d->GetLemma($mw->LemmaId());
      my $form_grm = $d->Ancode2Grammems($mw->Ancode());
      my $lemma_grm = $d->Ancode2Grammems($lemma->Ancode()) if defined $lemma->Ancode();
      print " # " . $lemma->GetDefForm()->Text() . "/" . $form_grm;
      if (length($lemma_grm) > 0) {
        print ", " . $lemma_grm; 
      }
    }
  }
  print "\n";
}
die;
for (my $l = 0; $l < $d->MaxLemmaNo(); $l++) {
  my $lemma = $d->GetLemma($l);
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

