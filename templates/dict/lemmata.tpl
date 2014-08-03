{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
    <h1>{if $user_permission_dict} Редактор морфологического словаря {else} Просмотр морфологического словаря {/if}</h1>
    <ul class="breadcrumb">
        <li><a href="{$web_prefix}/dict.php">Словарь</a> <span class="divider">/</span></li>
        <li>Поиск</li>
    </ul>
    <form method='get' class="form-inline">

        <label for="search_lemma">Поиск леммы:</label> <input type="text" name='search_lemma' class="input-medium" maxlength='40' value="{$smarty.get.search_lemma|htmlspecialchars}">
        <input type='hidden' name="act" value="lemmata" />
        <button type='submit' class="btn">Искать</button>

    </form>
    <form method='get' class="form-inline">

        <label for="search_form">Поиск формы:</label> <input type="text" name='search_form' maxlength='40' value="{$smarty.get.search_form|htmlspecialchars}" class="input-medium">
        <input type='hidden' name="act" value="lemmata" />
        <button type='submit' class="btn">Искать</button>

    </form>
    {if $smarty.get.search_lemma}
        {if $search.lemma.count > 0}
        {foreach item=lemma from=$search.lemma.found}
            <a href="?act=edit&amp;id={$lemma.id}&amp;found_lemma={$smarty.post.search_lemma|urlencode}">[{$lemma.id}] {$lemma.text}</a> ({$lemma.pos})<br/>
        {/foreach}
        {else}
            <p>Ничего не найдено. <a href="?act=edit&amp;id=-1&amp;text={$smarty.post.search_lemma|urlencode}">Добавить лемму &laquo;{$smarty.post.search_lemma}&raquo;</a>?</p>
        {/if}
    {/if}
    {if $smarty.get.search_form}
        {if $search.form.count > 0}
        {foreach item=lemma from=$search.form.found}
            <a href="?act=edit&amp;id={$lemma.id}&amp;found_form={$smarty.post.search_form}">[{$lemma.id}] {$lemma.text}</a> ({$lemma.pos})<br/>
        {/foreach}
        {else}
            <p>Ничего не найдено.</p>
        {/if}
    {/if}
{/block}
