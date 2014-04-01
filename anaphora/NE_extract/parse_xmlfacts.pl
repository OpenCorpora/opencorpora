#!/usr/bin/perl
use strict;
use utf8;
use XML::Parser;
use Scalar::Util;
use Getopt::Std;

my $start = time();

binmode (STDOUT,"encoding(utf8)");
binmode (STDERR,"encoding(utf8)");

my %opts;
getopts('x:m:',\%opts);

#usage
if (!$opts{'x'} || !$opts{'m'}){
    print "\n\nUsage:\n
    $0 -m MORPH_DIR  
    \t\t  -x XML_FILE \n\n";
   exit;
}

my $debug = 'debug.txt';
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
   'анафорическое местоимение' => 17,
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
    M:foreach my $f(keys %$fact) {
       my $sn = $facts{$doc}{$f}{'sn'};
       my $tmpNE = $facts{$doc}{$f}{'Self'};
       my $cur_type = lc($facts{$doc}{$f}{'Type'});
       my $cur_main = $facts{$doc}{$f}{'Main'}[0];
       my $mpos = $facts{$doc}{$f}{'Main'}[1];
       my $pos = $facts{$doc}{$f}{'pos'};
              
       if (exists $morph{$doc}{$pos}){
          my ($ID,$token) = ($morph{$doc}{$pos}[0],$morph{$doc}{$pos}[1]); 
          print OUT "$doc\t$pos\t$tmpNE\t$token\t$ID\n";
          my ($fp,$sp) = split/_/,$ID; 
          #для однословных
          if ($tmpNE eq $token) {
               my $Mid;
               if ($cur_main eq "") { $Mid = 0; }
               elsif (uc($cur_main) eq "NONE" || uc($cur_main) eq "ALL") { $Mid = uc($cur_main); }
               else { $Mid = $ID; }
               my $ne_ID = $fp."_".sprintf("%04d",$neID);
               $groups{$ne_ID}{$ID} = [$Mid,$types{$cur_type}];
               $neID++;
          } #для многословных
          elsif ($tmpNE =~ / /){
		my @nes = split/ /, $tmpNE;
                my ($mid,$ids);
                my $newpos = $pos+length($nes[0])+1;
		if ($nes[0] eq $morph{$doc}{$pos}[1]){
                   $ids = $ID;
                } else { next M; }
                for my $k (1..$#nes){
                   my $tmpI = $fp."_".sprintf("%04d",$sp+$k);
		   if (exists $morph{$doc}{$newpos}){
                      if ($nes[$k] eq $morph{$doc}{$newpos}[1]) { #safe check 	
                         $ids .= ",".$tmpI;
                      }
                   }  
                   $newpos += length($nes[$k])+1;
                }
                my @IDS = split/,/, $ids;
                if ($cur_main eq lc($token)) { $mid = $ID; }
                elsif (uc($cur_main) eq "NONE" || uc($cur_main) eq "ALL") {
                      $mid = uc($cur_main);
                } 
                elsif ($cur_main ne "") { 
                     my @mains = split/ /, $cur_main; 
                     my $tmppos = $mpos;    
                     if (exists $morph{$doc}{$tmppos} && lc($morph{$doc}{$tmppos}[1]) eq $mains[0]){ 
                        $mid = $morph{$doc}{$tmppos}[0];
                        my ($tmpf,$tmps) = split/_/,$morph{$doc}{$tmppos}[0]; 
                        for my $j (1..$#mains){
                            my $tmpI = $tmpf."_".sprintf("%04d",($tmps+$j)); 
		            $tmppos += length($mains[$j-1])+1;
                            if (exists $morph{$doc}{$tmppos} && $mains[$j] eq lc($morph{$doc}{$tmppos}[1])) { #safe check 	
                               $mid .= ",".$tmpI;
                            }   
                        }
                     }    
                } else { $mid = 0; }
                my $ne_ID = $fp."_".sprintf("%04d",$neID);
                $groups{$ne_ID}{$ids} = [$mid,$types{$cur_type}];
                $neID++;  
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
                  print "$key\t$f\t$groups{$j}{$val->[0]}->[0]\t$val->[1]\n";
               }
           }
        } else {  
          print "$key\t$f\t$val->[0]\t$val->[1]\n";
        }
      }
}
=comm
#тестовая печать
while (my($key,$value) = each(%morph)){
      while (my ($f,$val) = each(%$value)){
         print STDERR "$key\t$f\t$val->[0],$val->[1]\n";
        # print OUT "$key\t$f\t";
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
      $facts{$dID}{$id}{'Main'} = [lc($main),$attr{'pos'},$attr{'len'}];
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
      if ($s =~ /^"/ && $s !~ /.+".+$/){
          $s =~ s/"//g;
      }
      $s =~ s/"/ " /g;
      $s =~ s/\. /\./g;
      $s = trim($s);
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
     
    # my $sn = 1;
     #my $is_first = 0;
     my $fileID = $file;
     my $pos = 0;
     $fileID =~ s/\.txt\.new\.tab$//;     

     while (my $str = <IN>) {    
     	
	if ($str =~ /^\/sent/) {
        #   $sn++;
           $pos += 2;
           next;
     	}
     
     	if ($str =~ /^\d+/){
           my $quot = 0;
	  # my @tmp;
           if ($str =~ /\«|\»/){  
              $str =~ s/\«|\»/\"/;
              $quot = 1;
           } 
           $str =~ s/\&quot\;/\"/;
           my @tokens = split/\s+/,$str;
           my $t = $tokens[1];
           my ($tmpf,$tmps) = split/_/,$tokens[0]; 
          # my $prev_id = $tmpf."_".sprintf("%04d",($tmps-1)); 
          # if ($t eq "\." || $t eq "\!" || $t eq "\?"){
          #    $sn++;
          #    $is_first = 1;  
          # }
          # if (($t eq "com" || $t eq "ру" || $t eq "что" || $t eq "см") && $m{$fileID}{$sn}{$prev_id} eq "\."){
          #    $sn--;
          # }

          #В морфологии иногда след. за словом двоеточие прилепляется к токену,
	  #томита его отрывает, поэтому берём токен без этой пунктуации. 
           if (length(trim($t)) > 1 && $t =~ /(\"$|\:$|^\"|\/$|\-$)/){
              $t =~ s/$1//;
              if ($t !~ /\/$/){
                 $quot = 2;
              }
           }
           #$m{$fileID}{$sn}{$tokens[0]} = [trim($t), $pos];
           if ($tokens[0]) {
              $m{$fileID}{$pos} = [$tokens[0],trim($t)];
	      if ($t eq "(" || $t eq ")" || $t eq "," || $t eq "." || $t eq ":" || $quot == 1 || $t eq "...") {
                 $pos += length($tokens[1]); 
              } elsif ($quot == 2) { $pos += length($tokens[1])+2; } 
              else { $pos += length($tokens[1])+1; }
           }  
     	} 
     }
     close IN; 
  }
  closedir MD;
  return %m;
}
