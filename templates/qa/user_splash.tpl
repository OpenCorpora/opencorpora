{if $new_badge}
    <div class="alert alert-block clearfix" id="badge-alert">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <div class="pull-left" style="margin-right: 15px;"><img src="img/{if $new_badge.image}badges/{$new_badge.image}-100x100.png{else}icon_target.png{/if}"></div>
        <h4>Поздравляем!</h4>
        Вы получили медаль <strong>«{$new_badge.name}»</strong>{if $new_badge.description} &mdash; {$new_badge.description}{/if}! Спасибо, что помогаете нам!
    </div>
    <script>
        $("#badge-alert").bind('close',function(){
            $.get('ajax/game_mark_shown.php',{ 'act':'badge', 'badge_id':{$new_badge.id} })
        })
    </script>
{/if}
{if $new_level > 0}
    <div class="alert alert-block alert-success clearfix" id="level-alert">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <div class="pull-left" style="margin-right: 15px;"><img src="img/icon_trophy_black.png"></div>
        <h4>Ура!</h4>
        Вы достигли <strong>{$new_level}-го Уровня!</strong> Спасибо, что помогаете нам!
    </div>
    <script>
        $("#level-alert").bind('close',function(){
            $.get('ajax/game_mark_shown.php',{ 'act':'level', 'level':{$new_level} })
        })
    </script>
{/if}
