{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<p>Всего пар лемм со связью типа {$data.name}: {$data.total}. Ниже не более 100 примеров.</p>
<ul>
{foreach item=item from=$data.samples}
    <li>
        <a href="?act=edit&id={$item.lemma1[0]}">{$item.lemma1[1]}</a>
        -&gt;
        <a href="?act=edit&id={$item.lemma2[0]}">{$item.lemma2[1]}</a>
    </li>
{/foreach}
</ul>
{/block}
