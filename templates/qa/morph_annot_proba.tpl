{* Smarty *}
{extends file='common.tpl'}
{*block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block*}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('.ma_instance button').click(function(event) {
        $('button.ma_show_results').addClass('disabled');
        var $btn = $(event.target);
        $btn.closest('div').find('button').attr('disabled', 'disabled').removeClass('chosen');
        $btn.addClass('chosen');
        
        $btn.closest('div').fadeTo('slow', 0.5).removeClass('ma_not_ready').addClass('ma_ready');
        //perhaps all the instances are clicked
        var flag = 1;
        $('div.ma_instance').each(function(i, el) {
            if (!$(el).hasClass('ma_ready'))
                flag = 0;
        });
        if (flag) $('button.ma_show_results').removeClass('disabled');
        
        $btn.closest('div').find('button').removeAttr('disabled');
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
    $('button.ma_show_results').click(function() {
        if($(this).hasClass('disabled')) {
            $first_notready = $('.ma_not_ready').eq(0);
            $('html').scrollTop($first_notready.offset().top)
        }
        else {
            ShowResults();
        }
    });
    
    function ShowResults()
    {
        var samples_total = $('div.ma_instance').length;
        
        var samples_rejected = 0;
        $('button.reject').each(function(i, el) {
            if ($(el).hasClass('chosen'))
                samples_rejected++;
        });
        
        var samples_agree = 0;
        
        $('div.ma_instance').each(function(i, el) {
            var sample_id = $(el).attr('rel');
            var sample_answer_moderator = $('#h_' + sample_id).val();
            if ($('button#b_' + sample_id + '_' + sample_answer_moderator).hasClass('chosen'))
                samples_agree++;
            //alert("sample_id=" + sample_id + " moderator_answer =" + sample_answer_moderator);
        });
        
        alert('Всего примеров: ' + samples_total + '\nВы пропустили примеров: ' + samples_rejected + '\nОтветов совпало с правильным: = ' + samples_agree);
    }
    
});
</script>
</script>
{/literal}
<br>
<ul class="breadcrumb">
    <li><a href="{$web_prefix}/pool-probator.php?pool_type={$packet.pool_type}">Пробная разметка</a> <span class="divider">/</span></li>
    <li class="active">{$packet.gram_descr|implode:" &mdash; "}</li>
</ul>
<div class="ma_annot_top_block clearfix">
    {if $packet.has_manual}<div class="pull-right">
        <a class="btn btn-primary" href="manual.php?pool_type={$packet.pool_type}" target="_blank"><i class="icon-info-sign icon-white"></i> Инструкция по разметке</a>
    </div>{/if}
    <div class="ma_thanx_block">
        Попробуйте проставить свои ответы в этих примерах, взятых из давно размеченных пулов. Если вы не уверены,  пропускайте пример.
        <br>Эти задания не идут вам в статистику, просто можете проверить свои силы.
    </div>
</div>
{foreach from=$packet.instances item=instance}
<div class='ma_instance ma_not_ready' rel='{$instance.sample_id}' rev='{$instance.sample_id}'>
    <div class="ma_instance_words">
        <input type="hidden" id="h_{$instance.sample_id}" value="{$instance.correct_answer}">
        {if $instance.has_left_context}<a class='expand' href="#" rel='{$instance.has_left_context}' rev='-1'>...</a>{/if}
        {foreach from=$instance.context item=word name=x}
        {if $smarty.foreach.x.index == $instance.mainword}
        <b class='ma_instance_word' title='{$instance.lemmata}'>{$word|htmlspecialchars}</b>
        {else}
        {$word|htmlspecialchars}
        {/if}
        {/foreach}
        {if $instance.has_right_context}<a class='expand' href="#" rel='{$instance.has_right_context}' rev='1'>...</a>{/if}
    </div>
    {foreach from=$packet.gram_descr item=var name=x}
    <button rev='{$smarty.foreach.x.index + 1}' id="b_{$instance.sample_id}_{$smarty.foreach.x.index + 1}" class="btn">{$var|htmlspecialchars}</button>
    {/foreach}
    <button rev='99' id="b_{$instance.sample_id}_99" class='btn other'>Другое</button>
    <button rev='-1' class='btn reject btn-danger'>Пропустить</button>
    <div class='btn disabled debug_info'>Debug. Верный ответ: {$instance.correct_answer}</div>
</div>
{/foreach}
<button class='btn btn-primary btn-large ma_show_results disabled'>Узнать свою статистику!</button>
{/block}
