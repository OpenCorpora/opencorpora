{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='english/header.tpl'}
<div id='content'>
<h3><a href='dict.php?act=edit&amp;id={$diff.lemma_id}'>Lemma {$diff.lemma_id}</a>, changed by {$diff.new_user_name|default:'Robot'} on {$diff.new_timestamp|date_format:"%d.%m.%Y at %H:%M"}</h3>
<p>
{if $diff.comment}
<b>Comment:</b> {$diff.comment}
{else}
No comment.
{/if}
</p>
<table border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <td>{if $diff.prev_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.prev_set}'>&lt; previous version</a>{else}&nbsp;{/if}</td>
        <td align='right'>{if $diff.next_set}<a href='?lemma_id={$diff.lemma_id}&amp;set_id={$diff.next_set}'>next version &gt;</a>{else}&nbsp;{/if}</td>
    </tr>
    <tr>
{if $diff.old_ver > 0}
        <td valign='top'><b>(Before the edit)</b>
        {if $is_logged}
        <form class='inline' id='form_revert_t{$diff.old_ver}' method='post' action='{$web_prefix}/revert.php?dict_rev={$diff.old_ver}'><button onclick="submit_with_readonly_check(byid('form_revert_t{$diff.old_ver}'))">Revert to this version</button></form>
        {/if}
        <br/><b>Revision {$diff.old_ver} ({$diff.old_user_name|default:'Robot'}, {$diff.old_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.old_rev_xml|format_xml|htmlspecialchars}</pre></td>
        <td valign='top'><b>(After the edit)<br/>Revision {$diff.new_ver} ({$diff.new_user_name|default:'Robot'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{else}
        <td valign='top'><b>New lemma</b></td>
        <td valign='top'><b>Revision {$diff.new_ver} ({$diff.new_user_name|default:'Robot'}, {$diff.new_timestamp|date_format:"%d.%m.%Y, %H:%M"})</b><pre>{$diff.new_rev_xml|format_xml|htmlspecialchars}</pre></td>
{/if}
    </tr>
</table>
</div>
<div id='rightcol'>
{include file='english/right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
