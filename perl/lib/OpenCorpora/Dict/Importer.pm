package OpenCorpora::Dict::Importer;

use strict;
use warnings;
use utf8;

use Getopt::constant (
    'DEBUG' => 0,
    'STOP_AFTER' => 0
);

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
use constant ACTION_TYPE_GENERATE => 3;

sub new {
    print STDERR "Creating Importer\n" if DEBUG;
    my($class, %args) = @_;
 
    my $self = {};
    $self->{RULES} = undef;
    $self->{WORD} = undef;
    $self->{STATS} = undef;
 
    bless $self;
    #return is implicit in bless
}
sub read_aot {
    my $self = shift;
    my $path = shift;
    my @forms;
    my $para_no = undef;
    my $counter;
    open F, $path or die "Error: Cannot read $path";
    binmode(F, ':utf8');
    while(<F>) {
        if (/\S+\t\S+,\s?(?:\S+)?,?\s?(?:\S+)?/) {
            print STDERR $_ if DEBUG;
            push @forms, $_;
        }
        elsif (/PARA (\d+)/) {
            $para_no = $1;
        }
        elsif (/\S/) {
            warn "Warning: Bad string: <$_>";
        }
        elsif (scalar @forms > 0) {
            $self->{WORD} = new OpenCorpora::Dict::Importer::Word(\@forms, $para_no);
            $self->update_gram_stats(0);
            ++$self->{STATS}->{TOTAL}->{$para_no};
            my $applied = $self->apply_rules();
            $self->{STATS}->{APPLIED}->{$para_no} += $applied;
            if ($self->{WORD}->{LEMMA}) {
                $self->update_gram_stats(1);
                print $self->{WORD}->to_string()."\n";
            }
            @forms = ();
            $para_no = undef;
            ++$counter;
            print STDERR "====================\n" if DEBUG;
            if ($counter == STOP_AFTER && STOP_AFTER>0) {
                last;
            }
        }
    }
    #the last word
    if (scalar @forms > 0) {
        $self->{WORD} = new OpenCorpora::Dict::Importer::Word(\@forms, $para_no);
        $self->update_gram_stats(0);
        ++$self->{STATS}->{TOTAL}->{$para_no};
        my $applied = $self->apply_rules();
        $self->{STATS}->{APPLIED}->{$para_no} += $applied;
        if ($self->{WORD}->{LEMMA}) {
            $self->update_gram_stats(1);
            print $self->{WORD}->to_string()."\n";
        }
    }
    close F;
    $self->print_stats();
}
sub read_rules {
    print STDERR "Reading rules\n" if DEBUG;
    my $self = shift;
    my $path = shift;
    my $rule_ref = undef;
    open F, $path or die "Error: Cannot read $path";
    binmode(F, ':utf8');
    while(<F>) {
        s/^\x{feff}//;
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
                print STDERR "Reading rule #".$#{$self->{RULES}}."\n" if DEBUG;
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
        elsif (/[\s\t]+((?:CHANGE|SPLIT|GENERATE)\s*\(.+\))/i) {
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
        print STDERR "Reading rule #".$#{$self->{RULES}}."\n" if DEBUG;
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
    if ($str =~ /CHANGE\s*\((.+?)?\s*->\s*(.+)?\)/i) {
        $action->{TYPE} = ACTION_TYPE_CHANGE;
        @gram_in = split /,/, $1 if $1;
        @gram_out = split /,/, $2 if $2;
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
        for my $i(0..$#gram_in) {
            if ($gram_in[$i] eq '') {
                delete $gram_in[$i];
            }
        }
        $action->{GRAMMEMS_IN} = \@gram_in;
    }
    elsif($str =~ /GENERATE\s*\((.+)\)/i) {
        $action->{TYPE} = ACTION_TYPE_GENERATE;
        my @gram = split /;/, $1;
        for my $g(@gram) {
            my @gram1 = split /,/, $g;
            map { $_ =~ s/\s//g; } @gram1;
            for my $i(0..$#gram1) {
                if ($gram1[$i] eq '') {
                    delete $gram1[$i];
                }
            }
            push @gram_in, \@gram1;
        }
        $action->{GRAMMEMS_IN} = \@gram_in;
    }
    else {
        die "Error: Bad action string: $str";
    }
    return $action;
}
sub apply_rules {
    #returns 1 if at least 1 rule was applied
    my $self = shift;
    my $applied = 0;
    print STDERR "Applying rules to ".$self->{WORD}->{LEMMA}.' ('.$self->{WORD}->get_form_count()." forms)\n" if DEBUG;
    GF:for my $rule(@{$self->{RULES}}) {
        print STDERR "Checking rule ".$rule->{ID}."\n" if DEBUG;
        if ($rule->{TYPE} == RULE_TYPE_GLOBAL) {
            print STDERR "Global, applying\n" if DEBUG;
            my $res = $self->apply_rule($rule);
            last GF if $rule->{IS_LAST} && $res;
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
                my $res = $self->apply_rule($rule);
                $applied = 1 if $res;
                last GF if $rule->{IS_LAST} && $res;
            }
        }
        elsif ($rule->{TYPE} == RULE_TYPE_ANY) {
            for my $c(@{$rule->{CONDITIONS}}) {
                print STDERR "Condition check\n" if DEBUG;
                if ($self->test_condition($c)) {
                    print STDERR "Condition check ok\n" if DEBUG;
                    my $res = $self->apply_rule($rule);
                    $applied = 1 if $res;
                    last GF if $rule->{IS_LAST} && $res;
                    last;
                }
            }
        }
        else {
            die "Error: Wrong RULE_TYPE";
        }
    }
    return $applied;
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
    if ($word->rule_applied($rule)) {
        print STDERR "    Rule already applied, skipping\n" if DEBUG;
        return 0;
    }
    print STDERR "    Applying rule ".$rule->{ID}."\n" if DEBUG;
    for my $action(@{$rule->{ACTIONS}}) {
        if ($action->{TYPE} == ACTION_TYPE_CHANGE) {
            print STDERR "    Change\n" if DEBUG;
            $word->change_grammems($action->{GRAMMEMS_IN}, $action->{GRAMMEMS_OUT});
            push @{$word->{APPLIED_RULES}}, $rule->{ID};
        }
        elsif ($action->{TYPE} == ACTION_TYPE_SPLIT) {
            #any rule with SPLIT must be last
            $rule->{IS_LAST} = 1;
            print STDERR "    Split\n" if DEBUG;
            my @new_words = @{$word->split_lemma($action->{GRAMMEMS_IN})};
            if (scalar @new_words == 1) {
                warn "Warning: Splitting '".$word->{LEMMA}."' results in one word, skipping";
                return;
            }
            for my $new_word(@new_words) {
                push @{$new_word->{APPLIED_RULES}}, $rule->{ID};
                push @{$new_word->{APPLIED_RULES}}, @{$word->{APPLIED_RULES}} if $word->{APPLIED_RULES};
                $self->{WORD} = $new_word;
                $self->apply_rules();
                $self->update_gram_stats(1);
                print $self->{WORD}->to_string()."\n";
            }
            $self->{WORD} = undef;
        }
        elsif ($action->{TYPE} == ACTION_TYPE_GENERATE) {
            print STDERR "    Generate\n" if DEBUG;
            $word->generate_paradigm($action->{GRAMMEMS_IN});
            push @{$word->{APPLIED_RULES}}, $rule->{ID};
        }
        else {
            die "Error: Wrong ACTION_TYPE";
        }
    }
    return 1;
}
sub print_stats {
    my $self = shift;
    my %total = %{$self->{STATS}->{TOTAL}};
    my %applied = %{$self->{STATS}->{APPLIED}};
    my %gram_b = %{$self->{STATS}->{GRAM_BEFORE}};
    my %gram_a = %{$self->{STATS}->{GRAM_AFTER}};
    my $applied = 0;
    my $total = 0;
    print STDERR "=== PARADIGM STATS ===\n";
    for my $k(sort {$total{$b} <=> $total{$a}} sort {$a<=>$b} keys %total) {
        printf STDERR "PARA %-4s %6s of %6s\n", $k, $applied{$k}, $total{$k};
        $applied += $applied{$k};
        $total += $total{$k};
    }
    printf STDERR "TOTAL     %6s of %6s\n", $applied, $total;
    print STDERR "=== GRAMMEM STATS ===\n";
    for my $k(sort {$gram_b{$b} <=> $gram_b{$a}} sort {$a cmp $b} keys %gram_b) {
        printf STDERR "%12s %6s => %6s\n", $k, $gram_b{$k}, $gram_a{$k} ? $gram_a{$k} : 0;
    }
}
sub update_gram_stats {
    my $self = shift;
    my $type = shift;
    if ($type) {
        #after applying
        my %grams = %{$self->{WORD}->get_all_grammems()};
        for my $gram(keys %grams) {
            $self->{STATS}->{GRAM_AFTER}->{$gram} += $grams{$gram};
        }
    }
    else {
        #before applying
        my %grams = %{$self->{WORD}->get_all_grammems()};
        for my $gram(keys %grams) {
            $self->{STATS}->{GRAM_BEFORE}->{$gram} += $grams{$gram};
        }
    }
}

1;
