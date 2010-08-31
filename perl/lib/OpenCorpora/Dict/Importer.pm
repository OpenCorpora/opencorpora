package OpenCorpora::Dict::Importer;

use strict;
use warnings;
use utf8;

use OpenCorpora::Dict::Importer::Word;

sub new {
    my($class, %args) = @_;
 
    my $self = {};
 
    bless $self;
    #return is implicit in bless
}
sub read_aot {
    my $self = shift;
    my $path = shift;
    my $word = undef;
    my @forms;
    open F, $path or die "Cannot read $path";
    binmode(F, ':utf8');
    while(<F>) {
        if (/\S+\t\S+,\s?(?:\S+)?,?\s?(?:\S+)?/) {
            push @forms, $_;
        }
        elsif (/\S/) {
            warn "Bad string: <$_>";
        }
        else {
            $word = new OpenCorpora::Dict::Importer::Word(\@forms);
            if ($word->has_gram_all('од')) {
                print $word->{LEMMA}."\n";
            }
            @forms = ();
        }
    }
    close F;
}

1;
