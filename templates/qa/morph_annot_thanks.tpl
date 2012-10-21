{* Smarty *}
{extends file='common.tpl'}
{block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block}
{block name=content}
<h3>Спасибо!</h3>
<p>{if isset($final)}Вы разметили задание! {/if}Сейчас вы можете:
<ul>
{if $next_pool_id}<li><a href="?act=annot&amp;pool_id={$next_pool_id}">разметить задание того же типа</a></li>{/if}
<li><a href="?">попробовать другие типы заданий</a></li>
<li><a href="{$web_prefix}/?page=stats#user{$smarty.session.user_id}">увидеть себя в статистике разметки</a></li>
{if $user_permission_adder}<li><a href='{$web_prefix}/sources.php'>добавить новый текст в корпус</a></li>{/if}
</ul>
{/block}
