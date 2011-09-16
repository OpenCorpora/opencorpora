{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Странное деление предложений</h1>
<p>Список обновляется раз в час.</p>
<table border='1' cellspacing='0' cellpadding='3'>
<tr><th>id</th><th>Текст</th><th>&nbsp;</th></tr>
{foreach from=$sentences item=s}
<tr>
    <td><a href="{$web_prefix}/sentence.php?id={$s.id}">{$s.id}</a></td>
    <td>{$s.text|htmlspecialchars}</td>
    <td><a href='{$web_prefix}/books.php?book_id={$s.book_id}&amp;full#sen{$s.id}'>исправить</a></td>
</tr>
{/foreach}
</table>
{/block}
