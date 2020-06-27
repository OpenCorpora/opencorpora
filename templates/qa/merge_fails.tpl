{* Smarty *}
{extends file='common.tpl'}
{block name=content}
{$MISPRINT = $smarty.const.MA_SAMPLES_STATUS_MISPRINT}
{$HOMONYMY = $smarty.const.MA_SAMPLES_STATUS_HOMONYMOUS}
{$EDIT = $smarty.const.MA_SAMPLES_STATUS_MANUAL_EDIT}
{$MANUAL = $smarty.const.MA_MERGE_STATUS_MANUAL_OK}
<h1>Отмодерированные примеры, которые не изменили корпус</h1>
<div>
    {if empty($smarty.get.show_checked)}
    <a href="?act=merge_fails&status={$smarty.get.status|default:0}&show_checked=1">показать проверенные</a>
    {else}
    <a href="?act=merge_fails&status={$smarty.get.status|default:0}&show_checked=0">скрыть проверенные</a>
    {/if}
</div>
<h3>Общая статистика:</h3>
<p>Всего проверено: {$data.checked.sum} / {$data.total.sum} ({$data.ready|string_format:"%.2f"}%)
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
        <th rowspan='2'>#</th>
        <th rowspan='2'></th>
        <th rowspan='2'>Название</th>
        <th rowspan='2'>Статус</th>
        <th colspan='2'>Ответы</th>
        <th rowspan='2'></th>
        <th rowspan='2'>Комментарий<br/>(можно редактировать)</th>
    <tr><th>mod<th>prod</tr>
    <tr>
        <col></col>
        <col></col>
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
        {if     $sample.mod_status == $MISPRINT}опечатка
        {elseif $sample.mod_status == $HOMONYMY}неснимаемая омонимия
        {elseif $sample.mod_status == $EDIT}<a href="/diff.php?rev_id={$sample.revision}">ручная правка</a>
        {else}???
        {/if}
        {/strip}
    </td>
    <td>{$sample.mod_answer}</td>
    <td>{$sample.prod_answer}</td>
    <td><input type="checkbox" {if $sample.merge_status == $MANUAL}checked="checked"{/if} class="approve-sample" data-id="{$sample.id}"/></td>
    <td class="comment-cell" data-id="{$sample.id}" contenteditable>{$sample.comment}</td>
</tr>
{/foreach}
</table>
<script src="/assets/js/merge_fails.js"></script>
{/block}
