#!/usr/bin/perl

use strict;
use utf8;
use Getopt::Long;
use Archive::Zip;
use Archive::Zip::MemberRead;
use XML::Parser;
use Encode qw( decode );
use Digest::MD5;
use Data::Dumper;

my ($pools_fn, $dump_fn, $dict_fn) = ("pools.zip", "annot.opcorpora.xml.zip", "dict.opcorpora.txt");
my ($left_ctx, $right_ctx) = (1, 1);
my ($verbose) = ( 1 );

GetOptions("pools-fn=s"   => \$pools_fn,
           "dump-fn=s"    => \$dump_fn,
           "dict-fn=s"    => \$dict_fn,
           "left-ctx=i"   => \$left_ctx,
           "right-ctx=i"  => \$right_ctx,
           "verbose!"     => \$verbose)
or die <<'HELP_MESSAGE';

Possible arguments:

	--pools-fn=pools.zip
	--dump-fn=annot.opcorpora.xml
	--dict-fn=dict.opcorpora.txt
	--verbose / --no-verbose

HELP_MESSAGE

my (%sents, %tokens, $dump_revision);
read_corpus($dump_fn);

open(my $fh_pools, "< $pools_fn") || die "ERROR: can't open $pools_fn";
binmode($fh_pools);
my $pools_fn_md5 = Digest::MD5->new->addfile($fh_pools)->hexdigest;
close($fh_pools);

open(my $fh_dict, "< $dict_fn") || die "ERROR: can't open $dict_fn";
binmode($fh_dict);
my $dict_fn_md5 = Digest::MD5->new->addfile($fh_dict)->hexdigest;
close($fh_dict);

my $output_fn = join("_", ("feat", $dump_revision, $pools_fn_md5, $dict_fn_md5));
print "Output file: $output_fn\n";

my ($rpool_types, $rpools) = read_pools($pools_fn);
my ($rgram, $rforms) = read_dict($dict_fn);
my @all_grams = sort(keys %{$rgram});

merge_pools($rpools, $rpool_types, \%sents, \%tokens, \@all_grams, $rforms, 6, $output_fn);

#
# Merge pools
#

sub calc_agreement {
  my ($ranswers, $task_id, $pool_id) = @_;
  my %known_answers;
  my $total_answers = 0;

  foreach my $answer (@{$ranswers}) {
    $known_answers{$answer} += 1;
    $total_answers += 1;
  }

  if ($total_answers == 0) {
    die "ERROR: no answers in task_id=$task_id pool_id=$pool_id (" . Dumper($ranswers) . ")";
  }

  my $answer_with_max_votes;
  foreach my $answer (sort {$known_answers{$b} <=> $known_answers{$a}} keys %known_answers) {
    $answer_with_max_votes = $answer;
    last;
  }

  my $agreement = 1.0 * $known_answers{$answer_with_max_votes} / $total_answers;
  return $agreement;
}

sub calc_correctness {
  my ($ranswers, $correct_answer) = @_;
  my %known_answers;
  my $total_answers = 0;

  foreach my $answer (@{$ranswers}) {
    $known_answers{$answer} += 1;
    $total_answers += 1;
  }

  if (! exists $known_answers{$correct_answer}) {
    return 0.0;
  }

  my $correctness = 1.0 * $known_answers{$correct_answer} / $total_answers;

  return $correctness;
}

sub grammems_as_bits {
  my ($rdict_item, $rgrams) = @_;
  my %hash_gramms;

  if (defined($rdict_item)) {
    foreach my $rform (@{$rdict_item}) {
      foreach my $grm (@{$rform->{stem_gram}}) {
        $hash_gramms{$grm} += 1;
      }
      foreach my $grm (@{$rform->{form_gram}}) {
        $hash_gramms{$grm} += 1;
      }
    }
  }

  my @all_gramms;
  foreach my $grm (@{$rgrams}) {
    if (exists $hash_gramms{$grm}) {
      push @all_gramms, 1.0;
    } else {
      push @all_gramms, 0.0;
    }
  }

  return join(" ", @all_gramms);
}

