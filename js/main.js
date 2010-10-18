function byid(id) {
    return document.getElementById(id)
}
function show(el) {
    el.style.display='block';
}
function hide(el) {
    el.style.display='none';
}
function toggle(el) {
    if (el.style.display == 'none') el.style.display='block';
        else el.style.display='none';
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
function addOption(sel, txt, val) {
    var newopt = document.createElement("option");
    newopt.setAttribute('value', val);
    var t = document.createTextNode(txt);
    newopt.appendChild(t);
    sel.appendChild(newopt);
}
function clearCh(el) {
    while(el.childNodes.length>0) {
        el.removeChild(el.lastChild);
    }
}
function changeSelectBook(n) {
    var m;
    var el;
    var s_old = byid('book'+n);
    var s_new = byid('book'+(n+1));
    if (!s_new) {
        updateLastParInfo(s_old.value);
        return;
    }
    s_new.disabled = true;
    if (s_old.value==0) m = n+1;
        else m = n+2;
    for(var i = m; i < 2; ++i) {
        el = byid('book'+i);
        clearCh(el);
        addOption(s_new, '-- Не выбрано --', 0);
        el.disabled = true;
    }
    updateLastParInfo(0);
    if (s_old.value==0) return;
    clearCh(s_new);
    addOption(s_new, 'Загрузка...', 0);
    var req = makeRequest();
    req.onreadystatechange = function() {
        if (req.readyState==4) {
            clearCh(s_new);
            addOption(s_new, '-- Не выбрано --', 0);
            el = req.responseXML.documentElement;
            if (el.childNodes.length==0) {
                updateLastParInfo(s_old.value);
                return;
            }
            for (i = 0; i<el.childNodes.length; ++i) {
                t = el.childNodes[i];
                addOption(s_new, t.firstChild.data, t.getAttribute('value'));
            }
            s_new.disabled = false;
        }
    };
    req.open('get', 'ajax/select_book.php?id='+s_old.value, true);
    req.send(null);
}
function updateLastParInfo(book_id) {
    var p = byid('lastpar_info');
    var sub = byid('submitter');
    if (book_id==0) {
        sub.disabled = true;
        p.innerHTML = 'Надо выбрать книгу.';
        return;
    }
    var np = byid('newpar');
    var req = makeRequest();
    p.innerHTML = '<i>Загрузка...</i>';
    req.onreadystatechange = function() {
        if (req.readyState==4) {
            var el = req.responseXML.documentElement;
            if (el.childNodes.length==0) {
                p.innerHTML = 'Нет ни одного абзаца.';
                np.value = '1';
                sub.disabled = false;
                return;
            }
            p.innerHTML = 'Последний абзац #' + el.getAttribute('num') + ' &laquo;<i>' + el.firstChild.data + '</i>&raquo;';
            np.value = parseInt(el.getAttribute('num')) + 1;
            sub.disabled = false;
        }
    }
    req.open ('get', 'ajax/lastpar.php?book_id='+book_id, true);
    req.send(null);
}
function scroll_annot(offset) {
    var el = byid('main_annot');
    if (el.state == 1) {
        var newVal = el.scrollLeft + offset;
        if (newVal < 0) newVal = 0;
        el.scrollLeft = newVal;
        highlight_source();
        setTimeout('scroll_annot(' + offset + ')', 100);
    }
}
function scroll_annot_byword(dir) {
    var el = byid('main_annot');
    if (el.state == 2) {
        var l = el.scrollLeft;
        var wd = el.offsetWidth;
        var tr_el = el.firstChild.firstChild.firstChild;
        var i;
        var cur_token;
        var d;
        if (dir == 1) {
            for (i = 0; i < tr_el.childNodes.length; ++i) {
                cur_token = tr_el.childNodes[i];
                if ((d = cur_token.offsetLeft + cur_token.offsetWidth - l - wd) > 0) {
                    el.scrollLeft += (d + 10);
                    break;
                }
            }
            highlight_source();
            setTimeout('scroll_annot_byword(' + dir + ')', 500);
        }
        else if (dir == -1) {
            for (i = tr_el.childNodes.length; i > 0; --i) {
                cur_token = tr_el.childNodes[i-1];
                if ((d = cur_token.offsetLeft - l) < 0) {
                    el.scrollLeft += d;
                    break;
                }
            }
            highlight_source();
            setTimeout('scroll_annot_byword(' + dir + ')', 500);
        }
    }
}
function startScroll(offset) {
    byid('main_annot').state = 1;
    setTimeout('scroll_annot(' + offset + ')', 0);
}
function startScrollByWord(dir) {
    byid('main_annot').state = 2;
    setTimeout('scroll_annot_byword(' + dir + ')', 0);
}
function endScroll() {
    byid('main_annot').state = 0;
}
function checkKeyDown(evt) {
    var code = evt.keyCode ? evt.keyCode : evt.charCode;
    if (code == 37)
        startScroll(evt.ctrlKey ? -50: -20);
    if (code == 39)
        startScroll(evt.ctrlKey ? 50 : 20);
}
function checkKeyUp(evt) {
    var code = evt.keyCode ? evt.keyCode : evt.charCode;
    if (code == 37 || code == 39)
        endScroll();
}
function del_var(v) {
    v.childNodes[1].value = 0;
    v.className = 'var inactive';
    var b;
    if (b = byid('submit_button'))
        b.disabled = false;
}
function best_var(v) {
    v.childNodes[1].value = 1;
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
function dict_reload(el) {
    var td = el.parentNode.parentNode;
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
                new_div.innerHTML = '<img src="spacer.gif" height="1" width="100"><input name="var_flag['+tf_id+']['+(i+1)+']" value="1" type="hidden">';
                if (cvar.firstChild.getAttribute('id') > 0)
                    new_div.innerHTML += '<a href="dict.php?id='+cvar.firstChild.getAttribute('id')+'">'+cvar.firstChild.getAttribute('text')+'</a>';
                else
                    new_div.innerHTML += '<span>'+cvar.firstChild.getAttribute('text')+'</span>';
                new_div.innerHTML += '<a class="best_var" onclick="best_var(this.parentNode); return false" href="#">v</a><a class="del_var" onclick="del_var(this.parentNode); return false" href="#">x</a><br/>' + cvar.firstChild.firstChild.getAttribute('val');
                for (j = 1; cvar.firstChild.childNodes[j] != null; ++j) {
                    new_div.innerHTML += ', ' + cvar.firstChild.childNodes[j].getAttribute('val');
                }
                td.appendChild(new_div);
            }
            td.firstChild.innerHTML = old_inner + '<input type="hidden" name="dict_flag['+tf_id+']" value="1"/>';
            byid('submit_button').disabled = false;
        }
    }
    req.open ('get', 'ajax/dict_reload.php?tf_id='+tf_id, true);
    req.send(null);
}
function dict_add_form(a_el) {
    var tbody = a_el.parentNode.parentNode.parentNode;
    var new_tr = document.createElement('tr');
    var new_td = document.createElement('td');
    var new_input = document.createElement('input');
    new_input.setAttribute('name', 'form_text[]');
    new_td.appendChild(new_input);
    new_tr.appendChild(new_td);
    new_td = document.createElement('td');
    new_input = document.createElement('input');
    new_input.setAttribute('name', 'form_gram[]');
    new_input.setAttribute('size', '40');
    new_td.appendChild(new_input);
    new_tr.appendChild(new_td);
    tbody.appendChild(new_tr);
}
function edit_gram(tr, gram_id) {
    tr.setAttribute('onClick', 'return false');
    tr = tr.parentNode.parentNode;
    tr.firstChild.innerHTML = '<input name="inner_id" size="10" maxlength="20" value="'+tr.firstChild.innerHTML+'"/>'
    tr.childNodes[1].innerHTML = '<input name="outer_id" size="10" maxlength="20" value="'+tr.childNodes[1].innerHTML+'"/>'
    tr.childNodes[2].innerHTML = '<input name="descr" size="20" value="'+tr.childNodes[2].innerHTML+'"/>'
    tr.lastChild.innerHTML += ' <input type="hidden" name="id" value="'+gram_id+'"/><input type="submit" value="Сохранить"/>';
}
function add_meta_option() {
    var tbody = byid('tbl_meta_options').lastChild;
    var new_tr = document.createElement('tr');
    var new_td = document.createElement('td');
    var new_input = document.createElement('input');
    new_input.setAttribute('name', 'option_names[]');
    new_td.appendChild(new_input);
    new_tr.appendChild(new_td);
    new_td = document.createElement('td');
    new_input = document.createElement('input');
    new_input.setAttribute('name', 'option_values[]');
    new_td.appendChild(new_input);
    new_tr.appendChild(new_td);
    new_td = document.createElement('td');
    new_input = document.createElement('input');
    new_input.setAttribute('name', 'option_default[]');
    new_input.setAttribute('value', 'default');
    new_td.appendChild(new_input);
    new_tr.appendChild(new_td);
    tbody.appendChild(new_tr);
}
