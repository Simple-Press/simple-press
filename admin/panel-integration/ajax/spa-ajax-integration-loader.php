<?php
/*
Simple:Press Admin
Ajax form loader - Integration
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
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
	spa_render_integration_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'page') {
		echo spa_save_integration_page_data();
		die();
	}
	if ($saveform == 'storage') {
		echo spa_save_integration_storage_data();
		die();
	}
	if ($saveform == 'language') {
		echo spa_save_integration_language_data();
		die();
	}
}

die();
