<?php
/*
Simple:Press Users Admin
Ajax form loader - Users
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
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
	spa_render_users_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
    die();
}

die();
