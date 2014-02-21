#!/usr/bin/perl
use strict;
use utf8;
use XML::Parser;
use Scalar::Util;

#binmode(STDIN, ':encoding(utf-8)');
binmode(STDOUT, ':encoding(utf-8)');

my $file = $ARGV[0] || die "$! \n usage: program XMLFile";
my $debug = 'debug.txt';
my $file_output = 'groups.txt';
binmode($file, ':encoding(utf-8)');

open (GROUPS, ">$file_output") or die "Error: $!";
binmode(GROUPS, ':encoding(utf-8)');
open (OUT, ">$debug") or die "Error: $!";
binmode(OUT, ':encoding(utf-8)');
print OUT "NE\t\tType\t\tMain\n"; 

#hash with types
my %types = (
   'базовая именная' => 1,
   'имя собственное' => 2,
   'количественная' => 3,
   'сложный предлог' => 4,
   'наречное выражение' => 5,
   'вводное выражение' => 6,
   'составной союз' => 7,
   'собственное наименование' => 8,
   'несобственное наименование' => 9,
   'приложение' => 10,
   'предложная' => 11,
   'перечисление' => 12,
   'сложная именная' => 13,
   'сложная количественная' => 14,
   'сложное местоимение' => 15,
   'специальная' => 16,
);

#xml с фактами
my $parser = XML::Parser->new(Handlers=>{Start => \&tag_start, End => \&tag_end, Char =>\&tag_text});
$parser->parsefile($file);
my %facts; #DocID - FactId - { {k=>v} ...}

#NB! Нужно попробовать меня саму группу на то, что в Лидах

#файлы с морфологией
my $morph_dir = 'morph_files';
my %morph; #fileID - SentNum - id => token
readDir($morph_dir);

#для групп
my %groups; #neID - gr.tokens => main id

