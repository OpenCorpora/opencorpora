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

sub new {
    my($class, %args) = @_;
 
    my $self = {};
    $self->{RULES} = undef;
 
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
            @forms = ();
        }
    }
    close F;
}
sub read_rules {
    my $self = shift;
    my $path = shift;
    my $rule_ref = undef;
    open F, $path or die "Cannot read $path";
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
            }
            #new rule
            my $rule = {};
            $rule->{CONDITIONS} = undef;
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
            die "Bad line in rules: $_";
        }
    }
    #the last rule
    if ($rule_ref) {
        push @{$self->{RULES}}, $$rule_ref;
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
                    die "Condition type mismatch in: $_";
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
    }
    if ($str =~ /\|/ && $str =~ /\&/) {
        die "Condition must contain either conjunction or disjunction: $str";
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
        die "Bad action string: $str";
    }
    return $action;
}

1;
