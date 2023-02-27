<?php
/**
 * @package   Simple:Press
 * @author    The Simple:Press Team <contact@simple-press.com>
 * @license   GPL-2.0+
 * @link       https://simple-press.com
 * @copyright 2006-2018 Simple:Press
 *
 * @wordpress-plugin
 * Plugin Name: 		Simple:Press
 * Plugin URI: 			https://simple-press.com
 * Version: 			6.8.9
 * Description: 		The most versatile and feature-rich forums plugin for WordPress
 * Author: 				The Simple:Press Team
 * Author URI: 			https://simple-press.com/about
 * Text Domain: 		sp
 * License:				GPL-2.0+
 * License URI:			http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:			/languages
 * WordPress Versions:	4.9 and above
 *
 * For full acknowledgments click on the copyright/version strip at the bottom of forum pages.
 *
 * $LastChangedDate: 2020-07-24 19:11:16 -0600 (Sun, 16 Dec 2018) $
 * $Rev: 15869 $
 *
 */
if (!class_exists('spcSimplePress')) {
	/**
	 * Main Simple Press Class.
	 * Singleton Class
	 *
	 * @since 6.0
	 */
	final class spcSimplePress {
		/**
		 *
		 * @var self    Simple Press main instance
		 *
		 * @since 6.0
		 */
		private static $instance;

		/**
		 * Below are SP class objects
		 */

		/**
		 *
		 * @var object spCore    core loader class object
		 *
		 * @since 6.0
		 */
		public $core;

		/**
		 *
		 * @var object spSite    site loader class object
		 *
		 * @since 6.0
		 */
		public $site;

		/**
		 *
		 * @var object spForum    forum loader class object
		 *
		 * @since 6.0
		 */
		public $forum;

		/**
		 *
		 * @var object spAdminCore    admin core loader class object
		 *
		 * @since 6.0
		 */
		public $adminCore;

		/**
		 *
		 * @var object spAdmin    admin loader class object
		 *
		 * @since 6.0
		 */
		public $admin;

		/**
		 *
		 * @var object spError class object
		 *
		 * @since 6.0
		 */
		public $error;

		/**
		 *
		 * @var object spOptions class object
		 *
		 * @since 6.0
		 */
		public $options;

		/**
		 *
		 * @var object    spcDB class object
		 *
		 * @since 6.0
		 */
		public $DB;

		/**
		 *
		 * @var object    spAuths class object
		 *
		 * @since 6.0
		 */
		public $auths;

		/**
		 *
		 * @var object    spActivity class object
		 *
		 * @since 6.0
		 */
		public $activity;

		/**
		 *
		 * @var object    spCache class object
		 *
		 * @since 6.0
		 */
		public $cache;

		/**
		 *
		 * @var object    spMemberData class object
		 *
		 * @since 6.0
		 */
		public $memberData;

		/**
		 *
		 * @var object    spMeta class object
		 *
		 * @since 6.0
		 */
		public $meta;

		/**
		 * @var object    spNotifications class object
		 *
		 * @since 6.0
		 */
		public $notifications;

		/**
		 * @var object    spUser class object
		 *
		 * @since 6.0
		 */
		public $user;

		/**
		 * @var object    spDateTime class object
		 *
		 * @since 6.0
		 */
		public $dateTime;

		/**
		 * @var object    spFilters class object
		 *
		 * @since 6.0
		 */
		public $filters;

		/**
		 * @var object    spDisplayFilters class object
		 *
		 * @since 6.0
		 */
		public $displayFilters;

		/**
		 * @var object    spSaveFilters class object
		 *
		 * @since 6.0
		 */
		public $saveFilters;

		/**
		 * @var object    spEditFilters class object
		 *
		 * @since 6.0
		 */
		public $editFilters;

		/**
		 * @var object    spPrimitives class object
		 *
		 * @since 6.0
		 */
		public $primitives;

		/**
		 * @var object    spTheme class object
		 *
		 * @since 6.0
		 */
		public $theme;

		/**
		 * @var object    spPlugin class object
		 *
		 * @since 6.0
		 */
		public $plugin;

		/**
		 * @var object    spProfile class object
		 *
		 * @since 6.0
		 */
		public $profile;

		/**
		 * @var object    spRewrites class object
		 *
		 * @since 6.0
		 */
		public $rewrites;

		/**
		 * Below are SP class variables
		 */

		/**
		 *
		 * @var bool    WP admin page to be loaded
		 *
		 * @since 6.0
		 */
		public $isAdmin = false;

		/**
		 *
		 * @var bool    Simple Press admin page to be loaded
		 *
		 * @since 6.0
		 */
		public $isForumAdmin = false;

		/**
		 *
		 * @var bool    WP is loading a SimplePress page
		 *
		 * @since 6.0
		 */
		public $isForum = false;

		/**
		 * Main SimplePress Instance.
		 *
		 * Ensures that only one instance of SimplePress exists in memory at any one
		 * time. Also prevents needing to global namespace.
		 *
		 * @since 6.0
		 *
		 * @static
		 *
		 * @access public
		 *
		 * @return self SimplePress     The one SimplePress Instance.
		 */
		public static function instance() {
			# make sure the SimplePress instance doesn't already exist
			if (!isset(self::$instance) && !(self::$instance instanceof spcSimplePress)) {
				# create the SimplePress instance
				self::$instance = new spcSimplePress;

				# set up some constants to work with
				self::$instance->setup_constants();

				# include some files containing needed class definitions
				self::$instance->includes();

				# begin the forum startup sequence
				self::$instance->startup();
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 6.0
		 *
		 * @access protected
		 *
		 * @return void
		 */
		public function __clone() {
			# Cloning instances of the class is forbidden.
			_doing_it_wrong(__FUNCTION__, SP()->primitives->front_text('Cloning SimplePress class not allowed'), '6.0');
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 6.0
		 *
		 * @access protected
		 *
		 * @return void
		 */
		public function __wakeup() {
			# Unserializing instances of the class is forbidden.
			_doing_it_wrong(__FUNCTION__, SP()->primitives->front_text('Unserializing SimplePress class not allowed'), '6.0');
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 *
		 * @since 6.0
		 *
		 * @return void
		 */
		private function setup_constants() {
			# define required WP version - value needs manual updating if required
			define('SP_WP_VER', '4.9');

			# version and system control constants
			define('SPPLUGNAME', 'Simple:Press');
			define('SPVERSION', '6.8.9');
                        
			# Define a variable that can be used for versioning scripts - required to force multisite to use different version numbers for each site.
			if ( is_multisite() ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					define( 'SP_SCRIPTS_VERSION', (string) get_current_blog_id() . '_' . (string) time() );
				} else {
					define( 'SP_SCRIPTS_VERSION', (string) get_current_blog_id() . '_' . (string) SPVERSION );
				}
			} else {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					define( 'SP_SCRIPTS_VERSION', (string) time() );
				} else {
					define( 'SP_SCRIPTS_VERSION', (string) SPVERSION );
				}
			}			
                        
			define('SPBUILD', 15886);
			define('SPSILENT', 15882);
			define('SPRELEASE', 'Release');

			define('SPPLUGHOME', '<a class="spLink" href="https://simple-press.com" target="_blank">Simple:Press</a>');
			define('SPHOMESITE', 'https://simple-press.com');

			# Define startup constants
			# IMPORTANT - SPHOMEURL is always slashed! check user_trailingslashit()) if using standalone (ie no args)
			# IMPORTANT - This is NOT the same as what wp refers to as home url. This is actually URL to the WP files. Changing to be consistent ripples through everything.
			$home = trailingslashit(site_url());
			define('SPHOMEURL', $home);

			# IMPORTANT - SPSITEURL is always slashed! check user_trailingslashit()) if using standalone (ie no args)
			# IMPORTANT - This is NOT the same as what wp refers to as site url. This is actually to the site home URL. Changing to be consistent ripples through everything.
			$site = trailingslashit(home_url());
			define('SPSITEURL', $site);

			# SPALTURL is used to convert links in legacy posts where the scheme may have changed
			$altURL = (is_ssl()) ? str_replace('https://', 'http://', SPHOMEURL) : str_replace('http://', 'https://', SPHOMEURL);
			define('SPALTURL', $altURL);

			define('SP_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.basename(dirname(__file__)));
			define('SP_PLUGIN_URL', plugins_url().'/'.basename(dirname(__file__)));
			define('SP_FOLDER_NAME', basename(__DIR__));
			define('SPBOOT', dirname(__file__).'/sp-startup/');
			define('SPAPI', dirname(__file__).'/sp-api/');
			define('SPINSTALLPATH', SP_FOLDER_NAME.'/sp-startup/sp-load-install.php');

			# Set the table prefix that can be overridden in wp-config.sys
			global $wpdb;
			define('SP_PREFIX', $wpdb->prefix);

			# Define table constants
			define('SPGROUPS', SP_PREFIX.'sfgroups');
			define('SPFORUMS', SP_PREFIX.'sfforums');
			define('SPTOPICS', SP_PREFIX.'sftopics');
			define('SPPOSTS', SP_PREFIX.'sfposts');
			define('SPTRACK', SP_PREFIX.'sftrack');
			define('SPUSERGROUPS', SP_PREFIX.'sfusergroups');
			define('SPPERMISSIONS', SP_PREFIX.'sfpermissions');
			define('SPDEFPERMISSIONS', SP_PREFIX.'sfdefpermissions');
			define('SPROLES', SP_PREFIX.'sfroles');
			define('SPMEMBERS', SP_PREFIX.'sfmembers');
			define('SPMEMBERSHIPS', SP_PREFIX.'sfmemberships');
			define('SPMETA', SP_PREFIX.'sfmeta');
			define('SPLOG', SP_PREFIX.'sflog');
			define('SPLOGMETA', SP_PREFIX.'sflogmeta');
			define('SPOPTIONS', SP_PREFIX.'sfoptions');
			define('SPERRORLOG', SP_PREFIX.'sferrorlog');
			define('SPAUTHS', SP_PREFIX.'sfauths');
			define('SPAUTHCATS', SP_PREFIX.'sfauthcats');
			define('SPWAITING', SP_PREFIX.'sfwaiting');
			define('SPNOTICES', SP_PREFIX.'sfnotices');
			define('SPSPECIALRANKS', SP_PREFIX.'sfspecialranks');
			define('SPUSERACTIVITYTYPE', SP_PREFIX.'sfuseractivitytype');
			define('SPUSERACTIVITY', SP_PREFIX.'sfuseractivity');
			define('SPCACHE', SP_PREFIX.'sfcache');
			define('SPADMINKEYWORDS', SP_PREFIX.'sfadminkeywords');
			define('SPADMINTASKS', SP_PREFIX.'sfadmintasks');

			# Success/Failure
			if (!defined('SPSUCCESS')) define('SPSUCCESS', 0);
			if (!defined('SPFAILURE')) define('SPFAILURE', 1);
			if (!defined('SPWAIT')) define('SPWAIT', 2);

			# fire action to indicate constants complete
			do_action('sph_constants_complete');
		}

		/**
		 * Include required files early on for creating some class objects.
		 *
		 * @access private
		 *
		 * @since 6.0
		 *
		 * @return void
		 */
		private function includes() {
			# run our autoloader
			require_once SPBOOT.'core/sp-core-autoloader.php';
			sp_autoloader();

			# lets be able to debug anywhere
			require_once SPBOOT.'core/sp-core-debug.php';

			# load the ajax processing support functions
			require_once SPBOOT.'core/sp-core-ajax.php';

			# fire action to indicate startup complete
			do_action('sph_includes_complete');
		}

		/**
		 * Include required files and start up the forum plugin logic
		 *
		 * @access private
		 *
		 * @since 6.0
		 *
		 * @return void
		 */
		private function startup() {
			# set up a couple flags if this is admin page load (flag needed for core loader)
			if (is_admin() && !sp_is_frontend_ajax()) {
				$this->isAdmin = true;

				# is it an SP admin page load
				if ((isset($_GET['page'])) && (stristr($_GET['page'], SP_FOLDER_NAME)) !== false) {
					$this->isForumAdmin = true;
				}
			}

			# Fire up core loader
			$this->core = new spcCoreLoader();
			$this->core->load();

			# Load up admin boot files if an admin session
			if ($this->isAdmin) {
				# Fire up admin core loader
				$this->adminCore = new spcAdminCoreLoader();
				$this->adminCore->load();

				if ($this->isForumAdmin) {
					# Fire up admin loader
					$this->admin = new spcAdminLoader();
					$this->admin->load();
				}
			} else {
				# Fire up site loader
				$this->site = new spcSiteLoader();
				$this->site->load();
			}

			# Finally wait to find out if this is a forum page being requested
			if ($this->isAdmin == false) {
				add_action('wp', array($this,
				                       'check_is_forum_page'));
			}

			# fire action to indicate startup complete
			do_action('sph_startup_complete');
		}

		/**
		 * Determines if the wp page the forum is displayed on is being shown
		 * This is a callback from a wp hook on 'wp' in order to wait for wp to load.
		 *
		 * @access public
		 *
		 * @since 6.0
		 *
		 * @return void
		 */
		public function check_is_forum_page() {
			global $wp_query;
			if ((is_page()) && ($wp_query->post->ID == $this->options->get('sfpage'))) {
				$this->isForum = true;
				SP()->user->get_current_user();

				# Fire up forum loader
				$this->forum = new spcForumLoader();
				$this->forum->load();
			}
		}
	}
}

/**
 * The main function that returns the SimplePress instance.
 *
 * The main function responsible for returning the one true SimplePress
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 6.0
 *
 * @return spcSimplePress SimplePress     The one SimplePress Instance.
 */
function SP() {
	return spcSimplePress::instance();
}

# Get the SimplePress instance Running
SP();
