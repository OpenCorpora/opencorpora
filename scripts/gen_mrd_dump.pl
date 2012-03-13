use strict;
use utf8;

use Lingua::AOT::MorphDict;

my $d = new Lingua::AOT::MorphDict(Mrd=>"morphs.mrd", Gramtab=>"rgramtab.tab");

binmode(STDOUT, ":encoding(utf-8)");
binmode(STDERR, ":encoding(utf-8)");
binmode(STDIN, ":encoding(utf-8)");

#my %exception_to_remove = ( "БОСУ" => 1, "ВЕСТЬ" => 1 );
my %exceptions = (
                   ##########
                   # Фиксы из http://code.google.com/p/opencorpora/source/detail?r=372
                   "БОСОЙ" => # текст леммы (формы по умолчанию)
                      {
                        #grm => (map { $_ => 1 } qw/П кач/), # набор граммем леммы
                        grm     => [ qw/П кач/ ],
                        actions => [
                                     {
                                       what => "add",
                                       form => "БОСУ",
                                       grm  => "П, жр,ед,вн,од,но, кач,арх"
                                     }
                                   ]
                      },
                   "БОСУ" => # текст леммы (формы по умолчанию)
                      {
                        grm     => [ qw/ФРАЗ/ ],
                        actions => [
                                     {
                                       what => "remove_lemma"
                                     }
                                   ],
                      },
                   "ВЕСТЬ" => # текст леммы (формы по умолчанию)
                      {
                        grm     => [ qw/ФРАЗ/ ],
                        actions => [ 
                                     {
                                       what => "remove_lemma"
                                     }
                                   ],
                      },
                   "ВЕДАТЬ" =>
                      {
                        grm     => [ qw/ИНФИНИТИВ нс пе/ ],
                        actions => [
                                     {
                                       what => "add",
                                       form => "ВЕСТЬ",
                                       grm  => "Г, дст,нст,3л,ед, нс,пе,арх"
                                     }
                                   ]
                       }
                    #
                    ##########
                  );

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

  my $lemma_grm;
  if (defined $lemma->Ancode()) {
    $lemma_grm = $d->Ancode2Grammems($lemma->Ancode());
  } 

  my %lemma_grm_hash = map { $_ => 1 } split(/,\s*/, $lemma_grm);
  $lemma_grm_hash{$lemma->GetPOS()} = 1;
  my %defform_grm_hash = map { $_ => 1 } split (/[, ]\s*/, $d->Ancode2Grammems($lemma->GetDefForm()->Ancode()));
  my @extra_forms;
 
  if (exists $exceptions{$lemma->GetDefForm()->Text()}) {
    print STDERR "A: there are some exceptions for form " . $lemma->GetDefForm()->Text() . "/" . join(",", keys %lemma_grm_hash) . "\n";
    my @pattern_grm = @{$exceptions{$lemma->GetDefForm()->Text()}->{grm}};
    my $match_count = 0;
    foreach my $g (@pattern_grm) {
      if (exists($lemma_grm_hash{$g}) || exists($defform_grm_hash{$g})) {
        $match_count += 1;
      }
    }
    if ($#pattern_grm + 1 == $match_count) {
      my $skip_this_lemma = 0;
      foreach my $action (@{$exceptions{$lemma->GetDefForm()->Text()}->{actions}}) {
        if ("add" eq $action->{what}) {
          # добавляем форму
          print STDERR "A: add form to " . $lemma->GetDefForm()->Text() . "\n";
          push @extra_forms, { text => $action->{form}, grm => $action->{grm} };
        } elsif ("remove_lemma" eq $action->{what}) {
          print STDERR "A: removing exception " . $lemma->GetDefForm()->Text() . "\n";
          $skip_this_lemma = 1;
          next;
        }
      }
      if (1 == $skip_this_lemma) {
        next;
      }
    }
  }

  print "PARA " . $lemma->ParadigmId() . "\n";

  for (my $f = 0; $f < $lemma->MaxFormNo(); $f++) {
    my $form = $lemma->GetForm($f);

    my $form_grm = $d->Ancode2Grammems($form->Ancode());
    my $output_line = $form->Text() . "\t" . $form_grm;
    if (defined $lemma->Ancode()) {
      $output_line .= ", " . $lemma_grm . "\n";
    } else {
      $output_line .= "\n";
    }

    %lemma_grm_hash = map { $_ => 1 } split(/,\s*/, $lemma_grm);
    if (exists($lemma_grm_hash{"св"}) && exists($lemma_grm_hash{"нс"})) {
      delete $lemma_grm_hash{"св"};
      delete $lemma_grm_hash{"нс"};
      my $new_line = $form->Text() . "\t" . $form_grm . ", " . join(",", keys %lemma_grm_hash);
      if ($form_grm !~ /нст/) {
        print $new_line . ",св\n";
      }
      if ($form_grm !~ /буд/ || ($form_grm =~ /пвл/ && $form_grm !~ /1л/)) {
        print $new_line . ",нс\n";
      }
    } else {
      # pluralia tantum notation fix
      $output_line =~ s/мн,мн/мн,pl/;
      print $output_line;
    }
  }

  foreach my $ef (@extra_forms) {
    print $ef->{text} . "\t" . $ef->{grm} . "\n";
  }

  print "\n";
} 

