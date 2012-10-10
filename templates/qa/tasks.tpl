{* Smarty *}
{extends file='common.tpl'}
{block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block}
{block name=content}
<h1>Доступные задания</h1>
<div class="ma_types">
{foreach from=$available item=type}
<div class="ma_type_row">
    <div class="row">
        <div class="span6"><a href="#" class="ma_type_name pseudo" title="показать пулы">{$type.name|htmlspecialchars}</a></div>
        <div class="span2">{if $type.first_id}<a href="?act=annot&amp;pool_id={$type.first_id}" class="btn">Взять на разметку</a>{/if}</div>
    </div>
    <div class="ma_type_pools" style="display:none;">
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
    </div>
</div>
{foreachelse}
<p><strong>Нет доступных заданий.</strong></p>
{/foreach}
</div>
<script>
    $(document).ready(function(){
        $('.ma_type_name').click(function(event){
            event.preventDefault();
            $(this).closest('.ma_type_row').find('.ma_type_pools').toggle();
        })
    })
</script>
{/block}
