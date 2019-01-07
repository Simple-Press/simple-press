<?php
/*
Simple:Press Admin
Ajax form loader - Integration
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('integration-loader')) die();

global $spStatus;
if ($spStatus != 'ok') {
	echo $spStatus;
	die();
}

include_once SF_PLUGIN_DIR.'/admin/panel-integration/spa-integration-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-integration/support/spa-integration-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/panel-integration/support/spa-integration-save.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-integration';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Integration
if (!sp_current_user_can('SPF Manage Integration')) die();

if (isset($_GET['loadform'])) {
	spa_render_integration_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'page') {
		echo spa_save_integration_page_data();
		die();
	}
	if ($_GET['saveform'] == 'storage') {
		echo spa_save_integration_storage_data();
		die();
	}
	if ($_GET['saveform'] == 'language') {
		echo spa_save_integration_language_data();
		die();
	}
}

die();
?>