<?php
/*
Simple:Press
Admin Forums
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Forums
global $spStatus;
if (!sp_current_user_can('SPF Manage Forums')) die();

include_once SF_PLUGIN_DIR.'/admin/panel-forums/spa-forums-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if ($spStatus != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-forums';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'forums';
spa_panel_header();
spa_render_forums_panel($tab);
spa_panel_footer();
?>