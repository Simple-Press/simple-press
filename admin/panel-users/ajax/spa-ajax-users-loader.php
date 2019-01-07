<?php
/*
Simple:Press Users Admin
Ajax form loader - Users
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('users-loader')) die();

global $spStatus;
if ($spStatus != 'ok') {
	echo $spStatus;
	die();
}

include_once SF_PLUGIN_DIR.'/admin/panel-users/spa-users-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-users/support/spa-users-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/panel-users/support/spa-users-save.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-users';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Users
if (!sp_current_user_can('SPF Manage Users')) die();

if (isset($_GET['loadform'])) {
	spa_render_users_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
    die();
}

die();

?>