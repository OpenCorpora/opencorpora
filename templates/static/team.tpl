{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>О проекте</h1>
<ul class="nav nav-tabs">
    <li><a href="{$web_prefix}/?page=about">Описание проекта</a></li>
    <li class="active"><a href="{$web_prefix}/?page=team">Участники</a></li>
    <li><a href="{$web_prefix}/?page=publications">Публикации</a></li>
    <li><a href="{$web_prefix}/?page=faq">FAQ</a></li>
</ul>
<h2>Участники проекта</h2>
<table>
    <tr class='muted'><td width='200px'>Василий Алексеев</td><td>2010&ndash;2011</td></tr>
    <tr><td>Светлана Алексеева</td><td>{mailto address="bichineva@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Виктор Бочаров</td><td>{mailto address="bocharov@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Дмитрий Грановский</td><td>{mailto address="granovsky@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Мария Николаева</td></tr>
    <tr class='muted'><td>Наталья Остапук</td><td>2010&ndash;2011</td></tr>
    <tr><td>Екатерина Протопопова</td></tr>
    <tr class='muted'><td>Мария Степанова</td><td>2010&ndash;2012</td></tr>
    <tr><td>Алексей Суриков</td></tr>
    <tr><td>Александр Чучунков</td></tr>
</table>
<h2>Мы благодарны:</h2>
    <p>И.В.&nbsp;Азаровой,
    К.М.&nbsp;Аксарину,
    Анастасии Бодровой,
    Н.В.&nbsp;Борисову,
    Светлане Вольской,
    Дмитрию Гайворонскому,
    Алёне Гилевской,
    Анне Дёгтевой,
    Сергею Дмитриеву,
    Татьяне Игнатовой,
    Эдуарду Клышинскому,
    Михаилу Коробову,
    Андрею Крижановскому,
    Ирине Крыловой,
    Ольге Ксендзовской,
    Татьяне Ландо,
    Артёму Луканину,
    Анастасии Львовой,
    Ольге Ляшевской,
    О.В.&nbsp;Митрениной,
    О.А.&nbsp;Митрофановой,
    Лидии Пивоваровой,
    Лине Романовой,
    Сергею Слепову,
    Игорю Турченко,
    Дмитрию Усталову,
    Марии Холодиловой,
    Марии Яворской,
    Ростиславу Яворскому,
    Е.В.&nbsp;Ягуновой,
    а также Яндексу,
    кафедре математической лингвистики СПбГУ,
    кафедре информационных систем в гуманитарных науках и искусстве СПбГУ,
    коллективу АОТ (<a href="http://www.aot.ru">aot.ru</a>)<br>
    <b>и <a href="{$web_prefix}/?page=stats#users">всем</a>, кто помогал и помогает добавлять и размечать тексты</b>.
</p>
{/block}