sub collect_token_features {
  my ($rel_pos, $focus_pos, $focus_token_id, $rsent, $rtokens, $rforms, $rgrams) = @_;

  my $rfocus_token = $rtokens->{$rsent->[$focus_pos]->{id}};
  if (! defined($rfocus_token)) {
    die "ERROR: can't find token with id $rsent->[$focus_pos]->{id}";
  }

  #print "\n" . Dumper($rtoken) . "\n";
  #print Dumper($rsent) . "\n";
  if ($rfocus_token->{id} != $focus_token_id) {
    die "ERROR: focus token id missmatch: $rfocus_token->{id} , $focus_token_id";
  }

  my $token_pos = $focus_pos + $rel_pos;
  #print "$token_pos = $focus_pos + $rel_pos\n";

  my ($token_id, $rtoken, $text_uc, $rdict_item) = (undef, undef, "", undef);

  if ($token_pos >= 0 && $token_pos <= $#{$rsent}) {
    $token_id = $rsent->[$token_pos]->{id};
    if (! exists($rtokens->{$token_id})) {
      die "ERROR: can't find token with id = $token_id";
    }
    $rtoken = $rtokens->{$token_id};

    $text_uc = uc($rtoken->{text});

    if (exists $rforms->{$text_uc}) {
      $rdict_item = $rforms->{$text_uc};
    }
  }

  my $line = join(" ", (length($text_uc),                                           # token length
                        $token_pos,                                                 # token position in sentence (token number)
                        $#{$rsent} + 1 - $token_pos,                                # reverse token position in sentence
                        defined($rdict_item) ? $#{$rdict_item} : 0,                 # total number of homonyms according to the dictionary
                        grammems_as_bits($rdict_item, $rgrams)                      # grammems of focus token
                 ));

  return $line; 
} 

sub get_pool_type_features_vector {
  my ($pool_type_no, $max_pool_type) = @_;
  my $line; 

  for (my $i = 0; $i <= $max_pool_type; $i++) {
    if (length($line) > 0) {
      $line .= " ";
    }
 
    if ($pool_type_no == $i) {
      $line .= "1";
    } else {
      $line .= "0";
    }
  }

  return $line;
}

