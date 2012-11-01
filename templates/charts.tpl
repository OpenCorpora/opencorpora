{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.min.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="{$web_prefix}/js/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
{literal}
$(document).ready(function() {
    var options1 = {
        xaxis: {mode:"time", timeformat: "%d %b", monthNames: ["янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"]},
        yaxis: {max: 1000000},
        legend: {position: "nw"},
        series: {lines: {fill: true}},
        selection: {mode: "x"}
    };
    var chaskor_words = {label: "ЧасКор (статьи)", data: [{/literal}{$words_chart.chaskor_words}{literal}]};
    var wikinews_words = {label: "Викиновости", data: [{/literal}{$words_chart.wikinews_words}{literal}]};
    var wikipedia_words = {label: "Википедия", data: [{/literal}{$words_chart.wikipedia_words}{literal}]};
    var blogs_words = {label: "Блоги", data: [{/literal}{$words_chart.blogs_words}{literal}]};
    var chaskor_news_words = {label: "ЧасКор (новости)", data: [{/literal}{$words_chart.chaskor_news_words}{literal}]};
    var fiction_words = {label: "Худож. литература", data: [{/literal}{$words_chart.fiction_words}{literal}]};
    var data = [chaskor_words, wikinews_words, wikipedia_words, blogs_words, chaskor_news_words, fiction_words];

    $.plot($("#words_chart"), data, options1);

    var options2 = {
        xaxis: {mode:"time", timeformat: "%d %b", monthNames: ["янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"]},
        yaxes: [{}, {position: 'right'}],
        legend: {position: "sw"},
    };
    var avg_parses = {label: "разборов на слово", data: [{/literal}{$ambig_chart.avg_parses}{literal}], yaxis: 2, lines: {show: true}};
    var non_ambig = {label: "% однозначных", data: [{/literal}{$ambig_chart.non_ambig}{literal}], yaxis: 2, lines: {show: true}, color: 'green'};
    var unknown = {label: "% неизвестных", data: [{/literal}{$ambig_chart.unknown}{literal}], yaxis: 2, lines: {show: true}, color: 'red'};
    var total_words = {label: "всего слов", data: [{/literal}{$ambig_chart.total_words}{literal}], lines: {show:true, fill: true}};

    $.plot($("#ambig_chart1"), [total_words, avg_parses], options2);
    $.plot($("#ambig_chart2"), [total_words, non_ambig], options2);
    $.plot($("#ambig_chart3"), [total_words, unknown], options2);
});
{/literal}
</script>
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li><a href="?page=stats">Общая</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
    <li class="active"><a href="?page=charts">Графики</a></li>
</ul>
<h2>Состав корпуса</h2>
<div id="words_chart" style="width:700px; height: 400px"></div>
<h2>Неоднозначность</h2>
<h3>Среднее количество разборов</h3>
<div id="ambig_chart1" style="width:700px; height: 400px"></div>
<h3>% однозначно разобранных слов</h3>
<div id="ambig_chart2" style="width:700px; height: 400px"></div>
<h3>% неизвестных слов</h3>
<div id="ambig_chart3" style="width:700px; height: 400px"></div>
{/block}
