<?php
/*
Simple:Press
DESC: Loads core code - both back/front end - sitewide
$LastChangedDate: 2018-08-24 21:05:16 -0500 (Fri, 24 Aug 2018) $
$Rev: 15719 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE
#	This file loads the core SP support needed by both front/back ends for all page loads -
# 	not just for the forum. It also exposes base api files that may be needed for plugins,
#	template tags, widgets etc.
#
# ==========================================================================================

# Global variables needed
global $spIsAdmin, $spIsForumAdmin, $spStatus, $spPaths, $spGlobals;

# Include sf-debug by default
include_once (SPBOOT.'sp-load-debug.php');

# Determine quickly if admin and then if forum admin page load
if (is_admin()) {
	$spIsAdmin = true;
	# is it an SP admin load
	if ((isset($_GET['page'])) && (stristr($_GET['page'], 'simple-press')) !== false) {
		$spIsForumAdmin = true;
	}
}

# Define table constants
if (!defined('SFGROUPS'))         define('SFGROUPS',      	 SF_PREFIX.'sfgroups');
if (!defined('SFFORUMS'))         define('SFFORUMS',      	 SF_PREFIX.'sfforums');
if (!defined('SFTOPICS'))         define('SFTOPICS',    	 SF_PREFIX.'sftopics');
if (!defined('SFPOSTS'))          define('SFPOSTS',     	 SF_PREFIX.'sfposts');
if (!defined('SFTRACK'))          define('SFTRACK',     	 SF_PREFIX.'sftrack');
if (!defined('SFUSERGROUPS'))     define('SFUSERGROUPS',  	 SF_PREFIX.'sfusergroups');
if (!defined('SFPERMISSIONS'))    define('SFPERMISSIONS', 	 SF_PREFIX.'sfpermissions');
if (!defined('SFDEFPERMISSIONS')) define('SFDEFPERMISSIONS', SF_PREFIX.'sfdefpermissions');
if (!defined('SFROLES'))          define('SFROLES',       	 SF_PREFIX.'sfroles');
if (!defined('SFMEMBERS'))        define('SFMEMBERS',     	 SF_PREFIX.'sfmembers');
if (!defined('SFMEMBERSHIPS'))    define('SFMEMBERSHIPS',    SF_PREFIX.'sfmemberships');
if (!defined('SFMETA'))           define('SFMETA', 	    	 SF_PREFIX.'sfmeta');
if (!defined('SFLOG'))            define('SFLOG',			 SF_PREFIX.'sflog');
if (!defined('SFLOGMETA'))		  define('SFLOGMETA',		 SF_PREFIX.'sflogmeta');
if (!defined('SFOPTIONS'))        define('SFOPTIONS',		 SF_PREFIX.'sfoptions');
if (!defined('SFERRORLOG'))		  define('SFERRORLOG',		 SF_PREFIX.'sferrorlog');
if (!defined('SFAUTHS'))		  define('SFAUTHS',		     SF_PREFIX.'sfauths');
if (!defined('SFAUTHCATS'))  	  define('SFAUTHCATS',		 SF_PREFIX.'sfauthcats');
if (!defined('SFWAITING'))		  define('SFWAITING',		 SF_PREFIX.'sfwaiting');
if (!defined('SFNOTICES'))		  define('SFNOTICES',		 SF_PREFIX.'sfnotices');
if (!defined('SFSPECIALRANKS'))	  define('SFSPECIALRANKS', 	 SF_PREFIX.'sfspecialranks');
if (!defined('SFUSERACTIVITY'))	  define('SFUSERACTIVITY', 	 SF_PREFIX.'sfuseractivity');
if (!defined('SFCACHE'))		  define('SFCACHE',		 	 SF_PREFIX.'sfcache');
if (!defined('SFADMINKEYWORDS'))  define('SFADMINKEYWORDS',	 SF_PREFIX.'sfadminkeywords');
if (!defined('SFADMINTASKS'))	  define('SFADMINTASKS',	 SF_PREFIX.'sfadmintasks');

# Make plain text editor and filters available
include_once SF_PLUGIN_DIR.'/forum/editor/sp-text-editor.php';

# Include core api files, core constants and storage locations
include_once SPAPI.'sp-api-cache.php';
include_once SPAPI.'sp-api-error.php';
include_once SPAPI.'sp-api-wpdb.php';
include_once SPAPI.'sp-api-primitives.php';
include_once SPAPI.'sp-api-notifications.php';
include_once SPAPI.'sp-api-timezone.php';
include_once SPAPI.'sp-api-device.php';

include_once SPBOOT.'site/sp-site-support-functions.php';
include_once SPBOOT.'site/sp-site-deprecated.php';

# see if mobile page load
sp_mobile_check();

$spPaths = sp_get_option('sfconfig');
include_once SPBOOT.'site/sp-site-constants.php';

# include user functions file if exists (since 5.5.7)
if (file_exists(WP_CONTENT_DIR.'/sp-user-functions.php')) {
	include_once WP_CONTENT_DIR.'/sp-user-functions.php';
}

include_once SF_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';
include_once SF_PLUGIN_DIR.'/forum/database/sp-db-newposts.php';
include_once SF_PLUGIN_DIR.'/forum/database/sp-db-forums.php';

include_once SPAPI.'sp-api-permalinks.php';
include_once SPAPI.'sp-api-kses.php';
include_once SPAPI.'sp-api-filters.php';
include_once SPAPI.'sp-api-auths.php';
include_once SPAPI.'sp-api-users.php';
include_once SPAPI.'sp-api-globals.php';
include_once SPAPI.'sp-api-class-user.php';
include_once SPAPI.'sp-api-common-display.php';
include_once SPAPI.'sp-api-form-support.php';
include_once SPAPI.'sp-api-plugins.php';
include_once SPAPI.'sp-api-themes.php';
include_once SPAPI.'sp-api-profile.php';
include_once SPAPI.'sp-api-class-post.php';

# Load core support code
include_once SPBOOT.'site/sp-site-cron.php';

# common ajax actions
include_once SPBOOT.'site/sp-common-ajax-actions.php';

# Set th system status as soon as possible and init the globals
sp_get_system_status();
sp_initialize_globals();

# forum only ajax actions
if ($spStatus == 'ok') {
	include_once SPBOOT.'forum/sp-forum-ajax-actions.php';
}

# Load template functions file if exsists
if ($spStatus == 'ok') {
    # load theme spFunctions.php if it exists
	if (file_exists(SPTEMPLATES.'spFunctions.php')) include_once SPTEMPLATES.'spFunctions.php';

    # if child theme, load the parent spFunctions.php if it exists
	$curTheme = $spGlobals['theme'];
    if (!empty($curTheme['parent']) && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/templates/spFunctions.php')) include_once SPTHEMEBASEDIR.$curTheme['parent'].'/templates/spFunctions.php';

	do_action('sph_theme_functions_loaded');
}

# Load active plugins
if ($spStatus == 'ok' || !$spIsAdmin) {
	$sp_plugins = sp_get_active_and_valid_plugins();
	if ($sp_plugins) {
	    foreach ($sp_plugins as $sp_plugin) {
	    	include_once($sp_plugin);
	    }
	    unset($sp_plugin);
	}
	do_action('sph_plugins_loaded');

	# special call to allow plugins to filter $spGlobals
	sp_filter_globals();
}

# Set up core support WP Hooks

# Initialisation Routines
add_action('init', 'sp_localisation', 4);
add_action('init', 'sp_rewrite_rules_flush_check');

# Rewrite Rules
add_filter('page_rewrite_rules', 'sp_set_rewrite_rules');

# Capture the query variables
add_filter('query_vars', 'sp_set_query_vars');

# Credential Actions/Filters
if ($spStatus == 'ok') {
	include_once SPBOOT.'site/credentials/sp-credentials.php';
	add_action('login_redirect', 		'sp_login_redirect', 10, 3);
	add_action('registration_redirect', 'sp_register_redirect');
	add_action('wp_logout', 			'sp_logout_redirect');
	add_action('wp_login', 			    'sp_post_login_check');
	$sfmail = sp_get_option('sfnewusermail');
	if (isset($sfmail['sfusespfreg']) && $sfmail['sfusespfreg'] == true) include_once SPBOOT.'site/credentials/sp-new-user-email.php';
}

# User registrations and logout
add_action('register_form', 		'spa_register_math', 50);
add_filter('registration_errors', 	'spa_register_error');

# Keep track of logouts
add_action('wp_login',	'sp_track_login');
add_action('wp_logout', 'sp_track_logout');

# RSS feeds
add_action('template_redirect', 'sp_feed', 2);
add_filter('pre_get_posts',     'sp_is_feed_check');

# RPX Support
$sfrpx = sp_get_option('sfrpx');
if ($sfrpx['sfrpxenable']) {
	include_once SPBOOT.'site/credentials/sp-rpx.php';
	add_action('parse_request', 	'sp_rpx_process_token');
	add_action('sph_login_head', 	'sp_rpx_login_head');
	add_action('show_user_profile', 'sp_rpx_edit_user_page');
}

# Cron hooks
if ($spStatus != 'Install') {
	add_action('sph_cron_user', 				'sp_cron_remove_users');
	add_action('sph_transient_cleanup_cron', 	'sp_cron_transient_cleanup');
	add_action('sph_stats_cron', 				'sp_cron_generate_stats');
	add_action('sph_news_cron', 				'sp_cron_check_news');
	add_action('cron_schedules', 				'sp_cron_schedules');
	add_action('wp',                            'sp_cron_scheduler');
}

# WP Avatar replacement - low priority - let everyone else settle out
$sfavatars = array();
$sfavatars = sp_get_option('sfavatars');
if (!empty($sfavatars['sfavatarreplace'])) {
    add_filter('get_avatar',            'sp_wp_avatar', 900, 3);
    add_filter('default_avatar_select', 'spa_wp_discussion_avatar');
}

# Get_permalink() filter for forum pages
add_filter('page_link', 'sp_get_permalink', 10, 3);

# User related hooks
add_action('wpmu_new_user', 		'sp_create_member_data', 99);
add_action('wpmu_activate_user', 	'sp_create_member_data', 99);
add_action('added_existing_user', 	'sp_create_member_data', 99);
add_action('wpmu_delete_user', 		'sp_delete_member_data');
add_action('remove_user_from_blog', 'sp_delete_member_data', 10, 2);
add_action('user_register', 		'sp_create_member_data', 99);
add_action('delete_user', 			'sp_delete_member_data');
add_action('profile_update', 		'sp_update_member_data');
add_action('set_user_role', 		'sp_set_role_to_ug', 10, 3);
add_action('add_user_role', 		'sp_add_role_to_ug', 10, 2);
add_action('remove_user_role', 		'sp_remove_role_to_ug', 10, 2);
add_action('delete_user_form', 		'sp_delete_user_form', 10, 2);

add_filter('registration_errors',           'sp_validate_registration', 10, 3);
add_action('user_profile_update_errors',    'sp_validate_display_name', 10, 3);

add_action('wp_update_plugins', 'sp_check_for_updates');

do_action('sph_core_startup');
?>