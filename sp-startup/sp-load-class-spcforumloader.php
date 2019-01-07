<?php

/**
 * Class used for forum loader.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * load()
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcForumLoader {
	/**
	 *
	 * @var bool    has the forum content been loaded
	 *
	 * @since 6.0
	 */
	public $contentLoaded = false;

	/**
	 *
	 * @var bool    forum view class
	 *
	 * @since 6.0
	 */
	public $view;

	/**
	 * This method kicks off the class loader and loads up things needed on all forum page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function load() {
		# Include forum support functions early
		require_once SPBOOT.'forum/sp-forum-support-functions.php';

		# load up forum constants
		$this->setup_constants();

		# load up forum includes
		$this->includes();

		# run forum startup code
		$this->startup();

		# run forum hooks
		$this->hooks();

		do_action('sph_forum_load_complete');
	}

	/**
	 * This method loads up the forum constants needed on all forum page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function setup_constants() {
		require_once SPBOOT.'forum/sp-forum-constants.php';

		# fire action to indicate constants complete
		do_action('sph_forum_constants_complete');
	}

	/**
	 * This method the required forum files needed on all forum page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function includes() {
		include_once SP_PLUGIN_DIR.'/forum/library/sp-forum-support.php';
		include_once SPBOOT.'forum/sp-forum-framework.php';

		include_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';
		include_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';
		include_once SP_PLUGIN_DIR.'/forum/database/sp-db-newposts.php';
		include_once SP_PLUGIN_DIR.'/forum/database/sp-db-forums.php';

		# Include template control and template functions
		include_once SP_PLUGIN_DIR.'/forum/content/sp-template-control.php';
		include_once SP_PLUGIN_DIR.'/forum/content/sp-common-control-functions.php';
		include_once SP_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';

		# load up the rewrite page vars
		SP()->rewrites->page_vars();

		# only load what we need on page view
		if (!empty(SP()->rewrites->pageData['pageview'])) {
			switch (SP()->rewrites->pageData['pageview']) {
				case 'group':
					include_once SP_PLUGIN_DIR.'/forum/content/sp-group-view-functions.php';
					break;

				case 'forum':
					include_once SP_PLUGIN_DIR.'/forum/content/sp-forum-view-functions.php';
					break;

				case 'topic':
					include_once SP_PLUGIN_DIR.'/forum/content/sp-topic-view-functions.php';
					break;

				case 'search':
					include_once SP_PLUGIN_DIR.'/forum/content/sp-search-view-functions.php';
					break;

				case 'members':
					include_once SP_PLUGIN_DIR.'/forum/content/sp-member-view-functions.php';
					break;

				case 'profileshow' || 'profileedit':
					include_once SP_PLUGIN_DIR.'/forum/content/sp-profile-view-functions.php';
					break;
			}
		}

		# always load the list and list post stuff
		include_once SP_PLUGIN_DIR.'/forum/content/sp-list-view-functions.php';
		include_once SP_PLUGIN_DIR.'/forum/content/sp-list-view-search-functions.php';

		# always load the form stuff
		include_once SP_PLUGIN_DIR.'/forum/content/sp-forms.php';

		# Make plain text editor and filters available
		include_once SP_PLUGIN_DIR.'/forum/editor/sp-text-editor.php';

		# fire action to indicate includes complete
		do_action('sph_forum_includes_complete');
	}

	/**
	 * This method runs code needed on all forum page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function startup() {
		# try to increase some php settings
		sp_php_overrides();

		# create the page view class
		$this->view = new spcView();

		# fire action to indicate startup complete
		do_action('sph_forum_startup_complete');
	}

	/**
	 * This method loads up the forum hooks needed on all forum page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function hooks() {
		# Set up Forum support WP Hooks

		if (SP()->core->device == 'mobile' || SP()->core->device == 'tablet') {
			add_action('template_redirect', 'sp_load_mobile_template', 10);
		}
		add_action('wp_head', 'sp_forum_header');
		add_action('wp_print_styles', 'sp_load_plugin_styles');
		add_action('wp_enqueue_scripts', 'sp_load_forum_scripts', 1);
		add_action('wp_enqueue_scripts', 'sp_load_forum_footer_scripts', 9999);
		add_action('wp_enqueue_scripts', 'sp_load_editor');
		add_action('wp_footer', 'sp_forum_footer');

		# load masonry ifprofile page view
		add_action('sph_scripts_end', 'sp_profile_masonry', 1, 2);

		# Page Content Level Display Filters
		add_filter('the_content', 'sp_render_forum', 1);

		# are we running wptexturize on post content?
		$texturize = SP()->options->get('spwptexturize');
		if (!$texturize) add_filter('run_wptexturize', '__return_false');

		# redirect for forum on front page
		add_filter('redirect_canonical', array(SP()->rewrites,
		                                       'front_page_redirect'));

		# profile display inspector
		add_filter('sph_ProfileShowHeader', 'sp_display_inspector_profile_popup', 1, 3);
		add_action('sph_profile_edit_after_tabs', 'sp_display_inspector_profile_edit');

		if (SP()->core->status == 'ok') {
			# Shortcodes
			add_shortcode('spoiler', array(SP()->displayFilters,
			                               'spoilers'));

			# WP Page Title
			add_action('loop_start', 'sp_title_hook');
			add_filter('the_title', 'sp_setup_page_title', 9999, 2); # Needs to stay consistent with hook remove/add functions below
			# while WP is processing nav mnenus, we need to disable our the_title hook processing
			# so lets remove at start of nav processing and add back in at the end
			add_filter('pre_wp_nav_menu', 'sp_title_hook_remove');
			add_filter('wp_nav_menu', 'sp_title_hook_add');

			# keep wp capital P stuff from making menus show full page title
			remove_filter('the_content', 'capital_P_dangit', 11);
			remove_filter('the_title', 'capital_P_dangit', 11);
			remove_filter('comment_text', 'capital_P_dangit', 31);

			# add filter for wp canonical url generation for forum pages since it would always point to the single wp page
			add_filter('get_canonical_url', 'sp_get_canonical_url', 10, 2);

			# browser title
			if (current_theme_supports('title-tag')) { # wp 4.4+ if themes support
				add_filter('pre_get_document_title', 'sp_browser_title', 999); # want it to run last
			} else {
				add_filter('wp_title', 'sp_setup_browser_title', 999, 3); # want it to run last
			}

			# WP List Pages Hack
			$sfwplistpages = SP()->options->get('sfwplistpages');
			if ($sfwplistpages) {
				add_filter('wp_list_pages', 'sp_wp_list_pages');
				add_filter('wp_nav_menu', 'sp_wp_list_pages');
			}

			# SOME OTHER WP PLUGIN SUPPORT
			# all in one seo pack plugin api
			add_filter('aioseop_canonical_url', 'sp_aioseo_canonical_url');
			add_filter('aioseop_description', 'sp_aioseo_description');
			add_filter('aioseop_keywords', 'sp_aioseo_keywords');
			add_filter('aioseop_home_page_title', 'sp_aioseo_homepage');

			# Open Graph Meta Tags
			add_filter('language_attributes', 'sp_og_namespace', 100);
			add_action('wp_head', 'sp_og_meta', 100);

			# handle wp seo (yoast)
			add_filter('template_redirect', 'sp_wp_seo_hooks', 999999);

			# fire action to indicate hooks complete
			do_action('sph_forum_hooks_complete');
		}
	}
}