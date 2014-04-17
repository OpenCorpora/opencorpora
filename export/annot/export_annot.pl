#!/usr/bin/perl

use strict;
use utf8;
use DBI;
use Encode;
use Config::INI::Reader;

# reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

# main
my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'});
if (!$dbh) {
    die $DBI::errstr;
}
$dbh->do("SET NAMES utf8");
binmode(STDOUT, ':encoding(utf8)');

my $rev = $dbh->prepare("SELECT MAX(rev_id) AS m FROM tf_revisions");
my $books = $dbh->prepare("SELECT * FROM books");
my $tags = $dbh->prepare("SELECT tag_name FROM book_tags WHERE book_id=?");
my $par = $dbh->prepare("SELECT par_id FROM paragraphs WHERE book_id=? ORDER BY pos");
my $sent = $dbh->prepare("SELECT sent_id, source FROM sentences WHERE par_id=? ORDER BY pos");
my $tf = $dbh->prepare("SELECT tf_id, tf_text FROM tokens WHERE sent_id=? ORDER BY pos");
my $tfrev = $dbh->prepare("SELECT rev_id, rev_text FROM tf_revisions WHERE tf_id=? AND is_last=1");
my $r;

$rev->execute();
$r = $rev->fetchrow_hashref();
my $maxrev = $r->{'m'};

my $header = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n<annotation version=\"0.11\" revision=\"$maxrev\">\n";
my $footer = "</annotation>";

print $header;

$books->execute();
while ($r = $books->fetchrow_hashref()) {
    print '<text id="'.$r->{'book_id'}.'" parent="'.$r->{'parent_id'}.'" name="'.tidy_xml(decode('utf8', $r->{'book_name'}))."\">\n  <tags>\n";
    $tags->execute($r->{'book_id'});
    while (my $r1 = $tags->fetchrow_hashref()) {
        print "    <tag>".tidy_xml(decode('utf8', $r1->{'tag_name'}))."</tag>\n";
    }
    print "  </tags>\n";
    print "  <paragraphs>\n";
    $par->execute($r->{'book_id'});
    while (my $r1 = $par->fetchrow_hashref()) {
        print_paragraph($r1->{'par_id'}, $ARGV[1] eq 'no_ambig');
    }
    print "  </paragraphs>\n";
    print "</text>\n";
}

print $footer;

# subroutines
sub tidy_xml {
    my $arg = shift;
    $arg =~ s/&/&amp;/g;
    $arg =~ s/"/&quot;/g;
    $arg =~ s/'/&apos;/g;
    $arg =~ s/</&lt;/g;
    $arg =~ s/>/&gt;/g;
    return $arg;
}
sub print_paragraph {
    my $id = shift;
    my $only_unambiguous = shift;
    my $out = '';
    my $should_print = 0;

    $out .= "    <paragraph id=\"$id\">\n";
    $sent->execute($id);
    while (my $r = $sent->fetchrow_hashref()) {
        my $s = get_sentence($r->{'sent_id'}, $r->{'source'});
        if (!$only_unambiguous || !$s->[1]) {
            $should_print = 1;
            $out .= $s->[0];
        }
    }
    $out .= "    </paragraph>\n";
    print $out if $should_print;
}
sub get_sentence {
    my $id = shift;
    my $source = shift;
    my $out_text = '';
    my $has_ambiguity = 0;

    $out_text .= "      <sentence id=\"$id\">\n";
    $out_text .= "        <source>".tidy_xml(decode('utf8', $source))."</source>\n        <tokens>\n";
    $tf->execute($id);
    while (my $r = $tf->fetchrow_hashref()) {
        my $t = get_token($r->{'tf_id'}, $r->{'tf_text'});
        $out_text .= $t->[0];
        if ($t->[1]) {
            $has_ambiguity = 1;
        }
    }
    $out_text .= "        </tokens>\n      </sentence>\n";
    return [$out_text, $has_ambiguity];
}
sub get_token {
    my $id = shift;
    my $text = shift;
    my $out_text = '';
    my $is_ambiguous = 0;

    $out_text .= "          <token id=\"$id\" text=\"".tidy_xml(decode('utf8', $text))."\">";
    $tfrev->execute($id);
    my $r = $tfrev->fetchrow_hashref();
    my ($rev_id, $rev_text) = ($r->{'rev_id'}, $r->{'rev_text'});

    $text =~ s/<tfr/<tfr rev_id="$rev_id"/;

    my $vars = 0;
    while ($rev_text =~ /<v>/g) {
        if (++$vars > 1) {
            $is_ambiguous = 1;
            last;
        }
    }

    $out_text .= decode('utf8', $rev_text);
    $out_text .= "</token>\n";
    return [$out_text, $is_ambiguous];
}
