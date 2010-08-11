{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
<script language='JavaScript' src='{$web_prefix}/js/main.js'></script>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
    {if isset($smarty.get.saved)}
        <p class='p_info'>Изменения сохранены.</p>
    {/if}
    <p><a href="?act=lemmata">&lt;&lt;&nbsp;к поиску</a></p>
    {strip}
    <form action="?act=save" method="post">
        <b>Лемма</b>:<br/>
        <input type="hidden" name="lemma_id" value="{$editor.lemma_id}"/>
        <input name="lemma_text" readonly="readonly" value="{$editor.lemma_text|htmlspecialchars}"/> (<a href="dict_history.php?lemma_id={$editor.lemma_id}">история</a>)<br/>
        <b>Формы (оставление левого поля пустым удаляет форму):</b><br/>
        <table cellpadding="3">
        {foreach item=form from=$editor.forms}
        <tr>
            <td><input name='form_text[]' value='{$form.text|htmlspecialchars}'/>
            <td><input name='form_gram[]' size='40' value='{$form.grms|htmlspecialchars}'/>
        </tr>
        {/foreach}
        <tr><td>&nbsp;<td><a href="#" onClick="dict_add_form(this); return false">Добавить ешё одну форму</a></tr>
        </table><br/>
        <input type="submit" value="Сохранить"/>&nbsp;&nbsp;<input type="reset" value="Сбросить"/>
    </form>
    {/strip}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>

