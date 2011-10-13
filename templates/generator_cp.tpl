{extends file='common.tpl'}

{block name=content}
<script type="text/javascript">
    $(document).ready(
        function() {
            $('#tag').bind(
                'change keyup',
                function(ev) {
                    if($.trim($(ev.currentTarget).val()).length) {
                        $('#run').removeAttr('disabled');
                    }
                    else {
                        $('#run').attr('disabled', 'disabled');
                    }
                }
            );
            $('#details').click( function() {
                $('#output').toggle();
            } );
        }
    );
</script>
<style type="text/css">
    .enabled  { background-color: #0c3; }
    .disabled { background-color: #ccc; }
    .error    { background-color: #f00; }
    .pseudo-link {
        border-bottom: 1px dotted;
        cursor: pointer;
        color: #009;
    }
</style>
<h2>Что происходит на этой странице?</h2>
<p>Путём нажатия на кнопку &laquo;Запустить генератор&raquo; генерируются (новые) данные для токенизатора. Поле &laquo;тэг&raquo; определяет версию генератора, которая будет за этими данными ходить. Обновлять данные можно при соблюдении двух условий:</p>
<ol>
<li>все согласны, что в текущей токенизации нет глупостей (глупости обычно видны <a href='{$web_prefix}/qa.php?act=tokenizer'>здесь</a>),
<li>(если мы впервые генерируем данные для этого тэга) Алексей подтверил, что модуль синхронизирован с продакшен-токенизатором.
</ol>
<p>Правильный тэг смотреть <a href="http://search.cpan.org/~ksuri/Lingua-RU-OpenCorpora-Tokenizer-0.03/lib/Lingua/RU/OpenCorpora/Tokenizer.pm">в модуле</a> (совпадает с его текущей версией). Для верности можно спросить у Алексея ({mailto address="ksurent@gmail.com" encode="javascript"}).</p>
<br/><br/>
<div>
    <div style="margin-bottom: 2em;">
        <form action="?act=toggle" method="post">
            Текущий статус:
            <span class="{$status}" id="status" title="{t}Установлен{/t} {$since}">
                {if $status == "enabled"}{t}Включен{/t}
                {elseif $status == "disabled"}{t}Выключен{/t}
                {elseif $status == "running"}{t}Запущен{/t}
                {else}{t}Ошибка{/t}
                {/if}
            </span>
            {if $status !== "running"}
                <input type="submit" id="toggle" value="Переключить статус" style="margin-left: 1em;"/>
            {/if}
        </form>
    </div>
    {if $status == "disabled"}
        <div style="margin-bottom: 1em;">
            <form action="?act=run" method="post">
                <label for="tag">Тэг:</label>
                <input type="text" name="tag" id="tag"/>
                <input type="submit" id="run" value="Запустить генератор" disabled="disabled"/>
            </form>
        </div>
    {/if}
    {if isset($success)}
        <div>
            {if $success}
                <span class="enabled">{t}Обновление закончено{/t}</span>
            {else}
                <span class="error">{t}Произошла ошибка{/t}</span>
            {/if}
            <div>
                <a class="pseudo-link" id="details">Подробности</a>
                <div style="display: none;" id="output"><pre>{$output}</pre></div>
            </div>
        </div>
    {/if}
</div>
{/block}
