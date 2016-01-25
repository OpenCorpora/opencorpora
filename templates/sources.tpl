{* Smarty *}
{extends file="common.tpl"}
{block name=content}
{literal}
<script type="text/javascript">
    $(document).ready(function(){
        $("a.ab").click(function(event){
            var sid = $(this).data('srcid');
            $(this).hide();
            var $a1 = $(document.createElement('a')).html('уже есть').addClass('pseudo').attr('href', '#').click(function(event){
                $(this).next().hide();
                var $i = $(document.createElement('input')).attr('size', 3).val('id');
                var $b = $(document.createElement('button')).addClass('btn').html('Ok').click(function(event){
                    $.post('ajax/bind_book.php', {'sid':sid, 'book_id':$(this).closest('td').find('input').val()}, function(res) {
                        if (res.error) {
                            alert('Bind failed: ' + res.message);
                        }
                        else {
                            $i.hide();
                            $b.hide().closest('td').html('<a href="books.php?book_id=' + res.book_id + '" class="small">' + res.title + '</a>');
                        }
                    });
                });
                $(this).parent().empty().append($i, $b);
                event.preventDefault();
            });
            var $a2 = $(document.createElement('a')).html('создать').addClass('pseudo').attr('href', '#').click(function(event){
                event.preventDefault();
                var $i = $(document.createElement('input')).attr('size', '20').val('Название');
                var $b = $(document.createElement('button')).addClass('btn').html('Ok').click(function(event){
                    $(this).attr('disabled', 'disabled');
                    $.post('ajax/bind_book.php', {'sid':sid, 'book_id':-1, 'book_name':$(this).closest('td').find('input').val()}, function(res) {
                        if (res.error) {
                            alert('Bind failed: ' + res.message);
                        }
                        else {
                            $i.hide();
                            $b.hide().closest('td').html('<a href="books.php?book_id=' + res.book_id + '" class="small">' + res.title + '</a>');
                        }
                    });
                });
                var $cnc = $(document.createElement('a')).html('Отмена').addClass('pseudo').attr('href', '#').click(function(){
                    event.preventDefault();
                    location.reload();
                });
                $(this).parent().empty().append($i, $b, '&nbsp;', $cnc);
            });
            $(this).after($a1, '&nbsp;или&nbsp;', $a2);
            event.preventDefault();
        });
        $("a.ya").click(function(event){
            $.post('ajax/own_book.php', {'sid':$(this).data('srcid'), 'status':$(this).data('status')}, function(res) {
                var $t = $(event.target);
                if (!res.error) {
                    if ($t.data('status') == 1) {
                        $t.data('status', '0').html('не хочу');
                        var $b = $(document.createElement('button')).addClass('bgo').data({'srcid':$t.data('srcid'), 'status':'1'}).html('Готово').click(function(event){change_source_status(event)});
                        $t.closest('tr').addClass('bgyellow').children().last().append($b);
                    } else {
                        $t.data('status', '1').html('хочу').closest('tr').removeClass().children().last().empty();
                    }
                }
                else {
                    alert('Bind failed');
                }
            });
            event.preventDefault();
        });
        $("a.ac").click(function(event){
            var $i = $(document.createElement('textarea')).attr('rows', 2);
            var $b = $(document.createElement('button')).html('Добавить').data('srcid', $(event.target).data('srcid')).click(function(event){
                var $t = $(event.target);
                $t.attr('disabled', 'disabled');
                $.post('ajax/post_comment.php', {'type':'source', 'id':$t.data('srcid'), 'text':$t.closest('td').find('textarea').val()}, function(res) {
                    if (!res.error) {
                        $t.replaceWith('я: ' + $i.val());
                        $i.hide();
                    }
                });
            });
            $(this).hide().after($i, '<br/>', $b);
            event.preventDefault();
        });
        $("button.bgo").click(function(event){
            change_source_status(event);
        });
    });
