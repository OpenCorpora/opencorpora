<div class="container" style="position:relative;">
    <div class="ma_status_block">
        <div class="progress pull-left progress-success" title="Вам осталось до следующего уровня: {$user_rating.remaining_points}"><div class="bar" style="width: {100 - $user_rating.remaining_percent}%;">{if $user_rating.remaining_percent < 85}{$user_rating.current}{/if}</div></div>
        <div class="badges-block pull-left">
            <a href="#" title="Бейдж пока вам недоступен"><img class="img-circle" src="http://placehold.it/30/bc0000/ffffff"></a>
            <a href="#"><img class="img-circle" src="http://placehold.it/30/bc0000/ffffff"></a>
            <a href="#"><img class="img-circle" src="http://placehold.it/30/bc0000/ffffff"></a>
        </div>
    </div>
</div>
