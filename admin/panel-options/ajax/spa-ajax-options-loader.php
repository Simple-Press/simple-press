<?php

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('options-loader')) {
    die();
}

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-options/spa-options-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-options/support/spa-options-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-options/support/spa-options-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-options';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!SP()->auths->current_user_can('SPF Manage Options')) die();

if (isset($_GET['loadform'])) {
	spa_render_options_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
	switch (sanitize_text_field($_GET['saveform'])) {
		case 'global':
		echo spa_save_global_data();
		break;

		case 'display':
		echo spa_save_display_data();
		break;

		case 'content':
		echo spa_save_content_data();
		break;

		case 'members':
		echo spa_save_members_data();
		break;

		case 'email':
		echo spa_save_email_data();
		break;

		case 'newposts':
		echo spa_save_newposts_data();
		break;
	}
	die();
}

die();
