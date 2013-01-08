use strict;

my @names;
my @files;

foreach my $arg (@ARGV) {
  push @names, $arg;

  my %h;
  $h{before} = load_tab_markup($arg . ".orig");
  $h{after} = load_tab_markup($arg . ".final");
  
  push @files, \%h;
} 

for (my $i = 0; $i <= $#files; $i++) {
  my %cmp_result = compare_markup($files[$i]->{before}, $files[$i]->{after});
  print "$names[$i] "
        . $#{$files[$i]->{before}} 
        . " " . $cmp_result{ndiff}
        . " " . $cmp_result{ndiff_percent}
        . "\n";

  for (my $j = 0; $j <= $#{$cmp_result{diff}->{1}}; $j++) {
    print STDERR $cmp_result{diff}->{1}->[$j] . "\n";
    print STDERR $cmp_result{diff}->{2}->[$j] . "\n";
    print STDERR "\n";
  }
}

sub compare_markup {
  my ($rm1, $rm2) = @_;
  my %r;

  if ($#{$rm1} != $#{$rm2}) {
    die "$#{$rm1} != $#{$rm2}";
  }

  my $n = 0;

  for (my $i = 0; $i <= $#{$rm1}; $i++) {
    if ($rm1->[$i]->{str} ne $rm2->[$i]->{str}) {
      $n += 1;
      push @{$r{diff}->{1}}, $rm1->[$i]->{str};
      push @{$r{diff}->{2}}, $rm2->[$i]->{str};
    }
  }

  $r{ndiff} = $n;
  $r{ndiff_percent} = $n / $#{$rm1};

  return %r;
}

sub load_tab_markup {
  my ($fn) = @_;
  my @m;

  open(F, "< $fn") || die "can't open $fn";
  while (<F>) {
    chomp $_;
    if ($_ =~ /^$/) { next; }

    my %h;
    
    $h{str} = $_;
    if ($_ =~ /^(\d+)/) {
      $h{tid} = $1;
    }

    push @m, \%h;
  }
  close(F);

  return \@m;
}
