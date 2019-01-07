<?php
/**
 * Plugin upgrader class.
 * Extends the WP plugin upgrader class.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * upgrade_strings()
 * install_strings()
 * install($package)
 * bulk_upgrade($plugins)
 * delete_old_plugin($removed, $local_destination, $remote_destination, $plugin)
 * check_package($source)
 * plugin_info()
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */

require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';

class spcPluginUpgrader extends WP_Upgrader {
	/**
	 *
	 * @var bool    success or failure
	 *
	 * @since 6.0
	 */
	public $result;

	/**
	 *
	 * @var bool    is it bulk upgrade?
	 *
	 * @since 6.0
	 */
	public $bulk = false;

	/**
	 *
	 * @var string    text to show before upgrade link
	 *
	 * @since 6.0
	 */
	public $show_before = '';

	/**
	 * This method replaces the WP upgrader class upgrade_strings() with our plugin upgrade strings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function upgrade_strings() {
		$this->strings['up_to_date']          = SP()->primitives->admin_text('The plugin is at the latest version');
		$this->strings['no_package']          = SP()->primitives->admin_text('Update package not available');
		$this->strings['downloading_package'] = SP()->primitives->admin_text('Downloading update from %s');
		$this->strings['unpack_package']      = SP()->primitives->admin_text('Unpacking the update...');
		$this->strings['deactivate_plugin']   = SP()->primitives->admin_text('Deactivating the plugin...');
		$this->strings['remove_old']          = SP()->primitives->admin_text('Removing the old version of the plugin...');
		$this->strings['remove_old_failed']   = SP()->primitives->admin_text('Could not remove the old plugin');
		$this->strings['process_failed']      = SP()->primitives->admin_text('Plugin update failed');
		$this->strings['process_success']     = SP()->primitives->admin_text('Plugin updated successfully');
	}

	/**
	 * This method replaces the WP upgrader class install_strings() with our plugin upgrade strings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function install_strings() {
		$this->strings['no_package']         = SP()->primitives->admin_text('Install package not available');
		$this->strings['unpack_package']     = SP()->primitives->admin_text('Unpacking the package...');
		$this->strings['installing_package'] = SP()->primitives->admin_text('Installing the plugin...');
		$this->strings['process_failed']     = SP()->primitives->admin_text('SP Plugin install failed');
		$this->strings['process_success']    = SP()->primitives->admin_text('SP Plugin installed successfully');
	}

	/**
	 * This method replaces the WP upgrader class install() with our plugin upgrade code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return bool        true if install successful, otherwise false
	 */
	public function install($package) {
		$this->init();
		$this->install_strings();

		add_filter('upgrader_source_selection', array(&$this, 'check_package'));

		$info = pathinfo($package);
		$this->run(array('package'       => $package, 'destination' => SPPLUGINDIR.$info['filename'], 'clear_destination' => false, # Do not overwrite files.
						 'clear_working' => true, 'hook_extra' => array()));

		remove_filter('upgrader_source_selection', array(&$this, 'check_package'));

		if (!$this->result || is_wp_error($this->result)) return $this->result;

		# Force refresh of SP plugin update information
		delete_site_transient('sp_update_plugins');

		return true;
	}

	/**
	 * This method replaces the WP upgrader class bulk_upgrade() with our plugin bulk upgrade code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $plugins plugins being bulk upgraded
	 *
	 * @return array|bool
	 */
	public function bulk_upgrade($plugins) {
		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		$current = get_site_transient('sp_update_plugins');

		add_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'), 10, 4);

		$this->skin->header();

		# Connect to the Filesystem first
		$res = $this->fs_connect(array(WP_CONTENT_DIR, SPPLUGINDIR));
		if (!$res) {
			$this->skin->footer();

			return false;
		}

		$this->skin->bulk_header();

		$this->maintenance_mode(false);

		$results = array();

		$this->update_count   = count($plugins);
		$this->update_current = 0;
		foreach ($plugins as $plugin) {
			$this->update_current++;
			$this->skin->plugin_info = SP()->plugin->get_data(SPPLUGINDIR.$plugin, false, true);

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

			$this->skin->plugin_active = SP()->plugin->is_active($plugin);

			$result = $this->run(array('package' => $r->package, 'destination' => dirname(SPPLUGINDIR.$plugin), 'clear_destination' => true, 'clear_working' => true, 'is_multi' => true, 'hook_extra' => array('plugin' => $plugin)));

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

	/**
	 * This method replaces the WP upgrader class delete_old_plugin() with our plugin delete code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param bool   $removed           previous remove errors (passed through)
	 * @param string $local_destination unused
	 * @param string $remote            destination        unused
	 * @param string $plugin            current plugin info
	 *
	 * @return bool|WP_Error
	 */
	public function delete_old_plugin($removed, $local_destination, $remote_destination, $plugin) {
		global $wp_filesystem;

		if (is_wp_error($removed)) return $removed;# Pass errors through

		$plugin = isset($plugin['plugin']) ? $plugin['plugin'] : '';
		if (empty($plugin)) return new WP_Error('bad_request', $this->strings['bad_request']);

		$plugins_dir     = $wp_filesystem->find_folder(SPPLUGINDIR);
		$this_plugin_dir = trailingslashit(dirname($plugins_dir.$plugin));

		if (!$wp_filesystem->exists($this_plugin_dir)) return $removed;# If its already vanished
		# If plugin is in its own directory, recursively delete the directory.
		if (strpos($plugin, '/') && $this_plugin_dir != $plugins_dir) { # base check on if plugin includes directory separator AND that its not the root plugin folder
			$deleted = $wp_filesystem->delete($this_plugin_dir, true);
		} else {
			$deleted = $wp_filesystem->delete($plugins_dir.$plugin);
		}

		if (!$deleted) return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);

		return true;
	}

	/**
	 * This method replaces the WP upgrader class check_package() with our plugin package checking code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $source location of the source package for the install/upgrade
	 *
	 * @return string|WP_Error
	 */
	public function check_package($source) {
		global $wp_filesystem;

		if (is_wp_error($source)) return $source;

		$working_directory = str_replace($wp_filesystem->wp_content_dir(), trailingslashit(WP_CONTENT_DIR), $source);
		if (!is_dir($working_directory)) return $source;# Sanity check, if the above fails, lets not prevent installation.
		# Check the folder contains at least 1 valid plugin.
		$plugins_found = false;
		foreach (glob($working_directory.'*.php') as $file) {
			$info = SP()->plugin->get_data($file, false, false);
			if (!empty($info['Name'])) {
				$plugins_found = true;
				break;
			}
		}

		if (!$plugins_found) return new WP_Error('incompatible_archive', $this->strings['incompatible_archive'], SP()->primitives->admin_text('No valid plugins were found'));

		return $source;
	}

	/**
	 * This method replaces the WP upgrader class plugin_info() with our plugin info code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return bool|string
	 */
	public function plugin_info() {
		if (!is_array($this->result)) return false;
		if (empty($this->result['destination_name'])) return false;

		$plugin = SP()->plugin->get_list('/'.$this->result['destination_name']); # Ensure to pass with leading slash
		if (empty($plugin)) return false;

		$pluginfiles = array_keys($plugin); # Assume the requested plugin is the first in the list

		return $this->result['destination_name'].'/'.$pluginfiles[0];
	}
}