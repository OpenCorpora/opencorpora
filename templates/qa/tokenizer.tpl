{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Странно токенизированные места</h1>
<table border='1' cellspacing='0' cellpadding='3'>
{foreach item=i from=$items}
<tr>
    <td>Предложение <a href='{$web_prefix}/sentence.php?id={$i.sent_id}'>{$i.sent_id}</a></td>
    <td>{$i.coeff}</td>
    <td>{strip}
    {$i.lcontext|htmlspecialchars}
    <span class='doubt_border'>{$i.focus|htmlspecialchars}</span>
    {if $i.border}<span class='doubt_border'>&nbsp;&nbsp;</span>{/if}
    {$i.rcontext|htmlspecialchars}
    {/strip}</td>
</tr>
{/foreach}
</table>
{/block}
