<?php
/*
Simple:Press
Admin Panels - Component Management
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Components
if (!SP()->auths->current_user_can('SPF Manage Components')) die();

include_once SP_PLUGIN_DIR.'/admin/panel-components/spa-components-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-components/support/spa-components-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

# Check if plugin update is required
if (SP()->core->status != 'ok') {
	require_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-components';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'smileys';
spa_panel_header();
spa_render_components_panel($tab);
spa_panel_footer();
