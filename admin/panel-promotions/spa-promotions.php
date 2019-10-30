<?php
/*
Simple:Press
Promotions
$LastChangedDate: 2018-10-17 15:14:27 -0500 (Wed, 17 Oct 2018) $
$Rev: 15755 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Users
if (!SP()->auths->current_user_can('SPF Manage Promotions')) die();

include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';
include_once SP_PLUGIN_DIR.'/admin/panel-promotions/spa-promotions-display.php';

if (SP()->core->status != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

# --------------------------------------------------------------------

if (isset($_GET['tab'])) {
	$formid = SP()->filters->str($_GET['tab']);
} else {
	if (isset($_GET['form'])) {
		$formid = SP()->filters->str($_GET['form']);
	} else {
		$formid = 'promotions-1';
	}
}

spa_panel_header();
spa_render_promotions_panel($formid);
spa_panel_footer();
