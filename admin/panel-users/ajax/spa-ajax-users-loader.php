<?php
/*
Simple:Press Users Admin
Ajax form loader - Users
$LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
$Rev: 15795 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('users-loader')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

require_once SP_PLUGIN_DIR.'/admin/panel-users/spa-users-display.php';
include_once SP_PLUGIN_DIR.'/admin/panel-users/support/spa-users-prepare.php';
include_once SP_PLUGIN_DIR.'/admin/panel-users/support/spa-users-save.php';
include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

global $adminhelpfile;
$adminhelpfile = 'admin-users';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Users
if (!SP()->auths->current_user_can('SPF Manage Users')) die();

if (isset($_GET['loadform'])) {
	spa_render_users_container(sanitize_text_field($_GET['loadform']));
	die();
}

if (isset($_GET['saveform'])) {
    die();
}



$userid = (isset($_GET['user'])) ? SP()->filters->integer($_GET['user']) : 0;
$action = (isset($_GET['action'])) ? $_GET['action'] : '';

$out = '';

# is it a popup profile user groups?
if ($action == 'membergroup' ) {
	
	if (empty($userid)) {
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid profile request'));
		$out .= SP()->notifications->render_queued();
		$out .= '<div class="sfmessagestrip">';
		$out .= apply_filters('sph_ProfileErrorMsg', SP()->primitives->front_text('Sorry, an invalid profile groups request was detected'));
		$out .= '</div>';

		return $out;
	}

	require_once SP_PLUGIN_DIR.'/forum/content/sp-common-control-functions.php';
	require_once SP_PLUGIN_DIR.'/forum/content/sp-template-control.php';
	require_once SP_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';
	require_once SP_PLUGIN_DIR.'/forum/content/sp-profile-view-functions.php';
	
	sp_SetupUserProfileData($userid);
	
	$groups = spa_user_groups_list( $userid );

	echo '<div id="spMainContainer">';
	include 'user-groups.php';
	echo '</div>';

	die();
}


die();
