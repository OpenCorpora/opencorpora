$(document).ready(function(){
    
    $(".submit_link").click(function(event){
        $(this).closest('form').submit()
        })
    
    })

function byid(id) {
    return document.getElementById(id)
}
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
    var el = byid('scrollbar');
    if (el.state == (offset > 0 ? 1: -1) ) {
        var newVal = el.scrollLeft + offset;
        if (newVal < 0) newVal = 0;
        el.scrollLeft = newVal;
        highlight_source();
        setTimeout('scroll_annot(' + offset + ')', 100);
    }
}

function startScroll(offset) {
    byid('scrollbar').state = offset > 0 ? 1: -1;
    setTimeout('scroll_annot(' + offset + ')', 0);
}

function endScroll() {
    $('#scrollbar').attr('state', 0);
}

function syncScroll() {
	byid('main_annot').scrollLeft = byid('scrollbar').scrollLeft;
	highlight_source();
}

function prepareScroll() {
    byid('scrollbar').firstChild.style.width = byid('main_annot').firstChild.scrollWidth + "px";
    byid('scrollbar').onscroll = syncScroll;
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
    if ( (code == 37 && byid('scrollbar').state == -1) || (code == 39 && byid('scrollbar').state == 1) )
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
    var el = byid('main_annot');
    var l = el.scrollLeft;
    var wd = el.offsetWidth;
    el = el.firstChild.firstChild.firstChild; //el is <tr>
    var i;
    var cur_token;
    for (i=0; i < el.childNodes.length; ++i) {
        cur_token = el.childNodes[i];
        //if outer edge is visible + lighter if inner edge is visible
        if (cur_token.offsetLeft >= l && cur_token.offsetLeft + cur_token.offsetWidth < l + wd)
            byid('src_token_' + i).className = 'src_token_hlt';
        else if (cur_token.offsetLeft + cur_token.offsetWidth > l && cur_token.offsetLeft < l + wd) 
            byid('src_token_' + i).className = 'src_token_hlt_light';
    }
}
function dehighlight_source() {
    var i;
    var cur_token;
    for (i=0; cur_token = byid('src_token_' + i); i++) {
        cur_token.className='';
    }
}
function show_comment_field(btn) {
    $('#comment_fld').show();
    btn.style.fontWeight = 'bold';
    btn.setAttribute('onclick', 'submit_with_readonly_check(document.forms[0])');
}
function dict_reload_all() {
    var tr = byid('main_annot').firstChild.firstChild.firstChild;
    for (i=0; i<tr.childNodes.length; ++i) {
        dict_reload(tr.childNodes[i]);
    }
    highlight_source();
}
function dict_reload(td) {
    var tf_id = parseInt(td.id.substr(4));
    //delete all vars
    while (td.childNodes.length > 1) {
        td.removeChild(td.lastChild);
    }
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
            var sb;
            if (sb = byid('submit_button'))
                sb.disabled = false;
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
    var f = byid('add_link');
    var req = makeRequest();
    req.onreadystatechange = function() {
        if (req.readyState==4) {
            var root = req.responseXML.documentElement;
            for (i = 0; i < root.childNodes.length; ++i) {
                lid = root.childNodes[i].getAttribute('id');
                var new_radio = document.createElement('input');
                new_radio.setAttribute('type', 'radio');
                new_radio.setAttribute('name', 'lemma_id');
                new_radio.setAttribute('value', lid);
                new_radio.setAttribute('onClick', 'byid("add_link_submitter").disabled=false');
                var new_label = document.createElement('label');
                var new_href = document.createElement('a');
                new_href.setAttribute('href', '?act=edit&id=' + lid);
                new_href.setAttribute('target', '_blank');
                new_href.innerHTML = lid;
                new_label.appendChild(new_radio);
                new_label.appendChild(new_href);
                f.appendChild(new_label);
            }
            if (root.childNodes.length > 0) {
                var new_text = document.createTextNode(' ');
                f.appendChild(new_text);
                var new_button = document.createElement('input');
                new_button.setAttribute('type', 'submit');
                new_button.setAttribute('value', 'Добавить');
                new_button.setAttribute('disabled', 'disabled');
                new_button.setAttribute('id', 'add_link_submitter');
                f.appendChild(new_button);
            }
        }
    }
    req.open('get', 'ajax/lemma_search.php?q=' + q, true);
    req.send(null);
}
