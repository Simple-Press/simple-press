<?php
/*
Simple:Press
Ajax Action handler - Forum
$LastChangedDate: 2016-05-12 12:50:53 +0100 (Thu, 12 May 2016) $
$Rev: 14184 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# FORUM - This file loads at forum level - all page loads
# handles all AJAX calls via WP Ajax
#
# ==========================================================================================


function sp_ajax_newtopicpost() {
	include SF_PLUGIN_DIR.'/forum/library/sp-post.php';
}
add_action('wp_ajax_new-topic', 'sp_ajax_newtopicpost');
add_action('wp_ajax_nopriv_new-topic', 'sp_ajax_newtopicpost');
add_action('wp_ajax_new-post', 'sp_ajax_newtopicpost');
add_action('wp_ajax_nopriv_new-post', 'sp_ajax_newtopicpost');

function sp_ajax_search() {
	include SF_PLUGIN_DIR.'/forum/library/sp-search.php';
}
add_action('wp_ajax_search', 'sp_ajax_search');
add_action('wp_ajax_nopriv_search', 'sp_ajax_search');

function sp_ajax_admintoollinks() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-admintoollinks.php';
}
add_action('wp_ajax_spForumTopicTools', 'sp_ajax_admintoollinks');
add_action('wp_ajax_nopriv_spForumTopicTools', 'sp_ajax_admintoollinks');
add_action('wp_ajax_spForumPostTools', 'sp_ajax_admintoollinks');
add_action('wp_ajax_nopriv_spPostTopicTools', 'sp_ajax_admintoollinks');

function sp_ajax_forumtools() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-admintools.php';
}
add_action('wp_ajax_spForumTools', 'sp_ajax_forumtools');
add_action('wp_ajax_nopriv_spForumTools', 'sp_ajax_forumtools');


function sp_ajax_pagejump() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-manage.php';
}
add_action('wp_ajax_spForumPageJump', 'sp_ajax_pagejump');
add_action('wp_ajax_nopriv_spForumPageJump', 'sp_ajax_pagejump');
add_action('wp_ajax_spTopicPageJump', 'sp_ajax_pagejump');
add_action('wp_ajax_nopriv_spTopicPageJump', 'sp_ajax_pagejump');

function sp_ajax_quotepost() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-quote.php';
}
add_action('wp_ajax_spQuotePost', 'sp_ajax_quotepost');
add_action('wp_ajax_nopriv_spQuotePost', 'sp_ajax_quotepost');

function sp_ajax_removenotice() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-notice.php';
}
add_action('wp_ajax_spUserNotice', 'sp_ajax_removenotice');
add_action('wp_ajax_nopriv_spUserNotice', 'sp_ajax_removenotice');

function sp_ajax_unreadpostspopup() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-newpostpopup.php';
}
add_action('wp_ajax_spUnreadPostsPopup', 'sp_ajax_unreadpostspopup');
add_action('wp_ajax_nopriv_spUnreadPostsPopup', 'sp_ajax_unreadpostspopup');

function sp_ajax_permissions() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-permissions.php';
}
add_action('wp_ajax_permissions', 'sp_ajax_permissions');
add_action('wp_ajax_nopriv_permissions', 'sp_ajax_permissions');

function sp_ajax_profile() {
	include SF_PLUGIN_DIR.'/forum/profile/ajax/sp-ajax-profile.php';
}
add_action('wp_ajax_profile', 'sp_ajax_profile');
add_action('wp_ajax_nopriv_profile', 'sp_ajax_profile');

function sp_ajax_profilesave() {
	include SF_PLUGIN_DIR.'/forum/profile/ajax/sp-ajax-profile-save.php';
}
add_action('wp_ajax_profile-save', 'sp_ajax_profilesave');
add_action('wp_ajax_nopriv_profile-save', 'sp_ajax_profilesave');

?>