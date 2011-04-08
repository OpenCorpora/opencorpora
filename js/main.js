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
        }
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
        }
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
function show_comment_field(btn) {
    $('#comment_fld').show();
    btn.style.fontWeight = 'bold';
    btn.setAttribute('onclick', 'submit_with_readonly_check(document.forms[0])');
}
function dict_reload_all() {
    $('#main_annot td').each(function(i, el){
        dict_reload(el);
    })
    highlight_source();
}
function dict_reload(td) {
    var tf_id = parseInt(td.id.substr(4));
    //delete all vars
    $(td).find('.var').remove();

    var old_inner = td.firstChild.innerHTML;
    td.firstChild.innerHTML = 'Загрузка...';
    var req = makeRequest();
    req.onreadystatechange = function() {
        if (req.readyState==4) {
            var root = req.responseXML.documentElement;
            var rev = root.firstChild;
            var i;
            var j;
            var cvar;
            for (i = 0; i < rev.childNodes.length; ++i) {
                cvar = rev.childNodes[i];
                var new_div = document.createElement('div');
                new_div.className = 'var';
                new_div.setAttribute('id', 'var_'+tf_id+'_'+(i+1));
                new_div.innerHTML = '<input name="var_flag['+tf_id+']['+(i+1)+']" value="1" type="hidden"/>';
                if (cvar.firstChild.getAttribute('id') > 0)
                    new_div.innerHTML += '<a href="dict.php?act=edit&amp;id='+cvar.firstChild.getAttribute('id')+'">'+cvar.firstChild.getAttribute('t')+'</a>';
                else
                    new_div.innerHTML += '<span>'+cvar.firstChild.getAttribute('t')+'</span>';
                new_div.innerHTML += '<a class="best_var" onclick="best_var(this.parentNode); return false" href="#">v</a><a class="del_var" onclick="del_var(this.parentNode); return false" href="#">x</a><br/>' + '<span class="hint" title="' + cvar.firstChild.firstChild.getAttribute('d') + '">' + cvar.firstChild.firstChild.getAttribute('v') + '</span>';
                for (j = 1; cvar.firstChild.childNodes[j] != null; ++j) {
                    new_div.innerHTML += ', ' + '<span class="hint" title="' + cvar.firstChild.childNodes[j].getAttribute('d') + '">' + cvar.firstChild.childNodes[j].getAttribute('v') + '</span>';
                }
                td.appendChild(new_div);
            }
            td.firstChild.innerHTML = old_inner + '<input type="hidden" name="dict_flag['+tf_id+']" value="1"/>';
            $('#submit_button').removeAttr('disabled');
            prepareScroll();
        }
    }
    req.open ('get', 'ajax/dict_reload.php?tf_id='+tf_id, true);
    req.send(null);
}
function dict_add_form(event) {
    $(event.target).closest('tbody').append("<tr><td><input name='form_text[]'></td><td><input size='40' name='form_gram[]'></td></tr>")
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
