<?php
use \system\classes\Core;

_logs_print_table_structure();

/*
Keys used from Log:

    process_stats:
        time
        container
        ppid
        pid
        pcpu
        nthreads
        pmem
        command

    NOT USED:
        mem
        cputime

*/

$_condense_plots = True;
$_leaves_only = True;
$_show_all = False;
?>

<hr>

<script
    src="<?php echo Core::getJSscriptURL('bootstrap-slider.min.js', 'duckietown_diagnostics') ?>"
    type="text/javascript">
</script>
<link
    href="<?php echo Core::getCSSstylesheetURL('bootstrap-slider.min.css', 'duckietown_diagnostics') ?>"
    rel="stylesheet"
>

<style type="text/css">
#_logs_tab_processes > .panel > .panel-heading {
    background-image: none;
}

#_logs_tab_processes .panel-body div._proc_command{
    border: 1px solid lightgrey;
    padding: 5px 0 5px 10px;
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
    font-family: monospace;
    font-size: 0.9em;
}

#_logs_tab_processes .panel-body dl.dl-horizontal{
    margin-bottom: 0;
}
</style>


<div class="panel panel-default _logs_rigid_centered_component">
    <div class="panel-heading">Filter</div>
    <div class="panel-body">
        <form id="_logs_tab_processes_filter_form">
        <table style="width:100%">
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>CPU Usage (%)</dt>
                            <dd>
                                <input id="_logs_tab_processes_filter_pcpu" type="text"/><br/>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>RAM Usage (%)</dt>
                            <dd>
                                <input id="_logs_tab_processes_filter_pmem" type="text"/><br/>
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt># Threads</dt>
                            <dd>
                                <input id="_logs_tab_processes_filter_nthreads" type="text"/><br/>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Condense plots</dt>
                            <dd>
                                <input type="checkbox"
                                       data-toggle="toggle"
                                       data-onstyle="primary"
                                       data-class="fast"
                                       data-size="small"
                                       id="_logs_tab_processes_filter_condense_plots"
                                    <?php echo ($_condense_plots) ? 'checked' : '' ?>
                                >
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Show all processes</dt>
                            <dd>
                                <input type="checkbox"
                                       data-toggle="toggle"
                                       data-onstyle="primary"
                                       data-class="fast"
                                       data-size="small"
                                       id="_logs_tab_processes_filter_show_all"
                                    <?php echo ($_show_all) ? 'checked' : '' ?>
                                >
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="dl-horizontal">
                            <dt>Hide parents</dt>
                            <dd>
                                <input type="checkbox"
                                       data-toggle="toggle"
                                       data-onstyle="primary"
                                       data-class="fast"
                                       data-size="small"
                                       id="_logs_tab_processes_filter_leaves_only"
                                    <?php echo ($_leaves_only) ? 'checked' : '' ?>
                                >
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="height: 70px; vertical-align: top">
                <td>
                    <div class="col-md-12">
                        <dl class="dl-horizontal">
                            <dt style="padding-top: 6px">Command</dt>
                            <dd>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"> > </span>
                                    <input type="text" id="_logs_tab_processes_filter_command"
                                           class="form-control" placeholder="Command contains...">
                                </div>
                            </dd>
                        </dl>
                    </div>
                </td>
            </tr>
            <tr style="vertical-align: top">
                <td>
                    <div class="col-md-12 text-right">
                        <a role="button" class="btn btn-default" id="_logs_tab_processes_filter_reset">
                            Reset
                        </a>

                        <a role="button" class="btn btn-primary" id="_logs_tab_processes_filter_apply">
                            <span class="glyphicon glyphicon-filter" aria-hidden="true"></span>
                            Apply
                        </a>
                    </div>
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>

<hr>

<p>Found
 <strong id="_logs_tab_processes_num_processes">--</strong> processes in
 <strong id="_logs_tab_processes_num_groups">--</strong> groups.</p>
<hr>

<div id="_logs_tab_processes">
</div>


<script type="text/javascript">

let _LOGS_PROCESS_SELECTOR = "/process_stats";

