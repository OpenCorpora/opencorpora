{* Smarty *}
{extends file="common.tpl"}
{block name=content}
{literal}
<script type="text/javascript">
    $(document).ready(function(){
        $("a.ab").click(function(event){
            var sid = $(this).attr('rel');
            $(this).hide();
            var $a1 = $(document.createElement('a')).html('уже есть').addClass('hint').attr('href', '#').click(function(event){
                $(this).next().hide();
                var $i = $(document.createElement('input')).attr('size', 3).val('id');
                var $b = $(document.createElement('button')).html('Ok').click(function(event){
                    $.get('ajax/bind_book.php', {'sid':sid, 'book_id':$(this).closest('td').find('input').val()}, function(res) {
                        var $r = $(res).find('result');
                        if ($r.attr('ok') == 1) {
                            $i.hide();
                            $b.hide().closest('td').html('<a href="books.php?book_id=' + $r.attr('book_id') + '" class="small">' + $r.attr('title') + '</a>');
                        }
                        else {
                            alert('Bind failed');
                        }
                    });
                });
                $(this).parent().empty().append($i, $b);
                event.preventDefault();
            });
            var $a2 = $(document.createElement('a')).html('создать').addClass('hint').attr('href', '#').click(function(event){
                event.preventDefault();
                var $i = $(document.createElement('input')).attr('size', '20').val('Название');
                var $b = $(document.createElement('button')).html('Ok').click(function(event){
                    $(this).attr('disabled', 'disabled');
                    $.get('ajax/bind_book.php', {'sid':sid, 'book_id':-1, 'book_name':$(this).closest('td').find('input').val()}, function(res) {
                        var $r = $(res).find('result');
                        if ($r.attr('ok') == 1) {
                            $i.hide();
                            $b.hide().closest('td').html('<a href="books.php?book_id=' + $r.attr('book_id') + '" class="small">' + $r.attr('title') + '</a>');
                        }
                        else {
                            alert('Bind failed');
                        }
                    });
                });
                var $cnc = $(document.createElement('a')).html('Отмена').addClass('hint').attr('href', '#').click(function(){
                    event.preventDefault();
                    location.reload();
                });
                $(this).parent().empty().append($i, $b, '&nbsp;', $cnc);
            });
            $(this).after($a1, '&nbsp;или&nbsp;', $a2);
            event.preventDefault();
        });
        $("a.ya").click(function(event){
            $.get('ajax/own_book.php', {'sid':$(this).attr('rel'), 'status':$(this).attr('rev')}, function(res) {
                var $t = $(event.target);
                if ($(res).find('result').attr('ok') == 1) {
                    if ($t.attr('rev') == 1) {
                        $t.attr('rev', '0').html('не хочу');
                        var $b = $(document.createElement('button')).addClass('bgo').attr({'rel':$t.attr('rel'), 'rev':'1'}).html('Готово').click(function(event){change_source_status(event)});
                        $t.closest('tr').addClass('bgyellow').children().last().append($b);
                    } else {
                        $t.attr('rev', '1').html('хочу').closest('tr').removeClass().children().last().empty();
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
            var $b = $(document.createElement('button')).html('Добавить').attr('rel', $(event.target).attr('rel')).click(function(event){
                var $t = $(event.target);
                $t.attr('disabled', 'disabled');
                $.post('ajax/post_comment.php', {'sid':$t.attr('rel'), 'text':$t.closest('td').find('textarea').val()}, function(res) {
                    if ($(res).find('response').attr('ok') == 1) {
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
<form action="?act=add" method="post"><button>Добавить</button> новый: <input name='url' value='http://' size='50' maxlength='255'/> название (опц.): <input name='title' value=''> <select name='parent'><option value='0'>N/A</option></select></form>
<br/>
{/if}
{if $what != ''}
<a href="?">обычный режим</a> |
{/if}
{if $what != 'my'}
<a href="?what=my">показать мои</a> | 
{/if}
{if $what != 'active'}
<a href="?what=active">показать начатые</a> |
{/if}
{if $what != 'free'}
<a href="?what=free&amp;src=10881">показать свободные</a> |
{else}
<br/>Свободные:
<a href="?what=free&amp;src=1">ЧасКор (статьи)</a> |
<a href="?what=free&amp;src=8283">Викиновости</a> |
<a href="?what=free&amp;src=10881">ЧасКор (новости)</a> |
<a href="?what=free&amp;src=17674">Блоги</a> |
{/if}
<br/><br/><table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>Источник</th>
    <th>Отв.</th>
    <th>Провязка</th>
    <th>Комментарии</th>
    <th>&nbsp;</th>
</tr>
{foreach from=$sources.src item=s}
<tr{if $s.user_id} {if $s.status}class='bggreen'{else}class='bgyellow'{/if}{/if}>
    <td><a href="{$s.url|replace:'?':'%3F'}">{if $s.title}{$s.title}{else}{$s.url|truncate:50}{/if}</a></td>
    <td>
        {if $s.user_id}
            {if $s.user_id == $smarty.session.user_id}
            я <a href="#" class="hint ya" rel="{$s.id}" rev="0">не хочу</a>
            {else}
            {$s.user_name}
            {/if}
        {else}
            <a href="#" class="hint ya" rel="{$s.id}" rev="1">я хочу</a>
        {/if}
    </td>
    <td>
        {if $s.book_id}
        <a href="{$web_prefix}/books.php?book_id={$s.book_id}" class="small">{$s.book_title|htmlspecialchars}</a>
        {elseif !$s.user_id || $s.user_id == $smarty.session.user_id}
        <a href="#" class="hint ab" rel="{$s.id}">добавить</a>
        {else}
        &nbsp;
        {/if}
    </td>
    <td class='small'>
        {foreach item=comment from=$s.comments}
        {$comment.username}: {$comment.text|htmlspecialchars}<br/>
        {/foreach}
        <a href="#" rel="{$s.id}" class="ac small hint">добавить</a>
    </td>
    <td>
        {if $s.user_id && $s.user_id == $smarty.session.user_id}
        {if !$s.status}
        <button class="bgo" rel="{$s.id}" rev="1">Готово</button>
        {else}
        <button class="bgo" rel="{$s.id}" rev="0">Не готово</button>
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
    <td colspan="2">{if $skip > 0}<a href='?what={$what}&amp;skip={$skip - 200}'>&lt; {t}сюда{/t}</a>{else}&nbsp;{/if}</td>
    <td>Всего: {$sources.total}</td>
    <td colspan="2" align="right">{if $sources.total > ($skip + 200)}<a href='?what={$what}&amp;skip={$skip + 200}'>{t}туда{/t} &gt;</a>{else}&nbsp;{/if}</td>
</tr>
</table>
{/block}
