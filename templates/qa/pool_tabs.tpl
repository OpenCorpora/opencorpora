{foreach from=$pool.samples item=sample}
{$sample.id}	{strip}
{foreach from=$sample.context item=word name=x}{if $smarty.foreach.x.index == $sample.mainword}[[{$word}]]{else}{$word}{/if} {/foreach}
{/strip}	{strip}
{foreach from=$sample.comments item=comment}
    {$comment.text|replace:"\n":'\n'} ({$comment.author}, {$comment.timestamp|date_format:"%d.%m.%Y, %H:%M"});
{/foreach}
{/strip}	{strip}
{foreach from=$sample.instances item=instance}{if $instance.answer_num == 99}Other	{elseif $instance.answer_num > 0}{$instance.answer_gram}	{/if}
{/foreach}
{/strip}{if isset($smarty.get.mod_ans)}	{$sample.moder_answer_gram}{else}{/if}

{/foreach}
