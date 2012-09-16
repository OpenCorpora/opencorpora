{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}О проекте{/t}</h1>
<p>{t}Для решения многих лингвистических задач используются так называемые <em>текстовые корпуса</em> &mdash; специальным образом подобранные и структурированные коллекции текстов. Наиболее информативными являются <em>размеченные корпуса</em>, то есть такие, в которых частям текста приписана лингвистическая информация &mdash; например, каждое слово отнесено к той или иной части речи.{/t}</p>
<p>{t}Создание размеченного корпуса &mdash; очень трудоёмкий процесс, требующий времени и сил многих людей. По этой причине чаще всего размеченные корпуса создаются коллективами исследователей при государственных учреждениях, и таких корпусов не очень много. Однажды созданный корпус может быть использован многими исследователями для решения различных задач. Способы применения корпуса могут быть самыми разнообразными, в том числе и такими, о которых не думали его создатели. Чтобы корпус мог приносить максимальную отдачу научному сообществу, нужно, чтобы он был доступен не только для просмотра через предусмотренный его разработчиками интерфейс, но и для скачивания целиком на компьютер пользователя.{/t}</p>
<p>{t}OpenCorpora &mdash; это проект по созданию размеченного корпуса текстов силами сообщества. Корпус будет доступен бесплатно и в полном объёме (под лицензией CC-BY-SA). Мы создаём хранилище текстов, специально предназначенное для текстов с лингвистической разметкой, удобный интерфейс редактирования разметки и исправления ошибок, инструменты для контроля качества и стандарт разметки для русского языка.{/t}</p>
<p><a href="{$web_prefix}/?page=downloads" class="btn btn-primary btn-large btn-block" style="width: 385px;"><i class="icon icon-download icon-white" style="vertical-align: -1px;"></i> Downloads</a></p>
<form action="http://groups.google.com/group/opencorpora/boxsubscribe">
    <label for="email">{t}Подписаться на рассылку{/t}</label>
    <div class="input-append">
        <input name='email' type="text" class="span3" placeholder="Email">
        <button class="btn" type='submit' >Подписаться</button>
    </div>
</form>
<p><i class="icon-envelope"></i> Связаться с нами: <a href="mailto:opencorpora@opencorpora.org">email</a>, <a href="http://twitter.com/opencorpora">twitter</a>, <a href="http://vk.com/opencorpora">vk.com</a></p>
<h2>{t}Участники проекта{/t}</h2>
<table>
    <tr><td width='200px'>{t}Василий Алексеев{/t}</td></tr>
    <tr><td>{t}Светлана Бичинёва{/t}</td><td>{mailto address="bichineva@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>{t}Виктор Бочаров{/t}</td><td>{mailto address="bocharov@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>{t}Дмитрий Грановский{/t}</td><td>{mailto address="granovsky@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>{t}Мария Николаева{/t}</td></tr>
    <tr><td>{t}Наталья Остапук{/t}</td></tr>
    <tr><td>{t}Екатерина Протопопова{/t}</td></tr>
    <tr><td>{t}Мария Степанова{/t}</td><td>{mailto address="stepanova@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>{t}Алексей Суриков{/t}</td></tr>
</table>
<h2>{t}Мы благодарны:{/t}</h2>
    <p>{t}И.В. Азаровой{/t},
    {t}К.М. Аксарину{/t},
    {t}Анастасии Бодровой{/t},
    {t}Н.В. Борисову{/t},
    {t}Анне Дёгтевой{/t},
    {t}Сергею Дмитриеву{/t},
    {t}Эдуарду Клышинскому{/t},
    {t}Михаилу Коробову{/t},
    {t}Татьяне Ландо{/t},
    {t}О.В. Митрениной{/t},
    {t}О.А. Митрофановой{/t},
    {t}Лидии Пивоваровой{/t},
    {t}Марии Яворской{/t},
    {t}Ростиславу Яворскому{/t},
    {t}Е.В. Ягуновой{/t},
    {t}а также кафедре математической лингвистики СПбГУ{/t},
    {t}кафедре информационных систем в гуманитарных науках и искусстве СПбГУ{/t},
    {t}коллективу АОТ (<a href="http://www.aot.ru">aot.ru</a>){/t}<br>
    <b>{t}и <a href="{$web_prefix}/?page=stats#users">всем</a>, кто помогал и помогает добавлять и размечать тексты{/t}</b>.
</p>
<script type="text/javascript">
    $(document).ready(function(){
        $("a.tog").click(function(event){
            $(this).hide();
            $(this).closest('td').find('.hidden-block').show();
            event.preventDefault();
        });
    });
</script>
<h2>{t}Публикации{/t}</h2>
<table class="table">
<tr>
    <td colspan="3"><h4>2012</h4></td>
</tr>
<tr>
    <td>Сегментация текста в проекте Открытый корпус<br/>(доклад на конференции &laquo;Диалог&raquo;)</td>
    <td colspan='2'>скоро</td>
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
