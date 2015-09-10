{* Smarty *}
{extends file='common.tpl'}
{*block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block*}
{block name=content}
<h3>Спасибо!</h3>
<p>{if isset($final)}Вы разметили задание! {/if}Сейчас вы можете:
<ul>
{if $next_pool_id}<li><a href="?act=annot&amp;pool_id={$next_pool_id}">разметить задание того же типа</a></li>{/if}
<li><a href="?">попробовать другие типы заданий</a></li>
<li><a href="/?page=stats&weekly#user{$smarty.session.user_id}">увидеть себя в статистике разметки</a></li>
{if $user_permission_adder}<li><a href='/sources.php'>добавить новый текст в корпус</a></li>{/if}
</ul>
<h3>А если разметить ещё немного, то можно получить:</h3>
<div class="achievement-well">
    {if $achievement->given}
        <div class="achievement-wrap achievement-{$achievement->css_class} achievement-small"
            data-tab-name="{$achievement->css_class}-tab">
            <div class="achievement-level achievement-{$achievement->css_class}-level">{$achievement->level}</div>
            {$htgn = $achievement->how_to_get_next()}
            {if $htgn}
            <div class="progress" data-placement="bottom" title="{$htgn}">
                <div class="bar" style="width: {$achievement->progress}%;"></div>
            </div>
            {/if}
        </div>
    {else}
        <div class="achievement-wrap achievement-{$achievement->css_class} achievement-small achievement-stub"
            title="{$titles[$achievement->css_class].how_to_get}" data-placement="bottom"
            data-tab-name="{$achievement->css_class}-tab"></div>
    {/if}
</div>
{/block}
