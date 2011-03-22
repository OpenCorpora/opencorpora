{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
    {if isset($smarty.get.saved)}
        <p class='p_info'>{t}Изменения сохранены.{/t}</p>
    {/if}
    <p><form class='inline' method="post" action="?act=lemmata">
    {if $smarty.get.found_lemma}
    <input type='hidden' name='search_lemma' value='{$smarty.get.found_lemma}'/>
    {elseif $smarty.get.found_form}
    <input type='hidden' name='search_form' value='{$smarty.get.found_form}'/>
    {/if}
    <a href="#" onclick="document.forms[0].submit()">&lt;&lt;&nbsp;{t}к поиску{/t}</a>
    </form></p>
    <div id="errata">
    {foreach from=$editor.errata item=error}
        <div>{t}Ошибка{/t}.
        {if $error.type == 1}
            {t}Несовместимые граммемы{/t}:
        {elseif $error.type == 2}
            {t}Неизвестная граммема{/t}:
        {elseif $error.type == 3}
            {t}Формы-дубликаты{/t}:
        {elseif $error.type == 4}
            {t}Нет обязательной граммемы{/t}:
        {elseif $error.type == 5}
            {t}Не разрешённая граммема{/t}:
        {/if}
        {$error.descr}
        </div>
    {/foreach}
    </div>
    {strip}
    <form action="?act=save" method="post">
        <b>{t}Лемма{/t}</b>:<br/>
        <input type="hidden" name="lemma_id" value="{$editor.lemma.id}"/>
        {if $editor.lemma.id > 0}
        <input name="lemma_text" readonly="readonly" value="{$editor.lemma.text|htmlspecialchars}"/>
        <input name="lemma_gram" {if !$is_admin}readonly="readonly"{/if} value="{$editor.lemma.grms|htmlspecialchars}" size="40"/>
        <input type="button" onClick="location.href='dict_history.php?lemma_id={$editor.lemma.id}'" value="{t}История{/t}"/>
        {if $is_admin}<input type="button" onClick="if (confirm('Вы уверены?')) location.href='dict.php?act=del_lemma&lemma_id={$editor.lemma.id}'" value="{t}Удалить{/t}"/>{/if}
        {else}
        <input name="lemma_text" value="{$smarty.get.text}"/>
        <input name="lemma_gram" value="{t}граммемы{/t}" onClick="this.value=''; this.onclick=''" size="40"/>
        {/if}
        <br/>
        <b>{t}Формы{/t}
        {if $is_admin} ({t}оставление левого поля пустым удаляет форму{/t}){/if}
        :</b><br/>
        <table cellpadding="3">
        {foreach item=form from=$editor.forms}
        <tr>
            <td><input name='form_text[]' {if !$is_admin}readonly="readonly"{/if} value='{$form.text|htmlspecialchars}'/>
            <td><input name='form_gram[]' {if !$is_admin}readonly="readonly"{/if} size='40' value='{$form.grms|htmlspecialchars}'/>
        </tr>
        {/foreach}
        {if $is_admin}
            <tr><td>&nbsp;<td><a href="#" onClick="dict_add_form(this); return false">{t}Добавить ешё одну форму{/t}</a></tr>
        {/if}
        </table><br/>
        {if $is_admin}
            {t}Комментарий к правке{/t}:<br/>
            <input name='comment' size='60'/><br/>
            <input type="button" onclick="submit_with_readonly_check(document.forms[1])" value="{t}Сохранить{/t}"/>&nbsp;&nbsp;
            <input type="reset" value="{t}Сбросить{/t}"/>
        {/if}
    </form>
    {/strip}
    <p><b>{t}Связи{/t}</b></p>
    <p><a href="#" class="toggle" onclick="show(byid('add_link')); return false">{t}Добавить связь{/t}</a></p>
    <form id="add_link" method='post' action='?act=add_link'>
        <input type='hidden' name='from_id' value='{$editor.lemma.id}'/>
        <select name='link_type'>
            <option value='0' selected='selected'>--{t}Тип связи{/t}--</option>
            {html_options options=$link_types}
        </select>
        {t}с леммой{/t}
        <input id="find_lemma"/> <input type='button' value='{t}Найти{/t}' onclick='get_lemma_search()'/>
    </form>
    <ul>
    {foreach item=link from=$editor.links}
    <li><a href="?act=edit&amp;id={$link.lemma_id}">{$link.lemma_text}</a> ({$link.name}) [<a href="?act=del_link&amp;id={$link.id}&amp;lemma_id={$editor.lemma.id}" onclick="return confirm('{t}Вы уверены?{/t}')">{t}удалить{/t}</a>]
    {/foreach}
    </ul>
{/block}
