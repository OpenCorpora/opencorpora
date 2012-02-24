{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<p><a href="?">&lt;&lt; к списку пулов</a></p>
<h1>Пул &laquo;{$pool.name}&raquo;</h1>
{if $pool.status == 2}
Пул не опубликован. {if $is_admin}<form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button>Опубликовать</button></form>{/if}
{elseif $pool.status == 4}
Пул снят с публикации. {if $is_admin}<form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button>Опубликовать заново</button></form>{/if}
{elseif $pool.status == 3}
Пул опубликован. {if $is_admin}<form action="?act=unpublish&amp;pool_id={$pool.id}" method="post" class="inline"><button>Снять с публикации</button></form>{/if}
{/if}
{if $pool.status == 3 || $pool.status == 4}
<p>{if !isset($smarty.get.ext)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">к расширенному виду</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}">к обычному виду</a>{/if}</p>
<p>{if !isset($smarty.get.disagreed)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;disagreed">показать только несогласованные ответы</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if}</p>
{/if}
<br/><br/>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
    <th>id</th>
    <th>&nbsp;</th>
    <th>Ответов</th>
    {if isset($smarty.get.ext)}
    {for $i=1 to $pool.num_users}<th>{$i}</th>{/for}
    {else}
    {/if}
</tr>
{foreach from=$pool.samples item=sample}
<tr{if $sample.disagreed} class='bgpink'{/if}>
    <td>{$sample.id}</td>
    <td>{foreach from=$sample.context item=word name=x}{if $smarty.foreach.x.index == $sample.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>{else}{$word|htmlspecialchars}{/if} {/foreach}
    {if isset($smarty.get.ext)}
        <br/><ul>
        {foreach from=$sample.parses item=parse}
            <li>{strip}
                {$parse.lemma_text}
                {foreach from=$parse.gram_list item=gr}
                    , <span title='{$gr.descr}'>{$gr.inner}</span>
                {/foreach}
            {/strip}</li>
        {/foreach}
        </ul>
    {/if}</td>
    <td>{$sample.answered}/{$pool.num_users}</td>
    {if isset($smarty.get.ext)}
    {foreach from=$sample.instances item=instance}
    <td>{if $instance.answer_num == 99}<b>Other</b>{elseif $instance.answer_num > 0}{$instance.answer_gram}{else}&ndash;{/if}</td>
    {/foreach}
    {/if}
</tr>
{/foreach}
</table>
{/block}
