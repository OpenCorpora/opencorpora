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
    if (s_old.value==0) {
        updateLastParInfo(0);
        return;
    }
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
    var newVal = el.scrollLeft + offset;
    if (newVal < 0) newVal = 0;
    el.scrollLeft = newVal;
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
