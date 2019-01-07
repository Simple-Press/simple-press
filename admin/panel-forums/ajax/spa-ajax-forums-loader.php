<?php
/*
Simple:Press Admin
Ajax form loader - Forums
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('forums-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-forums/spa-forums-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-forums';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Forums
if (!SP()->auths->current_user_can('SPF Manage Forums')) die();

if (isset($_GET['loadform'])) {
	spa_render_forums_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'creategroup') {
		echo spa_save_forums_create_group();
		die();
	}
	if ($_GET['saveform'] == 'createforum') {
		echo spa_save_forums_create_forum();
		die();
	}
	if ($_GET['saveform'] == 'globalperm') {
		echo spa_save_forums_global_perm();
		die();
	}
	if ($_GET['saveform'] == 'removeperms') {
		echo spa_save_forums_remove_perms();
		die();
	}
	if ($_GET['saveform'] == 'mergeforums') {
		echo spa_save_forums_merge();
		die();
	}
	if ($_GET['saveform'] == 'globalrss') {
		echo spa_save_forums_global_rss();
		die();
	}
	if ($_GET['saveform'] == 'globalrssset') {
		echo spa_save_forums_global_rssset();
		die();
	}
	if ($_GET['saveform'] == 'grouppermission') {
		echo spa_save_forums_group_perm();
		die();
	}
	if ($_GET['saveform'] == 'editgroup') {
		echo spa_save_forums_edit_group();
		die();
	}
	if ($_GET['saveform'] == 'deletegroup') {
		echo spa_save_forums_delete_group();
		die();
	}
	if ($_GET['saveform'] == 'editforum') {
		echo spa_save_forums_edit_forum();
		die();
	}
	if ($_GET['saveform'] == 'deleteforum') {
		echo spa_save_forums_delete_forum();
		die();
	}
	if ($_GET['saveform'] == 'disableforum') {
		echo spa_save_forums_disable_forum();
		die();
	}
	if ($_GET['saveform'] == 'enableforum') {
		echo spa_save_forums_enable_forum();
		die();
	}
	if ($_GET['saveform'] == 'addperm') {
		echo spa_save_forums_forum_perm();
		die();
	}
	if ($_GET['saveform'] == 'editperm') {
		echo spa_save_forums_edit_perm();
		die();
	}
	if ($_GET['saveform'] == 'delperm') {
		echo spa_save_forums_delete_perm();
		die();
	}
	if ($_GET['saveform'] == 'orderforum') {
		echo spa_save_forums_order();
		die();
	}
}

die();
