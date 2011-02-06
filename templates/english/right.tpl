{* Smarty *}
{php}
$this->assign('dict_errors', sql_num_rows(sql_query("SELECT error_id FROM dict_errata LIMIT 1")));
{/php}
<div>
    <a href="{$web_prefix}/?page=about">About</a><br/>
    <a href="{$web_prefix}/?page=publications">Publications</a><br/>
    <a href="{$web_prefix}/?page=team">Our team</a><br/>
</div>
<div>
    <a href="{$web_prefix}/dict.php">Dictionary</a>
        {if $is_admin && $dict_errors}(<a class="red" href="{$web_prefix}/dict.php?act=errata">has errors</a>){/if}<br/>
    <a href="{$web_prefix}/?page=stats">Stats</a><br/>
    <a href="{$web_prefix}/?rand">Random sentence</a>
</div>
<div>
    <b>Latest edits</b><br/>
    <a href='{$web_prefix}/history.php'>In the annotation</a><br/>
    <a href='{$web_prefix}/dict_history.php'>In the dictionary</a>
</div>
<div>
<b><a href="{$web_prefix}/?page=downloads">Downloads</a></b>
</div>
{if $is_admin}
<div>
    <b>Revision</b> {$svn_revision}
</div>
{/if}
