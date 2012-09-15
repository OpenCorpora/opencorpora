{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
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
    <h1>Словарь</h1>
    <p>{t}Всего{/t} {$stats.cnt_g} {t}граммем{/t}, {$stats.cnt_l} {t}лемм{/t}, {$stats.cnt_f} {t}форм в индексе{/t} ({$stats.cnt_r} {t}ревизий не проверено{/t}).</p>
    <b>{t}Поиск по словарю{/t}</b>
    <form action="{$web_prefix}/dict.php?act=lemmata" method="post">
    <input name="search_form" type="text" class="span3" id="form_search_input" placeholder="Введите слово...">
</form>
    {if $user_permission_dict}
        <p><a href="?act=gram">{t}Редактор граммем{/t}</a><br/>
        <a href="?act=gram_restr">{t}Ограничения на граммемы{/t}</a></p>
        <p><a href="?act=lemmata">{t}Редактор лемм{/t}</a><br/>
        <a href="?act=errata">{t}Ошибки в словаре{/t}</a> ({$stats.cnt_v} {t}ревизий не проверено{/t})</p>
        <p><button class="btn" onClick="location.href='?act=edit&amp;id=-1'">{t}Добавить лемму{/t}</button></p>
    {else}
        <p><a href="?act=gram">{t}Просмотр граммем{/t}</a><br/>
        <a href="?act=gram_restr">{t}Ограничения на граммемы{/t}</a></p>
        <p><a href="?act=lemmata">{t}Просмотр лемм{/t}</a><br/>
        <a href="?act=errata">{t}Ошибки в словаре{/t}</a> ({$stats.cnt_v} {t}ревизий не проверено{/t})</p>
    {/if}
{/block}
