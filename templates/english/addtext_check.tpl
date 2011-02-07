{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='russian/header.tpl'}
<div id='content'>
    <form action="?" method="post" class="inline">
        <textarea style="display: none" name="txt">{$check.full|htmlspecialchars}</textarea>
        <a href="#" onClick="document.forms[0].submit()">Обратно к форме</a>
    </form>
    <ol type="I">
    {foreach item=paragraph from=$check.paragraphs}
        <li><ol>
        {foreach item=sentence from=$paragraph.sentences}
            <li>
            {foreach item=token from=$sentence.tokens}
                {if $token.class == -1}
                    <span class='check_unpos'>{$token.text}</span>
                {elseif $token.class == 0}
                    <span class='check_noword'>{$token.text}</span>
                {else}
                    {$token.text}
                {/if}
            {/foreach}
            </li>
        {/foreach}
        </ol></li>
    {/foreach}
    </ol>
    <form action="?act=add" method="post">
        Добавляем в
        <select id="book0" name="book[]" onChange="changeSelectBook(0)">
            <option value="0">-- Не выбрано --</option>
            {$check.select}
        </select>
        <select id="book1" name="book[]" disabled="disabled" onChange="changeSelectBook(1)">
            <option value="0">-- Не выбрано --</option>
        </select>
        <br/>
        <p id="lastpar_info">Надо выбрать книгу.</p>
        <textarea style="display: none" name="txt">{$check.full|htmlspecialchars}</textarea>
        Счёт абзацев &ndash; с
        <input id="newpar" name="newpar" size="3" maxlength="3" value="1"/>
        <input id="submitter" type="submit" value="Добавить" disabled="disabled"/>
    </form>
</div>
<div id='rightcol'>
{include file='russian/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
