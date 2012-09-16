{* Smarty *}
{extends file="common.tpl"}
{block name=content}
{$activepage='index'}
<h1>{t}Открытый корпус{/t}</h1>
{if !$is_logged}
<p>{t}Здравствуйте!{/t}</p>
<p>{t}Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.{/t}</p>
<p>{t}Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно{/t} <a href="http://opencorpora.googlecode.com">{t}здесь{/t}</a> ({t}да, код проекта открыт{/t}).</p>
{/if}
{if !$is_admin}
<h2>Как я могу помочь прямо сейчас?</h2>
<ul>
<li>
    принять участие в снятии морфологической неоднозначности {if $is_logged}(см. <a href="tasks.php">задания</a>):{else}(<a href="{$web_prefix}/login.php">зарегистрируйтесь</a>, чтобы получить доступ к заданиям){/if}
    <div>(всего мы получили уже <b>больше {($answer_count / 1000)|string_format:"%d"} тыс.</b> ответов)</div>
    {if $is_logged}<div><table class="table" style="width:800px; margin-top:7px;">
    <tr><th>Название пула</th><th>Доступно</th>{if $available}<th>&nbsp;</th>{/if}</tr>
    {foreach from=$available item=task}
    <tr>
        <td>{$task.name|htmlspecialchars}</td>
        <td>{$task.num}{if $task.num_started} +{$task.num_started} начатых{/if}</td>
        <td>
            {if $task.num || $task.num_started}<a href="tasks.php?act=annot&amp;pool_id={$task.id}">взять на разметку</a>{else}&nbsp;{/if}
        </td>
    </tr>
    {foreachelse}
    <tr><td colspan='2'>Нет доступных заданий.</td></tr>
    {/foreach}
    </table></div>{/if}</li>
<li><a href="http://goo.gl/jm3ol">предложить нам</a> источник свободно доступных (на условиях CC-BY-SA или совместимых) текстов</li>
<li>добавить тексты в корпус (напишите нам письмо на {mailto address="opencorpora@opencorpora.org" encode="javascript"}, мы расскажем как)</li>
<li>помочь в разработке ПО корпуса и связанных с ним библиотек (тоже напишите нам письмо на {mailto address="opencorpora@opencorpora.org" encode="javascript"})</li>
<li>рассказать о нас всем вокруг</li>
<li>сделать ещё что-нибудь полезное и интересное (разумеется, напишите нам письмо на {mailto address="opencorpora@opencorpora.org" encode="javascript"})</li>
</ul>
{/if}
{if $is_logged && ($user_permission_adder || $user_permission_dict)}
<p>Свежие правки: <a href="{$web_prefix}/history.php">в разметке</a> / <a href="{$web_prefix}/dict_history.php">в словаре</a>. <a href="{$web_prefix}/comments.php">Последние комментарии</a>.</p>
<h3>А ещё вот есть</h3>
{/if}
{* Admin options *}
{if $is_admin}
<a href='{$web_prefix}/users.php'>{t}Управление пользователями{/t}</a><br/><br/>
<a href='{$web_prefix}/generator_cp.php'>{t}Генерация данных для CPAN-токенизатора{/t}</a><br/><br/>
{/if}
{if $user_permission_adder}<a href='{$web_prefix}/books.php'>{t}Редактор источников{/t}</a><br/>{/if}
{if $user_permission_dict}<a href='{$web_prefix}/dict.php'>{t}Редактор словаря{/t}</a><br/><br/>{/if}
{if $user_permission_adder}<a href='{$web_prefix}/add.php'>{t}Добавить текст{/t}</a><br/>{/if}
{if $user_permission_adder}
    <br/>
    <form class='form-inline' method='post' action='{$web_prefix}/books.php?act=merge_sentences'>Склеить предложения <input type="text" name='id1' class="input-small"> и&nbsp;<input type="text" name='id2' class="input-small"> <button type='submit' class="btn" onclick="return confirm('Вы уверены?')">Склеить</button></form>
{/if}
{if $user_permission_adder}<h3>Контроль качества</h3>{/if}
{if $user_permission_adder}
<a href='{$web_prefix}/sources.php'>Координация заливки</a><br/>
{/if}
{if $user_permission_check_morph}
<a href='{$web_prefix}/pools.php?type=3'>Задания на разметку</a><br/>
{/if}
{if $user_permission_adder}
<br/>
<a href='{$web_prefix}/tokenizer_monitor.php'>Мониторинг качества токенизатора</a><br/>
<a href='{$web_prefix}/qa.php?act=tokenizer'>Странная токенизация</a><br/>
<a href='{$web_prefix}/qa.php?act=sent_split'>Странное разделение на предложения</a><br/>
<a href='{$web_prefix}/qa.php?act=empty_books'>Пустые тексты</a><br/>
<a href='{$web_prefix}/qa.php?act=book_tags'>Ошибки в тегах текстов</a><br/>
<a href='{$web_prefix}/qa.php?act=dl_urls'>Сохранённые копии источников</a><br/>
{/if}
{/block}
