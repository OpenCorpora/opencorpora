{extends file='common.tpl'}

{block name=content}
<script type="text/javascript">
    $(document).ready(
        function() {
            $('#details').click(function() {
                $('#output').toggle();
            });

            function show_output(res, action) {
                $indicator = $('#result-' + action).removeClass()
                                                   .addClass('result');
                if (!res.error) {
                    $indicator.addClass('green');
                }
                else {
                    $indicator.addClass('red');
                }

                $('#details').removeClass('hidden');

                $('#output').html(
                      '<pre>'
                    + res.output
                    + '</pre>'
                );
            }

            function do_request(action, control) {
                $('#result-' + action).ajaxStart(function() {
                    $(this).addClass('progress');
                    $('input[type=button]').attr('disabled', 'disabled');
                }).ajaxComplete(function() {
                    $(this).removeClass('progress');
                    $('input[type=button]').removeAttr('disabled');
                });
                $.get(
                    control.url,
                    function(res) { control.handler(res); },
                    'xml'
                );
            }

            var controls = {
                'run': {
                    'url': '/ajax/run_generator.php',
                    'handler': function(res) {
                        show_output(res, 'run');

                        $('#controls-test').removeClass('hidden');
                        $('#result-test').removeClass()
                                         .addClass('grey result');
                        $('#controls-publish').addClass('hidden');
                    }
                },
                'test': {
                    'url': '/ajax/run_test.php',
                    'handler': function(res) {
                        show_output(res, 'test');

                        $('#result-publish').removeClass()
                                            .addClass('grey result');
                        $('#controls-publish').removeClass('hidden');
                    }
                },
                'publish': {
                    'url': '/ajax/publish_update.php',
                    'handler': function(res) {
                        show_output(res, 'publish');
                        $('#current-tag').text($('#next-tag').text());
                        $('#next-tag').text('n/a');
                    }
                }
            };

            $.each(
                controls,
                function(k, v) {
                    $('#button-' + k).click(function() {
                        do_request(k, v);
                    });
                }
            );
        }
    );
</script>
<style type="text/css">
    .green, .enabled  { background-color: #0c3; }
    .grey, .disabled  { background-color: #ccc; }
    .red, .error      { background-color: #f00; }
    .hidden { display: none; }
    .result { width: 30px;   }
    .progress {
        background: url(/assets/img/ajax-loader.gif) no-repeat center center;
    }
    .pseudo-link {
        border-bottom: 1px dotted;
        cursor: pointer;
        color: #009;
    }
</style>
<h2>Что происходит на этой странице?</h2>
<p>Путём нажатия на кнопку &laquo;Запустить генератор&raquo; генерируются (новые) данные для токенизатора. Поле &laquo;тэг&raquo; определяет версию генератора, которая будет за этими данными ходить. Обновлять данные можно при соблюдении двух условий:</p>
<ol>
<li>все согласны, что в текущей токенизации нет глупостей (глупости обычно видны <a href='/qa.php?act=tokenizer'>здесь</a>),
<li>(если мы впервые генерируем данные для этого тэга) Алексей подтверил, что модуль синхронизирован с продакшен-токенизатором.
</ol>
<p>Правильный тэг смотреть <a href="http://search.cpan.org/perldoc?Lingua::RU::OpenCorpora::Tokenizer">в модуле</a> (<code>Lingua::RU::OpenCorpora::Tokenizer::Updater->data_version()</code>). Для верности можно спросить у Алексея ({mailto address="ksurent@gmail.com" encode="javascript"}).</p>
<br/><br/>
<div>
    <div style="margin-bottom: 2em;">
        <form action="?act=toggle" method="post">
            Статус:
            <span class="{$status}" id="status" title="Установлен {$since}">
                {if $status == "enabled"}Включен
                {elseif $status == "disabled"}Выключен
                {else}Ошибка
                {/if}
            </span>
            <br/>
            Текущий тэг: <span id="current-tag">{$tag}</span>
            <br/>
            Следующий тэг: <span id="next-tag">{$next}<span>
            <br/>
            <input type="submit" value="Переключить статус"/>
        </form>
    </div>
    {if $status == "enabled" && $next !== "n/a"}
        <div style="margin-bottom: 1em;">
            <table id="controls">
                <tr id="controls-run">
                    <td id="result-run" class="grey result">&nbsp;</td>
                    <td><input type="button" id="button-run" value="Запустить"/></td>
                </tr>
                <tr id="controls-test" class="hidden">
                    <td id="result-test" class="grey result">&nbsp;</td>
                    <td><input type="button" id="button-test" value="Протестировать"/></td>
                </tr>
                <tr id="controls-publish" class="hidden">
                    <td id="result-publish" class="grey result">&nbsp;</td>
                    <td><input type="button" id="button-publish" value="Опубликовать"/></td>
                </tr>
            </table>
            <span class="pseudo-link hidden" id="details">Подробности</span>
            <div id="output" class="hidden"></div>
        </div>
    {/if}
</div>
{/block}
