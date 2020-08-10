<?php
_logs_print_table_structure();

/*
Keys used from Log:

    containers_config:
        "Id"
        "Name"
        "Created"
        "State": {
            "Status"
            "StartedAt"
        },
        "Config": {
            "Hostname"
            "Image"
            "Env": [*]
            "Labels"
        }
        "Image": "sha256:6fc70f05819c4b51e467324be3883e122491721be24abc1688f7009feaff592f",
        "HostConfig": {
            "Privileged"
            "ShmSize"
            "NetworkMode"
            "PortBindings": [*]
            "RestartPolicy"
            "AutoRemove"
        },
        "Mounts": [*],
        "Path"
        "Args": [*],

*/
?>

<hr>

<div id="_logs_tab_containers">
</div>


<style type="text/css">
#_logs_tab_containers > .panel > .panel-heading {
    background-image: none;
}

#_logs_tab_containers dl {
    margin-bottom: 0;
}

#_logs_tab_containers dl dd {
    overflow-wrap: break-word;
}
</style>


<script type="text/javascript">
var _LOGS_CONTAINER_BLOCK_TEMPLATE = `
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
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt>Status</dt>
          <dd>{container_status}</dd>
        </dl>
    </div>
    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt>ID</dt>
          <dd>{container_id}</dd>
          <dt>Created</dt>
          <dd>{container_created}</dd>
          <dt>Started</dt>
          <dd>{container_started}</dd>
        </dl>
    </div>

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt><u>Image</u>:</dt><dd></dd>
          <dt>ID</dt>
          <dd>{image_id}</dd>
          <dt>Name</dt>
          <dd>{image_name}</dd>
        </dl>
    </div>

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt><u>Resources</u>:</dt><dd></dd>
          <dt>Privileged</dt>
          <dd>{resources_privileged}</dd>
          <dt>Shared Memory</dt>
          <dd>{resources_shared_mem}</dd>
          <dt>Devices</dt>
          <dd>{resources_devices}</dd>
        </dl>
    </div>

    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt><u>Network</u>:</dt><dd></dd>
          <dt>Mode</dt>
          <dd>{network_mode}</dd>
          <dt>Hostname</dt>
          <dd>{network_hostname}</dd>
          <dt>Ports</dt>
          <dd>{network_ports}</dd>
        </dl>
    </div>

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt><u>Environment</u>:</dt><dd></dd>
        </dl>
    </div>
    {environment}

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt><u>Mounts</u>:</dt><dd></dd>
        </dl>
    </div>
    {mounts}

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt><u>Start / Stop</u>:</dt><dd></dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt>Entrypoint</dt>
          <dd>{start_stop_entrypoint}</dd>
          <dt>Restart policy</dt>
          <dd>{start_stop_restart_policy}</dd>
          <dt>Restart (max retry)</dt>
          <dd>{start_stop_max_retry_count}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="dl-horizontal">
          <dt>Command</dt>
          <dd>{start_stop_command}</dd>
          <dt>Auto-remove</dt>
          <dd>{start_stop_auto_remove}</dd>
        </dl>
    </div>

    <div class="col-md-12">&nbsp;<br/></div>

    <div class="col-md-12">
        <dl class="dl-horizontal">
          <dt><u>Labels</u>:</dt><dd></dd>
        </dl>
    </div>
    {labels}

  </div>
</div>`;

var _LOGS_CONTAINER_ENV_TEMPLATE = `
<div class="col-md-6">
    <dl class="dl-horizontal">
      <dt>{key}</dt>
      <dd>{value}</dd>
    </dl>
</div>`;

var _LOGS_CONTAINER_MOUNT_TEMPLATE = `
<div class="col-md-12">
    <dl class="dl-horizontal">
      <dt>Mount[{i}]</dt>
      <dd>{Source} -> {Destination} [{Type}, {rw_mode}]</dd>
    </dl>
</div>`;

var _LOGS_CONTAINER_LABEL_TEMPLATE = `
<div class="col-md-6">
    <dl class="dl-horizontal">
      <dt style="direction: rtl">{key}</dt>
      <dd>{value}</dd>
    </dl>
</div>`;


function _tab_containers_render_single_log(key, seek){
    let color = get_log_info(key, '_color');
    color = 'rgba({0}, 0.6)'.format(color);
    let log_data = window._DIAGNOSTICS_LOGS_DATA[key][seek];
    // draw each container
    for (const [_, container_data] of Object.entries(log_data)) {
        // create dataset
        let data = {
            log: key,
            color: color,
            container_name: container_data['Name'],
            container_status: container_data['State']['Status'],
            container_id: container_data['Id'],
            container_created: container_data['Created'],
            container_started: container_data['State']['StartedAt'],
            image_id: container_data['Image'],
            image_name: container_data['Config']['Image'],
            resources_privileged: container_data['HostConfig']['Privileged'],
            resources_shared_mem: (container_data['HostConfig']['ShmSize'] / Math.pow(1000, 2)).toFixed(2) + 'MB',
            resources_devices: '',
            environment: container_data['Config']['Env'].map(function(e){
                let [k, v] = e.split('=');
                return _LOGS_CONTAINER_ENV_TEMPLATE.format({key: k, value: v});
            }).join(''),
            network_mode: container_data['HostConfig']['NetworkMode'],
            network_hostname: container_data['Config']['Hostname'],
            network_ports: '',
            mounts: container_data['Mounts'].map(function(m, i){
                let mode = m['RW']? 'RW' : 'RO';
                return _LOGS_CONTAINER_MOUNT_TEMPLATE.format(
                    {...m, i: i, rw_mode: mode}
                );
            }).join(''),
            start_stop_entrypoint: container_data['Path'],
            start_stop_command: container_data['Args'].join(' '),
            start_stop_restart_policy: container_data['HostConfig']['RestartPolicy']['Name'],
            start_stop_max_retry_count: container_data['HostConfig']['RestartPolicy']['MaximumRetryCount'],
            start_stop_auto_remove: container_data['HostConfig']['AutoRemove'],
            labels: Object.entries(container_data['Config']['Labels']).map(function(e){
                return _LOGS_CONTAINER_LABEL_TEMPLATE.format({key: e[0], value: e[1]});
            }).join(''),
        };
        $('#_logs_tab_containers').append(
            _LOGS_CONTAINER_BLOCK_TEMPLATE.format(data)
        );
    }
}

// this gets executed when the tab gains focus
let _tab_containers_on_show = function(){
    let seek = '/container_config';
    fetch_log_data(seek, _tab_containers_render_single_log);
};

// this gets executed when the tab loses focus
let _tab_containers_on_hide = function(){
    $('#_logs_tab_containers').empty();
};

$('#_logs_tab_btns a[href="#containers"]').on('shown.bs.tab', _tab_containers_on_show);
$('#_logs_tab_btns a[href="#containers"]').on('hidden.bs.tab', _tab_containers_on_hide);
</script>
