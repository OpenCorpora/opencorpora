$(document).ready(function(){
    
    $(".submit_link").click(function(event){
        $(this).closest('form').submit()
        })
    
    })

function makeRequest() {
    var req = false;
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
        if (req.overrideMimeType) {
            req.overrideMimeType('text/xml');
        }
    } else if (window.ActiveXObject) {
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(e) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch(e) {}
        }
    }
    if (!req) {
        document.write("Cannot instantiate XMLHTTP object");
        return false;
    }
    return req;
}

function changeSelectBook(n) {
    var m, $el;
    var $s_old = $('#book'+n);
    var $s_new = $('#book'+(n+1));
    
    // если следующего нет - обновляем блок с абзацем
    if (!$s_new.length) {
        updateLastParInfo($s_old.val());
        return;
    }
    $s_new.attr('disabled','disabled');
    if ($s_old.val() == 0) {
        m = n+1;
    }
    else {
        m = n+2;
    }
    // блокируются селекты со следующего или послеследующего
    for(var i = m; i < 2; ++i) {
        $el = $('#book'+i);
        $el.empty().html("<option value='0'>-- Не выбрано --</option>").attr('disabled','disabled');
    }
    // чистим блок с абзацем
    updateLastParInfo(0);
    
    if ($s_old.val() == 0) {
        // если ничего не выбрано, закончили на этом
        return;
    }
    // очищаем и временно блокируем следующий селект
    $s_new.empty().html("<option value='0'>Загрузка...</option>")
        
    $.get('ajax/select_book.php',{'id':$s_old.val()},
        function(res){
            $s_new.empty().html("<option value='0'>-- Не выбрано --</option>")
            $opts = $(res).find('option')
            if(!$opts.length) {
                updateLastParInfo($s_old.val());
                return;
            }
            $opts.each(function(i,el){
                $s_new.append("<option value='"+$(el).attr('value')+"'>"+$(el).text()+"</option>")
                })
            $s_new.removeAttr('disabled')
        },'xml'
        )
}

// обновление блока с абзацем
function updateLastParInfo(book_id) {
    
    var $p = $('#lastpar_info');
    var $sub = $('#submitter');
    
    // сброс
    if (book_id == 0) {
        $sub.attr('disabled','disabled');
        $p.html('Надо выбрать книгу.');
        return;
    }
    var $np = $('#newpar');
    
    $p.html('<i>Загрузка...</i>');
    
    $.get('ajax/lastpar.php',{'book_id':book_id},
        function(res) {
            $par = $(res).children().eq(0)
            // есть ли к чему крепить?
            if(!$par.text()) {
                $p.html('Нет ни одного абзаца.');
                $np.val('1');
            }
            else {
                $p.html('Последний абзац #' + $par.attr('num') + ' &laquo;<i>' + $par.text() + '</i>&raquo;');
                $np.val(parseInt($par.attr('num')) + 1);
            }
            // теперь можно добавлять
            $sub.removeAttr('disabled');
        },'xml'
        )
}

function scroll_annot(offset) {
    var $el = $('#scrollbar');
    if ($el.data('state') == (offset > 0 ? 1: -1) ) {
        var newVal = $el.scrollLeft() + offset;
        if (newVal < 0) newVal = 0;
        $el.scrollLeft(newVal);
        highlight_source();
        setTimeout('scroll_annot(' + offset + ')', 100);
    }
}

function startScroll(offset) {
    $('#scrollbar').data('state', offset > 0 ? 1: -1);
    setTimeout('scroll_annot(' + offset + ')', 0);
}

function endScroll() {
    $('#scrollbar').data('state', 0);
}

function syncScroll() {
	$('#main_annot').scrollLeft($('#scrollbar').scrollLeft());
	highlight_source();
}

function prepareScroll() {
    $('#scrollbar div').width($('#main_annot table').width());
    $('#scrollbar').scroll(syncScroll);
}
        
