package OpenCorpora::Dict::Importer;

use strict;
use warnings;
use utf8;

use OpenCorpora::Dict::Importer::Word;

use constant RULE_TYPE_ALL => 1;
use constant RULE_TYPE_ANY => 2;
use constant RULE_TYPE_GLOBAL => 3;
use constant COND_TYPE_ALL => 1;
use constant COND_TYPE_ONE => 2;
use constant COND_TYPE_NUM => 3;
use constant REL_TYPE_AND => 1;
use constant REL_TYPE_OR => 2;
use constant ACTION_TYPE_CHANGE => 1;
use constant ACTION_TYPE_SPLIT => 2;
use constant DEBUG => 1;
use constant STOP_AFTER => 5;

sub new {
    if (DEBUG) {
        print STDERR "Creating Importer\n";
    }
    my($class, %args) = @_;
 
    my $self = {};
    $self->{RULES} = undef;
    $self->{WORD} = undef;
 
    bless $self;
    #return is implicit in bless
}
sub read_aot {
    my $self = shift;
    my $path = shift;
    my @forms;
    my $counter;
    open F, $path or die "Error: Cannot read $path";
    binmode(F, ':utf8');
    while(<F>) {
        if (/\S+\t\S+,\s?(?:\S+)?,?\s?(?:\S+)?/) {
            if (DEBUG) {
                print STDERR $_;
            }
            push @forms, $_;
        }
        elsif (/\S/) {
            warn "Warning: Bad string: <$_>";
        }
        elsif (scalar @forms > 0) {
            $self->{WORD} = new OpenCorpora::Dict::Importer::Word(\@forms);
            $self->apply_rules();
            @forms = ();
            if (++$counter == STOP_AFTER) {
                last;
            }
            if (DEBUG) {
                print STDERR "====================\n";
            }
        }
    }
    #the last word
    if (scalar @forms > 0) {
        $self->{WORD} = new OpenCorpora::Dict::Importer::Word(\@forms);
        $self->apply_rules();
    }
    close F;
}
sub read_rules {
    if (DEBUG) {
        print STDERR "Reading rules\n";
    }
    my $self = shift;
    my $path = shift;
    my $rule_ref = undef;
    open F, $path or die "Error: Cannot read $path";
    binmode(F, ':utf8');
    while(<F>) {
        if (/^\s*\#/) {
            next; #skipping comments
        }
        if (/^\s*$/) {
            next; #skipping blank lines
        }
        if (/^(\*|\(.+)/) {
            #this is a condition
            #adding the previous rule if it exists
            if ($rule_ref) {
                push @{$self->{RULES}}, $$rule_ref;
                if (DEBUG) {
                    print STDERR "Reading rule #".$#{$self->{RULES}}."\n";
                }
            }
            #new rule
            my $rule = {};
            $rule->{CONDITIONS} = undef;
            $rule->{ID} = defined $self->{RULES} ? scalar @{$self->{RULES}} : 0;
            $rule->{TYPE} = undef;
            $rule->{ACTIONS} = undef;
            $rule->{IS_LAST} = 0;
            $rule_ref = \parse_condition_string($rule, $1);
        }
        elsif (/[\s\t]+((?:CHANGE|SPLIT)\s*\(.+\))/i) {
            #this is an action
            my $action = parse_action_string($1);
            push @{$$rule_ref->{ACTIONS}}, $action;
        }
        else {
            die "Error: Bad line in rules: $_";
        }
    }
    #the last rule
    if ($rule_ref) {
        push @{$self->{RULES}}, $$rule_ref;
        if (DEBUG) {
            print STDERR "Reading rule #".$#{$self->{RULES}}."\n";
        }
    }
}
sub parse_condition_string {
    my $rule = shift;
    my $str = shift;
    chomp $str;
    if ($str =~ s/\s+L\s*$//i) {
        #there's a flag indicating that the rule will be the last
        $rule->{IS_LAST} = 1;
    }
    if ($str =~ /^\*\s*$/) {
        $rule->{TYPE} = RULE_TYPE_GLOBAL;
    }
    while($str =~ /\(([^\)]+)\)\s*([\&\|])?\s*/g) {
        #operator check
        if ($2) {
            if (!$rule->{TYPE}) {
                if ($2 eq '&') {
                    $rule->{TYPE} = RULE_TYPE_ALL;
                } else {
                    $rule->{TYPE} = RULE_TYPE_ANY;
                }
            } else {
                if (
                    ($2 eq '&' && $rule->{TYPE} == RULE_TYPE_ANY) ||
                    ($2 eq '|' && $rule->{TYPE} == RULE_TYPE_ALL)
                   ) {
                    die "Error: Condition type mismatch in: $_";
                }
            }
        } elsif(!$rule->{TYPE}) {
            #if there is only one condition
            $rule->{TYPE} = RULE_TYPE_ALL;
        }
        my $condition = parse_simple_condition($1);
        push @{$rule->{CONDITIONS}}, $condition;
    }
    return $rule;
}
sub parse_simple_condition {
    my $str = shift;
    chomp $str;
    my $cond = {};
    $cond->{TYPE} = undef;
    $cond->{GRAMMEMS} = undef;
    $cond->{REL} = undef;
    $cond->{NUM} = undef;
    my @grammems;
    if ($str =~ /^\#\=(\d+)$/) {
        $cond->{NUM} = $1;
        $cond->{TYPE} = COND_TYPE_NUM;
        return $cond;
    }
    if ($str =~ s/^\*\s*//) {
        $cond->{TYPE} = COND_TYPE_ALL;
    }
    elsif ($str =~ s/^1\s*//) {
        $cond->{TYPE} = COND_TYPE_ONE;
    } else {
        die "Error: Bad condition string: $str";
    }
    if ($str =~ /\|/ && $str =~ /\&/) {
        die "Error: Condition must contain either conjunction or disjunction: $str";
    }
    if ($str =~ /([\|\&])/) {
        if ($1 eq '|') {
            $cond->{REL} = REL_TYPE_OR;
        } else {
            $cond->{REL} = REL_TYPE_AND;
        }
        @grammems = split /\Q$1\E/, $str;
        map { $_ =~ s/\s//g } @grammems;
    } else {
        $cond->{REL} = REL_TYPE_OR;
        @grammems = ($str);
    }
    $cond->{GRAMMEMS} = \@grammems;
    return $cond;
}
sub parse_action_string {
    my $str = shift;
    my $action = undef;
    my @gram_in;
    my @gram_out;
    chomp $str;
    if ($str =~ /CHANGE\s*\((.+?)\s*->\s*(.+)\)/i) {
        $action->{TYPE} = ACTION_TYPE_CHANGE;
        @gram_in = split /,/, $1;
        @gram_out = split /,/, $2;
        map { $_ =~ s/\s//g; } @gram_in;
        for my $i(0..$#gram_in) {
            if ($gram_in[$i] eq '') {
                delete $gram_in[$i];
            }
        }
        map { $_ =~ s/\s//g; } @gram_out;
        for my $i(0..$#gram_out) {
            if ($gram_out[$i] eq '') {
                delete $gram_out[$i];
            }
        }
        $action->{GRAMMEMS_IN} = \@gram_in;
        $action->{GRAMMEMS_OUT} = \@gram_out;
    }
    elsif($str =~ /SPLIT\s*\((.+)\)/i) {
        $action->{TYPE} = ACTION_TYPE_SPLIT;
        @gram_in = split /,/, $1;
        map { $_ =~ s/\s//g; } @gram_in;
        $action->{GRAMMEMS_IN} = \@gram_in;
    }
    else {
        die "Error: Bad action string: $str";
    }
    return $action;
}
sub apply_rules {
    print STDERR "Applying rules\n" if (DEBUG);
    my $self = shift;
    GF:for my $rule(@{$self->{RULES}}) {
            print STDERR "Checking rule ".$rule->{ID}."\n" if DEBUG;
        if ($rule->{TYPE} == RULE_TYPE_GLOBAL) {
            print STDERR "Global, applying\n" if DEBUG;
            $self->apply_rule($rule);
            last GF if $rule->{IS_LAST};
        }
        elsif ($rule->{TYPE} == RULE_TYPE_ALL) {
            my $test = 1;
            for my $c(@{$rule->{CONDITIONS}}) {
                print STDERR "Condition check\n" if DEBUG;
                if (!$self->test_condition($c)) {
                    $test = 0;
                    print STDERR "Condition check failed\n" if DEBUG;
                    last;
                }
            }
            if ($test) {
                $self->apply_rule($rule);
                last GF if $rule->{IS_LAST};
            }
        }
        elsif ($rule->{TYPE} == RULE_TYPE_ANY) {
            for my $c(@{$rule->{CONDITIONS}}) {
                print STDERR "Condition check\n" if DEBUG;
                if ($self->test_condition($c)) {
                    print STDERR "Condition check ok\n" if DEBUG;
                    $self->apply_rule($rule);
                    last GF if $rule->{IS_LAST};
                    last;
                }
            }
        }
        else {
            die "Error: Wrong RULE_TYPE";
        }
    }
}
sub test_condition {
    my $self = shift;
    my $cond = shift;
    my $word = $self->{WORD};
    if ($cond->{TYPE} == COND_TYPE_NUM) {
        if ($cond->{NUM} == $word->get_form_count()) {
            return 1;
        }
        return 0;
    }
    elsif ($cond->{TYPE} == COND_TYPE_ONE) {
        print STDERR "  COND_TYPE_ONE\n" if DEBUG;
        for my $form(@{$word->{FORMS}}) {
            #print STDERR "  testing form ".$form->{TEXT}."\n";
            if (($cond->{REL} == REL_TYPE_AND && $word->form_has_all_grams($form, $cond->{GRAMMEMS})) ||
                ($cond->{REL} == REL_TYPE_OR && $word->form_has_any_gram($form, $cond->{GRAMMEMS}))) {
                print STDERR "OK\n" if DEBUG;
                return 1;
            }
        }
        return 0;
    }
    elsif ($cond->{TYPE} == COND_TYPE_ALL) {
        print STDERR "COND_TYPE_ALL\n" if DEBUG;
        for my $form(@{$word->{FORMS}}) {
            #print STDERR "  testing form ".$form->{TEXT}."\n";
            if (($cond->{REL} == REL_TYPE_AND && !$word->form_has_all_grams($form, $cond->{GRAMMEMS})) ||
                ($cond->{REL} == REL_TYPE_OR && !$word->form_has_any_gram($form, $cond->{GRAMMEMS}))) {
                print STDERR "Fail\n" if DEBUG;
                return 0;
            }
        }
        return 1;
    }
    else {
        die "Error: Wrong COND_TYPE";
    }
}
sub apply_rule {
    my $self = shift;
    my $rule = shift;
    my $word = $self->{WORD};
    print STDERR "    Applying rule ".$rule->{ID}."\n" if DEBUG;
    for my $action(@{$rule->{ACTIONS}}) {
        if ($action->{TYPE} == ACTION_TYPE_CHANGE) {
            print STDERR "    Change\n" if DEBUG;
            $word->change_grammems($action->{GRAMMEMS_IN}, $action->{GRAMMEMS_OUT});
        }
        elsif ($action->{TYPE} == ACTION_TYPE_SPLIT) {
            print STDERR "    Split\n" if DEBUG;
            my @new_words = $word->split_lemma($action->{GRAMMEMS_IN});
            if (@new_words == 1) {
                return;
            }
            for my $new_word(@new_words) {
                $self->{WORD} = $new_word;
                $self->apply_rules();
            }
        }
        else {
            die "Error: Wrong ACTION_TYPE";
        }
    }
}

1;
