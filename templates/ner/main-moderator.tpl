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
    Вы состоите в группе модераторов NE. Вам виден список готовых текстов,
    <b>которые вы еще не размечали</b>.
  </div>
</div>

<table class='table'>
<tr class='small'>
    <th>#</th>
    <th>Абзацев</th>
    <th>Готовность</th>
    <th>Кол-во объектов</th>
    <th>Всего готово: {$page.ready}</th>
</tr>
{foreach from=$page.books item=book}
{if $book.all_ready &&
  ((!$book.moderator_id && !$book.started) ||
  $book.moderator_id == $smarty.session.user_id)}

  {$book_was_moderated = moderated_book_is_finished($book.id, $current_guideline)}

  {if $book_was_moderated}
  <tr class="book-was-moderated">
    <td>{$book.queue_num}</td>
    <td>{$book.num_par}</td>
    <td>{if $book.num_par}{(100 * $book.ready_annot / ($book.num_par * $book.required_annots))|string_format:"%d"} %{else}EMPTY{/if}
    </td>
    <td>{$book.objects_count}</td>
    <td><a class="btn btn-small btn-warning resume-moderation" data-book-id="{$book.id}" data-tagset-id="{$current_guideline}" href="#">Возобновить модерацию</a></td>
  </tr>
  {else}
  <tr>
      <td>{$book.queue_num}</td>
      <td>{$book.num_par}</td>
      <td>{if $book.num_par}{(100 * $book.ready_annot / ($book.num_par * $book.required_annots))|string_format:"%d"} %{else}EMPTY{/if}
      </td>
      <td>{$book.objects_count}</td>
      <td>
          {if !$book.moderator_id && !$book.started}
              <button class="btn btn-small become-moderator" data-tagset-id="{$current_guideline}"
              data-book-id="{$book.id}">Стать модератором</button>
          {elseif $book.moderator_id == $smarty.session.user_id}
              <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small  btn-primary">Модерировать</a>
              <a class="btn btn-small btn-inverse finish-moderation" data-book-id="{$book.id}" data-tagset-id="{$current_guideline}" href="#">Завершить модерацию</a>
          {/if}
      </td>
  </tr>
  {/if}
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
    });

    $(".finish-moderation").click(function() {
      var tagset_id = $(this).attr("data-tagset-id");
      var book_id = $(this).attr("data-book-id");

      $.post('/ajax/ner.php', {
        act: "finishModeration",
        tagset_id: tagset_id,
        book_id: book_id
      }, function() {
       document.location.reload();
      });
    });

    $(".resume-moderation").click(function() {
      var tagset_id = $(this).attr("data-tagset-id");
      var book_id = $(this).attr("data-book-id");

      if (!window.confirm("При возобновлении модераторская разметка этой книги " +
        "полностью переписывается на вас. Вы уверены?")) return false;

      $.post('/ajax/ner.php', {
        act: "resumeModeration",
        tagset_id: tagset_id,
        book_id: book_id
      }, function() {
        document.location.reload();
      });
    });

});
</script>
{/literal}
{/block}