sub merge_pools {
  my ($rpools, $rtypes, $rsents, $rtokens, $rgrams, $rforms, $min_state, $output_fn) = @_;
  my @lines;

  if ($verbose) {
    print "Merging pools ...\n";
  }

  my @all_pool_types = sort keys %{$rtypes};

  foreach my $pool_id (sort {$a <=> $b} keys %{$rpools}) {
    my $rpool = $rpools->{$pool_id};

    if ($verbose) {
      print "Pool id: $pool_id, pool state: $rpool->{state}\r";
    }

    if ($rpool->{state} < $min_state) {
      next;
    }

    #print "Pool id: $pool_id\r";

    my $pool_type_no = undef;
    for (my $i = 0; $i <= $#all_pool_types; $i++) {
      if ($rpool->{type} == $all_pool_types[$i]) {
        $pool_type_no = $i;
        last;
      }
    }
    if (! defined($pool_type_no)) {
      die "ERROR: can't find pool type \"$rpool->{type}\"";
    }

    foreach my $task_id (sort {$a <=> $b} keys %{$rpool->{tasks}}) {
      my $rtask = $rpool->{tasks}->{$task_id};
      my $rtoken = $rtokens->{$rtask->{token_id}};
      my $rsent = $rsents->{$rtoken->{sent_id}};
      my $text_uc = uc($rtoken->{text});
      my $rdict_item = undef;
      if (exists $rforms->{$text_uc}) {
        $rdict_item = $rforms->{$text_uc};
      }

      my $line = join(" ", ($task_id, $pool_id,                                         
                            $rtask->{token_id}, $rtoken->{sent_id},
                            $#{$rsent} + 1,                                                  # sentence length
                            calc_agreement($rtask->{answers}, $task_id, $pool_id),           # agreement
                            calc_correctness($rtask->{answers}, $rtask->{correct}),          # percent of correct answers
                            $rpool->{type},                                                  # pool type as string
                            get_pool_type_features_vector($pool_type_no, $#all_pool_types)   # pool type in "one hot" encoding
                     ));
      for (my $i = (-1) * $left_ctx; $i < 0; $i++) {
        $line .= " " . collect_token_features($i, $rtoken->{pos}, $rtask->{token_id}, $rsent, $rtokens, $rforms, $rgrams);
      }

      $line .= " " . collect_token_features(0, $rtoken->{pos}, $rtask->{token_id}, $rsent, $rtokens, $rforms, $rgrams);

      for (my $i = 1; $i <= $right_ctx; $i++) {
        $line .= " " . collect_token_features($i, $rtoken->{pos}, $rtask->{token_id}, $rsent, $rtokens, $rforms, $rgrams);
      }

      push @lines, $line;
    }
    #last;
  }

  if ($verbose) {
    print("\n");
  }

  open(F, "> $output_fn") || die "ERROR: can't open \"$output_fn\"";
  binmode(F, ":encoding(utf-8)");
  my $nfeatures = 0;
  foreach my $line (@lines) {
    my @features = split(/\s+/, $line);
    if ($nfeatures == 0) {
      $nfeatures = $#features + 1;
      if ($verbose) {
        print("$nfeatures features in output vectors\n");
      }
    } else {
      if ($nfeatures != $#features + 1) {
        die "ERROR: incorrect number of features: " . ($#features + 1) . " instead of $nfeatures\n";
      }
    }
    
    print F $line . "\n";
  }
  close(F);

}

#
# Corpus dump reader
#

my $current_sent_id = undef;
sub handle_corpus_dump_start_tag {
  my ($expat, $element, %attr) = @_;

  if ($element eq "sentence") {
    $current_sent_id = $attr{id};
  } elsif ($element eq "annotation") {
    $dump_revision = $attr{revision};
  } elsif ($element eq "token") {
    if (! defined($current_sent_id)) {
      die "ERROR: \$current_sent_id isn't defined in \"handle_corpus_dump_start_tag\"";
    }
    my %token;
    ($token{id}, $token{text}, $token{pos}, $token{sent_id}) = ($attr{id}, $attr{text}, $#{$sents{$current_sent_id}} + 1, $current_sent_id);
    push @{$sents{$current_sent_id}}, \%token;
    $tokens{$token{id}} = \%token; 
  }
}

sub handle_corpus_dump_end_tag {
  my ($expat, $element) = @_;
  if ($element eq "sentence") {
    $current_sent_id = undef;
  }
}

sub read_corpus() {
  my ($fn) = @_;

  my $parser = XML::Parser->new( Handlers => { Start => \&handle_corpus_dump_start_tag, End => \&handle_corpus_dump_end_tag }, ProtocolEncoding => "UTF-8" );

  my $fh = undef;
  if ($fn =~ /\.zip$/) {
    if ($verbose) {
      print("Reading corpus from \"$fn\" as ZIP archive ...\n");
    }
 
    my $zip = Archive::Zip->new($fn);
    if (!defined($zip)) {
      die "ERROR: can't open \"$fn\"";
    }
    my $member = $zip->memberNamed("annot.opcorpora.xml");
    $fh = $member->readFileHandle();
    if (!defined($fh)) {
      die "ERROR: can't read \"$fn\"";
    }

     my $expatnb = $parser->parse_start();
     my $line;
     while (defined($line = $fh->getline())) {
       $expatnb->parse_more($line);
     }

     $expatnb->parse_done();
     $fh->close();
  } else {
    if ($verbose) {
      print("Reading corpus from \"$fn\" ...\n");
    }
    open($fh, "<", $fn) || die "ERROR: can't read \"$fn\"";
    binmode($fh, ":encoding(utf-8)");
    $parser->parse($fh);
    close($fh);
  }

  if ($verbose) {
    my @sent_id = keys %sents;
    my @token_id = keys %tokens;
    print("Corpus: " . $#sent_id . " sentences, " . $#token_id . " tokens\n");
  }
 

  return (\%sents, \%tokens);
}


#
# pools.zip archive reader
#

sub read_pools() {
  my ($fn) = @_;
  my (%pool_types, %pools);

  if ($verbose) {
    print("Reading pools from \"$fn\" ...\n");
  }

  my $zip = Archive::Zip->new($fn);
  if (!defined($zip)) {
    die "ERROR: can't open \"$fn\"";
  }

  my $member = $zip->memberNamed("pools.txt");
  my $fh = $member->readFileHandle();
  if (!defined($fh)) {
    die "ERROR: can't read \"pools.txt\" from \"$fn\"";
  }

  my $line = undef;
  while (defined($line = $fh->getline())) {
    chomp $line;
    #print $line;
    my ($id, $type, $state) = split(/\t/, $line);
    #print "id=$id state=$state\n";
    if ($id =~ /\d+/) {
      push @{$pool_types{$type}}, $id;
      $pools{$id}->{state} = $state;
      $pools{$id}->{type} = $type;
    }
  }

  $fh->close();

  foreach my $id (sort {$a <=> $b} keys %pools) {
    my $member = $zip->memberNamed("pool_" . $id . ".tab");
    my $fh = $member->readFileHandle();
 
    if (!defined($fh)) {
      die "ERROR: can't read \"pool_$id.tab\" from \"$fn\"";
    }

    if (! exists $pools{$id}->{state}){
      die "ERROR: no state saved for pool_id=$id";
    }

    while (defined($line = $fh->getline())) {
      chomp $line;
      my ($task_id, $token_id, $text, $comment, @other) = split(/\t/, $line);
      if ($task_id !~ /\d+/) {
        next;
      }
      my @answers;
      my $correct = $other[$#other];
      for (my $i = 0; $i < ($#other - 1); $i++) {
        push @answers, $other[$i];
      }

      my %task;
      ($task{id}, $task{token_id}, $task{text}, $task{answers}, $task{correct}) = ($task_id, $token_id, decode("utf-8", $text), \@answers, $correct);
      $pools{$id}->{tasks}->{$task_id} = \%task;

      #print Dumper(\%task);
    }

    $fh->close();
  }

  if ($verbose) {
    print("Pools: " . scalar(keys %pool_types) . " types, " . scalar(keys %pools) . " pools\n");
  }

  return (\%pool_types, \%pools);
}


#
# dictionary reader
#

sub parse_dict_line {
  my ($line, $rstate, $rgram, $rforms) = @_;

  if ($line =~ /^$/) {

  } elsif ($line =~ /^(\d+)$/) {

  } else {
    my ($form, $gram_str) = split(/\t/, $line);
    my ($stem_gram_str, $form_gram_str) = split(/\s+/, $gram_str);
    my @stem_gram = split(/,/, $stem_gram_str);
    my @form_gram = split(/,/, $form_gram_str);
    my @all_gram = (@stem_gram, @form_gram);

    foreach my $grm (@all_gram) {
      $rgram->{$grm} += 1;
    }

    my $unicode_form = decode("utf-8", $form);

    my %hform;
    $hform{stem_gram} = \@stem_gram;
    $hform{form_gram} = \@form_gram;
    push @{$rforms->{$unicode_form}}, \%hform;
  }
}

sub read_dict() {
  my ($fn) = @_;
  my (%gram, %forms);

  my $fh = undef;
  if ($fn =~ /\.zip$/) {
    if ($verbose) {
      print("Reading dictionary from \"$fn\" as ZIP archive ...\n");
    }
 
    my $zip = Archive::Zip->new($fn);
    if (!defined($zip)) {
      die "ERROR: can't open \"$fn\"";
    }

    my $member = $zip->memberNamed("dict.opcorpora.txt");
    if (!defined($member)) {
      die "ERROR: can't find \"dict.opcorpora.txt\" in \"$fn\"";
    }
 
    $fh = $member->readFileHandle();
    if (!defined($fh)) {
      die "ERROR: can't read \"$fn\"";
    }

    my %state;
    while (defined(my $line = $fh->getline())) {
      chomp $line;
      parse_dict_line($line, \%state, \%gram, \%forms);
    }

    $fh->close();
  } else {
    if ($verbose) {
      print("Reading dictionary from \"$fn\" ...\n");
    }
    open($fh, "<", $fn) || die "ERROR: can't read \"$fn\"";
    #binmode($fh, ":encoding(utf-8)");
    my %state;
    while (<$fh>) {
      chomp $_;
      parse_dict_line($_, \%state, \%gram, \%forms);
    }
    close($fh);
  }

  if ($verbose) {
    print("Dictionary: " . scalar(keys %forms) . " forms, " . scalar(keys %gram) . " grammems\n");
  }

  return (\%gram, \%forms);
}
