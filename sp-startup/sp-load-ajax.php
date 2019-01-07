<?php
/*
Simple:Press
Global Ajax loader support
$LastChangedDate: 2016-06-25 08:14:16 -0500 (Sat, 25 Jun 2016) $
$Rev: 14331 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	Ajax
# 	This file exposes the core functions needed by Ajax calls (front/back end)
#
# ==========================================================================================


# ------------------------------------------------------------------------------------------
# sp_check_api_support()
#
# Checks and Loads admin constants and includes to support Ajax calls fro non-forum pages
# ------------------------------------------------------------------------------------------
function sp_check_api_support() {
	global $spIsForum;
	if(!$spIsForum) {
		sp_forum_ajax_support();
	}
}

# ------------------------------------------------------------------------------------------
# spa_admin_ajax_support()
#
# Loads admin constants and includes to support Ajax calls
# ------------------------------------------------------------------------------------------
function spa_admin_ajax_support() {
	include_once SPBOOT.'sp-load-core.php';
	sp_load_current_user();
	include_once SPBOOT.'sp-load-core-admin.php';
	include_once SPBOOT.'sp-load-admin.php';
}

# ------------------------------------------------------------------------------------------
# sp_forum_ajax_support()
#
# Loads forum constants and includes to support Ajax calls
# ------------------------------------------------------------------------------------------
function sp_forum_ajax_support() {
	include_once SPBOOT.'sp-load-core.php';
	sp_set_server_timezone();
	sp_load_current_user();
	include_once SPBOOT.'sp-load-site.php';
	include_once SPBOOT.'sp-load-forum.php';
	sp_load_plugin_styles(true);
	sp_get_track_id();
}

do_action('sph_ahah_startup');
do_action('sph_ajax_startup');

?>