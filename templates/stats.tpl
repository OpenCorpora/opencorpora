{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Статистика{/t}</h1>
<h2>{t}Общая{/t}</h2>
<table border='1' cellspacing='0' cellpadding='3'>
<tr>
    <th>{t}Источник{/t}</th>
    <th><a href="{$web_prefix}/books.php">{t}Текстов{/t}</a></th>
    <th>{t}Предложений{/t}</th>
    <th>{t}Токенов{/t}</th>
    <th>{t}Словоупотреблений{/t}</th>
</tr>
<tr>
    <td align="center"><a href="books.php?book_id=1">{t}ЧасКор{/t}</a></td>
    <td align='right' valign='top'><b>{$stats.chaskor_books.value|number_format}</b><br/><span class='small'>{$stats.chaskor_books.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.chaskor_sentences.value|number_format}</b><br/><span class='small'>{$stats.chaskor_sentences.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right' valign='top'><b>{$stats.chaskor_tokens.value|number_format}</b><br/><span class='small'>{$stats.chaskor_tokens.timestamp|date_format:"%d.%m.%y, %H:%M"}</span></td>
    <td align='right'>
        <b>{$stats.chaskor_words.value|number_format}</b><br/><span class='small'>{$stats.chaskor_words.timestamp|date_format:"%d.%m.%y, %H:%M"}</span><br/>
        <table border='0'><tr>
        <td><span class='hint' title='Цель до конца 2011 года &ndash; {$goals.chaskor_words}'>{$stats.percent_words.chaskor}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.chaskor}px"></div></div></td>
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
        <td><span class='hint' title='Цель до конца 2011 года &ndash; {$goals.wikipedia_words}'>{$stats.percent_words.wikipedia}%</span></td>
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
        <td><span class='hint' title='Цель до конца 2011 года &ndash; {$goals.wikinews_words}'>{$stats.percent_words.wikinews}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.wikinews}px"></div></div></td>
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
        <td><span class='hint' title='Цель до конца 2011 года &ndash; {$goals.total_words}'>{$stats.percent_words.total}%</span></td>
        <td><div class="progress"><div class="progress_load" style="width: {$stats.percent_words.total}px"></div></div></td>
        </tr></table>
    </td>
</tr>
</table>
<h2>{t}Словарь{/t}</h2>
{t}Лемм{/t}: {$stats.total_lemmata.value|number_format} <span class='small'>({$stats.total_lemmata.timestamp|date_format:"%d.%m.%y, %H:%M"})</span><br/>
<h2>{t}Пользователи{/t}</h2>
<h3>{t}По количеству добавленных предложений{/t}</h3>
<ol>
{foreach item=s from=$stats.added_sentences}
    <li>{$s.user_name} ({$s.value}) <span class='small'>({$s.timestamp|date_format:"%d.%m.%y, %H:%M"})</li>
{/foreach}
</ol>
{/block}
