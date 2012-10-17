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
/*    var options1 = {
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
    };*/
    //$.plot($("#adder_chart"), [{/literal}{$stats._chart.user_stats_full}{literal}], options2);
    //$.plot($("#week_adder_chart"), [{/literal}{$stats._chart.user_stats_week}{literal}], options2);
    $("#show_all_users").click(function(event){
        $("#users_table tr").show();
        $(this).hide();
        event.preventDefault();
    });
    $("a[name='" + location.hash.substring(1) +"']").closest('tr').addClass('bgyellow');
{/literal}
});
</script>
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li class="active"><a href="?page=stats">Общая</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
</ul>
{*<div id="chart" style="width:700px; height: 400px"></div>
<br/>*}
<a name="users"></a>
<h3>Участники по количеству размеченных примеров (всего {$ma_count})</h3>
<table class="table table-condensed" id="users_table">
<tr>
    <th rowspan='2'>#</th>
    <th rowspan='2'>Участник</th>
    <th rowspan='2'>Всего</th>
    <th colspan='2'>В завершённых пулах</th>
    <th colspan='2'>В проверенных пулах</th>
    <th rowspan='2'>Последняя<br/>активность</th></tr>
<tr><th>Размечено<th>% расхождений<th>Размечено<th>% ошибок</tr>
{foreach item=s from=$stats.annotators}
    <tr {if $s@iteration>20 && (!isset($smarty.session.user_id) || $smarty.session.user_id != $s.user_id)}style="display:none;"{/if}>
        <td><a name="user{$s.user_id}"></a>{$s@iteration}
        <td>{$s.fin.user_name}
        <td>{$s.total}
        <td>{$s.fin.value|default:'0'}
        <td>{$s.fin.divergence|string_format:"%.1f%%"}
        <td>{$s.fin.moderated|default:'0'}
        <td>{$s.fin.error_rate|string_format:"%.1f%%"}
        <td>
            {if $s.fin.last_active > $stats.timestamp_today}сегодня в {$s.fin.last_active|date_format:"%H:%M"}
            {elseif $s.fin.last_active > $stats.timestamp_yesterday}вчера в {$s.fin.last_active|date_format:"%H:%M"}
            {else}{$s.fin.last_active|date_format:"%d.%m.%y"}{/if}
    </tr>
{/foreach}
</table>
<a href="#" class="pseudo" id="show_all_users">Показать всех участников</a>
<h3>Команды по количеству размеченных примеров</h3>
<table class="table">
    <tr><th>#</th><th>Название</th><th>Количество участников</th><th>Размечено примеров</th><th>Проверено</th><th>% ошибок</th></tr>
    {foreach $stats.teams as $i=>$team name=x}
        <tr>
            <td>{$team@iteration}</td>
            <td>{$team.name}</td>
            <td>{$team.num_users}</td>
            <td>{$team.total}</td>
            <td>{$team.moderated}</td>
            <td>{$team.error_rate|string_format:"%.1f%%"}</td>
        </tr>
    {/foreach}
</table>
<h3>Участники по количеству добавленных предложений</h3>
<ol>
{foreach item=s from=$stats.added_sentences}
    <li>{$s.user_name} ({$s.value})</li>
{/foreach}
</ol>
<!--div id="adder_chart" style="width:700px; height:400px"></div-->
{if $stats.added_sentences_last_week}
<h3>За последнюю неделю</h3>
<ol>
{foreach item=s from=$stats.added_sentences_last_week}
    <li>{$s.user_name} ({$s.value})</li>
{/foreach}
</ol>
{/if}
<!--div id="week_adder_chart" style="width:700px; height:400px"></div-->
<h3>Наполнение корпуса</h3>
<p>Таблица показывает, какие тексты и в каком количестве сейчас есть в корпусе.</p>
<table class="table">
<tr>
    <th>Источник</th>
    <th><a href="{$web_prefix}/books.php">Текстов</a></th>
    <th>Предложений</th>
    <th>Токенов</th>
    <th>Словоупотреблений</th>
</tr>
<tr>
    <td><a href="books.php?book_id=1">ЧасКор (статьи)</a></td>
    <td><b>{$stats.chaskor_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.chaskor_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.chaskor_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.chaskor_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.chaskor_words}'>{$stats.percent_words.chaskor}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {min($stats.percent_words.chaskor,100)}%"></div></div>
    </td>
</tr>
<tr>
    <td><a href="books.php?book_id=226">ЧасКор (новости)</a></td>
    <td><b>{$stats.chaskor_news_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.chaskor_news_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.chaskor_news_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.chaskor_news_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.chaskor_news_words}'>{$stats.percent_words.chaskor_news}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.chaskor_news}%"></div></div>
    </td>
</tr>
<tr>
    <td><a href="books.php?book_id=8">Википедия</a></td>
    <td><b>{$stats.wikipedia_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.wikipedia_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.wikipedia_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.wikipedia_words.value|number_format:0:'':' '}</b> =  <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.wikipedia_words}'>{$stats.percent_words.wikipedia}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.wikipedia}%"></div></div>
    </td>
</tr>
<tr>
    <td><a href="books.php?book_id=56">Викиновости</a></td>
    <td><b>{$stats.wikinews_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.wikinews_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.wikinews_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.wikinews_words.value|number_format:0:'':' '}</b> =  <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.wikinews_words}'>{$stats.percent_words.wikinews}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.wikinews}%"></div></div>
    </td>
</tr>
<tr>
    <td><a href="books.php?book_id=184">Блоги</a></td>
    <td><b>{$stats.blogs_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.blogs_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.blogs_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.blogs_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.blogs_words}'>{$stats.percent_words.blogs}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.blogs}%"></div></div>
    </td>
</tr>
<tr>

    <td><a href="books.php?book_id=806">Худож. литература</a></td>
    <td><b>{$stats.fiction_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.fiction_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.fiction_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.fiction_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.fiction_words}'>{$stats.percent_words.fiction}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.fiction}%"></div></div>
    </td>
</tr>
<tr>
    <th>Всего</th>
    <td><b>{$stats.total_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.total_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.total_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.total_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.total_words}'>{$stats.percent_words.total}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.total}px"></div></div>
    </td>
</tr>
</table>
<h2>Словарь</h2>
Лемм: {$stats.total_lemmata.value|number_format:0:'':' '}
{/block}
