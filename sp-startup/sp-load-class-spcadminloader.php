<?php

/**
 * Class used for admin loader.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * load()
 *
 * $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 * $Rev: 15704 $
 */
class spcAdminLoader {
	/**
	 *
	 * @var string    wp admin page loaded
	 *
	 * @since 6.0
	 */
	public $adminPage = '';

	/**
	 * This method kicks off the admin loader and loads up things needed on all Simple Press admin pages.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function load() {
		# load up admin constants
		$this->setup_constants();

		# load up admin includes
		$this->includes();

		# run admin startup code
		$this->startup();

		# run admin hooks
		$this->hooks();

		do_action('sph_admin_load_complete');
	}

	/**
	 * This method loads up the admin constants needed on all simple press admin pages.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function setup_constants() {

		# fire action to indicate constants complete
		do_action('sph_admin_constants_complete');
	}

	/**
	 * This method the required site files needed on all simple press admin pages.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function includes() {
		# Include forum admin code files
		include_once SPBOOT.'admin/spa-admin-framework.php';
		include_once SP_PLUGIN_DIR.'/admin/library/spa-support.php';
		include_once SP_PLUGIN_DIR.'/forum/editor/sp-text-editor.php';
		include_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';

		# fire action to indicate includes complete
		do_action('sph_admin_includes_complete');
	}

	/**
	 * This method runs code needed on all simple press admin pages.
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

		# fire action to indicate startup complete
		do_action('sph_admin_startup_complete');
	}

	/**
	 * This method loads up the site hooks needed on all simple press admin pages.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function hooks() {
		# Load the forum admin CSS files
		add_action('admin_print_styles', 'spa_load_admin_css');

		# Set up Admin support WP Hooks
		add_action('admin_enqueue_scripts', 'spa_load_admin_scripts');
		add_action('admin_enqueue_scripts', 'spa_admin_footer_scripts');
		add_action('admin_head', 'spa_admin_header', 1);
		add_action('in_admin_footer', 'spa_admin_footer');

		# fire action to indicate hooks complete
		do_action('sph_admin_hooks_complete');
	}
}