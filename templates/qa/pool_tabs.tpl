{foreach from=$pool.samples item=sample}
{$sample.id}	{$sample.token_id}	{strip}
{foreach $sample.context as $token_id => $word}{if $token_id == $sample.mainword}[[{$word}]]{else}{$word}{/if} {/foreach}
{/strip}	{strip}
{foreach from=$sample.comments item=comment}
    {$comment.text|replace:"\n":'\n'} ({$comment.author}, {$comment.timestamp|date_format:"%d.%m.%Y, %H:%M"});
{/foreach}
{/strip}	{strip}
{foreach from=$sample.instances item=instance}{if $instance.answer_num == $smarty.const.MA_ANSWER_OTHER}Other	{elseif $instance.answer_num > 0}{$instance.answer_gram}	{/if}
{/foreach}
{/strip}{if isset($smarty.get.mod_ans) && isset($sample.moder_answer_gram)}	{$sample.moder_answer_gram}{else}{/if}

{/foreach}