let _LOGS_PROCESS_BLOCK_TEMPLATE = `
<div class="panel panel-default">
  {panel_heading}
  <div class="panel-body">
    <table style="width: 100%;">
        <tr>
            <td id="_log{log_i}_pid{pid}_process_info_container"></td>
        </tr>
        <tr>
            <td>
                <br/>
                <h4>CPU Usage (%)</h4>
                <div id="_log{log_i}_cpu_pid{pid}_canvas_container"></div>
                <br/><br/>
                <h4>RAM Usage (%)</h4>
                <div id="_log{log_i}_ram_pid{pid}_canvas_container"></div>
                <br/><br/>
                <h4>Number of Threads</h4>
                <div id="_log{log_i}_nthreads_pid{pid}_canvas_container"></div>
            </td>
        </tr>
        <tr>
            <td id="_log{log_i}_pid{pid}_command_container"></td>
        </tr>
    </table>

  </div>
</div>`;

let _LOG_PROCESSES_COMMAND_SPARSE_PANEL_HEADING = `
<div class="panel-heading" style="background-color: {panel_color}"><strong>Log: </strong>{log} <span style="float: right"><strong>Container: </strong>{container_name}</span></div>
`;

let _LOG_PROCESSES_COMMAND_CONDENSED_PANEL_HEADING = `
<div class="panel-heading"><strong>Process: </strong>{process_name_str_plain}</div>
`;

let _LOG_PROCESSES_PROCESS_INFO = `
<div class="col-md-6" style="background-color: {panel_color}">
    <dl class="dl-horizontal">
      <dt><u>Info</u>:</dt><dd></dd>
      <dt>Container</dt>
      <dd>{container_name}</dd>
    </dl>
</div>

{process_extra_info}

<div class="col-md-12">&nbsp;<br/></div>
`;

let _LOG_PROCESSES_COMMAND_SPARSE_EXTRA_INFO_PROCESS = `
<div class="col-md-3" style="background-color: {panel_color}">
    <dl class="dl-horizontal">
      <dt><u>Process</u>:</dt><dd></dd>
      <dt>Command</dt>
      <dd>{process_name_str}</dd>
    </dl>
</div>
<div class="col-md-3" style="background-color: {panel_color}">
    <dl class="dl-horizontal">
      <dt></dt><dd></dd>
      <dt>PID</dt>
      <dd>{pid}</dd>
    </dl>
</div>
`;

let _LOG_PROCESSES_COMMAND_CONDENSED_EXTRA_INFO_PROCESS = `
<div class="col-md-3" style="background-color: {panel_color}">
    <dl class="dl-horizontal">
      <dt><u>Process</u>:</dt><dd></dd>
      <dt>PID</dt>
      <dd>{pid}</dd>
    </dl>
</div>
<div class="col-md-3" style="background-color: {panel_color}">
    <dl class="dl-horizontal">
      <dt>&nbsp;</dt><dd>&nbsp;</dd>
      <dt>PPID</dt>
      <dd>{ppid}</dd>
    </dl>
</div>
`;

let _LOG_PROCESS_COMMAND_TEMPLATE = `
<div class="input-group" style="margin-top: 12px">
  <span class="input-group-addon" style="background-color: {command_color}"><strong>Command</strong></span>
    <div class="_proc_command">{command}</div>
</div>
`;

let _LOG_PROCESS_FILTER_DEFAULT_VALUES = {
  pcpu: [0, 100],
  pmem: [0, 100],
  nthreads: [0, 50],
  command: "",
  leaves_only: true,
  show_all: false,
  condense_plots: true,
  coverage: [0.1, 1.0]
};

let _LOG_PROCESS_FILTER_VALUES = {};

let _LOG_PROGRESS_PROC_GROUPS = {};


function _format_command(cmd){
    let indent = 0;
    let space = '&emsp; ';
    let out = [];
    cmd.split(' ').forEach(function(e, i){
        let _cur_space = space.repeat(indent);
        out.push(_cur_space + e);
        if (e.startsWith('/') || i === 0) indent += 1;
    });
    return out.join('<br/>');
}

