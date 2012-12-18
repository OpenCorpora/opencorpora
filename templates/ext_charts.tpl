{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.js"></script>
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.pie.js"></script>
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.stack.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="{$web_prefix}/js/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
{literal}
$(document).ready(function() {
    var data = {/literal}{$main.data}{literal};
    var data2 = {/literal}{$main.data2}{literal};
    var stack_opts = {
        series: {
            stack: true,
            bars: {show: true, horizontal: true, barWidth: 0.6, align: 'center'}
        },
        yaxis: {
            ticks: {/literal}{$main.ticks}{literal}
        },
        legend: {
            position: 'se'
        }
    };
    $.plot($("#words_chart"), data, stack_opts);

    $("#toggle_chart").click(function(event) {
        var $this = $(this);
        $this.attr('rel', 1 - $this.attr('rel'));

        if ($this.attr('rel') == 1) {
            this.innerHTML = 'вернуть';
            $.plot($("#words_chart"), data2, stack_opts);
        }
        else {
            this.innerHTML = 'привести все к 100%';
            $.plot($("#words_chart"), data, stack_opts);
        }
        event.preventDefault();
    });
});
{/literal}
</script>
<h1>Статистика</h1>
<ul class="nav nav-tabs">
    <li><a href="?page=stats&weekly">Активность за неделю</a></li>
    <li><a href="?page=genre_stats">Состав корпуса</a></li>
    <li><a href="?page=tag_stats">По тегам</a></li>
    <li><a href="?page=charts">Графики</a></li>
    <li class="active"><a href="?page=ext_charts">Спецграфики</a></li>
</ul>
<h2>Распределение пулов</h2>
<p><a href="#" id="toggle_chart" class="pseudo" rel="0">привести все к 100%</a></p>
<div id="words_chart" style="width:100%; height: 1000px"></div>
<h2>Модерация</h2>
<div class='progress'>
    <div class="bar bar-info" style="width: 33%">модерируется</div>
    <div class="bar bar-warning" style="width: 34%">отмодерировано</div>
    <div class="bar bar-success" style="width: 33%">в архиве</div>
</div>
<table class='table'>
<tr><td></td><td></td>
{foreach from=$moder.moderators item=mod key=modnum}
<td class='small'>{if $smarty.session.user_id == $modnum}<b>{$mod|htmlspecialchars}</b>{else}{$mod|htmlspecialchars}{/if}</td>
{/foreach}
</tr>
{foreach from=$moder.types item=type key=typenum}
    <tr>
        <td><div class='progress'>
        {if isset($moder.data.total.$typenum[5])}
            <div class="bar bar-info" style="width:{$moder.data.total.$typenum[5][1]}%" title="{$moder.data.total.$typenum[5][0]}">{$moder.data.total.$typenum[5][0]}</div>
        {/if}
        {if isset($moder.data.total.$typenum[6])}
            <div class="bar bar-warning" style="width:{$moder.data.total.$typenum[6][1]}%" title="{$moder.data.total.$typenum[6][0]}">{$moder.data.total.$typenum[6][0]}</div>
        {/if}
        {if isset($moder.data.total.$typenum[9])}
            <div class="bar bar-success" style="width:{$moder.data.total.$typenum[9][1]}%" title="{$moder.data.total.$typenum[9][0]}">{$moder.data.total.$typenum[9][0]}</div>
        {/if}
        </div></td>
        <td class='small'>
            {if $moder.data[0].$typenum[4][0] > 0}
                <span class='badge badge-important'>{$moder.data[0].$typenum[4][0]}</span>
            {/if}
            <a href="{$web_prefix}/pools.php?type=4&amp;filter={$type|urlencode}">{$type|htmlspecialchars}</a>
        </td>
        {foreach from=$moder.moderators key=modnum item=mod}
            <td><div class='progress'>
            {if isset($moder.data.$modnum.$typenum[5])}
                <div class="bar bar-info" style="width:{$moder.data.$modnum.$typenum[5][1]}%" title="{$moder.data.$modnum.$typenum[5][0]}">{$moder.data.$modnum.$typenum[5][0]}</div>
            {/if}
            {if isset($moder.data.$modnum.$typenum[6])}
                <div class="bar bar-warning" style="width:{$moder.data.$modnum.$typenum[6][1]}%" title="{$moder.data.$modnum.$typenum[6][0]}">{$moder.data.$modnum.$typenum[6][0]}</div>
            {/if}
            {if isset($moder.data.$modnum.$typenum[9])}
                <div class="bar bar-success" style="width:{$moder.data.$modnum.$typenum[9][1]}%" title="{$moder.data.$modnum.$typenum[9][0]}">{$moder.data.$modnum.$typenum[9][0]}</div>
            {/if}
            </div></td>
        {/foreach}
    </tr>
{/foreach}
<tr>
    <td><div class='progress'>
        <div class="bar bar-info" style="width:{$moder.data.total.total[5][1]|intval}%" title="{$moder.data.total.total[5][0]}">{$moder.data.total.total[5][0]}</div>
        <div class="bar bar-warning" style="width:{$moder.data.total.total[6][1]|intval}%" title="{$moder.data.total.total[6][0]}">{$moder.data.total.total[6][0]}</div>
        <div class="bar bar-success" style="width:{$moder.data.total.total[9][1]|intval}%" title="{$moder.data.total.total[9][0]}">{$moder.data.total.total[9][0]}</div>
    </div></td>
    <td>Всего</td>
    {foreach from=$moder.moderators key=modnum item=mod}
        <td><div class='progress'>
            <div class="bar bar-info" style="width:{$moder.data.$modnum.total[5][1]|intval}%" title="{$moder.data.$modnum.total[5][0]}">{$moder.data.$modnum.total[5][0]}</div>
            <div class="bar bar-warning" style="width:{$moder.data.$modnum.total[6][1]|intval}%" title="{$moder.data.$modnum.total[6][0]}">{$moder.data.$modnum.total[6][0]}</div>
            <div class="bar bar-success" style="width:{$moder.data.$modnum.total[9][1]|intval}%" title="{$moder.data.$modnum.total[9][0]}">{$moder.data.$modnum.total[9][0]}</div>
        </div></td>
    {/foreach}
</tr>
</table>
{/block}
