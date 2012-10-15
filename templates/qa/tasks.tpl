{* Smarty *}
{extends file='common.tpl'}
{block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block}
{block name=content}
<h1>Доступные задания</h1>
<table class="ma_types">
    {foreach from=$available item=type key=key}
<tr class="ma_type_row">
    <td class="ma_type_name">
        <div class="ma_pools_complexity ma_pools_complexity_{$type.complexity}" title="сложность: {$type.complexity} из 4"></div><a href="#" class="ma_type_show pseudo" title="показать список" data-key="{$key}">{$type.name|htmlspecialchars}</a>
    </td>
    <td class="">{if $type.first_id}<a href="?act=annot&amp;pool_id={$type.first_id}" class="btn">Взять на разметку</a>{/if}</td>
</tr>
<tr class="ma_type_pools" style="display: none;" id="pools_{$key}">
    <td colspan="2">
        <table class="table table-condensed">
            <tr class="borderless"><th>Название задания</th><th>Сделано мной</th><th>Доступно</th>{if $available}<th>&nbsp;</th>{/if}</tr>
            {foreach $type.pools as $pool}
                <tr>
                    <td>{$pool.name}</td>
                    <td>{if $pool.num_done > 0}<a href="?act=my&amp;pool_id={$pool.id}">{$pool.num_done}</a>{else}0{/if}</td>
                    <td>{$pool.num}{if $pool.num_started} +{$pool.num_started} начатых{/if}</td>
                    <td>
                        {if $pool.status == 3}
                            {if $pool.num || $pool.num_started}<a href="?act=annot&amp;pool_id={$pool.id}">взять на разметку</a>{else}&nbsp;{/if}
                        {else}
                            только чтение
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </td>
</tr>
{foreachelse}
<p><strong>Нет доступных заданий.</strong></p>
{/foreach}
</table>
<script>
    $(document).ready(function(){
        $('.ma_type_show').click(function(event){
            event.preventDefault();
            var $a = $(event.target);
            if(!$a.hasClass('active')) {
                $a.addClass('active');
                $('#pools_' + $a.data('key')).show();
            }
            else {
                $a.removeClass('active');
                $('#pools_' + $a.data('key')).hide();
            }
            
        })
    })
</script>
{/block}
