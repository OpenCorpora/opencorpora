{* Smarty *}
{extends file="common.tpl"}
{block name=content}
{$activepage='index'}
<h1>Открытый корпус</h1>
{if !$is_logged}
    <p>Здравствуйте!</p>
    <p>Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.</p>
    <p>Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно <a href="http://opencorpora.googlecode.com">здесь</a> (да, код проекта открыт).</p>
{/if}
{if !$is_admin}
    <h2>Как я могу помочь прямо сейчас?</h2>
    <ul>
    <li>
        принять участие в снятии морфологической неоднозначности {if $is_logged}(см. <b><a href="manual.php">руководство</a></b>, <a href="tasks.php">задания</a>):{else}(<a href="{$web_prefix}/login.php">зарегистрируйтесь</a>, чтобы получить доступ к заданиям, а также прочтите <a href="{$web_prefix}/manual.php">руководство</a>){/if}
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
{if $is_logged && ($user_permission_adder || $user_permission_dict || $user_permission_check_morph)}
    <div class="row">
        <div class="well nav-wrapper span5">
            <ul class="nav nav-list">
                <li class="nav-header">Свежие правки</li>
                <li><a href="{$web_prefix}/history.php">В разметке</a></li>
                <li><a href="{$web_prefix}/dict_history.php">В словаре</a></li>
                <li><a href="{$web_prefix}/comments.php">Последние комментарии</a></li>
                {if $user_permission_adder}<li class="nav-header">Тексты</li>
                    <li><a href='{$web_prefix}/books.php'>Редактор источников</a></li>
                    <li><a href='{$web_prefix}/add.php'>Добавить текст</a></li>
                {/if}
            </ul>
            {if $user_permission_adder}
                <form class='nav-well-form' method='post' action='{$web_prefix}/books.php?act=merge_sentences'>
                <label>Склеить предложения</label>
                <div class="controls"><input type="text" name='id1' class="input-small"> и&nbsp;<input type="text" name='id2' class="input-small"> <button type='submit' class="btn" onclick="return confirm('Вы уверены?')">Склеить</button></div>
                </form>
            {/if}
        </div>
        <div class="well nav-wrapper span5">
            <ul class="nav nav-list">
                {if $user_permission_adder}
                    <li class="nav-header">Контроль качества</li>
                    <li><a href='{$web_prefix}/sources.php'>Координация заливки</a></li>
                    <li><a href='{$web_prefix}/tokenizer_monitor.php'>Мониторинг качества токенизатора</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=tokenizer'>Странная токенизация</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=good_sentences&no_zero'>Наименее омонимичные предложения</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=sent_split'>Странное разделение на предложения</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=empty_books'>Пустые тексты</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=book_tags'>Ошибки в тегах текстов</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=dl_urls'>Сохранённые копии источников</a></li>
                {/if}
                {if $user_permission_check_morph}
                    <li class="nav-header">Задания на разметку</li>
                    <li><a href='{$web_prefix}/pools.php?type=3'>Опубликованные задания</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=merge_fails'>То, что не удалось перелить</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=useful_pools'><span class="small bggreen">new!</span> Пулы для приоритетной модерации</a></li>
                    <li><a href='?page=pool_charts'>Графики</a></li>
                    <li class="nav-header">Пулы, где я модератор</li>
                    <li><a href='{$web_prefix}/pools.php?type=5&amp;moder_id={$smarty.session.user_id}'>В работе</a></li>
                    <li><a href='{$web_prefix}/pools.php?type=6&amp;moder_id={$smarty.session.user_id}'>Готовые</a></li>
                    <li><a href='{$web_prefix}/pools.php?type=9&amp;moder_id={$smarty.session.user_id}'>В архиве</a></li>
                {/if}
                {* Admin options *}
                {if $is_admin}
                    <li class="nav-header">Функции администратора</li>
                    <li><a href='{$web_prefix}/users.php'>Управление пользователями</a></li>
                    <li><a href='{$web_prefix}/generator_cp.php'>Генерация данных для CPAN-токенизатора</a></li>
                {/if}
            </ul>
        </div>
    </div>
{/if}
<!-- VK api -->
<script type="text/javascript" src="//vk.com/js/api/openapi.js?59"></script>
<div id="fb-root"></div>
<script>
// FB init
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1&appId=459706510739356";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// VK init
VK.init({ apiId: 3183828, onlyWidgets: true });
</script>
<div class="social-index-block row">
    <div class="span5">
        <div id="vk_like"></div>
        <script type="text/javascript">
        VK.Widgets.Like("vk_like", { type: "full" });
        </script>
    </div>
    <div class="span5">
        <div class="fb-like" data-send="false" data-width="" data-show-faces="false"></div>
    </div>
</div>
{/block}
