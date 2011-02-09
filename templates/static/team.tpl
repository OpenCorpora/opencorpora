{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>{t}Участники проекта{/t}</h1>
<table border='0' cellpadding='4'>
<tr><td width='200px'>{t}Василий Алексеев{/t}</td></tr>
<tr><td>{t}Светлана Бичинёва{/t}</td><td>{mailto address=bichineva@opencorpora.org encode=javascript}</tr>
<tr><td>{t}Виктор Бочаров{/t}</td><td>{mailto address=bocharov@opencorpora.org encode=javascript}</td></tr>
<tr><td>{t}Дмитрий Грановский{/t}</td><td>{mailto address=granovsky@opencorpora.org encode=javascript}</td></tr>
<tr><td>{t}Наталья Остапук{/t}</td></tr>
<tr><td>{t}Мария Степанова{/t}</td></tr>
</table>
<h2>{t}Мы благодарны:{/t}</h2>
<ul>
<li>{t}И.В. Азаровой{/t},
<li>{t}К.М. Аксарину{/t},
<li>{t}Н.В. Борисову{/t},
<li>{t}Татьяне Ландо{/t},
<li>{t}О.В. Митрениной{/t},
<li>{t}О.А. Митрофановой{/t},
<li>{t}Марии Николаевой{/t},
<li>{t}Лидии Пивоваровой{/t},
<li>{t}а также кафедре математической лингвистики СПбГУ{/t},
<li>{t}кафедре информационных систем в гуманитарных науках и искусстве СПбГУ{/t}
<li>{t}и коллективу АОТ (aot.ru){/t}.
</ul>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
