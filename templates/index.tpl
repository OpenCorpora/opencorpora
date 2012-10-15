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
        {if $is_logged}
            <div>
                <table class="table" style="width:800px; margin-top:7px;">
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
                </table>
            </div>
        {/if}
    </li>
    <li><a href="http://goo.gl/jm3ol">предложить нам</a> источник свободно доступных (на условиях CC-BY-SA или совместимых) текстов</li>
    <li>добавить тексты в корпус (напишите нам письмо на {mailto address="opencorpora@opencorpora.org" encode="javascript"}, мы расскажем как)</li>
    <li>помочь в разработке ПО корпуса и связанных с ним библиотек (тоже напишите нам письмо на {mailto address="opencorpora@opencorpora.org" encode="javascript"})</li>
    <li>рассказать о нас всем вокруг</li>
    <li>сделать ещё что-нибудь полезное и интересное (разумеется, напишите нам письмо на {mailto address="opencorpora@opencorpora.org" encode="javascript"})</li>
    </ul>
{/if}
{if $is_logged && ($user_permission_adder || $user_permission_dict)}
    <div class="row">
        <div class="well nav-wrapper span5">
            <ul class="nav nav-list">
                <li class="nav-header">Свежие правки</li>
                <li><a href="{$web_prefix}/history.php">В разметке</a></li>
                <li><a href="{$web_prefix}/dict_history.php">В словаре</a></li>
                <li><a href="{$web_prefix}/comments.php">Последние комментарии</a></li>
                {if $user_permission_adder}<li class="nav-header">Тексты</li>
                    <li><a href='{$web_prefix}/books.php'>{t}Редактор источников{/t}</a></li>
                    <li><a href='{$web_prefix}/add.php'>{t}Добавить текст{/t}</a></li>
                {/if}
            </ul>
            {if $user_permission_adder}
                <form class='nav-well-form' method='post' action='{$web_prefix}/books.php?act=merge_sentences'>
                <label>Склеить предложения</label>
                <div class="controls"><input type="text" name='id1' class="input-small"> и&nbsp;<input type="text" name='id2' class="input-small"> <button type='submit' class="btn" onclick="return confirm('Вы уверены?')">Склеить</button></div>
                </form>
            {/if}
        </div>
        {if $user_permission_adder || $is_admin}
            <div class="well nav-wrapper span5">
                <ul class="nav nav-list">
                    <li class="nav-header">Контроль качества</li>
                    <li><a href='{$web_prefix}/sources.php'>Координация заливки</a></li>
                    {if $user_permission_check_morph}
                        <li><a href='{$web_prefix}/pools.php?type=3'>Задания на разметку</a></li>
                    {/if}
                    <li><a href='{$web_prefix}/tokenizer_monitor.php'>Мониторинг качества токенизатора</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=tokenizer'>Странная токенизация</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=good_sentences'>Наименее омонимичные предложения</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=sent_split'>Странное разделение на предложения</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=empty_books'>Пустые тексты</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=book_tags'>Ошибки в тегах текстов</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=dl_urls'>Сохранённые копии источников</a></li>
                    {* Admin options *}
                    {if $is_admin}
                        <li class="nav-header">Функции администратора</li>
                        <li><a href='{$web_prefix}/users.php'>{t}Управление пользователями{/t}</a></li>
                        <li><a href='{$web_prefix}/generator_cp.php'>{t}Генерация данных для CPAN-токенизатора{/t}</a></li>
                    {/if}
                </ul>
            </div>
        {/if}
    </div>
{/if}
{/block}
