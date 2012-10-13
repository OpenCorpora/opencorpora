{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}Материалы для скачивания{/t}</h1>
<ul class="nav nav-tabs">
    <li class="active"><a href="{$web_prefix}/?page=downloads">Скачать</a></li>
    <li><a href="{$web_prefix}/?page=export">Форматы экспорта</a></li>
</ul>
{literal}
<script type="text/javascript">
    $(document).ready(function(){
        $('input[type=radio]').change(function(){
            $('#table_freq tr').show();
            var N = $('input:checked[name="nval"]').val();
            var reg = $('input:checked[name="register"]').val();
            var ttype = $('input:checked[name="ttype"]').val();

            if (N > 0)
                $('#table_freq tr').not('.nval_' + N).hide();

            if (reg == 2)
                $('#table_freq tr').not('.lc').hide();
            else if (reg == 1)
                $('#table_freq tr.lc').hide();

            if (ttype == 1)
                $('#table_freq tr').not('.wds').hide();
            else if (ttype == 2)
                $('#table_freq tr.wds').hide();

            $('#table_freq tr.small').show();
        });
    });
</script>
{/literal}
<h3>{t}Размеченные тексты{/t}</h3>
<p>XML, {t}обновлён{/t} {$dl.annot.xml.updated}
<ul>
<li><a href="{$web_prefix}/files/export/annot/annot.opcorpora.xml.bz2">архив .bz2</a> ({$dl.annot.xml.bz2.size} {t}Мб{/t})</li>
<li><a href="{$web_prefix}/files/export/annot/annot.opcorpora.xml.zip">архив .zip</a> ({$dl.annot.xml.zip.size} {t}Мб{/t})</li>
</ul>
<p>Подкорпус со снятой омонимией<sup>*</sup>, XML
<ul>
<li><a href="{$web_prefix}/files/export/annot/annot.opcorpora.no_ambig.xml.bz2">архив .bz2</a></li>
<li><a href="{$web_prefix}/files/export/annot/annot.opcorpora.no_ambig.xml.zip">архив .zip</a></li>
</ul>
<p class='small'><sup>*</sup> В подкорпус включены целые предложения, не имеющие в своём составе ни одного неоднозначно разобранного слова &mdash; как изначально однозначные предложения, так и те, в которых неоднозначность была снята вручную.</p>
<h3>Частотные списки</h3>
<div class="row space-after">
    <div class="span3">
        <h4>Тип n-граммы:</h4>
        <label class="radio"><input type='radio' name='nval' value='0' checked='checked'/>все</label>
        <label class="radio"><input type='radio' name='nval' value='1'/>униграммы (1 слово)</label>
        <label class="radio"><input type='radio' name='nval' value='2'/>биграммы (2 слова)</label>
        <label class="radio"><input type='radio' name='nval' value='3'/>триграммы (3 слова)</label>
    </div>
    <div class="span3">
        <h4>Учёт регистра:</h4>
        <label class="radio"><input type='radio' name='register' value='0' checked='checked'/>все</label>
        <label class="radio"><input type='radio' name='register' value='1'/>с учётом</label>
        <label class="radio"><input type='radio' name='register' value='2'/>без учёта</label>
    </div>
    <div class="span3">
        <h4>Тип токенов:</h4>
        <label class="radio"><input type='radio' name='ttype' value='0' checked='checked'/>все</label>
        <label class="radio"><input type='radio' name='ttype' value='1'/>только слова</label>
        <label class="radio"><input type='radio' name='ttype' value='2'/>не только слова</label>
    </div>
</div>
<table class="table" id="table_freq">
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
{include file='static/downloads.row.tpl' N='3' suffix='exact_cyrA_lc' lowercase='1' lemma='0' words='A'}
{include file='static/downloads.row.tpl' N='3' suffix='exact_cyrB_lc' lowercase='1' lemma='0' words='B'}
{include file='static/downloads.row.tpl' N='3' suffix='exact_cyrA'    lowercase='0' lemma='0' words='A'}
{include file='static/downloads.row.tpl' N='3' suffix='exact_cyrB'    lowercase='0' lemma='0' words='B'}
{include file='static/downloads.row.tpl' N='3' suffix='exact_lc'      lowercase='1' lemma='0' words=''}
{include file='static/downloads.row.tpl' N='3' suffix='exact'         lowercase='0' lemma='0' words=''}
</table>
<p class='small'>* Словами мы считаем токены, имеющие в своём составе хотя бы одну кириллическую букву.</p>
<p class='small'>** Тип A: токены, не являющиеся словами, игнорируются, т.е. в биграмму могут входить, например, слова, разделённые запятой. Тип B: никакие токены не игнорируются, но из списка исключаются цепочки, где хотя бы один токен не является словом.</p>
<h2>{t}Коллокации{/t}</h2>
<p class='small'>(На данный момент только двусловные и рассчитываются только по метрике MI. На термы наложено ограничение по частоте снизу: не менее корня 4-й степени от объёма корпуса.)</p>
<p>{t}Обновлено{/t} {$dl.colloc.mi.updated}</p>
<ul>
<li><a href="{$web_prefix}/files/export/ngrams/colloc.MI.bz2">архив .bz2</a> ({$dl.colloc.mi.bz2.size} {t}Мб{/t})</li>
<li><a href="{$web_prefix}/files/export/ngrams/colloc.MI.zip">архив .zip</a> ({$dl.colloc.mi.zip.size} {t}Мб{/t})</li>
<li><a href="{$web_prefix}/?page=top100&amp;what=colloc&amp;type=MI">top100</a></li>
</ul>
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
