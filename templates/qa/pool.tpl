{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{if $user_permission_check_morph}
{literal}
<script type="text/javascript">
    function submit(id, answer, $target, manual) {
        $('select').attr('disabled', 'disabled');
        $.get('ajax/annot.php', {'id':id, 'answer':answer, 'moder':1, 'manual':manual}, function(res) {
            var $r = $(res).find('result');
            if ($r.attr('ok') > 0) {
                $target.closest('td').addClass('bggreen').find('select.sel_var [value=\''+answer+'\']').attr("selected", "selected");
                $('select').removeAttr('disabled');
                if ($r.attr('ok') == 2)
                    $('button.finish_mod').removeAttr('disabled');
            } else {
                alert('Save failed');
            }
        });
    }
    function agree_all() {
        if (confirm('Согласиться со всеми однозначными ответами?')) {
            $('table.samples_tbl tr:not(.notagreed)').each(function(i, el) {
                $el = $(el);
                if ($el.find('select.sel_var').val() == 0)
                    submit($el.attr('rel'), $el.attr('rev'), $el.find('a.agree'), 0);
            });
        }
    }
    $(document).ready(function(){
        $('select.sel_var').bind('change', function(event) {
            var $tgt = $(event.target);
            submit($tgt.closest('tr').attr('rel'), $tgt.val(), $tgt, 1);
        });
        $('select.sel_status').bind('change', function(event) {
            var $tgt = $(event.target);
            $tgt.closest('td').removeClass('bggreen');
            $('select').attr('disabled', 'disabled');
            $.get('ajax/annot.php', {'id':$tgt.closest('tr').attr('rel'), 'status': $tgt.val(), 'moder':1, 'manual':1}, function(res) {
                var $r = $(res).find('result');
                if ($r.attr('ok') > 0) {
                    $tgt.closest('td').addClass('bggreen');
                    $('select').removeAttr('disabled');
                } else {
                    alert('Save failed');
                }
            });
        });
        $('a.agree_all').click(function(event) {
            agree_all();
            event.preventDefault();
        });
        $('a.agree').click(function(event) {
            var $tgt = $(event.target);
            var answer = $tgt.closest('tr').attr('rev');
            submit($tgt.closest('tr').attr('rel'), answer, $tgt, 1);
            event.preventDefault();
        });
        $('a.expand').click(function(event) {
            var $btn = $(event.target);
            $.get('ajax/get_context.php', {'tf_id':$(this).attr('rel'), 'dir':$(this).attr('rev')}, function(res) {
                var s = '';
                $(res).find('w').each(function(i, el) {
                    s += ' ' + $(el).text();
                });
                if ($btn.attr('rev') == -1)
                    $btn.closest('span').prepend(s);
                else
                    $btn.closest('span').append(s);
                $btn.hide();
            });
            event.preventDefault();
        });
        $('a.comment').click(function(event) {
            if ($(event.target).closest('td').find('textarea').length == 0) {
                $(event.target).closest('td').append('<div><textarea placeholder="Ваш комментарий"></textarea><br/><button class="send_comment">Отправить комментарий</button></div>').find('button.send_comment').click(function() {
                    $.post('ajax/post_comment.php', {'type': 'morph_annot', 'id': $(event.target).attr('rel'), 'text': $(this).closest('div').find('textarea').val()}, function(res) {
                        var $r = $(res).find('response');
                        if ($r.attr('ok') == 1) {
                            $(event.target).closest('td').find('div').replaceWith('<p>Спасибо, ваш комментарий добавлен!</p>');
                            $(event.target).closest('td').find('p').fadeOut(3000);
                        } else {
                            alert('Comment saving failed');
                        }
                    });
                });
            }
            event.preventDefault();
        });
    });
</script>
{/literal}
{/if}
<p><a href="?type={$pool.status}">&lt;&lt; к списку пулов</a></p>
<h1>Пул &laquo;{$pool.name}&raquo;</h1>
{if $user_permission_check_morph}
{if $pool.status == 2}
Пул не опубликован. <form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn">Опубликовать</button></form>
{elseif $pool.status == 3}
Пул опубликован.
<form action="?act=unpublish&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn">Снять с публикации</button></form>
{elseif $pool.status == 4}
Пул снят с публикации.
<form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn">Опубликовать заново</button></form>
{elseif $pool.status == 5}
Пул модерируется (новые ответы запрещены). <form action="?act=finish_moder&amp;pool_id={$pool.id}" method="post" class="inline"><button{if !$pool.all_moderated} disabled="disabled"{/if} class="btn finish_mod">Закончить модерацию</button></form>
<form action="?act=agree&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn" onclick="return confirm('Вы уверены? Это действие необратимо.')">Согласиться со всеми однозначными</button></form>
{elseif $pool.status == 6}
Модерация пула закончена.
{if $user_permission_merge}<form action="?act=begin_merge&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn btn-primary" onclick="return confirm('Вы уверены? Это действие необратимо.')">Вернуть результаты в корпус</button></form>{/if}
{elseif $pool.status == 7 || $pool.status == 8}
Запущен процесс перемещения результатов в корпус. Обновите страницу через несколько минут.
{elseif $pool.status == 9}
Примеры из пула успешно возвращены в корпус.
{/if}
{if $pool.status == 4 || $pool.status == 6}
<form action="?act=begin_moder&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn" onclick="return confirm('Вы уверены? Это действие необратимо.')">Начать модерацию</button></form>
{/if}
{/if}
{if $pool.status > 2}
<p>{if !isset($smarty.get.ext)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">к расширенному виду</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}">к обычному виду</a>{/if}</p>
<p>
{if $pool.filter != 'focus'}<a class="{if $pool.has_focus}bggreen{else}bgpink{/if}" href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=focus">список для модерации</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if} |
{if $pool.filter != 'disagreed'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=disagreed">несогласованные ответы</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if} |
{if $pool.filter != 'comments'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=comments">примеры с комментариями</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if} |
{if $pool.filter != 'not_ok'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=not_ok">примеры с опечатками и т.п.</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if} |
{if $pool.filter != 'not_moderated'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=not_moderated">непроверенные</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext">показать все</a>{/if}</p>
{if $is_admin}<p><a href="?act=samples&amp;pool_id={$pool.id}&amp;tabs">в виде tab-separated файла</a> (<a href="?act=samples&amp;pool_id={$pool.id}&amp;tabs&amp;mod_ans">с ответами модератора</a>)</p>{/if}
{/if}
<div class="pagination pagination-centered"><ul>
<li {if $pool.pages.active == 0}class="disabled"{/if}><a href="?{$pool.pages.query}&skip={($pool.pages.active - 1) * 15}">&lt;</a></li>
{for $i=0 to $pool.pages.total - 1}
<li {if $i == $pool.pages.active}class="active"{/if}><a href="?{$pool.pages.query}&skip={$i * 15}">{$i+1}</a></li>
{/for}
<li {if $pool.pages.active == $pool.pages.total - 1}class="disabled"{/if}><a href="?{$pool.pages.query}&skip={($pool.pages.active + 1) * 15}">&gt;</a></li>
</ul></div>
<table border="1" cellspacing="0" cellpadding="3" class="small samples_tbl">
<tr>
    <th>id</th>
    <th>&nbsp;</th>
    {if isset($smarty.get.ext)}
        {for $i=1 to $pool.num_users}<th>{$i}</th>{/for}
        {if $user_permission_check_morph && $pool.status == 5}
            <th><a class='agree_all pseudo' href='#'>согласен со всеми однозначными</a></th>
        {elseif $pool.status > 5}
            <th>Модератор<br/>({$pool.moderator_name})</th>
        {/if}
    {else}
        <th>Ответов</th>
    {/if}
</tr>
{foreach from=$pool.samples item=sample}
{if isset($smarty.get.ext)}
    <tr rel='{$sample.id}'{if $sample.disagreed > 0} class='notagreed {if $sample.disagreed == 1}bgpink{else}bgorange{/if}'{else} rev='{$sample.instances[0].answer_num}'{/if}>
{else}
    <tr rel='{$sample.id}'>
{/if}
    <td>{$sample.id}</td>
    <td>
        <a href="{$web_prefix}/books.php?book_id={$sample.book_id}&amp;full#sen{$sample.sentence_id}" target="_blank">контекст</a>
        <span>{if $sample.has_left_context}<a class='expand' href="#" rel='{$sample.has_left_context}' rev='-1'>...</a>{/if}
        {foreach from=$sample.context item=word name=x}{if $smarty.foreach.x.index == $sample.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>{else}{$word|htmlspecialchars}{/if} {/foreach}
        {if $sample.has_right_context}<a class='expand' href="#" rel='{$sample.has_right_context}' rev='1'>...</a>{/if}</span>
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
            <a rel='{$sample.id}' class='hint comment' href='#'>Прокомментировать</a>
        {/if}
    </td>
    {if isset($smarty.get.ext)}
        {foreach from=$sample.instances item=instance}
        <td class="diff_colors_{$instance.user_color}">{if $instance.answer_num == 99}<b>Other</b>{elseif $instance.answer_num > 0}{$instance.answer_gram}{else}&ndash;{/if}</td>
        {/foreach}
        {if $user_permission_check_morph && $pool.status == 5}
            <td>
                {if !$sample.disagreed && !$sample.moder_answer_num}
                <a href='#' class='hint agree'>согласен</a><br/>
                {/if}
                {html_options options=$pool.variants name='sel_var' class='sel_var' selected={$sample.moder_answer_num}}
                <br/>
                <select class='sel_status'>
                    <option value='0' {if $sample.moder_status_num == 0}selected="selected"{/if}>OK</option>
                    <option value='1' {if $sample.moder_status_num == 1}selected="selected"{/if}>Частично правильно</option>
                    <option value='2' {if $sample.moder_status_num == 2}selected="selected"{/if}>Нет правильного разбора</option>
                    <option value='3' {if $sample.moder_status_num == 3}selected="selected"{/if}>Опечатка</option>
                    <option value='4' {if $sample.moder_status_num == 4}selected="selected"{/if}>Неснимаемая омонимия</option>
                </select>
            </td>
        {elseif $pool.status > 5}
            <td>
                {$sample.moder_answer_gram}<br/>
                {if $sample.moder_status_num == 1}
                    <b>Частично правильно</b>
                {elseif $sample.moder_status_num == 2}
                    <b>Нет правильного разбора</b>
                {elseif $sample.moder_status_num == 3}
                    <b>Опечатка</b>
                {elseif $sample.moder_status_num == 4}
                    <b>Неснимаемая омонимия</b>
                {/if}
            </td>
        {/if}
    {else}
        <td>{$sample.answered}/{$pool.num_users}</td>
    {/if}
</tr>
{/foreach}
{if isset($smarty.get.ext) && $user_permission_check_morph && $pool.status == 5}
<tr><th colspan='{$pool.num_users + 3}' align='right'><a class='agree_all pseudo' href='#'>согласен со всеми однозначными</a></th></tr>
{/if}
</table>
<div class="pagination pagination-centered"><ul>
<li {if $pool.pages.active == 0}class="disabled"{/if}><a href="?{$pool.pages.query}&skip={($pool.pages.active - 1) * 15}">&lt;</a></li>
{for $i=0 to $pool.pages.total - 1}
<li {if $i == $pool.pages.active}class="active"{/if}><a href="?{$pool.pages.query}&skip={$i * 15}">{$i+1}</a></li>
{/for}
<li {if $pool.pages.active == $pool.pages.total - 1}class="disabled"{/if}><a href="?{$pool.pages.query}&skip={($pool.pages.active + 1) * 15}">&gt;</a></li>
</ul></div>
{if isset($smarty.get.ext)}
<h2>Легенда</h2>
<table>
{foreach from=$pool.user_colors item=user}
<tr class='diff_colors_{$user[0]}'><td>{$user[1]|default:"Не заполнено"}</td></tr>
{/foreach}
</table>
{/if}
{/block}
