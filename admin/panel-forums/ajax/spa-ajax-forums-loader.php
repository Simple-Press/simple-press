<?php
/*
Simple:Press Admin
Ajax form loader - Forums
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('forums-loader')) die();

if (SP()->core->status != 'ok') {
	echo esc_html( SP()->core->status );
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
	spa_render_forums_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'creategroup') {
		echo esc_html( spa_save_forums_create_group() );
		die();
	}
	if ($saveform == 'createforum') {
		echo esc_html( spa_save_forums_create_forum() );
		die();
	}
	if ($saveform == 'globalperm') {
		echo esc_html( spa_save_forums_global_perm() );
		die();
	}
	if ($saveform == 'removeperms') {
		echo esc_html( spa_save_forums_remove_perms() );
		die();
	}
	if ($saveform == 'mergeforums') {
		echo esc_html( spa_save_forums_merge() );
		die();
	}
	if ($saveform == 'globalrss') {
		echo esc_html( spa_save_forums_global_rss() );
		die();
	}
	if ($saveform == 'globalrssset') {
		echo esc_html( spa_save_forums_global_rssset() );
		die();
	}
	if ($saveform == 'grouppermission') {
		echo esc_html( spa_save_forums_group_perm() );
		die();
	}
	if ($saveform == 'editgroup') {
		echo esc_html( spa_save_forums_edit_group() );
		die();
	}
	if ($saveform == 'deletegroup') {
		echo esc_html( spa_save_forums_delete_group() );
		die();
	}
	if ($saveform == 'editforum') {
		echo esc_html( spa_save_forums_edit_forum() );
		die();
	}
	if ($saveform == 'deleteforum') {
		echo esc_html( spa_save_forums_delete_forum() );
		die();
	}
	if ($saveform == 'disableforum') {
		echo esc_html( spa_save_forums_disable_forum() );
		die();
	}
	if ($saveform == 'enableforum') {
		echo esc_html( spa_save_forums_enable_forum() );
		die();
	}
	if ($saveform == 'addperm') {
		echo esc_html( spa_save_forums_forum_perm() );
		die();
	}
	if ($saveform == 'editperm') {
		echo esc_html( spa_save_forums_edit_perm() );
		die();
	}
	if ($saveform == 'delperm') {
		echo esc_html( spa_save_forums_delete_perm() );
		die();
	}
	if ($saveform == 'orderforum') {
		echo esc_html( spa_save_forums_order() );
		die();
	}
}

die();
