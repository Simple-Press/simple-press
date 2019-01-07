<?php
/*
Simple:Press Admin
Ajax form loader - Integration
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('integration-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-integration/spa-integration-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-integration/support/spa-integration-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-integration/support/spa-integration-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-integration';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Integration
if (!SP()->auths->current_user_can('SPF Manage Integration')) die();

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
