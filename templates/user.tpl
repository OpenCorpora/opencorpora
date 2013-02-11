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
<p>настоящий логин &mdash; {$user.name|htmlspecialchars}, зарегистрировался {$user.registered|date_format:"%d.%m.%Y"}</p>
<h2>Разметка</h2>
<table class='table-condensed table'>
<tr><th>Тип пула<th>Название пула<th>Всего ответов<th>Проверено<th colspan='2'>Ошибок</tr>
<tr><td></td><td><b>ВСЕГО</b></td><td><b>{$user.total_answers}</b></td><td><b>{$user.checked_answers}</b></td><td><b>{$user.incorrect_answers}</b></td><td><b>=&nbsp;{($user.incorrect_answers / $user.checked_answers * 100)|number_format:1}%</b></td></tr>
{foreach from=$user.annot item=pool_type}
<tr><td><div class="ma_pools_complexity ma_pools_complexity_{$pool_type.complexity}"></div>{$pool_type.grammemes}</td><td><a href="#" class="ma_type_show pseudo" title="показать список" data-key="{$pool_type.id}">{$pool_type.name|htmlspecialchars}</a></td><td><b>{$pool_type.total_answers}</b></td><td><b>{$pool_type.checked_answers}</b></td><td><b>{$pool_type.incorrect_answers}</b></td><td><b>=&nbsp;{($pool_type.incorrect_answers / $pool_type.checked_answers * 100)|number_format:1}%</b></td></tr>
    {foreach from=$pool_type.pools item=pool}
    <tr style='display: none' class='pools_{$pool.type}'><td></td><td><a href="{$web_prefix}/pools.php?act=samples&amp;pool_id={$pool.id}">{$pool.name}</a></td><td>{$pool.total_answers}</td><td>{$pool.checked_answers}</td><td{if $pool.incorrect_answers > 0} class='bgpink'{/if}>{if $pool.incorrect_answers > 0}<a href="{$web_prefix}/pools.php?act=samples&amp;pool_id={$pool.id}&amp;ext&amp;filter=user:{$smarty.get.id}">{$pool.incorrect_answers}</a>{else}0{/if}</td></tr>
    {/foreach}
{/foreach}
</table>
{/block}
