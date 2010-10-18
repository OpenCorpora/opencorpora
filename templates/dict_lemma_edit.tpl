{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
    {if isset($smarty.get.saved)}
        <p class='p_info'>Изменения сохранены.</p>
    {/if}
    <p><a href="?act=lemmata">&lt;&lt;&nbsp;к поиску</a></p>
    {strip}
    <form action="?act=save" method="post">
        <b>Лемма</b>:<br/>
        <input type="hidden" name="lemma_id" value="{$editor.lemma.id}"/>
        {if $editor.lemma.id > 0}
        <input name="lemma_text" readonly="readonly" value="{$editor.lemma.text|htmlspecialchars}"/>
        <input name="lemma_gram" value="{$editor.lemma.grms|htmlspecialchars}"/>
        <input type="button" onClick="location.href='dict_history.php?lemma_id={$editor.lemma.id}'" value="История"/>
        <input type="button" onClick="if (confirm('Вы уверены?')) location.href='dict.php?act=del_lemma&lemma_id={$editor.lemma.id}'" value="Удалить"/>
        {else}
        <input name="lemma_text" value="{$smarty.get.text}"/>
        <input name="lemma_gram" value="граммемы" onClick="this.value=''; this.onclick=''"/>
        {/if}
        <br/>
        <b>Формы
        {if $is_admin} (оставление левого поля пустым удаляет форму){/if}
        :</b><br/>
        <table cellpadding="3">
        {foreach item=form from=$editor.forms}
        <tr>
            <td><input name='form_text[]' value='{$form.text|htmlspecialchars}'/>
            <td><input name='form_gram[]' size='40' value='{$form.grms|htmlspecialchars}'/>
        </tr>
        {/foreach}
        {if $is_admin}
            <tr><td>&nbsp;<td><a href="#" onClick="dict_add_form(this); return false">Добавить ешё одну форму</a></tr>
        {/if}
        </table><br/>
        {if $is_admin}
            <input type="submit" value="Сохранить"/>&nbsp;&nbsp;
        {/if}
        <input type="reset" value="Сбросить"/>
    </form>
    {/strip}
    <b>Связи</b>
    <ul>
    {foreach item=link from=$editor.links}
    <li><a href="?act=edit&amp;id={$link.lemma_id}">{$link.lemma_text}</a> ({$link.name})
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
