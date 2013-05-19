{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Результаты поиска</h1>
<p>Всего найдено: <b>{$search.total}</b></p>
<ol>
{foreach from=$search.results item=s}
        <li>{foreach from=$s.context item=word name=x}{if $smarty.foreach.x.index == $s.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>{else}{$word|htmlspecialchars}{/if} {/foreach}</li>
{/foreach}
</ol>
{/block}
