package OpenCorpora::ISO::LMF::WordForm;

use strict;
use warnings;
use utf8;

use OpenCorpora::ISO::LMF::EntityBase;

our $VERSION = "0.01";


sub new {
  my($class, %args) = @_;
  my $base = OpenCorpora::ISO::LMF::EntityBase->new();
  my $self = bless($base, $class);
 
  $self->{form_representations} = ();

  return $self;
}

sub add_form_representation {
  my ($self, @form_representations) = @_;
  foreach my $fr (@form_representations) {
    push @{$self->{form_representations}}, $fr;
  }
}

1;
