<?php
_logs_print_table_structure();

/*
Keys used from Log:

    health:
        time
        status
        temp
        frequency
        volts
            ram
            core
        battery
            cell_voltage
            current

*/
?>

<hr>

<div id="_logs_tab_health">
    <br/>
    <h4>Overall status</h4>
    <div id="_logs_tab_health_status">
    </div>
    <br/><br/>
    <h4>CPU Temperature ('C)</h4>
    <div id="_logs_tab_health_cpu_temp">
    </div>
    <br/><br/>
    <h4>CPU Frequency (GHz)</h4>
    <div id="_logs_tab_health_cpu_frequency">
    </div>
    <br/><br/>
    <h4>CPU Voltage (V)</h4>
    <div id="_logs_tab_health_cpu_voltage">
    </div>
    <br/><br/>
    <h4>RAM Voltage (V)</h4>
    <div id="_logs_tab_health_ram_voltage">
    </div>
    <br/><br/>
    <h4>Battery Cell Voltage (V)</h4>
    <div id="_logs_tab_health_battery_cell_voltage">
    </div>
    <br/><br/>
    <h4>Battery Output Current (A)</h4>
    <div id="_logs_tab_health_battery_current">
    </div>
</div>


<script type="text/javascript">

function _tab_health_render_logs(){
    let status_datasets = [];
    let cpu_temp_datasets = [];
    let cpu_freq_datasets = [];
    let cpu_volt_datasets = [];
    let ram_volt_datasets = [];
    let battery_volt_datasets = [];
    let battery_current_datasets = [];
    let seek = '/health';
    let status_to_val = {'ok': 0, 'warning': 1, 'error': 2};
    let val_to_status = ['ok', 'warning', 'error'];

    get_listed_logs('_key').forEach(function(key){
        let color = get_log_info(key, '_color');
        let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
        let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
        let log_legend_entry = '{0} ({1})'.format(
            window._DIAGNOSTICS_LOGS_DATA[key]['/general'].group,
            window._DIAGNOSTICS_LOGS_DATA[key]['/general'].subgroup
        );
        // create datasets
        // Overall status
        let status = log_data.map(function(e){return {
            x: parseInt(e.time - start_time),
            y: status_to_val[e.status]
        }});
        let status_txt = log_data.map(function(e){
            switch (e.status) {
                case "warning":
                    return e.status_msgs.filter(m => m.startsWith('Warning:')).map(m => m.slice(9));
                case "error":
                    return e.status_msgs.filter(m => m.startsWith('Error:')).map(m => m.slice(7));
                default:
                    return ['OK'];
            }
        });
        status_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: status,
            color: color,
            _txt: status_txt
        }));
        // CPU temperature
        let cpu_temp = log_data.map(function(e){return {
            x: parseInt(e.time - start_time),
            y: ($.type(e.temperature) === "string")?
                parseFloat(e.temperature.slice(0,-2)) : e.temperature
        }});
        cpu_temp_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: cpu_temp,
            color: color
        }));
        // CPU frequency
        let cpu_frequency = log_data.map(function(e){
            return {
                x: parseInt(e.time - start_time),
                y: (e.cpu.frequency.current / (10 ** 9)).toFixed(4)
            };
        });
        cpu_freq_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: cpu_frequency,
            color: color
        }));
        // CPU voltage
        let cpu_volt = log_data.map(function(e){return {
            x: parseInt(e.time - start_time),
            y: ($.type(e.volts.core) === "string")?
                parseFloat(e.volts.core.slice(0,-1)) : e.volts.core
        }});
        cpu_volt_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: cpu_volt,
            color: color
        }));
        // RAM voltage
        let ram_volt = log_data.map(function(e){return {
            x: parseInt(e.time - start_time),
            y: ($.type(e.volts.ram) === "string")?
                parseFloat(e.volts.ram.slice(0,-1)) : e.volts.ram
        }});
        ram_volt_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: ram_volt,
            color: color
        }));
        // Battery Cell voltage
        let battery_cell_volt = log_data.map(function(e){
            return {
                x: parseInt(e.time - start_time),
                y: e.battery.cell_voltage.toFixed(2)
            };
        });
        battery_volt_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: battery_cell_volt,
            color: color
        }));
        // Battery Output current
        let battery_current = log_data.map(function(e){
            return {
                x: parseInt(e.time - start_time),
                y: e.battery.current.toFixed(2)
            };
        });
        battery_current_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: battery_current,
            color: color
        }));
    });
    // ---
    // add device status canvas to tab
    let status_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_status').append(status_canvas);
    // render Overall status
    new Chart(status_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: status_datasets
        },
        options: {
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex]._txt[tooltipItem.index].join('; ');
                    }
                }
            },
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return val_to_status[label];
                            },
                            min: -1,
                            max: 3
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
    // add CPU temp canvas to tab
    let temp_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_cpu_temp').append(temp_canvas);
    // render CPU temperature
    new Chart(temp_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: cpu_temp_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(0)+' \'C';
                            },
                            min: 20,
                            max: 90
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
    // add CPU frquency canvas to tab
    let cpu_freq_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_cpu_frequency').append(cpu_freq_canvas);
    // render CPU frequency
    new Chart(cpu_freq_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: cpu_freq_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(1)+' GHz';
                            },
                            min: 0.0,
                            max: 2.0,
                            stepSize: 0.4
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
    // add volts canvas to tab
    let cpu_volt_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_cpu_voltage').append(cpu_volt_canvas);
    // render RAM usage
    new Chart(cpu_volt_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: cpu_volt_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(1)+' V';
                            },
                            min: 0.6,
                            max: 1.4
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
    // add volts canvas to tab
    let ram_volt_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_ram_voltage').append(ram_volt_canvas);
    // render RAM usage
    new Chart(ram_volt_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: ram_volt_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(1)+' V';
                            },
                            min: 0.6,
                            max: 1.4
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
    // add battery voltage canvas to tab
    let battery_volt_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_battery_cell_voltage').append(battery_volt_canvas);
    // render Battery Cell voltage
    new Chart(battery_volt_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: battery_volt_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(1)+' V';
                            },
                            min: 0.0,
                            max: 7.0
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
    // add battery voltage canvas to tab
    let battery_current_canvas = get_empty_canvas();
    $('#_logs_tab_health #_logs_tab_health_battery_current').append(battery_current_canvas);
    // render Battery Cell voltage
    new Chart(battery_current_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: battery_current_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(1)+' A';
                            },
                            min: -4.0,
                            max: 4.0
                        },
                        gridLines: {
                            display: false
                        }
                    }
                ],
                xAxes: [
                    {
                        ticks: {
                            callback: format_time
                        }
                    }
                ]
            }
        }
    });
}

// this gets executed when the tab gains focus
let _tab_health_on_show = function(){
    let seek = '/health';
    fetch_log_data(seek, null, _tab_health_render_logs);
};

// this gets executed when the tab loses focus
let _tab_health_on_hide = function(){
    $('#_logs_tab_health #_logs_tab_health_status').empty();
    $('#_logs_tab_health #_logs_tab_health_cpu_temp').empty();
    $('#_logs_tab_health #_logs_tab_health_cpu_frequency').empty();
    $('#_logs_tab_health #_logs_tab_health_cpu_voltage').empty();
    $('#_logs_tab_health #_logs_tab_health_ram_voltage').empty();
    $('#_logs_tab_health #_logs_tab_health_battery_cell_voltage').empty();
    $('#_logs_tab_health #_logs_tab_health_battery_current').empty();
};

$('#_logs_tab_btns a[href="#health"]').on('shown.bs.tab', _tab_health_on_show);
$('#_logs_tab_btns a[href="#health"]').on('hidden.bs.tab', _tab_health_on_hide);
</script>
