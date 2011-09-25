{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<table border="1" cellspacing="0" cellpadding="4">
<tr><th>#<th>Токен</th><th>Абс. частота</th><th>ipm (частота на миллион)</th></tr>
{foreach $stats as $i=>$s}
<tr><td>{$i+1}<td>{$s.token|htmlspecialchars}</td><td>{$s.abs}</td><td>{$s.ipm}</td></tr>
{/foreach}
</table>
{/block}
