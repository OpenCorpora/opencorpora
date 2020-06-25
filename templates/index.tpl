{* Smarty *}
{extends file="common.tpl"}
{block name=content}
{$activepage='index'}

<h1>Открытый корпус</h1>
{if !$is_logged}
    <p>Здравствуйте!</p>
    <p>Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.</p>
    <p>Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно <a href="https://github.com/OpenCorpora/opencorpora">здесь</a> (да, код проекта открыт).</p>
{/if}
{if !$is_admin}
    <h2>Как я могу помочь прямо сейчас?</h2>
    <ul>
    <li>принять участие в <a href="./ner.php">разметке именованных сущностей</a>
        (см. <b><a href="ner.php?act=manual&id={$ner_tagset_id}" target="_blank">инструкцию</a></b>)</li>
    {if $is_logged}
    <table class="table"  style="width:800px; margin-top:7px;">
    <tr>
        <th>Текст</th>
        <th>Абзацев</th>
        <th>Готовность</th>
        <th>Всего готово: {$ner_tasks.ready}</th>
    </tr>
    {foreach from=array_slice($ner_tasks.books, 0, 3) item=book}
    <tr class="{if $book.started and $book.available}warning
               {elseif $book.started and !$book.available}success
               {elseif !$book.started and !$book.available}error
               {else}{/if}">
        <td>{$book.queue_num}</td>
        <td>{$book.num_par}</td>
        <td>{(100 * $book.ready_annot / ($book.num_par * $book.required_annots))|string_format:"%d"} %</td>
        <td>
            {if $book.started and $book.available}
                <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small btn-primary">Продолжить</a>
            {elseif $book.available and !$book.started}
                <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small">Размечать</a>
            {elseif !$book.available and !$book.started}
                <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small" disabled>Размечать</a>
            {else}
                <a href="/books.php?book_id={$book.id}&amp;act=ner" class="btn btn-small"><i class="icon-ok"></i> Просмотреть</a>
            {/if}
        </td>
    </tr>
    {/foreach}
    </table>
    {/if}
    <li>
        принять участие в снятии морфологической неоднозначности {if $is_logged}(см. <b><a href="manual.php">руководство</a></b>, <a href="tasks.php">задания</a>):{else}(<a href="/login.php">зарегистрируйтесь</a>, чтобы получить доступ к заданиям, а также прочтите <a href="/manual.php">руководство</a>){/if}
        <div>(всего мы получили уже <b>больше {($answer_count / 1000000)|string_format:"%.2f"} млн</b> ответов)</div>
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
{if $is_logged && ($user_permission_adder || $user_permission_dict || $user_permission_check_morph || $user_permission_syntax)}
    <div class="row">
        <div class="well nav-wrapper span5">
            <ul class="nav nav-list">
                <li class="nav-header">Свежие правки</li>
                <li><a href="/history.php">В разметке</a></li>
                <li><a href="/dict_history.php">В словаре</a></li>
                <li><a href="/comments.php">Последние комментарии</a></li>
                {if $user_permission_adder}<li class="nav-header">Тексты</li>
                    <li><a href='/books.php'>Редактор источников</a></li>
                    <li><a href='/add.php'>Добавить текст</a></li>
                {/if}
            </ul>
            {if $user_permission_adder}
                <form class='nav-well-form' method='post' action='/books.php?act=merge_sentences'>
                <label>Склеить предложения</label>
                <div class="controls"><input type="text" name='id1' class="input-small"> и&nbsp;<input type="text" name='id2' class="input-small"> <button type='submit' class="btn" onclick="return confirm('Вы уверены?')">Склеить</button></div>
                </form>
            {/if}
            {* Admin options *}
            {if $is_admin}
            <ul class="nav nav-list">
                <li class="nav-header">Функции администратора</li>
                <li><a href='/generator_cp.php'>Генерация данных для CPAN-токенизатора</a></li>
            </ul>
            {/if}
        </div>
        <div class="well nav-wrapper span5">
            <ul class="nav nav-list">
                {if $user_permission_check_morph}
                    <li class="nav-header">Задания на разметку (морфология)</li>
                    <li><a href='/pools.php?type=3'>Опубликованные задания</a></li>
                    <li><a href='/manual.php?what=morph_moderation'><i class="icon-info-sign"></i> Инструкция для модераторов</a></li>
                    <li><a href='/pools.php?act=types'>Типы пулов</a></li>
                    <li><a href='/qa.php?act=merge_fails'>То, что не удалось перелить</a></li>
                    <li><a href='/qa.php?act=useful_pools'>Пулы для приоритетной модерации</a></li>
                    <li><a href='?page=pool_charts'>Графики</a></li>
                    <li class="nav-header">Пулы, где я модератор</li>
                    <li><a href='/pools.php?type=5&amp;moder_id={$smarty.session.user_id}'>В работе</a></li>
                    <li><a href='/pools.php?type=6&amp;moder_id={$smarty.session.user_id}'>Готовые</a></li>
                    <li><a href='/pools.php?type=9&amp;moder_id={$smarty.session.user_id}'>В архиве</a></li>
                {/if}
                {if $user_permission_syntax}
                    <li class="nav-header">Синтаксис</li>
                    <li><a href='/syntax.php'>Тексты</a></li>
                {/if}
                {* TODO: special permission? *}
                    <li class="nav-header">Именованные сущности</li>
                    <li><a href='/ner.php'>Тексты</a></li>
                {if $user_permission_adder}
                    <li class="nav-header">Контроль качества</li>
                    <li><a href='/sources.php'>Координация заливки</a></li>
                    <li><a href='/tokenizer_monitor.php'>Мониторинг качества токенизатора</a></li>
                    <li><a href='/qa.php?act=tokenizer'>Странная токенизация</a></li>
                    <li><a href='/qa.php?act=good_sentences&no_zero'>Наименее омонимичные предложения</a></li>
                    <li><a href='/qa.php?act=sent_split'>Странное разделение на предложения</a></li>
                    <li><a href='/qa.php?act=empty_books'>Пустые тексты</a></li>
                    <li><a href='/qa.php?act=book_tags'>Ошибки в тегах текстов</a></li>
                    <li><a href='/qa.php?act=dl_urls'>Сохранённые копии источников</a></li>
                    <li><a href='/qa.php?act=unkn'><span class="label label-small label-success">new!</span> Словарные токены с UNKN</a></li>
                {/if}
            </ul>
        </div>
    </div>
{/if}
{/block}
