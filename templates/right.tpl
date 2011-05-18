{* Smarty *}
<script>
    $(document).ready(function(){
        $("#form_search_input").autocomplete("ajax/dict_substring_search.php",{
            minChars:3,
            maxItemsToShow:10,
            width:200,
            formatItem:function (row, i, num) {
                var result = row[0] + " <em>" +
                row[1] + "</em>";
                return result;
            },
            onItemSelect:function(el){
                location.href="dict.php?act=edit&id="+el.extra[1]
            }
        })
    })
</script>
<div>
    <a href="{$web_prefix}/?page=about">{t}О проекте{/t}</a><br/>
    <a href="{$web_prefix}/?page=publications">{t}Публикации{/t}</a><br/>
    <a href="{$web_prefix}/?page=team">{t}Участники{/t}</a><br/>
</div>
<div>
    <a href="{$web_prefix}/dict.php">{t}Словарь{/t}</a>
        {if $user_permission_dict && $dict_errors}(<a class="red" href="{$web_prefix}/dict.php?act=errata">{t}есть ошибки{/t}</a>){/if}<br/>
    <a href="{$web_prefix}/books.php">{t}Тексты{/t}</a><br/>
    <a href="{$web_prefix}/?page=stats">{t}Статистика{/t}</a><br/>
    <a href="{$web_prefix}/?rand">{t}Случайное предложение{/t}</a>
</div>
<div>
<b>{t}Поиск по словарю{/t}</b>
<form action="{$web_prefix}/dict.php?act=lemmata" method="post">
<input name="search_form" size="20" class="small" id="form_search_input"/>
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
