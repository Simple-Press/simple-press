<?php
/*
Simple:Press
Admin Integration
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

global $spStatus;

# Check Whether User Can Manage Integration
if (!sp_current_user_can('SPF Manage Integration')) die();

include_once SF_PLUGIN_DIR.'/admin/panel-integration/spa-integration-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-integration/support/spa-integration-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if ($spStatus != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-integration';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'page';
spa_panel_header();
spa_render_integration_panel($tab);
spa_panel_footer();
?>