#!/usr/bin/perl
use strict;
use utf8;

open (F, "../ana_test.tab"); # хеш вида номер токена -> токен
@s=<F>;
my @a, %hash;
foreach $line (@s){
        @a = split (/\t/, $line);
	if (@a[1] ne ""){
        	%hash = (%hash, @a[0], @a[1]);
	}
}


open(FIL, '../ana_test.groups'); #хеш вида номер токена ИГ -> номер ИГ
foreach $line (@g=<FIL>){
	chomp ($line);
	@evth = split (/\t/, $line);

	 @np = split (/\,/, @evth[1]);
               foreach $arg(@np){ 
                       %groups = (%groups,$arg,@evth[0]);
               }
}

open(FILE, 'txt.parse'); # выбор существительных и местоимений, сопоставление им номера токена, принадлежность к ИГ и является или нет предик.
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
				if (@all[7] eq "предик") {
#					print "$i\t@all[1]\t$groups{$i}\t1\n";
					%result = (%result, $groups{$i}, 1); 
					
	               		}
                		else {
 #                       		print "$i\t@all[1]\t$groups{$i}\t0\n";
					unless (exists($result{$groups{$i}})){ %result = (%result, $groups{$i}, @all[7]);} 
		  		}
			
			}
		}
		else{
		if (@all[7] eq "предик"){
		#	print "$i\t@all[1]\t$groups{$i}\t1\n";
			 %result = (%result, $groups{$i}, 1);
		}
		}
       			$i++; 
	}
}

foreach $key(sort{$a <=> $b} keys %result){
	print "$key\t$result{$key}\n"
}



