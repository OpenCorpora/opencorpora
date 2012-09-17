{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
    <script type="text/javascript">
        $(document).ready(function(){
            $(".edit_gram_link").one('click',edit_gram)
            })
    </script>
    <h2>{t}Граммемы{/t}</h2>
    <ul class="breadcrumb">
        <li><a href="{$web_prefix}/dict.php">Словарь</a> <span class="divider">/</span></li>
        <li>Редактор граммем</li>
    </ul>
    {if $user_permission_dict}
    <b>{t}Добавить граммему{/t}</b>:<br/>
    <form action="?act=add_gram" method="post" class="form-inline">
        <label for="g_name">{t}Внутр. ID{/t}</label> <input type="text" name="g_name" value="grm" class="input-mini" maxlength="20">,
        <label for="outer_id">{t}внешн. ID{/t}</label> <input type="text" name="outer_id" value="грм" class="input-mini" maxlength="20">,
        <label for="parent_gram">{t}родительская граммема{/t}</label> <select name='parent_gram'><option value='0'>--{t}Не выбрана{/t}--</option>{html_options options=$select}</select>,
        <label for="descr">{t}описание{/t}</label> <input name="descr" type="text" class="input-medium">
        <button type="button" class="btn" onclick="submit_with_readonly_check($(this).closest('form'))"/> {t}Добавить{/t}</button>
    </form>
    {/if}
    <form action="?act=edit_gram" method="post">
    <table border="0" class="table table-collapsed" cellspacing="0" cellpadding="2">
        <tr>
            <th>{if $order == 'priority'}{t}Порядок{/t}{else}<a href="?act=gram&amp;order=priority">{t}Порядок{/t}</a>{/if}</th>
            <th>{if $order == 'id'}{t}Внутр. ID{/t}{else}<a href="?act=gram&amp;order=id">{t}Внутр. ID{/t}</a>{/if}</th>
            <th>{if $order == 'outer'}{t}Внешн. ID{/t}{else}<a href="?act=gram&amp;order=outer">{t}Внешн. ID{/t}</a>{/if}</th>
            <th>{t}Описание{/t}</th>
            <th>Parent</th>
            {if $user_permission_dict}<th>&nbsp;</th>{/if}
        </tr>
        {foreach key=id item=grammem from=$grammems}
            <tr class='{$grammem.css_class}'><td><a name='g{$grammem.id}'></a>{$grammem.order}<td>{$grammem.name}</td><td>{$grammem.outer_id|default:'&nbsp;'}</td><td>{$grammem.description}</td><td>{$grammem.parent_name|default:'&mdash;'}</td>{if $user_permission_dict}<td>[<a href='?act=move_gram&amp;dir=up&amp;id={$grammem.id}'>{t}вверх{/t}</a>] [<a href='?act=move_gram&amp;dir=down&amp;id={$grammem.id}'>{t}вниз{/t}</a>] [<a href='#' class="edit_gram_link" rel="{$grammem.id}">{t}ред.{/t}</a>] [<a href='?act=del_gram&amp;id={$grammem.id}' onClick="return confirm('{t}Вы уверены, что хотите удалить граммему?{/t}');">x</a>]</td>{/if}</tr>
        {/foreach}
    </table>
    </form>
{/block}
