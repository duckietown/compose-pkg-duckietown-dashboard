<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Configuration as Configuration;
use \system\classes\Core as Core;

if( Configuration::$ACTION == NULL ){
	// redirect to /show
    // TODO: if there is already a duckietown saved somewhere choose `show` otherwise `new`
    Core::redirectTo('duckietown/new');
}elseif( Configuration::$ACTION == 'show' ){
	// show the town editor
	require_once __DIR__.'/actions/town-show.php';
}elseif( Configuration::$ACTION == 'new' ){
	// show the town editor
	require_once __DIR__.'/actions/town-editor.php';
}elseif( Configuration::$ACTION == 'review' ){
	// show the town editor
	require_once __DIR__.'/actions/town-review.php';
}

?>
