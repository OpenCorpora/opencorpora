{* Smarty *}
<html>
<head>
<meta http-equiv='content' content='text/html;charset=utf-8'/>
<link rel='stylesheet' type='text/css' href='{$web_prefix}/css/main.css'/>
</head>
<body>
{include file='header.tpl'}
<div id='content'>
    <h2>{$book.title}</h2>
    <form action='?act=rename' method='post' class='inline'>Переименовать в:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <input name='new_name' value='{$book.title|htmlspecialchars}'/>&nbsp;&nbsp;
        <input type='submit' value='Переименовать'/>
    </form>
    ИЛИ
    <form action='?act=move' method='post' class='inline'>Переместить в:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <select name='book_to' onChange='document.forms[1].submit()'>
            <option value='0'>&lt;root&gt;</option>
            {$book.select}
        </select>
    </form>
    {* Tag list *}
    <h3>Теги</h3>
    {if isset($book.tags[0])}
        <ul>
        {foreach item=tag from=$book.tags}
            {strip}
            <li>
                [<a href="?act=del_tag&book_id={$book.id}&tag_name={$tag.prefix|cat:":"|cat:$tag.body|urlencode}" onClick="return confirm('Точно удалить этот тег?')">x</a>]&nbsp;
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
        <p>Тегов нет.</p>
    {/if}
    <form action='?act=add_tag' method='post' class='inline'>Добавить тег:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <input name='tag_name' value='New_tag'/>&nbsp;&nbsp;
        <input type='submit' value='Добавить'/>
    </form>
    {* Sub-books list *}
    <h3>Разделы</h3>
    {if isset($book.children[0])}
        <ul>
        {foreach item=book from=$book.children}
            <li><a href="?book_id={$book.id}">{$book.title|htmlspecialchars}</a></li>
        {/foreach}
        </ul>
    {else}
        <p>Разделов нет.</p>
    {/if}
    {* Sentence list *}
    {if count($book.paragraphs) > 0}
        <h3>Предложения по абзацам</h3>
        <p>
        {if isset($smarty.get.ext)}
            <a href="?book_id={$book.id}">к сокращённому виду</a>
        {else}
            <a href="?book_id={$book.id}&ext">к расширенному виду</a>
        {/if}
        </p>
        <ol type="I">
        {foreach key=num item=paragraph from=$book.paragraphs}
            <li value="{$num}">
            {if isset($smarty.get.ext)}
                <ol>
            {/if}
            {foreach name=s item=sentence from=$paragraph}
                {if isset($smarty.get.ext)}
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
            {if isset($smarty.get.ext)}
                </ol>
            {/if}
            </li>
        {/foreach}
        </ol>
    {else}
        <p>В тексте нет ни одного предложения.</p>
    {/if}
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
</body>
</html>


