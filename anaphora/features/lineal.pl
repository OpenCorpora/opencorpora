#! /usr/bin/perl
use strict;
use Encode;
use utf8;
use Getopt::Std;

my $start = time();

#NB! важен порядок внутри файла морфологии, чтоб не терять токены. 

#общий вид программы:
#считывание файлов групп и пар внутрь, морфология 
#запуск подпрограмм для подсчёта каждого типа значения
#было бы круто, если б пересчёт каждого типа значения можно было запускать из консоли, не влезая в код программы - параметры 

binmode (STDOUT,"encoding(utf8)");

my %opts;
getopts('p:g:m:h',\%opts);

#usage
if (!$opts{'m'} || !$opts{'g'} || !$opts{'p'}){
    print "\n\nUsage:\n
    $0 -m MORPH_FILE \n
    $0 -g GROUPS_FILE \n
    $0 -p PAIRS_FILE \n
    Additional:\t
	-h - count from head to head of NPs\n
       Default: from border to border of NP\n";
   exit;
}

my %pairs_TSV = read_pairs("<$opts{'p'}");
my ($groups_TSV, $tokens) = read_groups("<$opts{'g'}");
   

#печатает результаты в STDOUT 
count_dist("<$opts{'m'}", $groups_TSV, $tokens, \%pairs_TSV, $opts{'h'});

#print STDERR time() - $start."\n";   

########################################################################
sub read_groups{
   open my $f,$_[0] or die "cannot open file: $!";
   binmode ($f, ":encoding(utf8)");
   my %gr;
   my %t;
   while (<$f>){
       chomp;
       my ($group_id,$token,$main) = split/\t/, $_ ;
       my @tokens = split/,/, $token;
       if ($main =~ /ALL/){ $main = $tokens[0]; } #допущение для групп типа ФИО (мб от последнего надо)
       $gr{$group_id} = $main;
       $t{$group_id} = \@tokens; 
   }
   close $f; 
   return (\%gr, \%t);
}

sub read_pairs{
   open my $file,$_[0] or die "cannot open file: $!";
   binmode ($file, ":encoding(utf8)");
   #my @ar;
   my %hash;
   while (<$file>){
       chomp;
       my ($pair, $type) = split /\s+/, $_ ;
      # push @ar, $pair;
       $hash{$pair} = $type; 
   }
   close $file; 
   #return @ar;
   return %hash;
}

sub count_dist{

   my ($morph, $gr, $tok, $pr, $opt) = @_; 
   my %res = ();
   #my %g = %gr;
  # my @p = @$pr;
   my @p = sort {$a  <=> $b} keys %$pr;
   #my %t = %tok; 

   #while (my ($key, $val) = each $gr){
   #     print "G: $key --> $val \n";
   #} 
   
   open (MORPH, $morph) or die "cannot open file: $!";
   binmode (MORPH, ":encoding(utf8)");
   
   if ($opt) {
      print "Pair\tSobstH\tSentH\tanaH\tNH\tNPROH\tNNPROH\tantH\n";
   } else {
      print "Pair\tSobst\tSent\tana\tN\tNPRO\tNNPRO\tant\n";
   }

   for my $i(0..$#p){
      my ($ant,$ana) = split /_/, $p[$i];
      if ($ant > $ana){ ($ana,$ant) = ($ant,$ana); }
   #   my ($count,$count_ana,$count_ant,$count_N,$count_Sobst,$count_Sent,$count_NPRO,$count_NNPRO) = (0);
      my $count = 0;
      my $count_ana = 0;
      my $count_ant = 0;
      my $count_N = 0;
      my $count_Sobst = 0;
      my $count_Sent = 0;
      my $count_NPRO = 0;
      my $count_NNPRO = 0;
      my $start = 0;

      for my $j(0..$#p){
          my ($ant2,$ana2) = split /_/, $p[$j]; 
          if ($ant2 > $ana2){ ($ana2,$ant2) = ($ant2,$ana2); }
          if ($ant2 == $ant && $ana2 < $ana){
             $count_ana++;
          } 
         #подсчёт потенциальных антецедентов,когда будет перебор всех пар РВ
         # if ($ana2 == $ana && $ant < $ant2){
         #    $count_ant++;
         # } 
          
      }
     $count_ant = $ana - $ant - 1;
      
      my ($anaId, $antId); 
      if ($opt) { $antId = ${$gr}{$ant}; $anaId = ${$gr}{$ana}; } #print "AntId: $antId, AnaId: $anaId\n"; }     
      else { $antId = ${$tok}{$ant}->[-1]; $anaId = ${$tok}{$ana}->[0]; }
      
      seek(MORPH,0,0);
      M:while (my $s = <MORPH>){
          chomp;
          my @cur = split /\t/, $s;      
          if ($cur[0] == $antId){ $start = 1; next; }
          if ($cur[0] == $anaId){ 
             $count_NNPRO = $count_N + $count_NPRO;
             print "$p[$i]\t$count_Sobst\t$count_Sent\t$count_ana\t$count_N\t$count_NPRO\t$count_NNPRO\t$count_ant\n";
             last M;
          }
          if ($start == 1){
               if ($cur[2] =~ /Abbr|Name|Surn|Patr|Geox|Orgn|Trad/){ $count_Sobst++; }#имена_собств
               if ($cur[2] =~ /NOUN/){ $count_N++; } #существ
               if ($cur[0] =~ /^sent/i){ $count_Sent++; } #предложений
               if ($cur[2] =~ /NPRO/){ $count_NPRO++; } #местоимение-существит.
          }
      } 
   }

   close MORPH;
}

=comm
          if ($ant2 == $ant && $ana2 == $ana) {
             my @tmp = split/,/, $t{$ant};
             my @tmp2 = split/,/, $t{$ana};
             for my $k (0..$#tmp){ 
                 if ($tmp[$k] > $g{$ant}){
                    $count_ant++;
                 }
             }
             for my $l (0..$#tmp2){
                 if ($tmp2[$l] < $g{$ana}){
                   $count_ant++;
                 }
             }

      for my $key (sort keys %g){ #количество токенов в иг между анафорой и антецедентом 
          if ($key > $ant && $key < $ana){  
             my @tmp3 = split/,/, $t{$key};
             $count_ant = $count_ant + $#tmp3+1;
          }
      }
=cut      
