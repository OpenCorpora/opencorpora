{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Найденные примеры для типа {$data.id}</h1>
<p>Пулы этого типа называются <b>{$data.pool_name}</b></p>
<p>Показано не более 200 случайно выбранных примеров из {$data.found_samples}.</p>
{if $is_admin}
<form method="post" action="?act=promote&amp;pool_type={$data.id}"> 
<input type='radio' name='type' value='random' checked='checked'/><input type='text' name='random_n' maxlength='4' class='span1' value='{$default_size}'/> случайных<br/>
<input type='radio' name='type' value='first'/><input type='text' name='first_n' maxlength='4' class='span1' value='{$default_size}'/> первых<br/>
сделать <input type='text' name='pools_num' class='span1' value='{floor($data.found_samples / $default_size)}'/> таких пулов<br/>
<input class='btn btn-primary' type='submit' value='Поехали'/><br/>
</form>
<br/>
{/if}
<table border="1" cellspacing='0' cellpadding='2'>
{foreach from=$data.samples item=c}
<tr><td>{strip}
    {foreach from=$c.context item=word key=tf_id}
        {if $tf_id == $c.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>
        {else}{$word|htmlspecialchars}{/if}
        &nbsp;
    {/foreach}
{/strip}</td></tr>
{/foreach}
</table>
{/block}
