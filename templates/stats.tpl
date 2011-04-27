{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>{t}Статистика{/t}</h1>
<a href="{$web_prefix}/books.php">{t}Книг{/t}</a>: {$stats.total_books.value} <span class='small'>({$stats.total_books.timestamp|date_format:"%d.%m.%y, %H:%m"})</span><br/>
{t}Предложений{/t}: {$stats.total_sentences.value} <span class='small'>({$stats.total_sentences.timestamp|date_format:"%d.%m.%y, %H:%m"})</span><br/>
{t}Токенов{/t}: {$stats.total_tokens.value} <span class='small'>({$stats.total_tokens.timestamp|date_format:"%d.%m.%y, %H:%m"})</span><br/>
&nbsp;&nbsp;&nbsp;&nbsp;{t}Словоупотреблений{/t}: {$stats.total_words.value} <span class='small'>({$stats.total_words.timestamp|date_format:"%d.%m.%y, %H:%m"})</span><br/>
<h2>{t}Словарь{/t}</h2>
{t}Лемм{/t}: {$stats.total_lemmata.value} <span class='small'>({$stats.total_lemmata.timestamp|date_format:"%d.%m.%y, %H:%m"})</span><br/>
{/block}
