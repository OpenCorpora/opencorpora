{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Результаты поиска</h1>
<p>Всего найдено: <b>{$search.total}</b></p>
{foreach from=$search.results item=s name=m}
        <p><a href="sentence.php?id={$s.sentence_id}">{$smarty.foreach.m.index + 1}</a>. {foreach from=$s.context item=word key=tid}{if $tid == $s.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>{else}{$word|htmlspecialchars}{/if} {/foreach}</p>
{/foreach}
{/block}
