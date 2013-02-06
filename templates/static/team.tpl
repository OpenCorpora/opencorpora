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
    <tr><td width='200px'>Василий Алексеев</td></tr>
    <tr><td>Светлана Бичинёва</td><td>{mailto address="bichineva@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Виктор Бочаров</td><td>{mailto address="bocharov@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Дмитрий Грановский</td><td>{mailto address="granovsky@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Мария Николаева</td></tr>
    <tr><td>Наталья Остапук</td></tr>
    <tr><td>Екатерина Протопопова</td></tr>
    <tr><td>Мария Степанова</td><td>{mailto address="stepanova@opencorpora.org" encode="javascript"}</td></tr>
    <tr><td>Алексей Суриков</td></tr>
</table>
<h2>Мы благодарны:</h2>
    <p>И.В. Азаровой,
    К.М. Аксарину,
    Анастасии Бодровой,
    Н.В. Борисову,
    Дмитрию Гайворонскому,
    Алёне Гилевской,
    Анне Дёгтевой,
    Сергею Дмитриеву,
    Эдуарду Клышинскому,
    Михаилу Коробову,
    Татьяне Ландо,
    Анастасии Львовой,
    О.В. Митрениной,
    О.А. Митрофановой,
    Лидии Пивоваровой,
    Лине Романовой,
    Игорю Турченко,
    Дмитрию Усталову,
    Марии Холодиловой,
    Марии Яворской,
    Ростиславу Яворскому,
    Е.В. Ягуновой,
    а также кафедре математической лингвистики СПбГУ,
    кафедре информационных систем в гуманитарных науках и искусстве СПбГУ,
    коллективу АОТ (<a href="http://www.aot.ru">aot.ru</a>)<br>
    <b>и <a href="{$web_prefix}/?page=stats#users">всем</a>, кто помогал и помогает добавлять и размечать тексты</b>.
</p>
{/block}
