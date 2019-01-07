<?php
/*
Simple:Press
Admin ADmins
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

if (!SP()->auths->current_user_can('SPF Manage Admins') && !SP()->user->thisUser->admin && !SP()->user->thisUser->moderator) die();

include_once SP_PLUGIN_DIR.'/admin/panel-admins/spa-admins-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-admins';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'youradmin';
spa_panel_header();
spa_render_admins_panel($tab);
spa_panel_footer();
