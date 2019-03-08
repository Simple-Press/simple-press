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
				if ($panel[7] && ((SP()->auths->current_user_can($panel[1])) || ($panel[0] == 'Admins' && (SP()->user->thisUser->admin || SP()->user->thisUser->moderator)))) {
					if (empty($adminparent)) {
						$adminparent = $panel[2];
						$parent_file = $panel[2]; # make sure wp knows the parent file (for non admin users)
						add_menu_page('Simple:Press', SP()->primitives->admin_text('Forum'), 'read', $adminparent, '', 'div', $_wp_last_object_menu + 1);
						add_submenu_page($adminparent, esc_attr($panel[0]), esc_attr($panel[0]), 'read', $panel[2]);
					} else {
						add_submenu_page($adminparent, esc_attr($panel[0]), esc_attr($panel[0]), 'read', $panel[2]);
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
			if (strpos($plugin_page, SP_FOLDER_NAME) !== false) add_submenu_page($adminparent, '', '', 'read', $adminparent);
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
		if (strpos($plugin_page, SP_FOLDER_NAME) !== false) add_submenu_page($adminparent, '', '', 'read', $adminparent);
		$submenu[$adminparent][1] = null;
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
		if (SP()->auths->current_user_can($panel[1]) || ($panel[0] == 'Admins' && (SP()->user->thisUser->admin || SP()->user->thisUser->moderator))) return true;
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

	$sfadminpanels = $sfactivepanels = array();

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
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Forums'),
		'SPF Manage Forums',
		SP_FOLDER_NAME.'/admin/panel-forums/spa-forums.php',
		$sfatooltips['forums'],
		'icon-Forums',
		wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader'),
		$forms,
		true);
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
			'newposts' => ''));
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Options'),
		'SPF Manage Options',
		SP_FOLDER_NAME.'/admin/panel-options/spa-options.php',
		$sfatooltips['options'],
		'icon-Options',
		wp_nonce_url(SPAJAXURL.'options-loader', 'options-loader'),
		$forms,
		true);
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
		SP()->primitives->admin_text('Components'),
		'SPF Manage Components',
		SP_FOLDER_NAME.'/admin/panel-components/spa-components.php',
		$sfatooltips['components'],
		'icon-Components',
		wp_nonce_url(SPAJAXURL.'components-loader', 'components-loader'),
		$forms,
		true);
	$sfactivepanels['components'] = 2;

	$forms = array(
		SP()->primitives->admin_text('Manage User Groups')		 => array(
			'usergroups' => 'sfreloadub'),
		SP()->primitives->admin_text('Create New User Group')	 => array(
			'createusergroup' => ''),
		SP()->primitives->admin_text('Map Users to User Group')	 => array(
			'mapusers' => 'sfreloadmu'));
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('User Groups'),
		'SPF Manage User Groups',
		SP_FOLDER_NAME.'/admin/panel-usergroups/spa-usergroups.php',
		$sfatooltips['usergroups'],
		'icon-UserGroups',
		wp_nonce_url(SPAJAXURL.'usergroups-loader', 'usergroups-loader'),
		$forms,
		true);
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
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Permissions'),
		'SPF Manage Permissions',
		SP_FOLDER_NAME.'/admin/panel-permissions/spa-permissions.php',
		$sfatooltips['permissions'],
		'icon-Permissions',
		wp_nonce_url(SPAJAXURL.'permissions-loader', 'permissions-loader'),
		$forms,
		true);
	$sfactivepanels['permissions'] = 4;

	$forms = array(
		SP()->primitives->admin_text('Page and Permalink')		 => array(
			'page' => 'sfreloadpp'),
		SP()->primitives->admin_text('Storage Locations')		 => array(
			'storage' => 'sfreloadsl'),
		SP()->primitives->admin_text('Language Translations')	 => array(
			'language' => 'sfreloadla'));
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Integration'),
		'SPF Manage Integration',
		SP_FOLDER_NAME.'/admin/panel-integration/spa-integration.php',
		$sfatooltips['integration'],
		'icon-Integration',
		wp_nonce_url(SPAJAXURL.'integration-loader', 'integration-loader'),
		$forms,
		true);
	$sfactivepanels['integration'] = 5;

	$forms = array(
		SP()->primitives->admin_text('Profile Options')		 => array(
			'options' => ''),
		SP()->primitives->admin_text('Profile Tabs & Menus') => array(
			'tabsmenus' => 'sfreloadptm'),
		SP()->primitives->admin_text('Avatar Options')		 => array(
			'avatars' => 'sfreloadav'),
		SP()->primitives->admin_text('Avatar Pool')			 => array(
			'pool' => 'sfreloadpool'));
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Profiles'),
		'SPF Manage Profiles',
		SP_FOLDER_NAME.'/admin/panel-profiles/spa-profiles.php',
		$sfatooltips['profiles'],
		'icon-Profiles',
		wp_nonce_url(SPAJAXURL.'profiles-loader', 'profiles-loader'),
		$forms,
		true);
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
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Admins'),
		'SPF Manage Admins',
		SP_FOLDER_NAME.'/admin/panel-admins/spa-admins.php',
		$sfatooltips['admins'],
		'icon-Admins',
		wp_nonce_url(SPAJAXURL.'admins-loader', 'admins-loader'),
		$forms,
		true);
	$sfactivepanels['admins'] = 7;

	$forms = array(
		SP()->primitives->admin_text('Member Information') => array(
			'member-info' => ''));
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Users'),
		'SPF Manage Users',
		SP_FOLDER_NAME.'/admin/panel-users/spa-users.php',
		$sfatooltips['users'],
		'icon-Users',
		wp_nonce_url(SPAJAXURL.'users-loader', 'users-loader'),
		$forms,
		true);
	$sfactivepanels['users'] = 8;

	$forms = array(
		SP()->primitives->admin_text('Available Plugins') => array(
			'plugin-list' => 'sfreloadpl'));
	if (!is_multisite() || is_super_admin()) $forms[SP()->primitives->admin_text('Plugin Uploader')] = array(
			'plugin-upload' => '');
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Plugins'),
		'SPF Manage Plugins',
		SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins.php',
		$sfatooltips['plugins'],
		'icon-Plugins',
		wp_nonce_url(SPAJAXURL.'plugins-loader', 'plugins-loader'),
		$forms,
		true);
	$sfactivepanels['plugins'] = 9;

	$forms = array(
		SP()->primitives->admin_text('Available Themes')	 => array(
			'theme-list' => 'sfreloadtlist'),
		SP()->primitives->admin_text('Mobile Phone Theme')	 => array(
			'mobile' => 'sfreloadmlist'),
		SP()->primitives->admin_text('Mobile Tablet Theme')	 => array(
			'tablet' => 'sfreloadtablist'));
	if (!is_multisite() || is_super_admin()) {
		$forms[SP()->primitives->admin_text('Theme Editor')] = array(
			'editor' => 'sfreloadttedit');
		$forms[SP()->primitives->admin_text('Custom CSS')] = array(
			'css' => 'sfreloadcss');
		$forms[SP()->primitives->admin_text('Theme Uploader')] = array(
			'theme-upload' => '');
	}
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Themes'),
		'SPF Manage Themes',
		SP_FOLDER_NAME.'/admin/panel-themes/spa-themes.php',
		$sfatooltips['themes'],
		'icon-Themes',
		wp_nonce_url(SPAJAXURL.'themes-loader', 'themes-loader'),
		$forms,
		true);
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
	$sfadminpanels[] = array(
		SP()->primitives->admin_text('Toolbox'),
		'SPF Manage Toolbox',
		SP_FOLDER_NAME.'/admin/panel-toolbox/spa-toolbox.php',
		$sfatooltips['toolbox'],
		'icon-Toolbox',
		wp_nonce_url(SPAJAXURL.'toolbox-loader', 'toolbox-loader'),
		$forms,
		true);
	$sfactivepanels['toolbox'] = 11;

	# allow plugins to alter the admin menus
	$sfadminpanels = apply_filters('sf_admin_panels', $sfadminpanels);
	$sfactivepanels = apply_filters('sf_admin_activepanels', $sfactivepanels);
}
