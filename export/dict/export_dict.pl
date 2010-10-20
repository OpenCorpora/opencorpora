#!/usr/bin/perl

use strict;
use utf8;
use DBI;

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

my $ts = $dbh->prepare("SELECT MAX(`timestamp`) `timestamp` FROM `rev_sets` WHERE `set_id` IN ((SELECT `set_id` FROM dict_revisions ORDER BY `rev_id` DESC LIMIT 1), (SELECT `set_id` FROM dict_links_revisions ORDER BY `rev_id` DESC LIMIT 1))");
$ts->execute();
my $r = $ts->fetchrow_hashref();
if (time() - $r->{'timestamp'} > 60*60*25) {
    unlink $lock_path;
    die ("Dictionary not updated for 25 hours, exiting");
}

my $rev = $dbh->prepare("SELECT MAX(rev_id) AS m FROM dict_revisions");
my $read_gg = $dbh->prepare("SELECT * FROM gram_types ORDER by `orderby`");
my $read_g = $dbh->prepare("SELECT inner_id FROM gram WHERE gram_type=? ORDER BY `orderby`");
my $read_l = $dbh->prepare("SELECT * FROM (SELECT lemma_id, rev_id, rev_text FROM dict_revisions WHERE lemma_id BETWEEN ? AND ? ORDER BY lemma_id, rev_id DESC) T GROUP BY T.lemma_id");
my $read_lt = $dbh->prepare("SELECT * FROM dict_links_types ORDER BY link_id");
my $read_links = $dbh->prepare("SELECT * FROM dict_links ORDER BY link_id LIMIT ?, 10000");

$rev->execute();
$r = $rev->fetchrow_hashref();
my $r1;
my $maxrev = $r->{'m'};

my $header = "<?xml version=\"1.0\" encoding=\"utf8\" standalone=\"yes\"?>\n<dictionary version=\"0.7\" revision=\"$maxrev\">\n";
my $footer = "</dictionary>";

# grammems
my $grams = "<grammems>\n";

$read_gg->execute();
while($r = $read_gg->fetchrow_hashref()) {
    $grams .= "    <group name=\"".tidy_xml($r->{'type_name'})."\">\n";
    $read_g->execute($r->{'type_id'});
    while($r1 = $read_g->fetchrow_hashref()) {
        $grams .= "        <grammem>".tidy_xml($r1->{'inner_id'})."</grammem>\n";
    }
    $grams .= "    </group>\n";
}
$grams .= "</grammems>\n";

print $header.$grams;

# lemmata
print "<lemmata>\n";

my $flag = 1;
my $min_lid = 0;

while ($flag) {
    $flag = 0;
    $read_l->execute($min_lid + 1, $min_lid + 50000);
    while($r = $read_l->fetchrow_hashref()) {
        $flag = 1;
        $r->{'rev_text'} =~ s/<\/?dr>//g;
        print '    <lemma id="'.$r->{'lemma_id'}.'" rev="'.$r->{'rev_id'}.'">'.$r->{'rev_text'}."</lemma>\n";
    }
    $min_lid += 50000;
}

print "</lemmata>\n";

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
