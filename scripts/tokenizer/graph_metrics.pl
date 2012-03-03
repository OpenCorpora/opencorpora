#!/usr/bin/env perl

use strict;
use warnings;

use Pod::Usage;
use Getopt::Long;
use Chart::Gnuplot;
use Parallel::Jobs qw(start_job watch_jobs);

GetOptions(
    \my %opts,
    'help',
    'step=f',
    'jobs=i',
    'config=s',
    'script=s',
    'data_dir=s',
    'output_dir=s',
);
usage(2) if $opts{help};
usage() unless defined $opts{config};

if(
    defined $opts{step}
    and (
        $opts{step} > 1
        or $opts{step} < 0
    )
)
{
    die "--step value '$opts{step}' doesn't make sense";
}

$opts{jobs}       ||= 2;
$opts{step}       ||= 0.01;
$opts{output_dir} ||= '.';
$opts{script}     ||= 'calculate_metrics.pl';

my $fmt =()= (split /\./, $opts{step})[1] =~ /([0-9])/g;

my(@precision, @recall, @F);

my $threshold = 0;
while($threshold < 1) {
    for(1 .. $opts{jobs}) {
        my @cmd = (
            'perl',
            "-I$ENV{HOME}/Lingua--RU--OpenCorpora--Tokenizer/blib/lib",
            $opts{script},
            "--config=$opts{config}",
            sprintf("--threshold=%.${fmt}f", $threshold),
            defined $opts{data_dir}
                ? ("--data_dir=$opts{data_dir}")
                : (),
        );
        start_job(
            {
                stdout_capture => 1,
            },
            @cmd,
        ) or die "failed to execute '@{[ join ' ', @cmd]}'";

        $threshold += $opts{step};
        last if $threshold >= 1;
    }

    while(my(undef, $event, $data) = watch_jobs()) {
        if($event eq 'STDOUT') {
            $data =~ /Threshold: (.+?), Precision: (.+?), Recall: (.+?), F1: (.+?)$/ or next;

            push @precision, [$1, $2];
            push @recall, [$1, $3];
            push @F, [$1, $4];
        }
    }
}

my @datasets;
for(
    [\@precision, 'precision'],
    [\@recall, 'recall'],
    [\@F, 'F-score'],
)
{
    my($best_threshold, $best_value) = find_best(@$_);
    printf "%s: max=%.4f threshold=%.${fmt}f\n", $_->[1], $best_value, $best_threshold;

    my $fn = dump_data(@$_);
    push @datasets, Chart::Gnuplot::DataSet->new(
        datafile => $fn,
        title    => $_->[1],
        style    => 'lines',
        width    => 2,
    );
}

my $chart = Chart::Gnuplot->new(
    output => "$opts{output_dir}/metrics.png",
    title  => 'Tokenizer metrics',
    xlabel => 'Threshold',
    ylabel => 'Metrics',
    grid   => {
        width => 1,
    },
    legend => {
        position => 'outside center bottom',
        order    => 'horizontal',
        border   => 'on',
    },
);
$chart->plot2d(@datasets);

sub find_best {
    my($data, $name) = @_;

    my $max_idx = 0;
    for(0 .. $#{ $data }) {
        $max_idx = $_ if $data->[$_][1] > $data->[$max_idx][1];
    }

    ($data->[$max_idx][0], $data->[$max_idx][1]);
}

sub dump_data {
    my($data, $name) = @_;

    my $fn = "$opts{output_dir}/$name.dat";
    open my $fh, '>', $fn or die "$fn: $!";
    print $fh join "\n",
              map join(' ', @$_),
              sort { $a->[0] <=> $b->[0] }
              @{ $_->[0] };
    close $fh;

    $fn;
}

sub usage { pod2usage({-verbose => $_[0]}) }

__END__

=head1 SYNOPSIS

perl metrics.pl --options

=head1 DESCRIPTION

This script builds a graph of metrics by running calculate_metrics.pl with different threshold value.
It also prints out optimal threshold value for each metric.

Output files are:

=over 4

=item metrics.png

The graph itself.

=item {precision, recall, F-score}.dat

For each metric the script spews out a .dat file with the source data used to build the graph.

=back

=head1 OPTIONS

=over 4

=item --config

Required.

Path to opencorpora config file.

=item --step

Optional.

Increment value for tokenizer's threshold. Must be between [0, 1]. Defaults to 0.01.

Be careful with this value, it can cause the script to consume a whole lot of time.

Make sure you provide a "round" value (e.g. 0.1, 0.0001). Providing something like 0.333 may result in undefined behaviour.

=item --jobs

Optional.

Number of parallel jobs to run. Defaults to 2.

Useful value would be the number of cores/processors your server has.
Note that the right value for this argument may save you a lot of time.

=item --output_dir

Optional.

Path to output directory. Defaults to current directory.

=item --data_dir

Optional.

Path to tokenizer's data directory. Defaults to distribution directory.

=item --script

Optional.

Path to the metrics calculating script. Defaults to calculate_metrics.pl in current directory.

=item --help

Show this message.

=back
