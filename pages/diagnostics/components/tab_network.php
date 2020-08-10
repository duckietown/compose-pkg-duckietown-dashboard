<?php
_logs_print_table_structure();

/*
Keys used from Log:

    container_stats:
        container
        time
        network


*/
?>

<hr>

<div id="_logs_tab_network">
</div>


<style type="text/css">
#_logs_tab_network > .panel > .panel-heading {
    background-image: none;
}

#_logs_tab_network dl {
    margin-bottom: 0;
}

#_logs_tab_network dl dd {
    overflow-wrap: break-word;
}
</style>


<script type="text/javascript">
var _LOGS_CONTAINER_NET_BLOCK_TEMPLATE = `
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

    <div id="_log{log_i}_cont{container}_network_canvas_container" style="margin-top: 90px"></div>

  </div>
</div>`;


function _tab_network_render_single_log(key, seek, log_i){
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
                network: {}
            };
        }
        let rel_time = parseInt(cont['time'] - start_time);
        // add temporal data
        for (const [iface_name, iface_data] of Object.entries(cont['network'])) {
            if (!data[c_id].network.hasOwnProperty(iface_name))
                data[c_id].network[iface_name] = {io_r: [], io_w: []};
            data[c_id].network[iface_name].io_r.push({x: rel_time, y: iface_data['rx']});
            data[c_id].network[iface_name].io_w.push({x: rel_time, y: iface_data['tx']});
        }
    });
    // remove past history
    for (const [_, c_data] of Object.entries(data)) {
        for (const [_, iface_data] of Object.entries(c_data['network'])) {
            if (iface_data.io_r.length <= 0) continue;
            let ior_start = iface_data.io_r[0].y;
            let iow_start = iface_data.io_w[0].y;
            iface_data.io_r = iface_data.io_r.map(function (p) {
                return {x: p.x, y: (p.y - ior_start)}
            });
            iface_data.io_w = iface_data.io_w.map(function (p) {
                return {x: p.x, y: (p.y - iow_start)}
            });
        }
    }
    // draw each container
    for (const [c_id, container_data] of Object.entries(data)) {
        $('#_logs_tab_network').append(
            _LOGS_CONTAINER_NET_BLOCK_TEMPLATE.format(container_data)
        );
        let container_plots_div = $('#_logs_tab_network #_log{0}_cont{1}_network_canvas_container'.format(log_i, c_id));
        // add Network I/O canvas to container tab
        for (const [iface_name, iface_data] of Object.entries(container_data['network'])) {
            container_plots_div.append($('<h4 class="col-md-12"/>').append('Network device: '+iface_name));
            let net_rw_canvas = get_empty_canvas();
            container_plots_div.append(net_rw_canvas);
            // render IFace usage
            new Chart(net_rw_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        get_chart_dataset({
                            canvas: net_rw_canvas,
                            label: 'Network (Read)',
                            data: iface_data.io_r,
                            color: '0, 0, 0',
                            borderDash: [10, 5],
                            no_background: true
                        }),
                        get_chart_dataset({
                            canvas: net_rw_canvas,
                            label: 'Network (Write)',
                            data: iface_data.io_w,
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
}

// this gets executed when the tab gains focus
let _tab_network_on_show = function(){
    let seek = ['/containers', '/container_stats/[container,time,network]'];
    fetch_log_data(seek, _tab_network_render_single_log);
};

// this gets executed when the tab loses focus
let _tab_network_on_hide = function(){
    $('#_logs_tab_network').empty();
};

$('#_logs_tab_btns a[href="#network"]').on('shown.bs.tab', _tab_network_on_show);
$('#_logs_tab_btns a[href="#network"]').on('hidden.bs.tab', _tab_network_on_hide);
</script>
