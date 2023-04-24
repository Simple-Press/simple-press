<?php
/**
 * Admin menu functions
 * Loads for all forum admin pages to generate Simple Press menus
 *
 * $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 *
 * $Rev: 15817 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function displays the forum menu in the wp admin side menu.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_admin_menu() {
	global $sfadminpanels, $plugin_page, $submenu, $_wp_last_object_menu, $_registered_pages, $parent_file;

	if (SP()->core->status == 'ok') {
		# set up our default admin menu
		spa_setup_admin_menu();

		$adminparent = '';
		if (spa_can_access_admin_panels()) {
			# build our admin nav menu
			foreach ($sfadminpanels as $panel) {
				if ($panel['show_in_wp_menu'] && ((SP()->auths->current_user_can($panel['spf_capability'])) || ($panel['panel_name'] == 'Admins' && (SP()->user->thisUser->admin || SP()->user->thisUser->moderator)))) {
					if (empty($adminparent)) {
						$adminparent = $panel['admin_file'];
						$parent_file = $panel['admin_file']; # make sure wp knows the parent file (for non admin users)
						add_menu_page('Simple:Press', SP()->primitives->admin_text('Forum'), 'read', $adminparent, '', 'div', $_wp_last_object_menu + 1);
						add_submenu_page($adminparent, esc_attr($panel['panel_name']), esc_attr($panel['panel_name']), 'read', $panel['admin_file']);
					} else {
						add_submenu_page($adminparent, esc_attr($panel['panel_name']), esc_attr($panel['panel_name']), 'read', $panel['admin_file']);
					}
				}

				# we have to simulate sp plugins admin panel loaded so our plugins can use it as hookname for adding menu items
				# kind of hacky- will allow a user to manual type in our plugin panel url, but access will be denied by our permission check
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins'] = 1;
			}
		} else if (current_user_can('administrator')) {
			$adminparent = SP_FOLDER_NAME.'/sp-startup/admin/spa-admin-notice.php';
			add_menu_page('Simple:Press', SP()->primitives->admin_text('Forum'), 'manage_options', $adminparent, '', 'div', $_wp_last_object_menu + 1);
			add_submenu_page($adminparent, SP()->primitives->admin_text('WP Admin Notice'), SP()->primitives->admin_text('WP Admin Notice'), 'read', $adminparent);

			# hack for wp stubborness of not wanting singular submenu under a menu item
			if($plugin_page){
				if (strpos($plugin_page, SP_FOLDER_NAME) !== false) add_submenu_page($adminparent, '', '', 'read', $adminparent);
			}
			
			$submenu[$adminparent][1] = null;
		}
	} else {
		$adminparent = SPINSTALLPATH;
		add_menu_page('Simple:Press', SP()->primitives->admin_text('Forum'), 'activate_plugins', $adminparent, '', 'div', $_wp_last_object_menu + 1);

		if (SP()->core->status == 'Install') {
			if (current_user_can('activate_plugins')) {
				add_submenu_page($adminparent, SP()->primitives->admin_text('Install Simple:Press'), SP()->primitives->admin_text('Install Simple:Press'), 'activate_plugins', $adminparent);
			} else {
				add_submenu_page($adminparent, SP()->primitives->admin_text('Needs Installing'), SP()->primitives->admin_text('Needs Installing'), 'read', '');
			}
		} else {
			if (current_user_can('update_plugins')) {
				add_submenu_page($adminparent, SP()->primitives->admin_text('Upgrade Simple:Press'), SP()->primitives->admin_text('Upgrade Simple:Press'), 'update_plugins', $adminparent);

				# lets allow our admin panel hookname so we dont get wp error and our redirect will work
				# kind of hacky- will allow a user to manual type in these plugin panels for url, but access will be denied by our permission check
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-forums/spa-forums'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-options/spa-options'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-components/spa-components'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-usergroups/spa-usergroups'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-permissions/spa-plugins'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-integration/spa-integration'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-profiles/spa-profiles'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-admins/spa-admins'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-users/spa-users'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-themes/spa-themes'] = 1;
				$_registered_pages['admin_page_'.SP_FOLDER_NAME.'/admin/panel-toolbox/spa-toolbox'] = 1;
			} else {
				add_submenu_page($adminparent, SP()->primitives->admin_text('Needs Upgrading'), SP()->primitives->admin_text('Needs Upgrading'), 'read', '');
			}
		}

		# hack for wp stubborness of not wanting singular submenu under a menu item
		if($plugin_page){
			if (strpos($plugin_page, SP_FOLDER_NAME) !== false) {
                add_submenu_page($adminparent, '', '', 'read', 'hidden-menu');
            }
		}
	}

	# let plugins add new wp admin nav panels
	do_action('sph_admin_menu', $adminparent);
}

/**
 * This function check permission for users to view the Simple Press admin panels.
 *
 * @since 6.0
 *
 * @return bool
 */
