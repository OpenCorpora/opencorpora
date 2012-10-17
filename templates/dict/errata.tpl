{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<script type="text/javascript">
    $(document).ready(function(){
        $(".ok_btn").one('click', function(){
            dict_add_exc_prepare($(this));
        });
    });
</script>
<h1>Контроль словаря</h1>
<p>{$errata.lag} ревизий не проверено, всего {$errata.total} ошибок.
{if $errata.total > 200}
    {if isset($smarty.get.all)}
    (<a href="?act=errata">Показать только первые 200</a>.)
    {elseif isset($smarty.get.rand)}
    (Показаны 200 случайных. <a href="?act=errata&amp;all">Показать все</a>, <a href="?act=errata">показать первые 200</a>.)
    {else}
    (Показаны первые 200. <a href="?act=errata&amp;all">Показать все</a>, <a href="?act=errata&amp;rand">показать 200 случайных</a>.)
    {/if}
{/if}
</p>
{if $user_permission_dict}
<p>Сбросить флаг проверки <a href="?act=clear_errata&amp;old" onclick="return confirm('Вы уверены?')">у всех ревизий</a>, <a href="?act=clear_errata" onclick="return confirm('Вы уверены?')">у текущих ревизий</a>.</p>
{/if}
<table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>id</th>
    <th>Время проверки</th>
    <th>Ревизия</th>
    <th>Тип ошибки</th>
    <th>Описание</th>
    {if $user_permission_dict}<th>&nbsp;</th>{/if}
</tr>
{foreach item=error from=$errata.errors}
{if $error.is_ok}
<tr style='background-color: #9f9'>
{else}
<tr>
{/if}
    <td>{$error.id}</td>
    <td>{$error.timestamp|date_format:"%d.%m.%Y, %H:%M"}</td>
    <td><a href="{$web_prefix}/dict_diff.php?lemma_id={$error.lemma_id}&amp;set_id={$error.set_id}">{$error.revision}</a></td>
    <td>
        {if $error.type == 1}
            Несовместимые граммемы
        {elseif $error.type == 2}
            Неизвестная граммема
        {elseif $error.type == 3}
            Формы-дубликаты
        {elseif $error.type == 4}
            Нет обязательной граммемы
        {elseif $error.type == 5}
            Не разрешённая граммема
        {/if}
    </td>
    <td>{$error.description}</td>
    {if $user_permission_dict}<td>{if !$error.is_ok}<form class="inline" method='post' action="?act=not_error&amp;error_id={$error.id}"><button type='button' class="ok_btn">OK</button></form>{else}<span class='hint' title='{$error.author_name}, {$error.exc_time|date_format:"%d.%m.%y, %H:%M"}, "{$error.comment|htmlspecialchars|replace:"'":"&#39;"}"'>исключение</span>{/if}</td>{/if}
</tr>
{/foreach}
</table>
{/block}
