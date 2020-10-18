<?php
use \system\classes\Core;

$LOGS_VERSION = "v1";

$logs_db_host = Core::getSetting('database/hostname', 'duckietown_diagnostics');
$logs_db_name = Core::getSetting('database/name', 'duckietown_diagnostics');
$db_app_id = Core::getSetting('authentication/app_id', 'duckietown_diagnostics');
$db_app_secret = Core::getSetting('authentication/app_secret', 'duckietown_diagnostics');

$api_info = [];
if (strlen($logs_db_host) > 0) {
    $api_info['host'] = $logs_db_host;
}
if (strlen($db_app_id) > 0 && strlen($db_app_secret) > 0) {
    $api_info['auth'] = [
        'app_id' => $db_app_id,
        'app_secret' => $db_app_secret
    ];
}
?>

<style type="text/css">
.page-title {
    margin-bottom: 10px;
}
</style>

<div class="col-md-12" style="margin-bottom: 30px;">
    <div style="width: 970px; margin: auto">
        <h2 class="page-title"></h2>
    
        <span style="float: right; font-size: 12pt">
            Wide mode
            <label for="_logs_wide_mode"></label>
            <input type="checkbox"
                data-toggle="toggle"
                data-onstyle="primary"
                data-offstyle="default"
                data-class="fast"
                data-size="small"
                name="_logs_wide_mode"
                id="_logs_wide_mode"/>
        </span>
    </div>
</div>

<style type="text/css">
    #_log_selectors_form .row ._selector:nth-child(2){
        padding-left: 15px;
    }
    
    #_log_selectors_form .row ._selector{
        padding-left: 5px;
        padding-right: 5px;
    }
    
    ._logs_list{
        font-size: 13px;
    }
    
    #_logs_tab_btns li > a{
        color: #555;
    }
    
    ._logs_rigid_centered_component {
        width: 970px;
        margin: auto;
    }
</style>

<?php
$tabs = [
    'logs' => [
        'name' => 'Logs',
        'icon' => 'info-circle'
    ],
    'system' => [
        'name' => 'System',
        'icon' => 'microchip'
    ],
    'resources' => [
        'name' => 'Resources',
        'icon' => 'tachometer'
    ],
    'events' => [
        'name' => 'Events',
        'icon' => 'history'
    ],
    'health' => [
        'name' => 'Health',
        'icon' => 'medkit'
    ],
    'containers' => [
        'name' => 'Containers',
        'icon' => 'cubes'
    ],
    'processes' => [
        'name' => 'Processes',
        'icon' => 'gears'
    ],
    'disk' => [
        'name' => 'Disk',
        'icon' => 'hdd-o'
    ],
    'network' => [
        'name' => 'Network',
        'icon' => 'exchange'
    ]
];
?>


<!-- Nav tabs -->
<ul class="nav nav-tabs _logs_rigid_centered_component" id="_logs_tab_btns" role="tablist">
    <?php
    foreach ($tabs as $tab_id => $tab) {
        ?>
        <li role="presentation" class="<?php echo ($tab_id == 'logs')? 'active' : '' ?>">
            <a href="#<?php echo $tab_id ?>" aria-controls="<?php echo $tab_id ?>" role="tab" data-toggle="tab">
                <i class="fa fa-<?php echo $tab['icon'] ?>" aria-hidden="true"></i> <?php echo $tab['name'] ?>
            </a>
        </li>
        <?php
    }
    ?>
</ul>

<!-- Tab panes -->
<div class="tab-content" id="_logs_tab_container" style="padding: 20px 0">
    <?php
    foreach ($tabs as $tab_id => $tab) {
        ?>
        <div role="tabpanel" class="tab-pane <?php echo ($tab_id == 'logs')? 'active' : '' ?>" id="<?php echo $tab_id ?>">
        <?php
            include sprintf('%s/components/tab_%s.php', __DIR__, $tab_id);
        ?>
        </div>
        <?php
    }
    ?>
</div>

<script type="text/javascript">
window._DIAGNOSTICS_LOGS_KEYS = [];
window._DIAGNOSTICS_LOGS_DATA = {};
window._DIAGNOSTICS_LOGS_DURATION = 0;
window._DIAGNOSTICS_LOGS_X_RESOLUTION = 1;
window._DIAGNOSTICS_LOGS_X_RANGE = [];
window._DIAGNOSTICS_LOGS_WIDTH = '100%';
window._DIAGNOSTICS_LOGS_HEIGHT = '280px';
window._DIAGNOSTICS_LOGS_BG_ALPHA = 0.1;

$('#_logs_wide_mode').change(function(){
    if ($(this).prop('checked')){
        window._DIAGNOSTICS_LOGS_WIDTH = '100%';
        window._DIAGNOSTICS_LOGS_HEIGHT = '400px';
        window._DIAGNOSTICS_LOGS_BG_ALPHA = 0.4;
        $('#page_container').css('min-width', '100%');
    }else{
        window._DIAGNOSTICS_LOGS_WIDTH = '100%';
        window._DIAGNOSTICS_LOGS_HEIGHT = '280px';
        window._DIAGNOSTICS_LOGS_BG_ALPHA = 0.1;
        $('#page_container').css('min-width', '970px');
    }
    refresh_current_tab();
  });

function get_empty_canvas(width, height){
    let _w = width || window._DIAGNOSTICS_LOGS_WIDTH;
    let _h = height || window._DIAGNOSTICS_LOGS_HEIGHT;
    return $('<canvas/>').width(_w).height(_h);
}

function get_chart_dataset(opts){
    let bg_alpha = opts['background_alpha'] || window._DIAGNOSTICS_LOGS_BG_ALPHA;
    let gradient = $('<canvas/>').get(0).getContext('2d').createLinearGradient(0, 0, 0, 600);
    gradient.addColorStop(0, "rgba({0}, {1})".format(opts['color'], bg_alpha));
    gradient.addColorStop(0.5, "rgba(255, 255, 255, 0)");
    gradient.addColorStop(1, "rgba(255, 255, 255, 0)");
    opts['data'] = opts['data'].map(function(p){return {
        x: Math.min(p.x, window._DIAGNOSTICS_LOGS_DURATION),
        y: p.y
    }});
    // ---
    let default_opts = {
        backgroundColor: opts['no_background']? 'rgba(0, 0, 0, 0)' : gradient,
        borderColor: "rgba({0}, .9)".format(opts['color']),
        pointRadius: 3,
        pointBackgroundColor: '#fff',
        borderWidth: 2,
        fill: true
    };
    return {...default_opts, ...opts};
}

function format_time(secs){
    let parts = [];
    if (secs > 59)
        parts.push('{0}m'.format(Math.floor(secs / 60)));
    if (secs % 60 !== 0 || secs === 0)
        parts.push('{0}s'.format(secs % 60));
    return parts.join(' ');
}
</script>
