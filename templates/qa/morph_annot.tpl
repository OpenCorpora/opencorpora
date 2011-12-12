{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('.ma_instance button').click(function(event) {
        $(this).closest('div').find('button').attr('disabled', 'disabled').removeClass('chosen');
        $(event.target).addClass('chosen');
        $.get('ajax/annot.php', {'id':$(this).attr('rel'), 'answer':$(this).attr('rev')}, function(res){
            var $r = $(res).find('result');
            if ($r.attr('ok') == 1) {
                $(event.target).closest('div').fadeTo('slow', 0.5);
            }
            $(event.target).closest('div').find('button').removeAttr('disabled');
        });
    });
});
</script>
{/literal}
<p>Спасибо, что помогаете нам. Не торопитесь, будьте внимательны. Если вы не уверены, пропускайте пример.</p>
<br/>
{strip}{foreach from=$packet.instances item=instance}
<div class='ma_instance'>
    {foreach from=$instance.context item=word name=x}
    {if $smarty.foreach.x.index == $instance.mainword}
    <b class='bggreen'>{$word|htmlspecialchars}</b>&nbsp;
    {else}
    {$word|htmlspecialchars}&nbsp;
    {/if}
    {/foreach}
    <br/>
    {foreach from=$packet.gram_descr item=var name=x}
    <button rel='{$instance.id}' rev='{$smarty.foreach.x.index + 1}'>{$var|htmlspecialchars}</button>
    {/foreach}
    <button rel='{$instance.id}' rev='-1' class='reject'>Пропустить</button>
</div>
{/foreach}{/strip}
{/block}
