#!/usr/bin/perl
use strict;
use utf8;
use DBI;
use Encode;

my $lock_path = "/var/lock/opcorpora_updtstats.lock";
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

my %bookid2wordnum;
my %tags;
my $ref;
my $prefix;

my $dbh = DBI->connect('DBI:mysql:'.$mysql{'dbname'}.':'.$mysql{'host'}, $mysql{'user'}, $mysql{'passwd'}) or die $DBI::errstr;
$dbh->do("SET NAMES utf8");
my $scan_books = $dbh->prepare("SELECT book_id, tag_name FROM book_tags WHERE tag_name NOT LIKE 'url:%' AND tag_name NOT LIKE 'Дата:%' ORDER BY book_id");
my $count_words = $dbh->prepare("SELECT COUNT(*) AS cnt FROM text_forms WHERE sent_id IN (SELECT sent_id FROM sentences WHERE par_id IN (SELECT par_id FROM paragraphs WHERE book_id=?)) AND tf_text REGEXP '[А-Яа-яЁё]'");
my $drop = $dbh->prepare("DELETE FROM tag_stats");
my $ins = $dbh->prepare("INSERT INTO tag_stats VALUES(?, ?, ?, ?)");

$scan_books->execute();
while ($ref = $scan_books->fetchrow_hashref()) {
    if (!exists $bookid2wordnum{$ref->{'book_id'}}) {
        $count_words->execute($ref->{'book_id'});
        $bookid2wordnum{$ref->{'book_id'}} = $count_words->fetchrow_hashref()->{'cnt'};
    }

    next if $bookid2wordnum{$ref->{'book_id'}} == 0;

    printf STDERR "tag_name = <%s>\n", $ref->{'tag_name'};
    if ($ref->{'tag_name'} =~ /^([^\:]+)\:(.+)/) {
        my ($pre, $val) = ($1, $2);
        $val =~ s/^\s+//;
        $val =~ s/\s+$//;
        $tags{$pre}{$val}{'texts'}++;
        $tags{$pre}{$val}{'words'} += $bookid2wordnum{$ref->{'book_id'}};
    }
}

$drop->execute();
for my $pre(keys %tags) {
    for my $main(keys %{$tags{$pre}}) {
        printf STDERR "%s/%s %d txt, %d wds\n", $pre, $main, $tags{$pre}{$main}{'texts'}, $tags{$pre}{$main}{'words'};
        $ins->execute($pre, $main, $tags{$pre}{$main}{'texts'}, $tags{$pre}{$main}{'words'});
    }
}

unlink ($lock_path);
