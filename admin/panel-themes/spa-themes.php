<?php
/*
Simple:Press
Admin Themes
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
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

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'theme-list';

spa_panel_header();
spa_render_themes_panel($tab);
spa_panel_footer();
