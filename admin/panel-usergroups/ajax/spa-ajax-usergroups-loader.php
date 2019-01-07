<?php
/*
Simple:Press User Groups Admin
Ajax form loader - User Groups
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('usergroups-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/spa-usergroups-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-usergroups/support/spa-usergroups-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-usergroups/support/spa-usergroups-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-usergroups';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage User Groups
if (!SP()->auths->current_user_can('SPF Manage User Groups')) die();

if (isset($_GET['loadform'])) {
	spa_render_usergroups_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'newusergroup') {
		echo spa_save_usergroups_new_usergroup();
		die();
	}
	if ($saveform == 'editusergroup') {
		echo spa_save_usergroups_edit_usergroup();
		die();
	}
	if ($saveform == 'delusergroup') {
		echo spa_save_usergroups_delete_usergroup();
		die();
	}
	if ($saveform == 'addmembers') {
		echo spa_save_usergroups_add_members();
		die();
	}
	if ($saveform == 'delmembers') {
		echo spa_save_usergroups_delete_members();
		die();
	}
	if ($saveform == 'mapsettings') {
		echo spa_save_usergroups_map_settings();
		die();
	}
	if ($saveform == 'mapusers') {
		echo spa_save_usergroups_map_users();
		die();
	}
}

die();
