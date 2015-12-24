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
    {foreach $possible_guidelines as $id => $name}
        <li {if $id == $current_guideline}class="active"{/if}>
            <a class="guideline-switch" data-guideline-id="{$id}">{$name}</a>
        </li>
    {/foreach}
  </ul>
</div>
</h3>

<div class='alert alert-info'>
  <div class="container">
    Вы состоите в группе модераторов NE. Вам виден список готовых текстов.
  </div>
</div>

<table class='table'>
<tr class='small'>
    <th>#</th>
    <th>Абзацев</th>
    <th>Uid</th>
    <th>Готовность</th>
    <th>Всего готово: {$page.ready}</th>
</tr>
{foreach from=$page.books item=book}
{if $book.all_ready && (!$book.moderator_id || $book.moderator_id == $smarty.session.user_id)}
<tr>
    <td>{$book.queue_num}</td>
    <td>{$book.num_par}</td>
    <td>{$book.moderator_id}</td>
    <td>{(100 * $book.ready_annot / ($book.num_par * $smarty.const.NE_ANNOTATORS_PER_TEXT))|string_format:"%d"} %</td>
    <td>
        {if !$book.moderator_id}
            <button class="btn btn-small become-moderator" data-tagset-id="{$current_guideline}"
            data-book-id="{$book.id}">Стать модератором</button>
        {elseif $book.moderator_id == $smarty.session.user_id}
            <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small  btn-primary">Модерировать</a>
        {/if}
    </td>
</tr>
{/if}
{/foreach}
</table>
{/block}

{block name="javascripts"}
<script>
{literal}
$(document).ready(function() {
    $(".guideline-switch").click(function() {
        var id = $(this).attr("data-guideline-id");
        $.post('/ajax/set_option.php', {option: 6, value: id}, function() {
          document.location.reload();
        });
    });

    $(".become-moderator").click(function() {
      var tagset_id = $(this).attr("data-tagset-id");
      var book_id = $(this).attr("data-book-id");
      $.post('/ajax/ner.php', {
        act: "becomeModerator",
        tagset_id: tagset_id,
        book_id: book_id
      }, function() {
        document.location.reload();
      });
    })
});
</script>
{/literal}
{/block}
