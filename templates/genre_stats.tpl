{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li><a href="?page=stats&weekly">Активность за неделю</a></li>
    <li class="active"><a href="?page=genre_stats">Состав корпуса</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
    <li><a href="?page=charts">Графики</a></li>
</ul>
{*<div id="chart" style="width:700px; height: 400px"></div>
<br/>*}
<h2>Наполнение корпуса</h2>
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
    <td><a href="books.php?book_id=2037">Нон-фикшн</a></td>
    <td><b>{$stats.nonfiction_books.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.nonfiction_sentences.value|number_format:0:'':' '}</b></td>
    <td><b>{$stats.nonfiction_tokens.value|number_format:0:'':' '}</b></td>
    <td>
        <p><b>{$stats.nonfiction_words.value|number_format:0:'':' '}</b> = <span class='hint' title='Цель до июля 2012 года &ndash; {$goals.nonfiction_words}'>{$stats.percent_words.nonfiction}%</span></p>
        <div class="progress" style="width: 200px;"><div class="bar" style="width: {$stats.percent_words.nonfiction}%"></div></div>
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
{/block}
