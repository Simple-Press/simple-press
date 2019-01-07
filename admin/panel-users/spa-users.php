<?php
/*
Simple:Press
Admin Users
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Users
if (!sp_current_user_can('SPF Manage Users')) die();

global $spStatus;

include_once SF_PLUGIN_DIR.'/admin/panel-users/spa-users-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-users/support/spa-users-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if ($spStatus != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-users';
# --------------------------------------------------------------------

spa_panel_header();
if (isset($_GET['tab'])) {
	$formid=$_GET['tab'];
} else {
	if (isset($_GET['form'])) {
		$formid = $_GET['form'];
	} else {
		$formid = 'member-info';
	}
}
spa_render_users_panel($formid);
spa_panel_footer();

?>