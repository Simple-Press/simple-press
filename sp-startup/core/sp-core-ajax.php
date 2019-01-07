<?php
/**
 * Core AJAX support functions.
 * Loaded on all pages for both admin and front end.
 *
 * @since 6.0
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_is_frontend_ajax() {
    $ajax = false;
    if (wp_doing_ajax()) {
        $filename = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
        $ref = isset($_SERVER['HTTP_REFERER']) ? wp_unslash($_SERVER['HTTP_REFERER']) : '';
        if (((strpos($ref, admin_url()) === false) && (basename($filename) === 'admin-ajax.php'))) {
            $ajax = true;
        }
    }

    return $ajax;
}

/**
 * Checks and Loads admin constants and includes to support Ajax calls for non-forum pages.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function sp_check_api_support() {
	if (!SP()->isForum) {
		sp_forum_ajax_support();
	}
}

/**
 * Loads admin constants and includes to support Ajax calls.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_admin_ajax_support() {
	SP()->user->get_current_user();

	if (!SP()->admin) {
		SP()->admin = new spcAdminLoader();
		SP()->admin->load();
	}
}

/**
 * Loads forum constants and includes to support Ajax calls.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function sp_forum_ajax_support() {
	SP()->dateTime->set_timezone();
	SP()->user->get_current_user();

    if (!SP()->forum) {
		SP()->forum = new spcForumLoader();
		SP()->forum->load();
	}

	# backwards compat for legacy themes where topic lists created in themes
	if (!current_theme_supports('level-2-theme')) {
		include_once SP_PLUGIN_DIR.'/forum/content/legacy/sp-legacy-theme-support.php';
		include SP_PLUGIN_DIR.'/forum/content/legacy/sp-legacy-theme-globals.php';
		sp_legacy_theme_list_setup();
	}

	sp_load_plugin_styles(true);
	sp_get_track_id();
}
