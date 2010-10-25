{* Smarty *}
{include file='commonhtmlheader.tpl' title='Разметка предложения'}
<body onload="highlight_source(); document.onkeyup=checkKeyUp; document.onkeydown=checkKeyDown; document.onmouseup=endScroll; prepareScroll();">
<div id='main'>
{include file='header.tpl'}
<div id='content'>
    {strip}
    <div id="source_text"><b>Исходный текст:</b> {$sentence.fulltext}</div>
    <form method="post" action="?id={$sentence.id}&amp;act=save">
        <div id="main_scroller">
            <!--<span id="scr_ll" onMouseDown="startScroll(-50)" onMouseUp="endScroll()" onMouseMove="endScroll()">&lt;&lt;</span>
            <span id="scr_l" onMouseDown="startScroll(-20)" onMouseUp="endScroll()" onMouseMove="endScroll()">&lt;</span>
            <span id="scr_lw" onMouseDown="startScrollByWord(-1)" onMouseUp="endScroll()">&lt;W</span>-->
            <div>
                {if $is_logged == 1}
                    <button type="submit" disabled="disabled" id="submit_button">Сохранить</button>&nbsp;
                {/if}
                <button type="reset" onClick="window.location.reload()">Отменить правки</button>&nbsp;
                <button type="button" onClick="window.location.href='history.php?sent_id={$sentence.id}'">История</button>&nbsp;
                <button type="button" onClick="dict_reload_all()">Разобрать заново</button>
            </div>
        <!--    <span id="scr_rr" onMouseDown="startScroll(50)" onMouseUp="endScroll()" onMouseMove="endScroll()">&gt;&gt;</span>
            <span id="scr_r" onMouseDown="startScroll(20)" onMouseUp="endScroll()" onMouseMove="endScroll()">&gt;</span>
            <span id="scr_rw" onMouseDown="startScrollByWord(1)" onMouseUp="endScroll()">W&gt;</span>-->
        </div>
        <br/><br/>
        <div id="scrollbar"><div style="height:10px;"></div></div>
        <div id="main_annot"><table><tr>
        {foreach item=token from=$sentence.tokens}
            <td id="var_{$token.tf_id}">
                <div class="tf">
                    {$token.tf_text|htmlspecialchars}
                    {if $token.dict_updated == 1}
                        <a href="#" class="reload" title="Разобрать заново из словаря" onClick="dict_reload(this.parentNode.parentNode)">D</a>
                    {/if}
                </div>
                {foreach item=variant from=$token.variants}
                    <div class="var" id="var_{$token.tf_id}_{$variant.num}">
                        <img src="spacer.gif" width="100" height="1"/>
                        <input type="hidden" name="var_flag[{$token.tf_id}][{$variant.num}]" value="1"/>
                        {if $variant.lemma_id > 0}
                            <a href="{$web_prefix}/dict.php?act=edit&amp;id={$variant.lemma_id}">{$variant.lemma_text}</a>
                        {else}
                            <span>{$variant.lemma_text|htmlspecialchars}</span>
                        {/if}
                        <a href="#" class="best_var" onclick="best_var(this.parentNode); return false">v</a>
                        <a href="#" class="del_var" onclick="del_var(this.parentNode); return false">x</a>
                        <br/>
                        {$variant.gram_list}
                    </div>
                {/foreach}
            </td>
        {/foreach}
        </tr></table></div>
    </form>
    {/strip}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
