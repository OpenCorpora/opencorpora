{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('.ma_instance button').click(function(event) {
        $('button.ma_next_pack').addClass('disabled');
        var $btn = $(event.target);
        $btn.closest('div').find('button').attr('disabled', 'disabled').removeClass('chosen');
        $btn.addClass('chosen');
        $.post('ajax/annot.php', {'mw': 1, 'id':$(this).closest('div').attr('rel'), 'answer':$(this).attr('rev')}, function(res){
            if (res.error != 1) {
                $btn.closest('div').fadeTo('slow', 0.5).removeClass('ma_not_ready').addClass('ma_ready');
                //perhaps all the instances are clicked
                var flag = 1;
                $('div.ma_instance').each(function(i, el) {
                    if (!$(el).hasClass('ma_ready'))
                        flag = 0;
                });
                if (flag) $('button.ma_next_pack').removeClass('disabled');
            } else
                alert('Что-то пошло не так. Попробуйте перезагрузить страницу.')
            $btn.closest('div').find('button').removeAttr('disabled');
        });
    });
    $('a.expand').click(function(event) {
        var $btn = $(event.target);
        $.post('ajax/get_context.php', {'tf_id':$(this).attr('rel'), 'dir':$(this).attr('rev')}, function(res) {
            var s = '';
            for (var i = 0; i < res.context.length; ++i) {
                s += ' ' + res.context[i];
            };
            if ($btn.attr('rev') == -1) {
                $btn.closest('div').prepend(s);
                $btn.remove();
            }
            else {
                $btn.closest('div').append(s);
                $btn.remove();
            }

        });
        event.preventDefault();
    });
    $('button.ma_next_pack').click(function(e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) {
            $first_notready = $('.ma_not_ready').eq(0);
            $('html').scrollTop($first_notready.offset().top)
        }
        else {
            location.reload();
        }
    });
});
</script>
{/literal}
<br>
<ul class="breadcrumb">
    <li><a href="/tasks.php">Разметка</a> <span class="divider">/</span></li>
    <li class="active">Мультитокены</li>
</ul>
<div class="ma_annot_top_block clearfix">
    <div class="ma_thanx_block">
        {if $mwords}Спасибо, что помогаете нам. Не торопитесь, будьте внимательны. Если вы не уверены,  пропускайте пример.{else}В данный момент доступных заданий нет! Загляните к нам позже.{/if}
    </div>
</div>
{foreach from=$mwords item=instance}
<div class='ma_instance ma_not_ready' rel='{$instance.id}'>
    <div class="ma_instance_words">
        {if $instance.context.has_left_context}<a class='expand' href="#" rel='{$instance.context.has_left_context}' rev='-1'>...</a>{/if}
        {foreach from=$instance.context.context item=word key=tf_id}
        {if in_array($tf_id, $instance.token_ids)}
        <b class='ma_instance_word'>{$word|htmlspecialchars}</b>
        {else}
        {$word|htmlspecialchars}
        {/if}
        {/foreach}
        {if $instance.context.has_right_context}<a class='expand' href="#" rel='{$instance.context.has_right_context}' rev='1'>...</a>{/if}
    </div>
    <button rev='{$answers.ANSWER_YES}' class='btn'>Мультитокен</button>
    <button rev='{$answers.ANSWER_NO}' class='btn'>НЕ мультитокен</button>
    <button rev='{$answers.ANSWER_SKIP}' class='btn reject btn-danger'>Пропустить</button>
</div>
{/foreach}
{if $mwords}<button class='btn btn-primary btn-large ma_next_pack disabled'>Хочу ещё примеров!</button> <button onclick='location.href="/"' class="btn btn-large">Спасибо, достаточно</button>{/if}
{/block}
