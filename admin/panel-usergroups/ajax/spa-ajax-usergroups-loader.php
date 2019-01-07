<?php
/*
Simple:Press User Groups Admin
Ajax form loader - User Groups
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('usergroups-loader')) die();

global $spStatus;
if ($spStatus != 'ok') {
	echo $spStatus;
	die();
}

include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/spa-usergroups-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/support/spa-usergroups-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/panel-usergroups/support/spa-usergroups-save.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-usergroups';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage User Groups
if (!sp_current_user_can('SPF Manage User Groups')) die();

if (isset($_GET['loadform'])) {
	spa_render_usergroups_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'newusergroup') {
		echo spa_save_usergroups_new_usergroup();
		die();
	}
	if ($_GET['saveform'] == 'editusergroup') {
		echo spa_save_usergroups_edit_usergroup();
		die();
	}
	if ($_GET['saveform'] == 'delusergroup') {
		echo spa_save_usergroups_delete_usergroup();
		die();
	}
	if ($_GET['saveform'] == 'addmembers') {
		echo spa_save_usergroups_add_members();
		die();
	}
	if ($_GET['saveform'] == 'delmembers') {
		echo spa_save_usergroups_delete_members();
		die();
	}
	if ($_GET['saveform'] == 'mapsettings') {
		echo spa_save_usergroups_map_settings();
		die();
	}
	if ($_GET['saveform'] == 'mapusers') {
		echo spa_save_usergroups_map_users();
		die();
	}
}

die();

?>