my $neID = 1;
#сопоставление
while (my ($doc,$fact) = each(%facts)) {
    L:foreach my $f(keys %$fact) {
       my $sn = $facts{$doc}{$f}{'sn'};
       my $tmpNE = lc($facts{$doc}{$f}{'Self'});
       my $cur_type = lc($facts{$doc}{$f}{'Type'});
       my $cur_main = lc($facts{$doc}{$f}{'Main'});
      M: while (my ($ID,$token) = each(%{$morph{$doc}{$sn}})) {
          #print $tmpNE."\t$token\n";
          my $tk = lc($token);
          if ($tmpNE =~ /\Q$tk\E/) {
             #для однословных
             if (lc($tmpNE) eq lc($token)) {
               my $Mid;
               if ($cur_main eq "") { $Mid = 0; }
               elsif (uc($cur_main) eq "NONE" || uc($cur_main) eq "ALL") { $Mid = uc($cur_main); }
               else { $Mid = $ID; }
               if (exists $types{$cur_type}) { 
                  print GROUPS "$neID\t$ID\t$Mid\t".$types{$cur_type}."\n";
               } else { 
                  print GROUPS "$neID\t$ID\t$Mid\t".$cur_type."\n";
               }
             #  print GROUPS "$neID\t$tmpNE\t$cur_main\t".$cur_type."\n";
               $neID++;  
             } #для многословных
             elsif ($tmpNE =~ / /) {
		my @nes = split/ /, $tmpNE; 
                my ($ind, $ids, $mid);
                for my $i (0..$#nes){
                    if ($nes[$i] eq lc($token)) {
                       $ind = $i; last;
                    }
                }
		if ($ind eq "") {
                    next M;	
		}                
                elsif ($ind == 0){
#                print "in complex, ind is $ind\n";
                  $ids = $ID;
                  for my $k (1..$#nes){
		    if (lc($nes[$k]) eq lc($morph{$doc}{$sn}{($ID+$k)})) { #safe check 	
                       $ids .= ",".($ID + $k);
#		       print GROUPS $nes[$k]."\t".lc($morph{$doc}{$sn}{($ID+$k)})."\t id: $ids\n"; 	
                    } else { next M; }  
                  }     
                } else {
                   $ids = $ID - $ind;
                   for (my $k=($ind-1);$k>=0;$k--) {  #если попали в середину ИГ
		       if ($nes[$k] eq lc($morph{$doc}{$sn}{($ID-$k)})) { #safe check 	
                           $ids .= ",".($ID - $k);
                       } else { next M; }
                   }  
                   for my $k (1..($#nes-$ind)) {  
		       if ($nes[$k] eq lc($morph{$doc}{$sn}{($ID+$k)})) { #safe check 	
                          $ids .= ",".($ID + $k);
                       } else { next M; }
		    }  
                }
                my @IDS = split/,/, $ids;    
                if ($cur_main eq lc($token)) { $mid = $ID; }
                elsif (uc($cur_main) eq "NONE" || uc($cur_main) eq "ALL") {
 #                     print "We are in ALL\n";
                      $mid = uc($cur_main);
                } 
                elsif ($cur_main !~ / / && $cur_main ne "") { 
                    # print GROUPS "We are in findings\n";
                    # my @mids = split/,/, $ids;
                     for my $i (0..$#IDS){
                         if (lc($morph{$doc}{$sn}{$IDS[$i]}) eq $cur_main){
                            $mid = $IDS[$i]; 
                         }    
                     }
=comm
                }
                elsif ($cur_main =~ / / && $cur_main ne "") { 
                     my $mids;
                     
                     for my $i (0..$#IDS){
                         if (lc($morph{$doc}{$sn}{$IDS[$i]}) eq $cur_main){
                            $mids .= ",".$IDS[$i]; 
                         }    
                     }
                     $mids =~ s/^,//;
                     my @keys = keys %groups;
                     for my $j (0..$#keys){
                        print "Trying to find group\n";
                     	if (exists $groups{$j}{$mids}){
                           $mid = $groups{$j}{$mids};
                           last;
                        }
                     }                         
=cut
                } else { $mid = 0; }  
                if (exists $types{$cur_type}) { 
                    print GROUPS "$neID\t$ids\t$mid\t".$types{$cur_type}."\n";
                } else { 
                    print GROUPS "$neID\t$ids\t$mid\t".$cur_type."\n";
                }
                $groups{$doc}{$ids} = $mid;
               # print GROUPS "$neID\t$tmpNE\t$cur_main\t".$cur_type."\n";
                $neID++;  
                next L; 
             }
          }

       }    
   }
}


=comm
#тестовая печать
while (my($key,$value) = each(%facts)){
      while (my ($f,$val) = each(%$value)){
         print OUT "$key\t$f\t";
         while (my ($k,$v) = each (%$val)){
           print OUT "$k,$v\t";
         }
         print OUT "\n";
      }
}
=cut

#-subs-------------------------------------------------------------

my $id;
my $dID;
#read and search xml file
my ($fact,$NE,$type,$main);

sub tag_start{
  my ($expat, $tag_name, %attr) = @_;

  if ($tag_name eq 'document') {
     $dID = $attr{'url'};
     $dID =~ s/\.txt//;
     $dID =~ s/\\//;
  }
  if($tag_name eq 'facts') {
     $fact = $tag_name;
  }
  
  if ($tag_name =~ /NamedEntity|ComplexNE/){
      my %tmp;
      $id = $attr{'FactID'};
      while (my ($att, $val) = each %attr){
         $tmp{$att} = $val;
      }
      $facts{$dID}{$id} = \%tmp;
  }
  
  if ($tag_name eq 'Self'){
      $NE = $attr{'val'};
      if ($NE =~ /^"/ && $NE =~ /"$/){
      	 $NE =~ s/"//g;
      }
      $NE = trim($NE);
      #$NE =~ s/  / /g;
      $facts{$dID}{$id}{'Self'} = $NE;
  } 
  
  if ($tag_name eq 'Type'){
      $type = $attr{'val'};
      $facts{$dID}{$id}{'Type'} = $attr{'val'};
    }  

  if ($tag_name eq 'Main'){
      $main = $attr{'val'};
      $main =~ s/"//g;
      $main = trim($main);
      #$main =~ s/  / /g;
      $facts{$dID}{$id}{'Main'} = $main;
     # print OUT "$NE\t$type\t$main\n";
      ($NE,$type,$main) = "";
  }

  #if ($tag_name eq 'Lead'){
    #  if ($atrr{'id'} eq 
  #}

}

sub trim {
  my ($str) = @_;
     $str =~ s/^\s+//g;
     $str =~ s/\s+$//g;
  return $str;
}


sub tag_text{
   my ($expat, $string) = @_;
}

sub tag_end{
  my ($expat, $tag_name) = @_;
  
  if ($tag_name =~ /NamedEntity|ComplexNE/){
     $id = "";
  } 
  if ($tag_name eq 'document'){
     $dID = "";
  }

  if ($tag_name eq 'facts'){
  }

  if ($tag_name eq 'Leads'){
  }

}
close OUT;
close GROUPS;

#чтение файлов морфологии и запись хеша morph
sub readDir {

  opendir MD, $_[0] or die "cannot open dir: $!";
  while (defined(my $file = readdir(MD))) {
     if ($file eq "." || $file eq ".."){next;} #omit these files
     open(IN, "<$_[0]/$file")or die "$!"; #open each file
     binmode(IN, ":encoding(utf8)");	      
     
     my $sn = 0;
     my $fileID = $file;
     $fileID =~ s/\.txt//;     

     while (my $str = <IN>) {    
     
     	if ($str =~ /^sent/) {
           $sn++;
           next;
     	}
     	if ($str =~ /^\d+/){
           my @tokens = split/\t/,$str;
           $morph{$fileID}{$sn}{$tokens[0]} = $tokens[1];         
     	} 
     }
     close IN; 
  }
  closedir MD;
}
