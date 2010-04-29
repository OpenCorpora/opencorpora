<?php
function books_mainpage() {
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` WHERE `parent_id`=0 ORDER BY `book_name`");
    $num = sql_num_rows($res);
    $out = "Всего книг: <b>$num</b>, <a href='#' class='toggle' onClick='show(byid(\"book_add\")); return false;'>добавить</a>: <form id='book_add' style='display:none' method='post' action='?act=add'><input name='book_name' size='30' maxlength='100' value='&lt;Название&gt;'/><input type='hidden' name='book_parent' value='0'/><br/><input type='submit' value='Добавить'/></form>";
    $out .= "<ul>\n";
    while ($r = sql_fetch_array($res)) {
       $out .= "<li><a href='?book_id=".$r['book_id']."'>".$r['book_name']."</a></li>\n";
    }
    $out .= '</ul>';
    return $out;
}
function books_page($book_id) {
    $r = sql_fetch_array(sql_query("SELECT * FROM `books` WHERE `book_id`=$book_id"));
    $out = '<h2>'.$r['book_name']."</h2>\n";
    $out .= "<form action='?act=rename' method='post' class='inline'>Переименовать в: <input type='hidden' name='book_id' value='$book_id'/><input name='new_name' value='".htmlspecialchars($r['book_name'])."'/>&nbsp;&nbsp;<input type='submit' value='Переименовать'/></form>\n";
    $out .= "ИЛИ <form action='?act=move' method='post' class='inline'>Переместить в: <input type='hidden' name='book_id' value='$book_id'/><select name='book_to' onChange='document.forms[1].submit()'>\n<option value='0'>&lt;root&gt;</option>\n".books_get_select()."</select></form>";
    $res = sql_query("SELECT book_id, book_name FROM books WHERE parent_id=$book_id");
    if (sql_num_rows($res)==0) {
        $out .= '<p>Разделов нет.</p>';
    } else {
        $out .= '<h3>Разделы</h3><ul>';
        while($r = sql_fetch_array($res)) {
            $out .= '<li><a href="?book_id='.$r['book_id'].'">'.htmlspecialchars($r['book_name']).'</a></li>';
        }
        $out .= '</ul>';
    }
    return $out;
}
function books_add($name, $parent_id=0) {
    #TODO: check if the name is empty
    if (sql_query("INSERT INTO `books` VALUES(NULL, '$name', '$parent_id')")) {
        header("Location:books.php?book_id=$parent_id");
    } else {
        #some error message
    }
}
function books_move($book_id, $to_id) {
    if ($book_id == $to_id) {
        header("Location:books.php?book_id=$book_id");
        return;
    }
    if (sql_query("UPDATE `books` SET `parent_id`='$to_id' WHERE `book_id`=$book_id LIMIT 1")) {
        header("Location:books.php?book_id=$to_id");
    } else {
        #some error message
    }
}
function books_rename($book_id, $name) {
    #TODO: check if the name is empty
    if (sql_query("UPDATE `books` SET `book_name`='$name' WHERE `book_id`=$book_id LIMIT 1")) {
        header("Location:books.php?book_id=$book_id");
    } else {
        #some error meassage
    }
}
function books_get_select($parent = -1) {
    $out = '';
    $pg = $parent > -1 ? "WHERE `parent_id`=$parent " : '';
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` ".$pg."ORDER BY `book_name`", 0);
    while($r = sql_fetch_array($res)) {
        $out .= "<option value='".$r['book_id']."'>".$r['book_name']."</option>";
    }
    return $out;
}
?>
