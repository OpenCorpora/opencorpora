{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript">
$(document).ready(function(){
{literal}
    $("#show_more_users").click(function(event){
        $("#users_table tr:hidden").each(function(i, el) {
            $(el).show();
            if (i == 19)
                return false;
        });
        if ($("#users_table tr:hidden").length == 0)
            $(this).hide();
        event.preventDefault();
    });
    $("#show_user").click(function(event) {
        $("a[name='user{/literal}{if $is_logged}{$smarty.session.user_id}{/if}{literal}']").closest('tr').show().addClass('bgyellow');
    });
    $("a[name='" + location.hash.substring(1) +"']").closest('tr').addClass('bgyellow');
{/literal}
});
</script>
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li class="active"><a href="?page=stats">Общая</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
    <li><a href="?page=charts">Графики</a></li>
</ul>
{*<div id="chart" style="width:700px; height: 400px"></div>
<br/>*}
<a name="users"></a>
<h3>Участники по количеству размеченных примеров (всего размечено {$ma_count})</h3>
<table class="table table-condensed" id="users_table">
<tr>
    <th rowspan='2'>#</th>
    <th rowspan='2'>Участник</th>
    <th rowspan='2'>Всего</th>
    <th colspan='2'>В завершённых пулах</th>
    <th colspan='2'>В проверенных пулах</th>
    <th rowspan='2'>Последняя<br/>активность</th></tr>
<tr><th>Размечено<th>% расхождений<th>Размечено<th>% ошибок</tr>
{foreach item=s from=$user_stats.annotators}
    <tr {if $s@iteration>20 && (!isset($smarty.session.user_id) || $smarty.session.user_id != $s.user_id)}style="display:none;"{/if}>
        <td><a name="user{$s.user_id}"></a>{$s@iteration}
        <td>{$s.fin.user_name}
        <td>{$s.total}
        <td>{$s.fin.value|default:'0'}
        <td>{if isset($s.fin.divergence)}{$s.fin.divergence|string_format:"%.1f%%"}{else}&mdash;{/if}
        <td>{$s.fin.moderated|default:'0'}
        <td>{$s.fin.error_rate|string_format:"%.1f%%"}
        <td>
            {if $s.fin.last_active > $stats.timestamp_today}сегодня в {$s.fin.last_active|date_format:"%H:%M"}
            {elseif $s.fin.last_active > $stats.timestamp_yesterday}вчера в {$s.fin.last_active|date_format:"%H:%M"}
            {else}{$s.fin.last_active|date_format:"%d.%m.%y"}{/if}
    </tr>
{/foreach}
</table>
<a href="#" class="pseudo" id="show_more_users">Показать ещё 20 участников</a>
{if $is_logged}или <a href="#user{$smarty.session.user_id}" class="pseudo" id="show_user">найти меня</a>{/if}
<h3>Команды по количеству размеченных примеров</h3>
<table class="table">
    <tr><th>#</th><th>Название</th><th>Количество участников</th><th>Размечено примеров</th><th>Проверено</th><th>% ошибок</th></tr>
    {foreach $user_stats.teams as $i=>$team name=x}
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
{if $stats.added_sentences_last_week}
<h3>За последнюю неделю</h3>
<ol>
{foreach item=s from=$stats.added_sentences_last_week}
    <li>{$s.user_name} ({$s.value})</li>
{/foreach}
</ol>
{/if}
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
    <td><a href="books.php?book_id=1675">Юридические тексты</a></td>
    <td><b>{$stats.law_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.law_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.law_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.law_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.law_words}'>{$stats.percent_words.law}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.law}%"></div></div>
    </td>
</tr>
<tr>
    <td><a href="books.php?book_id=1651">Другое</a></td>
    <td><b>{$stats.misc_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.misc_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.misc_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.misc_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.misc_words}'>{$stats.percent_words.misc}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.misc}%"></div></div>
    </td>
</tr>
<tr>
    <th>Всего</th>
    <td><b>{$stats.total_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.total_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.total_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.total_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.total_words}'>{$stats.percent_words.total}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.total}%"></div></div>
    </td>
</tr>
</table>
<h2>Словарь</h2>
Лемм: {$stats.total_lemmata.value|number_format:0:'':' '}
{/block}
