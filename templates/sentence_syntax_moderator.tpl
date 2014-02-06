{* Smarty *}
{extends file='sentence_syntax.tpl'}

{block name=syntax_heading}
<h5>Вы являетесь модератором этой книги. Ваши именные группы будут использоваться при разметке анафоры.</h5>
{/block}

{block name=button_caption}<span class="badge badge-inverse">Модератор</span>{/block}
{block name=inject_groups_json}
    <script type="text/javascript" src="{$web_prefix}/js/syntax_groups_moderator.js"></script>
    {literal}
        <script>
        var groups_json = JSON.parse('{/literal}{$all_groups|@json_encode|replace:"\"":"\\\""}{literal}');
        </script>
    {/literal}
{/block}

{block name=syntax_bottom}
<h4>Разметка других пользователей</h4>

{foreach from=$all_groups item=gr key=uid}

    {if $uid == $smarty.session.user_id}{continue}{/if}

<div class="main_annot_syntax row row_other_user">
    <div class="span7">
        <h5>Разметка @{$group_owners[$uid].shown_name}:</h5>
        <div class="tokens" data-sentenceid="{$sentence.id}" data-userid="{$uid}">
            {foreach item=token from=$sentence.tokens}<span data-tid="{$token.tf_id}" class="token">{$token.tf_text|htmlspecialchars}</span>{/foreach}
        </div>
    </div>
    <div class="span4">
        <h5>Выделенные группы <a href="#" class="small toggle show-dummy">показать искусственные</a></h5>
        <div class="table_wrapper" data-sentenceid="{$sentence.id}">
        {include "sentence_syntax_groups_moderator.tpl" groups=$gr group_types=$group_types}
        </div>
    </div>
</div>
{/foreach}
{/block}