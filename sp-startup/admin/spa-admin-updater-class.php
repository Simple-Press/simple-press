<?php
/*
Simple:Press
DESC: Admin functions for core, theme and plugin updates
$LastChangedDate: 2017-06-14 19:57:44 -0500 (Wed, 14 Jun 2017) $
$Rev: 15427 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

include_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
class SP_Plugin_Upgrader extends WP_Upgrader {
	var $result;
	var $bulk = false;
	var $show_before = '';

	function upgrade_strings() {
		$this->strings['up_to_date'] 			= spa_text('The plugin is at the latest version');
		$this->strings['no_package'] 			= spa_text('Update package not available');
		$this->strings['downloading_package']	= spa_text('Downloading update from %s');
		$this->strings['unpack_package'] 		= spa_text('Unpacking the update...');
		$this->strings['deactivate_plugin'] 	= spa_text('Deactivating the plugin...');
		$this->strings['remove_old'] 			= spa_text('Removing the old version of the plugin...');
		$this->strings['remove_old_failed'] 	= spa_text('Could not remove the old plugin');
		$this->strings['process_failed'] 		= spa_text('Plugin update failed');
		$this->strings['process_success'] 		= spa_text('Plugin updated successfully');
	}

	function install_strings() {
		$this->strings['no_package']          = spa_text('Install package not available');
		$this->strings['unpack_package']      = spa_text('Unpacking the package...');
		$this->strings['installing_package']  = spa_text('Installing the plugin...');
		$this->strings['process_failed']      = spa_text('SP Plugin install failed');
		$this->strings['process_success']     = spa_text('SP Plugin installed successfully');
	}

	function install($package) {
		$this->init();
		$this->install_strings();

		add_filter('upgrader_source_selection', array(&$this, 'check_package'));

        $info = pathinfo($package);
		$this->run(array(
					'package' => $package,
					'destination' => SFPLUGINDIR.$info['filename'],
					'clear_destination' => false, # Do not overwrite files.
					'clear_working' => true,
					'hook_extra' => array()
					));

		remove_filter('upgrader_source_selection', array(&$this, 'check_package'));

		if (!$this->result || is_wp_error($this->result)) return $this->result;

		# Force refresh of SP plugin update information
		delete_site_transient('sp_update_plugins');

		return true;
	}

	function bulk_upgrade($plugins) {
		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		$current = get_site_transient('sp_update_plugins');

		add_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'), 10, 4);

		$this->skin->header();

		# Connect to the Filesystem first
		$res = $this->fs_connect(array(WP_CONTENT_DIR, SFPLUGINDIR));
		if (!$res) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		$this->maintenance_mode(false);

		$results = array();

		$this->update_count = count($plugins);
		$this->update_current = 0;
		foreach ($plugins as $plugin) {
			$this->update_current++;
			$this->skin->plugin_info = sp_get_plugin_data(SFPLUGINDIR.$plugin, false, true);

			if (!isset($current->response[$plugin])) {
				$this->skin->set_result(false);
				$this->skin->before();
				$this->skin->error('up_to_date');
				$this->skin->after();
				$results[$plugin] = false;
				continue;
			}

			# Get the URL to the zip file
			$r = $current->response[$plugin];

			$this->skin->plugin_active = sp_is_plugin_active($plugin);

			$result = $this->run(array(
						'package' 			=> $r->package,
						'destination' 		=> dirname(SFPLUGINDIR.$plugin),
						'clear_destination' => true,
						'clear_working' 	=> true,
						'is_multi' 			=> true,
						'hook_extra' 		=> array('plugin' => $plugin)
			));

			$results[$plugin] = $this->result;

            # fire action for plugin upgrdes
            do_action('sph_plugin_update_'.$plugin);

			# Prevent credentials auth screen from displaying multiple times
			if (false === $result) break;
		} # end foreach $plugins

		$this->maintenance_mode(false);

		$this->skin->bulk_footer();

		$this->skin->footer();

		# Cleanup our hooks, incase something else does a upgrade on this connection
		remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'));

		# Force refresh of plugin update information
		delete_site_transient('sp_update_plugins');

		return $results;
	}

	# Hooked to upgrade_clear_destination
	function delete_old_plugin($removed, $local_destination, $remote_destination, $plugin) {
		global $wp_filesystem;

		if (is_wp_error($removed)) return $removed; # Pass errors through

		$plugin = isset($plugin['plugin']) ? $plugin['plugin'] : '';
		if (empty($plugin)) return new WP_Error('bad_request', $this->strings['bad_request']);

		$plugins_dir = $wp_filesystem->find_folder(SFPLUGINDIR);
		$this_plugin_dir = trailingslashit(dirname($plugins_dir.$plugin));

		if (!$wp_filesystem->exists($this_plugin_dir)) return $removed; # If its already vanished

		# If plugin is in its own directory, recursively delete the directory.
		if (strpos($plugin, '/') && $this_plugin_dir != $plugins_dir) { # base check on if plugin includes directory separator AND that its not the root plugin folder
			$deleted = $wp_filesystem->delete($this_plugin_dir, true);
		} else {
			$deleted = $wp_filesystem->delete($plugins_dir.$plugin);
		}

		if (!$deleted) return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);

		return true;
	}

	function check_package($source) {
		global $wp_filesystem;

		if (is_wp_error($source)) return $source;

		$working_directory = str_replace($wp_filesystem->wp_content_dir(), trailingslashit(WP_CONTENT_DIR), $source);
		if (!is_dir($working_directory)) return $source; # Sanity check, if the above fails, lets not prevent installation.

		# Check the folder contains at least 1 valid plugin.
		$plugins_found = false;
		foreach (glob($working_directory.'*.php') as $file) {
			$info = sp_get_plugin_data($file, false, false);
			if (!empty( $info['Name'])) {
				$plugins_found = true;
				break;
			}
		}

		if (!$plugins_found) return new WP_Error('incompatible_archive', $this->strings['incompatible_archive'], spa_text('No valid plugins were found'));

		return $source;
	}

	function plugin_info() {
		if (!is_array($this->result)) return false;
		if (empty($this->result['destination_name'])) return false;

		$plugin = sp_get_plugins('/'.$this->result['destination_name']); # Ensure to pass with leading slash
		if (empty($plugin)) return false;

		$pluginfiles = array_keys($plugin); # Assume the requested plugin is the first in the list

		return $this->result['destination_name'].'/'.$pluginfiles[0];
	}
}

class Bulk_SP_Plugin_Upgrader_Skin extends Bulk_Upgrader_Skin {
	var $plugin_info = array(); # Plugin_Upgrader::bulk() will fill this in

	function __construct($args=array()) {
		parent::__construct($args);
	}

	function add_strings() {
		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = spa_text('Updating Plugin %1$s (%2$d/%3$d)');
	}

	function before($title = '') {
		parent::before($this->plugin_info['Name']);
	}

	function after($title = '') {
		parent::after($this->plugin_info['Name']);
	}

	function bulk_footer() {
		parent::bulk_footer();
		$update_actions = array(
			'plugins_page' => '<a href="'.admin_url('admin.php?page=simple-press/admin/panel-plugins/spa-plugins.php').'" title="'.spa_text('Go to SP plugins page').'" target="_parent">'.spa_text('Go to SP plugins page').'</a>',
			'updates_page' => '<a href="'.self_admin_url('update-core.php').'" title="'.spa_text('Go to WordPress updates page').'" target="_parent">'.spa_text('Return to WordPress updates').'</a>'
		);

		$update_actions = apply_filters('sph_update_bulk_plugins_complete_actions', $update_actions, $this->plugin_info);
		if (!empty($update_actions)) $this->feedback(implode(' | ', (array)$update_actions));
	}
}

class SP_Plugin_Installer_Skin extends Plugin_Installer_Skin {
	function after() {
		$plugin_file = $this->upgrader->plugin_info();

		$install_actions = array();
		$install_actions['plugins_page'] = '<a href="'.SFADMINPLUGINS.'" title="'.esc_attr(spa_text('Return to SP Plugins page')).'" target="_parent">'.spa_text('Return to SP plugins page').'</a>';
		$install_actions = apply_filters('sph_install_plugin_actions', $install_actions, $plugin_file);
		if (!empty($install_actions)) $this->feedback(implode(' | ', (array)$install_actions));
    }
}

class SP_Theme_Upgrader extends WP_Upgrader {
	var $result;

	function upgrade_strings() {
		$this->strings['up_to_date'] 			= spa_text('The theme is at the latest version');
		$this->strings['no_package'] 			= spa_text('Update package not available');
		$this->strings['downloading_package'] 	= spa_text('Downloading update from %s');
		$this->strings['unpack_package'] 		= spa_text('Unpacking the update...');
		$this->strings['remove_old'] 			= spa_text('Removing the old version of the theme...');
		$this->strings['remove_old_failed'] 	= spa_text('Could not remove the old theme');
		$this->strings['process_failed'] 		= spa_text('Theme update failed');
		$this->strings['process_success'] 		= spa_text('Theme updated successfully');
	}

	function install_strings() {
		$this->strings['no_package']          = spa_text('Install package not available');
		$this->strings['unpack_package']      = spa_text('Unpacking the package...');
		$this->strings['installing_package']  = spa_text('Installing the theme...');
		$this->strings['process_failed']      = spa_text('SP Theme install failed');
		$this->strings['process_success']     = spa_text('SP Theme installed successfully');
	}

	function install($package) {
		$this->init();
		$this->install_strings();

		add_filter('upgrader_source_selection', array(&$this, 'check_package'));

        $info = pathinfo($package);
		$options = array(
						'package' => $package,
						'destination' => SPTHEMEBASEDIR.$info['filename'],
						'clear_destination' => false, # Do not overwrite files.
						'clear_working' => true
						);

		$this->run($options);

		remove_filter('upgrader_source_selection', array(&$this, 'check_package'));

		if (!$this->result || is_wp_error($this->result)) return $this->result;

		# Force refresh of theme update information
		delete_site_transient('sp_update_themes');

		return true;
	}

	function bulk_upgrade($themes) {
		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		$current = get_site_transient('sp_update_themes');

		add_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10, 4);

		$this->skin->header();

		# Connect to the Filesystem first
		$res = $this->fs_connect(array(SPTHEMEBASEDIR));
		if (!$res) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		$this->maintenance_mode(false);

		$results = array();

		$this->update_count = count($themes);
		$this->update_current = 0;

		foreach ($themes as $theme) {
			$this->update_current++;

			if (!isset($current->response[$theme])) {
				$this->skin->set_result(false);
				$this->skin->before();
				$this->skin->error('up_to_date');
				$this->skin->after();
				$results[$theme] = false;
				continue;
			}

			# Get the URL to the zip file
			$r = $current->response[$theme];

			include_once SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
			$theme_file = SPTHEMEBASEDIR.$theme.'/spTheme.txt';
			$this->skin->theme_info = sp_get_theme_data($theme_file, false, true);

			$options = array(
							'package' 			=> $r->package,
							'destination' 		=> dirname($theme_file),
							'clear_destination' => true,
							'clear_working' 	=> true,
							'hook_extra' 		=> array('theme' => $theme)
			);

			$result = $this->run($options);

			$results[$theme] = $this->result;

            # fire action for theme upgrdes
            do_action('sph_theme_update_'.$theme);

			# Prevent credentials auth screen from displaying multiple times
			if (false === $result) break;
		} # end foreach $themes

		$this->skin->bulk_footer();

		$this->skin->footer();

		# Cleanup our hooks, incase something else does a upgrade on this connection
		remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10, 4);

		# Force refresh of theme update information
		delete_site_transient('sp_update_themes');

		return $results;
	}

	function check_package($source) {
		global $wp_filesystem;

		if (is_wp_error($source)) return $source;

		# Check the folder contains a valid theme
		$working_directory = str_replace( $wp_filesystem->wp_content_dir(), trailingslashit(WP_CONTENT_DIR), $source);
		if (!is_dir($working_directory)) return $source; # Sanity check, if the above fails, lets not prevent installation.

		# A proper archive should have an spTheme.txt file in the single subdirectory
		if (!file_exists($working_directory.'spTheme.txt'))
			return new WP_Error( 'incompatible_archive', $this->strings['incompatible_archive'], spa_text('The theme is missing the spTheme.txt file'));

		$info = get_file_data($working_directory.'spTheme.txt', array('Name' => 'Simple:Press Theme Title'));

		if (empty($info['Name']))
			return new WP_Error('incompatible_archive', $this->strings['incompatible_archive'], spa_text("The spTheme.txt stylesheet doesn't contain a valid theme header"));

		return $source;
	}

	function theme_info($theme=null) {
		if (empty($theme)) {
			if (!empty($this->result['destination_name'])) {
				$theme = $this->result['destination_name'];
			} else {
				return false;
            }
		}
		return sp_get_theme_data($theme);
	}

	function delete_old_theme($removed, $local_destination, $remote_destination, $theme) {
		global $wp_filesystem;

		$theme = isset($theme['theme']) ? $theme['theme'] : '';

		if (is_wp_error($removed) || empty($theme)) return $removed; # Pass errors through

		$themes_dir = $wp_filesystem->find_folder(SPTHEMEBASEDIR);
		if ($wp_filesystem->exists($themes_dir.$theme)) {
			if (!$wp_filesystem->delete($themes_dir.$theme, true)) return false;
		}
		return true;
	}
}

class Bulk_SP_Theme_Upgrader_Skin extends Bulk_Upgrader_Skin {
	var $theme_info = array(); # Theme_Upgrader::bulk() will fill this in

	function __construct($args=array()) {
		parent::__construct($args);
	}

	function add_strings() {
		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = spa_text('Updating Theme %1$s (%2$d/%3$d)');
	}

	function before($title = '') {
		parent::before($this->theme_info['Name']);
	}

	function after($title = '') {
		parent::after($this->theme_info['Name']);
	}

	function bulk_footer() {
		parent::bulk_footer();
		$update_actions = array(
			'plugins_page' => '<a href="'.admin_url('admin.php?page=simple-press/admin/panel-themes/spa-themes.php').'" title="'.spa_text('Go to SP themes page').'" target="_parent">'.spa_text('Go to SP themes page').'</a>',
			'updates_page' => '<a href="'.self_admin_url('update-core.php').'" title="'.spa_text('Go to WordPress updates page').'" target="_parent">'.spa_text('Return to WordPress updates').'</a>'
		);

		$update_actions = apply_filters('sph_update_bulk_themes_complete_actions', $update_actions, $this->theme_info);
		if (!empty($update_actions)) $this->feedback(implode(' | ', (array)$update_actions));
	}
}

class SP_Theme_Installer_Skin extends Theme_Installer_Skin {
	function after() {
		$theme_info = $this->upgrader->theme_info();

		$install_actions = array();
		$install_actions['themes_page'] = '<a href="'.SFADMINTHEMES.'" title="'.esc_attr(spa_text('Return to SP themes page')).'" target="_parent">'.spa_text('Return to SP themes page').'</a>';
		$install_actions = apply_filters('sph_install_theme_actions', $install_actions, $theme_info);
		if (!empty($install_actions)) $this->feedback(implode(' | ', (array)$install_actions));
    }
}

?>