{$c = count($stats)}{capture name=text}
{if !$among_them}
    {if $c == 1}
        Этот бейдж есть у
            <a target="_blank" href="/user.php?id={$stats[0]['user_id']}">{$stats[0]['user_shown_name']}</a>.
    {else if ($c > 1 && $c <= 4)}
        Этот бейдж получили
        {foreach $stats as $user}
            <a target="_blank" href="/user.php?id={$user['user_id']}">{$user['user_shown_name']}</a>{if $user@iteration != $c},{else}.{/if}
        {/foreach}
    {else}
    Этот бейдж получили
    <a target="_blank" href="/user.php?id={$stats[0]['user_id']}">{$stats[0]['user_shown_name']}</a>,

    <a target="_blank" href="/user.php?id={$stats[1]['user_id']}">{$stats[1]['user_shown_name']}</a>

        {$remaining = $c - 2}
        и еще {number_format($remaining)}

        {$last2 = $remaining|substr:-1}
        {$last1 = $remaining|substr:-2}
        {if in_array($last2, range(2, 4)) && $last1 != 1}
        человека.
        {else}
        человек.
        {/if}
    {/if}

{else} {* ХХХ, среди них *}
    {if $c == 1}
            Этот бейдж есть у
            <a target="_blank" href="/user.php?id={$stats[0]['user_id']}">{$stats[0]['user_shown_name']|truncate:40:".."}</a>.
    {else if ($c > 1 && $c <= 4)}
        {foreach $stats as $user}
            <a target="_blank" href="/user.php?id={$user['user_id']}">{$user['user_shown_name']|truncate:40:".."}</a>{if $user@iteration != $c},{else}.{/if}
        {/foreach}
    {else}
    {number_format($c)}

        {$last2 = $c|substr:-1}
        {$last1 = $c|substr:-2:-1}
        {if in_array($last2, range(2, 4)) && $last1 != 1}
        человека,
        {else}
        человек,
        {/if}
        среди них &mdash;
    <a target="_blank" href="/user.php?id={$stats[0]['user_id']}">{$stats[0]['user_shown_name']|truncate:40:".."}</a> и

    <a target="_blank" href="/user.php?id={$stats[1]['user_id']}">{$stats[1]['user_shown_name']|truncate:40:".."}</a>.

    {/if}
{/if}
{/capture}{if empty($threshold) || $threshold > mb_strlen($smarty.capture.text)}
    {$smarty.capture.text}
{/if}
