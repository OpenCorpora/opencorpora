use strict;
use utf8;
use Data::Dumper;
use List::Util qw(min);
#use Algorithm::NeedlemanWunsch;

#print levenshtein([split //, "mwma"], [split //, "mama"]) ."\n";

my @files;
my @names;
my $edit_distance = 0;
#my %file2corpus;

foreach my $arg (@ARGV) {
  push @names, $arg;
  #push @files, load_rules($arg);
  my $rules = load_rules($arg);
  print Dumper(\build_tree($rules)) . "\n";
die;
  push @files, remove_duplicates($rules);
  if ($arg =~ /^(.*?)\/rand\/(\d+)\//) {
    my $corpus_fn = $1 . "/rand" . $2 . ".tab";
    print STDERR "$corpus_fn " . count_tokens($corpus_fn) . " " . $#{$files[$#files]}. "\n";
  }
}

sub score_sub {
  if (!@_) {
    return -2; # gap penalty
  }

  return ($_[0] eq $_[1]) ? 1 : -1;
}

#my $matcher = Algorithm::NeedlemanWunsch->new(\&score_sub);

sub spearman {
  my ($_ra, $_rb, $append_missing) = @_;
  my %h;
  my $c = 0;
  my ($ra, $rb) = (undef, undef);
  if (defined $append_missing) {
    ($ra, $rb) = append_missing($_ra, $_rb);
  } else {
    ($ra, $rb) = leave_common_subsets($_ra, $_rb);
  }

  for (my $i = 0; $i <= $#{$ra}; $i++) {
    $h{$ra->[$i]}->{a} = $i;
  }

  for (my $i = 0; $i <= $#{$rb}; $i++) {
    $h{$rb->[$i]}->{b} = $i;
  }

  my $sd = 0;
  my $n = 0;
  my ($ma, $mb) = (0, 0);
  foreach my $k (sort {$h{$a}->{a} <=> $h{$b}->{a}} keys %h) {
    if (exists $h{$k}->{a} && exists $h{$k}->{b}) {
      $sd += ($h{$k}->{a} - $h{$k}->{b}) * ($h{$k}->{a} - $h{$k}->{b});
      $n += 1;

#      print STDERR "$k\t$h{$k}->{a}\t$h{$k}->{b}\t" . ($h{$k}->{a} - $h{$k}->{b}) . "\n";
    } elsif (! exists $h{$k}->{a}) {
#      print "k $k\n";
      $ma += 1;
    } elsif (! exists $h{$k}->{b}) {
#      print "k $k\n";
      $mb += 1;
    }
  }

#  print "$sd $ma $mb\n";

  return 1 - (6 * $sd) / ( $n * ( $n * $n - 1) );
}

sub intersect {
  my ($ra, $rb) = @_;
  my %h;

  foreach my $r (@{$ra}) {
    $h{$r} += 1;
  }

  foreach my $r (@{$rb}) {
    $h{$r} += 1;
  }

  my $n = 0;
  foreach my $r (keys %h) {
    if (2 == $h{$r}) { 
      $n += 1;
    }
  }

  return ($n / (scalar keys %h)), $n;
}

sub append_missing {
  my ($ra, $rb) = @_;
  my (@na, @nb);
  @na = @{$ra};
  @nb = @{$rb};

  my %ha = map { $_ => 1 } @na;
  my %hb = map { $_ => 1 } @nb;

  my @missing_a;
  foreach my $x (@nb) {
    if (! exists($ha{$x})) {
      push @missing_a, $x;
    }
  }

  my @missing_b;
  foreach my $x (@na) {
    if (! exists($hb{$x})) {
      push @missing_b, $x;
    }
  }

#  print $#na . " " . $#missing_a . " " . $#nb . " " . $#missing_b . "\n";

  push @na, @missing_a;
  push @nb, @missing_b;

  if ($#na != $#nb) {
    die "append_missing failed: $#na != $#nb";
  }

  return (\@na, \@nb);
}

sub leave_common_subsets {
  my ($ra, $rb) = @_;
  my (@na, @nb);

  my %ha = map { $_ => 1 } @{$ra};
  my %hb = map { $_ => 1 } @{$rb};

  foreach my $x (@{$ra}) {
    if (exists $hb{$x}) {
      push @na, $x;
    }
  }

  foreach my $x (@{$rb}) {
    if (exists $ha{$x}) {
      push @nb, $x;
    }
  }

  if ($#na != $#nb) {
    die "leave common subsets failed: $#na != $#nb";
  }

  return (\@na, \@nb);
}

