{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{$MISPRINT = $smarty.const.MA_SAMPLES_STATUS_MISPRINT}
{$HOMONYMY = $smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}
{$EDIT = $smarty.const.MA_SAMPLES_STATUS_MANUAL_EDIT}
<h1>Отмодерированные примеры, которые не изменили корпус</h1>
Общая статистика:
<ul>
<li>Всего: <a href="?act=merge_fails&status=0">{$data.total[0]}</a>
<li>Опечатка: <a href="?act=merge_fails&status={$MISPRINT}">{$data.checked[$MISPRINT]}/{$data.total[$MISPRINT]}</a></li>
<li>Неснимаемая омонимия: <a href="?act=merge_fails&status={$HOMONYMY}">{$data.checked[$HOMONYMY]}/{$data.total[$HOMONYMY]}</a></li>
<li>Ручная правка: <a href="?act=merge_fails&status={$EDIT}">{$data.checked[$EDIT]}/{$data.total[$EDIT]}</a></li>
<li>???: {$data.total[-1]|default:0}</li>
</ul>
<table class='table'>
<thead>
    <tr>
        <th>#</th>
        <th></th>
        <th>Название</th>
        <th>Статус</th>
        <th></th>
        <th>Комментарий<br/>(можно редактировать)</th>
    </tr>
    <tr>
        <col></col>
        <col></col>
        <col></col>
        <col></col>
        <col></col>
        <col width="20%"></col>
    </tr>
</thead>
{foreach from=$data.samples item=sample}
<tr>
    <td><a href='pools.php?act=samples&amp;pool_id={$sample.pool_id}&amp;ext=1'>{$sample.id}</a></td>
    <td>{$sample.token_text|htmlspecialchars}</td>
    <td>{$sample.pool_name}</td>
    <td>{strip}
        {if     $sample.mod_status == $smarty.const.MA_SAMPLES_STATUS_MISPRINT}опечатка
        {elseif $sample.mod_status == $smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}неснимаемая омонимия
        {elseif $sample.mod_status == $smarty.const.MA_SAMPLES_STATUS_MANUAL_EDIT}<a href="/diff.php?rev_id={$sample.revision}">ручная правка</a>
        {else}???
        {/if}
        {/strip}
    </td>
    <td><input type="checkbox" {if $sample.merge_status == 2}checked="checked"{/if} class="approve-sample" data-id="{$sample.id}"/></td>
    <td class="comment-cell" data-id="{$sample.id}" contenteditable>{$sample.comment}</td>
</tr>
{/foreach}
</table>
<script src="/assets/js/merge_fails.js"></script>
{/block}
