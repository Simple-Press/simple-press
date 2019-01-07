<?php
/*
Simple:Press
Admin Profiles
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Profiles
if (!sp_current_user_can('SPF Manage Profiles')) die();

global $spStatus;

include_once SF_PLUGIN_DIR.'/admin/panel-profiles/spa-profiles-display.php';
include_once SF_PLUGIN_DIR.'/admin/panel-profiles/support/spa-profiles-prepare.php';
include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if ($spStatus != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-profiles';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'options';
spa_panel_header();
spa_render_profiles_panel($tab);
spa_panel_footer();

?>