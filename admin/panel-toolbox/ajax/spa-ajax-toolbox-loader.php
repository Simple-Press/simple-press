<?php
/*
Simple:Press Admin
Ajax form loader - Toolbox
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('toolbox-loader')) die();

global $spStatus;
if ($spStatus != 'ok') {
	echo $spStatus;
	die();
}

include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/spa-toolbox-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/support/spa-toolbox-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/panel-toolbox/support/spa-toolbox-save.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-toolbox';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!sp_current_user_can('SPF Manage Toolbox')) die();

if (isset($_GET['loadform'])) {
	spa_render_toolbox_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'toolbox') {
		echo spa_save_toolbox_data();
		die();
	}
	if ($_GET['saveform'] == 'uninstall') {
		echo spa_save_uninstall_data();
		die();
	}
	if ($_GET['saveform'] == 'sfclearlog') {
		echo spa_save_toolbox_clearlog();
		die();
	}
	if ($_GET['saveform'] == 'housekeeping') {
		echo spa_save_housekeeping_data();
		die();
	}
	if ($_GET['saveform'] == 'inspector') {
		echo spa_save_inspector_data();
		die();
	}
	if ($_GET['saveform'] == 'cron') {
		echo spa_save_cron_data();
		die();
	}
}

die();

?>