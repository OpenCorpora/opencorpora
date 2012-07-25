{* Smarty *}
{if $smarty.session.hidemenu}
<a href="#" id="toggle-menu" onclick="toggle_rightmenu(this); return false;" class="show-menu" title="показать меню">←</a>
{else}
<a href="#" id="toggle-menu" onclick="toggle_rightmenu(this); return false;" class="hide-menu" title="скрыть меню">→</a>
{/if}
<div id="rightcol-inner" {if $smarty.session.hidemenu}style="display:none;"{/if}>
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
    <a href="{$web_prefix}/?page=team">{t}Разработчики{/t}</a><br/>
</div>
<div>
    <a href="{$web_prefix}/dict.php">{t}Словарь{/t}</a>
        {if $user_permission_dict && $dict_errors}(<a class="red" href="{$web_prefix}/dict.php?act=errata">{t}есть ошибки{/t}</a>){/if}<br/>
    <a href="{$web_prefix}/books.php">{t}Тексты{/t}</a><br/>
    <a href="{$web_prefix}/?page=stats">{t}Статистика{/t}</a><br/>
    <a href="{$web_prefix}/?rand">{t}Случайное предложение{/t}</a>
</div>
{if $is_logged}
<div>
    <a href="{$web_prefix}/tasks.php">{t}Задания{/t}</a>
</div>
{/if}
<div>
<b>{t}Поиск по словарю{/t}</b>
<form action="{$web_prefix}/dict.php?act=lemmata" method="post">
<input name="search_form" size="20" class="small" id="form_search_input"/>
</form>
</div>
<div>
    <b>{t}Свежие правки{/t}</b><br/>
    <a href='{$web_prefix}/history.php'>{t}В разметке{/t}</a><br/>
    <a href='{$web_prefix}/dict_history.php'>{t}В словаре{/t}</a><br/>
    <a href='{$web_prefix}/comments.php'>{t}Свежие комментарии{/t}</a>
</div>
<div>
<b><a href="{$web_prefix}/?page=downloads">Downloads</a></b>
</div>
{if $is_admin}
<div>
    <b>{t}Ревизия{/t}</b> {$svn_revision}
</div>
{/if}
<br/>
<div class='small'><a href="http://goo.gl/jm3ol">Сообщить о свободных текстах</a></div>
<br/><div class='small'>
{t}Подписаться на рассылку{/t}<br/>
(введите свой email):
<form action="http://groups.google.com/group/opencorpora/boxsubscribe">
<input name='email'/><br/>
<input type='submit' value='Подписаться'/>
</form>
</div>
<!-- Yandex.Metrika counter -->
{literal}
<div style="display:none;"><script type="text/javascript">
(function(w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter9552538 = new Ya.Metrika({id:9552538,
                trackLinks:true});
        }
        catch(e) { }
    });
})(window, "yandex_metrika_callbacks");
{/literal}
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div><img src="//mc.yandex.ru/watch/9552538" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</div>
