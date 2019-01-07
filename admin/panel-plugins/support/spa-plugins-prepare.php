<?php
/*
Simple:Press
Admin plugins prepare Support Functions
$LastChangedDate: 2014-06-20 22:47:00 -0500 (Fri, 20 Jun 2014) $
$Rev: 11582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Get the list of plugins
function spa_get_plugins_list_data() {
    $plugins = sp_get_plugins();
    return $plugins;
}

?>