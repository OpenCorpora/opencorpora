{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript" src="/assets/js/jquery.flot.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.flot.time.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.flot.pie.min.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/assets/js/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
{literal}
$(document).ready(function() {
    var options1 = {
        xaxis: {mode:"time", timeformat: "%d %b", monthNames: ["янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"]},
        yaxis: {max: 1750000},
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
    var nonfiction_words = {label: "Нон-фикшн", data: [{/literal}{$words_chart.nonfiction_words}{literal}]};
    var law_words = {label: "Юридические тексты", data: [{/literal}{$words_chart.law_words}{literal}]};
    var misc_words = {label: "Другое", data: [{/literal}{$words_chart.misc_words}{literal}]};
    var data = [chaskor_words, wikinews_words, wikipedia_words, blogs_words, chaskor_news_words, fiction_words, nonfiction_words, law_words, misc_words];

    $.plot($("#words_chart"), data, options1);

    var options2 = {
        xaxis: {mode:"time", timeformat: "%d %b", monthNames: ["янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"]},
        yaxes: [{}, {position: 'right', minTickSize: 0.001}],
        legend: {position: "sw"},
    };
    var avg_parses = {label: "разборов на слово", data: [{/literal}{$ambig_chart.avg_parses}{literal}], yaxis: 2, lines: {show: true}};
    var non_ambig = {label: "% однозначных", data: [{/literal}{$ambig_chart.non_ambig}{literal}], yaxis: 2, lines: {show: true}, color: 'green'};
    var unknown = {label: "% неизвестных", data: [{/literal}{$ambig_chart.unknown}{literal}], yaxis: 2, lines: {show: true}, color: 'red'};
    var disamb_sents = {label: "однозначных предложений", data: [{/literal}{$ambig_chart.disamb_sentences}{literal}], yaxis: 1, lines: {show: true}, color: 'navy'}
    var disamb_sents2 = {data: [{/literal}{$ambig_chart.disamb_sentences_strict}{literal}], yaxis: 1, lines: {show: true, lineWidth: 1}, color: 'blue', label: "то же без UNKN"}
    var disamb_sent_length = {label: "средняя длина (слов)", data: [{/literal}{$ambig_chart.disamb_sent_length}{literal}], yaxis: 2, lines: {show: true}, color: 'green'}
    var disamb_sent_length2 = {data: [{/literal}{$ambig_chart.disamb_sent_strict_length}{literal}], yaxis: 2, lines: {show: true, lineWidth: 1}, color: 'lime', label: "то же без UNKN"}
    var total_words = {label: "всего слов", data: [{/literal}{$ambig_chart.total_words}{literal}], lines: {show:true, fill: true}};
    var users_by_day = {label: "размечавших", data: [{/literal}{$annot_chart.users}{literal}], yaxis:2, lines: {show: true}, color: 'navy'}
    var samples_by_day = {label: "ответов", data: [{/literal}{$annot_chart.samples}{literal}], lines: {show:true, fill:true}}
    var pools = [
        {label: 'готовится', data: {/literal}{$pools_stats[2]}{literal}},
        {label: 'размечается', data: {/literal}{$pools_stats[3]}{literal}},
        {label: 'размечено', data: {/literal}{$pools_stats[4]}{literal}},
        {label: 'на модерации', data: {/literal}{$pools_stats[5] + $pools_stats[6]}{literal}},
        {label: 'ушло в корпус', data: {/literal}{$pools_stats[9]}{literal}},
    ];

    var pie_options = {
        series: {
            pie: {
                show: true,
                radius: 1,
                label: {
                    show: true,
                    radius: 5/6,
                    formatter: function(label, series) {
                        return '<div style="text-align: center; padding: 2px; color: white">' + Math.round(series.percent) + '%</div>'
                    },
                    threshold: 0.02,
                    background: {
                        opacity: 0.5,
                        color: '#000'
                    }
                }
            }
        }
    };

    $.plot($("#ambig_chart1"), [total_words, avg_parses], options2);
    $.plot($("#ambig_chart2"), [total_words, non_ambig], options2);
    $.plot($("#ambig_chart3"), [total_words, unknown], options2);
    $.plot($("#ambig_chart4"), [disamb_sents, disamb_sents2, disamb_sent_length, disamb_sent_length2], options2);
    $.plot($("#pools_chart"), pools, pie_options);
    options2['legend']['position'] = 'nw';
    options2['yaxis'] = {min: 0};
    $.plot($("#users_chart"), [samples_by_day, users_by_day], options2);
});
{/literal}
</script>
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li><a href="?page=stats&weekly">Активность за неделю</a></li>
    <li><a href="?page=genre_stats">Состав корпуса</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
    <li class="active"><a href="?page=charts">Графики</a></li>
</ul>
<h2>Состав корпуса</h2>
<div id="words_chart" style="width:1000px; height: 400px"></div>
<h2>Неоднозначность</h2>
<h3>Среднее количество разборов</h3>
<div id="ambig_chart1" style="width:1000px; height: 400px"></div>
<h3>% однозначно разобранных слов</h3>
<div id="ambig_chart2" style="width:1000px; height: 400px"></div>
<h3>Однозначные разборы</h3>
<div id="ambig_chart4" style="width:1000px; height: 400px"></div>
<h3>% неизвестных слов</h3>
<div id="ambig_chart3" style="width:1000px; height: 400px"></div>
<h2>Задания на разметку</h2>
<div id="pools_chart" style="width:1000px; height: 400px"></div>
<h3>Ответов и участников в день</h3>
<div id="users_chart" style="width:1000px; height: 400px"></div>
{/block}
