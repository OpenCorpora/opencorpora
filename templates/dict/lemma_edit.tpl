{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
    {if isset($smarty.get.saved)}
        <p class='p_info'>Изменения сохранены.</p>
    {/if}
    <p><form class='inline' method="post" action="?act=lemmata">
    {if $smarty.get.found_lemma}
    <input type='hidden' name='search_lemma' value='{$smarty.get.found_lemma}'/>
    {elseif $smarty.get.found_form}
    <input type='hidden' name='search_form' value='{$smarty.get.found_form}'/>
    {/if}
    <a href="#" onclick="document.forms[0].submit()">&lt;&lt;&nbsp;к поиску</a>
    </form></p>
    <div id="errata">
    {foreach from=$editor.errata item=error}
        <div>Ошибка.
        {if $error.type == 1}
            Несовместимые граммемы:
        {elseif $error.type == 2}
            Неизвестная граммема:
        {elseif $error.type == 3}
            Формы-дубликаты:
        {elseif $error.type == 4}
            Нет обязательной граммемы:
        {elseif $error.type == 5}
            Не разрешённая граммема:
        {/if}
        {$error.descr}
        </div>
    {/foreach}
    </div>
    {strip}
    <form action="?act=save" method="post">
        <b>Лемма</b>:<br/>
        <input type="hidden" name="lemma_id" value="{$editor.lemma.id}"/>
        {if $editor.lemma.id > 0}
        <input name="lemma_text" readonly="readonly" value="{$editor.lemma.text|htmlspecialchars}"/>
        <input name="lemma_gram" {if !$is_admin}readonly="readonly"{/if} value="{$editor.lemma.grms|htmlspecialchars}" size="40"/>
        <input type="button" onClick="location.href='dict_history.php?lemma_id={$editor.lemma.id}'" value="История"/>
        {if $is_admin}<input type="button" onClick="if (confirm('Вы уверены?')) location.href='dict.php?act=del_lemma&lemma_id={$editor.lemma.id}'" value="Удалить"/>{/if}
        {else}
        <input name="lemma_text" value="{$smarty.get.text}"/>
        <input name="lemma_gram" value="граммемы" onClick="this.value=''; this.onclick=''" size="40"/>
        {/if}
        <br/>
        <b>Формы
        {if $is_admin} (оставление левого поля пустым удаляет форму){/if}
        :</b><br/>
        <table cellpadding="3">
        {foreach item=form from=$editor.forms}
        <tr>
            <td><input name='form_text[]' {if !$is_admin}readonly="readonly"{/if} value='{$form.text|htmlspecialchars}'/>
            <td><input name='form_gram[]' {if !$is_admin}readonly="readonly"{/if} size='40' value='{$form.grms|htmlspecialchars}'/>
        </tr>
        {/foreach}
        {if $is_admin}
            <tr><td>&nbsp;<td><a href="#" onClick="dict_add_form(this); return false">Добавить ешё одну форму</a></tr>
        {/if}
        </table><br/>
        {if $is_admin}
            Комментарий к правке:<br/>
            <input name='comment' size='60'/><br/>
            <input type="button" onclick="submit_with_readonly_check(document.forms[1])" value="Сохранить"/>&nbsp;&nbsp;
            <input type="reset" value="Сбросить"/>
        {/if}
    </form>
    {/strip}
    <p><b>Связи</b></p>
    {* Off until there is jquery *}
    {*
    <p><a href="#" class="toggle" onclick="show(byid('add_link')); return false">Добавить связь</a></p>
    <form id="add_link">
        <select name='link_type'>
            <option value='0' selected='selected'>--Тип связи--</option>
            {html_options options=$link_types}
        </select>
        с леммой
        <input id="find_lemma"/> <input type='button' value='Найти'/>
    </form>
    *}
    <ul>
    {foreach item=link from=$editor.links}
    <li><a href="?act=edit&amp;id={$link.lemma_id}">{$link.lemma_text}</a> ({$link.name}) [<a href="?act=del_link&amp;id={$link.id}&amp;lemma_id={$editor.lemma.id}" onclick="return confirm('Вы уверены?')">удалить</a>]
    {/foreach}
    </ul>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
