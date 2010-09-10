package OpenCorpora::Dict::Importer::Word;

use strict;
use warnings;
use utf8;

use constant DEBUG => 1;

sub new {
    if (DEBUG) {
        print STDERR "Creating Word\n";
    }
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
sub form_has_gram {
    my $form = shift;
    my $search = shift;
    for my $gram(@{$form->{GRAMMEMS}}) {
        if ($gram eq $search) {
            return 1;
        }
    }
    return 0;
}
sub form_has_all_grams {
    my $self = shift;
    my $form = shift;
    my @search = @{shift()};
    print STDERR "  testing if ".$form->{TEXT}." has all of ".join(',', @search).'.. ' if DEBUG;
    my $neg = 0; #whether we have a '!'
    for my $gram(@search) {
        $neg = ($gram =~ s/^\!//);
        if (($neg && form_has_gram($form, $gram)) ||
            (!$neg && !form_has_gram($form, $gram))) {
            print STDERR "It doesn't.\n" if DEBUG;
            return 0;
        }
    }
    print STDERR "It does!\n" if DEBUG;
    return 1;
}
sub form_has_any_gram {
    my $self = shift;
    my $form = shift;
    my @search = @{shift()};
    print STDERR "  testing if ".$form->{TEXT}." has any of ".join(',', @search).'.. ' if DEBUG;
    my $neg = 0; #whether we have a '!'
    for my $gram(@search) {
        $neg = ($gram =~ s/^\!//);
        if ((!$neg && form_has_gram($form, $gram)) ||
            ($neg && !form_has_gram($form, $gram))) {
            print STDERR "It does!\n" if DEBUG;
            return 1;
        }
    }
    print STDERR "It doesn't.\n" if DEBUG;
    return 0;
}
sub change_grammems {
    my $self = shift;
    my @in = @{shift()};
    my %in; $in{$_} = 1 for (@in);
    my @out = @{shift()};
    my %out; $out{$_} = 1 for (@out);
    for my $form(@{$self->{FORMS}}) {
        if ($self->form_has_all_grams($form, \@in)) {
            my @new_grams;
            for my $gram(@{$form->{GRAMMEMS}}) {
                push (@new_grams, $gram) if !exists $in{$gram};
            }
            push (@new_grams, $_) for(keys %out);
        }
    }
    return $self;
}
sub split_lemma {
    my $self = shift;
    return [$self];
}
sub to_lower {
    my $s = shift;
    $s =~ tr/[А-ЯЁ]/[а-яё]/;
    return $s;
}
sub get_form_count {
    my $self = shift;
    return scalar @{$self->{FORMS}};
}

1;
