{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h3 class="clearfix">Разметка именованных сущностей
<div class="btn-group pull-right">
  <a href="?act=manual&id={$current_guideline}" class="btn btn-primary" target="_blank">
    <i class="icon-info-sign icon-white"></i> Инструкция {$possible_guidelines[$current_guideline]}</a>
  <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    {foreach $possible_guidelines as $id => $name }
        <li {if $id == $current_guideline }class="active"{/if}>
            <a class="guideline-switch" data-guideline-id="{$id}">{$name}</a>
        </li>
    {/foreach}
  </ul>
</div>
</h3>
<table class='table'>
<tr class='small'>
    <th></th>
    <th></th>
    <th>Абзацев</th>
    <th>Готовность</th>
    <th></th>
</tr>
{foreach from=$page item=book}
<tr class="{if $book.started and $book.available}warning
           {elseif $book.started and !$book.available}success
           {elseif !$book.started and !$book.available}error
           {else}{/if}">
    <td><a href="/books.php?book_id={$book.id}">{$book.id}</a></td>
    <td>{$book.name|htmlspecialchars}</td>
    <td>{$book.num_par}</td>
    <td>{(100 * $book.ready_annot / ($book.num_par * $smarty.const.NE_ANNOTATORS_PER_TEXT))|string_format:"%d"} %</td>
    <td>
        {if $book.started and $book.available}
            <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small btn-primary">Продолжить</a>
        {elseif $book.available and !$book.started}
            <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small">Размечать</a>
        {elseif !$book.available and !$book.started}
            <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small" disabled>Размечать</a>
        {else}
            <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small"><i class="icon-ok"></i> Просмотреть</a>
        {/if}
    </td>
</tr>
{/foreach}
</table>
{/block}

{block name="javascripts"}
<script>
{literal}
$(document).ready(function() {
    $(".guideline-switch").click(function() {
        var id = $(this).attr("data-guideline-id");
        $.post('/ajax/set_option.php', {option: 6, value: id});
        document.location.reload();
    });
});
</script>
{/literal}
{/block}
