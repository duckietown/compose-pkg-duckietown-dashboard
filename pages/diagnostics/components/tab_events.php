<?php
_logs_print_table_structure();

/*
Keys used from Log:

    events:
        time
        type
        id

    containers:
        *

*/
?>

<hr>

<div id="_logs_tab_events">

    <h4>Events:</h4>
    <table id="_events_table" class="table table-striped table-condensed text-center">
        <tr>
          <th class="col-md-2 text-center">Time</th>
          <th class="col-md-2 text-center">Absolute Time</th>
          <th class="col-md-3 text-center">Event type</th>
          <th class="col-md-5 text-center">Event data</th>
        </tr>
    </table>

</div>


<script type="text/javascript">

function _tab_events_render_all(){
    let events = [];
    let keys = get_listed_logs('_key');
    keys.forEach(function(key){
        let color = get_log_info(key, '_color');
        color = 'rgba({0}, 0.4)'.format(color);
        let log_evts = window._DIAGNOSTICS_LOGS_DATA[key]['/events'];
        let log_containers = window._DIAGNOSTICS_LOGS_DATA[key]['/containers'];
        log_evts.forEach(function(evt){
            let rel_time = evt.time - window._DIAGNOSTICS_LOGS_DATA[key]['/general'].time;
            let abs_time = new Date(parseInt(evt.time * 1000)).toISOString();
            events.push({
                color: color,
                rel_time: parseInt(rel_time.toFixed(0)),
                abs_time: "{0} {1}".format(abs_time.slice(0,10), abs_time.slice(11,19)),
                event_type: evt.type,
                event_data: "Container: {0}".format(log_containers[evt.id]),
            });
        });
    });
    events.sort((a, b) => (a.rel_time > b.rel_time) ? 1 : -1);
    // ---
    events.forEach(function(evt){
        // render events
        $('#_logs_tab_events #_events_table').append(`
            <tr class="_event_row" style="background-color: {color}">
              <td class="col-md-2">T +{rel_time} s</td>
              <td class="col-md-2">{abs_time}</td>
              <td class="col-md-3">{event_type}</td>
              <td class="col-md-5 text-left">{event_data}</td>
            </tr>
        `.format(evt));
    })
}

// this gets executed when the tab gains focus
let _tab_events_on_show = function(){
    let seek = ['/containers', '/events'];
    fetch_log_data(seek, null, _tab_events_render_all);
};

// this gets executed when the tab loses focus
let _tab_events_on_hide = function(){
    $('#_logs_tab_events #_events_table ._event_row').remove();
};

$('#_logs_tab_btns a[href="#events"]').on('shown.bs.tab', _tab_events_on_show);
$('#_logs_tab_btns a[href="#events"]').on('hidden.bs.tab', _tab_events_on_hide);
</script>
