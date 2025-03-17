<?php
/*
Simple:Press Permissions Admin
Ajax form loader - Permissions
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('permissions-loader')) {
    die();
}

if (SP()->core->status != 'ok') {
	die(esc_html(SP()->core->status));
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
	spa_render_permissions_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	$saveform = sanitize_text_field($_GET['saveform']);
	if ($saveform == 'addperm') {
		die(esc_html(spa_save_permissions_new_role()));
	}
	if ($saveform == 'editperm') {
		die(esc_html(spa_save_permissions_edit_role()));
	}
	if ($saveform == 'delperm') {
		die(esc_html(spa_save_permissions_delete_role()));
	}
	if ($saveform == 'resetperms') {
		die(esc_html(spa_save_permissions_reset()));
	}
	if ($saveform == 'newauth') {
		die(esc_html(spa_save_permissions_new_auth()));
	}
}

die();
