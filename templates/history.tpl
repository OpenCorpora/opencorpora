{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Свежие правки в разметке</h1>
{if !isset($smarty.get.sent_id)}
    <p>Всего: {$history.total}. <a href='?skip={$skip}&amp;maa={1 - $maa}'>Показать {if $maa}все{else}только слияния и разбиения{/if}</a></p>
    <ul class="pager">
        {if $history.total > ($skip + 20)}<li class="next"><a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip + 20}&amp;maa={$maa}&amp;user_id={$user_id}'>раньше &rarr;</a></li>{else}<li class="next disabled"><a href="#">раньше &rarr;</a></li>{/if}
        {if $skip > 0}<li class="previous"><a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip - 20}&amp;maa={$maa}&amp;user_id={$user_id}'>&larr; позже</a></li>{else}<li class="previous disabled"><a href="#">&larr; позже</a></li>{/if}
    </ul>
{/if}
<table class="table">
{foreach from=$history.sets item=h}
<tr>
	<td>{$h.set_id}</td>
	<td>{$h.user_name|default:'Робот'}</td>
	<td>{$h.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
	{if $h.sent_cnt}
        <td><a href="?set_id={$h.set_id}&amp;maa={$maa}">{$h.sent_cnt} предл.</a></td>
    {/if}
    {if isset($h.sent_id)}
        <td><a href="sentence.php?id={$h.sent_id}">Предложение {$h.sent_id}</a></td>
        <td><a href="diff.php?sent_id={$h.sent_id}&amp;set_id={$h.set_id}">Изменения</a></td>
    {/if}
    <td>{if $h.comment_html}
            {$h.comment_html}
        {elseif $h.comment}
            {$h.comment|htmlspecialchars}
        {else}
            (без комментария)
        {/if}
    </td>
</tr>
{/foreach}
</table>
{if !isset($smarty.get.sent_id)}
    <ul class="pager">
        {if $history.total > ($skip + 20)}<li class="next"><a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip + 20}&amp;maa={$maa}&amp;user_id={$user_id}'>раньше &rarr;</a></li>{else}<li class="next disabled"><a href="#">раньше &rarr;</a></li>{/if}
        {if $skip > 0}<li class="previous"><a href='?{if isset($smarty.get.set_id)}set_id={$smarty.get.set_id}&amp;{/if}skip={$skip - 20}&amp;maa={$maa}&amp;user_id={$user_id}'>&larr; позже</a></li>{else}<li class="previous disabled"><a href="#">&larr; позже</a></li>{/if}
    </ul>
{/if}
{/block}
