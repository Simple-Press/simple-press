<?php
/*
Simple:Press
Admin User Groups
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage User Groups
if (!SP()->auths->current_user_can('SPF Manage User Groups')) die();

require_once SP_PLUGIN_DIR.'/admin/panel-usergroups/spa-usergroups-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-usergroups/support/spa-usergroups-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
	include_once SPLOADINSTALL;
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-usergroups';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'usergroups';
spa_panel_header();
spa_render_usergroups_panel($tab);
spa_panel_footer();