function checkKeyDown(evt) {
    var code = evt.keyCode ? evt.keyCode : evt.charCode;
    if (code == 37)
        startScroll(evt.shiftKey ? -50: -20);
    if (code == 39)
        startScroll(evt.shiftKey ? 50 : 20);
}
function checkKeyUp(evt) {
    var code = evt.keyCode ? evt.keyCode : evt.charCode;
    if ( (code == 37 && $('#scrollbar').data('state') == -1) || (code == 39 && $('#scrollbar').data('state') == 1) )
        endScroll();
}
function del_var(v) {
    v.firstChild.value = 0;
    v.className = 'var inactive';
    $('#submit_button').removeAttr('disabled');
}
function best_var(v) {
    v.firstChild.value = 1;
    v.className = 'var';
    for (var i = 1; i<v.parentNode.childNodes.length; ++i) {
        if (v.parentNode.childNodes[i].id != v.id)
            del_var(v.parentNode.childNodes[i]);
    }
}
function highlight_source() {
    dehighlight_source();
    var l = $('#main_annot').scrollLeft();
    var wd = $('#main_annot').width();
    var ol;

    $('#main_annot td').each(function(i){
        //if outer edge is visible + lighter if inner edge is visible
        ol = this.offsetLeft;
        if (ol >= l && ol + $(this).width() < l + wd)
            $('#src_token_' + i).removeClass().addClass('src_token_hlt');
        else if (ol + $(this).width() > l && ol < l + wd)
            $('#src_token_' + i).removeClass().addClass('src_token_hlt_light');
    });
}
function dehighlight_source() {
    var i;
    for (i=0; $('#src_token_' + i).length; i++) {
        $("#src_token_"+i).removeClass();
    }
}
function dict_add_form(event) {
    $('#paradigm tbody').append("<tr><td><input type='text' name='form_text[]'></td><td><input type='text' size='40' name='form_gram[]'></td></tr>")
    if (event)
        event.preventDefault()
}
function edit_gram(event) {
    var $a = $(event.target).closest('a')
    var names = ['','inner_id','outer_id','descr','','submit']
    $a.closest('tr').find('td').each(function(i,el) {
        if(names[i]) {
            var $el = $(el)
            if(names[i] == 'submit') {
                $el.html($el.html()+'<input type="hidden" name="id" value="'+$a.attr('rel')+'"/><input type="submit" value="Сохранить"/>&nbsp;<input type="button" value="Отменить" onClick="location.reload()"/>')
            }
            else {
                $el.html('<input name="'+names[i]+'" '+(names[i] == 'descr' ? 'size="35"' : 'size="10" maxlength="20"')+' value="'+$el.html()+'"/>')
            }
        }
        })
    event.preventDefault()
}

