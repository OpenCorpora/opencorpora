#!/usr/bin/env perl

use strict;
use warnings;
use utf8;
use DBI;
use Encode;
use Config::INI::Reader;


binmode(STDOUT, ":encoding(utf-8)");
binmode(STDERR, ":encoding(utf-8)");

($ARGV[0] && $ARGV[1] && $ARGV[2]) or die "Usage: $0 <path_to_config.ini> <moderator_uid> <tagset_id> <last_book_id>";
my ($moderator_user_id, $tagset_id) = ($ARGV[1], $ARGV[2]);
my $last_book_id = undef;

if ($ARGV[3]) {
  $last_book_id = $ARGV[3];
}

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'});
if (!$dbh) {
    die $DBI::errstr;
}

$dbh->do("SET NAMES utf8");

my $sth_senttokens = $dbh->prepare("SELECT tf_id, tf_text FROM tokens WHERE sent_id=? ORDER BY pos");
my $sth_books = $dbh->prepare("SELECT book_id FROM paragraphs WHERE par_id=?");
my $sth_paragraphs = $dbh->prepare("SELECT par_id FROM paragraphs WHERE book_id=?");
my $sth_sentences = $dbh->prepare("SELECT sent_id, source FROM sentences WHERE par_id=?");
my $sth_spans = $dbh->prepare("
                               SELECT par_id, user_id, entity_id, tag_name, start_token, length
                                      FROM ne_entities
                                           JOIN ne_paragraphs np USING (annot_id)
                                           JOIN ne_entity_tags USING (entity_id)
                                           JOIN ne_tags USING (tag_id)
                                      WHERE np.tagset_id=?
                                      ORDER BY par_id, user_id
                              ");
my $sth_tokens = $dbh->prepare("
                                SELECT tf_id, tf_text
                                       FROM tokens
                                       WHERE sent_id=(SELECT sent_id FROM tokens WHERE tf_id=?)
                                             AND pos>=(SELECT pos FROM tokens WHERE tf_id=?)
                                       LIMIT ?
                               ");

my $moderator_user_id = 2;
my $tagset_id = 2;

my %books;

$sth_spans->execute($tagset_id);

while (my $r = $sth_spans->fetchrow_hashref) {
  if ($r->{user_id} != $moderator_user_id) { next; }

  my $rbook = fetch_book($r->{par_id}, $dbh);
  if (! defined $rbook) {
    next;
  }
  
  $sth_tokens->execute($r->{start_token}, $r->{start_token}, $r->{length});

  my %span;
  ($span{tag_name}, $span{entity_id}, $span{start_token}, $span{length} , $span{user_id}) = ($r->{tag_name}, $r->{entity_id}, $r->{start_token}, $r->{length}, $r->{user_id});

  $span{start_pos} = $rbook->{token_by_id}->{$span{start_token}}->{start_pos};

  while (my $rt = $sth_tokens->fetchrow_hashref) {
    push @{$span{tokens}}, $rt->{tf_id};
    push @{$span{tokens_text}}, decode("utf-8", $rt->{tf_text});
  }

  my $r_last_token = $rbook->{token_by_id}->{$span{tokens}->[$#{$span{tokens}}]};
  $span{nchars} = $r_last_token->{start_pos} + $r_last_token->{len} - $span{start_pos};

  push @{$rbook->{spans}}, \%span;
}

foreach my $book_id (keys %books) {
  save_spans($books{$book_id});
}

$sth_spans->finish;
$sth_senttokens->finish;
$sth_tokens->finish;
$sth_books->finish;
$sth_paragraphs->finish;
$sth_sentences->finish;

$dbh->disconnect;


sub fetch_book {
  my ($par_id, $dbh) = @_;
  my %book;

  $sth_books->execute($par_id);
  my $book_id = $sth_books->fetchrow_hashref->{book_id};
  if (defined($last_book_id) && $book_id > $last_book_id) {
    return undef;
  }

  if (exists $books{$book_id}) {
    return $books{$book_id};
  }

  $book{id} = $book_id;

  $sth_paragraphs->execute($book_id);

  my $offset = 0;
  while (my $r = $sth_paragraphs->fetchrow_hashref) {
    $sth_sentences->execute($r->{par_id});

    my @paragraph;
    while (my $r = $sth_sentences->fetchrow_hashref) {
      my %sentence;
      ($sentence{sent_id}, $sentence{source}) = ($r->{sent_id}, decode("utf8", $r->{source}));
      
      my $pos = 0;
      $sth_senttokens->execute($r->{sent_id});
      while (my $rt = $sth_senttokens->fetchrow_hashref()) {
        my %token;
        ($token{tf_id}, $token{tf_text}) = ($rt->{tf_id}, decode("utf8", $rt->{tf_text}));

        my $token_pos = index($sentence{source}, $token{tf_text}, $pos - 1);
        if (-1 == $token_pos) {
          die "Can't find \"$token{tf_text}\" in sentence \"$sentence{source}\" after position $pos";
        }

        $token{start_pos} = $token_pos + $offset;
        $token{len} = length($token{tf_text});

        push @{$sentence{tokens}}, \%token;
        $book{token_by_id}->{$token{tf_id}} = \%token;

        $pos += $token{len};
      }

      $offset += length($sentence{source}) + 1; # sentence length + space after sentence end
      push @paragraph, \%sentence;
    }

    $offset += 2; # two EOLs after paragraph end
    push @{$book{paragraphs}}, \@paragraph;
  }

  save_book(\%book);
  $books{$book_id} = \%book;

  return \%book;
}

sub save_spans {
  my ($rbook) = @_;
  my $fn_spans = "book_$rbook->{id}.spans";

  open(FS, "> $fn_spans") || die "Can't open file \"$fn_spans\"";
  binmode(FS, ":encoding(utf-8)");

  foreach my $rs (@{$rbook->{spans}}) {
    print FS join(" ", ($rs->{entity_id}, $rs->{tag_name}, $rs->{start_pos}, $rs->{nchars}, $rs->{start_token}, $rs->{length}));
    print FS "  # " . join(" ", (@{$rs->{tokens}}, @{$rs->{tokens_text}})) . "\n";
  }

  close(FS);

}

sub save_book {
  my ($rbook) = @_;
  my $fn_book = "book_$rbook->{id}.txt";
  my $fn_tokens = "book_$rbook->{id}.tokens";

  open(FB, "> $fn_book") || die "Can't open file \"$fn_book\"";
  binmode(FB, ":encoding(utf-8)");

  open(FT, "> $fn_tokens") || die "Can't open file \"$fn_tokens\"";
  binmode(FT, ":encoding(utf-8)");


  for (my $i = 0; $i <= $#{$rbook->{paragraphs}}; $i++) {
    my $rpar = $rbook->{paragraphs}->[$i];
    for (my $s = 0; $s <= $#{$rpar}; $s++) {
      my $rsent = $rpar->[$s];

      print FB $rsent->{source};
      if ($s < $#{$rpar}) { 
        print FB " ";
      }

      for (my $j = 0; $j <= $#{$rsent->{tokens}}; $j++) {
        my $rtoken = $rsent->{tokens}->[$j];
        print FT join(" ", ($rtoken->{tf_id}, $rtoken->{start_pos}, $rtoken->{len}, $rtoken->{tf_text})) . "\n";

        if ($j == $#{$rsent->{tokens}}) {
          print FT "\n";
        }
      }
    }

    print FB "\n";
    if ($i < $#{$rbook->{paragraphs}}) {
      print FB "\n";
    }
  }

  close(FB);
  close(FT);
} 
