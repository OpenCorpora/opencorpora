#!/usr/bin/perl
use strict;
use utf8;
use open ':std', ':encoding(UTF-8)';
use Data::Dumper;

#на вход четыре аргумента -файл с  морф. разметкой, файл с id ИГ, файл с парами антецедент-анафора и распарсеный MaltParser'ом текст 

my @s, my $arg, my @np, my @evth,my @g, my $key;
my $max_left = 0;
open (F, @ARGV[0]); # хеш вида номер токена -> токен
@s=<F>;

my @a, my %hash, my $line, my $i, my $r, my %groups, my %result,my %h;
my $i = 0;
foreach $line (@s){
        chomp $line;
        @a = split (/\t/, $line);
	if (@a[1] ne ""){
        	#%hash = (%hash, $i, $a[1]); #
                $hash{$i} = $a[1];
                
		%h = (%h, $i, $a[0]);
#		print "morph -> $h{$i}\n";
		$i++;
	}
}
$max_left = $i;

open(FIL, @ARGV[1]); #хеш вида номер токена ИГ -> номер ИГ
foreach $line (@g=<FIL>){
	chomp ($line);
	@evth = split (/\t/, $line);

	 @np = split (/\,/, @evth[1]);
               foreach $arg(@np){ 
                       %groups = (%groups,$arg,@evth[0]);
#		       print "goups -> $groups{$arg}\n";

		}
}

#print Dumper(\%groups);
#die;
open(FILE, @ARGV[3]); # выбор существительных и местоимений, сопоставление им номера токена, принадлежность к ИГ и является или нет предик.
$r="";
my @right_file;
foreach $line (@s=<FILE>) {
    chomp $line;
    my @all = split (/\t/, $line);
    push @right_file, \@all;
}

my @idx;
my ($l, $r) = (0, 0);
while ($r <= $#right_file && $l < $max_left) { 
    #if (length($all[1]) < 1) { die "length($all[1]) < 1"); }
    my ($left_word, $right_word) = ($hash{$l}, $right_file[$r]->[1]);
    my ($left_markup, $right_markup) = ($l, $r);
    if ($hash{$l} eq $right_file[$r]->[1]) {
      #
    } elsif (length($hash{$l}) < length($right_file[$r]->[1])) {
      my $lw = $hash{$l};
      while ($lw ne $right_file[$r]->[1]) {
        $l++;
        $lw .= $hash{$l};
      }
    } elsif (length($hash{$l}) > length($right_file[$r]->[1])) {
      my $rw = $right_file[$r]->[1];
      while ($rw ne $hash{$l}) {
        $r++;
        $rw .= $right_file[$r]->[1];
      }
    } else {
 #     print "$hash{$l} $l <-> $right_file[$r]->[1] $r\n";
  #    print Dumper($right_file[$r]);
      die;
    }

    #print "$l <-> $r / $left_word <-> $right_word\n";
    push @idx, [ $l, $r ];
    $l++; $r++;
}

foreach my $ri (@idx) {
    if ($right_file[$ri->[1]]->[7] eq "предик" ) {
    #     print join(" ", @{$right_file[$ri->[1]]}) . "\n";
    #     print $ri->[0] . " " . $h{$ri->[0]} . " " . $groups{$h{$ri->[0]}} . "\n"; 
         $result{$groups{$h{$ri->[0]}}} = 1;
#	 print "result -> $groups{$h{$ri->[0]}}\n";
    }
    elsif ($right_file[$ri->[1]]->[7] ne "предик"){
         $result{$groups{$h{$ri->[0]}}} = 0;
#	 print "result -> $groups{$h{$ri->[0]}}\n";
    }

}


open(FL, @ARGV[2]);  
my @p, my @d;
foreach $line (@p=<FL>){
        chomp $line;
        $line =~ s/[\n\r]+//g;
        @d = split(/__/, $line);
        print "$d[0]__$d[1]\t$result{$d[0]}\t$result{$d[1]}\n"
}

#print Dumper(\%result);
#die;
#foreach $key(sort{$a <=> $b} keys %result){
#	print "$key\t$result{$key}\n"
#}



