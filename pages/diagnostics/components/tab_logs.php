<?php
use \system\classes\Core;
use \system\classes\Configuration;

$logs_db_host = Core::getSetting('database/hostname', 'duckietown_diagnostics');
$logs_db_name = Core::getSetting('database/name', 'duckietown_diagnostics');

if (strlen($logs_db_host) < 1) {
    $logs_db_host = Configuration::$BASE;
}
?>

<form class="form-inline _logs_rigid_centered_component" id="_log_selectors_form">

  <div class="row">

    <div class="_selector col-md-3" style="display:none">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Version</div>
                <select id="_sel_version" class="selectpicker" data-live-search="true" data-width="100%">
                </select>
            </div>
        </div>
    </div>

    <div class="_selector col-md-3" style="width: 20%">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Group</div>
                <select id="_sel_group" class="selectpicker" data-live-search="true" data-width="100%">
                </select>
            </div>
        </div>
    </div>

    <div class="_selector col-md-3" style="width: 20%">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Subgroup</div>
                <select id="_sel_type" class="selectpicker" data-live-search="true" data-width="100%">
                </select>
            </div>
        </div>
    </div>

    <div class="_selector col-md-3">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Device</div>
                <select id="_sel_device" class="selectpicker" data-live-search="true" data-width="100%">
                </select>
            </div>
        </div>
    </div>

    <div class="_selector col-md-3">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Time</div>
                <select id="_sel_stamp" class="selectpicker" data-live-search="true" data-width="100%" multiple>
                </select>
            </div>
        </div>
    </div>

    <div class="_selector col-md-1">
        <button type="button" id="_btn_add_log" class="btn btn-default" style="height: 32px" disabled>
            <span class="glyphicon glyphicon-plus" aria-hidden="true" style="color: green"></span>
        </button>
    </div>

  </div>
</form>

<br/>
<br/>

<?php
function _logs_print_table_structure($id = null, $read_only = true) {
    $id_str = is_null($id)? '' : sprintf('id="%s"', $id);
    ?>
    <h4 class="_logs_rigid_centered_component">Selected logs:</h4>
    <table <?php echo $id_str ?> class="_logs_list _logs_rigid_centered_component table table-striped table-condensed text-center">
        <tr>
          <th style="display:none">_key</th>
          <th style="display:none">_color</th>
          <th class="col-md-1 text-center">Color</th>
          <th class="col-md-2 text-center">Group</th>
          <th class="col-md-2 text-center">Subgroup</th>
          <th class="col-md-2 text-center">Device</th>
          <th class="col-md-3 text-center">Time</th>
          <th class="col-md-2 text-center">Actions</th>
        </tr>
    </table>
    <?php
}

_logs_print_table_structure('_main_table', false);
?>


<script type="text/javascript">
$('#_sel_version').on('changed.bs.select', function(){
    let _ = undefined;
    let v = "<?php echo $LOGS_VERSION ?>";
    let [_v, _g, _t, _d, _s] = filter_keys(v);
    apply_keys(_, _g, [], [], []);
    $('#_sel_group').selectpicker('val', []);
});

$('#_sel_group').on('changed.bs.select', function (){
    let _ = undefined;
    let v = "<?php echo $LOGS_VERSION ?>";
    let g = $('#_sel_group').val();
    let [_v, _g, _t, _d, _s] = filter_keys(v, g);
    apply_keys(_, _, _t, [], []);
    $('#_sel_type').selectpicker('val', []);
});

$('#_sel_type').on('changed.bs.select', function (){
    let _ = undefined;
    let v = "<?php echo $LOGS_VERSION ?>";
    let g = $('#_sel_group').val();
    let t = $('#_sel_type').val();
    let [_v, _g, _t, _d, _s] = filter_keys(v, g, t);
    apply_keys(_, _, _, _d, []);
    $('#_sel_device').selectpicker('val', []);
});

$('#_sel_device').on('changed.bs.select', function (){
    let _ = undefined;
    let v = "<?php echo $LOGS_VERSION ?>";
    let g = $('#_sel_group').val();
    let t = $('#_sel_type').val();
    let d = $('#_sel_device').val();
    let [_v, _g, _t, _d, _s] = filter_keys(v, g, t, d);
    apply_keys(_, _, _, _, _s);
    $('#_sel_stamp').selectpicker('val', []);
});

$('#_sel_stamp').on('changed.bs.select', function (){
    let s = $('#_sel_stamp').val();
    if (s && s.length > 0)
        $('#_btn_add_log').prop('disabled', false);
    else
        $('#_btn_add_log').prop('disabled', true);
});

