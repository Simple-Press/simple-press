<?php
/*
Simple:Press Users Admin
Ajax form loader - Users
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('users-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-users/spa-users-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-users/support/spa-users-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-users/support/spa-users-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-users';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Users
if (!SP()->auths->current_user_can('SPF Manage Users')) die();

if (isset($_GET['loadform'])) {
	spa_render_users_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
    die();
}

die();
