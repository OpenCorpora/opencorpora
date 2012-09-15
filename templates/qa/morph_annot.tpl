{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    if ({/literal}{$packet.editable}{literal}) {
        $('.ma_instance').each(function(i, el){
            $.get('ajax/clck_log.php', {'id':$(el).attr('rel'), 'type':(20 + i)});
        });
        $('.ma_instance button').click(function(event) {
            $('button.ma_next_pack').attr('disabled', 'disabled');
            var $btn = $(event.target);
            $btn.closest('div').find('button').attr('disabled', 'disabled').removeClass('chosen');
            $btn.addClass('chosen');
            $.get('ajax/annot.php', {'id':$(this).closest('div').attr('rel'), 'answer':$(this).attr('rev')}, function(res){
                var $r = $(res).find('result');
                if ($r.attr('ok') == 1) {
                    $btn.closest('div').fadeTo('slow', 0.5).addClass('ma_ready');
                    //perhaps all the instances are clicked
                    var flag = 1;
                    $('div.ma_instance').each(function(i, el) {
                        if (!$(el).hasClass('ma_ready'))
                            flag = 0;
                    });
                    if (flag) $('button.ma_next_pack').removeAttr('disabled');
                } else
                    $btn.closest('div').hide();
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
            if ($btn.attr('rev') == -1)
                $btn.closest('div').prepend(s);
            else
                $btn.closest('div').find('br').before(s);
        });
        $.get('ajax/clck_log.php', {
            'id': $btn.closest('div').attr('rel'),
            'type': ($btn.attr('rev') == -1 ? 11 : 12)
        }, function(res){if ($(res).find('result').attr('ok') == 1) $btn.hide()});
        event.preventDefault();
    });
    $('a.comment').click(function(event) {
        if ($(event.target).closest('div').find('textarea').length == 0) {
            $(event.target).closest('div').append('<div><textarea placeholder="Ваш комментарий"></textarea><br/><button class="send_comment">Отправить комментарий</button></div>').find('button.send_comment').click(function() {
                $.post('ajax/post_comment.php', {'type': 'morph_annot', 'id': $(event.target).attr('rel'), 'text': $(this).closest('div').find('textarea').val()}, function(res) {
                    var $r = $(res).find('response');
                    if ($r.attr('ok') == 1) {
                        $(event.target).closest('div').find('div').replaceWith('<p>Спасибо, ваш комментарий добавлен!</p>');
                        $(event.target).closest('div').find('p').fadeOut(3000);
                    } else {
                        alert('Comment saving failed');
                    }
                });
            });
        }
        event.preventDefault();
    });
    $('button.ma_next_pack').click(function() {
        document.location.reload();
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
<p>Спасибо, что помогаете нам. Не торопитесь, будьте внимательны. Если вы не уверены, пропускайте пример.</p>
<!--<p><input type="text" id="test-progress"></p>
<div id="progress-bar" class="progress-bar"><div></div></div>
<div id="progress-splash" class="splash-block success" style="display:none;">
    <div><strong>Поздравляем!</strong> Вы разметили 50% пула.</div>
</div>
<br/>-->
{foreach from=$packet.instances item=instance}
<div class='ma_instance' rel='{$instance.id}' rev='{$instance.sample_id}'>
    {if $instance.has_left_context}<a class='expand' href="#" rel='{$instance.has_left_context}' rev='-1'>...</a>{/if}
    {foreach from=$instance.context item=word name=x}
    {if $smarty.foreach.x.index == $instance.mainword}
    <b class='bggreen' title='{$instance.lemmata}'>{$word|htmlspecialchars}</b> 
    {else}
    {$word|htmlspecialchars}
    {/if}
    {/foreach}
    {if $instance.has_right_context}<a class='expand' href="#" rel='{$instance.has_right_context}' rev='1'>...</a>{/if}
    <br/>
    {foreach from=$packet.gram_descr item=var name=x}
    <button rev='{$smarty.foreach.x.index + 1}' {if $instance.answer == $smarty.foreach.x.index + 1}class='chosen'{/if}>{$var|htmlspecialchars}</button>
    {/foreach}
    <button rev='99' class='other{if $instance.answer == 99} chosen{/if}'>Другое</button>
    <button rev='-1' class='reject'>Пропустить</button>
    <a rel='{$instance.sample_id}' class='hint comment' href='#'>Прокомментировать</a>
</div>
{/foreach}
{if !$packet.my}<center><button class='ma_next_pack' disabled='disabled'>Хочу ещё примеров!</button></center>{/if}
{/block}