function filter_keys(version, group, type, device) {
    let keys = window._DIAGNOSTICS_LOGS_KEYS;
    // apply filter
    let _versions = [];
    let _groups = [];
    let _types = [];
    let _devices = [];
    let _stamps = [];
    keys.forEach(function(k){
        let [_v, _g, _t, _d, _s] = k.split('__');
        if (version != undefined && _v != version) return;
        if (group != undefined && _g != group) return;
        if (type != undefined && _t != type) return;
        if (device != undefined && _d != device) return;
        _versions.push(_v);
        _groups.push(_g);
        _types.push(_t);
        _devices.push(_d);
        _stamps.push([k, _s]);
    });
    return [
        Array.from(new Set(_versions)),
        Array.from(new Set(_groups)),
        Array.from(new Set(_types)),
        Array.from(new Set(_devices)),
        _stamps
    ];
}

function apply_keys(versions, groups, types, devices, stamps){
    // refill selects
    if (versions != undefined) {
        $('#_sel_version').empty();
        versions.forEach(function(e){
            $('#_sel_version').append(new Option(e, e));
        });
    }
    if (groups != undefined) {
        $('#_sel_group').empty();
        groups.forEach(function(e){
            $('#_sel_group').append(new Option(e, e));
        });
    }
    if (types != undefined) {
        $('#_sel_type').empty();
        types.forEach(function(e){
            $('#_sel_type').append(new Option(e, e));
        });
    }
    if (devices != undefined) {
        $('#_sel_device').empty();
        devices.forEach(function(e){
            $('#_sel_device').append(new Option(e, e));
        });
    }
    if (stamps != undefined) {
        $('#_sel_stamp').empty();
        stamps.forEach(function(e){
            let datetime = new Date(parseInt(e[1]) * 1000).toISOString().slice(0, 19);
            $('#_sel_stamp').append(new Option(datetime, e[0]));
        });
    }
    // refresh select
    $('#_sel_version').selectpicker('refresh');
    $('#_sel_group').selectpicker('refresh');
    $('#_sel_type').selectpicker('refresh');
    $('#_sel_device').selectpicker('refresh');
    $('#_sel_stamp').selectpicker('refresh');
}

function get_listed_logs(seek){
    let tab_data = tableToObject('._logs_list#_main_table');
    if (seek != undefined) {
        return tab_data.map(e => e[seek]);
    }
    return tab_data;
}

function get_log_info(key, param){
    let tab_data = get_listed_logs();
    for (let i = 0; i < tab_data.length; i++) {
        let tab_row = tab_data[i];
        if (tab_row['_key'] == key){
            return tab_row[param];
        }
    }
}

function _populate_table(keys){
    // clear table
    $('._logs_list > tbody tr._row').remove();
    // get colors
    let colors = [
        'red',
        'blue',
        'green',
        'orange',
        'purple',
        'yellow',
        'grey'
    ];
    let _color = c => '<span class="fa fa-stop" aria-hidden="true" style="font-size: 16px; color: {0}"></span>'.format(window.chartColors[colors[c]]);
    // add logs to the list
    keys.forEach(function(k, i){
        let c_i = i % colors.length;
        let [_v, _g, _t, _d, _s] = k.split('__');
        _dt = new Date(parseInt(_s) * 1000).toISOString().slice(0, 19);
        $('._logs_list > tbody').append(
            `<tr class="_row">
                <td style="display:none">{0}</td>
                <td id="_color_hex" style="display:none">{1}</td>
                <td id="_color">{2}</td>
                <td>{3}</td>
                <td>{4}</td>
                <td>{5}</td>
                <td>{6}</td>
                <td>
                    <a href="#" role="button" class="_log_row_{0} btn btn-default" title="Download log" onclick="_download_log('{0}')">
                        <span class="glyphicon glyphicon-download-alt" aria-hidden="true" style="color: darkgreen"></span>
                    </a>
                    <a href="#" role="button" class="_log_row_{0} btn btn-default" title="Remove log" onclick="_rm_log('{0}')">
                        <span class="glyphicon glyphicon-trash" aria-hidden="true" style="color: darkred"></span>
                    </a>
                </td>
            </tr>`.format(
                k, window.chartColors[colors[c_i]].slice(4, -1), _color(c_i), _g, _t, _d, _dt
            )
        );
    });
    // store keys
    localStorage.setItem('_LOGS_SELECTED_KEYS', keys);
    // pull general info about the logs
    fetch_log_data('/general', undefined, _update_duration);
}

function _refresh_table(){
    _populate_table(get_listed_logs('_key'));
}

$('#_btn_add_log').on('click', function(){
    let _sel_keys = ($('#_sel_stamp').val() != null)? $('#_sel_stamp').val() : [];
    // get the list of keys already in the table
    let keys = Array.from(new Set(
        get_listed_logs('_key').concat(_sel_keys)
    ));
    // populate table
    _populate_table(keys);
    // clear stamps selection
    $('#_sel_stamp').val([]);
    $('#_sel_stamp').trigger('changed.bs.select');
    $('#_sel_stamp').selectpicker('refresh');
});

function _download_log(key){
    // compile log URL
    let log_url = '{0}/script.php?package={1}&script={2}&database={3}&key={4}'.format(
        "<?php echo $logs_db_host ?>",
        "duckietown_diagnostics",
        "download_diagnostics_log",
        "<?php echo $logs_db_name ?>",
        key
    );
    // download log
    window.open(log_url, '_blank');
}

