{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}О проекте{/t}</h1>
<ul class="nav nav-tabs">
    <li><a href="{$web_prefix}/?page=about">Описание проекта</a></li>
    <li class="active"><a href="{$web_prefix}/?page=team">Участники</a></li>
    <li><a href="{$web_prefix}/?page=publications">Публикации</a></li>
    <li><a href="{$web_prefix}/?page=faq">FAQ</a></li>
</ul>
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
    {t}Лине Романовой{/t},
    {t}Марии Яворской{/t},
    {t}Ростиславу Яворскому{/t},
    {t}Е.В. Ягуновой{/t},
    {t}а также кафедре математической лингвистики СПбГУ{/t},
    {t}кафедре информационных систем в гуманитарных науках и искусстве СПбГУ{/t},
    {t}коллективу АОТ (<a href="http://www.aot.ru">aot.ru</a>){/t}<br>
    <b>{t}и <a href="{$web_prefix}/?page=stats#users">всем</a>, кто помогал и помогает добавлять и размечать тексты{/t}</b>.
</p>
{/block}
