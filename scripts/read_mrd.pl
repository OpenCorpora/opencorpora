
use strict;

my $paradigm_id = 0;
#my $max_paradigm = undef;
#chomp $max_paradigm;
my $dict_items_count;

my %paradigm; # id -> ref.to.array -> ref.to.hash -> { flex => "", $grm => "" }

my $mrd_fn = $ARGV[0];

open ( my $mrd_fh, $mrd_fn ) || die "can't open $mrd_fn";
print STDERR "reading $mrd_fn ...\n";

my $r_mrdsec_paradigms = mrd_read_section( $mrd_fh );
print STDERR $#{ $r_mrdsec_paradigms } + 1 . " paradigms read\n";
#print STDERR ${ $r_mrdsec_paradigms }[ $#{ $r_mrdsec_paradigms } ];

my $r_mrdsec_accent = mrd_read_section( $mrd_fh );
print STDERR $#{ $r_mrdsec_accent } + 1 . " accent patterns read\n";
#print STDERR ${ $r_mrdsec_accent }[ $#{ $r_mrdsec_accent } ];

my $r_mrdsec_log = mrd_read_section( $mrd_fh );
print STDERR $#{ $r_mrdsec_log } + 1 . " log records read\n";

my $r_mrdsec_prefix = mrd_read_section( $mrd_fh );
print STDERR $#{ $r_mrdsec_prefix } + 1 . " prefixes read\n";

my $r_mrdsec_lemma = mrd_read_section( $mrd_fh );
print STDERR $#{ $r_mrdsec_lemma } + 1 . " lemmas read\n";


#die "ok";

my $p;
foreach $p ( @{ $r_mrdsec_paradigms } )
{
  my ( $paradigm_text, $other ) = split( /\#/, $p );
  if ( length( $other ) > 0 )
  { die "mrd paring error: $_"; } 

  while ( $paradigm_text =~ /%([À-ß¨]*)\*([À-ß¨à-ÿ¸]+)(\*([À-ß¨]*))?/g )
  {
    if ( length( $4 ) > 0 )
    {
      push @{ $paradigm{ $paradigm_id } }, { "flex" => $1, "grm" => $2, "prefix" => $4 };
#      print STDERR "$paradigm_id\n";
    }
    else
    {
      push @{ $paradigm{ $paradigm_id } }, { "flex" => $1, "grm" => $2 };
    }
  }

  $paradigm_id += 1;
}

#print STDERR "max_paradigm = $max_paradigm\n";
#print STDERR "next paradigm_id = $paradigm_id\n";

my %dict;
my $item_id = 0;

foreach $p ( @{ $r_mrdsec_lemma } )
{
  my ( $pseudo_stem, $p_id, $a_id, $s_id, $common_ancode, $prefixset_no ) = split( /\s+/, $p );
  #if ( "-" ne $prefixset_no ) { print STDERR "$p\n"; }

  if ( $p_id >= $paradigm_id )
  { die "unknown paradigm id $p_id"; }

  if ( $pseudo_stem =~ /\#/ )
  {
    $pseudo_stem = "";
  }

  $dict{ $item_id } = { 
                        "stem" => $pseudo_stem, 
                        "pid" => $p_id,
                        "aid" => $a_id,
                        "sid" => $s_id,
                        "common_ancode" => $common_ancode,
                        "prefixid" => $prefixset_no
                      };
  $item_id += 1;
}

#print STDERR "$dict_items_count $item_id\n";

read_grm_tab( "rgramtab.tab" ); 

my %grmtab;

my $d;
foreach $d ( keys %dict )
{
  my $stem = $dict{ $d }->{ "stem" };
  my $pid = $dict{ $d }->{ "pid" };
  print "PARA $pid\n";
  
  my $f;
  foreach $f ( @{ $paradigm{ $pid } } )
  {
    my $flex = $f->{ "flex" };
    my $grm = $f->{ "grm" };
    my $form_prefix = $f->{ "prefix" };
    my $common_grm = undef;
    if ( "-" ne $dict{ $d }->{ "common_ancode" } )
    {
      $common_grm = $dict{ $d }->{ "common_ancode" };
    }

    for ( my $j = 0; $j < length( $grm ); $j += 2 )
    {
      my $tag = substr( $grm, $j, 2 );
      if ( ! exists( $grmtab{ $tag } ) )
      { die "unknown ancode $tag ($grm $flex $pid $stem)"; }

      my $prefix;
      if ( "-" ne $dict{ $d }->{ "prefixid" } )
      {
        $prefix = ${ $r_mrdsec_prefix }[ $dict{ $d }->{ "prefixid" } ];
        #print STDERR $dict{ $d }->{ "prefixid" } . "\n";
      }
 
      my $common_tag;
      if ( defined( $common_grm ) )
      {
        if ( !exists( $grmtab{ $common_grm } ) )
        {
          die "unknown ancode $common_grm ($grm $flex $pid $stem)";
        }

        my $c = $grmtab{ $common_grm };
        $c =~ s/^\*(.*)$/$1/;
        
        if ( length( $prefix ) > 0 )
        { print "$prefix$form_prefix$stem$flex\t" . $grmtab{ $tag } . $c . "\n"; }
        else
        { print "$form_prefix$stem$flex\t" . $grmtab{ $tag } . $c . "\n"; }
      }
      else
      {
        
        if ( length( $prefix ) > 0 )
        { print "$prefix$form_prefix$stem$flex\t" . $grmtab{ $tag } . "\n"; }
        else
        { print "$form_prefix$stem$flex\t" . $grmtab{ $tag } . "\n"; }
      }
    }
  }
  print "\n";
}


sub read_grm_tab
{
  my $fn = shift;

  open ( F, "< $fn" );

  while ( <F> )
  {
    chomp $_;
    if ( $_ =~ /^\/\// || $_ =~ /^\s*$/ )
    { next; }

    my ( $ancode, $smth, $pos, $grm_str ) = split( /\s+/, $_ );
    my @grammems = split( /,/, $grm_str );
    $grmtab{ $ancode } = "$pos, $grm_str";
  }

  close( F );
}

sub mrd_read_section
{
  my $f = shift;
  my @vec_lines;
  my $n_lines = undef;

  while ( <$f> )
  {
    chomp $_;
    if ( ! defined( $n_lines ) )
    {
      if ( $_ =~ /^\s*(\d+)\s*$/ )
      {
        $n_lines = $1;
        next;
      }
      else
      {
        die "can't find lines count. Input line is \"$_\"";
      }
    }   
 
    push @vec_lines, $_;
    $n_lines--;
    if ( 0 == $n_lines )
    { last; }
  }
  
  return \@vec_lines;
}
