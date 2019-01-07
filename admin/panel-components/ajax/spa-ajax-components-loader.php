<?php
/*
Simple:Press Admin
Ajax form loader - Components
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('components-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

include_once SP_PLUGIN_DIR.'/admin/panel-components/spa-components-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-components/support/spa-components-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-components/support/spa-components-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-components';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!SP()->auths->current_user_can('SPF Manage Components')) die();

if (isset($_GET['loadform'])) {
	spa_render_components_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	switch($_GET['saveform']) {
		case 'smileys':
			echo spa_save_smileys_data();
			break;

		case 'login':
			echo spa_save_login_data();
			break;

		case 'seo':
			echo spa_save_seo_data();
			break;

		case 'forumranks':
			echo spa_save_forumranks_data();
			break;

		case 'specialranks':
			switch ($_GET['targetaction']) {
				case 'newrank':
					echo spa_add_specialrank();
					break;
				case 'updaterank':
					echo spa_update_specialrank(SP()->filters->integer($_GET['id']));
					break;
				case 'addmember':
					echo spa_add_special_rank_member(SP()->filters->integer($_GET['id']));
					break;
				case 'delmember':
					echo spa_del_special_rank_member(SP()->filters->integer($_GET['id']));
					break;
			}
			break;

		case 'messages':
			echo spa_save_messages_data();
			break;
	}
	die();
}

die();
