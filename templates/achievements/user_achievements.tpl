{strip}
<script src="{$web_prefix}/assets/vendor/bootstrap/js/bootstrap.tooltip-fixed.js"></script>
{$mine = ($user_id == $smarty.session.user_id)}
{$titles = $achievements_titles}
<div class="achievement-well">

	{foreach $achievements as $a}
		{if $a->given}
			<div class="achievement-wrap achievement-{$a->css_class} achievement-small"
				data-tab-name="{$a->css_class}-tab">
				{if $a->level}
					<div class="achievement-level achievement-{$a->css_class}-level">{$a->level}</div>
					{$htgn = $a->how_to_get_next()}
					{if $htgn && $mine}
					<div class="progress" data-placement="bottom" title="{$htgn}">
	  					<div class="bar" style="width: {$a->progress}%;"></div>
					</div>
					{/if}
				{/if}
			</div>
		{else}
			<div class="achievement-wrap achievement-{$a->css_class} achievement-small achievement-stub"
				title="{if $mine}{$titles[$a->css_class].how_to_get}{/if}" data-placement="bottom"
				data-tab-name="{$a->css_class}-tab"></div>
		{/if}
	{/foreach}

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

	$('.achievement-well > .achievement-wrap').click(function() {
		document.location.href = "/?page=achievements#" + $(this).attr('data-tab-name');
	});
{/literal}
</script>
