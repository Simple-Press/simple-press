<?php
/*
Simple:Press Permissions Admin
Ajax form loader - Permissions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('permissions-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-permissions/spa-permissions-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-permissions/support/spa-permissions-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-permissions/support/spa-permissions-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-permissions';

# ----------------------------------
# Check Whether User Can Manage Forums
if (!SP()->auths->current_user_can('SPF Manage Permissions')) die();

if (isset($_GET['loadform'])) {
	spa_render_permissions_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'addperm') {
		echo spa_save_permissions_new_role();
		die();
	}
	if ($_GET['saveform'] == 'editperm') {
		echo spa_save_permissions_edit_role();
		die();
	}
	if ($_GET['saveform'] == 'delperm') {
		echo spa_save_permissions_delete_role();
		die();
	}
	if ($_GET['saveform'] == 'resetperms') {
		echo spa_save_permissions_reset();
		die();
	}
	if ($_GET['saveform'] == 'newauth') {
		echo spa_save_permissions_new_auth();
		die();
	}
}

die();
