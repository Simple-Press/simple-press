<?php
/*
Simple:Press Promotions
Ajax form loader - Promotions
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('promotions-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-promotions/spa-promotions-display.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

# --------------------------------------------------------------------

# ----------------------------------
if (!SP()->auths->current_user_can('SPF Manage Promotions')) die();

if (isset($_GET['loadform'])) {
	spa_render_promotions_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
    die();
}

die();
