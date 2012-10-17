{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <h3>Добавляем текст</h3>
    <form action="?act=check" method="post">
    {if isset($txt)}
            <textarea class="span6" rows="20" name="txt">{$txt}</textarea>
    {else}
            <textarea class="span7" rows="20" name="txt" onClick="this.innerHTML=''; this.setAttribute('onClick','')">Товарищ, помни! Абзацы разделяются двойным переводом строки, предложения &ndash; одинарным.</textarea>
    {/if}
    {if isset($smarty.get.to)}
        <input type='hidden' name='book_id' value='{$smarty.get.to}'/>
    {/if}
    <br/><br/>
    <button type="button" class="btn" onclick="submit_with_readonly_check($(this).closest('form'))">Проверить</button>
    </form>
{/block}
