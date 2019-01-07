<?php
/*
Simple:Press
Admin ADmins
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Admins
global $spStatus;

if (!sp_current_user_can('SPF Manage Admins') && !$spThisUser->admin && !$spThisUser->moderator) die();

include_once SF_PLUGIN_DIR.'/admin/panel-admins/spa-admins-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if ($spStatus != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-admins';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'youradmin';
spa_panel_header();
spa_render_admins_panel($tab);
spa_panel_footer();
?>