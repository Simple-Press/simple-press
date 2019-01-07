<?php
/*
Simple:Press
Admin Panels - Option Management
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Options
if (!sp_current_user_can('SPF Manage Options')) die();

global $spStatus;

include_once SF_PLUGIN_DIR.'/admin/panel-options/spa-options-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-options/support/spa-options-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if ($spStatus != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-options';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'global';

spa_panel_header();
spa_render_options_panel($tab);
spa_panel_footer();

?>