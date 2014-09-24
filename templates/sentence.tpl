{* Smarty *}
{extends file='common.tpl'}
{block name=body}<body onload="highlight_source(); document.onkeyup=checkKeyUp; document.onkeydown=checkKeyDown; document.onmouseup=endScroll; prepareScroll();">{/block}
{block name=content}
    <script type="text/javascript">

        function show_comments(event, need_scroll) {
            $(".oc_tabs").hide();
            $("#comments").show();
            $("#a_parse").show();
            if ($(".comment_main").length == 0) {
                load_sentence_comments({$sentence.id}, {$is_logged}, need_scroll);
            }
            if (!need_scroll)
                location.hash = '#comments';
            if (event)
                event.preventDefault();
        }

        $(document).ready(function(){

            $("#source_text,#main_scroller,#scrollbar").mousewheel(function(event,delta){
                $('#scrollbar').data('state', delta > 0 ? -1: 1);
                scroll_annot( delta>0 ? -50 : 50);
                endScroll();
                event.preventDefault()
                });

            $("#submit_button").click(function(){
                if($(this).data('step') == '1') {
                    $(this).data('step','2')
                    submit_with_readonly_check($(this).closest('form'))
                }
                else {
                    $('#comment_fld').show()
                    $(this).css('font-weight','bold').data('step','1')
                }
                });

            $("#show_src").click(function(){
                $(this).hide();
                $("#source_orig").show();
            });

            $("#main_annot a.reload").click(function(event){
                dict_reload($(this));
                event.preventDefault();

            });

            $("div.tf").dblclick(function(){
                var $this = $(this);
                var token_text = $this.html();
                var tid = $this.closest('td').data('tid');
                $(this).html('<form class="inline" method="post" action="?act=save_token_src&amp;id={$sentence.id}"><input name="token_id" type="hidden" value="' + tid + '"/><input name="src_text" value="' + token_text + '" class="span2"/><button class="btn btn-success btn-small"><i class="icon-ok"></i></button></form>');
            });

            $("#btn_show_src_edit").click(function(){
                $(this).hide();
                var $div = $(this).closest('div');
                var text = $div.find('span').html();
                $div.find('span').replaceWith('<form class="inline" method="post" action="?act=save_src&amp;id={$sentence.id}"><textarea style="width: 90%" rows="4" name="src_text">' + text + '</textarea><br/><button class="btn btn-primary">Сохранить</button></form>');
            });

            $("#a_comments").click(show_comments);

            $("#a_parse").click(function(){
                $(this).hide();
                $(".oc_tabs").hide();
                $("#form_annot").show();
                highlight_source();
                prepareScroll();
                location.hash = '#';
            });

            if (location.hash.substring(1, 5) == 'comm') {
                show_comments(null, (location.hash.substring(5,6) == '_'));
            }

        });
        var unload_msg = "Вы уверены, что хотите уйти, не сохранив предложение?";
        var root = window.addEventListener || window.attachEvent ? window : document.addEventListener ? document : null;
        if(root) {
            root.onbeforeunload=function () {
                if($("#submit_button").length && !$("#submit_button").attr('disabled') && $("#submit_button").data('step') != '2') {
                    return unload_msg;
                    };

                };
        }
    </script>
    {* TODO allow only perm_syntax *}
    {if $user_permission_disamb == 1 || $user_permission_syntax == 1}
        <div class="btn-group">
            <a href="?id={$sentence.id}&mode=morph" class="btn {if !isset($smarty.get.mode) || $smarty.get.mode == 'morph'}btn-success active{/if}">Морфология</a>
            <a href="?id={$sentence.id}&mode=syntax" class="btn {if isset($smarty.get.mode) && $smarty.get.mode == 'syntax'}btn-success{/if}">Синтаксис</a>
        </div>
    {/if}
    <p align='right'>
    {if $sentence.status == 0}
    <span class='sent_status0'>Предложение разобрано автоматически.</span>
    {elseif $sentence.status == 1}
    <span class='sent_status1'>Частично снята омонимия.</span>
    {/if}
    </p>
    {strip}
    <div id="source_text"><b>Весь текст:</b> {$sentence.fulltext}</div>
    <p class='small'><a href='#' class='hint' id="show_src">Показать исходный текст</a></p>
    <div class='small' style='display:none' id='source_orig'><span>{$sentence.source|htmlspecialchars}</span>{if $is_admin}<button class='btn btn-small' id='btn_show_src_edit'>Исправить</button>{/if}</div>
    <p class='small' align='right'>Источник: <a href="{$sentence.url}">{$sentence.book_name}</a> (<a href="{$web_prefix}/books.php?book_id={$sentence.book_id}&amp;full">весь текст</a>)</p>
    <button id="a_parse" class="hidden-block">Вернуться к разбору</button>
    <form method="post" action="?id={$sentence.id}&amp;act=save" class='oc_tabs' id="form_annot">
        <div id="main_scroller">
            <div id="main_scroller_inner">
                {if $user_permission_disamb == 1}
                    <button type="button" disabled="disabled" id="submit_button" class="btn btn-primary">Сохранить</button>&nbsp;
                {/if}
                <button type="reset" onclick="window.location.reload()" class="btn">Отменить правки</button>&nbsp;
                <button type="button" onclick="window.location.href='history.php?sent_id={$sentence.id}'" class="btn">История</button>&nbsp;
                <button type="button" id="a_comments" class="btn">
                    {if $sentence.comment_count > 0}
                    Комментарии ({$sentence.comment_count})
                    {else}
                    Комментировать
                    {/if}
                </button>
                <br/>
                <span id='comment_fld'>Комментарий: <input name='comment' size='60' placeholder='необязательно' type="text"/></span>
            </div>
        </div>
        <div id="scrollbar"><div style="height:10px;"></div></div>
        <div id="main_annot"><table><tr>
        {foreach item=token from=$sentence.tokens}
            <td id="var_{$token.tf_id}" data-tid="{$token.tf_id}">
                <div class="tf">
                    {$token.tf_text|htmlspecialchars}
                    <a href="#" class="reload" title="Разобрать заново из словаря"><i class="icon-refresh bggreen"></i></a>
                </div>
                {foreach item=variant from=$token.variants}
                    <div class="var" id="var_{$token.tf_id}_{$variant.num}">
                        <input type="hidden" name="var_flag[{$token.tf_id}][{$variant.num}]" value="1"/>
                        {if $variant.lemma_id > 0}
                            <a href="{$web_prefix}/dict.php?act=edit&amp;id={$variant.lemma_id}">{$variant.lemma_text}</a>
                        {else}
                            <span class='lt'>{$variant.lemma_text|htmlspecialchars}</span>
                        {/if}
                        <a href="#" class="best_var" onclick="best_var(this.parentNode); return false">v</a>
                        <a href="#" class="del_var" onclick="del_var(this.parentNode); return false">x</a>
                        <br/>
                        {foreach item=gram from=$variant.gram_list name=gramf}
                        <span class='hint' title='{$gram.descr}'>
                        {if isset($smarty.session.options) && $smarty.session.options.1 == 1}
                            {$gram.outer|default:"<b class='red'>`$gram.inner`</b>"}
                        {else}
                            {$gram.inner}
                        {/if}
                        </span>{if !$smarty.foreach.gramf.last}, {/if}
                        {/foreach}
                    </div>
                {/foreach}
            </td>
        {/foreach}
        </tr></table></div>
    </form>
    {/strip}
    <div id="comments" class="oc_tabs hidden-block">
    {if $is_logged}
    <a href="#" class="hint" onclick="$('#comment_form').insertAfter($(this)).show().find('textarea').focus(); return false">Добавить комментарий:</a><br/>
    <form id="comment_form" class="hidden-block"><textarea cols="30" rows="3"></textarea><br/><button type="button" onclick="post_sentence_comment($(this), {$sentence.id}, '{$smarty.session.user_name}')">Прокомментировать</button></form>
    {/if}
    </div>
{/block}
