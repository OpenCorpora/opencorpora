{* Smarty *}
{extends file='common.tpl'}
{*block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block*}
{block name=content}
{literal}
<style type="text/css">
    .correct {background-color:green;}
    .incorrect {background-color:red;}
</style>
<script type="text/javascript">
$(document).ready(function() {
    $('div.alert').hide();
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

    function ShowResults(){
        var answers = new Array();
        $('div.ma_instance').each(function(i, el) {
            var sample_id = Number($(el).attr('rel'));
            var answer_id = GetMyAnswer(sample_id);
            answers.push(new Array(sample_id, answer_id));
        });

        $.post('ajax/annot_proba.php', {answers: JSON.stringify(answers)}, function(res){
            if (res.status == 1) {
                CalculateResults(res.answers);
            } else
                alert('Что-то пошло не так. Попробуйте перезагрузить страницу.')
        });

    }
    
    function CalculateResults(answers)
    {
        var samples_total = answers.length;
        var samples_rejected = 0;
        var samples_agree = 0;
        for(var k=0; k<samples_total; k++) {
            var sample_id = answers[k][0];
            var answer_id = answers[k][1];
            var moderator_answer_id = answers[k][2];
            if ( answer_id == moderator_answer_id)
                samples_agree++;	
            else
                samples_rejected++;
            if ( answer_id == moderator_answer_id)
                $('#a_' + sample_id).addClass('correct');	
            else
                $('#a_' + sample_id).addClass('incorrect');	
        }
        var text = 'Всего примеров: ' + samples_total + ' Вы пропустили примеров: ' + samples_rejected + ' Ответов совпало с правильным: = ' + samples_agree;
        $('div.alert').show();
        $("div.alert").text(text);
    }
	
    function GetMyAnswer(sample_id)
    {
        if ($('button#b_' + sample_id + '_99').hasClass('chosen'))
            return 99;
        var result = -1;
        $("button[rel='" + sample_id+"']").each(function(i, el) {
            var button_id = $(el).attr('rev');
            if ($('button#b_' + sample_id + '_' + button_id).hasClass('chosen')){
                result = button_id;
            }
        });
        return Number(result);
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
    <button rev='{$smarty.foreach.x.index + 1}'  rel="{$instance.sample_id}" id="b_{$instance.sample_id}_{$smarty.foreach.x.index + 1}" class="btn">{$var|htmlspecialchars}</button>
    {/foreach}
    <button rev='99' id="b_{$instance.sample_id}_99" class='btn other'>Другое</button>
    <button rev='-1' class='btn reject btn-danger'>Пропустить</button>
    <div class="btn answer_helper" id="a_{$instance.sample_id}">ответ</div>
</div>
{/foreach}
<button class='btn btn-primary btn-large ma_show_results disabled'>Узнать свою статистику!</button>
<div class="alert" ></div>
{/block}
