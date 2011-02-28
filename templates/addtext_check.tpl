{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <form action="?act=add" method="post">
    <input type='hidden' name='source_text' value='{$check.full|htmlspecialchars}'/>
    <ol type="I">
    {foreach item=paragraph from=$check.paragraphs}
        <li><ol>
        {foreach item=sentence from=$paragraph.sentences}
            <li>
            {$sentence.src|htmlspecialchars}<br/>
            {strip}
            <textarea cols="70" rows="3" name="sentence[]">
            {foreach item=token from=$sentence.tokens}
            {$token.text}
            ^^
            {/foreach}
            </textarea><br/>
            {/strip}
            В словаре нет: 
            {foreach item=token from=$sentence.tokens}
            {if $token.class == 0}{$token.text} {/if}
            {/foreach}
            </li>
        {/foreach}
        </ol></li>
    {/foreach}
    </ol>
        {t}Добавляем в{/t}
        <select id="book0" name="book[]" onChange="changeSelectBook(0)">
            <option value="0">-- {t}Не выбрано{/t} --</option>
            {$check.select}
        </select>
        <select id="book1" name="book[]" disabled="disabled" onChange="changeSelectBook(1)">
            <option value="0">-- Не выбрано --</option>
        </select>
        <br/>
        <p id="lastpar_info">{t}Надо выбрать книгу.{/t}</p>
        <textarea style="display: none" name="txt">{$check.full|htmlspecialchars}</textarea>
        {t}Счёт абзацев &ndash; с{/t}
        <input id="newpar" name="newpar" size="3" maxlength="3" value="1"/>
        <input id="submitter" type="submit" value="{t}Добавить{/t}" disabled="disabled"/>
    </form>
{/block}
