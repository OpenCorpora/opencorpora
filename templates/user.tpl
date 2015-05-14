{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script>
    $(document).ready(function(){
        $('.ma_type_show').click(function(event){
            event.preventDefault();
            var $a = $(event.target);
            if(!$a.hasClass('active')) {
                $a.addClass('active');
                $('.pools_' + $a.data('key')).show();
            }
            else {
                $a.removeClass('active');
                $('.pools_' + $a.data('key')).hide();
            }

        })
    })
</script>
<h1>{$user.shown_name|htmlspecialchars}</h1>
{if $is_admin || ($is_logged && $smarty.session.user_id == $smarty.get.id)}
<p>настоящий логин &mdash; {$user.name|htmlspecialchars}, дата регистрации {$user.registered|date_format:"%d.%m.%Y"}</p>
{/if}

{capture name="stats_table"}

<table class='table-condensed table'>
<tr><th>Тип пула<th>Название пула<th>Всего ответов<th>Проверено<th colspan='2'>Ошибок</tr>
<tr><td></td><td><b>ВСЕГО</b></td><td><b>{$user.total_answers}</b></td><td><b>{$user.checked_answers}</b></td><td><b>{$user.incorrect_answers}</b></td><td><b>=&nbsp;{if $user.checked_answers > 0}{($user.incorrect_answers / $user.checked_answers * 100)|number_format:1}%{else}N/A{/if}</b></td></tr>
{foreach from=$user.annot item=pool_type}
<tr><td><div class="ma_pools_complexity ma_pools_complexity_{$pool_type.complexity}" title="{$complexity[$pool_type.complexity]}"></div>{$pool_type.grammemes}</td><td><a href="#" class="ma_type_show pseudo" title="показать список" data-key="{$pool_type.id}">{$pool_type.name|htmlspecialchars}</a></td><td><b>{$pool_type.total_answers}</b></td><td><b>{$pool_type.checked_answers}</b></td><td><b>{$pool_type.incorrect_answers}</b></td><td><b>=&nbsp;{if $pool_type.checked_answers > 0}{($pool_type.incorrect_answers / $pool_type.checked_answers * 100)|number_format:1}%{else}N/A{/if}</b></td></tr>
    {foreach from=$pool_type.pools item=pool}
    <tr style='display: none' class='pools_{$pool.type}'>
        <td></td>
        <td>
            {if $pool.status == 3}<i class="icon-forward" title="пул размечается"></i>
            {elseif $pool.status == 4}<i class="icon-pause" title="пул размечен"></i>
            {elseif $pool.status == 9}<i class="icon-check" title="пул в архиве"></i>
            {else}{$pool.status}
            {/if}
            <a href="{$web_prefix}/pools.php?act=samples&amp;pool_id={$pool.id}">{$pool.name}</a>
        </td>
        <td>{$pool.total_answers}</td>
        <td>{$pool.checked_answers}</td>
        <td{if $pool.incorrect_answers > 0} class='bgpink'{/if}>{if $pool.incorrect_answers > 0}<a href="{$web_prefix}/pools.php?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=user:{$smarty.get.id}">{$pool.incorrect_answers}</a>{else}0{/if}</td>
    </tr>
    {/foreach}
{/foreach}
</table>
{/capture}

{if $game_is_on == 1}
    <ul class="nav nav-tabs" id="profile-tabs">
      <li class="active"><a href="#achievements" data-toggle="tab">Мои бейджи</a></li>
      <li><a href="#stats" data-toggle="tab">Мои ответы</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" id="achievements">
          {include file="achievements/user_achievements.tpl" badges=$badges user=$user}
      </div>
      <div class="tab-pane" id="stats">
          {$smarty.capture.stats_table}
      </div>
    </div>
{else}
    <h2>Мои ответы</h2>
    {$smarty.capture.stats_table}
{/if}

{/block}
