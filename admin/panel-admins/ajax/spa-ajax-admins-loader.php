<?php
/*
Simple:Press Admin
Ajax form loader - Admins
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
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
$modchk = (SP()->user->thisUser->admin || SP()->user->thisUser->moderator) && ((isset($_GET['saveform']) && $_GET['saveform'] == 'youradmin') || (isset($_GET['loadform']) && $_GET['loadform'] == 'youradmin'));
if (!SP()->auths->current_user_can('SPF Manage Admins') && !$modchk) die();

if (isset($_GET['loadform'])) {
	spa_render_admins_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'youradmin') {
		echo spa_save_admins_your_options_data();
		die();
	}
	if ($_GET['saveform'] == 'globaladmin') {
		echo spa_save_admins_global_options_data();
		die();
	}
	if ($_GET['saveform'] == 'manageadmin') {
		echo spa_save_admins_caps_data();
		die();
	}
	if ($_GET['saveform'] == 'addadmin') {
		echo spa_save_admins_newadmin_data();
		die();
	}
}

die();
