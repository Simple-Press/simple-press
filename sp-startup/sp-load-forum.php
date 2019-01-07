<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2016-10-02 18:05:28 -0500 (Sun, 02 Oct 2016) $
$Rev: 14611 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	FORUM
# 	This file loads the SP support needed by all front end forum page loads
#	Template tags, widgets and support outside of the forum page itself
#	requires the includes only.
#
# ==========================================================================================

# Include fortum specific api files, core constants
include_once SF_PLUGIN_DIR.'/forum/database/sp-db-management.php';
include_once SPBOOT.'forum/sp-global-forum-constants.php';
include_once SPBOOT.'forum/sp-forum-constants.php';
include_once SF_PLUGIN_DIR.'/forum/library/sp-forum-support.php';
include_once SPBOOT.'forum/sp-forum-support-functions.php';
include_once SPBOOT.'forum/sp-forum-framework.php';

# Include template control and template functions
include_once SF_PLUGIN_DIR.'/forum/content/sp-template-control.php';

include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-group-view-class.php';
include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-forum-view-class.php';
include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-topic-view-class.php';
include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-search-view-class.php';
include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-list-topic-class.php';
include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-list-post-class.php';
include_once SF_PLUGIN_DIR.'/forum/content/classes/sp-member-view-class.php';

include_once SF_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-group-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-forum-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-topic-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-search-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-list-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-list-view-search-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-member-view-functions.php';
include_once SF_PLUGIN_DIR.'/forum/content/sp-profile-view-functions.php';

include_once SF_PLUGIN_DIR.'/forum/content/sp-forms.php';

# try to increase some php settings
sp_php_overrides();

# Only load the hooks below for an actual forum page
global $spIsForum, $spDevice, $spStatus;
if ($spIsForum) {
	# Set up Forum support WP Hooks

	# Main Forum Display Hooks
	add_action('template_redirect', 'sp_populate_query_vars', 1);
	if ($spDevice == 'mobile' || $spDevice == 'tablet') {
		add_action('template_redirect', 'sp_load_mobile_template', 10);
	}
	add_action('wp_head',              'sp_forum_header');
	add_action('wp_print_styles',      'sp_load_plugin_styles');
	add_action('wp_enqueue_scripts',   'sp_load_forum_scripts', 1);
	add_action('wp_enqueue_scripts',   'sp_load_forum_footer_scripts', 9999);
	add_action('wp_enqueue_scripts',   'sp_load_editor');
	add_action('wp_footer',            'sp_forum_footer');

	# load masonry ifprofile page view
	add_action('sph_scripts_end', 		'sp_profile_masonry', 1, 2);

	# Page Content Level Display Filters
   	add_filter('the_content',      'sp_render_forum', 1);

    # are we running wptexturize on post content?
	$texturize = sp_get_option('spwptexturize');
    if (!$texturize) add_filter('run_wptexturize', '__return_false');

   	# redirect for forum on front page
   	add_filter('redirect_canonical', 'sp_front_page_redirect');

    if ($spStatus == 'ok') {
    	# Shortcodes
    	add_shortcode('spoiler', 'sp_filter_display_spoiler');

    	# WP Page Title
    	add_action('loop_start',   'sp_title_hook');
    	add_filter('the_title',    'sp_setup_page_title', 9999, 2); # Needs to stay consistent with hook remove/add functions below

        # while WP is processing nav mnenus, we need to disable our the_title hook processing
        # so lets remove at start of nav processing and add back in at the end
        add_filter('pre_wp_nav_menu', 'sp_title_hook_remove');
        add_filter('wp_nav_menu', 'sp_title_hook_add');

    	# keep wp capital P stuff from making menus show full page title
    	remove_filter('the_content',   'capital_P_dangit', 11);
    	remove_filter('the_title',     'capital_P_dangit', 11);
    	remove_filter('comment_text',  'capital_P_dangit', 31);

    	# add filter for wp canonical url generation for forum pages since it would always point to the single wp page
    	add_filter('get_canonical_url', 'sp_get_canonical_url', 10, 2);

    	# browser title
    	if (current_theme_supports('title-tag')) { # wp 4.4+ if themes support
        	add_filter('pre_get_document_title', 'sp_browser_title', 999); # want it to run last
        } else {
            add_filter('wp_title', 'sp_setup_browser_title', 999, 3); # want it to run last
        }

    	# WP List Pages Hack
    	$sfwplistpages = sp_get_option('sfwplistpages');
    	if ($sfwplistpages) {
    		add_filter('wp_list_pages',   'sp_wp_list_pages');
    		add_filter('wp_nav_menu',     'sp_wp_list_pages');
    	}

        # SOME OTHER WP PLUGIN SUPPORT
    	# all in one seo pack plugin api
    	add_filter('aioseop_canonical_url',    'sp_aioseo_canonical_url');
    	add_filter('aioseop_description',      'sp_aioseo_description');
    	add_filter('aioseop_keywords',         'sp_aioseo_keywords');
    	add_filter('aioseop_home_page_title',  'sp_aioseo_homepage');

		# Open Graph Meta Tags
		add_filter('language_attributes',		'sp_og_namespace', 100);
		add_action('wp_head',					'sp_og_meta', 100);

        # handle wp seo (yoast)
        add_filter('template_redirect', 'sp_wp_seo_hooks', 999999);
    }

	do_action('sph_forum_startup');
}
?>