<?php
/**
 * Forum ajax actions
 * This file loads at core level - all page loads for front end
 * Handles all forum level ajax calls sent through the WP ajax
 *
 *  $LastChangedDate: 2016-12-25 16:24:07 -0800 (Sun, 25 Dec 2016) $
 *  $Rev: 14902 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

add_action('wp_ajax_new-topic', 'sp_ajax_newtopicpost');
add_action('wp_ajax_nopriv_new-topic', 'sp_ajax_newtopicpost');
add_action('wp_ajax_new-post', 'sp_ajax_newtopicpost');
add_action('wp_ajax_nopriv_new-post', 'sp_ajax_newtopicpost');

function sp_ajax_newtopicpost() {
	require_once SP_PLUGIN_DIR.'/forum/library/sp-post.php';
}

add_action('wp_ajax_search', 'sp_ajax_search');
add_action('wp_ajax_nopriv_search', 'sp_ajax_search');

function sp_ajax_search() {
	require_once SP_PLUGIN_DIR.'/forum/library/sp-search.php';
}

add_action('wp_ajax_spForumTopicTools', 'sp_ajax_admintoollinks');
add_action('wp_ajax_nopriv_spForumTopicTools', 'sp_ajax_admintoollinks');
add_action('wp_ajax_spForumPostTools', 'sp_ajax_admintoollinks');
add_action('wp_ajax_nopriv_spPostTopicTools', 'sp_ajax_admintoollinks');

function sp_ajax_admintoollinks() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-admintoollinks.php';
}

add_action('wp_ajax_spForumTools', 'sp_ajax_forumtools');
add_action('wp_ajax_nopriv_spForumTools', 'sp_ajax_forumtools');

function sp_ajax_forumtools() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-admintools.php';
}

add_action('wp_ajax_spForumPageJump', 'sp_ajax_pagejump');
add_action('wp_ajax_nopriv_spForumPageJump', 'sp_ajax_pagejump');
add_action('wp_ajax_spTopicPageJump', 'sp_ajax_pagejump');
add_action('wp_ajax_nopriv_spTopicPageJump', 'sp_ajax_pagejump');

function sp_ajax_pagejump() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-manage.php';
}

add_action('wp_ajax_spQuotePost', 'sp_ajax_quotepost');
add_action('wp_ajax_nopriv_spQuotePost', 'sp_ajax_quotepost');

function sp_ajax_quotepost() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-quote.php';
}

add_action('wp_ajax_spUserNotice', 'sp_ajax_removenotice');
add_action('wp_ajax_nopriv_spUserNotice', 'sp_ajax_removenotice');

function sp_ajax_removenotice() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-notice.php';
}

add_action('wp_ajax_spUnreadPostsPopup', 'sp_ajax_unreadpostspopup');
add_action('wp_ajax_nopriv_spUnreadPostsPopup', 'sp_ajax_unreadpostspopup');

function sp_ajax_unreadpostspopup() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-newpostpopup.php';
}

add_action('wp_ajax_permissions', 'sp_ajax_permissions');
add_action('wp_ajax_nopriv_permissions', 'sp_ajax_permissions');

function sp_ajax_permissions() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-permissions.php';
}

add_action('wp_ajax_profile', 'sp_ajax_profile');
add_action('wp_ajax_nopriv_profile', 'sp_ajax_profile');

function sp_ajax_profile() {
	require_once SP_PLUGIN_DIR.'/forum/profile/ajax/sp-ajax-profile.php';
}

add_action('wp_ajax_profile-save', 'sp_ajax_profilesave');
add_action('wp_ajax_nopriv_profile-save', 'sp_ajax_profilesave');

function sp_ajax_profilesave() {
	require_once SP_PLUGIN_DIR.'/forum/profile/ajax/sp-ajax-profile-save.php';
}
