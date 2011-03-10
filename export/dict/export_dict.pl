#!/usr/bin/perl

use strict;
use utf8;
use DBI;
use Encode;
use Getopt::constant('FORCE' => 0, 'PLAINTEXT' => 0);

my $lock_path = "/var/lock/opcorpora_export_dict.lock";
if (-f $lock_path) {
    die ("lock exists, exiting");
}

#reading config
my %mysql;
while(<>) {
    if (/\$config\['mysql_(\w+)'\]\s*=\s*'([^']+)'/) {
        $mysql{$1} = $2;
    }
}

open my $lock, ">$lock_path";
print $lock 'lock';
close $lock;

#main
my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'});
if (!$dbh) {
    unlink $lock_path;
    die $DBI::errstr;
}
$dbh->do("SET NAMES utf8");
binmode(STDOUT, ':utf8');

my $ts = $dbh->prepare("SELECT MAX(`timestamp`) `timestamp` FROM `rev_sets` WHERE `set_id` IN ((SELECT `set_id` FROM dict_revisions ORDER BY `rev_id` DESC LIMIT 1), (SELECT `set_id` FROM dict_links_revisions ORDER BY `rev_id` DESC LIMIT 1))");
$ts->execute();
my $r = $ts->fetchrow_hashref();
if (time() - $r->{'timestamp'} > 60*60*25 && !FORCE) {
    unlink $lock_path;
    die ("Dictionary not updated for 25 hours, exiting");
}

my $rev = $dbh->prepare("SELECT MAX(rev_id) AS m FROM dict_revisions");
my $read_g = $dbh->prepare("SELECT g1.inner_id AS id, g2.inner_id AS pid FROM gram g1 LEFT JOIN gram g2 ON (g1.parent_id=g2.gram_id) ORDER BY g1.`orderby`");
my $read_l = $dbh->prepare("SELECT * FROM (SELECT lemma_id, rev_id, rev_text FROM dict_revisions WHERE lemma_id BETWEEN ? AND ? ORDER BY lemma_id, rev_id DESC) T GROUP BY T.lemma_id");
my $read_lt = $dbh->prepare("SELECT * FROM dict_links_types ORDER BY link_id");
my $read_links = $dbh->prepare("SELECT * FROM dict_links ORDER BY link_id LIMIT ?, 10000");

$rev->execute();
$r = $rev->fetchrow_hashref();
my $r1;
my $maxrev = $r->{'m'};

my $header;
my $footer;
unless (PLAINTEXT) {
    $header = "<?xml version=\"1.0\" encoding=\"utf8\" standalone=\"yes\"?>\n<dictionary version=\"0.8\" revision=\"$maxrev\">\n";
    $footer = "</dictionary>";

    # grammems
    my $grams = "<grammems>\n";

    $read_g->execute();
    while($r = $read_g->fetchrow_hashref()) {
        $grams .= "    <grammem parent=\"$r->{'pid'}\">".tidy_xml($r->{'id'})."</grammem>\n";
    }
    $grams .= "</grammems>\n";

    print $header.$grams;
}

# lemmata
print "<lemmata>\n" unless PLAINTEXT;

my $flag = 1;
my $min_lid = 0;

while ($flag) {
    $flag = 0;
    $read_l->execute($min_lid + 1, $min_lid + 50000);
    while($r = $read_l->fetchrow_hashref()) {
        $flag = 1;
        $r->{'rev_text'} =~ s/<\/?dr>//g;
        if (PLAINTEXT) {
            print $r->{'lemma_id'}."\n";
            print rev2text($r->{'rev_text'})."\n";
        } else {
            print '    <lemma id="'.$r->{'lemma_id'}.'" rev="'.$r->{'rev_id'}.'">'.$r->{'rev_text'}."</lemma>\n";
        }
    }
    $min_lid += 50000;
}

print "</lemmata>\n" unless PLAINTEXT;

unless (PLAINTEXT) {
    # link types
    print "<link_types>\n";

    $read_lt->execute();
    while($r = $read_lt->fetchrow_hashref()) {
        print '<type id="'.$r->{'link_id'}.'">'.tidy_xml($r->{'link_name'})."</type>\n";
    }

    print "</link_types>\n";

    # links
    print "<links>\n";

    $min_lid = 0;
    $flag = 1;

    while($flag) {
        $flag = 0;
        $read_links->execute($min_lid);
        while($r = $read_links->fetchrow_hashref()) {
            $flag = 1;
            print '    <link id="'.$r->{'link_id'}.'" from="'.$r->{'lemma1_id'}.'" to="'.$r->{'lemma2_id'}.'" type="'.$r->{'link_type'}."\"/>\n";
        }
        $min_lid += 10000;
    }

    print "</links>\n";

    print $footer."\n";
}

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
sub rev2text {
    my $str = shift;

    my @lgr;
    my @fgr;
    my $fstr;
    my $out = '';

    $str =~ /<l(.+)\/l/;
    my $lstr = $1;
    while ($lstr =~ /<g v="([^"]+)"/g) {
        push @lgr, $1;
    }
    while ($str =~ /<f t="([^"]+)">(.+?)?<\/f>/g) {
        $out .= uc(decode('utf8', $1))."\t";
        my $fstr = $2;
        @fgr = ();
        while ($fstr =~ /<g v="([^"]+)"/g) {
            push @fgr, $1;
        }
        $out .= join(',', (@lgr, @fgr))."\n";
    }
    return $out;
}
