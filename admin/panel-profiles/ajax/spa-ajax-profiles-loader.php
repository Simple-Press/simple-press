<?php
/*
Simple:Press Admin
Ajax form loader - Profiles
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('profiles-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-profiles/spa-profiles-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-profiles/support/spa-profiles-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-profiles/support/spa-profiles-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-profiles';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Profiles
if (!SP()->auths->current_user_can('SPF Manage Profiles')) die();

if (isset($_GET['loadform'])) {
	spa_render_profiles_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	switch(sanitize_text_field($_GET['saveform'])) {
		case 'global':
			echo spa_save_global_data();
			break;

		case 'tabs-menus':
			echo spa_save_tabs_menus_data();
			break;

		case 'options':
			echo spa_save_options_data();
			break;

		case 'avatars':
			echo spa_save_avatars_data();
			break;
	}
}

die();
