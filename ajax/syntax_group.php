<?php

/*
	Сюда приходит POST'ом следующее:

	act - что нужно делать
	  = newGroup - создать новую именную группу
	  	в POST будет массив из id токенов ($_POST['tokens']),
	  	тип группы ($_POST['type'])

	  	в ответ ожидается xml с полями error (boolean)
	  	и gid (id новой группы)

	  = setGroupRoot - установить вершину именной группы
	    $_POST['gid'] - id группы
	    $_POST['root_id'] - id токена-вершины

	  	в ответ ожидается xml с полем error (boolean)

	  TODO:
	  = copyGroups - скопировать все именные группы от одного пользователя
	  другому (модератору) (при этом затерев предыдущие)

	  = ??? - зафиксировать разметку именных групп в предложении



*/

switch ($_POST['act']) {
	// ...
}

header('Content-type: applciation/xml');
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
	<error>0</error>';