{* Smarty *}
{include file='commonhtmlheader.tpl' title='Dictionary'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
    <p>{$stats.cnt_g} grammemes total, {$stats.cnt_l} lemmata, {$stats.cnt_f} forms in index ({$stats.cnt_r} revisions not checked).</p>
    {if $is_admin}
        <p><a href="?act=gram">Grammeme editor</a><br/>
        <a href="?act=gram_restr">Restrictions on grammemes</a></p>
        <p><a href="?act=lemmata">Lemma editor</a><br/>
        <a href="?act=errata">Errors in the dictionary</a> ({$stats.cnt_v} revisions not checked)</p>
        <p><button onClick="location.href='?act=edit&amp;id=-1'">Add lemma</button></p>
    {else}
        <p><a href="?act=gram">View grammemes</a><br/>
        <a href="?act=gram_restr">Restrictions on grammemes</a></p>
        <p><a href="?act=lemmata">View lemmata</a><br/>
        <a href="?act=errata">Errors in the dictionary</a> ({$stats.cnt_v} revisions not checked)</p>
    {/if}
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
