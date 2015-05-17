{* Smarty *}
{extends file='common.tpl'}
{block name=content}
    <script src='{$web_prefix}/js/jquery.autocomplete.min.js'></script>
    {literal}
    <script type="text/javascript">
        $(document).ready(function(){
            //highlight the sentence in the anchor
            var h;
            if (h = document.location.hash) {
                if (h.indexOf('sen') == 1) {
                    $('a[name='+h.substring(1)+']').closest('tr').addClass('bgyellow');
                }
            }
            {/literal}{if $user_permission_adder}{literal}

            $("input.tok").click(function(){
                save_check_tokens($(this));
            });
            $(".tok_c").click(function(){
                show_edit_token($(this));
            });
            $('.download_url').one('click',download_url);
            $('#edit_tok').find('button:last').click(function(){
                $(this).attr('disabled', 'disabled');
                merge_tokens();
            });
            $('#edit_tok div.close a').click(function(event){
                $('#edit_tok').hide();
                event.preventDefault();
            });
            $('#wikinews_addtag_link').click(function(event){
                $(this).html('выполняется запрос...');
                get_wikinews_info($(this));
                event.preventDefault();
            });
            $('#chaskor_addtag_link').click(function(event){
                $(this).html('выполняется запрос...');
                get_chaskor_info($(this));
                event.preventDefault();
            });
            $('a.spp').click(function(event){
                return confirm('Разбить абзац после этого предложения?');
            });
            $('a.dls').click(function(event){
                return confirm('Удалить это предложение? Подумайте дважды.');
            });
            $('a.merge-par').click(function(event){
                return confirm('Приклеить абзац к предыдущему?');
            });
        
            $("#tag_name").autocomplete({
                serviceUrl: "ajax/tag_autocomplete.php",
                minChars:2,
                lookupLimit:10
            });
            {/literal}{/if}{literal}
        })
    </script>
    {/literal}
    <h2>{$book.title} (id={$book.id})</h2>
    {if isset($book.parents.0)}
    <ul class="breadcrumb">
    {foreach item=prn from=$book.parents}
        <li><a href="?book_id={$prn.id}">{$prn.title}</a> <span class="divider">/</span></li>
    {/foreach}
    <li>{$book.title}</li>
    </ul>
    {/if}
    {if $user_permission_adder}
    <form action='?act=rename' method='post' class='form-inline'>Переименовать в:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <input type="text" name='new_name' value="{$book.title|htmlspecialchars}" class="span3">
        <button type='submit' class="btn">Переименовать</button>
    </form>
    <form action='?act=move' method='post' class='form-inline'>Переместить в:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <select name='book_to' onChange="$(this).closest('form').submit();">
            <option value='-1'>-- Не выбрано --</option>
            <option value='0'>&lt;root&gt;</option>
            {html_options options=$book.select}
        </select>
    </form>
    {/if}
    <div id="edit_tok"><div class='close'><a href="#">x</a></div><div class='tid'></div>
    {if $user_permission_adder}
    <a href="#" onclick="$(this).parent().find('form:first').toggle(); return false" class="hint">&#8596; токен</a><br/>
    <form action="?act=split_token" method="post"><button onclick="return confirm('Вы уверены?')">Разбить</button> токен <input name='tid' value='0' type='hidden'/>&laquo;<b></b>&raquo;, отделив <input name="nc" value="1" size="1"/> первых символов</form>
    <a href="#" onclick="$(this).parent().find('form').eq(1).toggle(); return false" class="hint">&#8596; предложение</a>
    <form action="?act=split_sentence" method="post"><button onclick="return confirm('Вы уверены?')">Разбить</button> после этого токена<input name='tid' type='hidden' value='0'/></form>
    {/if}
    <hr/>
    <form><label><input type="checkbox" onclick="check_merge($(this))"/>склеить</label> <button disabled="disabled" type="button">Ok</button></form>
    </div>
    {* Tag list *}
    <h3>Теги</h3>
    {if $user_permission_adder}
    {if $book.is_wikinews}
    <p><span class="hidden-block">{$book.wikinews_title}</span><a href="#" class="pseudo" id="wikinews_addtag_link" data-bookid="{$book.id}">попробовать заполнить автоматически</a></p>
    {elseif $book.is_chaskor_news}
    <div><span class="hidden-block">{$book.chaskor_news_title}</span><a href="#" class="hint" id="chaskor_addtag_link" data-bookid="{$book.id}">попробовать заполнить автоматически</a></div>
    {/if}
    {/if}
    {if isset($book.tags[0])}
        <ul id="book_tags">
        {foreach item=tag from=$book.tags}
            {strip}
            <li>
                {if $user_permission_adder}[<a href="?act=del_tag&amp;book_id={$book.id}&amp;tag_name={$tag.full|urlencode}" onClick="return confirm('Точно удалить этот тег?')">x</a>]&nbsp;{/if}
                {if $tag.prefix == 'url'}
                    url:<a href="{$tag.body}" target="_blank">{$tag.body}</a>
                    {if isset($tag.filename)}
                    , <a class='small' href="{$web_prefix}/files/saved/{$tag.filename}.html">сохранённая копия</a> (<a class='small download_url redo' href="#" data-url='{$tag.body}'>перезакачать</a>)
                    {elseif $user_permission_adder}
                    , <a class='small download_url' href="#" data-url='{$tag.body}'>скачать</a>
                    {/if}
                {else}
                    {$tag.full|htmlspecialchars}
                {/if}
            </li>
            {/strip}
        {/foreach}          
        </ul>
    {else}
        <p>Тегов нет.</p>
    {/if}
    {if $user_permission_adder}
    <form action='?act=add_tag' method='post' class='form-inline'>Добавить тег:
        <input type='hidden' name='book_id' value='{$book.id}'/>
        <input id='tag_name' type="text" name='tag_name' class="span6">  <button type='submit' class="btn">Добавить</button>
    </form>
    {/if}
    {if !isset($book.paragraphs)}
    {* Sub-books list *}
    <h3>Разделы</h3>
    {if $user_permission_adder}
    Добавить раздел
    <form class='inline' action='{$web_prefix}/books.php?act=add' method='post'>
        <input name='book_name' size='30' maxlength='100' value='&lt;Название&gt;'/>
        <input type='hidden' name='book_parent' value='{$book.id}'/>
        <input type='submit' value='Добавить'/>
        <label><input type='checkbox' name='goto' checked='checked'/> и перейти в этот раздел</label>
    </form>
    {/if}
    {if isset($book.children[0])}
        <ul>
        {foreach item=subbook from=$book.children}
            <li><a href="?book_id={$subbook.id}">{$subbook.title|htmlspecialchars}</a></li>
        {/foreach}
        </ul>
    {else}
        <p>Разделов нет.</p>
    {/if}
    {/if}
    {* Sentence list *}
    {if isset($book.paragraphs)}
        <h3>Предложения по абзацам</h3>
        <p>
        {if isset($smarty.get.full)}
            <a href="?book_id={$book.id}">к сокращённому виду</a>
        {else}
            <a href="?book_id={$book.id}&amp;full">к расширенному виду</a>
        {/if}
        </p>
        {if isset($smarty.get.full)}
        <table cellspacing='1' cellpadding='3'>
        {else}
        <ol type="I" style='line-height: 25px'>
        {/if}
        {foreach key=num item=paragraph from=$book.paragraphs}
            {if isset($smarty.get.full)}
                <tr><td>{$num}&nbsp;{if $is_admin}<a href="?act=merge_paragraph&amp;pid={$paragraph.id}" title="Приклеить к предыдущему абзацу" class="merge-par">&uarr;</a>{/if}</td><td></td><td></td><td></td></tr>
            {else}
                <li value="{$num}">
                <ol>
            {/if}
            {foreach name=s item=sentence from=$paragraph.sentences}
                {if isset($smarty.get.full)}
                    {strip}
                    <tr><td></td><td valign='top'><a name="sen{$sentence.id}" href="{$web_prefix}/sentence.php?id={$sentence.id}">{$sentence.id}</a>.</td><td valign="top">
                        {if !$smarty.foreach.s.last && $user_permission_adder}
                            <a href="?act=split_paragraph&amp;sid={$sentence.id}" title="Разбить абзац после этого предложения" class="spp">Р</a>
                        {/if}
                        &nbsp;
                        {if $is_admin}
                            <a href="?act=del_sentence&amp;sid={$sentence.id}&amp;book_id={$book.id}" title="Удалить предложение" class="dls">X</a>
                        {/if}
                        </td><td>
                        {if $user_permission_check_tokens}
                            <span><input type="checkbox" {if $sentence.checked}checked="checked"{/if} class="tok" id="s{$sentence.id}"/></span>&nbsp;
                        {/if}
                        {foreach name=t item=token from=$sentence.tokens}
                            {if $user_permission_adder}
                            <span class="tok_c" id="t{$token.id}">{$token.text|htmlspecialchars}</span>
                            {else}
                            {$token.text|htmlspecialchars}
                            {/if}
                            {if $smarty.foreach.t.last != true}
                            <span class='ok_border'>&nbsp; </span>
                            {/if}
                        {/foreach}
                    </td></tr>
                    {/strip}
                {else}
                    <li value="{$sentence.pos}"><a href="sentence.php?id={$sentence.id}">{$sentence.snippet}</a></li>
                {/if}
            {/foreach}
                </ol>
            {if isset($smarty.get.full)}
            {else}
            </li>
            {/if}
        {/foreach}
        {if isset($smarty.get.full)}
        </table>
        {else}
        </ol>
        {/if}
    {else}
        <p>В тексте нет ни одного предложения.</p>
    {/if}
    {if !isset($book.children[0]) && $user_permission_adder}<p><a href="{$web_prefix}/add.php?to={$book.id}">Добавить текст в эту книгу</a></p>{/if}
{/block}
