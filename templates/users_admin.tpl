{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript">
        $(document).ready(function(){
            $("input:checkbox").click(function(){
                $(this).parent().toggleClass('bggreen');
                $(this).closest('tr').find("input[type='hidden']").val(1);
            })
        })
</script>
<h1>{t}Управление пользователями{/t}</h1>
<form action="?act=save" method="post">
<table border='1' cellspacing='0' cellpadding='3'>
<tr>
    <th rowspan='2'>Логин</th>
    <th rowspan='2'>Зарегистрирован</th>
    <th colspan='5'>Права</th>
</tr>
<tr>
    <th>Админ</th>
    <th>Добавление текстов</th>
    <th>Словарь</th>
    <th>Токенизация</th>
    <th>Морфология</th>
</tr>
{foreach item=user from=$users}
<tr>
    <td>{$user.user_name}<input type='hidden' name='changed[{$user.user_id}]' value='0'/></td>
    <td>{$user.user_reg|date_format:"%d.%m.%Y, %H:%M"}</td>
    <td><input name='perm[{$user.user_id}][admin]' type='checkbox'{if $user.perm_admin} checked="checked" disabled="disabled"{/if}/></td>
    <td><input name='perm[{$user.user_id}][adder]' type='checkbox'{if $user.perm_adder} checked="checked"{/if}/></td>
    <td><input name='perm[{$user.user_id}][dict]' type='checkbox'{if $user.perm_dict} checked="checked"{/if}/></td>
    <td><input name='perm[{$user.user_id}][tokens]' type='checkbox'{if $user.perm_check_tokens} checked="checked"{/if}/></td>
    <td><input name='perm[{$user.user_id}][morph]' type='checkbox'{if $user.perm_check_morph} checked="checked"{/if}/></td>
</tr>
{/foreach}
</table>
<button>Сохранить</button>
</form>
{/block}
