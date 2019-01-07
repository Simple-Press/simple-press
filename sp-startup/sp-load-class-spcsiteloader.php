<?php

/**
 * Class used for site loader.
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
class spcSiteLoader {
	/**
	 * This method kicks off the class loader and loads up things needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function load() {
		# Include site support functions early
		require_once SPBOOT.'site/sp-site-support-functions.php';

		# load up site constants
		$this->setup_constants();

		# load up site includes
		$this->includes();

		# run site startup code
		$this->startup();

		# run site hooks
		$this->hooks();

		do_action('sph_site_load_complete');
	}

	/**
	 * This method loads up the site constants needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function setup_constants() {
		require_once SPBOOT.'site/sp-site-constants.php';

		# fire action to indicate constants complete
		do_action('sph_site_constants_complete');
	}

	/**
	 * This method the required site files needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function includes() {
		# fire action to indicate includes complete
		do_action('sph_site_includes_complete');
	}

	/**
	 * This method runs code needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function startup() {

		# fire action to indicate startup complete
		do_action('sph_site_startup_complete');
	}

	/**
	 * This method loads up the site hooks needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function hooks() {
		# Get_permalink() filter for forum pages
		add_filter('page_link', 'sp_get_permalink', 10, 3);

		# Load blog script support
		add_action('wp_enqueue_scripts', 'sp_load_blog_script');

		# Load blog header support
		add_action('wp_head', 'sp_load_blog_support');

		# RSS feeds
		add_action('template_redirect', 'sp_feed', 2);
		add_filter('pre_get_posts', 'sp_is_feed_check');

		# fire action to indicate hooks complete
		do_action('sph_site_hooks_complete');
	}
}