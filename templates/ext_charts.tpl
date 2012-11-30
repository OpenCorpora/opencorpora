{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.js"></script>
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.pie.js"></script>
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.stack.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="{$web_prefix}/js/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
{literal}
$(document).ready(function() {
    var data = {/literal}{$charts.data}{literal};
    var data2 = {/literal}{$charts.data2}{literal};
    var stack_opts = {
        series: {
            stack: true,
            bars: {show: true, horizontal: true, barWidth: 0.6, align: 'center'}
        },
        yaxis: {
            ticks: {/literal}{$charts.ticks}{literal}
        },
        legend: {
            position: 'se'
        }
    };
    $.plot($("#words_chart"), data, stack_opts);

    $("#toggle_chart").click(function() {
        var $this = $(this);
        $this.attr('rel', 1 - $this.attr('rel'));

        if ($this.attr('rel') == 1) {
            this.innerHTML = 'вернуть';
            $.plot($("#words_chart"), data2, stack_opts);
        }
        else {
            this.innerHTML = 'привести все к 100%';
            $.plot($("#words_chart"), data, stack_opts);
        }
    });
});
{/literal}
</script>
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li><a href="?page=stats&weekly">Активность за неделю</a></li>
    <li><a href="?page=genre_stats">Состав корпуса</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
    <li><a href="?page=charts">Графики</a></li>
    <li class="active"><a href="?page=ext_charts">Спецграфики</a></li>
</ul>
<h2>Распределение пулов</h2>
<p><a href="#" id="toggle_chart" class="pseudo" rel="0">привести все к 100%</a></p>
<div id="words_chart" style="width:100%; height: 1000px"></div>
{/block}