</script>
{/literal}
<h1>Заливаемые тексты</h1>
{if $is_admin}
<form action="?act=add" method="post" class="form-inline">Добавать новый: <input type="text" name='url' value='http://' size='50' maxlength='255'/> название (опц.): <input type="text" name='title' value=''> <select name='parent'><option value='0'>N/A</option></select> <button class="btn" type="submit">Добавить</button></form>
<br/>
{/if}
<ul class="nav nav-tabs">
    <li{if $what == ''} class="active"{/if}><a href="?">Обычный режим</a></li>
    <li{if $what == 'my'} class="active"{/if}><a href="?what=my">Мои</a></li>
    <li{if $what == 'active'} class="active"{/if}><a href="?what=active">Начатые</a></li>
    <li class="dropdown{if $what == 'free'} active{/if}">
        <a href="?what=free&amp;src=10881" class="dropdown-toggle" data-toggle="dropdown" data-target="#">Свободные <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <!--
            <li><a href="?what=free&amp;src=1">ЧасКор (статьи до 2013)</a></li>
            <li><a href="?what=free&amp;src=20079">ЧасКор (статьи 2013)</a></li>
            <li><a href="?what=free&amp;src=20080">ЧасКор (статьи 2014)</a></li>
            <li><a href="?what=free&amp;src=8283">Викиновости</a></li>
            <li><a href="?what=free&amp;src=10881">ЧасКор (новости)</a></li>-->
            <li><a href="?what=free&amp;src=24432">Часкор (Диалог 2016)</a></li>
            <!--<li><a href="?what=free&amp;src=17674">Блоги</a></li>-->
        </ul>
    </li>
</ul>
<table class="table">
<tr class="borderless">
    <th>Источник</th>
    <th>Отв.</th>
    <th>Провязка</th>
    <th>Комментарии</th>
    <th>&nbsp;</th>
</tr>
{foreach from=$sources.src item=s}
<tr{if $s.user_id} {if $s.status}class='success'{else}class='bgyellow'{/if}{/if}>
    <td><a href="{$s.url|replace:'?':'%3F'}">{if $s.title}{$s.title}{else}{$s.url|truncate:50}{/if}</a></td>
    <td>
        {if $s.user_id}
            {if $s.user_id == $smarty.session.user_id}
            я <a href="#" class="pseudo ya" data-srcid="{$s.id}" data-status="0">не хочу</a>
            {else}
            {$s.user_name}
            {/if}
        {else}
            <a href="#" class="pseudo ya" data-srcid="{$s.id}" data-status="1">я хочу</a>
        {/if}
    </td>
    <td>
        {if $s.book_id}
        <a href="/books.php?book_id={$s.book_id}" class="small">{$s.book_title|htmlspecialchars}</a>
        {elseif !$s.user_id || $s.user_id == $smarty.session.user_id}
        <a href="#" class="pseudo ab" data-srcid="{$s.id}">добавить</a>
        {else}
        &nbsp;
        {/if}
    </td>
    <td class='small'>
        {foreach item=comment from=$s.comments}
        {$comment.username}: {$comment.text|htmlspecialchars}<br/>
        {/foreach}
        <a href="#" data-srcid="{$s.id}" class="ac small pseudo">добавить</a>
    </td>
    <td>
        {if $s.user_id && $s.user_id == $smarty.session.user_id}
        {if !$s.status}
        <button class="bgo btn" data-srcid="{$s.id}" data-status="1">Готово</button>
        {else}
        <button class="bgo btn" data-srcid="{$s.id}" data-status="0">Не готово</button>
        {/if}
        {elseif $s.status}
        <span class='small'>{$s.status_ts|date_format:"%d.%m.%Y, %H:%M"}</span>
        {else}
        &nbsp;
        {/if}
    </td>
</tr>
{/foreach}
<tr>
    <td colspan="2">{if $skip > 0}<a href='?what={$what}&amp;skip={$skip - 200}'>&lt; сюда</a>{else}&nbsp;{/if}</td>
    <td>Всего: {$sources.total}</td>
    <td colspan="2" align="right">{if $sources.total > ($skip + 200)}<a href='?what={$what}&amp;skip={$skip + 200}{if isset($smarty.get.src)}&amp;src={$smarty.get.src}{/if}'>туда &gt;</a>{else}&nbsp;{/if}</td>
</tr>
</table>
{/block}
