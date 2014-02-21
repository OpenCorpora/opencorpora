#!/usr/bin/perl
use strict;
use utf8;
use open ':std', ':encoding(UTF-8)';

#на вход три аргумента -файл с  морф. разметкой, файл с id ИГ и распарсеный MaltParser'ом текст 

my @s, my $arg, my @np, my @evth,my @g, my $key;
open (F, @ARGV[0]); # хеш вида номер токена -> токен
@s=<F>;

my @a, my %hash, my $line, my $i, my $r, my @all, my %groups, my %result;

foreach $line (@s){
        @a = split (/\t/, $line);
	if (@a[1] ne ""){
        	%hash = (%hash, @a[0], @a[1]);
	}
}


open(FIL, @ARGV[1]); #хеш вида номер токена ИГ -> номер ИГ
foreach $line (@g=<FIL>){
	chomp ($line);
	@evth = split (/\t/, $line);

	 @np = split (/\,/, @evth[1]);
               foreach $arg(@np){ 
                       %groups = (%groups,$arg,@evth[0]);
               }
}

open(FILE, @ARGV[2]); # выбор существительных и местоимений, сопоставление им номера токена, принадлежность к ИГ и является или нет предик.
$i = 1;
$r="";
foreach $line (@s=<FILE>){
	
        @all = split (/\t/, $line);
	
        if (@all[1] ne ""){
	       if ((@all[3] eq "N") | (@all[3] eq "P")){
			
			if ($hash{$i} ne @all[1]) {$r= $r.@all[1];} #сведение токенизации opencorpora и тритеггера

			if ($hash{$i} eq $r) {@all[1]=$r;$r="";}

                	if ( $hash{$i} eq  @all[1]) {

				unless(exists($groups{$i})) {$groups{$i} = "-"} 
				if (@all[7] eq 'предик') {
	#				print "$i\t@all[1]\t$groups{$i}\t@all[7]\t1\n";
					%result = (%result, $groups{$i}, 1); 
					
	               		}
                		else {
	#				  		print "$i\t@all[1]\t$groups{$i}\t@all[7]\t0\n";
					unless (exists($result{$groups{$i}})){ %result = (%result, $groups{$i}, 0);} 
		  		}
			
			}
		}
		else{
		if (@all[7] eq 'предик'){
	#	print "$i\t@all[1]\t$groups{$i}\t@all[7]\t1\n";
			 %result = (%result, $groups{$i}, 1);
		}
		}
       			$i++; 
	}
}

foreach $key(sort{$a <=> $b} keys %result){
	print "$key\t$result{$key}\n"
}



