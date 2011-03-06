{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <form action="?act=add" method="post">
    <input type='hidden' name='source_text' value='{$check.full|htmlspecialchars}'/>
    <ol type="I">
    {foreach item=paragraph from=$check.paragraphs name=par}
        <li><ol>
        {foreach item=sentence from=$paragraph.sentences name=s}
            <li style='line-height: 25px'>
            {strip}
            {foreach item=token from=$sentence.tokens}
            {if $token.class == 0}
                <span class='check_noword'>{$token.text|htmlspecialchars}</span>
            {else}
                {$token.text|htmlspecialchars}
            {/if}
            {if $token.border < 1}
                <span class='doubt_border'> ?&nbsp;</span>
            {else}
                <span class='ok_border'> &nbsp;</span>
            {/if}
            {/foreach}
            <br/>
            <a href="#" onclick="hide(this); show(byid('p{$smarty.foreach.par.index}s{$smarty.foreach.s.index}')); return false" class='toggle'>внести исправления</a>
            <textarea cols="70" rows="3" name="sentence[]" style="display:none" id="p{$smarty.foreach.par.index}s{$smarty.foreach.s.index}">
            {foreach item=token from=$sentence.tokens}
            {$token.text|htmlspecialchars}
            ^^
            {/foreach}
            </textarea><br/>
            {/strip}
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
        {t}Счёт абзацев &ndash; с{/t}
        <input id="newpar" name="newpar" size="3" maxlength="3" value="1"/>
        <input id="submitter" type="submit" value="{t}Добавить{/t}" disabled="disabled"/>
    </form>
{/block}
