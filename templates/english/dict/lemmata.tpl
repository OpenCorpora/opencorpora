{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='russian/header.tpl'}
<div id='content'>
    <p><a href="?">&lt;&lt;&nbsp;назад</a></p>
    {if $is_admin}
    <h2>Редактор морфологического словаря</h2>
    {else}
    <h2>Просмотр морфологического словаря</h2>
    {/if}
    <form action='?act=lemmata' method='post'>Поиск леммы: <input name='search_lemma' size='25' maxlength='40' value='{$smarty.post.search_lemma|htmlspecialchars}'/> <input type='submit' value='Искать'/></form>
    <form action='?act=lemmata' method='post'>Поиск формы: <input name='search_form' size='25' maxlength='40' value='{$smarty.post.search_form|htmlspecialchars}'/> <input type='submit' value='Искать'/></form>
    {if $smarty.post.search_lemma}
        {if $search.lemma.count > 0}
        {foreach item=lemma from=$search.lemma.found}
            <a href="?act=edit&amp;id={$lemma.id}&amp;found_lemma={$smarty.post.search_lemma|urlencode}">[{$lemma.id}] {$lemma.text}</a><br/>
        {/foreach}
        {else}
            <p>Ничего не найдено. <a href="?act=edit&amp;id=-1&amp;text={$smarty.post.search_lemma|urlencode}">Добавить лемму &laquo;{$smarty.post.search_lemma}&raquo;</a>?</p>
        {/if}
    {/if}
    {if $smarty.post.search_form}
        {if $search.form.count > 0}
        {foreach item=lemma from=$search.form.found}
            <a href="?act=edit&amp;id={$lemma.id}&amp;found_form={$smarty.post.search_form}">[{$lemma.id}] {$lemma.text}</a><br/>
        {/foreach}
        {else}
            <p>Ничего не найдено.</p>
        {/if}
    {/if}
</div>
<div id='rightcol'>
{include file='russian/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
