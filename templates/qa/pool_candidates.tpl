{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Найденные примеры для пула &laquo;{$pool.name|htmlspecialchars}&raquo;</h1>
<p>Показано не более 200 случайно выбранных примеров.</p>
{if $is_admin}
<form method="post" action="?act=promote&amp;pool_id={$pool.id}"> 
<input type='radio' name='type' value='random' checked='checked'/><input type='text' name='random_n' maxlength='4' size='4' value='100'/> случайных<br/>
<input type='radio' name='type' value='first'/><input type='text' name='first_n' maxlength='4' size='4' value='100'/> первых<br/>
<input class='btn btn-primary' type='submit' value='Добавить их в пул'/><br/>
<label><input type='checkbox' name='keep' checked='checked'/> и сохранить найденные примеры для следующего пула</label> по имени <input type='text' name='next_pool_name' value='{$pool.next_name}' class='span6'/>
</form>
<br/>
{/if}
<table border="1" cellspacing='0' cellpadding='2'>
{foreach from=$pool.samples item=c}
<tr><td>{strip}
    {foreach from=$c.context item=word name=x}
        {if $smarty.foreach.x.index == $c.mainword}<b class='bggreen'>{$word|htmlspecialchars}</b>
        {else}{$word|htmlspecialchars}{/if}
        &nbsp;
    {/foreach}
{/strip}</td></tr>
{/foreach}
</table>
{/block}
