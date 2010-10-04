package OpenCorpora::Dict::Importer::Word;

use strict;
use warnings;
use utf8;

use constant DEBUG => 0;

sub new {
    #a Word may be created either with a set of forms or without one
    print STDERR "Creating Word\n" if DEBUG;
    my ($class, $ref, $para_no) = @_;
 
    my $self = {};
    $self->{LEMMA} = undef;
    $self->{FORMS} = undef;
    $self->{APPLIED_RULES} = undef;
    $self->{PARADIGM_NO} = $para_no;
    $self->{LINKS} = undef;

    if (!$ref) {
        #no forms given
        bless $self;
        return $self;
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
sub count_form_has_gram {
    my $form = shift;
    my @search = @{shift()};
    my $count = 0;
    for my $search(@search) {
        form_has_gram($form, $search) && $count++;
    }
    return $count;
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
    for my $form(@{$self->{FORMS}}) {
        if ($self->form_has_all_grams($form, \@in)) {
            my @new_grams;
            for my $gram(@{$form->{GRAMMEMS}}) {
                push (@new_grams, $gram) if !exists $in{$gram};
            }
            push (@new_grams, $_) for(@out);
            $form->{GRAMMEMS} = \@new_grams;
        }
    }
}
sub split_lemma {
    my $self = shift;
    my @grammems = @{shift()};
    my %new_words;
    my @out_words;
    my $has_aster = 0;
    my $ok;
    print STDERR "    split with (".join(',', @grammems).")\n" if DEBUG;
    #check if there is '*'
    for my $gram(@grammems) {
        if ($gram eq '*') {
            $has_aster = 1;
            last;
        }
    }
    #splitting itself
    for my $form(@{$self->{FORMS}}) {
        $ok = 0;
        #check if any form has more than one of @grammems
        if (count_form_has_gram($form, \@grammems) > 1) {
            warn "Warning: Form '".$form->{TEXT}."' has several grammems among (".join(',', @grammems)."), cannot split, skipping";
            return [$self];
        }
        for my $gram(@grammems) {
            if (form_has_gram($form, $gram)) {
                push @{$new_words{$gram}}, $form;
                $ok = 1;
                last;
            }
        }
        if (!$ok) {
            if ($has_aster) {
                push @{$new_words{'*'}},  $form;
            }
            else {
                warn "Warning: Form '".$form->{TEXT}."' has no grammems among (".join(',', @grammems)."), cannot split, skipping";
                return [$self];
            }
        }
    }
    #split successful, now we should construct Word's
    my $k;
    for my $i(0..$#grammems) {
        $k = $grammems[$i];
        next unless exists $new_words{$k};
        my $word = new();
        my @forms = @{$new_words{$k}};
        $word->{LEMMA} = $forms[0]->{TEXT};
        $word->{FORMS} = \@forms;
        $out_words[$i] = $word;
    }
    return \@out_words;
}
sub generate_paradigm {
    my $self = shift;
    if ($self->get_form_count() > 1) {
        warn "Warning: Word ".$self->{LEMMA}." has more than one form, cannot generate full paradigm, skipping";
        return;
    }
    my @gram = @{shift()};
    for my $gr(@gram) {
        $self->generate_paradigm_plain($gr);
    }
}
sub generate_paradigm_plain {
    my $self = shift;
    my @gram = @{shift()};
    my @new_forms;
    for my $form(@{$self->{FORMS}}) {
        for my $g(@gram) {
            my $new_form = {};
            $new_form->{TEXT} = $form->{TEXT};
            my @new_grams = (@{$form->{GRAMMEMS}}, $g);
            $new_form->{GRAMMEMS} = \@new_grams;
            push @new_forms, $new_form;
        }
    }
    $self->{FORMS} = \@new_forms;
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
sub rule_applied {
    my $self = shift;
    my $rule = shift;
    for my $r(@{$self->{APPLIED_RULES}}) {
        if ($r == $rule->{ID}) {
            return 1;
        }
    }
    return 0;
}
sub to_string {
    my $self = shift;
    my $out = 'PARA '.($self->{PARADIGM_NO} ? $self->{PARADIGM_NO} : '-1')."\n";
    for my $form(@{$self->{FORMS}}) {
        $out .= $form->{TEXT}."\t".join(',', @{$form->{GRAMMEMS}})."\n";
    }
    for my $lnk(@{$self->{LINKS}}) {
        $out .= "link '".$$lnk[1]."' to ".$$lnk[0]."\n";
    }
    return $out;
}
sub to_xml {
    my $self = shift;
    my $out = '<dr><l t="'.$self->{LEMMA}.'">';
    my @lgram = @{$self->get_lemma_grammems()};
    my %lgram;
    for my $gr(@lgram) {
        $out .= "<g v=\"$gr\"/>";
        $lgram{$gr} = 1;
    }
    $out .= '</l>';
    for my $form(@{$self->{FORMS}}) {
        $out .= '<f t="'.$form->{TEXT}.'">';
        for my $gr(@{$form->{GRAMMEMS}}) {
            $out .= "<g v=\"$gr\"/>" unless exists $lgram{$gr};
        }
        $out .= '</f>';
    }
    $out .= '</dr>';
    return $out;
}
sub get_all_grammems {
    my $self = shift;
    my %all = ();
    for my $form(@{$self->{FORMS}}) {
        for my $gram(@{$form->{GRAMMEMS}}) {
            ++$all{$gram};
        }
    }
    return \%all;
}
sub get_lemma_grammems {
    my $self = shift;
    my %grams = %{$self->get_all_grammems()};
    my @out;
    my $num = $self->get_form_count();
    for my $gr(keys %grams) {
        if ($grams{$gr} == $num) {
            push @out, $gr;
        }
    }
    return \@out;
}

1;
