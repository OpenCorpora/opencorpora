{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
    <script type="text/javascript">
        $(document).ready(function(){
            $("#add_form_link").bind('click',dict_add_form)
            })
    </script>
    {if isset($smarty.get.saved)}
        <div class='info'>Изменения сохранены.</div>
    {/if}
    <!--p><form class='inline' method="post" action="?act=lemmata">
    {if $smarty.get.found_lemma}
    <input type='hidden' name='search_lemma' value='{$smarty.get.found_lemma}'/>
    {elseif $smarty.get.found_form}
    <input type='hidden' name='search_form' value='{$smarty.get.found_form}'/>
    {/if}
    <a href="#" onclick="$(this).closest('form').submit()">&lt;&lt;&nbsp;к поиску</a>
    </form></p-->
    <h1>Лемма &laquo;{$editor.lemma.text|htmlspecialchars}&raquo;</h1>
    <ul class="breadcrumb">
        <li><a href="{$web_prefix}/dict.php">Словарь</a> <span class="divider">/</span></li>
        <li><a href="{$web_prefix}/dict.php">Поиск</a> <span class="divider">/</span></li>
    </ul>
    <div id="errata">
    {foreach from=$editor.errata item=error}
        <div class="{if $error.is_ok}ok{else}error{/if}">Ошибка.
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
        {$error.descr}.
        {if $user_permission_dict}
        {if $error.is_ok}
        (<span class='hint' title='{$error.author_name}, {$error.exc_time|date_format:"%d.%m.%y, %H:%M"}, "{$error.comment|htmlspecialchars}"'>Эта ошибка была помечена как исключение</span>.)
        {else}
        <form action="?act=not_error&amp;error_id={$error.id}" method="post" class="inline"><button type='button' onclick='dict_add_exc_prepare($(this))'>Это не ошибка</button></form>
        {/if}
        {/if}
        </div>
    {/foreach}
    </div>
    {strip}
    <form action="?act=save" method="post">
        <b>Лемма</b>:<br/>
        <p class="form-inline"><input type="hidden" name="lemma_id" value="{$editor.lemma.id}"/>
        {if $editor.lemma.id > 0}
        <input type="text" name="lemma_text" value="{$editor.lemma.text|htmlspecialchars}"> 
        <input type="text" name="lemma_gram" {if !$user_permission_dict}readonly="readonly"{/if} value="{$editor.lemma.grms|htmlspecialchars}" size="40">  
        <button class="btn" type="button" onClick="location.href='dict_history.php?lemma_id={$editor.lemma.id}'" >История</button> 
        {if $user_permission_dict}<button type="button" class="btn" onClick="if (confirm('Вы уверены?')) location.href='dict.php?act=del_lemma&lemma_id={$editor.lemma.id}'" >Удалить лемму</button>{/if}
        {else}
        <input type="text" name="lemma_text" value="{$smarty.get.text}"> 
        <input type="text" name="lemma_gram" value="граммемы" onClick="this.value=''; this.onclick=''" size="40">
        {/if}
        </p>
        <b>Формы
        {if $user_permission_dict} (оставление левого поля пустым удаляет форму){/if}
        :</b><br/>
        <table cellpadding="3">
        {foreach item=form from=$editor.forms}
        <tr>
            <td><input name='form_text[]' {if !$user_permission_dict}readonly="readonly"{/if} value="{$form.text|htmlspecialchars}"/>
            <td><input name='form_gram[]' {if !$user_permission_dict}readonly="readonly"{/if} size='40' value="{$form.grms|htmlspecialchars}"/>
        </tr>
        {/foreach}
        {if $user_permission_dict}
            <tr><td>&nbsp;<td><a id="add_form_link" href="#">Добавить ешё одну форму</a></tr>
        {/if}
        </table><br/>
        {if $user_permission_dict}
            Комментарий к правке:<br/>
            <input name='comment' size='60'/><br/>
            <input type="button" onclick="submit_with_readonly_check($(this).closest('form'))" value="Сохранить"/>&nbsp;&nbsp;
            <input type="reset" value="Сделать как было"/>
        {/if}
    </form>
    {/strip}
    <p><b>Связи</b></p>
    <p><a href="#" class="toggle" onclick="$('#add_link').show(); return false">Добавить связь</a></p>
    <form id="add_link" method='post' class='hidden-block' action='?act=add_link'>
        <input type='hidden' name='from_id' value='{$editor.lemma.id}'/>
        <select name='link_type'>
            <option value='0' selected='selected'>--Тип связи--</option>
            {html_options options=$link_types}
        </select>
        с леммой
        <input id="find_lemma"/> <input type='button' value='Найти' onclick='get_lemma_search()'/>
    </form>
    <ul>
    {foreach item=link from=$editor.links}
    <li><a href="?act=edit&amp;id={$link.lemma_id}">{$link.lemma_text}</a> ({$link.name}) [<a href="?act=del_link&amp;id={$link.id}&amp;lemma_id={$editor.lemma.id}" onclick="return confirm('Вы уверены?')">удалить</a>]
    {/foreach}
    </ul>
{/block}
