package OpenCorpora::Dict::SimpleReader;

use strict;
use warnings;
use utf8;

use OpenCorpora::Dict::Entry;

our $VERSION = "0.01";




sub new {
  my($class, %args) = @_;
 
  my $self = bless({}, $class);
 
  if (exists $args{handlers}) { 
    my $handlers = $args{handlers};
    $self->{handler_lemma} = exists $handlers->{lemma} ? $handlers->{lemma} : \&nop_function;
  }

  $self->{buffer} = "";
 
  return $self;
}

sub parse {
  my $self = shift;
  my $chunk = shift; 

  $self->{buffer} .= $chunk;

  if ($self->{buffer} =~ s/<dr>(.+?)<\/dr>//ms) {
    my $dr = $1;
    my $dict_entry = OpenCorpora::Dict::Entry->new();
    while ($dr =~ s/<l t=\"([^\"]+)\">(.+?)<\/l>//ms) {
      if (!defined($dict_entry->lemma_text)) {
        $dict_entry->lemma_text($1);
        my $gram_chunk = $2;
        while ($gram_chunk =~ /<g v=\"([^\"]+)\"\/>/g) {
          $dict_entry->lemma_gram_add($1);
        }
      } else {
        die "multiple lemma in article $dr";
      }
    }
 
    while ($dr =~ s/<f t=\"([^\"]+)\">(.+?)<\/f>//ms) {
      my $fid = $dict_entry->add_form($1);
      my $gram_chunk = $2;
      while ($gram_chunk =~ /<g v=\"([^\"]+)\"\/>/g) {
        $dict_entry->add_form_gram($fid, $1);
      }
    }
   
    $self->{handler_lemma}($dict_entry);
  }
}

sub nop_function { }
