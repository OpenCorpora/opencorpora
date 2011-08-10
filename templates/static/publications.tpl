{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<script type="text/javascript">
    $(document).ready(function(){
        $("a.tog").click(function(event){
            $(this).hide();
            $(this).closest('td').find('div').show();
            event.preventDefault();
        });
    });
</script>
<h1>{t}Публикации{/t}</h1>
<table cellpadding='8' cellspacing='0' border='1'>
<tr>
    <td colspan="3"><h2>2011</h2></td>
</tr>
<tr>
    <td>Корпусная лингвистика: проект Открытый корпус и место компьютерной лингвистики в народном хозяйстве<br/>(презентация для семинара в компании Witology, см. также <a href="http://vimeo.com/27140749">видео</a>)</td>
    <td><a href='{$web_prefix}/doc/presentations/2011_Witology.pdf'>pdf</a></td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td>Программное обеспечение для коллективной работы над морфологической разметкой корпуса<br/>(доклад на конференции &laquo;Корпусная лингвистика&ndash;2011&raquo;)<br/><a href='#' class='small hint tog'>выходные данные</a><div class='small hidden-block'><i>Бочаров В.В., Грановский Д.В.</i> Программное обеспечение для коллективной работы над морфологической разметкой корпуса // Труды международной конференции «Корпусная лингвистика &ndash; 2011». 27&ndash;29 июня 2011 г., Санкт-Петербург. — СПб.: С.-Петербургский гос. университет, Филологический факультет, 2011. — 348 с.</div></td>
    <td><a href='{$web_prefix}/doc/articles/2011_CorpusLing.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2010_CorpusLing.tex'>tex</a></td>
</tr>
<tr>
    <td rowspan="2">Инструменты контроля качества данных в проекте Открытый Корпус<br/>(для конференции &laquo;Диалог&raquo;)<br/><a href='#' class='small hint tog'>выходные данные</a><div class='small hidden-block'><i>Bocharov V., Bichineva S., Granovsky D., Ostapuk N., Stepanova M.</i> Quality assurance tools in the OpenCorpora project // Компьютерная лингвистика и интеллектуальные технологии: По материалам ежегодной Международной конференции «Диалог» (Бекасово, 25–29 мая 2011 г.). Вып. 10 (17). — М.: РГГУ, 2011.</div></td>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog.tex'>tex</a></td>
</tr>
<tr>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog_eng.pdf'>pdf</a> (англ.)</td>
    <td><a href='{$web_prefix}/doc/articles/2011_Dialog_eng.tex'>tex</a> (англ.)</td>
</tr>
<tr>
    <td>Как и зачем мы делаем Открытый корпус<br/>(презентация для Семинара по автоматической обработке текста, см. также <a href="http://video.yandex.ru/users/nataxane/view/2/">видео</a>)</td>
    <td><a href='{$web_prefix}/doc/presentations/2011_NLPSeminar.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/presentations/2011_NLPSeminar.tex'>tex</a></td>
</tr>
<tr>
    <td colspan="3"><h2>2010</h2></td>
</tr>
<tr>
    <td>Открытый корпус: принципы работы и перспективы<br/>(доклад на конференции &laquo;Интернет и современное общество&raquo;)<br/><a href='#' class='small hint tog'>выходные данные</a><div class='small hidden-block'><i>Грановский Д.В., Бочаров В.В., Бичинева С.В.</i> Открытый корпус: принципы работы и перспективы // Компьютерная лингвистика и развитие семантического поиска в Интернете: Труды научного семинара XIII Всероссийской объединенной конференции «Интернет и современное общество». Санкт-Петербург, 19–22 октября 2010 г. / Под ред. В.Ш. Рубашкина. — СПб., 2010. — 94 с.</div></td>
    <td><a href='{$web_prefix}/doc/articles/2010_IMS.pdf'>pdf</a></td>
    <td><a href='{$web_prefix}/doc/articles/2010_IMS.tex'>tex</a></td>
</tr>
</table>
{/block}
