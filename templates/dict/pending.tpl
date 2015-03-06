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
        $.post('ajax/dict_pending.php', {'act':action, 'token_id':tr.data('tokenid'), 'rev_id':tr.data('revision')}, function(res) {
            if (!res.error) {
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
<h2>Оглавление</h2>
<ol>
{foreach from=$data.header item=i}
    <li><a href="?act=pending&amp;skip={$i.skip}">{$i.revision}</a>: <a href="{$web_prefix}/dict.php?act=edit&id={$i.lemma_id}">{$i.lemma|htmlspecialchars}</a> ({$i.count})</li>
{/foreach}
</ol>
<div class="pagination pagination-centered"><ul>
<li {if $data.pages.active == 0}class="disabled"{/if}><a href="?act=pending&skip={($data.pages.active - 1) * 500}">&lt;</a></li>
{for $i=0 to $data.pages.total - 1}
<li {if $i == $data.pages.active}class="active"{/if}><a href="?act=pending&skip={$i * 500}">{$i+1}</a></li>
{/for}
<li {if $data.pages.active == $data.pages.total - 1}class="disabled"{/if}><a href="?act=pending&skip={($data.pages.active + 1) * 500}">&gt;</a></li>
</ul></div>
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
<tr data-tokenid="{$token.id}" data-revision="{$revision.id}" {if $token.is_unkn}class='bggreen'{elseif !$token.human_edits}class='bgblue'{else}class='bgpink'{/if}><td colspan='3'>
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
