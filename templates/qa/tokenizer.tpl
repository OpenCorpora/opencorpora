{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Странно токенизированные места</h1>
<p>Обновлено {$obj.timestamp|date_format:"%d.%m.%Y, %H:%M"}, однозначные решения в {$obj.coeff}% случаев.</p>
{if isset($obj.broken_sent_id)}
<p class='bgpink'>Сломалось на <a href="{$web_prefix}/sentence.php?id={$obj.broken_sent_id}">предложении {$obj.broken_sent_id}</a>, токен &laquo;<b>{$obj.broken_token_text|htmlspecialchars}</b>&raquo;.</p>
{/if}
<table border='1' cellspacing='0' cellpadding='3'>
{foreach item=i from=$obj.items}
<tr>
    <td>
        Предложение <a href='{$web_prefix}/sentence.php?id={$i.sent_id}'>{$i.sent_id}</a>
        {if $i.comments == 1}
        (<a href='{$web_prefix}/sentence.php?id={$i.sent_id}#comments'>комментарии</a>)
        {/if}
    </td>
    <td>{$i.coeff}</td>
    <td>{strip}
    {$i.lcontext|htmlspecialchars}
    <span class='doubt_border'>{$i.focus|htmlspecialchars}</span>
    {if $i.border}<span class='doubt_border'>&nbsp;&nbsp;</span>{/if}
    {$i.rcontext|htmlspecialchars}
    {/strip}</td>
    <td><a href='{$web_prefix}/books.php?book_id={$i.book_id}&amp;full#sen{$i.sent_id}'>исправить</a></td>
</tr>
{/foreach}
</table>
{/block}
