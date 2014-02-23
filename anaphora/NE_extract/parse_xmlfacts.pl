#!/usr/bin/perl
use strict;
use utf8;
use XML::Parser;
use Scalar::Util;
use Getopt::Std;

my $start = time();

binmode (STDOUT,"encoding(utf8)");

my %opts;
getopts('x:o:m:',\%opts);

#usage
if (!$opts{'x'} || !$opts{'m'} || !$opts{'o'}){
    print "\n\nUsage:\n
    $0 -m MORPH_DIR  
    \t\t  -o OUTPUT_FILE 
    \t\t  -x XML_FILE \n\n";
   exit;
}

my $debug = 'debug.txt';
#my $file_output = $opts{'o'};

open (GROUPS, ">$opts{'o'}") or die "No output file: $!";
binmode(GROUPS, ':encoding(utf-8)');
open (OUT, ">$debug") or die "Error: $!";
binmode(OUT, ':encoding(utf-8)');

#hash with group-types
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
my $parser = XML::Parser->new(Handlers=>{Start => \&tag_start, End => \&tag_end});
$parser->parsefile($opts{'x'});
my %facts; #DocID - FactId - { {k=>v} ...}

#файлы с морфологией
my %morph = readDir("$opts{'m'}"); #fileID - SentNum - id => token

#для групп
my %groups; #neID - token_ids => [main id,type]

