{* Smarty *}
{extends file="common.tpl"}
{block name=content}
<h1>{t}Открытый корпус{/t}</h1>
{* Admin options *}
{if $is_admin}<a href='{$web_prefix}/users.php'>{t}Управление пользователями{/t}</a><br/><br/>{/if}
{if $user_permission_adder}<a href='{$web_prefix}/books.php'>{t}Редактор источников{/t}</a><br/>{/if}
{if $user_permission_dict}<a href='{$web_prefix}/dict.php'>{t}Редактор словаря{/t}</a><br/><br/>{/if}
{if $user_permission_adder}<a href='{$web_prefix}/add.php'>{t}Добавить текст{/t}</a><br/>{/if}
{if $user_permission_adder}
    <br/>
    <form class='inline' method='post' action='{$web_prefix}/books.php?act=merge_sentences'>Склеить предложения <input name='id1' size='5'/> и&nbsp;<input name='id2' size='5'/> <input type='submit' value='Склеить' onclick="return confirm('Вы уверены?')"/></form><br/>
    <form class='inline' method='post' action='{$web_prefix}/books.php?act=merge_tokens'>Склеить токены с&nbsp;<input name='id1' size='5'/> по&nbsp;<input name='id2' size='5'/> <input type='submit' value='Склеить' onclick="return confirm('Вы уверены?')"/></form><br/>
    <br/>
{/if}
{if $user_permission_adder}<h2>Контроль качества</h2>{/if}
{if $user_permission_adder}<a href='{$web_prefix}/qa.php?act=tokenizer'>Странная токенизация</a><br/>{/if}
{if $user_permission_adder}<a href='{$web_prefix}/qa.php?act=empty_books'>Пустые тексты</a><br/>{/if}
{if !$is_admin}
<p>{t}Здравствуйте!{/t}</p>
<p>{t}Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.{/t}</p>
<p>{t}Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно{/t} <a href="http://opencorpora.googlecode.com">{t}здесь{/t}</a> ({t}да, код проекта открыт{/t}).</p>
<h2>{t}Как я могу помочь?{/t}</h2>
<p>{t}Если вы:{/t}</p>
<ul>
<li>{t}интересуетесь компьютерной лингвистикой и хотите поучаствовать в настоящем проекте;{/t}</li>
<li>{t}хотя бы немного умеете программировать;{/t}</li>
<li>{t}не знаете ничего о лингвистике и программировании, но вам просто интересно{/t}</li>
</ul>
<p>&ndash; {t}пишите нам на{/t} <b>{mailto address="opencorpora@opencorpora.org" encode="javascript"}</b></p>
{/if}
{/block}
