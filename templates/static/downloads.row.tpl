{*
    variables are:
        $N
        $suffix
        $lowercase
        $lemma
        $words
*}
<tr class="nval_{$N}{if $lowercase} lc{/if}{if $words != ''} wds{/if}">
    <td>{$N}_{$suffix}</td>
    <td align='center'>{if $lemma}+{else}&mdash;{/if}</td>
    <td align='center'>{if $lowercase}&mdash;{else}+{/if}</td>
    <td align='center'>{if $words == 'A'}+ (A**){elseif $words == 'B'}+ (B**){elseif $words}+{else}&mdash;{/if}</td>
    <td><a href="{$web_prefix}/files/export/ngrams/{if $N == 1}unigrams{elseif $N == 2}bigrams{elseif $N == 3}trigrams{/if}{if $words == 'A'}.cyrA{elseif $words == 'B'}.cyrB{elseif $words}.cyr{/if}{if $lowercase}.lc{/if}.bz2">архив .bz2</a> ({$dl.ngram.$N.$suffix.bz2.size} Мб)</td>
    <td><a href="{$web_prefix}/files/export/ngrams/{if $N == 1}unigrams{elseif $N == 2}bigrams{elseif $N == 3}trigrams{/if}{if $words == 'A'}.cyrA{elseif $words == 'B'}.cyrB{elseif $words}.cyr{/if}{if $lowercase}.lc{/if}.zip">архив .zip</a> ({$dl.ngram.$N.$suffix.zip.size} Мб)</td>
    <td><a href="?page=top100&amp;type={$N}_{$suffix}">top100</a></td>
    <td>{$dl.ngram.$N.$suffix.updated}</td>
</tr>
