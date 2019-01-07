<?php
/*
Simple:Press Admin
Ajax form loader - Admins
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('admins-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

include_once SP_PLUGIN_DIR.'/admin/panel-admins/spa-admins-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-admins';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
$modchk = (SP()->user->thisUser->admin || SP()->user->thisUser->moderator) && ((isset($_GET['saveform']) && sanitize_text_field($_GET['saveform']) == 'youradmin') || (isset($_GET['loadform']) && sanitize_text_field($_GET['loadform']) == 'youradmin'));
if (!SP()->auths->current_user_can('SPF Manage Admins') && !$modchk) die();

if (isset($_GET['loadform'])) {
	spa_render_admins_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'youradmin') {
		echo spa_save_admins_your_options_data();
		die();
	}
	if ($saveform == 'globaladmin') {
		echo spa_save_admins_global_options_data();
		die();
	}
	if ($saveform == 'manageadmin') {
		echo spa_save_admins_caps_data();
		die();
	}
	if ($saveform == 'addadmin') {
		echo spa_save_admins_newadmin_data();
		die();
	}
}

die();
