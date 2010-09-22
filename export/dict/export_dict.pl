#!/usr/bin/perl

use strict;
use utf8;
use DBI;
use HTML::Entities qw/encode_entities/;

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
my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");

my $rev = $dbh->prepare("SELECT MAX(rev_id) AS m FROM dict_revisions");
my $read_gg = $dbh->prepare("SELECT * FROM gram_types ORDER by `orderby`");
my $read_g = $dbh->prepare("SELECT inner_id FROM gram WHERE gram_type=? ORDER BY `orderby`");
my $read_l = $dbh->prepare("SELECT * FROM (SELECT lemma_id, rev_id, rev_text FROM dict_revisions WHERE lemma_id<10 ORDER BY lemma_id, rev_id DESC) T GROUP BY T.lemma_id");

$rev->execute();
my $r = $rev->fetchrow_hashref();
my $r1;
my $maxrev = $r->{'m'};

my $header = "<?xml version=\"1.0\" encoding=\"utf8\" standalone=\"yes\"?>\n<dictionary version=\"0.7\" revision=\"$maxrev\">\n";
my $footer = "</dictionary>";
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
my $lemmata = "<lemmata>\n";

$read_l->execute();
while($r = $read_l->fetchrow_hashref()) {
    $r->{'rev_text'} =~ s/<l/<l id="$r->{'lemma_id'}" rev="$r->{'rev_id'}"/;
    $r->{'rev_text'} =~ s/<\/?dr>//g;
    $lemmata .= '    '.$r->{'rev_text'}."\n";
}
$lemmata .= "</lemmata>\n";

print $header.$grams.$lemmata.$footer."\n";

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
