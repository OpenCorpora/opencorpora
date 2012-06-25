{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.min.js"></script>
<!--script type="text/javascript" src="{$web_prefix}/js/jquery.flot.selection.min.js"></script>
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.pie.js"></script-->
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="{$web_prefix}/js/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
$(document).ready(function(){
{literal}
    var options1 = {
        xaxis: {mode:"time", timeformat: "%d %b", monthNames: ["янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"]},
        yaxis: {max: 1000000},
        legend: {position: "nw"},
        series: {lines: {fill: true}},
        selection: {mode: "x"}
    };
    var chaskor_words = {label: "ЧасКор (статьи)", data: [{/literal}{$stats._chart.chaskor_words}{literal}]};
    var wikinews_words = {label: "Викиновости", data: [{/literal}{$stats._chart.wikinews_words}{literal}]};
    var wikipedia_words = {label: "Википедия", data: [{/literal}{$stats._chart.wikipedia_words}{literal}]};
    var blogs_words = {label: "Блоги", data: [{/literal}{$stats._chart.blogs_words}{literal}]};
    var chaskor_news_words = {label: "ЧасКор (новости)", data: [{/literal}{$stats._chart.chaskor_news_words}{literal}]};
    var fiction_words = {label: "Худож. литература", data: [{/literal}{$stats._chart.fiction_words}{literal}]};
    var data = [chaskor_words, wikinews_words, wikipedia_words, blogs_words, chaskor_news_words, fiction_words];
                                    
    $.plot($("#chart"), data, options1);
    $("#chart").bind("plotselected", function(event, ranges){
        $.plot("#chart", data, $.extend(true, {}, options1, {
            xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
        }));
    });
    
    var options2 = {
        series: {
            pie: {
                show: true,
                radius: 1,
                label: {
                    show: true,
                    radius: 2/3,
                    formatter: function(label, series) {
                        return '<div style="font-size:12px;text-align:center;padding:2px;color:white;">'+label+'<br/>'+series.data.toString().substring(2)+'</div>';
                    }
                },
                combine: {
                    threshold: 0.05,
                    color: '#999',
                    label: "Остальные"
                }
            }
        },
        legend: {
            show: false
        }
    };
    //$.plot($("#adder_chart"), [{/literal}{$stats._chart.user_stats_full}{literal}], options2);
    //$.plot($("#week_adder_chart"), [{/literal}{$stats._chart.user_stats_week}{literal}], options2);

{/literal}
});
</script>
<h1>{t}Статистика{/t}</h1>
<h2>{t}Общая{/t} | <a href="?page=tag_stats">{t}По тегам{/t}</a></h2>
<div id="chart" style="width:700px; height: 400px"></div>
<br/>
<table border='1' cellspacing='0' cellpadding='3'>
<tr>
    <th>{t}Источник{/t}</th>
    <th><a href="{$web_prefix}/books.php">{t}Текстов{/t}</a></th>
    <th>{t}Предложений{/t}</th>
    <th>{t}Токенов{/t}</th>
    <th>{t}Словоупотреблений{/t}</th>
</tr>
<tr>
    <td align="center"><a href="books.php?book_id=1">{t}ЧасКор{/t} (статьи)</a></td>
    <td align='right' valign='top'><b>{$stats.chaskor_books.value|number_format}</b><br/><span class='small'>{$stats.chaskor_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.chaskor_sentences.value|number_format}</b><br/><span class='small'>{$stats.chaskor_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.chaskor_tokens.value|number_format}</b><br/><span class='small'>{$stats.chaskor_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.chaskor_words.value|number_format}</b><br/><span class='small'>{$stats.chaskor_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.chaskor_words}'>{$stats.percent_words.chaskor}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.chaskor}px"></div></div></td>
        </tr></table>
    </td>
</tr>
<tr>
    <td align="center"><a href="books.php?book_id=226">{t}ЧасКор{/t} (новости)</a></td>
    <td align='right' valign='top'><b>{$stats.chaskor_news_books.value|number_format}</b><br/><span class='small'>{$stats.chaskor_news_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.chaskor_news_sentences.value|number_format}</b><br/><span class='small'>{$stats.chaskor_news_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.chaskor_news_tokens.value|number_format}</b><br/><span class='small'>{$stats.chaskor_news_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.chaskor_news_words.value|number_format}</b><br/><span class='small'>{$stats.chaskor_news_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.chaskor_news_words}'>{$stats.percent_words.chaskor_news}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.chaskor_news}px"></div></div></td>
        </tr></table>
    </td>
</tr>
<tr>
    <td align="center"><a href="books.php?book_id=8">{t}Википедия{/t}</a></td>
    <td align='right' valign='top'><b>{$stats.wikipedia_books.value|number_format}</b><br/><span class='small'>{$stats.wikipedia_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.wikipedia_sentences.value|number_format}</b><br/><span class='small'>{$stats.wikipedia_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.wikipedia_tokens.value|number_format}</b><br/><span class='small'>{$stats.wikipedia_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.wikipedia_words.value|number_format}</b><br/><span class='small'>{$stats.wikipedia_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.wikipedia_words}'>{$stats.percent_words.wikipedia}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.wikipedia}px"></div></div></td>
        </tr></table>
    </td>
</tr>
<tr>
    <td align="center"><a href="books.php?book_id=56">{t}Викиновости{/t}</a></td>
    <td align='right' valign='top'><b>{$stats.wikinews_books.value|number_format}</b><br/><span class='small'>{$stats.wikinews_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.wikinews_sentences.value|number_format}</b><br/><span class='small'>{$stats.wikinews_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.wikinews_tokens.value|number_format}</b><br/><span class='small'>{$stats.wikinews_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.wikinews_words.value|number_format}</b><br/><span class='small'>{$stats.wikinews_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.wikinews_words}'>{$stats.percent_words.wikinews}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.wikinews}px"></div></div></td>
        </tr></table>
    </td>
</tr>
<tr>
    <td align="center"><a href="books.php?book_id=184">{t}Блоги{/t}</a></td>
    <td align='right' valign='top'><b>{$stats.blogs_books.value|number_format}</b><br/><span class='small'>{$stats.blogs_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.blogs_sentences.value|number_format}</b><br/><span class='small'>{$stats.blogs_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.blogs_tokens.value|number_format}</b><br/><span class='small'>{$stats.blogs_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.blogs_words.value|number_format}</b><br/><span class='small'>{$stats.blogs_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.blogs_words}'>{$stats.percent_words.blogs}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.blogs}px"></div></div></td>
        </tr></table>
    </td>
</tr>
<tr>

    <td align="center"><a href="books.php?book_id=806">{t}Худож. литература{/t}</a></td>
    <td align='right' valign='top'><b>{$stats.fiction_books.value|number_format}</b><br/><span class='small'>{$stats.fiction_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.fiction_sentences.value|number_format}</b><br/><span class='small'>{$stats.fiction_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.fiction_tokens.value|number_format}</b><br/><span class='small'>{$stats.fiction_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.fiction_words.value|number_format}</b><br/><span class='small'>{$stats.fiction_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.fiction_words}'>{$stats.percent_words.fiction}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.fiction}px"></div></div></td>
        </tr></table>
    </td>
</tr>
<tr>
    <th>{t}Всего{/t}</th>
    <td align='right' valign='top'><b>{$stats.total_books.value|number_format}</b><br/><span class='small'>{$stats.total_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.total_sentences.value|number_format}</b><br/><span class='small'>{$stats.total_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.total_tokens.value|number_format}</b><br/><span class='small'>{$stats.total_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.total_words.value|number_format}</b><br/><span class='small'>{$stats.total_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до июля 2012 года &ndash; {$goals.total_words}'>{$stats.percent_words.total}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.total}px"></div></div></td>
        </tr></table>
    </td>
</tr>
</table>
<h2>{t}Словарь{/t}</h2>
{t}Лемм{/t}: {$stats.total_lemmata.value|number_format} <span class='small'>({$stats.total_lemmata.timestamp|date_format:"%d.%m.%y, %H:%M"})</span><br/>
<a name="users"></a><h2>{t}Пользователи по количеству добавленных предложений{/t}</h2>
<ol>
{foreach item=s from=$stats.added_sentences}
    <li>{$s.user_name} ({$s.value}) <span class='small'>({$s.timestamp|date_format:"%d.%m.%y, %H:%M"})</li>
{/foreach}
</ol>
<!--div id="adder_chart" style="width:700px; height:400px"></div-->
<h3>{t}За последнюю неделю{/t}</h3>
<ol>
{foreach item=s from=$stats.added_sentences_last_week}
    <li>{$s.user_name} ({$s.value}) <span class='small'>({$s.timestamp|date_format:"%d.%m.%y, %H:%M"})</li>
{/foreach}
</ol>
<h2>{t}Пользователи по количеству размеченных примеров{/t}</h2>
<table border='1' cellspacing='0' cellpadding='3'>
<tr><th>#<th>Пользователь<th>Размечено<th>% расхождений<th>Пересчитано</tr>
{foreach item=s from=$stats.annotators}
    <tr><td>{$s@iteration}<td>{$s.user_name}<td>{$s.value}<td>{$s.divergence|string_format:"%.1f%%"}<td class='small'>{$s.timestamp|date_format:"%d.%m.%y, %H:%M"}</tr>
{/foreach}
</table>
<p class='small'>Учитываются только полностью завершённые пулы.</p>
<!--div id="week_adder_chart" style="width:700px; height:400px"></div-->
{/block}
