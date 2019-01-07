<?php
/*
Simple:Press Admin
Ajax form loader - Toolbox
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('toolbox-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/spa-toolbox-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-toolbox/support/spa-toolbox-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-toolbox/support/spa-toolbox-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-toolbox';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!SP()->auths->current_user_can('SPF Manage Toolbox')) die();

if (isset($_GET['loadform'])) {
	spa_render_toolbox_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'toolbox') {
		echo spa_save_toolbox_data();
		die();
	}
	if ($saveform == 'uninstall') {
		echo spa_save_uninstall_data();
		die();
	}
	if ($saveform == 'sfclearlog') {
		echo spa_save_toolbox_clearlog();
		die();
	}
	if ($saveform == 'housekeeping') {
		echo spa_save_housekeeping_data();
		die();
	}
	if ($saveform == 'inspector') {
		echo spa_save_inspector_data();
		die();
	}
	if ($saveform == 'cron') {
		echo spa_save_cron_data();
		die();
	}
}

die();
