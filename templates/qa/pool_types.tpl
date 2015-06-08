{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<form action="?act=save_types" method="post">
<input class="btn btn-success" value="Сохранить" type="submit"/>
<table class="table">
{foreach $data as $id => $t}
    <tr>
        <td>{$id}</td>
        <td>{$t.grammemes}</td>
        <td>
            <label class="radio"><input type="radio" name="complexity[{$id}]" value="0"{if $t.complexity == 0} checked="checked"{/if}/><img src="/assets/img/icon_star_gray.png"/></label>
            <label class="radio"><input type="radio" name="complexity[{$id}]" value="1"{if $t.complexity == 1} checked="checked"{/if}/><img src="/assets/img/icon_star_green.png"/></label>
            <label class="radio"><input type="radio" name="complexity[{$id}]" value="2"{if $t.complexity == 2} checked="checked"{/if}/><img src="/assets/img/icon_star_yellow.png"/></label>
            <label class="radio"><input type="radio" name="complexity[{$id}]" value="3"{if $t.complexity == 3} checked="checked"{/if}/><img src="/assets/img/icon_star_orange.png"/></label>
            <label class="radio"><input type="radio" name="complexity[{$id}]" value="4"{if $t.complexity == 4} checked="checked"{/if}/><img src="/assets/img/icon_star_red.png"/></label>
        </td>
        <td><input value="{$t.doc_link|htmlspecialchars}" class="span4" name="doc[{$id}]"/></td>
    </tr>
{/foreach}
</table>
<input class="btn btn-success" value="Сохранить" type="submit"/>
</form>
{/block}
