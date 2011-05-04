{* Smarty *}
<div>
    <a href="{$web_prefix}/?page=about">{t}О проекте{/t}</a><br/>
    <a href="{$web_prefix}/?page=publications">{t}Публикации{/t}</a><br/>
    <a href="{$web_prefix}/?page=team">{t}Участники{/t}</a><br/>
</div>
<div>
    <a href="{$web_prefix}/dict.php">{t}Словарь{/t}</a>
        {if $user_permission_dict && $dict_errors}(<a class="red" href="{$web_prefix}/dict.php?act=errata">{t}есть ошибки{/t}</a>){/if}<br/>
    <a href="{$web_prefix}/?page=stats">{t}Статистика{/t}</a><br/>
    <a href="{$web_prefix}/?rand">{t}Случайное предложение{/t}</a>
</div>
<div>
<b>{t}Поиск по словарю{/t}</b>
<form action="{$web_prefix}/dict.php?act=lemmata" method="post">
<input name="search_form" size="20" class="small"/>
</form>
</div>
<div>
    <b>{t}Свежие правки{/t}</b><br/>
    <a href='{$web_prefix}/history.php'>{t}В разметке{/t}</a><br/>
    <a href='{$web_prefix}/dict_history.php'>{t}В словаре{/t}</a>
</div>
<div>
<b><a href="{$web_prefix}/?page=downloads">Downloads</a></b>
</div>
{if $is_admin}
<div>
    <b>{t}Ревизия{/t}</b> {$svn_revision}
</div>
{/if}
