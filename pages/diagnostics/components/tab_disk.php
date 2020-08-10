<?php
_logs_print_table_structure();

/*
Keys used from Log:

    container_stats:
        container
        time
        io_r
        io_w

*/
?>

<hr>

<div id="_logs_tab_disk">
</div>


<style type="text/css">
#_logs_tab_disk > .panel > .panel-heading {
    background-image: none;
}

#_logs_tab_disk dl {
    margin-bottom: 0;
}

#_logs_tab_disk dl dd {
    overflow-wrap: break-word;
}
</style>


<script type="text/javascript">
var _LOGS_CONTAINER_DISK_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  <div class="panel-heading" style="background-color: {color}"><strong>Log: </strong>{log} <span style="float: right"><strong>Container: </strong>{container_name}</span></div>
  <div class="panel-body">

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt><u>Container</u>:</dt><dd></dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt>Name</dt>
          <dd>{container_name}</dd>
        </dl>
    </div>

    <div id="_log{log_i}_cont{container}_disk_canvas_container"></div>

  </div>
</div>`;


function _tab_disk_render_single_log(key, seek, log_i){
    if (!seek.startsWith('/container_stats')) return;
    let color = get_log_info(key, '_color');
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    let log_containers = window._DIAGNOSTICS_LOGS_DATA[key]['/containers'];
    let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
    // aggregate data
    let data = {};
    log_data.forEach(function(cont){
        let c_id = cont['container'];
        if (!data.hasOwnProperty(c_id)) {
            data[c_id] = {
                log: key,
                log_i: log_i,
                color: 'rgba({0}, 0.6)'.format(color),
                container: cont['container'],
                container_name: log_containers[cont['container']],
                io_r: [],
                io_w: []
            };
        }
        let rel_time = parseInt(cont['time'] - start_time);
        // add temporal data
        data[c_id].io_r.push({x: rel_time, y: parseFloat(cont['io_r'])});
        data[c_id].io_w.push({x: rel_time, y: cont['io_w']});
    });
    // remove past history
    for (const [_, c_data] of Object.entries(data)) {
        if (c_data.io_r.length <= 0) continue;
        let ior_start = c_data.io_r[0].y;
        let iow_start = c_data.io_w[0].y;
        c_data.io_r = c_data.io_r.map(function (p) {
            return {x: p.x, y: (p.y - ior_start)}
        });
        c_data.io_w = c_data.io_w.map(function (p) {
            return {x: p.x, y: (p.y - iow_start)}
        });
    }
    // draw each container
    for (const [c_id, container_data] of Object.entries(data)) {
        $('#_logs_tab_disk').append(
            _LOGS_CONTAINER_DISK_BLOCK_TEMPLATE.format(container_data)
        );
        // add Disk I/O canvas to container tab
        let io_rw_canvas = get_empty_canvas();
        $('#_logs_tab_disk #_log{0}_cont{1}_disk_canvas_container'.format(log_i, c_id)).append(io_rw_canvas);
        // render CPU usage
        new Chart(io_rw_canvas, {
            type: 'line',
            data: {
                labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                datasets: [
                    get_chart_dataset({
                        canvas: io_rw_canvas,
                        label: 'Disk (Read)',
                        data: container_data.io_r,
                        color: '0, 0, 0',
                        borderDash: [10, 5],
                        no_background: true
                    }),
                    get_chart_dataset({
                        canvas: io_rw_canvas,
                        label: 'Disk (Write)',
                        data: container_data.io_w,
                        color: '0, 0, 0',
                        no_background: true
                    })
                ]
            },
            options: {
                scales: {
                    yAxes: [
                        {
                            ticks: {
                                callback: function(label) {
                                    return toHumanReadableSize(label);
                                },
                                min: 0
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
}

// this gets executed when the tab gains focus
let _tab_disk_on_show = function(){
    let seek = ['/containers', '/container_stats/[container,time,io_r,io_w]'];
    fetch_log_data(seek, _tab_disk_render_single_log);
};

// this gets executed when the tab loses focus
let _tab_disk_on_hide = function(){
    $('#_logs_tab_disk').empty();
};

$('#_logs_tab_btns a[href="#disk"]').on('shown.bs.tab', _tab_disk_on_show);
$('#_logs_tab_btns a[href="#disk"]').on('hidden.bs.tab', _tab_disk_on_hide);
</script>
