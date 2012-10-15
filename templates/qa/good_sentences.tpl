{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Наименее омонимичные предложения</h1>
<p>Список обновляется раз в сутки.</p>
<p>{if isset($smarty.get.no_zero)}<a href="?act=good_sentences">показать неомонимичные</a>{else}<a href="?act=good_sentences&no_zero">скрыть неомонимичные</a>{/if}</p>
<table class="table">
<tr><td>#<td>Всего слов<td>Омонимичных слов</tr>
{foreach item=sentence from=$sentences}
<tr><td><a href="sentence.php?id={$sentence.id}">{$sentence.id}</a><td>{$sentence.total}<td>{$sentence.homonymous}</tr>
{/foreach}
</table>
{/block}
