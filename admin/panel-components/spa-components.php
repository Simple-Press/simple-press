<?php
/*
Simple:Press
Admin Panels - Component Management
$LastChangedDate: 2018-11-02 13:31:02 -0500 (Fri, 02 Nov 2018) $
$Rev: 15796 $
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

$tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'smileys';
spa_panel_header();
spa_render_components_panel($tab);
spa_panel_footer();
