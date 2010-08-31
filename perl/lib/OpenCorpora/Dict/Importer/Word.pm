package OpenCorpora::Dict::Importer::Word;

use strict;
use warnings;
use utf8;

sub new {
    my ($class, $ref) = @_;
 
    my $self = {};
    $self->{LEMMA} = undef;
    $self->{FORMS} = undef;

    if (!$ref) {
        die "Cannot dereference";
    }
    my @forms = @$ref;
    #adding forms
    for my $form_string(@forms) {
        if ($form_string =~ /(\S+)\t(\S+),\s?(\S+)?,?\s?(\S+)?/) {
            if (!$self->{LEMMA}) {
                $self->{LEMMA} = to_lower($1);
            }
            my $form_ref;
            $form_ref->{TEXT} = to_lower($1);
            $form_ref->{GRAMMEMS} = undef;
            my @all_gram = ($2);
            push @all_gram, split (/,/, $3) if $3;
            push @all_gram, split (/,/, $4) if $4;
            map { $_ =~ s/\s//g; } @all_gram;
            $form_ref->{GRAMMEMS} = \@all_gram;
            push @{$self->{FORMS}}, $form_ref;
        }
        else {
            die "Bad string: $form_string";
        }
    }
 
    bless $self;
    #return is implicit in bless
}
sub has_gram_all {
    my $self = shift;
    my $search = shift;
    my $test;
    for my $form(@{$self->{FORMS}}) {
        $test = 0;
        for my $gram(@{$form->{GRAMMEMS}}) {
            if ($gram eq $search) {
                $test = 1;
            }
        }
        $test || return 0;
    }
    return 1;
}
sub has_gram_one {
    my $self = shift;
    my $search = shift;
    for my $form(@{$self->{FORMS}}) {
        for my $gram(@{$form->{GRAMMEMS}}) {
            if ($gram eq $search) {
                return 1;
            }
        }
    }
    return 0;
}
sub to_lower {
    my $s = shift;
    $s =~ tr/[А-ЯЁ]/[а-яё]/;
    return $s;
}

1;
