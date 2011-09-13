{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>Форматы экспорта данных</h1>
<h2>Формат экспорта словаря (версия 0.81)</h2>
<p>Словарь представляет собой файл XML в кодировке utf-8.</p>
<p>Всё содержимое обёрнуто в корневой тег <code>&lt;dictionary&gt;</code>, атрибуты которого указывают на версию формата (сейчас 0.81) и номер ревизии словаря на момент экспорта. Внутри выделяются 4 секции, описывающие граммемы, собственно леммы, типы связей между ними и сами связи.</p>
<h3>Описание граммем</h3>
<p>В секции <code>&lt;grammems&gt;</code> перечислен весь инвентарь граммем в виде: <code>&lt;grammem parent="pid"&gt;gid&lt;/grammem&gt;</code>, где gid &ndash; идентификатор граммемы (например, NOUN), а pid &ndash; идентификатор граммемы, являющейся родительской по отношению к данной (например, POST); pid может быть пустым.</p>
<h3>Описание лемм</h3>
<p>Леммы перечисляются в секции <code>&lt;lemmata&gt;</code>. Каждая лемма описывается элементом <code>&lt;lemma&gt;</code> с атрибутами <code>id</code> (числовой идентификатор леммы, не меняется никогда) и <code>rev</code> (номер последней ревизии этой леммы). Внутри этого элемента находится содержимое последней ревизии:</p>
<ul>
<li>элемент <code>&lt;l&gt;</code>, содержащий текст леммы в атрибуте <code>t</code> и набор граммем, относящихся к лемме, в элементах <code>&lt;g&gt;</code>;</li>
<li>набор элементов <code>&lt;f&gt;</code>, описывающий все словоформы данной леммы: текст словоформы содержится в атрибуте <code>t</code>, а набор граммем &ndash; в наборе элементов <code>&lt;g&gt;</code>.</li>
</ul>
<h3>Описание типов связей</h3>
<p>В секции <code>&lt;link_types&gt;</code> перечисляются все возможные типы связей между леммами в виде <code>&lt;type id="tid"&gt;type_name&lt;/type&gt;, где tid &ndash; числовой идентификатор типа (в дальнейшем мы будем на него ссылаться), а type_name &ndash; название типа (обычно из латинских букв).</code></p>
<h3>Описание связей</h3>
В секции <code>&lt;links&gt;</code> перечислены все связи между леммами в виде <code>&lt;link id="lid" from="from_id" to="to_id" type="tid"/&gt;</code>, где lid &ndash; числовой идентификатор конкретной связи, from_id и to_id &ndash; идентификаторы лемм, между которыми существует эта связь, а tid &ndash; идентификатор типа связи.
<h3>Пример</h3>
<code><pre>
&lt;?xml version="1.0" encoding="utf8" standalone="yes"?&gt;
&lt;dictionary version="0.8" revision="403605"&gt;
    &lt;grammems&gt;
        &lt;grammem parent=""&gt;POST&lt;/grammem&gt;
        &lt;grammem parent="POST"&gt;NOUN&lt;/grammem&gt;
        ...
    &lt;/grammems&gt;
    &lt;lemmata&gt;
        &lt;lemma id="1" rev="402007"&gt;
            &lt;l t="абажур"&gt;&lt;g v="NOUN"/&gt;&lt;g v="inan"/&gt;&lt;g v="masc"/&gt;&lt;/l&gt;
            &lt;f t="абажур"&gt;&lt;g v="sing"/&gt;&lt;g v="nomn"/&gt;&lt;/f&gt;
            &lt;f t="абажура"&gt;&lt;g v="sing"/&gt;&lt;g v="gent"/&gt;&lt;/f&gt;
            ...
        &lt;/lemma&gt;
        ...
    &lt;/lemmata&gt;
    &lt;link_types&gt;
        &lt;type id="1"&gt;VERB_GERUND&lt;/type&gt;
        ...
    &lt;/link_types&gt;
    &lt;links&gt;
        &lt;link id="1" from="104" to="106" type="1"/&gt;
        ...
    &lt;/links&gt;
&lt;/dictionary&gt;
</pre></code>
{/block}
