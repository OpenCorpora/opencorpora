{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<ul class="breadcrumb">
    <li><a href="{$web_prefix}/dict.php">Словарь</a> <span class="divider">/</span></li>
    <li class="active">Top несловарных токенов</li>
</ul>
<h1>Top 500 токенов с UNKN</h1>
<ol>
{foreach from=$words item=word}
<li>{$word.word|htmlspecialchars} [{$word.count}]</li>
{/foreach}
</ol>
{/block}
