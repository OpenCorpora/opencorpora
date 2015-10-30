{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<script type="text/javascript">
    {literal}
    $(document).ready(function(){
        $("#pool_select").change(function() {
            location.href = '?act=useful_pools&type=' + $(this).val();
        });
    });
    {/literal}
</script>
<h1>Пулы, сильнее всего влияющие на объём корпуса со снятой омонимией</h1>
<p>Список хороших предложений обновляется раз в сутки.</p>
<p>Тип:
<select id="pool_select">
{foreach from=$types item=t key=id}
    <option value='{$id}' {if $id == $smarty.get.type}selected="selected"{/if}>{$t.grammemes}</option>
{/foreach}
</select>
<table class="table">
<tr><td>Пул<td>Циферка</tr>
{foreach item=pool from=$pools}
<tr>
    <td>
            {if $pool.status == 4}<i class="icon-pause" title="пул снят с публикации"></i>
            {elseif $pool.status == 5}<i class="icon-forward" title="пул на модерации"></i>
            {elseif $pool.status == 6}<i class="icon-check" title="пул отмодерирован"></i>
            {/if}
            <a href="pools.php?act=samples&amp;pool_id={$pool.id}">{$pool.name|htmlspecialchars}</a>
            {if $pool.moderator} (модератор &ndash; {$pool.moderator|htmlspecialchars}){/if}
    <td>{$pool.count}
    <td>{$pool.count2}
</tr>
{/foreach}
</table>
{/block}
