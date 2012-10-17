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
<h1>Управление пользователями</h1>
<form action="?act=save" method="post">
    <table class="table">
        <tr>
            <th rowspan='3'>Логин</th>
            <th rowspan='3'>Дата регистрации</th>
            <th rowspan='3'>Email</th>
            <th rowspan='3'>Игровой режим</th>
            <th colspan='6'>Права</th>
        </tr>
        <tr>
            <th rowspan='2'>Админ</th>
            <th rowspan='2'>Добавление текстов</th>
            <th rowspan='2'>Словарь</th>
            <th rowspan='2'>Снятие неодн-ти</th>
            <th colspan='2'>Проверка</th>
        </tr>
        <tr>
            <th>токенизации</th>
            <th>морфологии</th>
        </tr>
        {foreach item=user from=$users}
        <tr>
            <td>{if mb_strlen($user.user_name)>20}<abbr title="{$user.user_name}">{$user.user_name|mb_substr:0:20}...</abbr>{else}{$user.user_name}{/if}<input type='hidden' name='changed[{$user.user_id}]' value='0'/></td>
            <td>{$user.user_reg|date_format:"%d.%m.%y %H:%M"}</td>
            <td>{$user.user_email}</td>
            <td><input name='game[{$user.user_id}]' type='checkbox'{if $user.show_game > 0} checked="checked"{/if}/></td>
            <td><input name='perm[{$user.user_id}][admin]' type='checkbox'{if $user.perm_admin} checked="checked" disabled="disabled"{/if}/></td>
            <td><input name='perm[{$user.user_id}][adder]' type='checkbox'{if $user.perm_adder} checked="checked"{/if}/></td>
            <td><input name='perm[{$user.user_id}][dict]' type='checkbox'{if $user.perm_dict} checked="checked"{/if}/></td>
            <td><input name='perm[{$user.user_id}][disamb]' type='checkbox'{if $user.perm_disamb} checked="checked"{/if}/></td>
            <td><input name='perm[{$user.user_id}][tokens]' type='checkbox'{if $user.perm_check_tokens} checked="checked"{/if}/></td>
            <td><input name='perm[{$user.user_id}][morph]' type='checkbox'{if $user.perm_check_morph} checked="checked"{/if}/></td>
        </tr>
        {/foreach}
    </table>
    <button class="btn btn-large btn-primary">Сохранить</button>
</form>
{/block}