sub load_rules {
  my ($fn) = @_;
  my @lines;

  open(F, "< $fn");
  while (<F>) { 
    chmod $_; 
    if ($_ =~ /Change/) {
      push @lines, convert_rule($_);
    } elsif ($_ =~ /->/) {
      push @lines, $_;
    }
  }
  close(F);

  return \@lines;
}

sub remove_duplicates {
  my ($ra) = @_;
  my %h;
  my @r;

  foreach my $s (@{$ra}) {
    if (exists $h{$s}) { next; }
    push @r, $s;
    $h{$s} += 1;
  }

  return \@r;
}

sub build_tree {
  my ($ra) = @_;
  my %h;
  my @res;
  my $rcurr_array = \@res;
  my $last_to = undef;
  my @to_stack;
  my @parent_stack;

  foreach my $str (@{$ra}) {
    if (! exists $h{$str}) {
      print "unknown\n";
      @to_stack = (); @parent_stack = (); $rcurr_array = \@res;
     
      my %rule_node = ( rule => $str, children => [] );
      push @{$rcurr_array}, \%rule_node; #$str;
      $h{$str} += 1;
      if ($str =~ /->\s*([^\s]+)/) {
        $last_to = $1;
      } else {
        die "can't parse To in \"$str\"";
      }
    } else {
      print "known\n";
      if ($str =~ /|(.*)$/) {
        my $cond = $1;
        print "last_to = \"$last_to\"\n";
        while (defined $last_to) {
          if ($last_to =~ /$cond/) {
            print "PUSH\n";
            # do PUSH
            push @to_stack, $last_to;
            $rcurr_array = $rcurr_array->[$#{$rcurr_array}]->{children};
 
            my %rule_node = ( rule => $str, children => [] );
            push @{$rcurr_array}, \%rule_node; #$str;
            $h{$str} += 1;
            if ($str =~ /->\s*([^\s]+)/) {
              $last_to = $1;
            } else {
              die "can't parse To in \"$str\"";
            }
   
            #push_new_rule($str, $rcurr_array, \@to_stack);
            last;
          } else {
            
          }

          # do POP
          $last_to = pop @to_stack;
          $rcurr_array = pop @parent_stack;
        }
      } else {
        die "can't parse Condition in \"$str\"";
      }
    }
  }

  return @res;
}

sub push_new_rule {
  my ($str, $rcurr_array, $rto_stack) = @_;

  my %rule_node = ( rule => $str, children => [] );
  push @{$rcurr_array}, \%rule_node; #$str;

  if ($str =~ /->\s*([^\s]+)/) {
    push @{$rto_stack}, $1;
  } else {
    die "can't parse To in \"$str\"";
  }
}

sub count_tokens {
  my ($fn) = @_;
  my $n = 0;

  open(F, "< $fn") || die "can't open $fn";
  while (<F>) {
    if ($_ =~ /^[0-9]+/) {
      $n += 1;
    }
  }
  close(F);

  return $n;
}

sub convert_rule {
  my $s = $_;
  my $pos = undef;

  if ($s =~ /Change tag from (\S+) to (\S+) if (\S+) (\S+) is (\S+)/) {
    if ($3 eq "next") { $pos = "+1"; } elsif ($3 eq "previous") { $pos = "-1"; } else { die; }
    $s = "$1 -> $2 | $pos:$4=$5";
  } else {
    die "can't parse $s";
  }

  return $s;
}

use List::Util qw(min);
 
sub levenshtein {
    my ($ra1, $ra2) = @_;
    my @ar1 = @{$ra1}; #split //, $str1;
    my @ar2 = @{$ra2}; #split //, $str2;
 
    my @dist;
    $dist[$_][0] = $_ foreach (0 .. @ar1);
    $dist[0][$_] = $_ foreach (0 .. @ar2);
 
    foreach my $i (1 .. @ar1){
        foreach my $j (1 .. @ar2){
            my $cost = $ar1[$i - 1] eq $ar2[$j - 1] ? 0 : 1;
            $dist[$i][$j] = min(
                        $dist[$i - 1][$j] + 1, 
                        $dist[$i][$j - 1] + 1, 
                        $dist[$i - 1][$j - 1] + $cost );
        }
    }
 
    return $dist[@ar1][@ar2];
}
