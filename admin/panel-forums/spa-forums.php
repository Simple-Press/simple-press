<?php
/*
Simple:Press
Admin Forums
$LastChangedDate: 2018-11-02 13:31:02 -0500 (Fri, 02 Nov 2018) $
$Rev: 15796 $
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

$tab = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'forums';
spa_panel_header();
spa_render_forums_panel($tab);
spa_panel_footer();
