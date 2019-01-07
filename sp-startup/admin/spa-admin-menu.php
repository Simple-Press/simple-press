<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2016-10-30 15:33:57 -0500 (Sun, 30 Oct 2016) $
$Rev: 14688 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE ADMIN
#	Loaded by global admin - globally required by back end/admin for all pages
#
# ==========================================================================================

function spa_admin_menu() {
	global $spStatus, $sfadminpanels, $spThisUser, $plugin_page, $submenu, $_wp_last_object_menu, $_registered_pages, $parent_file;

	if ($spStatus == 'ok') {
		# set up our default admin menu
		spa_setup_admin_menu();

		$adminparent = '';
		if (spa_can_access_admin_panels()) {
			# build our admin nav menu
			foreach ($sfadminpanels as $panel) {
				if ($panel[7] && ((sp_current_user_can($panel[1])) || ($panel[0] == 'Admins' && ($spThisUser->admin || $spThisUser->moderator)))) {
					if (empty($adminparent)) {
						$adminparent = $panel[2];
                        $parent_file = $panel[2]; # make sure wp knows the parent file (for non admin users)
						add_menu_page('Simple:Press', spa_text('Forum'), 'read', $adminparent, '', 'div', $_wp_last_object_menu + 1);
						add_submenu_page($adminparent, esc_attr($panel[0]), esc_attr($panel[0]), 'read', $panel[2]);
					} else {
						add_submenu_page($adminparent, esc_attr($panel[0]), esc_attr($panel[0]), 'read', $panel[2]);
					}
				}

                # we have to simulate sp plugins admin panel loaded so our plugins can use it as hookname for adding menu items
                # kind of hacky- will allow a user to manual type in our plugin panel url, but access will be denied by our permission check
                $_registered_pages['admin_page_simple-press/admin/panel-plugins/spa-plugins'] = 1;
			}
		} else if (current_user_can('administrator')) {
			$adminparent = 'simple-press/sp-startup/admin/spa-admin-notice.php';
			add_menu_page('Simple:Press', spa_text('Forum'), 'manage_options', $adminparent, '', 'div', $_wp_last_object_menu + 1);
			add_submenu_page($adminparent, spa_text('WP Admin Notice'), spa_text('WP Admin Notice'), 'read', $adminparent);

			# hack for wp stubborness of not wanting singular submenu under a menu item
            if (strpos($plugin_page, 'simple-press') !== false) add_submenu_page($adminparent, '', '', 'read', $adminparent);
      		$submenu[$adminparent][1] = null;
		}
	} else {
		$adminparent = SPINSTALLPATH;
		add_menu_page('Simple:Press', spa_text('Forum'), 'activate_plugins', $adminparent, '', 'div', $_wp_last_object_menu + 1);

		if ($spStatus == 'Install') {
		    if (current_user_can('activate_plugins')) {
                add_submenu_page($adminparent, spa_text('Install Simple:Press'), spa_text('Install Simple:Press'), 'activate_plugins', $adminparent);
            } else {
                add_submenu_page($adminparent, spa_text('Needs Installing'), spa_text('Needs Installing'), 'read', '');
            }
		} else {
		    if (current_user_can('update_plugins')) {
    			add_submenu_page($adminparent, spa_text('Upgrade Simple:Press'), spa_text('Upgrade Simple:Press'), 'update_plugins', $adminparent);

                # lets allow our admin panel hookname so we dont get wp error and our redirect will work
                # kind of hacky- will allow a user to manual type in these plugin panels for url, but access will be denied by our permission check
                $_registered_pages['admin_page_simple-press/admin/panel-forums/spa-forums'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-options/spa-options'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-components/spa-components'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-usergroups/spa-usergroups'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-permissions/spa-plugins'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-integration/spa-integration'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-profiles/spa-profiles'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-admins/spa-admins'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-users/spa-users'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-plugins/spa-plugins'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-themes/spa-themes'] = 1;
                $_registered_pages['admin_page_simple-press/admin/panel-toolbox/spa-toolbox'] = 1;
            } else {
                add_submenu_page($adminparent, spa_text('Needs Upgrading'), spa_text('Needs Upgrading'), 'read', '');
            }
		}

		# hack for wp stubborness of not wanting singular submenu under a menu item
		if (strpos($plugin_page, 'simple-press') !== false) add_submenu_page($adminparent, '', '', 'read', $adminparent);
   		$submenu[$adminparent][1] = null;
	}

	# let plugins add new wp admin nav panels
	do_action('sph_admin_menu', $adminparent);
}

function spa_can_access_admin_panels() {
	global $sfadminpanels, $spThisUser;

	foreach ($sfadminpanels as $panel) {
		if (sp_current_user_can($panel[1]) || ($panel[0] == 'Admins' && ($spThisUser->admin || $spThisUser->moderator))) return true;
	}
	return false;
}

