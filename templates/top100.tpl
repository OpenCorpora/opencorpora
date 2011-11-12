{* Smarty *}
{extends file='common.tpl'}
{block name='content'}
<table border="1" cellspacing="0" cellpadding="4">
{if $smarty.get.what == 'colloc'}
<tr><th>#<th>&nbsp;</th><th>Абс. частота 1</th><th>Абс. частота 2</th><th>Совм. частота</th><th>Коэфф.</th></tr>
{foreach $stats as $i=>$s}
<tr><td>{$i+1}<td>{$s.lterm|htmlspecialchars} {$s.rterm|htmlspecialchars}</td><td>{$s.lfreq}</td><td>{$s.rfreq}</td><td>{$s.cfreq}</td><td>{$s.coeff}</td></tr>
{/foreach}
{else}
<tr><th>#<th>Токен</th><th>Абс. частота</th><th>ipm (частота на миллион)</th></tr>
{foreach $stats as $i=>$s}
<tr><td>{$i+1}<td>{$s.token|htmlspecialchars}</td><td>{$s.abs}</td><td>{$s.ipm}</td></tr>
{/foreach}
{/if}
</table>
{/block}
