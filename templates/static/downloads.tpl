{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<h1>{t}Материалы для скачивания{/t}</h1>
<h2>{t}Морфологический словарь{/t}</h2>
<p><a href="{$web_prefix}/files/export/dict/dict.opcorpora.xml.bz2">{t}Словарь{/t}</a> (xml, {t}обновлён{/t} {$dl.dict.updated}, {$dl.dict.size} {t}Мб{/t})</p>
{/block}
