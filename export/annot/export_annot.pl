#!/usr/bin/perl

use strict;
use utf8;
use DBI;
use Encode;
use Config::INI::Reader;

my $lock_path = "/var/lock/opcorpora_export_annot.lock";
if (-f $lock_path) {
    die ("lock exists, exiting");
}

#reading config
my $conf = Config::INI::Reader->read_file($ARGV[0]);
$conf = $conf->{mysql};

open my $lock, ">$lock_path";
print $lock 'lock';
close $lock;

#main
my $dbh = DBI->connect('DBI:mysql:'.$conf->{'dbname'}.':'.$conf->{'host'}, $conf->{'user'}, $conf->{'passwd'});
if (!$dbh) {
    unlink $lock_path;
    die $DBI::errstr;
}
$dbh->do("SET NAMES utf8");
binmode(STDOUT, ':utf8');

my $rev = $dbh->prepare("SELECT MAX(rev_id) AS m FROM tf_revisions");
my $books = $dbh->prepare("SELECT * FROM books");
my $tags = $dbh->prepare("SELECT tag_name FROM book_tags WHERE book_id=?");
my $par = $dbh->prepare("SELECT par_id FROM paragraphs WHERE book_id=? ORDER BY pos");
my $sent = $dbh->prepare("SELECT sent_id, source FROM sentences WHERE par_id=? ORDER BY pos");
my $tf = $dbh->prepare("SELECT tf_id, tf_text FROM text_forms WHERE sent_id=? ORDER BY pos");
my $tfrev = $dbh->prepare("SELECT rev_id, rev_text FROM tf_revisions WHERE tf_id=? ORDER BY rev_id DESC LIMIT 1");
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
        print "    <paragraph id=\"".$r1->{'par_id'}."\">\n";
        $sent->execute($r1->{'par_id'});
        while (my $r2 = $sent->fetchrow_hashref()) {
            print "      <sentence id=\"".$r2->{'sent_id'}."\">\n";
            print "        <source>".tidy_xml(decode('utf8', $r2->{'source'}))."</source>\n        <tokens>\n";
            $tf->execute($r2->{'sent_id'});
            while (my $r3 = $tf->fetchrow_hashref()) {
                print "          <token id=\"".$r3->{'tf_id'}."\" text=\"".tidy_xml(decode('utf8', $r3->{'tf_text'}))."\">";
                $tfrev->execute($r3->{'tf_id'});
                my $r4 = $tfrev->fetchrow_hashref();
                $r4->{'rev_text'} =~ s/<tfr/<tfr rev_id="$r4->{rev_id}"/;
                print decode('utf8', $r4->{'rev_text'});
                print "</token>\n";
            }
            print "        </tokens>\n      </sentence>\n";
        }
        print "    </paragraph>\n";
    }
    print "  </paragraphs>\n";
    print "</text>\n";
}

print $footer;

unlink($lock_path);

sub tidy_xml {
    my $arg = shift;
    $arg =~ s/&/&amp;/g;
    $arg =~ s/"/&quot;/g;
    $arg =~ s/'/&apos;/g;
    $arg =~ s/</&lt;/g;
    $arg =~ s/>/&gt;/g;
    return $arg;
}
