{* Smarty *}
{extends file='common.tpl'}

{block name=content}
    <script type="text/javascript">
        $(document).ready(function(){
            $("#select_book_form").find('select').change(function(event){
                var n = parseInt($(event.target).closest('select').data('selectid'))
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
            <textarea style="width: 95%" rows="3" name="sentence[]" class="hidden-block" id="p{$smarty.foreach.par.index}s{$smarty.foreach.s.index}">
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
        Добавляем в
        <select id="book0" name="book[]" data-selectid="0">
            <option value="0">-- Не выбрано --</option>
            {html_options options=$check.select0 selected=$check.selected0}
        </select>
        <select id="book1" name="book[]"{if !isset($check.select1)} disabled="disabled"{/if} data-selectid="1">
            <option value="0">-- Не выбрано --</option>
            {if isset($check.select1)}
            {html_options options=$check.select1 selected=$check.selected1}
            {/if}
        </select>
        <br/>
        <p id="lastpar_info">Надо выбрать книгу.</p>
        Счёт абзацев &ndash; с
        <input id="newpar" name="newpar" size="3" maxlength="3" value="1"/>
        <input id="submitter" type="button" value="Добавить" disabled="disabled" onclick="if (check_for_whitespace()) submit_with_readonly_check($(this).closest('form'))"/>
    </form>
{/block}
