<?php
/*
Simple:Press
Admin plugins prepare Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Get the list of plugins
function spa_get_plugins_list_data() {
    $plugins = SP()->plugin->get_list();
    return $plugins;
}
