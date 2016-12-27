{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h1>Странное деление предложений</h1>
<p>Список обновляется раз в час.</p>
<table class="table">
<tr><th>id</th><th>Текст</th><th>&nbsp;</th></tr>
{foreach from=$sentences item=s}
<tr>
    <td><a href="/sentence.php?id={$s.id}">{$s.id}</a></td>
    <td>{$s.text|htmlspecialchars}</td>
    <td><a href='/books.php?book_id={$s.book_id}&amp;full=1#sen{$s.id}'>исправить</a></td>
</tr>
{/foreach}
</table>
{/block}
