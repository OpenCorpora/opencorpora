{strip}
<script src="{$web_prefix}/assets/vendor/bootstrap/js/bootstrap.tooltip-fixed.js"></script>
{$mine = ($am->user_id == $smarty.session.user_id)}
<div class="achievement-well">

	{foreach $achievements as $a}
		{if $a->given}
			<div class="achievement-wrap achievement-{$a->css_class} achievement-small">
				{if $a->level}
					<div class="achievement-level achievement-{$a->css_class}-level">{$a->level}</div>
					{if $htgn = $a->how_to_get_next() && $mine}
					<div class="progress" data-placement="bottom" title="{$htgn}">
	  					<div class="bar" style="width: {$a->progress}%;"></div>
					</div>
					{/if}
				{/if}
			</div>
		{else}
			<div class="achievement-wrap achievement-{$a->css_class} achievement-small achievement-stub" 
				title="{if $mine}{$a->how_to_get}{/if}" data-placement="bottom"></div>
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
		document.location.href = "/?page=achievements";
	});
{/literal}
</script>
