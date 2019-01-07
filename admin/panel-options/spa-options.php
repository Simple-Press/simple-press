<?php
/*
Simple:Press
Admin Panels - Option Management
$LastChangedDate: 2018-11-02 13:31:02 -0500 (Fri, 02 Nov 2018) $
$Rev: 15796 $
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

$tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'global';

spa_panel_header();
spa_render_options_panel($tab);
spa_panel_footer();