function _rm_log(key){
    // remove row
    $('._log_row_{0}'.format(key)).closest('tr').remove();
    // refresh table
    _refresh_table();
    //refresh current tab
    refresh_current_tab();
}

function refresh_current_tab(){
    $('#_logs_tab_btns li.active a').trigger('hidden.bs.tab');
    $('#_logs_tab_btns li.active a').trigger('shown.bs.tab');
}

function fetch_log_data(seeks, on_step, on_success){
    // arguments
    if (!Array.isArray(seeks))
        seeks = [seeks];
    // get list of keys
    let keys = get_listed_logs('_key');
    if (keys.length <= 0) return;
    // get args
    let _on_step_fcn = on_step || function(){};
    let _on_success_fcn = on_success || function(){};
    // create destinations
    let to_load = [];
    keys.forEach(function(key) {
        seeks.forEach(function(seek){
            if (!window._DIAGNOSTICS_LOGS_DATA.hasOwnProperty(key)) {
                window._DIAGNOSTICS_LOGS_DATA[key] = {};
            }else if (window._DIAGNOSTICS_LOGS_DATA[key].hasOwnProperty(seek)) {
                console.log('Using cached {0} : {1}'.format(key, seek));
            }
            to_load.push({key: key, seek: seek});
        });
    });
    let total = to_load.length;
    let _pbar_next = function(q){return 100 * (total - q.length) / total};
    if (total <= 0){
        return;
    }
    ProgressBar.set(1);
    // define task function
    let _fetch = function(queue){
        // base case, nothing left in the queue
        if (queue.length === 0){
            ProgressBar.set(100);
            return _on_success_fcn();
        }
        // get next element from queue
        let job = queue.pop();
        // base case, nothing to do
        if (window._DIAGNOSTICS_LOGS_DATA.hasOwnProperty(job.key) &&
            window._DIAGNOSTICS_LOGS_DATA[job.key].hasOwnProperty(job.seek)) {
            // run step success function
            let i = total - queue.length;
            _on_step_fcn(job.key, job.seek, i, total);
            // update progress bar
            ProgressBar.set(_pbar_next(queue));
            // move to the next job
            return _fetch(queue);
        }
        // get extra info
        let api_info = JSON.parse('<?php echo json_encode($api_info) ?>');
        // call API
        smartAPI('data', 'get', {
            'arguments': {
                'database': '<?php echo $logs_db_name ?>',
                'key': job.key,
                'seek': job.seek
            },
            'block': false,
            'confirm': false,
            ...api_info,
            'on_success': function (res) {
                console.log('Loaded {key} : {seek}'.format(job));
                window._DIAGNOSTICS_LOGS_DATA[job.key][job.seek] = res['data']['value'];
                // run step success function
                let i = total - queue.length;
                _on_step_fcn(job.key, job.seek, i, total);
                // update progress bar
                ProgressBar.set(_pbar_next(queue));
                // move to the next job
                return _fetch(queue);
            }
        });
    };
    // start queue
    _fetch(to_load.reverse());
}

function _update_duration(){
    get_listed_logs('_key').forEach(function(key){
        let duration = window._DIAGNOSTICS_LOGS_DATA[key]['/general'].duration;
        window._DIAGNOSTICS_LOGS_DURATION = Math.max(
            window._DIAGNOSTICS_LOGS_DURATION, duration
        );
        window._DIAGNOSTICS_LOGS_X_RANGE = range(
            0, window._DIAGNOSTICS_LOGS_DURATION, window._DIAGNOSTICS_LOGS_X_RESOLUTION
        );
    });
}

$(document).on('ready', function(){
    ProgressBar.set(1);
    // get extra info
    let api_info = JSON.parse('<?php echo json_encode($api_info) ?>');
    // fetch list of keys
    smartAPI('data', 'list', {
        'arguments': {
            'database': '<?php echo $logs_db_name ?>'
        },
        'block': false,
        'confirm': false,
        ...api_info,
        'on_success': function(res){
            window._DIAGNOSTICS_LOGS_KEYS = res['data']['keys'];
            // trigger the changed event on the version selector in order to populate the group selector
            $('#_sel_version').trigger('changed.bs.select');
            // get cached keys
            let cached_keys = localStorage.getItem('_LOGS_SELECTED_KEYS') || [];
            if (!Array.isArray(cached_keys)) cached_keys = cached_keys.split(',');
            let get_keys = [
                <?php
                $_get_keys = array_key_exists('keys', $_GET)? explode(',', $_GET['keys']) : '';
                echo implode(', ', array_map(function($k){return sprintf('"%s"', $k);}, $_get_keys));
                ?>
            ];
            if (get_keys.length > 0) {
                cached_keys = [];
            }
            let keys = Array.from(new Set(cached_keys.concat(get_keys)));
            // populate table
            _populate_table(keys);
            // update progress bar
            ProgressBar.set(100);
        }
    });
});
</script>
