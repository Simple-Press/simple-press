<?php
/*
Simple:Press
Admin Panels - Option Management
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Options
if (!SP()->auths->current_user_can('SPF Manage Options')) die();

require_once SP_PLUGIN_DIR.'/admin/panel-options/spa-options-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-options/support/spa-options-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
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
