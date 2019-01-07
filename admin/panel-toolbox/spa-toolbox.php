<?php
/*
Simple:Press
Admin Panels - Toolbox
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Toolbox
if (!SP()->auths->current_user_can('SPF Manage Toolbox')) die();

require_once SP_PLUGIN_DIR.'/admin/panel-toolbox/spa-toolbox-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-toolbox/support/spa-toolbox-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-toolbox';
# --------------------------------------------------------------------

if (isset($_GET['tab'])) {
	$formid = $_GET['tab'];
} else {
	if (isset($_GET['form'])) {
		$formid = $_GET['form'];
	} else {
		$formid = 'toolbox';
	}
}

spa_panel_header();
spa_render_toolbox_panel($formid);
spa_panel_footer();
