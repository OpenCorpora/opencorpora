{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}Материалы для скачивания{/t}</h1>
<h2>{t}Размеченные тексты{/t}</h2>
<p>XML, {t}обновлён{/t} {$dl.annot.xml.updated}
<ul>
<li><a href="{$web_prefix}/files/export/annot/annot.opcorpora.xml.bz2">архив .bz2</a> ({$dl.annot.xml.bz2.size} {t}Мб{/t})</li>
<li><a href="{$web_prefix}/files/export/annot/annot.opcorpora.xml.zip">архив .zip</a> ({$dl.annot.xml.zip.size} {t}Мб{/t})</li>
</ul>
<h2>Частотные списки</h2>
<table border='1' cellspacing='0' cellpadding='3'>
<tr class='small'>
    <th>&nbsp;</th>
    <th>Леммы</th>
    <th>Учёт регистра</th>
    <th>Только слова*</th>
    <th colspan='3'>&nbsp;</th>
    <th>Обновлено</th>
</tr>
{include file='static/downloads.row.tpl' N='1' suffix='exact_cyr_lc'  lowercase='1' lemma='0' words='1'}
{include file='static/downloads.row.tpl' N='1' suffix='exact_cyr'     lowercase='0' lemma='0' words='1'}
{include file='static/downloads.row.tpl' N='1' suffix='exact_lc'      lowercase='1' lemma='0' words=''}
{include file='static/downloads.row.tpl' N='1' suffix='exact'         lowercase='0' lemma='0' words=''}
{include file='static/downloads.row.tpl' N='2' suffix='exact_cyrA_lc' lowercase='1' lemma='0' words='A'}
{include file='static/downloads.row.tpl' N='2' suffix='exact_cyrB_lc' lowercase='1' lemma='0' words='B'}
{include file='static/downloads.row.tpl' N='2' suffix='exact_cyrA'    lowercase='0' lemma='0' words='A'}
{include file='static/downloads.row.tpl' N='2' suffix='exact_cyrB'    lowercase='0' lemma='0' words='B'}
{include file='static/downloads.row.tpl' N='2' suffix='exact_lc'      lowercase='1' lemma='0' words=''}
{include file='static/downloads.row.tpl' N='2' suffix='exact'         lowercase='0' lemma='0' words=''}
</table>
<p class='small'>* Словами мы считаем токены, имеющие в своём составе хотя бы одну кириллическую букву.</p>
<p class='small'>** Тип A: токены, не являющиеся словами, игнорируются, т.е. в биграмму могут входить, например, слова, разделённые запятой. Тип B: никакие токены не игнорируются, но из списка исключаются цепочки, где хотя бы один токен не является словом.</p>
<h2>{t}Морфологический словарь{/t}</h2>
<p>XML, {t}обновлён{/t} {$dl.dict.xml.updated}, см. <a href="{$web_prefix}/?page=export">описание формата</a></p>
<ul>
<li><a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.bz2">архив .bz2</a> ({$dl.dict.xml.bz2.size} {t}Мб{/t})</li>
<li><a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.zip">архив .zip</a> ({$dl.dict.xml.zip.size} {t}Мб{/t})</li>
</ul>
<p>Plain text, {t}обновлён{/t} {$dl.dict.txt.updated}</p>
<ul>
<li><a href="{$web_prefix}/files/export/dict/dict.opcorpora.txt.bz2">архив .bz2</a> ({$dl.dict.txt.bz2.size} {t}Мб{/t})</li>
<li><a href="{$web_prefix}/files/export/dict/dict.opcorpora.txt.zip">архив .zip</a> ({$dl.dict.txt.zip.size} {t}Мб{/t})</li>
</ul>
{/block}
