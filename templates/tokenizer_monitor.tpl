{extends file='common.tpl'}

{block name=content}
<style type="text/css">
    #chart, #chart2 {
        width: 800px;
        height: 600px;
    }
    #update {
        margin-right: 3em;
    }
    #threshold-label {
        margin-left: 1em;
    }
    .controls {
        margin-top: 1em;
    }
</style>
<script type="text/javascript" src="{$web_prefix}/js/jquery.flot.min.js"></script>
<script type="text/javascript">
{literal}
    $(document).ready(function() {
        $('#tabs').tabs();

        var dates = $('#from, #until').datepicker({
            dateFormat: 'yy-mm-dd',
            constraintInput: true,
            onSelect: function(selected) {
                var option = this.id == 'from' ? 'minDate' : 'maxDate',
                    instance = $(this).data('datepicker'),
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat,
                        selected,
                        instance.settings
                    );
                dates.not(this).datepicker('option', option, date);
            }
        });
        $(dates[1]).datepicker('setDate', new Date());
        $(dates[0]).datepicker('setDate', '-7d');

        function update() {
            $.get(
                '/ajax/tokenizer_monitor.php',
                {
                    from: $('#from').val(),
                    until: $('#until').val()
                },
                function(json) {
                    plot(json, 'metrics');
                    plot(json, 'f-score-threshold');
                },
                'json'
            );
        }

        function plot(json, mode) {
            if(mode == 'metrics') {
                plot_metrics(json);
            }
            else if(mode == 'f-score-threshold') {
                plot_fscore_threshold(json);
            }
        }

        function plot_fscore_threshold(json) {
            var intermideate = {};
            $.each(
                json.F1,
                function(threshold) {
                    $.each(
                        json.F1[threshold],
                        function(idx, val) {
                            // that's what you get when you can't use autovivification, eat it
                            var d = new Date(val[0]),
                                ymd = [d.getFullYear(), d.getMonth()+1, d.getDate()].join('-');

                            if(ymd in intermideate) {
                                intermideate[ymd].data.push([parseFloat(threshold), val[1]]);
                            }
                            else {
                                intermideate[ymd] = {
                                    label: ymd,
                                    data: []
                                };
                            }
                        }
                    );
                }
            );
            var datasets = Object.keys(intermideate).map(function(k) {
                intermideate[k].data.sort(function(a, b) {
                    return a[0] - b[0];
                });
                return intermideate[k];
            });

            var options = {
                series: {
                    lines: {show: true}
                },
                legend: {
                    show: true,
                    position: 'se',
                    backgroundOpacity: 0.2
                }
            };

            $.plot($('#chart2'), datasets, options);

            var max = [0, 0];
            for(var ymd in intermideate) {
                var curr = intermideate[ymd].data;
                for(var i = 0; i < curr.length; i++) {
                    if(curr[i][1] > max[1]) max = curr[i];
                }
            }
            $('#F1-max').text('Максимальное значение F-score=' + max[1] + ' достигается при threshold=' + max[0]);
        }

        function plot_metrics(json) {
            var datasets = [];
            $.each(
                json,
                function(idx) {
                    if($('#' + idx + ':checked').length == 0) return;

                    var dataset = {
                        label: idx,
                        data: json[idx][$('#threshold').val()]
                    };
                    datasets.push(dataset);
                }
            );

            var options = {
                xaxis: {
                    mode: 'time',
                    timeformat: '%d-%m-%y',
                    minTickSize: [1, 'day']
                },
                legend: {
                    show: true,
                    position: 'se',
                    backgroundOpacity: 0.2
                },
                series: {
                    lines: {show: true},
                    points: {show: true}
                }
            };

            $.plot($('#chart'), datasets, options);
        }

        $('#update, #precision, #recall, #F1').click(function() {
            update();
        });

        $('#threshold').change(function() {
            update();
        });

        $('#update').click();
    });
{/literal}
</script>

<h1>{t}Контроль качества токенизатора{/t}</h1>

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">{t}Все метрики{/t}</a></li>
        <li><a href="#tabs-2">{t}F-score и threshold{/t}</a></li>
    </ul>

    <div id="tabs-1">
        <div id="chart"></div>

        <div class="controls">
            <input type="checkbox" id="precision" checked="checked"/>
            <label for="precision">precision</label>

            <input type="checkbox" id="recall" checked="checked"/>
            <label for="recall">recall</label>

            <input type="checkbox" id="F1" checked="checked"/>
            <label for="F1">F1</label>

            <label for="threshold" id="threshold-label">Threshold:</label>
            <select name="threshold" id="threshold">
                <option>0</option>
                <option>0.01</option>
                <option>0.05</option>
                <option>0.1</option>
                <option>0.15</option>
                <option>0.2</option>
                <option>0.25</option>
                <option>0.3</option>
                <option>0.35</option>
                <option>0.4</option>
                <option>0.45</option>
                <option>0.5</option>
                <option>0.55</option>
                <option>0.6</option>
                <option>0.65</option>
                <option>0.7</option>
                <option>0.75</option>
                <option>0.8</option>
                <option>0.85</option>
                <option>0.9</option>
                <option>0.95</option>
                <option>0.99</option>
                <option>1</option>
            </select>
        </div>
    </div>

    <div id="tabs-2">
        <div id="chart2"></div>

        <div id="F1-max" class="controls"></div>
    </div>
</div>

<div class="controls">
    <label for="from">{t}С{/t}</label>
    <input type="text" id="from"/>

    <label for="until">{t}По{/t}</label>
    <input type="text" id="until"/>

    <input type="button" id="update" value="{t}Обновить{/t}"/>
</div>

{/block}
