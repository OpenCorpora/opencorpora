{strip}
<script src="{$web_prefix}/assets/vendor/bootstrap/js/bootstrap.tooltip-fixed.js"></script>
<div class="achievement-well">

	<div class="achievement-wrap achievement-stork achievement-small with-static-tip" title="За регистрацию">
	</div>

	<div class="achievement-wrap achievement-proceed achievement-small with-static-tip" title="Хочу еще!">
	</div>

	<div class="achievement-wrap achievement-bobr achievement-small with-static-tip" title="За трудолюбие">
	    <div class="achievement-bobr-level achievement-level">2</div>
	    <div class="progress" title="Для получения уровня 3 осталось сделать 35 заданий" data-placement="bottom">
	  		<div class="bar" style="width: 60%;"></div>
		</div>
	</div>

	<div class="achievement-wrap achievement-hameleon achievement-small with-static-tip" title="За разнообразие">
	    <div class="achievement-hameleon-level achievement-level">1</div>
	    <div class="progress" data-placement="bottom" title="Нужно сделать по 20 заданий в 3 типах пулов для получения уровня 2">
	  		<div class="bar" style="width: 20%;"></div>
		</div>
	</div>

	<div class="achievement-wrap achievement-stub achievement-dog achievement-small" title="Эту ачивку вы получите, если будете делать задания в течение месяца после регистрации" data-placement="bottom">
	    <div class="achievement-dog-level achievement-level">8</div>
	    <div class="progress" data-placement="bottom" title="Нужно в течение месяца сделать 100 заданий для получения уровня 9">
	  		<div class="bar" style="width: 33%;"></div>
		</div>
	</div>

	<div class="achievement-wrap achievement-stub achievement-fish3 achievement-small" title="Эту ачивку вы получите, когда вступите в команду" data-placement="bottom">
	</div>


	{*foreach from=$badges item=badge}
	<div class="pull-left" style="border: 1px #ddd solid; margin-right: 10px; padding: 5px">
	    <div><img style="margin-bottom: 5px; cursor: help" src="{if $badge.image}img/badges/{$badge.image}-100x100.png{else}http://placehold.it/100x100{/if}" title="{$badge.description|htmlspecialchars}"></div>
	    <div align="center"><b>{$badge.name}</b><br/>{$badge.shown_time|date_format:"%d.%m.%Y"}</div>
	</div>
	{/foreach*}
</div>
{/strip}

<script type="text/javascript">
{literal}
	$(".with-static-tip").tooltip({
		trigger: 'manual',
		template: '<div class="tooltip achievement-tooltip tooltip-white"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
		container: '.achievement-well'
	}).tooltip('show');

 	$(".achievement-stub").tooltip({
		template: '<div class="tooltip achievement-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
		container: '.achievement-well',
	});

	$('.achievement-wrap > .progress').tooltip({
		template: '<div class="tooltip achievement-tooltip tooltip-white"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
		container: '.achievement-well'
	});
{/literal}
</script>