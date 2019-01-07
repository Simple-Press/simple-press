<?php
/*
Simple:Press
Admin Themes
$LastChangedDate: 2018-11-02 13:31:02 -0500 (Fri, 02 Nov 2018) $
$Rev: 15796 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

if (!SP()->auths->current_user_can('SPF Manage Themes')) die();

require_once SP_PLUGIN_DIR.'/admin/panel-themes/spa-themes-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-themes';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'theme-list';

spa_panel_header();
spa_render_themes_panel($tab);
spa_panel_footer();
