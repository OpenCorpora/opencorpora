{nocache}
{$titles = $achievements_titles}
{if $achievements_unseen}
    <script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script>
    <link rel="stylesheet" href="/assets/css/animate.min.css">
    {assign var="single" value=count($achievements_unseen)==1}
    <div class="modal hide fade a-modal {if $single}a-modal-square{/if}">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Поздравляем!</h3>
        </div>
        <div class="modal-body fs0-fix">
        {foreach $achievements_unseen as $a}
            {counter assign=id}
            <div class="{if !$single}inline-50{/if} a-wrap">
                <div class="achievement-wrap achievement-{$a->css_class} {if !$single}achievement-small{else}achievement-medium{/if} {if $a->level <= 1}bouncy{/if}"
                data-tab-name="{$a->css_class}-tab">
                    {if $a->level}
                        <div class="achievement-level achievement-{$a->css_class}-level
                        {if $a->level > 1}bouncy{/if}">{$a->level}</div>
                    {/if}
                </div>
                <div class="a-desc">
                    {if $a->level <= 1}
                        {$titles[$a->css_class].popup_text}
                    {else}
                        {$randomindex=$titles.global.cheers|@array_rand}
                        {$titles.global.cheers.$randomindex}
                    {/if}

                    {$desc = "Я получил(а) бейдж"}
                    {if $a->level > 1}
                        {$desc = "`$desc` `$a->level`-го уровня"}
                    {/if}
                    {$desc = "`$desc`!"}
                    <div class="yashare-auto-init"
                        data-yashareL10n="ru"
                        data-yashareType="none"
                        data-yashareQuickServices="vkontakte,facebook,twitter"
                        data-yashareTitle="{$desc}"
                        data-yashareDescription="На тебя тоже хватит!"
                        data-yashareImage="http://opencorpora.org/assets/img/badges/share/{$a->css_class}.png"
                        data-yashareLink="http://opencorpora.org/page=achievement&uid={$smarty.session.user_id}&type={$a->css_class}"></div>
                </div>
            </div>
        {/foreach}

        </div>
        <div class="modal-footer">
            <a href="/user.php" class="btn btn-link pull-left">Мои бейджи</a>
            <a href="#" class="btn btn-primary" data-dismiss="modal">Круто!</a>
        </div>
    </div>

    <script>
        $('.a-modal').on('shown', function() {

            $.post("/ajax/game_mark_shown.php");
            $(this).find('.bouncy').addClass("animated bounceIn");
            // $.post("/ajax/game_mark_shown.php");
        });

        $('.a-modal').modal('show');

        $('.modal .achievement-wrap').click(function() {
            document.location.href = "/?page=achievements#" + $(this).attr('data-tab-name');
        });
    </script>
{/if}
{/nocache}
