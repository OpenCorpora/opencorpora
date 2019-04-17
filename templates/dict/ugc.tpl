{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-diff").click(function(e) {
            var $tr = $(this).parents('tr').first();
            $tr.next().find('pre').toggle();
            e.preventDefault();
        });
    });
</script>
<h1>Правки, ожидающие модерации</h1>
{if $user_permission_dict}
{/if}
<table border='1' cellspacing='0' cellpadding='2' width='95%'>
<tr>
    <th>id</th>
    <th>Создана</th>
    <th>Автор</th>
    <th>Комментарий</th>
    <th colspan='2' width='70%'>Содержание</th>
    {if $user_permission_dict}<th></th>{/if}
</tr>
{foreach item=edit from=$data}
<tr>
    <td rowspan='2'>{$edit.rev_id}</td>
    <td rowspan='2'>{$edit.created_ts|date_format:"%d.%m.%Y, %H:%M"}</td>
    <td rowspan='2'><a href="/user.php?id={$edit.user_id}">{$edit.user_name}</a></td>
    <td rowspan='2'>{$edit.comment|htmlspecialchars|default:"&nbsp;"}</td>
    <td colspan='2'><a href="#" class='toggle-diff'>показать/скрыть</a></td>
    <td rowspan='2' valign='top'>
        <a class="btn btn-small btn-success" href="?act=approve&id={$edit.rev_id}">Одобрить</a>
    </td>
</tr>
<tr><td><pre style='display: none'>
{foreach from=$edit.diff[0] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td>
    <td><pre style='display: none'>
{foreach from=$edit.diff[1] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td></tr>
{/foreach}
</table>
{/block}
