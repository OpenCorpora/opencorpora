{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<script type="text/javascript">
{literal}
$(document).ready(function(){
    $("button.btn-small").click(function(event){
        if (!confirm('Точно?'))
            return;

        var action;
        if ($(this).hasClass('upd_token'))
            action = 'update';
        else if ($(this).hasClass('forget_token'))
            action = 'forget';

        var tr = $(this).closest('tr');
        $.get('ajax/dict_pending.php', {'act':action, 'token_id':tr.attr('rel'), 'rev_id':tr.attr('rev')}, function(res) {
            var $r = $(res).find('result');
            if ($r.attr('ok') == 1) {
                $(event.target).closest('tr').css('backgroundColor', '#999').find('button').hide();
            }
        });
    });
    $("input.btn-small").click(function() {
        if (!confirm('Точно?'))
            return;
        $(this).closest('form').submit();
    });
});
{/literal}
</script>
<h1>Влияние словаря на корпус</h1>
<p>Токенов в очереди &mdash; <b>{$data.cnt_tokens}</b> {if $data.cnt_forms}<b>(+ {$data.cnt_forms} форм, т.е. список неполон)</b>{/if}</p>
{if $data.outdated_f2l}<h2>Осторожно, в словаре есть свежие правки, перезаливать не рекомендуется!</h2>{/if}
<p>Показано не более {if isset($smarty.post.count)}{$smarty.post.count}{else}200{/if} токенов в порядке добавления в очередь.</p>
<form class='form-inline' action="?act=pending" method="post"><button class='btn'>Показать</button> <input type='text' name='count' class='span1' value='500'></form>
<table class='table'>
{foreach from=$data.revisions item=revision}
<tr>
    <td>{$revision.id}<br/><form class='form-inline' action="?act=reannot" method="post"><input type='hidden' name='rev_id' value='{$revision.id}'><input type='button' class='btn btn-primary btn-small' value='Обновить все'/></form></td>
    <td><pre>
{foreach from=$revision.diff.diff[0] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td>
    <td><pre>
{foreach from=$revision.diff.diff[1] item=str}
<span class="{if $str[1] == 1}bgpink{elseif $str[1] == 2}bggreen{elseif $str[1] == 3}bgyellow{/if}">{$str[2]|htmlspecialchars}</span>
{/foreach}</pre></td>
</tr>
{foreach from=$revision.tokens item=token}
<tr rel="{$token.id}" rev="{$revision.id}" {if $token.is_unkn}class='bggreen'{elseif !$token.human_edits}class='bgblue'{else}class='bgpink'{/if}><td colspan='3'>
    <a href="sentence.php?id={$token.sentence_id}">{$token.context}</a>
        <div class='pull-right'>
            <button class='btn btn-small btn-success upd_token'>Обновить</button>
            <button class='btn btn-small btn-danger forget_token'>Забыть</button>
        </div>
</td></tr>
{/foreach}
{/foreach}
</table>
{/block}
