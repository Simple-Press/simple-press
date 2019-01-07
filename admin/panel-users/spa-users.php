<?php
/*
Simple:Press
Admin Users
$LastChangedDate: 2018-10-17 15:14:27 -0500 (Wed, 17 Oct 2018) $
$Rev: 15755 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Users
if (!SP()->auths->current_user_can('SPF Manage Users')) die();

require_once SP_PLUGIN_DIR.'/admin/panel-users/spa-users-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-users/support/spa-users-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-users';
# --------------------------------------------------------------------

spa_panel_header();
if (isset($_GET['tab'])) {
	$formid = SP()->filters->str($_GET['tab']);
} else {
	if (isset($_GET['form'])) {
		$formid = SP()->filters->str($_GET['form']);
	} else {
		$formid = 'member-info';
	}
}
spa_render_users_panel($formid);
spa_panel_footer();
