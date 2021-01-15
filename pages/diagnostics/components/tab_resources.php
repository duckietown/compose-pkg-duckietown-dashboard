<?php
_logs_print_table_structure();

/*
Keys used from Log:

    general:
        time

    health:
        time
        cpu
            percentage
        memory
            percentage
        swap
            percentage
        battery
            percentage

*/
?>

<hr>

<div id="_logs_tab_resources">
    <br/>
    <h4>CPU Usage</h4>
    <div id="_logs_tab_resources_cpu">
    </div>
    <br/><br/>
    <h4>Ram Usage</h4>
    <div id="_logs_tab_resources_ram">
    </div>
    <br/><br/>
    <h4>Swap Usage</h4>
    <div id="_logs_tab_resources_swap">
    </div>
    <br/><br/>
    <h4>Battery Level</h4>
    <div id="_logs_tab_resources_batt">
    </div>
</div>


<script type="text/javascript">

function _tab_resources_render_logs(){
    let seek = '/health';
    let pcpu_datasets = [];
    let pmem_datasets = [];
    let pswap_datasets = [];
    let pbatt_datasets = [];
    get_listed_logs('_key').forEach(function(key){
        let color = get_log_info(key, '_color');
        let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
        let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
        let log_legend_entry = '{0} ({1})'.format(
            window._DIAGNOSTICS_LOGS_DATA[key]['/general'].group,
            window._DIAGNOSTICS_LOGS_DATA[key]['/general'].subgroup
        );
        // create datasets
        let pcpu = log_data.map(function(e){return {x: parseInt(e.time - start_time), y: e.cpu.percentage}});
        let pmem = log_data.map(function(e){return {x: parseInt(e.time - start_time), y: e.memory.percentage}});
        let pswap = log_data.map(function(e){return {x: parseInt(e.time - start_time), y: e.swap.percentage}});
        let pbatt = log_data.map(function(e){return {x: parseInt(e.time - start_time), y: e.battery.percentage}});
        // ---
        pcpu_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: pcpu,
            color: color
        }));
        pmem_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: pmem,
            color: color
        }));
        pswap_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: pswap,
            color: color
        }));
        pbatt_datasets.push(get_chart_dataset({
            label: log_legend_entry,
            data: pbatt,
            color: color
        }));
    });
    // add CPU canvas to tab
    let cpu_canvas = get_empty_canvas();
    $('#_logs_tab_resources #_logs_tab_resources_cpu').append(cpu_canvas);
    // render CPU usage
    new Chart(cpu_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: pcpu_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(0)+' %';
                            },
                            min: 0,
                            max: 100
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
    // add RAM canvas to tab
    let ram_canvas = get_empty_canvas();
    $('#_logs_tab_resources #_logs_tab_resources_ram').append(ram_canvas);
    // render RAM usage
    new Chart(ram_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: pmem_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(0)+' %';
                            },
                            min: 0,
                            max: 100
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
    // add SWAP canvas to tab
    let swap_canvas = get_empty_canvas();
    $('#_logs_tab_resources #_logs_tab_resources_swap').append(swap_canvas);
    // render RAM usage
    new Chart(swap_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: pswap_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(0)+' %';
                            },
                            min: 0,
                            max: 100
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
    // add BATTERY canvas to tab
    let battery_canvas = get_empty_canvas();
    $('#_logs_tab_resources #_logs_tab_resources_batt').append(battery_canvas);
    // render RAM usage
    new Chart(battery_canvas, {
        type: 'line',
        data: {
            labels: window._DIAGNOSTICS_LOGS_X_RANGE,
            datasets: pbatt_datasets
        },
        options: {
            scales: {
                yAxes: [
                    {
                        ticks: {
                            callback: function(label) {
                                return label.toFixed(0)+' %';
                            },
                            min: 0,
                            max: 100
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
let _tab_resources_on_show = function(){
    let seek = '/health';
    fetch_log_data(seek, null, _tab_resources_render_logs);
};

// this gets executed when the tab loses focus
let _tab_resources_on_hide = function(){
    $('#_logs_tab_resources #_logs_tab_resources_cpu').empty();
    $('#_logs_tab_resources #_logs_tab_resources_ram').empty();
    $('#_logs_tab_resources #_logs_tab_resources_swap').empty();
    $('#_logs_tab_resources #_logs_tab_resources_batt').empty();
};

$('#_logs_tab_btns a[href="#resources"]').on('shown.bs.tab', _tab_resources_on_show);
$('#_logs_tab_btns a[href="#resources"]').on('hidden.bs.tab', _tab_resources_on_hide);
</script>
