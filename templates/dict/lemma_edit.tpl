{* Smarty *}
{extends file='common.tpl'}

{block name="javascripts"}
{literal}
    <script src="{/literal}{$web_prefix}{literal}/assets/js/bootstrap.select.min.js"></script>
{/literal}
{/block}
{block name=styles}
     <link rel="stylesheet" type="text/css" href="{$web_prefix}/assets/css/bootstrap-select.min.css" />
{/block}

{block name='content'}
    {literal}
    <script type="text/javascript">
        $(document).ready(function() {

            function dict_add_form(i, event) {
                var select = $('.gram-selectpicker.template').clone()
                                .removeClass('template hidden')
                                .attr('name', 'form_gram[' + i + '][]');

                $('#paradigm tbody').append(
                    $("<tr>").addClass("valign-top")
                        .append(
                            $("<td>").append("<input type='text' name='form_text[]'>") )
                        .append(
                            $("<td>").append(select) ));

                select.selectpicker();
                if (event) event.preventDefault();
            }

            // add new form
            $("#add_form_link").bind('click', function(e) {
                var i = $('#paradigm tbody select').length; // indexed from zero
                return dict_add_form(i, e);
            });

            // copy paradigm
            $("#copy_para").click(function(event) {
                $(event.target).attr('disabled', 'disabled');
                $.post('ajax/paradigm_info.php', {'word': $("#source").val()}, function(res) {
                    var $lemma = $("#lemma_txt");
                    if (!res.error) {
                        var pseudo_stem = $lemma.val().substr(0, $lemma.val().length - res.lemma.suffix);
                        $("#lemma_gr").selectpicker('val', res.lemma.gram);
                        for (var i = 0; i < res.forms.length; ++i) {
                            dict_add_form(i);
                            var $tr = $("#paradigm tr").last();
                            var form_text = pseudo_stem + res.forms[i].suffix;
                            $tr.find('td').first().find('input').val(form_text);
                            $tr.find('td').last().find('select').selectpicker('val', res.forms[i].gram.split(/[\s,]+/));
                        }
                    }
                    $(event.target).removeAttr('disabled');
                });
            });

            $.fn.selectpicker.defaults = {
              noneSelectedText: '',
              noneResultsText: 'Не найдено совпадений',
              countSelectedText: 'Выбрано {0} из {1}',
              maxOptionsText: ['Достигнут предел ({n} {var} максимум)', 'Достигнут предел в группе ({n} {var} максимум)', ['items', 'item']],
              multipleSeparator: ', '
            };

            $(".gram-selectpicker:not(.template)").selectpicker();
        })
    </script>
    {/literal}
    {if isset($smarty.get.saved)}
        <div class='info'>Изменения сохранены. <a href="?act=edit&amp;id=-1">Добавить другую лемму</a></div>
    {/if}
    {if $editor.deleted}
    <h1 class="bgpink">Лемма &laquo;{$editor.lemma.text|htmlspecialchars}&raquo; удалена</h1>
    {else}
    <h1>Лемма &laquo;{$editor.lemma.text|htmlspecialchars}&raquo;</h1>
    {/if}
    <ul class="breadcrumb">
        <li><a href="{$web_prefix}/dict.php">Словарь</a> <span class="divider">/</span></li>
        <li><a href="{$web_prefix}/dict.php">Поиск</a> <span class="divider">/</span></li>
    </ul>
    <div id="errata">
    {foreach from=$editor.errata item=error}
        <div class="{if $error.is_ok}ok{else}error{/if}">Ошибка.
        {if $error.type == 1}
            Несовместимые граммемы:
        {elseif $error.type == 2}
            Неизвестная граммема:
        {elseif $error.type == 3}
            Формы-дубликаты:
        {elseif $error.type == 4}
            Нет обязательной граммемы:
        {elseif $error.type == 5}
            Не разрешённая граммема:
        {/if}
        {$error.descr}.
        {if $user_permission_dict}
        {if $error.is_ok}
        (<span class='hint' title='{$error.author_name}, {$error.exc_time|date_format:"%d.%m.%y, %H:%M"}, "{$error.comment|htmlspecialchars}"'>Эта ошибка была помечена как исключение</span>.)
        {else}
        <form action="?act=not_error&amp;error_id={$error.id}" method="post" class="inline"><button type='button' onclick='dict_add_exc_prepare($(this))'>Это не ошибка</button></form>
        {/if}
        {/if}
        </div>
    {/foreach}
    </div>
    {strip}
    <form action="?act=save" method="post">
        <h4>Лемма</h4>
        <div class="form-inline">
        <input type="hidden" name="lemma_id" value="{$editor.lemma.id}"/>
        {if $editor.lemma.id > 0}
            <p>
                <input type="text" name="lemma_text" value="{$editor.lemma.text|htmlspecialchars}">
                <select name="lemma_gram[]" id="lemma_gr" class="gram-selectpicker"
                        data-live-search="true" title="граммемы" multiple {if !$user_permission_dict}disabled{/if}>
                    {foreach $possible_grammems as $name}
                        <option value="{$name}" {if in_array($name, $editor.lemma.grms_raw)}selected{/if}>{$name}</option>
                    {/foreach}
                </select>

                <button class="btn" type="button" onClick="location.href='dict_history.php?lemma_id={$editor.lemma.id}'" >История</button>

                {if $user_permission_dict && !$editor.deleted}<button type="button" class="btn" onClick="if (confirm('Вы уверены?')) location.href='dict.php?act=del_lemma&lemma_id={$editor.lemma.id}'" >Удалить лемму</button>{/if}
            </p>

        {else}
            <p>
                <input type="text" name="lemma_text" id="lemma_txt" value="{$smarty.get.text}"/>
                <select name="lemma_gram[]" id="lemma_gr" class="gram-selectpicker"
                        data-live-search="true" title="граммемы" multiple>
                    {foreach $possible_grammems as $id => $name}
                        <option value="{$name}">{$name}</option>
                    {/foreach}
                </select>

            </p>
            <p>
                <input id='copy_para' class='btn' type='button' value='Заполнить'/> по аналогии с леммой <input type='text' id='source' class='span2' placeholder='примус'/>
            </p>
        {/if}
        </div>
        <h4>Формы {if $user_permission_dict} <small>(оставление левого поля пустым удаляет форму)</small>{/if}</h4>
        <table id="paradigm" cellpadding="3">
            <tbody>
            {foreach item=form from=$editor.forms name=forms}
                <tr class="valign-top">
                    <td><input type='text' name='form_text[]' {if !$user_permission_dict}readonly="readonly"{/if} value="{$form.text|htmlspecialchars}"/></td>

                    <td>
                     <select name="form_gram[{$smarty.foreach.forms.index}][]" class="gram-selectpicker"
                             data-live-search="true" title="граммемы" multiple {if !$user_permission_dict}disabled{/if}>
                         {foreach $possible_grammems as $name}
                             <option value="{$name}" {if in_array($name, $form.grms_raw)}selected{/if}>{$name}</option>
                         {/foreach}
                     </select>
                    </td>
                </tr>
            {/foreach}
            {if $user_permission_dict && !$editor.deleted}
                <tr><td>&nbsp;<td><a id="add_form_link" class="pseudo" href="#">Добавить ещё одну форму</a></tr>
            {/if}
            </tbody>
        </table><br/>
        {if $user_permission_dict && !$editor.deleted}
            Комментарий к правке:<br/>
            <input name='comment' type='text' size='60'/><br/>
            <input type="button" class='btn btn-primary' onclick="submit_with_readonly_check($(this).closest('form'))" value="Сохранить"/>&nbsp;&nbsp;
            <input type="reset" class='btn' value="Сделать как было"/>
        {/if}
    </form>
    {/strip}
    <h4>Связи</h4>
    {if $user_permission_dict && !$editor.deleted}
    <p><a href="#" class="pseudo" onclick="$('#add_link').show(); return false">Добавить связь</a></p>
    <form id="add_link" method='post' class='hidden-block' action='?act=add_link'>
        <input type='hidden' name='from_id' value='{$editor.lemma.id}'/>
        <select name='link_type'>
            <option value='0' selected='selected'>--Тип связи--</option>
            {html_options options=$link_types}
        </select>
        с леммой
        <input id="find_lemma"/> <input type='button' value='Найти' onclick='get_lemma_search()'/>
    </form>
    {/if}
    <ul>
    {foreach item=link from=$editor.links}
    <li>
        {if $link.is_target}
            <a href="?act=edit&amp;id={$link.lemma_id}">{$link.lemma_text}</a> -&gt; {$editor.lemma.text} ({$link.name})
        {else}
            {$editor.lemma.text} -&gt; <a href="?act=edit&amp;id={$link.lemma_id}">{$link.lemma_text}</a> ({$link.name})
        {/if}
        {if $user_permission_dict}
        [<a href="?act=change_link_dir&amp;id={$link.id}&amp;lemma_id={$editor.lemma.id}" title="Изменить направление" onclick="return confirm('Изменить направление связи?')">&#8644;</a>]
        [<a href="?act=del_link&amp;id={$link.id}&amp;lemma_id={$editor.lemma.id}" onclick="return confirm('Вы уверены?')">удалить</a>]
        {/if}
    </li>
    {foreachelse}
    <p>Нет ни одной связи.</p>
    {/foreach}
    </ul>

    <select name="form_gram[]" class="gram-selectpicker hidden template"
        data-live-search="true" title="граммемы" multiple>
    {foreach $possible_grammems as $id => $name}
        <option value="{$name}">{$name}</option>
    {/foreach}
    </select>
{/block}