function submit_with_readonly_check(f) {
    $.get("ajax/readonly.php",function(res){
        if($(res).find('response').attr('readonly')=='1') {
            alert('Извините, система находится в режиме "только для чтения".');
        }
        else {
            f.submit();
        }
        })
}
function get_lemma_search() {
    var q = $('#find_lemma').val();
    if (q.length < 3) {
        alert('Слишком короткий запрос');
        return false;
    }

    var lid;

    $.get('ajax/lemma_search.php', {'q':q},
        function(res) {
            var $lemmata = $(res).find('lemma');
            $lemmata.each(function(i){
                lid = $(this).attr('id');

                var $new_radio = $(document.createElement('input'));
                $new_radio.attr({'type':'radio', 'name':'lemma_id'});
                $new_radio.val(lid);

                var $new_label = $(document.createElement('label'));
                $new_label.append($new_radio);
                $new_label.html($new_label.html() + '<a href="?act=edit&amp;id=' + lid + '" target="_blank">' + lid + '</a>');

                $('#add_link').append($new_label);
            })

            $("input[type='radio']").click(function(){
                $('#add_link_submitter').removeAttr('disabled');
            })

            if ($lemmata.length) {
                $('#add_link').append(document.createTextNode(' '));
                var $new_button = $(document.createElement('input'));
                $new_button.attr({'type':'submit', 'value':'Добавить', 'disabled':'disabled', 'id':'add_link_submitter'});
                $('#add_link').append($new_button);
            }
        }
    )
}
function dict_add_exc_prepare($btn) {
    $("<textarea name='comm' cols='20' rows='2'>no comment</textarea>").insertAfter($btn);
    $btn.click(function(event){
        $(this).closest('form').submit()
    })
}
function save_check_tokens($el) {
    $el.attr('disabled', 'disabled');
    $.get('ajax/save_check.php', {'type':'token', 'id':$el.attr('id').substr(1), 'value':$el.is(':checked')},
        function(res) {
            var ok = $(res).find('result').attr('ok');
            if (ok != 1) {
                alert('Check failed');
            } else {
                $el.closest('tr').fadeTo("normal", 0.4);
            }
        }
    )
    $el.removeAttr('disabled');
}
function show_edit_token($el) {
    var tid = parseInt($el.attr('id').substr(1));
    var $e = $("#edit_tok");
    var $sp = $("span#t"+tid);
    var $chb = $e.find("input[type='checkbox']");
    $e.find("a").show();
    if ($sp.data('checked') == 1) {
        $chb.attr('checked', 'checked');
    } else {
        $chb.removeAttr('checked');
    }
    $e.find("form:first").hide();
    $e.find("form").eq(1).hide();
    if ($el.html().length < 2) $e.find("a").eq(1).hide();
    $e.show();
    var offset = $el.offset();
    offset.top += 25;
    $e.offset(offset).find("b").text($el.html());
    $e.find("input[name='tid']").val(tid);
    $e.find("div.tid").html('#'+tid);
}
function check_merge($chbox) {
    var tid = $chbox.closest('div').find('div.tid').html().substr(1);
    if ($chbox.is(':checked')) {
        $("span#t"+tid).addClass('bgblue').data('checked', 1);
    } else {
        $("span#t"+tid).removeClass().removeData('checked');
    }

    //(de)activating button
    var num_checked = 0;
    $("span.bgblue").each(function(i, el){
        if ($(el).data('checked') == 1) num_checked++;
    });
    if (num_checked > 1) {
        $("#edit_tok").find('button:last').removeAttr('disabled');
    } else {
        $("#edit_tok").find('button:last').attr('disabled', 'disabled');
    }
}
function merge_tokens() {
    var a = new Array();
    $("span.bgblue").each(function(i, el){
        if ($(el).data('checked') == 1) {
            a.push($(el).attr('id').substr(1));
        }
    });
    $.get('ajax/merge_tokens.php', {'ids':a.join(',')}, function(res) {
        if ($(res).find('result').attr('ok') == 1)
            location.reload();
        else
            alert('Error');
    });
}
function download_url(event) {
    var $el = $(event.target).closest('a');
    var force = $el.hasClass('redo') ? 1 : 0;
    var url = $el.attr('rel');
    $el.text('скачивается...');
    $.get('ajax/download_url.php', {'url': url, 'force': force},
        function(res) {
            var ok = $(res).find('response').attr('ok');
            if (ok == 1) {
                var fname = $(res).find('response').attr('filename');
                $el.attr('href', '../files/saved/'+fname+'.html');               
                if (force)
                    $el.html('новая сохранённая копия');
                else
                    $el.html('сохранённая копия');
            } else {
                $el.html('ошибка при сохранении файла');
            }
        }
    )
    event.preventDefault();
}
function post_sentence_comment($el, sent_id, username) {
    var txt = $el.closest('form').find('textarea').val();
    var reply_to = $el.closest('form').attr('rel');
    $.post('ajax/post_comment.php', {'type':'sentence', 'text':txt, 'id':sent_id, 'reply_to':reply_to},
        function(res) {
            var $res = $(res).find('response');
            if ($res.attr('ok') == 1) {
                $el.closest('form').hide();
                var $newcomment = $(document.createElement('div'));
                $newcomment.attr({'id':'comm_'+$res.attr('id')});
                $newcomment.addClass('comment_main');
                $newcomment.append('<div class="comment_top">'+username+', '+$res.attr('ts')+'</div><div class="comment_text">'+txt+'</div>');
                var $reply_link = $(document.createElement('a')).addClass('small').attr({'rel':$res.attr('id'), 'href':'#'}).html('ответить').click(function(){
                    $(this).closest('div').after($("#comment_form"));
                    $("#comment_form").show().attr('rel', $(this).attr('rel'));
                    $("#comment_form").find('textarea').focus();
                    event.preventDefault();
                });
                $newcomment.append($reply_link);
                $newcomment.append(' <a href="#comm_'+$res.attr('id')+'" class="small">пост. ссылка</a>');
                if (!reply_to) {
                    $("#comments").append($newcomment);
                } else {
                    var $p = $("#comm_"+reply_to);
                    $p.after($newcomment);
                    var offset = $p.offset();
                    offset.left += 25;
                    offset.top = $newcomment.offset().top;
                    $newcomment.offset(offset);
                }
            }
        }
    );
}
function load_sentence_comments(sent_id, is_logged, need_scroll) {
    var $div = $("#comments");
    $.get('ajax/get_comments.php', {'sent_id':sent_id},
        function(res) {
            var $comm = $(res).find('comment');
            $comm.each(function(i, el) {
                var $el = $(el);
                var t = '<div id="comm_'+$el.attr('id')+'" class="comment_main"><div class="comment_top">'+$el.attr('author')+', '+$el.attr('ts')+'</div><div class="comment_text">'+$el.text()+'</div>';
                if (is_logged) t += '<a href="#" class="small reply" rel="'+$el.attr('id')+'">ответить</a>';
                t += ' <a href="#comm_'+$el.attr('id')+'" class="small">пост. ссылка</a>';
                t += '</div>';
                if ($el.attr('reply') == 0) {
                    $div.append(t);
                } else {
                    var $p = $("#comm_"+$el.attr('reply'));
                    $p.after(t);
                    var $n = $("#comm_"+$el.attr('id'));
                    var offset = $p.offset();
                    $n.width($n.width()-offset.left);
                    offset.left += 25;
                    offset.top = $n.offset().top;
                    $n.offset(offset);
                }
            });
            $("a.reply").click(function(event){
                $(this).closest('div').after($("#comment_form"));
                $("#comment_form").show().attr('rel', $(this).attr('rel'));
                $("#comment_form").find('textarea').focus();
                event.preventDefault();
            });
            if (need_scroll)
                window.scrollTo(0, $(location.hash).offset().top);
        }
    );
}
function change_source_status(event) {
    $.get('ajax/save_check.php', {'id':$(event.target).attr('rel'), 'type':'source', 'value':$(event.target).attr('rev')}, function(res) {
        var $b = $(event.target);
        if ($(res).find('result').attr('ok') == 1) {
            if($b.attr('rev') == 1) {
                $b.attr('rev', '0').html('Не готово').closest('tr').removeClass().addClass('bggreen');
            } else {
                $b.attr('rev', '1').html('Готово').closest('tr').removeClass().addClass('bgyellow');
            }
        } else {
            alert('Query failed');
        }
    });
}
function get_wikinews_info($link) {
    var ttl = $link.closest('p').find('span').html();
    $.getJSON(
        'http://ru.wikinews.org/w/api.php?callback=?',
        {'format':'json', 'action':'query', 'titles':ttl, 'prop':'revisions|categories', 'rvdir':'newer'},
        function(data) {
            var author;
            var categ = new Array();
            $.each(data.query.pages, function(i, item){
                author = item.revisions[0].user;
                $.each(item.categories, function(j, catitem){
                    categ.push(catitem.title);
                });
                add_field_for_tag($link.attr('rel'), 'Автор:http://ru.wikinews.org/wiki/Участник:' + author);
                $.get('ajax/guess_wiki_categ.php', {'cat':categ.join('|')}, function(res1){
                    add_field_for_tag($link.attr('rel'), 'Дата:' + $(res1).find("date").attr('v'));
                    add_field_for_tag($link.attr('rel'), 'Год:' + $(res1).find("year").attr('v'));
                    $.each($(res1).find("topic"), function(j, catitem){
                        add_field_for_tag($link.attr('rel'), 'Тема:ВикиКатегория:' + $(catitem).attr('v'));
                    });
                    $.each($(res1).find("geo"), function(j, catitem){
                        add_field_for_tag($link.attr('rel'), 'Гео:ВикиКатегория:' + $(catitem).attr('v'));
                    });
                });
                $.getJSON(
                    'http://ru.wikinews.org/w/api.php?callback=?',
                    {'format':'json', 'action':'query', 'titles':ttl, 'prop':'revisions'},
                    function(rdata) {
                        var lastrevid;
                        $.each(rdata.query.pages, function(i, item){
                            lastrevid = item.revisions[0].revid;
                        });
                        add_field_for_tag($link.attr('rel'), 'url:http://ru.wikinews.org/w/index.php?title=' +ttl.replace(/ /g, '_') + '&oldid=' + lastrevid);
                    }
                );
            });
            $link.hide();
        }
    );
}
function get_chaskor_info($link) {
    var ttl = $link.closest('div').find('span').html();
    $.get('python/chaskor.py', {'url':'news/'+ttl}, function(res){
        var $res = $(res);
        add_field_for_tag($link.attr('rel'), $res.find('year').text());
        add_field_for_tag($link.attr('rel'), $res.find('date').text());
        add_field_for_tag($link.attr('rel'), $res.find('mainSubject').text());
        add_field_for_tag($link.attr('rel'), $res.find('subSubject').text());
        $link.hide();
    });
}
function add_field_for_tag(book_id, s) {
    var $i = $(document.createElement('input')).css('width', '600').val(s);
    var $b = $(document.createElement('button')).html('Ok').click(function(event) {
        $(this).attr('disabled', 'disabled');
        $.get('ajax/add_book_tag.php', {'book_id':book_id, 'tag_name':$i.val()}, function(res){
            if ($(res).find('result').attr('ok') == 1)
                $(event.target).hide();
                $i.replaceWith($i.val());
        });
    });
    $(document.createElement('li')).append($i, '&nbsp;', $b).appendTo('#book_tags');
}
function check_for_whitespace() {
    var flag = 1;
    $('textarea').each(function(i,el){
        $(el).removeClass('bgpink');
        if (
            $(el).val().trim().indexOf(' ') != -1 ||
            $(el).val().trim().indexOf(String.fromCharCode(10)) != -1 ||
            $(el).val().trim().indexOf(String.fromCharCode(13)) != -1
        ) {
            $(el).addClass('bgpink').removeClass('hidden-block');
            window.scrollTo(0, $(el).offset().top);
            flag = 0;
            return;
        }
    });
    if (!flag) alert('Bad symbols (whitespace or line break) detected');
    return flag;
}
function toggle_rightmenu(el) {
    $el = $(el);
    if(!$el.hasClass('active')) {
        $el.addClass('active');
        if($el.hasClass('hide-menu')){
            $('#rightcol').animate({width:15},150,function(){
                $('#content').addClass('content-wide');
                $('#rightcol-inner').hide();
                $el.removeClass('hide-menu').addClass('show-menu').text('←').attr('title','показать меню');
                $.get('ajax/set_option.php',{'var':'hidemenu','value':1},function(){
                    $el.removeClass('active');
                })
            });
        }
        else {
            $('#rightcol-inner').show();
            $('#rightcol').animate({width:200},150,function(){
                $('#content').removeClass('content-wide');
                $('#rightcol').removeClass('rightcol-narrow');
                $el.removeClass('show-menu').addClass('hide-menu').text('→').attr('title','скрыть меню');
                $.get('ajax/set_option.php',{'var':'hidemenu','value':0},function(){
                    $el.removeClass('active');
                })
            });
        }
    }
}
function show_bootalert(type,msg) {
    $("#alert_wrap").html('<div class="alert alert-' + type + '">' + msg + '</div>');
    setTimeout('$("#alert_wrap .alert").fadeOut()',3000);
}
