<h1>О проекте</h1>
<?php $this->widget('bootstrap.widgets.TbMenu', array(
    'type'=>'tabs',
    'items'=>array(
        array('label'=>'Описание проекта', 'url'=>array('site/page','view'=>'about')),
        array('label'=>'Участники', 'url'=>array('site/page','view'=>'team'), 'active'=>true),
        array('label'=>'Публикации', 'url'=>array('site/page','view'=>'publications')),
        array('label'=>'FAQ', 'url'=>'#'),
    ),
)); ?>
<h2>Участники проекта</h2>
<table>
    <tr><td width='200px'>Василий Алексеев</td></tr>
    <tr><td>Светлана Бичинёва</td><td><?php echo CHtml::mailto("bichineva@opencorpora.org");?></td></tr>
    <tr><td>Виктор Бочаров</td><td><?php echo CHtml::mailto("bocharov@opencorpora.org");?></td></tr>
    <tr><td>Дмитрий Грановский</td><td><?php echo CHtml::mailto("granovsky@opencorpora.org");?></td></tr>
    <tr><td>Мария Николаева</td></tr>
    <tr><td>Наталья Остапук</td></tr>
    <tr><td>Екатерина Протопопова</td></tr>
    <tr><td>Мария Степанова</td><td><?php echo CHtml::mailto("stepanova@opencorpora.org");?></td></tr>
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
    <b>и <a href="index.php?page=stats#users">всем</a>, кто помогал и помогает добавлять и размечать тексты</b>.
</p>