my $neID = 1;
#сопоставление
while (my ($doc,$fact) = each(%facts)) {
    foreach my $f(keys %$fact) {
       my $sn = $facts{$doc}{$f}{'sn'};
       my $tmpNE = $facts{$doc}{$f}{'Self'};
       my $cur_type = lc($facts{$doc}{$f}{'Type'});
       my $cur_main = $facts{$doc}{$f}{'Main'};
      M: while (my ($ID,$token) = each(%{$morph{$doc}{$sn}})) {
          #print $tmpNE."\t$token\n";
         #my $tk = lc($token);
          if ($tmpNE =~ /\Q$token\E/) {
             #для однословных
             if ($tmpNE eq $token) {
               my $Mid;
               if ($cur_main eq "") { $Mid = 0; }
               elsif (uc($cur_main) eq "NONE" || uc($cur_main) eq "ALL") { $Mid = uc($cur_main); }
               else { $Mid = $ID; }
             #  if (exists $types{$cur_type}) { 
                  $groups{$neID}{$ID} = [$Mid,$types{$cur_type}];
             #  } else { 
             #     $groups{$neID}{$ID} = [$Mid,$cur_type];
             #  }
             #  print GROUPS "$neID\t$tmpNE\t$cur_main\t".$cur_type."\n";
             #  print "$tmpNE\n";
               $neID++;  
             } #для многословных
             elsif ($tmpNE =~ / /) {
		my @nes = split/ /, $tmpNE; 
                my ($ind, $ids, $mid);
                for my $i (0..$#nes){
                    if ($nes[$i] eq $token) {
                       $ind = $i; last;
                    }
                }
		if ($ind eq "") {
                    next M;	
		}                
                elsif ($ind == 0){
                  $ids = $ID;
                  for my $k (1..$#nes){
		    if ($nes[$k] eq $morph{$doc}{$sn}{($ID+$k)}) { #safe check 	
                       $ids .= ",".($ID + $k);
                    } else { next M; }  
                  }     
                } else {
                   $ids = $ID - $ind;
                   for (my $k=($ind-1);$k>=0;$k--) {  #если попали в середину ИГ
		       if ($nes[$k] eq $morph{$doc}{$sn}{($ID-$k)}) { #safe check 	
                           $ids .= ",".($ID - $k);
                       } else { next M; }
                   }  
                   for my $k (1..($#nes-$ind)) {  
		       if ($nes[$k] eq $morph{$doc}{$sn}{($ID+$k)}) { #safe check 	
                          $ids .= ",".($ID + $k);
                       } else { next M; }
		    }  
                }
                my @IDS = split/,/, $ids;    
                if ($cur_main eq lc($token)) { $mid = $ID; }
                elsif (uc($cur_main) eq "NONE" || uc($cur_main) eq "ALL") {
                      $mid = uc($cur_main);
                } 
                elsif ($cur_main ne "") { 
                     my @mains = split/ /, $cur_main; 
                     K:for my $i (0..$#IDS){
                         if (lc($morph{$doc}{$sn}{$IDS[$i]}) eq $mains[0]){
                            $mid = $IDS[$i];
                            for my $j (1..$#mains){
		                if ($mains[$j] eq lc($morph{$doc}{$sn}{($IDS[$i]+$j)})) { #safe check 	
                                   $mid .= ",".($IDS[$i] + $j);
                                } else { next K; }    
                            }
                            last K;  
                         }    
                     }
                } else { $mid = 0; }  
               # print STDERR "$mid\n";
              #  if (exists $types{$cur_type}) { 
                    $groups{$neID}{$ids} = [$mid,$types{$cur_type}];
                   # print GROUPS "$neID\t$ids\t$mid\t".$types{$cur_type}."\n";
               # } else { 
               #     $groups{$neID}{$ids} = [$mid,$cur_type];
                   # print GROUPS "$neID\t$ids\t$mid\t".$cur_type."\n";
               # }
               # print GROUPS "$neID\t$tmpNE\t$cur_main\t".$cur_type."\n";
              #  print "$tmpNE\n";
                $neID++;  
             #   next L; 
             }
          }

       }    
   }
}

my $size = keys %groups;
while (my($key,$value) = each(%groups)){
      while (my ($f,$val) = each(%$value)){
        if ($val->[0] =~ /,/){
           for my $j (0..$size){
               if (exists $groups{$j}{$val->[0]}){
                  print GROUPS "$key\t$f\t$groups{$j}{$val->[0]}->[0]\t$val->[1]\n";
               }
           }
        } else {  
          print GROUPS "$key\t$f\t$val->[0]\t$val->[1]\n";
        }
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

#тестовая печать
while (my($key,$value) = each(%facts)){
      while (my ($f,$val) = each(%$value)){
       #  print OUT "$key\t$f\t";
         print OUT "$val->{'Self'}\n";
        # while (my ($k,$v) = each (%$val)){
        #   print OUT "$k,$v\t";
        # }
        # print OUT "\n";
      }
}
=cut
print STDERR time() - $start."\n";   

#-subs-------------------------------------------------------------

my $id;
my $dID;
#read and search xml file
my ($fact,$NE,$type,$main);
my %tmplead; #n => text
my ($tmpN,$stag);

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
      $NE =~ s/\s+/ /g;
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
      $main =~ s/\s+/ /g;
      $facts{$dID}{$id}{'Main'} = lc($main);
     # print OUT "$NE\t$type\t$main\n";
      ($NE,$type,$main) = "";
  }
  if ($tag_name eq 'Lead'){
      my $tmpfId;
      while (my ($k,$v) = (each %{$facts{$dID}})){
         if ($attr{'id'} eq $v->{'LeadID'}){
           my $inparser = XML::Parser->new(Handlers=>{Start => \&tag_st1, End => \&tag_e1, Char =>\&tag_text});
           $inparser->parse($attr{'text'});
           my $n = $facts{$dID}{$k}->{'FieldsInfo'}; 
           $n =~ s/;//;
           $facts{$dID}{$k}->{'Self'} = $tmplead{$n};
         } 
      }
  }
}

sub trim {
  my ($str) = @_;
     $str =~ s/^\s+//g;
     $str =~ s/\s+$//g;
  return $str;
}
sub tag_st1{
  my ($expat, $tag_name, %attr) = @_;
  if ($tag_name eq "S"){
     $stag = $tag_name;
     my @k = keys %attr;
     if ($k[0] eq "lemma"){
         $tmpN = $k[1];
     } else { $tmpN = $k[0]; }
  }

}

sub tag_e1{
  my ($expat, $tag_name) = @_;
  if ($tag_name eq "S"){
     $tmpN = "";
     $stag = "";
  } 
}

sub tag_text{
   my ($expat, $string) = @_;
   if ($stag eq "S"){
      my $s = $string;
      if ($s =~ /^"/ && $s =~ /"$/){
      	 $s =~ s/"//g;
      }
      if ($s =~ /"$/ && $s !~ /.+".+"$/){
          $s =~ s/"//g;
      }
      $s =~ s/"/ " /g;
      $s =~ s/\. /\./g;
      $s = trim($s);
      $s =~ s/\s+/ /g;
      $tmplead{$tmpN} = $s;
   }
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

  if ($tag_name eq 'Lead'){
     %tmplead = ();	
  }

}
close OUT;
close GROUPS;

#чтение файлов морфологии и запись хеша morph
sub readDir {
  my %m;
  opendir MD, $_[0] or die "cannot open dir: $!";
  while (defined(my $file = readdir(MD))) {
     if ($file eq "." || $file eq ".."){next;} #omit these files
     open(IN, "<$_[0]/$file")or die "$!"; #open each file
     binmode(IN, ":encoding(utf8)");	      
     
     my $sn = 0;
     my $fileID = $file;
     $fileID =~ s/\..+$//;     

     while (my $str = <IN>) {    
     
     	if ($str =~ /^sent/) {
           $sn++;
           next;
     	}
     	if ($str =~ /^\d+/){
           $str =~ s/\«|\»/\"/;
           $str =~ s/\&quot\;/\"/;
           my @tokens = split/\t/,$str; 
           $m{$fileID}{$sn}{$tokens[0]} = $tokens[1];
     	} 
     }
     close IN; 
  }
  closedir MD;
  return %m;
}
