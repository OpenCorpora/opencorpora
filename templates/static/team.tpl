{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}Участники проекта{/t}</h1>
<table border='0' cellpadding='4'>
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
<ul>
<li>{t}И.В. Азаровой{/t},
<li>{t}К.М. Аксарину{/t},
<li>{t}Анастасии Бодровой{/t},
<li>{t}Н.В. Борисову{/t},
<li>{t}Анне Дёгтевой{/t},
<li>{t}Сергею Дмитриеву{/t},
<li>{t}Эдуарду Клышинскому{/t},
<li>{t}Михаилу Коробову{/t},
<li>{t}Татьяне Ландо{/t},
<li>{t}О.В. Митрениной{/t},
<li>{t}О.А. Митрофановой{/t},
<li>{t}Лидии Пивоваровой{/t},
<li>{t}Марии Яворской{/t},
<li>{t}Ростиславу Яворскому{/t},
<li>{t}Е.В. Ягуновой{/t},
<li>{t}а также кафедре математической лингвистики СПбГУ{/t},
<li>{t}кафедре информационных систем в гуманитарных науках и искусстве СПбГУ{/t},
<li>{t}коллективу АОТ (<a href="http://www.aot.ru">aot.ru</a>){/t}
<li><b>{t}и <a href="{$web_prefix}/?page=stats#users">всем</a>, кто помогал и помогает добавлять и размечать тексты{/t}</b>.</li>
</ul>
{/block}
