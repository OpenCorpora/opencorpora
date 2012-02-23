{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('.ma_instance button').click(function(event) {
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
        $.get('ajax/clck_log.php', {'id': $btn.closest('div').attr('rel'), 'type': $btn.attr('rev')});
    });
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
    $('button.ma_next_pack').click(function() {
        document.location.reload();
    });
});
</script>
{/literal}
<p>Спасибо, что помогаете нам. Не торопитесь, будьте внимательны. Если вы не уверены, пропускайте пример.</p>
<br/>
{foreach from=$packet.instances item=instance}
<div class='ma_instance' rel='{$instance.id}'>
    {if $instance.has_left_context}<a class='expand' href="#" rel='{$instance.has_left_context}' rev='-1'>...</a>{/if}
    {foreach from=$instance.context item=word name=x}
    {if $smarty.foreach.x.index == $instance.mainword}
    <b class='bggreen'>{$word|htmlspecialchars}</b> 
    {else}
    {$word|htmlspecialchars}
    {/if}
    {/foreach}
    {if $instance.has_right_context}<a class='expand' href="#" rel='{$instance.has_right_context}' rev='1'>...</a>{/if}
    <br/>
    {foreach from=$packet.gram_descr item=var name=x}
    <button rev='{$smarty.foreach.x.index + 1}'>{$var|htmlspecialchars}</button>
    {/foreach}
    <button rev='99' class='other'>Другое</button>
    <button rev='-1' class='reject'>Пропустить</button>
</div>
{/foreach}
<center><button class='ma_next_pack' disabled='disabled'>Хочу ещё примеров!</button></center>
{/block}
