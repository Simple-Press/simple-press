<?php
/*
Simple:Press
Admin Forums
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Forums
if (!SP()->auths->current_user_can('SPF Manage Forums')) die();

include_once SP_PLUGIN_DIR.'/admin/panel-forums/spa-forums-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

if (SP()->core->status != 'ok') {
    include_once SPLOADINSTALL;
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-forums';
# --------------------------------------------------------------------

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'forums';
spa_panel_header();
spa_render_forums_panel($tab);
spa_panel_footer();
