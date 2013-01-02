use strict;

my @files;
my @names;
#my %file2corpus;

foreach my $arg (@ARGV) {
  push @names, $arg;
  push @files, load_rules($arg);
  if ($arg =~ /^(.*?)\/rand\/(\d+)\//) {
    my $corpus_fn = $1 . "/rand" . $2 . ".tab";
    print STDERR "$corpus_fn " . count_tokens($corpus_fn) . " " . $#{$files[$#files]}. "\n";
  }
}

for (my $i = 0; $i <= $#files; $i++) {
  for (my $j = $i + 1; $j <= $#files; $j++) {

    print "$names[$i] $names[$j] " 
          . join(" ", intersect($files[$i], $files[$j])) . " " 
          . spearman($files[$i], $files[$j]) . "\n";
  }
}

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
    die "append_missing failed";
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
    die "leave common subsets failed";
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
    }
  }
  close(F);

  return \@lines;
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