function _tab_processes_render_single_log(key, seek, log_i){
    if (seek !== _LOGS_PROCESS_SELECTOR) return;
    let color = get_log_info(key, '_color');
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    let log_containers = window._DIAGNOSTICS_LOGS_DATA[key]['/containers'];
    let start_time = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
    let log_legend_entry = '{0} ({1})'.format(
        window._DIAGNOSTICS_LOGS_DATA[key]['/general'].group,
        window._DIAGNOSTICS_LOGS_DATA[key]['/general'].subgroup
    );
    // ---
    let number_of_processes = parseInt($('#_logs_tab_processes_num_processes').html()) || 0;
    let number_of_groups = parseInt($('#_logs_tab_processes_num_groups').html()) || 0;
    // filters
    let [min_pcpu, max_pcpu] = _LOG_PROCESS_FILTER_VALUES['pcpu'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['pcpu'];
    let [min_pmem, max_pmem] = _LOG_PROCESS_FILTER_VALUES['pmem'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['pmem'];
    let [min_nthreads, max_nthreads] = _LOG_PROCESS_FILTER_VALUES['nthreads'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['nthreads'];
    let command_filter = _LOG_PROCESS_FILTER_DEFAULT_VALUES['command'];
    if (_LOG_PROCESS_FILTER_VALUES.hasOwnProperty('command') && _LOG_PROCESS_FILTER_VALUES['command'].length > 0)
        command_filter = _LOG_PROCESS_FILTER_VALUES['command'];
    let condense_plots = _LOG_PROCESS_FILTER_DEFAULT_VALUES['condense_plots'];
    if (_LOG_PROCESS_FILTER_VALUES['condense_plots'] !== undefined)
        condense_plots = _LOG_PROCESS_FILTER_VALUES['condense_plots'];
    let [min_coverage, max_coverage] = _LOG_PROCESS_FILTER_VALUES['coverage'] || _LOG_PROCESS_FILTER_DEFAULT_VALUES['coverage'];
    let leaves_only = _LOG_PROCESS_FILTER_DEFAULT_VALUES['leaves_only'];
    if (_LOG_PROCESS_FILTER_VALUES['leaves_only'] !== undefined)
        leaves_only = _LOG_PROCESS_FILTER_VALUES['leaves_only'];
    // aggregate data
    let data = {};
    let max_n_points = 0;
    let parents_PID = new Set();
    log_data.forEach(function(proc){
        let PID = proc['pid'];
        let process_name = _find_process_name(proc['command']);
        let charts = {
            'pcpu': null,
            'pmem': null,
            'nthreads': null,
            'command': null,
            'process': null
        };
        let append = false;
        if (condense_plots && _LOG_PROGRESS_PROC_GROUPS.hasOwnProperty(process_name)) {
            charts = _LOG_PROGRESS_PROC_GROUPS[process_name];
            append = true;
        }
        if (!data.hasOwnProperty(PID)) {
            let container_name = _LOGS_PROCESS_SELECTOR.startsWith('/all_')?
                "Not available with 'Show all processes'" :
                log_containers[proc['container']];
            // ---
            data[PID] = {
                log: key,
                log_i: log_i,
                panel_color: condense_plots? 'rgba({0}, 0.4)'.format(color) : '',
                command_color: condense_plots ? 'rgba({0}, 0.5)'.format(color) : '#eee',
                container_name: container_name,
                process_name: process_name,
                process_name_str: process_name? '<strong>'+process_name+'</strong>' : '(check command below)',
                process_name_str_plain: process_name || '(check command below)',
                log_legend_entry: log_legend_entry,
                container: proc['container'],
                pid: PID,
                ppid: proc['ppid'],
                command: _format_command(proc['command']),
                pcpu: [],
                cputime: [],
                pmem: [],
                mem: [],
                nthreads: [],
                is_leaf: true,
                append: append,
                charts: charts
            };
            data[PID].panel_heading = condense_plots?
                _LOG_PROCESSES_COMMAND_CONDENSED_PANEL_HEADING.format(data[PID]) :
                _LOG_PROCESSES_COMMAND_SPARSE_PANEL_HEADING.format(data[PID]);
            data[PID].process_info = _LOG_PROCESSES_PROCESS_INFO.format({
                ...data[PID],
                process_extra_info: condense_plots?
                    _LOG_PROCESSES_COMMAND_CONDENSED_EXTRA_INFO_PROCESS.format(data[PID]) :
                    _LOG_PROCESSES_COMMAND_SPARSE_EXTRA_INFO_PROCESS.format(data[PID])
            });
        }
        parents_PID.add(proc['ppid']);
        let rel_time = parseInt(proc['time'] - start_time);
        // add temporal data
        data[PID].pcpu.push({x: rel_time, y: parseFloat(proc['pcpu'])});
        // data[PID].cputime.push({x: rel_time, y: proc['cputime']});
        data[PID].pmem.push({x: rel_time, y: parseFloat(proc['pmem'])});
        // data[PID].mem.push({x: rel_time, y: proc['mem']});
        data[PID].nthreads.push({x: rel_time, y: parseInt(proc['nthreads'])});
        // update stats
        max_n_points = Math.max(max_n_points, data[PID].pcpu.length);
    });
    // update fields
    for (const [_, proc_data] of Object.entries(data)) {
        proc_data.is_leaf = !parents_PID.has(proc_data.pid);
    }
    // filter processes
    let _filtered_data = {};
    for (const [pid, proc_data] of Object.entries(data)) {
        // 4. command
        if (!proc_data.command.includes(command_filter))
            continue;
        // 5. coverage
        if ((proc_data.pcpu.length / max_n_points) < min_coverage)
            continue;
        if ((proc_data.pcpu.length / max_n_points) > max_coverage)
            continue;
        // 6. leaves only
        if (leaves_only && !proc_data.is_leaf)
            continue;
        // 0. needs comparison
        if (proc_data.append)
            // add to filtered
            _filtered_data[pid] = proc_data;
        // 1. cpu usage
        if (proc_data.pcpu.filter(v => v.y >= min_pcpu).length <= 0)
            continue;
        if (proc_data.pcpu.filter(v => v.y >= max_pcpu).length > 0)
            continue;
        // 2. mem usage
        if (proc_data.pmem.filter(v => v.y >= min_pmem).length <= 0)
            continue;
        if (proc_data.pmem.filter(v => v.y >= max_pmem).length > 0)
            continue;
        // 3. threads usage
        if (proc_data.nthreads.filter(v => v.y >= min_nthreads).length <= 0)
            continue;
        if (proc_data.nthreads.filter(v => v.y >= max_nthreads).length > 0)
            continue;
        // add to filtered
        _filtered_data[pid] = proc_data;
    }
    data = _filtered_data;
    // draw each process
    for (const [pid, proc_data] of Object.entries(data)) {
        if (!proc_data.append) {
            $('#_logs_tab_processes').append(
                _LOGS_PROCESS_BLOCK_TEMPLATE.format(proc_data)
            );
            let command_container = $('#_logs_tab_processes #_log{0}_pid{1}_command_container'.format(log_i, pid));
            command_container.append(
                _LOG_PROCESS_COMMAND_TEMPLATE.format(proc_data)
            );
            if (proc_data.process_name){
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name] = {};
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['command'] = command_container;
            }

            let process_container = $('#_logs_tab_processes #_log{0}_pid{1}_process_info_container'.format(log_i, pid));
            process_container.append(proc_data.process_info);
            if (proc_data.process_name){
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['process'] = process_container;
            }
        }else{
            _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['command'].append(
                _LOG_PROCESS_COMMAND_TEMPLATE.format(proc_data)
            );
            _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['process'].append(
                proc_data.process_info
            );
        }
        // add CPU canvas to process tab
        let pcpu_dataset = get_chart_dataset({
            label: proc_data.log_legend_entry,
            data: proc_data.pcpu,
            color: color,
            background_alpha: 0.4
        });
        if (!proc_data.append) {
            let cpu_canvas = get_empty_canvas();
            $('#_logs_tab_processes #_log{0}_cpu_pid{1}_canvas_container'.format(log_i, pid)).append(cpu_canvas);
            // render CPU usage
            let chart = new Chart(cpu_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        pcpu_dataset
                    ]
                },
                options: {
                    animation: false,
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    callback: function (label) {
                                        return label.toFixed(0) + ' %';
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
            if (proc_data.process_name){
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['pcpu'] = chart;
            }
        }else{
            proc_data.charts['pcpu'].data.datasets.push(pcpu_dataset);
            proc_data.charts['pcpu'].update();
        }
        // add RAM canvas to process tab
        let pmem_dataset = get_chart_dataset({
            label: proc_data.log_legend_entry,
            data: proc_data.pmem,
            color: color,
            background_alpha: 0.4
        });
        if (!proc_data.append) {
            let ram_canvas = get_empty_canvas();
            $('#_logs_tab_processes #_log{0}_ram_pid{1}_canvas_container'.format(log_i, pid)).append(ram_canvas);
            // render RAM usage
            let chart = new Chart(ram_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        pmem_dataset
                    ]
                },
                options: {
                    animation: false,
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    callback: function (label) {
                                        return label.toFixed(0) + ' %';
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
            if (proc_data.process_name) {
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['pmem'] = chart;
            }
        }else{
            proc_data.charts['pmem'].data.datasets.push(pmem_dataset);
            proc_data.charts['pmem'].update();
        }
        // add NTHREADS canvas to process tab
        let nthreads_dataset = get_chart_dataset({
            label: proc_data.log_legend_entry,
            data: proc_data.nthreads,
            color: color,
            no_background: true
        });
        if (!proc_data.append) {
            let nthreads_canvas = get_empty_canvas();
            $('#_logs_tab_processes #_log{0}_nthreads_pid{1}_canvas_container'.format(log_i, pid)).append(nthreads_canvas);
            // render NTHREADS usage
            let chart = new Chart(nthreads_canvas, {
                type: 'line',
                data: {
                    labels: window._DIAGNOSTICS_LOGS_X_RANGE,
                    datasets: [
                        nthreads_dataset
                    ]
                },
                options: {
                    animation: false,
                    scales: {
                        yAxes: [
                            {
                                ticks: {
                                    callback: function (label) {
                                        return label;
                                    },
                                    min: 1
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
            if (proc_data.process_name) {
                _LOG_PROGRESS_PROC_GROUPS[proc_data.process_name]['nthreads'] = chart;
            }
            number_of_groups += 1;
        }else{
            proc_data.charts['nthreads'].data.datasets.push(nthreads_dataset);
            proc_data.charts['nthreads'].update();
        }
        number_of_processes += 1;
    }
    // update processes counter
    $('#_logs_tab_processes_num_processes').html(number_of_processes);
    $('#_logs_tab_processes_num_groups').html(number_of_groups);
}

function _find_process_name(command) {
    let parts = command.split(' ');
    let process_name = null;
    // ROS node
    for (let i = 0; i < parts.length; i++){
        if (parts[i].startsWith('__name:='))
            return parts[i].slice(8);
    }
    // python script
    for (let i = 0; i < parts.length; i++){
        if (parts[i].startsWith('/usr/bin/python') && i < parts.length - 1)
            return parts[i+1].split('/').slice(-1)[0];
    }
    // binary
    ['bin', 'sbin'].forEach(function (dir) {
        if (process_name !== null) return;
        if (parts[0].includes('/{0}/'.format(dir))) {
            let parts2 = parts[0].split('/');
            for (let j = 0; j < parts2.length; j++){
                if (parts2[j] === dir && j < parts2.length - 1)
                    process_name = parts2[j+1];
            }
        }
    });
    // ---
    return process_name;
}

function update_filter_inputs(settings){
    // populate inputs
    $('#_logs_tab_processes_filter_command').val(settings.command);
    // update switches
    $('#_logs_tab_processes_filter_leaves_only').bootstrapToggle(settings.leaves_only? 'on' : 'off');
    $('#_logs_tab_processes_filter_condense_plots').bootstrapToggle(settings.condense_plots? 'on' : 'off');
    $('#_logs_tab_processes_filter_show_all').bootstrapToggle(settings.show_all? 'on' : 'off');
    // update sliders
    $("input#_logs_tab_processes_filter_pcpu").slider('setValue', settings.pcpu);
    $("input#_logs_tab_processes_filter_pmem").slider('setValue', settings.pmem);
    $("input#_logs_tab_processes_filter_nthreads").slider('setValue', settings.nthreads);
}

// this gets executed when the tab gains focus
let _tab_processes_on_show = function(){
    // update filter inputs
    update_filter_inputs(_LOG_PROCESS_FILTER_VALUES);
    // choose selector
    _LOGS_PROCESS_SELECTOR = (_LOG_PROCESS_FILTER_VALUES.show_all? '/all_' : '/') + 'process_stats';
    // fetch data
    let seek = ['/containers', _LOGS_PROCESS_SELECTOR];
    fetch_log_data(seek, _tab_processes_render_single_log, hidePleaseWait);
};

// this gets executed when the tab loses focus
let _tab_processes_on_hide = function(){
    $('#_logs_tab_processes').empty();
    // clear cache
    _LOG_PROGRESS_PROC_GROUPS = {};
    // clear number of processes
    $('#_logs_tab_processes_num_processes').html('--');
    $('#_logs_tab_processes_num_groups').html('--');
};

$('#_logs_tab_btns a[href="#processes"]').on('shown.bs.tab', _tab_processes_on_show);
$('#_logs_tab_btns a[href="#processes"]').on('hidden.bs.tab', _tab_processes_on_hide);

// configure filter sliders
$("input#_logs_tab_processes_filter_pcpu").slider({
    id: "_logs_tab_processes_filter_pcpu",
    min: 0,
    max: 100,
    step: 1,
    range: true,
    value: [0, 100],
    tooltip: 'show',
    ticks: [0, 100],
    ticks_positions: [0, 100],
    ticks_labels: ['0%', '100%'],
    formatter: function(value) {
        return value[0] + '%  -  ' + value[1] + '%';
    }
});

$("input#_logs_tab_processes_filter_pmem").slider({
    id: "_logs_tab_processes_filter_pmem",
    min: 0,
    max: 100,
    step: 1,
    range: true,
    value: [0, 100],
    tooltip: 'show',
    ticks: [0, 100],
    ticks_positions: [0, 100],
    ticks_labels: ['0%', '100%'],
    formatter: function(value) {
        return value[0] + '%  -  ' + value[1] + '%';
    }
});

$("input#_logs_tab_processes_filter_nthreads").slider({
    id: "_logs_tab_processes_filter_nthreads",
    min: 1,
    max: 50,
    step: 1,
    range: true,
    value: [1, 50],
    tooltip: 'show',
    ticks: [1, 50],
    ticks_positions: [1, 100],
    ticks_labels: ['1', '50'],
    formatter: function(value) {
        return value[0] + '  -  ' + value[1];
    }
});

$('#_logs_tab_processes_filter_reset').on('click', function(){
    // update filter inputs
    update_filter_inputs(_LOG_PROCESS_FILTER_DEFAULT_VALUES);
});

$('#_logs_tab_processes_filter_apply').on('click', function(){
    showPleaseWait();
    _LOG_PROGRESS_PROC_GROUPS = {};
    setTimeout(function(){
        let filters = {
          pcpu: $('input#_logs_tab_processes_filter_pcpu').slider('getValue'),
          pmem: $('input#_logs_tab_processes_filter_pmem').slider('getValue'),
          nthreads: $('input#_logs_tab_processes_filter_nthreads').slider('getValue'),
          command: $('#_logs_tab_processes_filter_command').val(),
          leaves_only: $('#_logs_tab_processes_filter_leaves_only').get(0).checked,
          show_all: $('#_logs_tab_processes_filter_show_all').get(0).checked,
          condense_plots: $('#_logs_tab_processes_filter_condense_plots').get(0).checked,
        };
        // store new filter values
        _LOG_PROCESS_FILTER_VALUES = filters;
        // store in browser
        localStorage.setItem('_LOG_PROCESS_FILTER_VALUES', JSON.stringify(filters));
        // clear number of processes
        $('#_logs_tab_processes_num_processes').html('--');
        $('#_logs_tab_processes_num_groups').html('--');
        // refresh tab
        refresh_current_tab();
    }, 500);
});

$(document).on('ready', function(){
    // load filters from browser
    let filters = localStorage.getItem('_LOG_PROCESS_FILTER_VALUES') || '{}';
    filters = JSON.parse(filters);
    // define filters value
    let pcpu = filters.pcpu || _LOG_PROCESS_FILTER_DEFAULT_VALUES.pcpu;
    let pmem = filters.pmem || _LOG_PROCESS_FILTER_DEFAULT_VALUES.pmem;
    let nthreads = filters.nthreads || _LOG_PROCESS_FILTER_DEFAULT_VALUES.nthreads;
    let command = filters.command || _LOG_PROCESS_FILTER_DEFAULT_VALUES.command;
    let leaves_only = (filters.leaves_only !== undefined)? filters.leaves_only :
        _LOG_PROCESS_FILTER_DEFAULT_VALUES.leaves_only;
    let show_all = (filters.show_all !== undefined)? filters.show_all :
        _LOG_PROCESS_FILTER_DEFAULT_VALUES.show_all;
    let condense_plots = (filters.condense_plots !== undefined)? filters.condense_plots :
        _LOG_PROCESS_FILTER_DEFAULT_VALUES.condense_plots;
    let coverage = filters.coverage || _LOG_PROCESS_FILTER_DEFAULT_VALUES.coverage;
    // store new filter values
    _LOG_PROCESS_FILTER_VALUES = {
        pcpu: pcpu,
        pmem: pmem,
        nthreads: nthreads,
        command: command,
        leaves_only: leaves_only,
        show_all: show_all,
        condense_plots: condense_plots,
        coverage: coverage
    };
});

</script>
