<?php

/**
 * Core class used for base loader.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * load()
 *
 * $LastChangedDate: 2018-12-03 06:28:04 -0600 (Mon, 03 Dec 2018) $
 * $Rev: 15839 $
 */
class spcCoreLoader {
	/**
	 *
	 * @var array    class forum data (spGlobals)
	 *
	 * @since 6.0
	 */
	public $forumData = array();

	/**
	 *
	 * @var bool    mobile device viewing forum
	 *
	 * @since 6.0
	 */
	public $mobile = 0;

	/**
	 *
	 * @var string    type of device viewing forum
	 *
	 * @since 6.0
	 */
	public $device = 'desktop';

	/**
	 *
	 * @var string    Simple Press normal/install/upgrade status
	 *
	 * @since 6.0
	 */
	public $status = '';

	/**
	 * This method kicks off the class loader and loads up things needed on every page load.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function load() {
		# create class objects
		SP()->error          = new spcError();
		SP()->DB             = new spcDB();
		SP()->options        = new spcOptions();
		SP()->cache          = new spcCache();
		SP()->auths          = new spcAuths();
		SP()->activity       = new spcActivity();
		SP()->memberData     = new spcMemberData();
		SP()->meta           = new spcMeta();
		SP()->notifications  = new spcNotifications();
		SP()->user           = new spcUser();
		SP()->dateTime       = new spcDateTime();
		SP()->spPermalinks   = new spcPermalinks();
		SP()->displayFilters = new spcDisplayFilters();
		SP()->saveFilters    = new spcSaveFilters();
		SP()->editFilters    = new spcEditFilters();
		SP()->filters        = new spcFilters();
		SP()->primitives     = new spcPrimitives();
		SP()->rewrites       = new spcRewrites();
		SP()->theme          = new spcTheme();
		SP()->plugin         = new spcPlugin();
		SP()->profile        = new spcProfile();

		# load up core constants
		$this->setup_constants();

		# load up includes needed globally
		$this->includes();

		# run core startup code
		$this->startup();

		# run core hooks
		$this->hooks();

		do_action('sph_core_load_complete');
	}

	/**
	 * This method loads up the core constants needed on all page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function setup_constants() {

		add_filter( 'sp_default_storage_paths_data', [ $this, 'sp_default_storage_paths_data'], 10, 2 );

		# Include core support functions early
		require_once SPBOOT.'core/sp-core-support-functions.php';
		
		# Include 3rd party compatibility files
		require_once SPBOOT.'core/sp-core-compatibility.php';

		# set up paths data
		$storage = SP()->options->get('sfconfig');

		if( empty( $storage ) || !is_array( $storage ) ) {
			$storage = apply_filters( 'sp_default_storage_paths_data', array() );
		}
		SP()->plugin->storage = $storage;

		require_once SPBOOT.'core/sp-core-constants.php';

		# fire action to indicate constants complete
		do_action('sph_core_constants_complete');
	}

	function sp_default_storage_paths_data( $sfconfig, $is_install = false ) {
	
		SP()->define_install_path_constants();
		$perms		 = fileperms(INSTALL_STORE_DIR);
		$owners		 = stat(INSTALL_STORE_DIR);
		if ($perms === false) $perms		 = 0755;
	
		$basepath	 = 'sp-resources';
	
		# makes sure storage exists
		if (!file_exists(INSTALL_STORE_DIR.'/'.$basepath) && $is_install ) @mkdir(INSTALL_STORE_DIR.'/'.$basepath, $perms);

        if (is_multisite()) {
            global $wpdb;
            if (!defined('SPBLOGID')) define('SPBLOGID', $wpdb->blogid);
        }
	
		# for multisite, make sure main site storage exists
		if (is_multisite() && SPBLOGID != 1) {
			if (!file_exists(SP_STORE_DIR.'/uploads') && $is_install ) @mkdir(SP_STORE_DIR.'/uploads', $perms);
		}
	
		if( $is_install ) {
			# hive off the basepath for later use - use wp options
			add_option('sp_storage1', INSTALL_STORE_DIR.'/'.$basepath);
		}
	
		# Did it get created?
		$success = true;
		if( $is_install ) {
			if (!file_exists(INSTALL_STORE_DIR.'/'.$basepath)) $success = false;
			SP()->options->add('spStorageInstall1', $success);
		}
	
		# Is the ownership correct?
		if( $is_install ) {
			$ownersgood = false;
			if ($success) {
				$newowners = stat(INSTALL_STORE_DIR.'/'.$basepath);
				if ($newowners['uid'] == $owners['uid'] && $newowners['gid'] == $owners['gid']) {
					$ownersgood = true;
				} else {
					@chown(INSTALL_STORE_DIR.'/'.$basepath, $owners['uid']);
					@chgrp(INSTALL_STORE_DIR.'/'.$basepath, $owners['gid']);
					$newowners	 = stat(INSTALL_STORE_DIR.'/'.$basepath);
					if ($newowners['uid'] == $owners['uid'] && $newowners['gid'] == $owners['gid']) $ownersgood	 = true;
				}
			}
			SP()->options->add('spOwnersInstall1', $ownersgood);
		}
		$basepath .= '/';
	
		if (is_multisite()) {
			# multisite install - default paths are under the storage folder.
			# To set these we need to manipulate the INSTALL_STORE_DIR a bit to get the exact string we need.
			# The INSTALL_STORE_DIR looks like this: /var/www/simple-press.com/html/wp-content/uploads/sites/5.
			# We just want the /uploads/sites/5 portion of it.
			$wpc_position = strpos(INSTALL_STORE_DIR, 'wp-content');
			$wpc_portion = substr(INSTALL_STORE_DIR, $wpc_position);
			$wpc_portion = str_replace('wp-content', '', $wpc_portion);
			
			$sfconfig['avatars']		 = $wpc_portion.'/'.$basepath.'forum-avatars';
			$sfconfig['avatar-pool']	 = $wpc_portion.'/'.$basepath.'forum-avatar-pool';
			$sfconfig['smileys']		 = $wpc_portion.'/'.$basepath.'forum-smileys';
			$sfconfig['ranks']			 = $wpc_portion.'/'.$basepath.'forum-badges';
			$sfconfig['custom-icons']	 = $wpc_portion.'/'.$basepath.'forum-custom-icons';
			$sfconfig['cache']			 = $wpc_portion.'/'.$basepath.'forum-cache';
			$sfconfig['forum-images']	 = $wpc_portion.'/'.$basepath.'forum-feature-images';
			$sfconfig['iconsets']		 = $wpc_portion.'/'.$basepath.'forum-iconsets';				
		} else {
			# standard install - default paths are under wp-content/sp-resources
			$sfconfig['avatars']		 = $basepath.'forum-avatars';
			$sfconfig['avatar-pool']	 = $basepath.'forum-avatar-pool';
			$sfconfig['smileys']		 = $basepath.'forum-smileys';
			$sfconfig['ranks']			 = $basepath.'forum-badges';
			$sfconfig['custom-icons']	 = $basepath.'forum-custom-icons';
			$sfconfig['cache']			 = $basepath.'forum-cache';
			$sfconfig['forum-images']	 = $basepath.'forum-feature-images';
			$sfconfig['iconsets']		 = $basepath.'forum-iconsets';
		}
	
		if (is_multisite()) {
			if (SPBLOGID != 1) {
				switch_to_blog(1);
				$uploads		 = wp_get_upload_dir();
				$basepath		 = 'sp-resources';
				$already_created = (file_exists($uploads['basedir'].'/'.$basepath)) ? true : false;
	
				# if main site storage does not exist, try creating it
				# @TODO - not sure why we're doing this again - we already did it in the multsite section above.
				# The only difference in this code is that we're creating the folder using $uploads['basedir'].
				# But, the constant INSTALL_STORE_DIR used above is the same as $uploads['basedir'] in most cases.
				# So this is just duplicate work.
				# The only reason this duplicated code should exist is if the INSTALL_STORE_DIR constant is set in 
				# wp-config to something different than the $uploads['basedir'] value.
				if (!$already_created && $is_install ) {
					@mkdir($uploads['basedir'].'/'.$basepath, $perms);
					$success = (file_exists($uploads['basedir'].'/'.$basepath)) ? true : false;
				}
	
				# Is the ownership correct?
				$ownersgood = false;
				if ($already_created || $success) {
	
					if( $is_install ) {
						# @TODO: The folder check and creation in this block is also duplicated work.
						$newowners = stat($uploads['basedir'].'/'.$basepath);
						if ($newowners['uid'] == $owners['uid'] && $newowners['gid'] == $owners['gid']) {
							$ownersgood = true;
						} else {
							@chown($uploads['basedir'].'/'.$basepath, $owners['uid']);
							@chgrp($uploads['basedir'].'/'.$basepath, $owners['gid']);
							$newowners	 = stat($uploads['basedir'].'/'.$basepath);
							if ($newowners['uid'] == $owners['uid'] && $newowners['gid'] == $owners['gid']) $ownersgood	 = true;
						}
					}
	
					# the plugins, themes and language file are stored in /wp-content/uploads/sp-resources/ for a multisite installation.
					# @TODO - the weird thing here is that the folders being specified below are NOT the ones being tested for and created above.
					# So, the duplicated tests and folder creation in the section above needs to be fixed to match these folders.
					$basepath						 .= '/';
					$sfconfig['language-sp']		 = '/uploads'.'/'.$basepath.'forum-language/simple-press';
					$sfconfig['language-sp-plugins'] = '/uploads'.'/'.$basepath.'forum-language/sp-plugins';
					$sfconfig['language-sp-themes']	 = '/uploads'.'/'.$basepath.'forum-language/sp-themes';
					$sfconfig['plugins']			 = '/uploads'.'/'.$basepath.'forum-plugins';
					$sfconfig['themes']				 = '/uploads'.'/'.$basepath.'forum-themes';
				}
	
				restore_current_blog();
	
				if( $is_install ) {
					add_option('sp_storage2', ($already_created) ? 'multisite already done' : untrailingslashit($uploads['basedir'].'/'.$basepath));
					SP()->options->add('spStorageInstall2', $already_created || $success);
					SP()->options->add('spOwnersInstall2', $ownersgood);
				}
			} else {
				# the plugins, themes and language file are stored in /wp-content/sp-resources/ for site #1.
				$basepath						 = 'sp-resources/';
				$sfconfig['language-sp']		 = $basepath.'forum-language/simple-press';
				$sfconfig['language-sp-plugins'] = $basepath.'forum-language/sp-plugins';
				$sfconfig['language-sp-themes']	 = $basepath.'forum-language/sp-themes';
				$sfconfig['plugins']			 = $basepath.'forum-plugins';
				$sfconfig['themes']				 = $basepath.'forum-themes';
				if( $is_install ) {
					add_option('sp_storage2', get_option('sp_storage1'));
					SP()->options->add('spOwnersInstall2', true);
					SP()->options->add('spStorageInstall2', true);
				}
			}
		} else {
			if( $is_install ) {
				add_option('sp_storage2', get_option('sp_storage1'));
				SP()->options->add('spStorageInstall2', true);
				SP()->options->add('spOwnersInstall2', true);
			}
			
			# the plugins, themes and language file are stored in /wp-content/sp-resources/ for single site installation
			$sfconfig['language-sp']		 = $basepath.'forum-language/simple-press';
			$sfconfig['language-sp-plugins'] = $basepath.'forum-language/sp-plugins';
			$sfconfig['language-sp-themes']	 = $basepath.'forum-language/sp-themes';
			$sfconfig['plugins']			 = $basepath.'forum-plugins';
			$sfconfig['themes']				 = $basepath.'forum-themes';
		}
	
		return $sfconfig;
	}


	/**
	 * This method the required core files needed on all page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function includes() {
		# Include DB statistics
		require_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';
		# Include core decprecated function list
		include_once SPBOOT.'core/sp-core-deprecated.php';

		# Load core support code
		include_once SPBOOT.'core/sp-core-cron.php';
		include_once SPBOOT.'core/credentials/sp-credentials.php';

		$sfmail = SP()->options->get('sfnewusermail');
		if (isset($sfmail['sfusespfreg']) && $sfmail['sfusespfreg']) {
			include_once SPBOOT.'core/credentials/sp-new-user-email.php';
		}

		$sfrpx = SP()->options->get('sfrpx');
		if (isset($sfrpx['sfrpxenable']) && $sfrpx['sfrpxenable']) {
			include_once SPBOOT.'core/credentials/sp-rpx.php';
		}

		# common ajax actions
		require_once SPBOOT.'core/sp-core-ajax-actions.php';

		# forum only ajax actions - must be loaded always
		require_once SPBOOT.'forum/sp-forum-ajax-actions.php';

		# personal privacy export and remove
		include_once SP_PLUGIN_DIR.'/admin/library/spa-privacy.php';

		# include user functions file if exists (since 5.5.7)
		if (file_exists(WP_CONTENT_DIR.'/sp-user-functions.php')) {
			require_once WP_CONTENT_DIR.'/sp-user-functions.php';
		}

		# fire action to indicate includes complete
		do_action('sph_core_includes_complete');
	}

	/**
	 * This method runs core code needed on all page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function startup() {

		# Set th system status as soon as possible and init the globals
		sp_get_system_status();

		if (SP()->core->status != 'ok') {
			global $wpdb;
			$wpdb->hide_errors();
		}

		# check for mobile page load
		sp_mobile_check();

		# load up some forum data and auths cache
		if (SP()->core->status == 'ok') {
			sp_setup_forum_data();
			sp_build_site_auths_cache();
		}

		# Load template functions file if exsists
		if (SP()->core->status == 'ok') {
			# load theme spFunctions.php if it exists
			if (file_exists(SPTEMPLATES.'spFunctions.php')) {
				require_once SPTEMPLATES.'spFunctions.php';
			}

			# if child theme, load the parent spFunctions.php if it exists
			$curTheme = SP()->core->forumData['theme'];
			if (!empty($curTheme['parent']) && file_exists(SPTHEMEBASEDIR.$curTheme['parent'].'/templates/spFunctions.php')) {
				require_once SPTHEMEBASEDIR.$curTheme['parent'].'/templates/spFunctions.php';
			}

			do_action('sph_theme_functions_loaded');
		}

		# Load active plugins
		if (SP()->core->status == 'ok' || !SP()->isAdmin) {
			$sp_plugins = SP()->plugin->get_active();
			if ($sp_plugins) {
				foreach ($sp_plugins as $sp_plugin) {
					require_once $sp_plugin;
				}
				unset($sp_plugin);
			}

			# special call to allow plugins to filter forumData
			SP()->core->forumData = apply_filters('sph_load_page_data', SP()->core->forumData);

			do_action('sph_plugins_loaded');
		}

		# fire action to indicate startup complete
		do_action('sph_core_startup_complete');
	}

	/**
	 * This method loads up the core hooks needed on all page loads.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function hooks() {
		# Initialisation Routines
		add_action('init', 'sp_localisation', 4);

		# Rewrite Rules
		add_action('init', array(SP()->rewrites, 'check_flush'));
		add_filter('page_rewrite_rules', array(SP()->rewrites, 'rules'));
		add_filter('query_vars', array(SP()->rewrites, 'query_vars'));

		# Credential Actions/Filters
		if (SP()->core->status == 'ok') {
			add_action('login_redirect', 'sp_login_redirect', 10, 3);
			add_action('registration_redirect', 'sp_register_redirect');
			add_action('wp_logout', 'sp_logout_redirect');
			add_action('wp_login', 'sp_post_login_check');
		}

		# User registrations and logout
		add_action('register_form', 'spa_register_math', 50);
		add_filter('registration_errors', 'spa_register_error');

		# Keep track of logouts
		add_action('wp_login', 'sp_track_login');
		add_action('wp_logout', 'sp_track_logout');

		# RPX Support
		$sfrpx = SP()->options->get('sfrpx');
		if($sfrpx){
			if ($sfrpx['sfrpxenable']) {
				add_action('parse_request', 'sp_rpx_process_token');
				add_action('sph_login_head', 'sp_rpx_login_head');
				add_action('show_user_profile', 'sp_rpx_edit_user_page');
			}
}

		# Cron hooks
		if (SP()->core->status != 'Install') {
			add_action('sph_cron_user', 'sp_cron_remove_users');
			add_action('sph_transient_cleanup_cron', 'sp_cron_transient_cleanup');
			add_action('sph_stats_cron', 'sp_cron_generate_stats');
			add_action('sph_news_cron', 'sp_cron_check_news');
			add_action('cron_schedules', 'sp_cron_schedules');
			add_action('wp', 'sp_cron_scheduler');
			add_action('sph_check_addons_status_interval', 'sph_check_addons_status');
			add_action('wp_update_plugins', 'sph_check_for_addons_updates');
			add_action('wp_update_plugins', 'sp_check_for_updates');
		}

		# WP Avatar replacement - low priority - let everyone else settle out
		$sfavatars = SP()->options->get('sfavatars');
		if (!empty($sfavatars['sfavatarreplace'])) {
			add_filter('get_avatar', 'sp_wp_avatar', 900, 3);
			add_filter('default_avatar_select', 'spa_wp_discussion_avatar');
		}

		# User related hooks
		add_action('wpmu_new_user', array(SP()->user, 'create_data'), 99);
		add_action('wpmu_activate_user', array(SP()->user, 'create_data'), 99);
		add_action('added_existing_user', array(SP()->user, 'create_data'), 99);
		add_action('wpmu_delete_user', array(SP()->user, 'delete_data'));
		
		# This hook removed to fix github issue #121.
		# When a subsite is deleted, there's no reason to also delete data for that user for that site.
		# The way our delete_data function is written right now, it can't handle this call properly and 
		# really isn't needed.
		# The only reason to use this hook would be if we want to remove the user from the whole network.
		# But you would only want to do that if the user wasn't in use on any other site.
		# Given that a subsite can have thousands of users, we really don't want to evaluate
		# each user before deleting the site anyway because that can cause php timeouts.
		# If a user needs to be deleted, the admin should use our standard delete functions before 
		# deleting the subsite.
		# add_action('remove_user_from_blog', array(SP()->user, 'delete_data'), 10, 2);
		
		add_action('user_register', array(SP()->user, 'create_data'), 99);
		add_action('delete_user', array(SP()->user, 'delete_data'));
		add_action('profile_update', array(SP()->user, 'update_data'));
		add_action('set_user_role', array(SP()->user, 'set_role_to_ug'), 10, 3);
		add_action('add_user_role', array(SP()->user, 'add_role_to_ug'), 10, 2);
		add_action('remove_user_role', array(SP()->user, 'remove_role_to_ug'), 10, 2);
		add_action('delete_user_form', array(SP()->user, 'delete_form'), 10, 2);

		# Privacy data export and eraser
		add_filter('wp_privacy_personal_data_exporters', 'sp_register_profile_exporter', 20);
		add_filter('wp_privacy_personal_data_exporters', 'sp_register_forum_exporter', 21);
		add_filter('wp_privacy_personal_data_erasers', 'sp_register_forum_eraser', 10);
		
		add_filter('registration_errors', array(SP()->user, 'validate_registration'), 10, 3);
		add_action('user_profile_update_errors', array(SP()->user, 'validate_display_name'), 10, 3);

		# debug stuff
		add_action('admin_head', 'spdebug_admindev');
		add_action('wp_head', 'spdebug_styles');
		add_action('wp_footer', 'spdebug_stats');

        # Add Simple:Press admin bar
        add_action( 'admin_bar_menu', 'simple_press_admin_bar', 9999 );

		# fire action to indicate hooks complete
		do_action('sph_core_hooks_complete');
	}
}
