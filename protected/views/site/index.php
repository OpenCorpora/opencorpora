<?php
/* @var $this SiteController */
?>
<h1>Открытый корпус</h1>
<?php if(Yii::app()->user->isGuest):?>
    <p>Здравствуйте!</p>
    <p>Это сайт проекта &laquo;Открытый корпус&raquo; (OpenCorpora). Наша цель &ndash; создать морфологически, синтаксически и семантически размеченный корпус текстов на русском языке, в полном объёме доступный для исследователей и редактируемый пользователями.</p>
    <p>Мы начали работу в 2009 году, сейчас идёт разработка. Следить за тем, как мы продвигаемся, можно <a href="http://opencorpora.googlecode.com">здесь</a> (да, код проекта открыт).</p>
<?php endif;?>
<?php if(!Yii::app()->user->isAdmin):?>
    <h2>Как я могу помочь прямо сейчас?</h2>
    <ul>
    <li>
        принять участие в снятии морфологической неоднозначности <?php if(Yii::app()->user->isGuest):?>(см. <b><a href="manual.php">руководство</a></b>, <a href="tasks.php">задания</a>):<?php else:?>(<a href="login.php">зарегистрируйтесь</a>, чтобы получить доступ к заданиям, а также прочтите <a href="manual.php">руководство</a>)<?php endif;?>
        <div>(всего мы получили уже <b>больше <?php echo round($answer_count / 1000);?> тыс.</b> ответов)</div>
        <?php if(!Yii::app()->user->isGuest):?>
            <div>
                <?php if($available):?>
                    <table class="table" style="width:800px; margin-top:7px;">
                        <tr><th>Название пула</th><th>Доступно</th><th>&nbsp;</th></tr>
                        <?php foreach($available as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task.name);?></td>
                            <td><?php echo $task['num']; if($task['num_started']): echo $task['num_started'] . "начатых"; endif;?></td>
                            <td>
                                <?php if($task['num'] || $task['num_started']):?><a href="tasks.php?act=annot&amp;pool_id=<?php echo $task['id'];?>">взять на разметку</a><?php else:?>&nbsp;<?php endif;?>
                            </td>
                        </tr>
                    <?php endforeach;?>
                    </table>
                <?php else:?>
                    Нет доступных заданий.
                <?php endif;?>
            </div>
        <?php endif;?>
    </li>
    <li><a href="http://goo.gl/jm3ol">предложить нам</a> источник свободно доступных (на условиях CC-BY-SA или совместимых) текстов</li>
    <li>добавить тексты в корпус (напишите нам письмо на <?php CHtml::mailto("opencorpora@opencorpora.org");?>, мы расскажем как)</li>
    <li>помочь в разработке ПО корпуса и связанных с ним библиотек (тоже напишите нам письмо на <?php CHtml::mailto("opencorpora@opencorpora.org");?>)</li>
    <li>рассказать о нас всем вокруг</li>
    <li>сделать ещё что-нибудь полезное и интересное (разумеется, напишите нам письмо на <?php CHtml::mailto("opencorpora@opencorpora.org");?>)</li>
    </ul>
<?php endif;?>
<?php if(Yii::app()->user->isAdder || Yii::app()->user->isDict):?>
    <div class="row">
        <div class="well nav-wrapper span5">
            <ul class="nav nav-list">
                <li class="nav-header">Свежие правки</li>
                <li><a href="{$web_prefix}/history.php">В разметке</a></li>
                <li><a href="{$web_prefix}/dict_history.php">В словаре</a></li>
                <li><a href="{$web_prefix}/comments.php">Последние комментарии</a></li>
                <?php if(Yii::app()->user->isAdder):?><li class="nav-header">Тексты</li>
                    <li><a href='{$web_prefix}/books.php'>Редактор источников</a></li>
                    <li><a href='{$web_prefix}/add.php'>Добавить текст</a></li>
                <?php endif;?>
            </ul>
            <?php if(Yii::app()->user->isDict):?>
                <form class='nav-well-form' method='post' action='{$web_prefix}/books.php?act=merge_sentences'>
                <label>Склеить предложения</label>
                <div class="controls"><input type="text" name='id1' class="input-small"> и&nbsp;<input type="text" name='id2' class="input-small"> <button type='submit' class="btn" onclick="return confirm('Вы уверены?')">Склеить</button></div>
                </form>
            <?php endif;?>
        </div>
        <?php if(Yii::app()->user->isAdder || Yii::app()->user->isAdmin):?>
            <div class="well nav-wrapper span5">
                <ul class="nav nav-list">
                    <li class="nav-header">Контроль качества</li>
                    <li><a href='{$web_prefix}/sources.php'>Координация заливки</a></li>
                    <?php if(Yii::app()->user->isCheckMorph):?>
                        <li><a href='{$web_prefix}/pools.php?type=3'>Задания на разметку</a></li>
                    <?php endif;?>
                    <li><a href='{$web_prefix}/tokenizer_monitor.php'>Мониторинг качества токенизатора</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=tokenizer'>Странная токенизация</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=good_sentences&no_zero'>Наименее омонимичные предложения</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=sent_split'>Странное разделение на предложения</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=empty_books'>Пустые тексты</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=book_tags'>Ошибки в тегах текстов</a></li>
                    <li><a href='{$web_prefix}/qa.php?act=dl_urls'>Сохранённые копии источников</a></li>
                    <?php if(Yii::app()->user->isAdmin):?>
                        <li class="nav-header">Функции администратора</li>
                        <li><a href='{$web_prefix}/users.php'>Управление пользователями</a></li>
                        <li><a href='{$web_prefix}/generator_cp.php'>Генерация данных для CPAN-токенизатора</a></li>
                    <?php endif;?>
                </ul>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>
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
