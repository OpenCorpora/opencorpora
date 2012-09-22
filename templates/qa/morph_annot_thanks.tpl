{* Smarty *}
{extends file='common.tpl'}
{block name=before_content}{if $game_is_on == 1}{include file="qa/game_status.tpl"}{/if}{/block}
{block name=content}
<p><b>Спасибо!</b></p>
<p>Задания этого типа закончились. Сейчас вы можете:
<ul>
<li><a href="?">попробовать другие типы заданий</a></li>
{if $user_permission_adder}<li><a href='{$web_prefix}/sources.php'>{t}добавить новый текст в корпус{/t}</a></li>{/if}
</ul>
{/block}
