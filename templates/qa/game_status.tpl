<div class="container" style="position:relative;">
    <div class="ma_status_block">
        <div class="progress pull-left progress-success" title="Вам осталось до следующего уровня: {$user_rating.remaining_points}"><div class="bar" style="width: {100 - $user_rating.remaining_percent}%;">{if $user_rating.remaining_percent < 85}{$user_rating.current}{/if}</div></div>
        <div class="badges-block pull-left">
            <a href="#" title="Бейдж пока вам недоступен" class="badge-inactive"><img src="assets/img/icon_speed_60.png"></a>
            <a href="#"><img class="img-circle" src="assets/img/icon_glass.png"></a>
            <a href="#"><img class="img-circle" src="assets/img/icon_plus.png"></a>
        </div>
    </div>
</div>
