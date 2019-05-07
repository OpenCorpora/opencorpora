{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{if $user_permission_check_morph}
{literal}
<script type="text/javascript">
    function submit(id, answer, $target, manual) {
        $('select').attr('disabled', 'disabled');
        $.post('ajax/annot.php', {'id':id, 'answer':answer, 'moder':1, 'manual':manual}, function(res) {
            var $r = res.status;
            if ($r > 0) {
                $target.closest('td').addClass('bggreen').find('select.sel_var [value=\''+answer+'\']').attr("selected", "selected");
                $('select').removeAttr('disabled');
                if ($r == 2)
                    $('button.finish_mod').removeAttr('disabled');
            } else {
                alert('Save failed');
            }
        });
    }
    function agree_all(manual) {
        if (confirm('Согласиться со всеми однозначными ответами?')) {
            $('table.samples_tbl tr:not(.notagreed)').each(function(i, el) {
                $el = $(el);
                if ($el.find('select.sel_var').val() == 0)
                    submit($el.data('sampleId'), $el.data('answerNum'), $el.find('a.agree'), manual);
            });
        }
    }
    $(document).ready(function(){
        $('select.sel_var').bind('change', function(event) {
            var $tgt = $(event.target);
            submit($tgt.closest('tr').data('sampleId'), $tgt.val(), $tgt, 1);
        });
        $('select.sel_status').bind('change', function(event) {
            var $tgt = $(event.target);
            $tgt.closest('td').removeClass('bggreen');
            $('select').attr('disabled', 'disabled');
            $.post('ajax/annot.php', {'id':$tgt.closest('tr').data('sampleId'), 'status': $tgt.val(), 'moder':1, 'manual':1}, function(res) {
                if (res.status > 0) {
                    $tgt.closest('td').addClass('bggreen');
                    $('select').removeAttr('disabled');
                } else {
                    alert('Save failed');
                }
            });
        });
        $('a.agree_all_auto').click(function(event) {
            agree_all(0);
            event.preventDefault();
        });
        $('a.agree_all_manual').click(function(event) {
            agree_all(1);
            event.preventDefault();
        });
        $('a.agree').click(function(event) {
            var $tgt = $(event.target);
            var answer = $tgt.closest('tr').data('answerNum');
            submit($tgt.closest('tr').data('sampleId'), answer, $tgt, 1);
            event.preventDefault();
        });
        $('a.expand').click(function(event) {
            var $btn = $(event.target);
            $.post('ajax/get_context.php', {'tf_id':$(this).data('context'), 'dir':$(this).data('dir')}, function(res) {
                var s = '';
                for (var i = 0; i < res.context.length; ++i) {
                    s += ' ' + res.context[i];
                }
                if ($btn.data('dir') == -1)
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
                    $.post('ajax/post_comment.php', {'type': 'morph_annot', 'id': $(event.target).data('sampleId'), 'text': $(this).closest('div').find('textarea').val()}, function(res) {
                        if (!res.error) {
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
{if $pool.status == $smarty.const.MA_POOLS_STATUS_NOT_STARTED}
Пул не опубликован. <form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn">Опубликовать</button></form>
{elseif $pool.status == $smarty.const.MA_POOLS_STATUS_IN_PROGRESS}
Пул опубликован.
<form action="?act=unpublish&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn">Снять с публикации</button></form>
{elseif $pool.status == $smarty.const.MA_POOLS_STATUS_ANSWERED}
Пул снят с публикации.
<form action="?act=publish&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn">Опубликовать заново</button></form>
{elseif $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATION}
    {if $pool.has_manual}<div>
        <a class="btn btn-primary" href="manual.php?pool_type={$pool.type}" target="_blank"><i class="icon-info-sign icon-white"></i> Инструкция</a>
    </div>{/if}
Пул модерируется (новые ответы запрещены). <form action="?act=finish_moder&amp;pool_id={$pool.id}" method="post" class="inline"><button{if !$pool.all_moderated} disabled="disabled"{/if} class="btn finish_mod">Закончить модерацию</button></form>
{if $user_permission_merge}<a href="?act=finish_and_merge&amp;pool_id={$pool.id}" class="btn btn-primary" onclick="return confirm('Вы уверены? Это действие необратимо.')">Закончить и вернуть результаты</a>{/if}
<form action="?act=agree&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn" onclick="return confirm('Вы уверены?')">Согласиться со всеми однозначными</button></form>
{elseif $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATED}
Модерация пула закончена.
{if $user_permission_merge}<form action="?act=begin_merge&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn btn-primary" onclick="return confirm('Вы уверены? Это действие необратимо.')">Вернуть результаты в корпус</button></form>{/if}
{elseif $pool.status == $smarty.const.MA_POOLS_STATUS_TO_MERGE || $pool.status == $smarty.const.MA_POOLS_STATUS_MERGING}
Запущен процесс перемещения результатов в корпус. Обновите страницу через несколько минут.
{elseif $pool.status == $smarty.const.MA_POOLS_STATUS_ARCHIVED}
Примеры из пула успешно возвращены в корпус.
{/if}
{if $pool.status == $smarty.const.MA_POOLS_STATUS_ANSWERED || $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATED}
<form action="?act=begin_moder&amp;pool_id={$pool.id}" method="post" class="inline"><button class="btn" onclick="return confirm('Вы уверены? Это действие необратимо.')">Начать модерацию</button></form>
{/if}
{/if}
{if $pool.status > $smarty.const.MA_POOLS_STATUS_NOT_STARTED}

<p>{if !isset($smarty.get.ext)}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1">к расширенному виду</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}">к обычному виду</a>{/if} |
{if !$sortby || $sortby == 'answer'}
    <a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter={$pool.filter}&amp;sortby=text">отсортировать по ключевому слову</a>
{else}
    <a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter={$pool.filter}&amp;sortby=answer">отсортировать по ответу</a>
{/if}
</p>

<p>
{if $pool.filter != 'focus'}<a class="{if $pool.has_focus}bggreen{else}bgpink{/if}" href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter=focus&amp;sortby={$sortby}">список для модерации</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;sortby={$sortby}">показать все</a>{/if} |
{if $pool.filter != 'disagreed'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter=disagreed&amp;sortby={$sortby}">несогласованные ответы</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;sortby={$sortby}">показать все</a>{/if} |
{if $pool.filter != 'comments'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter=comments&amp;sortby={$sortby}">примеры с комментариями</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;sortby={$sortby}">показать все</a>{/if} |
{if $pool.filter != 'not_ok'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter=not_ok&amp;sortby={$sortby}">примеры с опечатками и т.п.</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;sortby={$sortby}">показать все</a>{/if} |
{if $pool.filter != 'not_moderated'}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;filter=not_moderated&amp;sortby={$sortby}">непроверенные</a>{else}<a href="?act=samples&amp;pool_id={$pool.id}&amp;ext=1&amp;sortby={$sortby}">показать все</a>{/if}</p>
{if $is_admin}<p><a href="?act=samples&amp;pool_id={$pool.id}&amp;tabs=1">в виде tab-separated файла</a> (<a href="?act=samples&amp;pool_id={$pool.id}&amp;tabs=1&amp;mod_ans">с ответами модератора</a>)</p>{/if}
{/if}
{capture name="pagination"}
<div class="pagination pagination-centered"><ul>
<li {if $pool.pages.active == 0}class="disabled"{/if}><a href="?{$pool.pages.query}&skip={($pool.pages.active - 1) * $smarty.const.MA_PAGE_SIZE_FOR_MODERATORS}">&lt;</a></li>
{for $i=0 to $pool.pages.total - 1}
<li {if $i == $pool.pages.active}class="active"{/if}><a href="?{$pool.pages.query}&skip={$i * $smarty.const.MA_PAGE_SIZE_FOR_MODERATORS}">{$i+1}</a></li>
{/for}
<li {if $pool.pages.active == $pool.pages.total - 1}class="disabled"{/if}><a href="?{$pool.pages.query}&skip={($pool.pages.active + 1) * $smarty.const.MA_PAGE_SIZE_FOR_MODERATORS}">&gt;</a></li>
</ul></div>
{/capture}
{$smarty.capture.pagination}
<table border="1" cellspacing="0" cellpadding="3" class="small samples_tbl">
<tr>
    <th>id</th>
    <th>&nbsp;</th>
    {if isset($smarty.get.ext)}
        {for $i=1 to $pool.num_users}<th>{$i}</th>{/for}
        {if $user_permission_check_morph && $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATION}
            <th><a class='agree_all_auto pseudo' href='#'>согласен со всеми однозначными (не читал)</a><br/>
            <i class="icon-eye-open"></i> <a class='agree_all_manual pseudo' href='#'>согласен со всеми однозначными (читал)</a></th></tr>
        {elseif $pool.status > $smarty.const.MA_POOLS_STATUS_MODERATION}
            <th>Модератор<br/>({$pool.moderator_name})</th>
        {/if}
    {else}
        <th>Ответов</th>
    {/if}
</tr>
{foreach from=$pool.samples item=sample}
{if isset($smarty.get.ext)}
    <tr data-sample-id='{$sample.id}'{if $sample.disagreed > 0} class='notagreed {if $sample.disagreed == 1}bgpink{else}bgorange{/if}'{else} data-answer-num='{$sample.instances[0].answer_num}'{/if}>
{else}
    <tr data-sample-id='{$sample.id}'>
{/if}
    <td>{$sample.id}</td>
    <td>
        <a href="/books.php?book_id={$sample.book_id}&amp;full=1#sen{$sample.sentence_id}" target="_blank">контекст</a>
        <span>{if $sample.has_left_context}<a class='expand' href="#" data-context='{$sample.has_left_context}' data-dir='-1'>...</a>{/if}
        {foreach from=$sample.context item=word key=tf_id}{if $tf_id == $sample.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>{else}{$word|htmlspecialchars}{/if} {/foreach}
        {if $sample.has_right_context}<a class='expand' href="#" data-context='{$sample.has_right_context}' data-dir='1'>...</a>{/if}</span>
        {if isset($smarty.get.ext)}
            <br/><ul>
            {foreach from=$sample.parses item=parse}
                <li>{strip}
                    {$parse->lemma_text}
                    {foreach from=$parse->gramlist item=gr}
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
            <a data-sample-id='{$sample.id}' class='hint comment' href='#'>Прокомментировать</a>
        {/if}
    </td>
    {if isset($smarty.get.ext)}
        {foreach from=$sample.instances item=instance}
        <td class="diff_colors_{$instance.user_color}">{if $instance.answer_num == $smarty.const.MA_ANSWER_OTHER}<b>Other</b>{elseif $instance.answer_num > 0}{$instance.answer_gram}{else}&ndash;{/if}</td>
        {/foreach}
        {if $user_permission_check_morph && $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATION}
            <td>
                {if !$sample.disagreed && !$sample.moder_answer_num}
                <a href='#' class='hint agree'>согласен</a><br/>
                {/if}
                {html_options options=$pool.variants name='sel_var' class='sel_var' selected={$sample.moder_answer_num}}
                <br/>
                <select class='sel_status'>
                    <option value='{$smarty.const.MA_SAMPLES_STATUS_OK}' {if $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_OK}selected="selected"{/if}>OK</option>
                    <option value='{$smarty.const.MA_SAMPLES_STATUS_ALMOST_OK}' {if $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_ALMOST_OK}selected="selected"{/if}>Частично правильно</option>
                    <option value='{$smarty.const.MA_SAMPLES_STATUS_NO_CORRECT_PARSE}' {if $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_NO_CORRECT_PARSE}selected="selected"{/if}>Нет правильного разбора</option>
                    <option value='{$smarty.const.MA_SAMPLES_STATUS_MISPRINT}' {if $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_MISPRINT}selected="selected"{/if}>Опечатка</option>
                    <option value='{$smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}' {if $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}selected="selected"{/if}>Неснимаемая омонимия</option>
                </select>
            </td>
        {elseif $pool.status > $smarty.const.MA_POOLS_STATUS_MODERATION}
            <td>
                {$sample.moder_answer_gram}<br/>
                {if $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_ALMOST_OK}
                    <b>Частично правильно</b>
                {elseif $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_NO_CORRECT_PARSE}
                    <b>Нет правильного разбора</b>
                {elseif $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_MISPRINT}
                    <b>Опечатка</b>
                {elseif $sample.moder_status_num == $smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}
                    <b>Неснимаемая омонимия</b>
                {/if}
            </td>
        {/if}
    {else}
        <td>{$sample.answered}/{$pool.num_users}</td>
    {/if}
</tr>
{/foreach}
{if isset($smarty.get.ext) && $user_permission_check_morph && $pool.status == $smarty.const.MA_POOLS_STATUS_MODERATION}
<tr><th colspan='{$pool.num_users + 3}' align='right'><a class='agree_all_auto pseudo' href='#'>согласен со всеми однозначными (не читал)</a><br/>
<i class="icon-eye-open"></i> <a class='agree_all_manual pseudo' href='#'>согласен со всеми однозначными (читал)</a></th></tr>
{/if}
</table>
{$smarty.capture.pagination}
{if isset($smarty.get.ext)}
<h2>Легенда</h2>
<table>
{foreach from=$pool.user_colors item=user}
<tr class='diff_colors_{$user[0]}'><td>{$user[1]|default:"Не заполнено"}</td></tr>
{/foreach}
</table>
{/if}
{/block}
