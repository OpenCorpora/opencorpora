use strict;
use utf8;

use Data::Dumper;
use Dict;

my $d = new OpenCorpora::AOT::Dict(Mrd=>"morphs.mrd", Gramtab=>"rgramtab.tab");

binmode(STDOUT, ":encoding(utf-8)");

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

