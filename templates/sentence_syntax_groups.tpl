{* Вспомогательный шаблон - таблица групп *}

{* Требует: groups, group_types *}

<table class="table syntax_groups {if !$groups.simple}empty_table{/if}">
    <thead>
        <th></th>
        <th>Группа</th>
        <th>Тип</th>
        <th>Вершина</th>
    </thead>
    <tbody>
        {foreach $groups.simple as $group}
            <tr data-gid="{$group.id}">
                <td><i class="icon-remove remove_group" title="Удалить группу"></i></td>
                <td class="group_text">{$group.text}</td>
                <td>
                    <select class="group_type_select span2" data-gid="{$group.id}">
                        <option value="0" {if $group.type == 0}selected{/if}>Без типа</option>
                        {foreach $group_types as $gtid => $gtype}
                            <option value="{$gtid}" {if $group.type == $gtid}selected{/if}>{$gtype|htmlspecialchars}</option>
                        {/foreach}
                    </select>
                </td>
                <td>
                    <select class="group_head_select span2" data-gid="{$group.id}">
                        <option value="0" {if $group.head_id == 0}selected{/if}>-- Нет вершины --</option>
                        {foreach $group.token_texts as $tid => $ttext}
                            <option value="{$tid}" {if $group.head_id == $tid}selected{/if}>{$ttext|htmlspecialchars}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
        {/foreach}
        <tr class="stub_tr">
                <td colspan="4">
                    Пока ни одной группы не выделено.
                </td>
            </tr>
    </tbody>
</table>