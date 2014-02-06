{* Вспомогательный шаблон - таблица групп *}

{* Требует: groups, group_types *}

<table class="table syntax_groups table-condensed {if !$groups.simple}empty_table{/if}">
    <thead>
        <th></th>
        <th>Группа</th>
        <th>Тип</th>
        <th>Вершина</th>
    </thead>
    <tbody>
        {foreach $groups.simple as $group}
            <tr data-gid="{$group.id}" {if $group.type == 16}class="gr16" style="display: none"{/if}>
                <td><i class="icon-arrow-up copy_group" title="Копировать группу"></i></td>
                <td class="group_text">{$group.text}</td>
                <td>{$group_types[$group.type]}</td>
                <td>{$group.token_texts[$group.head_id]}</td>
            </tr>
        {/foreach}
        {foreach $groups.complex as $group}
            <tr data-gid="{$group.id}">
                <td><i class="icon-arrow-up copy_group" title="Копировать группу"></i></td>
                <td class="group_text">{$group.text}</td>
                <td>{$group_types[$group.type]}</td>
                <td>{foreach $group.children_texts as $tid => $ttext}
                    {if $group.head_id == $tid}{$ttext[1]|htmlspecialchars}{/if}
                    {/foreach}
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>
