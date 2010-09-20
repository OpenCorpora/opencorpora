use strict;
use utf8;

use OpenCorpora::Dict::SimpleReader;
use OpenCorpora::Dict::Entry;
use OpenCorpora::ISO::LMF::LexicalResource;
use OpenCorpora::ISO::LMF::Lexicon;
use OpenCorpora::ISO::LMF::LexicalEntry;

binmode(STDIN, ":encoding(utf-8)");
binmode(STDOUT, ":encoding(utf-8)");
binmode(STDERR, ":encoding(utf-8)");

my $dictReader = OpenCorpora::Dict::SimpleReader->new(handlers => {lemma => \&processLemma});

my $lmf_lexical_resource = OpenCorpora::ISO::LMF::LexicalResource->new(dtdRevision => "16.opecorpora.1");
# dtdRevision code - ISO dtd revision number + "opencorpora" + our revision number
# our LMF implementation have following changes from ISO specification:
# - attributes (att) in <feat> tag are to be uniq (is this specified anywhere?)
# - grammatical information is stored in FSR form (this is possible way according to Annex R)

my $lmf_lexicon = OpenCorpora::ISO::LMF::Lexicon->new();

while(<STDIN>) {
  $dictReader->parse($_); 
}

$lmf_lexical_resource->add_lexicon(\$lmf_lexicon);
print STDERR $lmf_lexical_resource->to_xml();

sub processLemma {
  my $rEntry = shift;
  my $lmf_le = OpenCorpora::ISO::LMF::LexicalEntry->new();
  my $lemma = OpenCorpora::ISO::LMF::WordForm->new();
  my $lemma->feat->{writtenForm} = $rEntry->lemma_text;
  print $rEntry->lemma_text . "\n";
  foreach my $fid ($rEntry->get_form_ids()) {
    print "\t" . join(" ", $rEntry->get_form_grams($fid)) . "\n"; 
  } 

  $lmf_lexicon->add_lexical_entry(\$lmf_le);
}

sub buildLexicalEntry {
  
}

sub convert_gram_to_pair {
  my $gram = shift;
  return {feat => "value"};

}
