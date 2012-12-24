{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<script type="text/javascript">
    $(document).ready(function(){
        $("a.tog").click(function(event){
            $(this).hide();
            $(this).closest('td').find('.hidden-block').show();
            event.preventDefault();
        });
    });
</script>
<h1>О проекте</h1>
<ul class="nav nav-tabs">
    <li><a href="{$web_prefix}/?page=about">Описание проекта</a></li>
    <li><a href="{$web_prefix}/?page=team">Участники</a></li>
    <li class="active"><a href="{$web_prefix}/?page=publications">Публикации</a></li>
    <li><a href="{$web_prefix}/?page=faq">FAQ</a></li>
</ul>
<h2>Публикации</h2>
<table class="table">
<tr>
    <td colspan="3"><h4>2012</h4></td>
</tr>
<tr>
    <td>&laquo;Он видел их семью своими глазами&raquo;<br/>(пост на Хабрахабре)</td>
    <td colspan='2'><a href="http://habrahabr.ru/post/152799">HTML</a></td>
</tr>
<tr>
    <td>
        <p>Сегментация текста в проекте Открытый корпус<br/>(доклад на конференции &laquo;Диалог&raquo;)</p>
        <a href='#' class='pseudo tog'>выходные данные</a>
        <div class='hidden-block'><i>Бочаров В.В., Алексеева С.В., Грановский Д.В., Остапук Н.А., Степанова М.Е., Суриков А.В.</i> Сегментация текста в проекте &laquo;Открытый корпус&raquo; // Компьютерная лингвистика и интеллектуальные технологии: По материалам ежегодной Международной конференции «Диалог» (Бекасово, 30&nbsp;мая–3&nbsp;июня 2012 г.). Вып. 11 (18). — М.: РГГУ, 2012.</div>
    </td>
    <td colspan='2'><a href='{$web_prefix}/doc/articles/2012_Dialog.pdf'>pdf</a></td>
</tr>
<tr>
    <td>
        <p>Вероятностная модель токенизации в проекте Открытый корпус<br/>(доклад на 15-м семинаре &laquo;Новые информационные технологии в автоматизированных системах&raquo;)</p>
        <a href='#' class='pseudo tog'>выходные данные</a>
        <div class='hidden-block'><i>Бочаров В.В., Грановский Д.В., Суриков А.В.</i> Вероятностная модель токенизации в проекте Открытый корпус // Новые информационные технологии в автоматизированных системах: материалы пятнадцатого научно-практического семинара. Моск. гос. ин-т электроники и математики. — М., 2012.</div></td>
    <td><a href='{$web_prefix}/doc/articles/2012_MIEM.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2012_MIEM.tex'>TeX</a></td>
</tr>
<tr>
    <td colspan="3"><h4>2011</h4></td>
</tr>
<tr>
    <td>Корпусная лингвистика: проект Открытый корпус и место компьютерной лингвистики в народном хозяйстве<br/>(презентация для семинара в компании Witology, см. также <a href="http://vimeo.com/27140749">видео</a>)</td>
    <td><a href='{$web_prefix}/doc/presentations/2011_Witology.pdf'>pdf</a></td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td>
        <p>Программное обеспечение для коллективной работы над морфологической разметкой корпуса<br/>(доклад на конференции &laquo;Корпусная лингвистика&ndash;2011&raquo;)</p>
        <a href='#' class='pseudo tog'>выходные данные</a>
        <div class='hidden-block'><i>Бочаров В.В., Грановский Д.В.</i> Программное обеспечение для коллективной работы над морфологической разметкой корпуса // Труды международной конференции «Корпусная лингвистика &ndash; 2011». 27&ndash;29 июня 2011 г., Санкт-Петербург. — СПб.: С.-Петербургский гос. университет, Филологический факультет, 2011. — 348 с.</div>
    </td>
    <td><a href='{$web_prefix}/doc/articles/2011_CorpusLing.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2011_CorpusLing.tex'>TeX</a></td>
</tr>
<tr>
    <td rowspan="2">
        <p>Инструменты контроля качества данных в проекте Открытый Корпус<br/>(доклад на конференции &laquo;Диалог&raquo;)</p>
        <a href='#' class='pseudo tog'>выходные данные</a>
        <div class='hidden-block'><i>Bocharov V., Bichineva S., Granovsky D., Ostapuk N., Stepanova M.</i> Quality assurance tools in the OpenCorpora project // Компьютерная лингвистика и интеллектуальные технологии: По материалам ежегодной Международной конференции «Диалог» (Бекасово, 25–29 мая 2011 г.). Вып. 10 (17). — М.: РГГУ, 2011.</div>
    </td>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog.tex'>TeX</a></td>
</tr>
<tr>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog_eng.pdf'>pdf</a> (англ.)</td>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog_eng.tex'>TeX</a> (англ.)</td>
</tr>
<tr>
    <td>Как и зачем мы делаем Открытый корпус<br/>(презентация для Семинара по автоматической обработке текста, см. также <a href="http://video.yandex.ru/users/nataxane/view/2/">видео</a>)</td>
    <td><a href='{$web_prefix}/doc/presentations/2011_NLPSeminar.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/presentations/2011_NLPSeminar.tex'>TeX</a></td>
</tr>
<tr>
    <td colspan="3"><h4>2010</h4></td>
</tr>
<tr>
    <td>
        <p>Открытый корпус: принципы работы и перспективы<br/>(доклад на конференции &laquo;Интернет и современное общество&raquo;)</p>
        <a href='#' class='pseudo tog'>выходные данные</a>
        <div class='hidden-block'><i>Грановский Д.В., Бочаров В.В., Бичинева С.В.</i> Открытый корпус: принципы работы и перспективы // Компьютерная лингвистика и развитие семантического поиска в Интернете: Труды научного семинара XIII Всероссийской объединенной конференции «Интернет и современное общество». Санкт-Петербург, 19–22 октября 2010 г. / Под ред. В.Ш. Рубашкина. — СПб., 2010. — 94 с.</div>
    </td>
    <td><a href='{$web_prefix}/doc/articles/2010_IMS.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2010_IMS.tex'>TeX</a></td>
</tr>
</table>
{/block}
