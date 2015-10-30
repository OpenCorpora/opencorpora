{* Smarty *}
{extends file='common.tpl'}
{block name=content}
<h3>Спасибо!</h3>
<p>{if isset($final)}Вы разметили задание! {/if}Сейчас вы можете:
    <ul>
    {if $next_pool_id}<li><a href="?act=annot&amp;pool_id={$next_pool_id}">разметить задание того же типа</a></li>{/if}
    <li><a href="?">попробовать другие типы заданий</a></li>
    <li><a href="/?page=stats&weekly#user{$smarty.session.user_id}">увидеть себя в статистике разметки</a></li>
    {if $user_permission_adder}<li><a href='/sources.php'>добавить новый текст в корпус</a></li>{/if}
    </ul>
</p>
{if $game_is_on == 1}
<h3>А если разметить ещё немного, то можно получить:</h3>
<div class="achievement-well achievement-well-no-margin">
    {if $achievement->given}
        {$htgn = $achievement->how_to_get_next()}
        <div class="achievement-wrap achievement-{$achievement->css_class} achievement-small with-static-tip"
            data-tab-name="{$achievement->css_class}-tab"
            data-placement="right" title="{$htgn}">
            <div class="achievement-level achievement-{$achievement->css_class}-level">{$achievement->level}</div>
            {if $htgn}
            <div class="progress">
                <div class="bar" style="width: {$achievement->progress}%;"></div>
            </div>
            {/if}
        </div>
    {else}
        <div class="achievement-wrap achievement-{$achievement->css_class} achievement-small achievement-stub with-static-tip"
            title="{$titles[$achievement->css_class].how_to_get}" data-placement="right"
            data-tab-name="{$achievement->css_class}-tab"></div>
    {/if}

    <script type="text/javascript">
    {literal}
        $(".with-static-tip").tooltip({
            trigger: 'manual',
            template: '<div class="tooltip achievement-tooltip tooltip-white tooltip-large"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            container: '.achievement-well'
        }).tooltip('show');
    {/literal}
    </script>
</div>
{/if}
{/block}