function spa_setup_admin_menu() {
	global $sfadminpanels, $sfactivepanels, $sfatooltips;

	# Get correct tooltips file
	$lang = spa_get_language_code();
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-menu-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-menu-tips-en.php';
	if (file_exists($ttpath)) include_once $ttpath;

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
		spa_text('Manage Groups And Forums') => array('forums' => 'sfreloadfb'),
		spa_text('Order Groups and Forums') => array('ordering' => 'sfreloadfo'),
		spa_text('Create New Group') => array('creategroup' => ''),
		spa_text('Create New Forum') => array('createforum' => ''),
		spa_text('Custom Icons') => array('customicons' => 'sfreloadci'),
		spa_text('Featured Images') => array('featuredimages' => 'sfreloadfi'),
		spa_text('Add Global Permission Set') => array('globalperm' => ''),
		spa_text('Delete All Permission Sets') => array('removeperms' => ''),
		spa_text('Merge Forums') => array('mergeforums' => 'sfreloadmf'),
		spa_text('Global RSS Settings') => array('globalrss' => 'sfreloadfd'));
	$sfadminpanels[] = array(spa_text('Forums'), 'SPF Manage Forums', 'simple-press/admin/panel-forums/spa-forums.php', $sfatooltips['forums'], 'icon-Forums', wp_nonce_url(SPAJAXURL.'forums-loader', 'forums-loader'), $forms, true);
	$sfactivepanels['forums'] = 0;

	$forms = array(
		spa_text('Global Settings') => array('global' => 'sfreloadog'),
		spa_text('General Display Settings') => array('display' => ''),
		spa_text('Content Settings') => array('content' => ''),
		spa_text('Member Settings') => array('members' => 'sfreloadms'),
		spa_text('Email Settings') => array('email' => ''),
		spa_text('New Post Handling') => array('newposts' => ''));
	$sfadminpanels[] = array(spa_text('Options'), 'SPF Manage Options', 'simple-press/admin/panel-options/spa-options.php', $sfatooltips['options'], 'icon-Options', wp_nonce_url(SPAJAXURL.'options-loader' , 'options-loader'), $forms, true);
	$sfactivepanels['options'] = 1;

	$forms = array(
		spa_text('Smileys') => array('smileys' => 'sfreloadsm'),
		spa_text('Login And Registration') => array('login' => ''),
		spa_text('SEO') => array('seo' => 'sfreloadse'),
		spa_text('Forum Ranks') => array('forumranks' => 'sfreloadfr'),
		spa_text('Custom Messages') => array('messages' => ''));
	$sfadminpanels[] = array(spa_text('Components'), 'SPF Manage Components', 'simple-press/admin/panel-components/spa-components.php', $sfatooltips['components'], 'icon-Components', wp_nonce_url(SPAJAXURL.'components-loader', 'components-loader'), $forms, true);
	$sfactivepanels['components'] = 2;

	$forms = array(
		spa_text('Manage User Groups') => array('usergroups' => 'sfreloadub'),
		spa_text('Create New User Group') => array('createusergroup' => ''),
		spa_text('Map Users to User Group') => array('mapusers' => 'sfreloadmu'));
	$sfadminpanels[] = array(spa_text('User Groups'), 'SPF Manage User Groups', 'simple-press/admin/panel-usergroups/spa-usergroups.php', $sfatooltips['usergroups'], 'icon-UserGroups', wp_nonce_url(SPAJAXURL.'usergroups-loader', 'usergroups-loader'), $forms, true);
	$sfactivepanels['usergroups'] = 3;

	$forms = array(
		spa_text('Manage Permissions Sets') => array('permissions' => 'sfreloadpb'),
		spa_text('Add New Permission Set') => array('createperm' => ''),
		spa_text('Reset Permissions') => array('resetperms' => ''),
		spa_text('Add New Authorization') => array('newauth' => ''));
	$sfadminpanels[] = array(spa_text('Permissions'), 'SPF Manage Permissions', 'simple-press/admin/panel-permissions/spa-permissions.php', $sfatooltips['permissions'], 'icon-Permissions', wp_nonce_url(SPAJAXURL.'permissions-loader', 'permissions-loader'), $forms, true);
	$sfactivepanels['permissions'] = 4;

	$forms = array(
		spa_text('Page and Permalink') => array('page' => 'sfreloadpp'),
		spa_text('Storage Locations') => array('storage' => 'sfreloadsl'),
		spa_text('Language Translations') => array('language' => 'sfreloadla'));
	$sfadminpanels[] = array(spa_text('Integration'), 'SPF Manage Integration', 'simple-press/admin/panel-integration/spa-integration.php', $sfatooltips['integration'], 'icon-Integration', wp_nonce_url(SPAJAXURL.'integration-loader', 'integration-loader'), $forms, true);
	$sfactivepanels['integration'] = 5;

	$forms = array(
		spa_text('Profile Options') => array('options' => ''),
		spa_text('Profile Tabs & Menus') => array('tabsmenus' => 'sfreloadptm'),
		spa_text('Avatar Options') => array('avatars' => 'sfreloadav'),
		spa_text('Avatar Pool') => array('pool' => 'sfreloadpool'));
	$sfadminpanels[] = array(spa_text('Profiles'), 'SPF Manage Profiles', 'simple-press/admin/panel-profiles/spa-profiles.php', $sfatooltips['profiles'], 'icon-Profiles', wp_nonce_url(SPAJAXURL.'profiles-loader', 'profiles-loader'), $forms, true);
	$sfactivepanels['profiles'] = 6;

	if (sp_current_user_can('SPF Manage Admins')) {
		$forms = array(
			spa_text('Your Admin Options') => array('youradmin' => 'sfreloadao'),
			spa_text('Global Admin Options') => array('globaladmin' => ''),
			spa_text('Manage Admins') => array('manageadmin' => 'sfreloadma'));
	} else {
		$forms = array(
			spa_text('Your Admin Options') => array('youradmin' => 'sfreloadao'));
	}
	$sfadminpanels[] = array(spa_text('Admins'), 'SPF Manage Admins', 'simple-press/admin/panel-admins/spa-admins.php', $sfatooltips['admins'], 'icon-Admins', wp_nonce_url(SPAJAXURL.'admins-loader', 'admins-loader'), $forms, true);
	$sfactivepanels['admins'] = 7;

	$forms = array(
		spa_text('Member Information') => array('member-info' => ''));
	$sfadminpanels[] = array(spa_text('Users'), 'SPF Manage Users', 'simple-press/admin/panel-users/spa-users.php', $sfatooltips['users'], 'icon-Users', wp_nonce_url(SPAJAXURL.'users-loader', 'users-loader'), $forms, true);
	$sfactivepanels['users'] = 8;

	$forms = array(
		spa_text('Available Plugins') => array('plugin-list' => 'sfreloadpl'));
	if (!is_multisite() || is_super_admin()) $forms[spa_text('Plugin Uploader')] = array('plugin-upload' => '');
	$sfadminpanels[] = array(spa_text('Plugins'), 'SPF Manage Plugins', 'simple-press/admin/panel-plugins/spa-plugins.php', $sfatooltips['plugins'], 'icon-Plugins', wp_nonce_url(SPAJAXURL.'plugins-loader', 'plugins-loader'), $forms, true);
	$sfactivepanels['plugins'] = 9;

	$forms = array(
		spa_text('Available Themes') => array('theme-list' => 'sfreloadtlist'),
		spa_text('Mobile Phone Theme') => array('mobile' => 'sfreloadmlist'),
		spa_text('Mobile Tablet Theme') => array('tablet' => 'sfreloadtablist'));
	if (!is_multisite() || is_super_admin()) {
	   $forms[spa_text('Theme Editor')] = array('editor' => 'sfreloadttedit');
	   $forms[spa_text('Custom CSS')] = array('css' => 'sfreloadcss');
	   $forms[spa_text('Theme Uploader')] = array('theme-upload' => '');
	}
	$sfadminpanels[] = array(spa_text('Themes'), 'SPF Manage Themes', 'simple-press/admin/panel-themes/spa-themes.php', $sfatooltips['themes'], 'icon-Themes', wp_nonce_url(SPAJAXURL.'themes-loader', 'themes-loader'), $forms, true);
	$sfactivepanels['themes'] = 10;

	$forms = array(
		spa_text('Toolbox') => array('toolbox' => ''),
		spa_text('Housekeeping') => array('housekeeping' => 'sfreloadhk'),
		spa_text('Data Inspector') => array('inspector' => ''),
		spa_text('CRON Inspector') => array('cron' => 'sfcron'),
		spa_text('Error Log') => array('errorlog' => 'sfreloadel'),
		spa_text('Environment') => array('environment' => ''),
		spa_text('Install Log') => array('log' => ''),
		spa_text('Change Log') => array('changelog' => ''),
		spa_text('Uninstall') => array('uninstall' => ''));
	$sfadminpanels[] = array(spa_text('Toolbox'), 'SPF Manage Toolbox', 'simple-press/admin/panel-toolbox/spa-toolbox.php', $sfatooltips['toolbox'], 'icon-Toolbox', wp_nonce_url(SPAJAXURL.'toolbox-loader', 'toolbox-loader'), $forms, true);
	$sfactivepanels['toolbox'] = 11;

	# allow plugins to alter the admin menus
	$sfadminpanels = apply_filters('sf_admin_panels', $sfadminpanels);
	$sfactivepanels = apply_filters('sf_admin_activepanels', $sfactivepanels);
}

?>