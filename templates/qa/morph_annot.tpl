{* Smarty *}
{extends file='common.tpl'}
{block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    if ({/literal}{$packet.editable}{literal}) {
        $('.ma_instance').each(function(i, el){
            $.get('ajax/clck_log.php', {'id':$(el).attr('rev'), 'type':(20 + i)});
        });
        $('.ma_instance button').click(function(event) {
            $('button.ma_next_pack').addClass('disabled');
            var $btn = $(event.target);
            $btn.closest('div').find('button').attr('disabled', 'disabled').removeClass('chosen');
            $btn.addClass('chosen');
            $.get('ajax/annot.php', {'id':$(this).closest('div').attr('rel'), 'answer':$(this).attr('rev')}, function(res){
                var $r = $(res).find('result');
                if ($r.attr('ok') == 1) {
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
            $.get('ajax/clck_log.php', {'id': $btn.closest('div').attr('rev'), 'type': $btn.attr('rev')});
        });
    } else {
        $('.ma_instance button').attr('disabled', 'disabled');
    }
    $('a.expand').click(function(event) {
        var $btn = $(event.target);
        $.get('ajax/get_context.php', {'tf_id':$(this).attr('rel'), 'dir':$(this).attr('rev')}, function(res) {
            var s = '';
            $(res).find('w').each(function(i, el) {
                s += ' ' + $(el).text();
            });
            if ($btn.attr('rev') == -1) {
                $btn.closest('div').prepend(s);
                $btn.remove();
            }
            else {
                $btn.closest('div').append(s);
                $btn.remove();
            }

        });
        $.get('ajax/clck_log.php', {
            'id': $btn.closest('div').attr('rev'),
            'type': ($btn.attr('rev') == -1 ? 11 : 12)
        }, function(res){if ($(res).find('result').attr('ok') == 1) $btn.hide()});
        event.preventDefault();
    });
    $('a.comment').click(function(event) {
        if ($(event.target).closest('div').find('textarea').length == 0) {
            $(event.target).closest('div').append('<div class="controls"><textarea placeholder="Ваш комментарий" class="span4"></textarea><button class="btn send_comment">Отправить комментарий</button></div>').find('button.send_comment').click(function(comment_event) {
                $.post('ajax/post_comment.php', {'type': 'morph_annot', 'id': $(event.target).attr('rel'), 'text': $(this).closest('div').find('textarea').val()}, function(res) {
                    var $r = $(res).find('response');
                    if ($r.attr('ok') == 1) {
                        $(comment_event.target).closest('.controls').hide();
                        show_bootalert('success','Спасибо, ваш комментарий добавлен!');
                    } else {
                        alert('Comment saving failed');
                    }
                });
            });
        }
        else {
            $(event.target).closest('div').find('.controls').remove();
        }
        event.preventDefault();
    });
    $('button.ma_next_pack').click(function() {
        if($(this).hasClass('disabled')) {
            $first_notready = $('.ma_not_ready').eq(0);
            $('html').scrollTop($first_notready.offset().top)
        }
        else {
            document.location.reload();
        }
    });
    // class for progress-bar
    function Progress(val) {
        // init members
        this.percent = 0;
        this.splashStep = 10;
        this.splashTimeout = 2000;
        this.$bar = $('#progress-bar');
        this.$splash = $('#progress-splash');
        // set current value
        this.set(val);
    }
    // updates current percent & shows
    Progress.prototype.set = function(val) {
        val = parseInt(val);
        if(isNaN(val)) {
            val = 0;
        }
        if(val != this.percent) {
            this.percent = val;
            this.updateBar();
            if(this.percent%this.splashStep == 0) {
                this.showSplash();
            }
        }
    }
    // shows percent in bar
    Progress.prototype.updateBar = function() {
        this.$bar.find("div").css({width:this.percent+'%'});
        this.$bar.attr('title','Текущий процент выполнения: ' + this.percent + '%');
    }
    // shows percent in splash block
    Progress.prototype.showSplash = function(){
        this.$splash.find('div').html('<strong>Поздравляем!</strong> Вы разметили уже ' + this.percent + '% пула!');
        this.$splash.show();
        setTimeout('$("#' + this.$splash.attr('id') + '").fadeOut("slow")',this.splashTimeout);
    }
    // create instance on a bar with actual value
    var progress = new Progress(55);

    // test progress update
    $("#test-progress").blur(function(){
        progress.set($('#test-progress').val());
    })
});
</script>
{/literal}
<br>
<ul class="breadcrumb">
    <li><a href="{$web_prefix}/tasks.php">Разметка</a> <span class="divider">/</span></li>
    <li class="active">{$packet.gram_descr|implode:" &mdash; "}</li>
</ul>
<div class="ma_annot_top_block clearfix">
    {if $packet.has_manual}<div class="pull-right">
        <a class="btn btn-primary" href="manual.php?pool_type={$packet.pool_type}" target="_blank"><i class="icon-info-sign icon-white"></i> Инструкция по разметке</a>{/if}
    </div>
    <div class="ma_thanx_block">
        Спасибо, что помогаете нам. Не торопитесь, будьте внимательны. Если вы не уверены,  пропускайте пример.
    </div>
</div>
<!--<p><input type="text" id="test-progress"></p>
<div id="progress-bar" class="progress-bar"><div></div></div>
<div id="progress-splash" class="splash-block success" style="display:none;">
    <div><strong>Поздравляем!</strong> Вы разметили 50% пула.</div>
</div>-->
{foreach from=$packet.instances item=instance}
<div class='ma_instance ma_not_ready' rel='{$instance.id}' rev='{$instance.sample_id}'>
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
    <button rev='{$smarty.foreach.x.index + 1}' class="btn {if $instance.answer == $smarty.foreach.x.index + 1} chosen{/if}">{$var|htmlspecialchars}</button>
    {/foreach}
    <button rev='99' class='btn other{if $instance.answer == 99} chosen{/if}'>Другое</button>
    <button rev='-1' class='btn reject btn-danger'>Пропустить</button>
    <a rel='{$instance.sample_id}' class='pseudo comment' href='#'>Прокомментировать</a>
</div>
{/foreach}
{if !$packet.my}<button class='btn btn-primary btn-large ma_next_pack disabled'>Хочу ещё примеров!</button> <button onclick='location.href="{$web_prefix}/?page=stats#user{$smarty.session.user_id}"' class="btn btn-large">Спасибо, достаточно</button>{/if}
{/block}
