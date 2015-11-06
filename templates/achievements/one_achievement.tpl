{extends file='common.tpl'}
{block name='content'}
    <div class="single-achievement-wrap">
    <h2 class="slim-header">Бейдж пользователя
    <a href="{$web_prefix}/user.php?id={$user_id}">{$user['shown_name']}</a></h2>

    {$a = $achievement}
    <div class="achievement-wrap achievement-{$a->css_class} achievement-medium">
        {if $a->level}
            <div class="achievement-{$a->css_class}-level achievement-level">{$a->level}</div>
        {/if}
    </div>
    </div>
{/block}
