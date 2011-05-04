{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}Контроль словаря{/t}</h1>
<p>{$errata.lag} {t}ревизий не проверено{/t}, {t}всего{/t} {$errata.total} {t}ошибок{/t}.
{if $errata.total > 200}
    {if isset($smarty.get.all)}
    (<a href="?act=errata">{t}Показать только первые 200{/t}</a>.)
    {elseif isset($smarty.get.rand)}
    ({t}Показаны 200 случайных{/t}. <a href="?act=errata&amp;all">{t}Показать все{/t}</a>, <a href="?act=errata">{t}показать первые 200{/t}</a>.)
    {else}
    ({t}Показаны первые 200{/t}. <a href="?act=errata&amp;all">{t}Показать все{/t}</a>, <a href="?act=errata&amp;rand">{t}показать 200 случайных{/t}</a>.)
    {/if}
{/if}
</p>
{if $user_permission_dict}
<p>{t}Сбросить флаг проверки{/t} <a href="?act=clear_errata&amp;old" onclick="return confirm('{t}Вы уверены?{/t}')">{t}у всех ревизий{/t}</a>, <a href="?act=clear_errata" onclick="return confirm('{t}Вы уверены?{/t}')">{t}у текущих ревизий{/t}</a>.</p>
{/if}
<table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>id</th>
    <th>{t}Время проверки{/t}</th>
    <th>{t}Ревизия{/t}</th>
    <th>{t}Тип ошибки{/t}</th>
    <th>{t}Описание{/t}</th>
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
            {t}Несовместимые граммемы{/t}
        {elseif $error.type == 2}
            {t}Неизвестная граммема{/t}
        {elseif $error.type == 3}
            {t}Формы-дубликаты{/t}
        {elseif $error.type == 4}
            {t}Нет обязательной граммемы{/t}
        {elseif $error.type == 5}
            {t}Не разрешённая граммема{/t}
        {/if}
    </td>
    <td>{$error.description}</td>
    {if $user_permission_dict}<td>{if !$error.is_ok}<form class="inline" method='post' action="?act=not_error&amp;error_id={$error.id}"><button type='button' onclick='dict_add_exc_prepare($(this))'>OK</button></form>{else}<span class='hint' title='{$error.author_name}, {$error.exc_time|date_format:"%d.%m.%y, %H:%M"}, "{$error.comment|htmlspecialchars}"'>исключение</span>{/if}</td>{/if}
</tr>
{/foreach}
</table>
{/block}
