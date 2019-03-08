<?php

/**
 * Class used for admin core loader.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * load()
 *
 * $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 * $Rev: 15817 $
 */
class spcAdminCoreLoader {
	/**
	 * This method kicks off the class loader and loads up things needed on all admin loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function load() {
		# Include admin core support functions early
		require_once SPBOOT.'admin/spa-admin-support-functions.php';

		# load up admin core constants
		$this->setup_constants();

		# load up admin core includes
		$this->includes();

		# run admin core startup code
		$this->startup();

		# run admin core hooks
		$this->hooks();

		do_action('sph_admin_core_load_complete');
	}

	/**
	 * This method loads up the admin core constants needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function setup_constants() {
		require_once SPBOOT.'admin/spa-admin-constants.php';

		# fire action to indicate constants complete
		do_action('sph_admin_core_constants_complete');
	}

	/**
	 * This method the required admin core files needed on all front end page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function includes() {
		if (!defined('SPHELP')) define('SPHELP', SP_PLUGIN_DIR.'/admin/help/');

		include_once SPBOOT.'admin/spa-admin-updater.php';
		include_once SPBOOT.'admin/spa-admin-menu.php';

		if (is_admin() && !wp_doing_ajax()) include_once SP_PLUGIN_DIR.'/forum/editor/sp-text-editor-filters.php';

		# admin only ajax actions
		include_once SPBOOT.'admin/spa-admin-ajax-actions.php';

		# fire action to indicate includes complete
		do_action('sph_admin_core_includes_complete');
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
		do_action('sph_admin_Core_startup_complete');
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
		add_action('admin_print_styles', 'spa_load_dashboard_css');
		add_action('admin_menu', 'spa_admin_menu');

		# Load spThisUser for admin side
		add_action('init', array(SP()->user,
		                         'get_current_user'), 1);

		# Do we need to nag about install or upgrade?
		if (SP()->core->status != 'ok') {
			add_action('admin_notices', 'sp_action_nag');
		}

		# Dashboard notifications
		add_action('wp_dashboard_setup', 'spa_dashboard_setup', 1);

		# WP admin access
		if (SP()->core->status == 'ok' && SP()->options->get('sfblockadmin')) {
			add_action('init', 'spa_block_admin', 2);
		}

		# Change forum permalink if needed
		add_action('permalink_structure_changed', 'spa_permalink_changed', 10, 2);

		if (SP()->core->status != 'Install') {
			add_action('save_post', 'spa_check_page_change', 1, 2);
		}

		# check sp version for upgrades
		if (is_main_site()) {
			# sp plugin checks
			add_action('update-core-custom_do-sp-plugin-upgrade', 'sp_update_plugins');
			add_action('update-custom_update-sp-plugins', 'sp_do_plugins_update');
			add_action('update-custom_upload-sp-plugin', 'sp_do_plugin_upload');

			# sp theme checks
			add_action('update-core-custom_do-sp-theme-upgrade', 'sp_update_themes');
			add_action('update-custom_update-sp-themes', 'sp_do_themes_update');
			add_action('update-custom_upload-sp-theme', 'sp_do_theme_upload');

			# add our plugin/theme updates into wp update coumt
			add_filter('wp_get_update_data', 'sp_update_wp_counts', 10, 2);
		}

		# Plugin page updating and links
		add_action('after_plugin_row_'.SP_FOLDER_NAME.'/sp-control.php', 'sp_plugins_check_sp_version');
		add_filter('network_admin_plugin_action_links', 'spa_add_plugin_action', 10, 2);
		add_filter('plugin_action_links', 'spa_add_plugin_action', 10, 2);
		add_action('admin_head', 'spa_check_removal');

		# Actiating, Deactivating and Uninstall
		add_action('activate_'.SP_FOLDER_NAME.'/sp-control.php', 'spa_activate_plugin');
		add_action('deactivate_'.SP_FOLDER_NAME.'/sp-control.php', 'spa_deactivate_plugin');

		# ------------------------------------------------------------------
		# spa_check_wp_plugin_page()
		# Used to load jquery dialog on the wp plugin page for out uninstall dialog
		# ------------------------------------------------------------------
		add_action('admin_enqueue_scripts', array($this,
		                                          'check_wp_plugin_page'));

		# fire action to indicate hooks complete
		do_action('sph_admin_core_hooks_complete');
		
		# fire action for edd update and license check function
		
		add_action('wp_dashboard_setup', 'spa_dashboard_addon_news_setup', 1);
		
		add_action('core_upgrade_preamble', 'spa_check_plugin_addon_update' );
		
		add_action('core_upgrade_preamble', 'spa_check_theme_addon_update' );
		
		add_filter( 'plugins_api', 'spa_addons_changelog', 10, 3 );
		
	}

	public function check_wp_plugin_page() {
		
		$screen = get_current_screen();
		if ($screen->id == 'plugins') {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-dialog');

			wp_enqueue_style('wp-jquery-ui-dialog');
		}
	}
}
