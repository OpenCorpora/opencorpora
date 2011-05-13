{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    {if $user_permission_adder}
    <script type="text/javascript">
        $(document).ready(function(){
            $("input.tok").click(function(){
                save_check_tokens($(this));
            })
        })
    </script>
    {/if}
    <h2>{$book.title}</h2>
    {if isset($book.parents)}
    <p>
    {foreach item=prn from=$book.parents}
    <a href="?book_id={$prn.id}">{$prn.title}</a> ::
    {/foreach}
    {$book.title}
    </p>
    {/if}
    {if $user_permission_adder}
    <form action='?act=rename' method='post' class='inline'>{t}Переименовать в{/t}:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <input name='new_name' value='{$book.title|htmlspecialchars}'/>&nbsp;&nbsp;
        <input type='submit' value='{t}Переименовать{/t}'/>
    </form>
    {t}ИЛИ{/t}
    <form action='?act=move' method='post' class='inline'>{t}Переместить в{/t}:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <select name='book_to' onChange='document.forms[1].submit()'>
            <option value='-1'>-- {t}Не выбрано{/t} --</option>
            <option value='0'>&lt;root&gt;</option>
            {html_options options=$book.select}
        </select>
    </form>
    {/if}
    {* Tag list *}
    <h3>{t}Теги{/t}</h3>
    {if isset($book.tags[0])}
        <ul>
        {foreach item=tag from=$book.tags}
            {strip}
            <li>
                {if $user_permission_adder}[<a href="?act=del_tag&amp;book_id={$book.id}&amp;tag_name={$tag.prefix|cat:":"|cat:$tag.body|urlencode}" onClick="return confirm('{t}Точно удалить этот тег?{/t}')">x</a>]&nbsp;{/if}
                {if $tag.prefix == 'url'}
                    url:<a href="{$tag.body}" target="_blank">{$tag.body}</a>
                {else}
                    {$tag.prefix|cat:":"|cat:$tag.body|htmlspecialchars}
                {/if}
            </li>
            {/strip}
        {/foreach}          
        </ul>
    {else}
        <p>{t}Тегов нет.{/t}</p>
    {/if}
    {if $user_permission_adder}
    <form action='?act=add_tag' method='post' class='inline'>{t}Добавить тег{/t}:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <input name='tag_name' value='New_tag'/>&nbsp;&nbsp;
        <input type='submit' value='{t}Добавить{/t}'/>
    </form>
    {/if}
    {* Sub-books list *}
    <h3>{t}Разделы{/t}</h3>
    {if $user_permission_adder}
    Добавить раздел <form class='inline' action='{$web_prefix}/books.php?act=add' method='post'><input name='book_name' size='30' maxlength='100' value='&lt;{t}Название{/t}&gt;'/><input type='hidden' name='book_parent' value='{$book.id}'/><input type='submit' value='{t}Добавить{/t}'/></form>
    {/if}
    {if isset($book.children[0])}
        <ul>
        {foreach item=subbook from=$book.children}
            <li><a href="?book_id={$subbook.id}">{$subbook.title|htmlspecialchars}</a></li>
        {/foreach}
        </ul>
    {else}
        <p>{t}Разделов нет.{/t}</p>
    {/if}
    {* Sentence list *}
    {if count($book.paragraphs) > 0}
        <h3>{t}Предложения по абзацам{/t}</h3>
        <p>
        {if isset($smarty.get.ext)}
            <a href="?book_id={$book.id}">{t}к сокращённому виду{/t}</a>
        {else}
            <a href="?book_id={$book.id}&amp;ext">{t}к расширенному виду{/t}</a>
        {/if}
        {if !isset($smarty.get.full)}
            | <a href="?book_id={$book.id}&amp;full">{t}смотреть целиком{/t}</a>
        {/if}
        </p>
        <ol type="I" style='line-height: 25px'>
        {foreach key=num item=paragraph from=$book.paragraphs}
            <li value="{$num}">
            {if isset($smarty.get.ext) || isset($smarty.get.full)}
                <ol>
            {/if}
            {foreach name=s item=sentence from=$paragraph}
                {if isset($smarty.get.full)}
                    {strip}
                    <li value="{$sentence.pos}">
                        {if $user_permission_check_tokens}
                            <span><input type="checkbox" {if $sentence.checked}checked="checked"{/if} class="tok" id="s{$sentence.id}"/></span>&nbsp;
                        {/if}
                        {$sentence.text|htmlspecialchars}   
                        {foreach name=t item=token from=$sentence.tokens}
                            {$token|htmlspecialchars}
                            {if $smarty.foreach.t.last != true}
                            <span class='ok_border'>&nbsp; </span>
                            {/if}
                        {/foreach}
                    </li>
                    {/strip}
                {elseif isset($smarty.get.ext)}
                    <li value="{$sentence.pos}"><a href="sentence.php?id={$sentence.id}">{$sentence.snippet}</a></li>
                {else}
                    {strip}
                    <a href="sentence.php?id={$sentence.id}">{$sentence.id}</a>
                    {if $smarty.foreach.s.last != true}
                    , 
                    {/if}
                    {/strip}
                {/if}
            {/foreach}
            {if isset($smarty.get.ext) || isset($smarty.get.full)}
                </ol>
            {/if}
            </li>
        {/foreach}
        </ol>
    {else}
        <p>{t}В тексте нет ни одного предложения.{/t}</p>
    {/if}
    <p><a href="{$web_prefix}/add.php?to={$book.id}">Добавить текст в эту книгу</a></p>
{/block}
