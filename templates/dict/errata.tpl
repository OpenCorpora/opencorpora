{* Smarty *}
{include file='commonhtmlheader.tpl'}
<body>
<div id='main'>
{include file='header.tpl'}
<div id='content'>
<h1>Контроль словаря</h1>
<p>Не проверено {$errata.lag} ревизий, всего {$errata.total} ошибок.</p>
<table border='1' cellspacing='0' cellpadding='2'>
<tr>
    <th>id</th>
    <th>timestamp</th>
    <th>revision</th>
    <th>type</th>
    <th>description</th>
</tr>
{foreach item=error from=$errata.errors}
<tr>
    <td>{$error.id}</td>
    <td>{$error.timestamp|date_format:"%a %d.%m.%Y, %H:%M"}</td>
    <td>{$error.revision}</td>
    <td>{$error.type}</td>
    <td>{$error.description}</td>
</tr>
{/foreach}
</table>
</div>
<div id='rightcol'>
{include file='right.tpl'}
</div>
<div id='fake'></div>
</div>
{include file='footer.tpl'}
</body>
{include file='commonhtmlfooter.tpl'}
