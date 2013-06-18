{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{literal}
<script type="text/javascript">
function check_adjacency($token) {
    var $p = $token.prev();
    if ($p.length && $p.hasClass('bggreen'))
            return true;
    $p = $token.next();
    if ($p.length && $p.hasClass('bggreen'))
        return true;
    return false;
}
function update_selection() {
    var l = $("span.token.bggreen").length + $("span.group.bggreen").length;
    $("#selection_info b").html(l);
    if (l > 1)
        $("#selection_info #new_group").show().find('#add1').hide();
    else {
        $("#selection_info #new_group").hide();
        $("#group_type").hide();
    }
}
function show_new_group() {
    $('span.token.bggreen:first').before('<span class="group" id="last_group"></span>');
    $('span.token.bggreen').appendTo($('span#last_group')).removeClass('token').removeClass('bggreen').unbind('click').not(':first').each(function(i, el) {
        $(el).html(' ' + $(el).html());
    });
    $("span#last_group").attr('id', null);
}
function save_group() {
    alert('Not implemented');
    show_new_group();
}
function clck_handler($target) {
    if (!check_adjacency($target)) {
        $('span.token').removeClass('bggreen');
        $('span.group').removeClass('bggreen');
    }
    $target.addClass('bggreen');
    update_selection();
}
$(document).ready(function(){
    $('#group_type').hide();
    $('#add0').click(function() {
        $('#group_type').show();
        $(this).hide();
        $("#add1").show();
    });
    $('#add1').click(function() {
        save_group();
        $("#group_type").hide();
        $("#add0").show();
        update_selection();
    });
    $('span.token').click(function() {
        clck_handler($(this));
    });
    $('#main_annot_syntax').delegate('span.group', 'click', function() {
        clck_handler($(this));
    });
});
</script>
{/literal}
<div class="btn-group">
    <a href="?id={$sentence.id}&mode=morph" class="btn {if !isset($smarty.get.mode) || $smarty.get.mode == 'morph'}btn-success{/if}">Морфология</a>
    <a href="?id={$sentence.id}&mode=syntax" class="btn {if isset($smarty.get.mode) && $smarty.get.mode == 'syntax'}btn-success{/if}">Синтаксис</a>
</div>
<div id="main_annot_syntax">
    <div id="tokens">
    {foreach item=token from=$sentence.tokens}
        <span data-tid="{$token.tf_id}" class="token">{$token.tf_text|htmlspecialchars}</span>
    {/foreach}
    </div>
    <div id="selection_info"><form class="form-inline">
        Выделено <b>0</b><span id="new_group" style="display: none">, <button type="button" id="add0" class="btn btn-small">Создать группу</button><button type="button" id="add1" class="btn btn-small btn-primary">Создать!</button></span>
    <select id="group_type"><option value="0">Без типа</option>
    {foreach from=$group_types item=group key=gid}<option value="{$key}">{$group|htmlspecialchars}</option>{/foreach}
    </select>
    </form></div>
</div>
{*$sentence|var_dump*}
{/block}