function spa_can_access_admin_panels() {
	global $sfadminpanels;

	foreach ($sfadminpanels as $panel) {
		if (SP()->auths->current_user_can($panel['spf_capability']) || ($panel['panel_name'] === 'Admins' && (SP()->user->thisUser->admin || SP()->user->thisUser->moderator))) return true;
	}
	return false;
}

/**
 * This function initializes the forum admin menu to be displayed.
 * Plugins are able to add their menus to this initialization.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_setup_admin_menu() {
	global $sfadminpanels, $sfactivepanels, $sfatooltips;

	# Get correct tooltips file
	$lang = spa_get_language_code();
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-menu-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-menu-tips-en.php';
	if (file_exists($ttpath)) require_once $ttpath;

	$sfadminpanels = $sfactivepanels = [];

	/**
	 * admin panel array elements
	 * 0 - panel name
	 * 1 - spf capability to view
	 * 2 - admin file
	 * 3 - tool tip
	 * 4 - icon
	 * 5 - loader function
	 * 6 - subpanels
	 * 7 - display in wp admin left side menu (should be false for user plugins)
	 */
	$forms = array(
		SP()->primitives->admin_text('Manage Groups And Forums')	 => array(
			'forums' => 'sfreloadfb'),
		SP()->primitives->admin_text('Order Groups and Forums')		 => array(
			'ordering' => 'sfreloadfo'),
		SP()->primitives->admin_text('Create New Group')			 => array(
			'creategroup' => ''),
		SP()->primitives->admin_text('Create New Forum')			 => array(
			'createforum' => ''),
		SP()->primitives->admin_text('Custom Icons')				 => array(
			'customicons' => 'sfreloadci'),
		SP()->primitives->admin_text('Featured Images')				 => array(
			'featuredimages' => 'sfreloadfi'),
		SP()->primitives->admin_text('Add Global Permission Set')	 => array(
			'globalperm' => ''),
		SP()->primitives->admin_text('Delete All Permission Sets')	 => array(
			'removeperms' => ''),
		SP()->primitives->admin_text('Merge Forums')				 => array(
			'mergeforums' => 'sfreloadmf'),
		SP()->primitives->admin_text('Global RSS Settings')			 => array(
			'globalrss' => 'sfreloadfd'));
	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Forums'),
		'spf_capability'  => 'SPF Manage Forums',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-forums/spa-forums.php',
		'tool_tip'        => $sfatooltips['forums'],
		'icon'            => 'forums',
		'loader_function' => wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];

	$sfactivepanels['forums'] = 0;

	$forms = array(
		SP()->primitives->admin_text('Global Settings')			 => array(
			'global' => 'sfreloadog'),
		SP()->primitives->admin_text('General Display Settings') => array(
			'display' => ''),
		SP()->primitives->admin_text('Content Settings')		 => array(
			'content' => ''),
		SP()->primitives->admin_text('Member Settings')			 => array(
			'members' => 'sfreloadms'),
		SP()->primitives->admin_text('Email Settings')			 => array(
			'email' => ''),
		SP()->primitives->admin_text('New Post Handling')		 => array(
			'newposts' => ''),
		SP()->primitives->admin_text('Icon Sets')		 => array(
			'iconsets' => '')

			);
    /**
     * admin panel array elements
     * 0 - panel name
     * 1 - spf capability to view
     * 2 - admin file
     * 3 - tool tip
     * 4 - icon
     * 5 - loader function
     * 6 - subpanels
     * 7 - display in wp admin left side menu (should be false for user plugins)
     */
    /*
     * $sfadminpanels[] = array(
		SP()->primitives->admin_text('Options'),
		'SPF Manage Options',
		SP_FOLDER_NAME.'/admin/panel-options/spa-options.php',
		$sfatooltips['options'],
		'options',
		wp_nonce_url(SPAJAXURL.'options-loader', 'options-loader'),
		$forms,
		true);
     */
	$sfadminpanels[] = array(
		'panel_name'      => SP()->primitives->admin_text('Options'),
		'spf_capability'  => 'SPF Manage Options',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-options/spa-options.php',
		'tool_tip'        => $sfatooltips['options'],
		'icon'            => 'options',
		'loader_function' => wp_nonce_url(SPAJAXURL.'options-loader', 'options-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    );
	$sfactivepanels['options'] = 1;

	$forms = array(
		SP()->primitives->admin_text('Smileys')					 => array(
			'smileys' => 'sfreloadsm'),
		SP()->primitives->admin_text('Login And Registration')	 => array(
			'login' => ''),
		SP()->primitives->admin_text('SEO')						 => array(
			'seo' => 'sfreloadse'),
		SP()->primitives->admin_text('Forum Ranks')				 => array(
			'forumranks' => 'sfreloadfr'),
		SP()->primitives->admin_text('Custom Messages')			 => array(
			'messages' => ''));
	$sfadminpanels[] = array(
		'panel_name'      => SP()->primitives->admin_text('Components'),
		'spf_capability'  => 'SPF Manage Components',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-components/spa-components.php',
		'tool_tip'        => $sfatooltips['components'],
		'icon'            => 'components',
		'loader_function' => wp_nonce_url(SPAJAXURL.'components-loader', 'components-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true
    );
	$sfactivepanels['components'] = 2;

	$forms = array(
		SP()->primitives->admin_text('Manage User Groups')		 => array(
			'usergroups' => 'sfreloadub'),
		SP()->primitives->admin_text('Create New User Group')	 => array(
			'createusergroup' => ''),
		SP()->primitives->admin_text('Map Users to User Group')	 => array(
			'mapusers' => 'sfreloadmu'));
	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('User Groups'),
		'spf_capability'  => 'SPF Manage User Groups',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-usergroups/spa-usergroups.php',
		'tool_tip'        => $sfatooltips['usergroups'],
		'icon'            => 'user-groups',
		'loader_function' => wp_nonce_url(SPAJAXURL.'usergroups-loader', 'usergroups-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['usergroups'] = 3;

	$forms = array(
		SP()->primitives->admin_text('Manage Permissions Sets')	 => array(
			'permissions' => 'sfreloadpb'),
		SP()->primitives->admin_text('Add New Permission Set')	 => array(
			'createperm' => ''),
		SP()->primitives->admin_text('Reset Permissions')		 => array(
			'resetperms' => ''),
		SP()->primitives->admin_text('Add New Authorization')	 => array(
			'newauth' => ''));
	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Permissions'),
		'spf_capability'  => 'SPF Manage Permissions',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-permissions/spa-permissions.php',
		'tool_tip'        => $sfatooltips['permissions'],
		'icon'            => 'permissions',
		'loader_function' => wp_nonce_url(SPAJAXURL.'permissions-loader', 'permissions-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['permissions'] = 4;

	$forms = array(
		SP()->primitives->admin_text('Page and Permalink')		 => array(
			'page' => 'sfreloadpp'),
		SP()->primitives->admin_text('Storage Locations')		 => array(
			'storage' => 'sfreloadsl'),
		SP()->primitives->admin_text('Language Translations')	 => array(
			'language' => 'sfreloadla'));

		# Remove some items not needed when running as an saas
		if (spa_saas_check()) {
			unset($forms[SP()->primitives->admin_text('Storage Locations')]);
		}

	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Integration'),
		'spf_capability'  => 'SPF Manage Integration',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-integration/spa-integration.php',
		'tool_tip'        => $sfatooltips['integration'],
		'icon'            => 'integration',
		'loader_function' => wp_nonce_url(SPAJAXURL.'integration-loader', 'integration-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['integration'] = 5;

	$forms = [
		SP()->primitives->admin_text('Profile Options')		 => [
			'options' => ''
        ],
		SP()->primitives->admin_text('Profile Tabs & Menus') => [
			'tabsmenus' => 'sfreloadptm'
        ],
		SP()->primitives->admin_text('Avatar Options')		 => [
			'avatars' => 'sfreloadav'
        ],
		SP()->primitives->admin_text('Avatar Pool')			 => [
			'pool' => 'sfreloadpool'
        ]
    ];
	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Profiles'),
		'spf_capability'  => 'SPF Manage Profiles',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-profiles/spa-profiles.php',
		'tool_tip'        => $sfatooltips['profiles'],
		'icon'            => 'profiles',
		'loader_function' => wp_nonce_url(SPAJAXURL.'profiles-loader', 'profiles-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['profiles'] = 6;

	if (SP()->auths->current_user_can('SPF Manage Admins')) {
		$forms = array(
			SP()->primitives->admin_text('Your Admin Options')	 => array(
				'youradmin' => 'sfreloadao'),
			SP()->primitives->admin_text('Global Admin Options') => array(
				'globaladmin' => ''),
			SP()->primitives->admin_text('Manage Admins')		 => array(
				'manageadmin' => 'sfreloadma'));
	} else {
		$forms = array(
			SP()->primitives->admin_text('Your Admin Options') => array(
				'youradmin' => 'sfreloadao'));
	}
	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Admins'),
		'spf_capability'  => 'SPF Manage Admins',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-admins/spa-admins.php',
		'tool_tip'        => $sfatooltips['admins'],
		'icon'            => 'admins',
		'loader_function' => wp_nonce_url(SPAJAXURL.'admins-loader', 'admins-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['admins'] = 7;

	$forms = array(
		SP()->primitives->admin_text('Member Information') => array(
			'member-info' => ''));
	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Users'),
		'spf_capability'  => 'SPF Manage Users',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-users/spa-users.php',
		'tool_tip'        => $sfatooltips['users'],
		'icon'            => 'users',
		'loader_function' => wp_nonce_url(SPAJAXURL.'users-loader', 'users-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true
    ];
	$sfactivepanels['users'] = 8;

	$forms = array(
		SP()->primitives->admin_text('Available Plugins') => array(
			'plugin-list' => 'sfreloadpl'));
	if (!is_multisite() || is_super_admin()) $forms[SP()->primitives->admin_text('Plugin Uploader')] = array(
			'plugin-upload' => '');

	# Remove some items not needed when running as an saas
	if (spa_saas_check()) {
		unset($forms[SP()->primitives->admin_text('Plugin Uploader')]);
	}

	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Plugins'),
		'spf_capability'  => 'SPF Manage Plugins',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins.php',
		'tool_tip'        => $sfatooltips['plugins'],
		'icon'            => 'plugins',
		'loader_function' => wp_nonce_url(SPAJAXURL.'plugins-loader', 'plugins-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['plugins'] = 9;

	$forms = array(
		SP()->primitives->admin_text('Available Themes')	 => array(
			'theme-list' => 'sfreloadtlist'),
		SP()->primitives->admin_text('Mobile Phone Theme')	 => array(
			'mobile' => 'sfreloadmlist'),
		SP()->primitives->admin_text('Mobile Tablet Theme')	 => array(
			'tablet' => 'sfreloadtablist'));
	if (!is_multisite() || is_super_admin()) {
		# Only allow the theme editor if defined in the wp-config.php file.
		# Note that allowing this option allows the admin to write to ANY file in the wp installation folder.
		# And, if the web server or php configuration is insecure, potentially write to any file on the server.
		if (defined('SP_ALLOW_THEME_EDITOR') && SP_ALLOW_THEME_EDITOR) {
			$forms[SP()->primitives->admin_text('Theme Editor')] = array(
				'editor' => 'sfreloadttedit');
		}
		$forms[SP()->primitives->admin_text('Custom CSS')] = array(
			'css' => 'sfreloadcss');
		$forms[SP()->primitives->admin_text('Theme Uploader')] = array(
			'theme-upload' => '');
	}

	# Remove some items not needed when running as an saas
	if (spa_saas_check()) {
		unset($forms[SP()->primitives->admin_text('Mobile Phone Theme')]);
	}
	if (spa_saas_check()) {
		unset($forms[SP()->primitives->admin_text('Mobile Tablet Theme')]);
	}
	if (spa_saas_check()) {
		# The theme editor menu option is only allowed sometimes so check to make sure it's even in the array before attempting to unset it.
		if (isset($forms[SP()->primitives->admin_text('Theme Editor')])) {
			unset($forms[SP()->primitives->admin_text('Theme Editor')]);
		}
	}
	if (spa_saas_check()) {
		unset($forms[SP()->primitives->admin_text('Theme Uploader')]);
	}

	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Themes'),
		'spf_capability'  => 'SPF Manage Themes',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-themes/spa-themes.php',
		'tool_tip'        => $sfatooltips['themes'],
		'icon'            => 'themes',
		'loader_function' => wp_nonce_url(SPAJAXURL.'themes-loader', 'themes-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['themes'] = 10;

	$forms = array(
		SP()->primitives->admin_text('Toolbox')			 => array(
			'toolbox' => ''),
		SP()->primitives->admin_text('Licensing')		 => array(
			'licensing' => ''),
		SP()->primitives->admin_text('Housekeeping')	 => array(
			'housekeeping' => 'sfreloadhk'),
		SP()->primitives->admin_text('Data Inspector')	 => array(
			'inspector' => ''),
		SP()->primitives->admin_text('CRON Inspector')	 => array(
			'cron' => 'sfcron'),
		SP()->primitives->admin_text('Error Log')		 => array(
			'errorlog' => 'sfreloadel'),
		SP()->primitives->admin_text('Environment')		 => array(
			'environment' => ''),
		SP()->primitives->admin_text('Install Log')		 => array(
			'log' => ''),
		SP()->primitives->admin_text('Change Log')		 => array(
			'changelog' => ''),
		SP()->primitives->admin_text('Uninstall')		 => array(
			'uninstall' => ''));

		# Remove some items not needed when running as an saas
		if (spa_saas_check()) {
			unset($forms[SP()->primitives->admin_text('Toolbox')]);
			unset($forms[SP()->primitives->admin_text('Licensing')]);
			unset($forms[SP()->primitives->admin_text('Data Inspector')]);
			unset($forms[SP()->primitives->admin_text('CRON Inspector')]);
			unset($forms[SP()->primitives->admin_text('Error Log')]);
			unset($forms[SP()->primitives->admin_text('Environment')]);
			unset($forms[SP()->primitives->admin_text('Install Log')]);
			unset($forms[SP()->primitives->admin_text('Change Log')]);
			unset($forms[SP()->primitives->admin_text('Uninstall')]);
		}

	$sfadminpanels[] = [
		'panel_name'      => SP()->primitives->admin_text('Toolbox'),
		'spf_capability'  => 'SPF Manage Toolbox',
		'admin_file'      => SP_FOLDER_NAME.'/admin/panel-toolbox/spa-toolbox.php',
		'tool_tip'        => $sfatooltips['toolbox'],
		'icon'            => 'toolbox',
		'loader_function' => wp_nonce_url(SPAJAXURL.'toolbox-loader', 'toolbox-loader'),
		'subpanels'       => $forms,
		'show_in_wp_menu' => true,
        'core'            => true,
    ];
	$sfactivepanels['toolbox'] = 11;

	# allow plugins to alter the admin menus
	$sfadminpanels = apply_filters('sf_admin_panels', $sfadminpanels);
	$sfactivepanels = apply_filters('sf_admin_activepanels', $sfactivepanels);

	# allow plugins to alter the admin menus after promotions item if they really really want to do so!
	$sfadminpanels = apply_filters('sf_admin_panels_after_promo', $sfadminpanels);
	$sfactivepanels = apply_filters('sf_admin_activepanels_after_promo', $sfactivepanels);
}


add_action( 'admin_enqueue_scripts', 'spa_enqueue_menu_style' );

/**
 * Add css for forum menu icon
 */
function spa_enqueue_menu_style() {
	$spAdminMenuUrl = SPADMINCSS.'spa-menu.css';
	wp_register_style('spAdminMenu', $spAdminMenuUrl, array(), SP_SCRIPTS_VERSION);
	wp_enqueue_style('spAdminMenu');
}