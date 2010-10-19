use strict;
use utf8;

use Data::Dumper;
use Dict;

my $d = new OpenCorpora::AOT::Dict(Mrd=>"morphs.mrd", Gramtab=>"rgramtab.tab");

foreach my $lemma (@{$d->Lemmata()}) {
  print STDERR $lemma->ParadigmId() . "\n";
  foreach my $form (@{$lemma->Forms()}) {
    print STDERR $form->Text() . $d->Gramtab($form->Ancode());
    if (defined $lemma->Ancode()) {
      print STDERR ", " . $d->Gramtab($lemma->Ancode()) . "\n";
    }
  }
} 

print Dumper($d);
