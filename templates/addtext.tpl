{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <h3>{t}Добавляем текст{/t}</h3>
    <form action="?act=check" method="post">
    {if isset($txt)}
            <textarea cols="70" rows="20" name="txt">{$txt}</textarea>
    {else}
            <textarea cols="70" rows="20" name="txt" onClick="this.innerHTML=''; this.onClick=''">Товарищ, помни! Абзацы разделяются двойным переводом строки, предложения &ndash; одинарным; предложение должно быть токенизировано.</textarea>
    {/if}
    <br/><br/>
    <input type="submit" value="{t}Проверить{/t}"/>
    </form>
{/block}
