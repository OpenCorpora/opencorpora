package OpenCorpora::Dict::Importer;

use strict;
use warnings;
use utf8;
use DBI;

use Getopt::constant (
    'DEBUG' => 0,
    'STOP_AFTER' => 0,
    'CONFIG' => '',
    'INSERT' => 0,
    'QUIET' => 0,
    'SORT_GRAMMEMS' => 0,
    'PRINT_STATS' => 0
);

use OpenCorpora::Dict::Importer::Word;

use constant RULE_TYPE_ALL => 1;
use constant RULE_TYPE_ANY => 2;
use constant RULE_TYPE_GLOBAL => 3;
use constant COND_TYPE_ALL => 1;
use constant COND_TYPE_ONE => 2;
use constant COND_TYPE_NUM => 3;
use constant COND_TYPE_PARA => 4;
use constant REL_TYPE_AND => 1;
use constant REL_TYPE_OR => 2;
use constant ACTION_TYPE_CHANGE => 1;
use constant ACTION_TYPE_SPLIT => 2;
use constant ACTION_TYPE_GENERATE => 3;
use constant ACTION_TYPE_LINK => 4;

sub new {
    print STDERR "Creating Importer\n" if DEBUG && !INSERT;
    my($class, %args) = @_;
 
    my $self = {};
    $self->{RULES} = undef;
    $self->{WORD} = undef;
    $self->{STATS} = undef;
    $self->{CONNECTION} = undef;
    $self->{CONNECTION_LEMMA} = undef;
    $self->{CONNECTION_REVISION} = undef;
    $self->{CONNECTION_LINKTYPE} = undef;
    $self->{CONNECTION_LINK} = undef;
    $self->{CONNECTION_LINKREV} = undef;
    $self->{WORD_ID} = 1;
    $self->{BASE_WORD_ID} = undef;
    $self->{LINK_TYPES} = undef;    #all existing link types so far (in order not to ask the DB if it has one)
    $self->{GRAM_ORDER} = undef;
    $self->{BAD_LEMMA_GRAMMEMS} = undef;

    bless $self;
    #return is implicit in bless
}
sub sql_connect {
    my $self = shift;
    my %mysql;
    open F, CONFIG or die "Error: Cannot read ".CONFIG;
    while(<F>) {
        if (/\$config\['mysql_(\w+)'\]\s*=\s*'([^']+)'/) {
             $mysql{$1} = $2;
         }
    }
    close F;
    my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
    $dbh->do("SET NAMES utf8");
    $self->{CONNECTION} = $dbh;
}
sub prepare_insert {
    my $self = shift;
    my $dbh = $self->{CONNECTION};
    my $newset = $dbh->prepare("INSERT INTO `rev_sets` VALUES(NULL, ?, ?, ?)");
    $newset->execute(time(), 0, 'Dictionary import') or die $DBI::errstr;
    my $set_id = $dbh->{'mysql_insertid'} or die $DBI::errstr;
    print STDERR "Created revision set #$set_id\n" unless QUIET;
    my $max = $dbh->prepare("SELECT MAX(`lemma_id`) AS m FROM `dict_lemmata`");
    $max->execute() or die $DBI::errstr;
    my $r = $max->fetchrow_hashref();
    $self->{BASE_WORD_ID} = $r->{'m'} ? $r->{'m'} : 0;
    my $newlemma = $dbh->prepare("INSERT INTO `dict_lemmata` VALUES(NULL, ?)");
    my $newrev = $dbh->prepare("INSERT INTO `dict_revisions` VALUES(NULL, '$set_id', ?, ?, '0', '0')"); #null, set, lemma, text, null
    my $newlinktype = $dbh->prepare("INSERT INTO `dict_links_types` VALUES(NULL, ?)");
    my $newlink = $dbh->prepare("INSERT INTO `dict_links` VALUES(NULL,?, ?, ?)");
    my $newlinkrev = $dbh->prepare("INSERT INTO `dict_links_revisions` VALUES(NULL, '$set_id', ?, ?, ?, '1')");
    $self->{CONNECTION_LEMMA} = $newlemma;
    $self->{CONNECTION_REVISION} = $newrev;
    $self->{CONNECTION_LINKTYPE} = $newlinktype;
    $self->{CONNECTION_LINK} = $newlink;
    $self->{CONNECTION_LINKREV} = $newlinkrev;
}
sub read_grammem_order {
    my $self = shift;
    my $gram = $self->{CONNECTION}->prepare("SELECT inner_id FROM gram ORDER BY orderby");
    $gram->execute();
    my $i = 0;
    while(my $r = $gram->fetchrow_hashref()) {
        $self->{GRAM_ORDER}->{$r->{'inner_id'}} = $i++;
    }
}
sub read_bad_lemma_grammems {
    my $self = shift;
    my $path = shift;

    open F, $path;
    binmode (F, ':utf8');
    while(<F>) {
        next unless /\S/;
        next if /^\s*#/;
        chomp;
        my ($pos, $gr) = split /\t/;
        $self->{BAD_LEMMA_GRAMMEMS}->{$pos}->{$gr} = 1;
    }
    close F;
}
sub read_aot {
    my $self = shift;
    my $path = shift;
    my @forms;
    my $para_no = undef;
    my $counter;

    #connecting to mysql server
    if (CONFIG) {
        $self->sql_connect();
        if (INSERT) {
            $self->prepare_insert();
        }
        if (SORT_GRAMMEMS) {
            $self->read_grammem_order();
        }
    }

    open F, $path or die "Error: Cannot read $path";
    binmode(F, ':utf8');
    while(<F>) {
        if (/^\S+\t\S+,?\s?(?:\S+)?,?\s?(?:\S+)?/) {
            print STDERR $_ if DEBUG && !INSERT;
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
            if ($self->{WORD}->{LEMMA} && !$self->{WORD}->is_to_delete()) {
                $self->update_gram_stats(1);
                $self->print_or_insert();
            }
            @forms = ();
            $para_no = undef;
            ++$counter;
            print STDERR "====================\n" if DEBUG && !INSERT;
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
        if ($self->{WORD}->{LEMMA} && !$self->{WORD}->is_to_delete()) {
            $self->update_gram_stats(1);
            $self->print_or_insert();
        }
    }
    close F;
    if (PRINT_STATS) {
        $self->print_stats();
    } else {
        print STDERR "\n";
    }
}
sub read_rules {
    print STDERR "Reading rules\n" if DEBUG && !INSERT;
    my $self = shift;
    my $path = shift;
    my $rule_ref = undef;
    open F, $path or die "Error: Cannot read $path";
    binmode(F, ':utf8');
    my $string_no = 0;
    while(<F>) {
        $string_no++;
        s/^\x{feff}//;  #killing BOM
        if (/^\s*\#/) {
            next; #skipping comments
        }
        if (/^\s*$/) {
            next; #skipping blank lines
        }
        if (/^((?:\*|1).*|\(.+)/) {
            #this is a condition
            #adding the previous rule if it exists
            if ($rule_ref) {
                push @{$self->{RULES}}, $$rule_ref;
                print STDERR "Reading rule #".$#{$self->{RULES}}."\n" if DEBUG && !INSERT;
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
        elsif (/[\s\t]+((?:CHANGE|SPLIT|GENERATE|LINK)\s*\(.+\))/i) {
            #this is an action
            my $action = parse_action_string($1);
            $action->{STRING_NO} = $string_no;
            $action->{RULE_NO} = $$rule_ref->{ID};
            if ($action->{TYPE} == ACTION_TYPE_LINK) {
                my $last_action = $$rule_ref->{ACTIONS}->[$#{$$rule_ref->{ACTIONS}}];
                if ($last_action->{TYPE} != ACTION_TYPE_SPLIT) {
                    die "Error: LINK not after SPLIT";
                }
                push @{$last_action->{LINKS}}, $action;
            } else {
                push @{$$rule_ref->{ACTIONS}}, $action;
            }
        }
        else {
            die "Error: Bad line in rules: $_";
        }
    }
    #the last rule
    if ($rule_ref) {
        push @{$self->{RULES}}, $$rule_ref;
        print STDERR "Reading rule #".$#{$self->{RULES}}."\n" if DEBUG && !INSERT;
    }
}
sub parse_condition_string {
    my $rule = shift;
    my $str = shift;
    chomp $str;
    $str =~ s/\s+$//;
    if ($str =~ s/\s+L\s*$//i) {
        #there's a flag indicating that the rule will be the last
        $rule->{IS_LAST} = 1;
    }
    if ($str =~ /^\*\s*$/) {
        $rule->{TYPE} = RULE_TYPE_GLOBAL;
        return $rule;
    }
    elsif ($str !~ /\(/) {
        $rule->{TYPE} = RULE_TYPE_ALL;
        push @{$rule->{CONDITIONS}}, parse_simple_condition($str);
        return $rule;
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
    if ($str =~ /^\#(\d+)$/) {
        $cond->{PARA_NO} = $1;
        $cond->{TYPE} = COND_TYPE_PARA;
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
        @gram_in = split /,/, $1 if defined $1;
        @gram_out = split /,/, $2 if defined $2;
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
    elsif($str =~ /LINK\s*\((.+)\)/i) {
        $action->{TYPE} = ACTION_TYPE_LINK;
        my ($i, $j, $link_name) = split /,/, $1;
        if (!defined $j) {
            #if short syntax was used
            $link_name = $i;
            $i = 0;
            $j = 1;
        }
        $i =~ s/\s//g;
        $j =~ s/\s//g;
        if ($i !~ /^\d+$/ || $j !~ /^\d+$/) {
            die "Error: not a number: $str";
        }
        $link_name =~ s/\s//g;
        $action->{INDEX1} = $i;
        $action->{INDEX2} = $j;
        $action->{LINK_NAME} = $link_name;
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
    print STDERR "Applying rules to ".$self->{WORD}->{LEMMA}.' ('.$self->{WORD}->get_form_count()." forms)\n" if DEBUG && !INSERT;
    GF:for my $rule(@{$self->{RULES}}) {
        print STDERR "Checking rule ".$rule->{ID}."\n" if DEBUG && !INSERT;
        if ($rule->{TYPE} == RULE_TYPE_GLOBAL) {
            print STDERR "Global, applying\n" if DEBUG && !INSERT;
            my $res = $self->apply_rule($rule);
            last GF if $rule->{IS_LAST};
        }
        elsif ($rule->{TYPE} == RULE_TYPE_ALL) {
            my $test = 1;
            for my $c(@{$rule->{CONDITIONS}}) {
                print STDERR "Condition check\n" if DEBUG && !INSERT;
                if (!$self->test_condition($c)) {
                    $test = 0;
                    print STDERR "Condition check failed\n" if DEBUG && !INSERT;
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
                print STDERR "Condition check\n" if DEBUG && !INSERT;
                if ($self->test_condition($c)) {
                    print STDERR "Condition check ok\n" if DEBUG && !INSERT;
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
    if ($cond->{TYPE} == COND_TYPE_PARA) {
        if (defined $word->{PARADIGM_NO} && $cond->{PARA_NO} == $word->{PARADIGM_NO}) {
            return 1;
        }
        return 0;
    }
    if ($cond->{TYPE} == COND_TYPE_ONE) {
        print STDERR "  COND_TYPE_ONE\n" if DEBUG && !INSERT;
        for my $form(@{$word->{FORMS}}) {
            #print STDERR "  testing form ".$form->{TEXT}."\n";
            if (($cond->{REL} == REL_TYPE_AND && $word->form_has_all_grams($form, $cond->{GRAMMEMS})) ||
                ($cond->{REL} == REL_TYPE_OR && $word->form_has_any_gram($form, $cond->{GRAMMEMS}))) {
                print STDERR "OK\n" if DEBUG && !INSERT;
                return 1;
            }
        }
        return 0;
    }
    if ($cond->{TYPE} == COND_TYPE_ALL) {
        print STDERR "COND_TYPE_ALL\n" if DEBUG && !INSERT;
        for my $form(@{$word->{FORMS}}) {
            #print STDERR "  testing form ".$form->{TEXT}."\n";
            if (($cond->{REL} == REL_TYPE_AND && !$word->form_has_all_grams($form, $cond->{GRAMMEMS})) ||
                ($cond->{REL} == REL_TYPE_OR && !$word->form_has_any_gram($form, $cond->{GRAMMEMS}))) {
                print STDERR "Fail\n" if DEBUG && !INSERT;
                return 0;
            }
        }
        return 1;
    }
    die "Error: Wrong COND_TYPE";
}
sub apply_rule {
    my $self = shift;
    my $rule = shift;
    my $word = $self->{WORD};
    if ($word->rule_applied($rule)) {
        print STDERR "    Rule already applied, skipping\n" if DEBUG && !INSERT;
        return 0;
    }
    print STDERR "    Applying rule ".$rule->{ID}."\n" if DEBUG && !INSERT;
    for my $action(@{$rule->{ACTIONS}}) {
        if ($action->{TYPE} == ACTION_TYPE_CHANGE) {
            print STDERR "    Change\n" if DEBUG && !INSERT;
            $word->change_grammems($action->{GRAMMEMS_IN}, $action->{GRAMMEMS_OUT});
            push @{$word->{APPLIED_RULES}}, $rule->{ID};
        }
        elsif ($action->{TYPE} == ACTION_TYPE_SPLIT) {
            #any rule with SPLIT must be last
            $rule->{IS_LAST} = 1;
            print STDERR "    Split\n" if DEBUG && !INSERT;
            my @new_words = @{$word->split_lemma($action)};
            if (scalar @new_words == 1) {
                printf STDERR "[rule %d, string %d] Warning: Splitting '%s' results in one word, skipping\n",
                    $rule->{ID}, $action->{STRING_NO}, $word->{LEMMA};
                return;
            }
            #preliminary calculations
            my %absent;
            for my $i(0..$#new_words) {
                $new_words[$i] || ($absent{$i} = 1);
            }
            #setting links
            for my $lnk(@{$action->{LINKS}}) {
                my ($i, $j) = ($lnk->{INDEX1}, $lnk->{INDEX2});
                my $j1 = modify_index($j, \%absent);
                if (defined $new_words[$i] && defined $new_words[$j]) {
                    my @for_i = ($self->{WORD_ID} + $j1, $lnk->{LINK_NAME});
                    push @{$new_words[$i]->{LINKS}}, \@for_i;
                }
                elsif (DEBUG) {
                    printf STDERR "[rule %d, string %d] Warning: Cannot link after splitting '%s' with indices %d and %d; this may be normal\n",
                        $rule->{ID}, $action->{STRING_NO}, $word->{LEMMA}, $i, $j;
                }
            }
            for my $new_word(@new_words) {
                next unless $new_word; #this can be if some grammems were not found but split was ok
                push @{$new_word->{APPLIED_RULES}}, $rule->{ID};
                push @{$new_word->{APPLIED_RULES}}, @{$word->{APPLIED_RULES}} if $word->{APPLIED_RULES};
                $self->{WORD} = $new_word;
                $self->apply_rules();
                $self->update_gram_stats(1);
                $self->print_or_insert();
            }
            $self->{WORD} = undef;
        }
        elsif ($action->{TYPE} == ACTION_TYPE_GENERATE) {
            print STDERR "    Generate\n" if DEBUG && !INSERT;
            $word->generate_paradigm($action);
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
sub print_or_insert {
    my $self = shift;
    $self->{WORD}->sort_grammems($self->{GRAM_ORDER}) if SORT_GRAMMEMS;
    if (INSERT) {
        $self->{CONNECTION_LEMMA}->execute($self->{WORD}->{LEMMA}) or die $DBI::errstr;
        $self->{CONNECTION_REVISION}->execute($self->{CONNECTION}->{'mysql_insertid'}, $self->{WORD}->to_xml($self->{BAD_LEMMA_GRAMMEMS})) or die $DBI::errstr;
        print STDERR "Committed revision ".$self->{CONNECTION}->{'mysql_insertid'}."\r" unless QUIET;
        # links
        my $link_typeid;
        for my $lnk(@{$self->{WORD}->{LINKS}}) {
            my ($link_to, $link_name) = @$lnk;
            if (!defined $self->{LINK_TYPES} || !exists $self->{LINK_TYPES}->{$link_name}) {
                $self->{CONNECTION_LINKTYPE}->execute($link_name) or die $DBI::errstr;
                $link_typeid = $self->{CONNECTION}->{'mysql_insertid'};
                $self->{LINK_TYPES}->{$link_name} = $link_typeid;
            } else {
                $link_typeid = $self->{LINK_TYPES}->{$link_name};
            }
            $self->{CONNECTION_LINK}->execute($self->{BASE_WORD_ID} + $self->{WORD_ID}, $self->{BASE_WORD_ID} + $link_to, $link_typeid);
            $self->{CONNECTION_LINKREV}->execute($self->{BASE_WORD_ID} + $self->{WORD_ID}, $self->{BASE_WORD_ID} + $link_to, $link_typeid);
        }
    } else {
        print $self->{WORD_ID}."\n";
        print $self->{WORD}->to_string()."\n";
    }
    $self->{WORD_ID}++;
}
sub modify_index {
    my ($j, $ref) = @_;
    my %h = %$ref;
    my $j1 = 0;
    for my $k(sort {$a <=> $b} keys %h) {
        $j1-- if $k<$j;
    }
    return $j + $j1;
}

1;
