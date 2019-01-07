<?php
/*
Simple:Press Admin
Ajax form loader - plugins
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('plugins-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-plugins/spa-plugins-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-plugins';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Plugins
# dont check for admin panels loaded/saved by plugins - the plugins api will do that
if ((isset($_GET['loadform']) && sanitize_text_field($_GET['loadform']) != 'plugin') || (isset($_GET['saveform']) && sanitize_text_field($_GET['saveform']) != 'plugin')) {
    if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();
}

if (isset($_GET['loadform'])) {
	spa_render_plugins_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'list') {
		echo spa_save_plugin_list_actions();
		die();
	}

	if ($saveform == 'activation') {
		echo spa_save_plugin_activation();
		die();
	}

	if ($saveform == 'plugin') {
		echo spa_save_plugin_userdata(SP()->filters->str($_GET['func']));
		die();
	}
}

die();
