{* Smarty *}
{extends file='common.tpl'}

{block name=content}
    <script type="text/javascript">
        $(document).ready(function(){
            $("#select_book_form").find('select').change(function(event){
                var n = parseInt($(event.target).closest('select').attr('rel'))
                changeSelectBook(n)
                })
            {if isset($check.selected1)}changeSelectBook(1);
            {elseif isset($check.selected0)}changeSelectBook(0);
            {/if}
            })
    </script>
    <form action="?act=add" method="post" id="select_book_form">
    <input type='hidden' name='source_text' value="{$check.full|htmlspecialchars}"/>
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
                <span class='doubt_border'> <span title='{$token.border}; {$token.vector}'>&nbsp;</span> </span>
            {else}
                <span class='ok_border'> &nbsp;</span>
            {/if}
            {/foreach}
            <br/>
            <a href="#" onclick="$(this).hide(); $('#p{$smarty.foreach.par.index}s{$smarty.foreach.s.index}').show(); return false" class='toggle'>внести исправления</a>
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
        <select id="book0" name="book[]" rel="0">
            <option value="0">-- {t}Не выбрано{/t} --</option>
            {html_options options=$check.select0 selected=$check.selected0}
        </select>
        <select id="book1" name="book[]"{if !isset($check.select1)} disabled="disabled"{/if} rel="1">
            <option value="0">-- {t}Не выбрано{/t} --</option>
            {if isset($check.select1)}
            {html_options options=$check.select1 selected=$check.selected1}
            {/if}
        </select>
        <br/>
        <p id="lastpar_info">{t}Надо выбрать книгу.{/t}</p>
        {t}Счёт абзацев &ndash; с{/t}
        <input id="newpar" name="newpar" size="3" maxlength="3" value="1"/>
        <input id="submitter" type="button" value="{t}Добавить{/t}" disabled="disabled" onclick="submit_with_readonly_check($(this).closest('form'))"/>
    </form>
{/block}
