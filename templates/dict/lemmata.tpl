{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
    <p><a href="?">&lt;&lt;&nbsp;{t}назад{/t}</a></p>
    {if $is_admin}
    <h2>{t}Редактор морфологического словаря{/t}</h2>
    {else}
    <h2>{t}Просмотр морфологического словаря{/t}</h2>
    {/if}
    <form action='?act=lemmata' method='post'>{t}Поиск леммы{/t}: <input name='search_lemma' size='25' maxlength='40' value='{$smarty.post.search_lemma|htmlspecialchars}'/> <input type='submit' value='{t}Искать{/t}'/></form>
    <form action='?act=lemmata' method='post'>{t}Поиск формы{/t}: <input name='search_form' size='25' maxlength='40' value='{$smarty.post.search_form|htmlspecialchars}'/> <input type='submit' value='{t}Искать{/t}'/></form>
    {if $smarty.post.search_lemma}
        {if $search.lemma.count > 0}
        {foreach item=lemma from=$search.lemma.found}
            <a href="?act=edit&amp;id={$lemma.id}&amp;found_lemma={$smarty.post.search_lemma|urlencode}">[{$lemma.id}] {$lemma.text}</a><br/>
        {/foreach}
        {else}
            <p>{t}Ничего не найдено.{/t} <a href="?act=edit&amp;id=-1&amp;text={$smarty.post.search_lemma|urlencode}">{t}Добавить лемму{/t} &laquo;{$smarty.post.search_lemma}&raquo;</a>?</p>
        {/if}
    {/if}
    {if $smarty.post.search_form}
        {if $search.form.count > 0}
        {foreach item=lemma from=$search.form.found}
            <a href="?act=edit&amp;id={$lemma.id}&amp;found_form={$smarty.post.search_form}">[{$lemma.id}] {$lemma.text}</a><br/>
        {/foreach}
        {else}
            <p>{t}Ничего не найдено.{/t}</p>
        {/if}
    {/if}
{/block}
