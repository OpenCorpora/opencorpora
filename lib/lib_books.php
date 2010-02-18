<?php
function books_mainpage() {
    $res = sql_query("SELECT `book_id`, `book_name` FROM `books` WHERE `parent_id`=0 ORDER BY `book_name`");
    $num = sql_num_rows($res);
    $out = "Всего книг: <b>$num</b>, <a href='#' class='toggle' onClick='document.getElementById(\"book_add\").style.display=\"block\"; return false;'>добавить</a>: <form id='book_add' style='display:none' method='post' action='?act=add'><input name='book_name' size='30' maxlength='100' value='&lt;Название&gt;'/><input type='hidden' name='book_parent' value='0'/><br/><input type='submit' value='Добавить'/></form>";
    $out .= "<ul>\n";
    while ($r = sql_fetch_array($res)) {
       $out .= "<li><a href='?book_id=".$r['book_id']."'>".$r['book_name']."</a></li>\n";
    }
    $out .= '</ul>';
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
    if (sql_query("UPDATE `books` SET `parent_id`='$to_id' WHERE `book_id`=$book_id LIMIT 1")) {
        header("Location:books.php?book_id=$to_id");
    } else {
        #some error message
    }
}
?>
