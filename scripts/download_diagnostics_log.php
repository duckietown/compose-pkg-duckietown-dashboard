<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// load core libraries
require_once $GLOBALS['__SYSTEM__DIR__'].'classes/Core.php';

// simplify namespaces
use system\classes\Core;

// create a Session
Core::startSession();

// init Core
$res = Core::init();
if (!$res['success']){
    echo $res['data'];
    die($res['data']);
}

// ==> Your code after this line
// ===============================================>

// load database library
use \system\packages\data\Data;

// check arguments: [database]
if (!isset($_GET['database']) || strlen($_GET['database']) < 1){
    echo 'Argument "database" is mandatory.';
    return;
}
$database = $_GET['database'];

// check arguments: [key]
if (!isset($_GET['key']) || strlen($_GET['key']) < 1){
    echo 'Argument "key" is mandatory.';
    return;
}
$key = $_GET['key'];

// get log
$res = Data::get($database, $key);
if (!$res['success']) {
    echo $res['data'];
    return;
}
$log_json_text = json_encode($res['data']);

// clean buffer
if (ob_get_length()) ob_clean();

// send the right headers
header("Content-Type: application/json");
header(sprintf("Content-Length: %s", strlen($log_json_text)), true);
header(sprintf("Content-Disposition: attachment; filename=\"%s\"", $key.'.json'));

// dump the file and stop the script
echo $log_json_text;
ob_flush();
flush();

// ---
exit;

// <===============================================
// <== Your code before this line

// terminate
Core::close();
?>