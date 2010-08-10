{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
</head>
<body>
</body>
{include file='header.tpl'}
<div id='content'>
    <p><a href="?">&lt;&lt;&nbsp;назад</a></p>
    <h2>Редактор морфологического словаря</h2>
    <form action='?act=lemmata' method='post'>Поиск леммы: <input name='search_lemma' size='25' maxlength='40' value='{$smarty.post.search_lemma|htmlspecialchars}'/> <input type='submit' value='Искать'/></form>
    <form action='?act=lemmata' method='post'>Поиск формы: <input name='search_form' size='25' maxlength='40' value='{$smarty.post.search_form|htmlspecialchars}'/> <input type='submit' value='Искать'/></form>
    {if $smarty.post.search_lemma}
        {if $search.lemma.count > 0}
        {foreach item=lemma from=$search.lemma.found}
            <a href="?act=edit&id={$lemma.id}">[{$lemma.id}] {$lemma.text}</a><br/>
        {/foreach}
        {else}
            Ничего не найдено.
        {/if}
    {/if}
    {if $smarty.post.search_form}
        {if $search.form.count > 0}
        {foreach item=lemma from=$search.form.found}
            <a href="?act=edit&id={$lemma.id}">[{$lemma.id}] {$lemma.text}</a><br/>
        {/foreach}
        {else}
            Ничего не найдено.
        {/if}
    {/if}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>

