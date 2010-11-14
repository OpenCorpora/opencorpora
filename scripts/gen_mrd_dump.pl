use strict;
use utf8;

use Lingua::AOT::MorphDict;

my $d = new Lingua::AOT::MorphDict(Mrd=>"morphs.mrd", Gramtab=>"rgramtab.tab");

binmode(STDOUT, ":encoding(utf-8)");
binmode(STDERR, ":encoding(utf-8)");
binmode(STDIN, ":encoding(utf-8)");

for (my $l = 0; $l < $d->MaxLemmaNo(); $l++) {
  my $lemma = $d->GetLemma($l);
  if ($lemma->GetDefForm()->Text() =~ /\-/) {
    print STDERR $lemma->GetPOS() . ", " . $lemma->GetDefForm()->Text() . ", " . $l;
    if (defined $lemma->Ancode()) {
      print STDERR ", " . $d->Ancode2Grammems($lemma->Ancode());
    }
    print STDERR "\n";
  }
  if ($lemma->GetDefForm()->Text() =~ /^([А-ЯЁа-яёA-Za-z]+)\-([А-ЯЁа-яёA-Za-z]+)$/) {
    my ($p1, $p2) = ($1, $2);
    if ($p2 !~ /[Ёё]/ && $p2 =~ /[Ее]/) {
      if ($lemma->GetPOS() eq "П" ) {
        while ($p2 =~ /^([А-ЯЁа-яё]*)[Ее]([А-ЯЁа-яё]*)$/g) {
          my $w = $1 . "Ё" . $2;
          my $i = $d->Lookup($w);
          if (defined $i) {
            my $related_lemma;
            foreach my $mv (@{$i}) {
              $related_lemma = $d->GetLemma($mv->LemmaId());
              #print STDERR "$p1-$p2 " . $lemma->ParadigmId() . " -> $w " . $related_lemma->ParadigmId() . "\n";
              if ($lemma->GetPOS() eq $related_lemma->GetPOS()) {
                last;
              } else {
                $related_lemma = undef;
              }
            }
            if (defined $related_lemma) {
              # fix stem ...
              $lemma->SetStem($p1 . "-" . $related_lemma->Stem());
              $lemma->SetParadigmId($related_lemma->ParadigmId());
              #print STDERR "SetStem from $p1-$p2 to $p1-$w\n";
              last;
            }
          }
        } 
      } else {
        # не прилагательное, а другая часть речи
        while ($p2 =~ /^([А-ЯЁа-яё]*)[Ее]([А-ЯЁа-яё]*)$/g) {
          my $w = $1 . "Ё" . $2;
          my $i = $d->Lookup($w);
          if (defined $i) {
            #print STDERR "$p1-$p2 -> $p1-$w\n";
          }
        } 
      }
    } # если во второй половине слова нет Ё, но есть Е  
  }

  print "PARA " . $lemma->ParadigmId() . "\n";

  for (my $f = 0; $f < $lemma->MaxFormNo(); $f++) {
    my $form = $lemma->GetForm($f);

    my $output_line = $form->Text() . "\t" . $d->Ancode2Grammems($form->Ancode());
    if (defined $lemma->Ancode()) {
      $output_line .= ", " . $d->Ancode2Grammems($lemma->Ancode()) . "\n";
    } else {
      $output_line .= "\n";
    }

    # pluralia tantum notation fix
    $output_line =~ s/мн,мн/мн,pl/;
    print $output_line;
  }

  print "\n";
} 

