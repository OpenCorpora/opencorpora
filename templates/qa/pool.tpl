{* Smarty *}
{extends file='common.no-right.tpl'}
{block name=content}
{if $user_permission_check_morph}
{literal}
<script type="text/javascript">
    function submit(id, answer, $target) {
        $('select').attr('disabled', 'disabled');
        $.get('ajax/annot.php', {'id':id, 'answer':answer, 'moder':1}, function(res) {
            var $r = $(res).find('result');
            if ($r.attr('ok') == 1) {
                $target.closest('td').addClass('bggreen').find('select [value=\''+answer+'\']').attr("selected", "selected");
                $('select').removeAttr('disabled');
            } else {
                alert('Save failed');
            }
        });
    }
    $(document).ready(function(){
        $('select').bind('change', function(event) {
            var $tgt = $(event.target);
            submit($tgt.closest('tr').attr('rel'), $tgt.val(), $tgt);
        });
        $('a.agree').click(function(event) {
            var $tgt = $(event.target);
            var answer = $tgt.closest('tr').attr('rev');
            submit($tgt.closest('tr').attr('rel'), answer, $tgt);
            event.preventDefault();
        });
    });
</script>
{/literal}
{/if}
<p><a href="?">&lt;&lt; к списку пулов</a></p>
<h1>Пул &laquo;{$pool.name}&raquo;</h1>
{if $user_permission_check_morph}
{if $pool.status == 2}
Пул не опубликован. <form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button>Опубликовать</button></form>
{elseif $pool.status == 3}
Пул опубликован.
<form action="?act=unpublish&amp;pool_id={$pool.id}" method="post" class="inline"><button>Снять с публикации</button></form>
{elseif $pool.status == 4}
Пул снят с публикации.
<form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button>Опубликовать заново</button></form>
{elseif $pool.status == 5}
Пул модерируется (новые ответы запрещены).
{/if}
{if $pool.status != 5}
<form action="?act=begin_moder&amp;pool_id={$pool.id}" method="post" class="inline"><button onclick="return confirm('Вы уверены? Это действие необратимо.')">Начать модерацию</button></form>
{/if}
{/if}
{if $pool.status > 2}
<p>{if !isset($smarty.get.ext)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">к расширенному виду</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}">к обычному виду</a>{/if}</p>
<p>{if !isset($smarty.get.disagreed)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;disagreed">показать только несогласованные ответы</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if}</p>
{/if}
<br/>
<table border="1" cellspacing="0" cellpadding="3" class="small">
<tr>
    <th>id</th>
    <th>&nbsp;</th>
    {if isset($smarty.get.ext)}
    {for $i=1 to $pool.num_users}<th>{$i}</th>{/for}
    {if $user_permission_check_morph && $pool.status == 5}<th>&nbsp;</th>{/if}
    {else}
    <th>Ответов</th>
    {/if}
</tr>
{foreach from=$pool.samples item=sample}
<tr rel='{$sample.id}'{if $sample.disagreed} class='bgpink'{else} rev='{$sample.instances[0].answer_num}'{/if}>
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
        <ol>
        {foreach from=$sample.comments item=comment}
            <li>{$comment.text|htmlspecialchars} ({$comment.author}, {$comment.timestamp|date_format:"%d.%m.%Y, %H:%M"})</li>
        {/foreach}
        </ol>
    {/if}</td>
    {if isset($smarty.get.ext)}
    {foreach from=$sample.instances item=instance}
    <td class="diff_colors_{$instance.user_color}">{if $instance.answer_num == 99}<b>Other</b>{elseif $instance.answer_num > 0}{$instance.answer_gram}{else}&ndash;{/if}</td>
    {/foreach}
    {if $user_permission_check_morph && $pool.status == 5}
        <td>
            {if !$sample.disagreed && !$sample.moder_answer_num}
            <a href='#' class='hint agree'>согласен</a>
            {/if}
            {html_options options=$pool.variants name='sel_var' selected={$sample.moder_answer_num}}
        </td>
    {/if}
    {else}
    <td>{$sample.answered}/{$pool.num_users}</td>
    {/if}
</tr>
{/foreach}
</table>
{if isset($smarty.get.ext)}
<h2>Легенда</h2>
<table>
{foreach from=$pool.user_colors item=user}
<tr class='diff_colors_{$user[0]}'><td>{$user[1]|default:"Не заполнено"}</td></tr>
{/foreach}
</table>
{/if}
{/block}
