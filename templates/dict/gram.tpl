{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
    <p><a href="?">&lt;&lt;&nbsp;{t}назад{/t}</a></p>
    <h2>{t}Граммемы{/t}</h2>
    {if $is_admin}
    <b>{t}Добавить граммему{/t}</b>:<br/>
    <form action="?act=add_gram" method="post" class="inline">
        {t}Внутр. ID{/t} <input name="g_name" value="grm" size="10" maxlength="20"/>,
        {t}внешн. ID{/t} <input name="outer_id" value="грм" size="10" maxlength="20"/>,
        {t}родительская граммема{/t} <select name='parent_gram'><option value='0'>--{t}Не выбрана{/t}--</option>{html_options options=$select}</select>,<br/>
        {t}описание{/t} <input name="descr" size="40"/>
        <input type="button" value="{t}Добавить{/t}" onclick="submit_with_readonly_check(document.forms[0])"/>
    </form>
    <br/><br/>
    {/if}
    <form action="?act=edit_gram" method="post">
    <table border="1" cellspacing="0" cellpadding="2">
        <tr>
            <th>{if $smarty.get.order == 'priority'}{t}Порядок{/t}{else}<a href="?act=gram&amp;order=priority">{t}Порядок{/t}</a>{/if}</th>
            <th>{if $smarty.get.order == 'id'}{t}Внутр. ID{/t}{else}<a href="?act=gram&amp;order=id">{t}Внутр. ID{/t}</a>{/if}</th>
            <th>{if $smarty.get.order == 'outer'}{t}Внешн. ID{/t}{else}<a href="?act=gram&amp;order=outer">{t}Внешн. ID{/t}</a>{/if}</th>
            <th>{t}Описание{/t}</th>
            <th>Parent</th>
            {if $is_admin}<th>&nbsp;</th>{/if}
        </tr>
        {foreach key=id item=grammem from=$grammems}
            <tr class='{$grammem.css_class}'><td><a name='g{$grammem.id}'></a>{$grammem.order}<td>{$grammem.name}</td><td>{$grammem.outer_id|default:'&nbsp;'}</td><td>{$grammem.description}</td><td>{$grammem.parent_name|default:'&mdash;'}</td>{if $is_admin}<td>[<a href='?act=move_gram&amp;dir=up&amp;id={$grammem.id}'>{t}вверх{/t}</a>] [<a href='?act=move_gram&amp;dir=down&amp;id={$grammem.id}'>{t}вниз{/t}</a>] [<a href='#' onClick='edit_gram(this, {$grammem.id}); return false;'>{t}ред.{/t}</a>] [<a href='?act=del_gram&amp;id={$grammem.id}' onClick="return confirm('{t}Вы уверены, что хотите удалить граммему?{/t}');">x</a>]</td>{/if}</tr>
        {/foreach}
    </table>
    </form>
{/block}
