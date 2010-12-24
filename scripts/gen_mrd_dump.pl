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

    my $form_grm = $d->Ancode2Grammems($form->Ancode());
    my $output_line = $form->Text() . "\t" . $form_grm;
    my $lemma_grm;
    if (defined $lemma->Ancode()) {
      $lemma_grm = $d->Ancode2Grammems($lemma->Ancode());
      $output_line .= ", " . $lemma_grm . "\n";
    } else {
      $output_line .= "\n";
    }

    my %lemma_grm_hash = map { $_ => 1 } split(/,\s*/, $lemma_grm);
    if (exists($lemma_grm_hash{"св"}) && exists($lemma_grm_hash{"нс"})) {
      delete $lemma_grm_hash{"св"};
      delete $lemma_grm_hash{"нс"};
      my $new_line = $form->Text() . "\t" . $form_grm . ", " . join(",", keys %lemma_grm_hash);
      print $new_line . ",св\n";
      print $new_line . ",нс\n";
    } else {
      # pluralia tantum notation fix
      $output_line =~ s/мн,мн/мн,pl/;
      print $output_line;
    }
  }

  print "\n";
} 

