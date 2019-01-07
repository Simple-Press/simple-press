<?php
/**
 * Theme upgrader class.
 * Extends the WP theme upgrader class.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * upgrade_strings()
 * install_strings()
 * install($package)
 * bulk_upgrade($themes)
 * delete_old_theme($removed, $local_destination, $remote_destination, $theme)
 * check_package($source)
 * theme_info()
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */

require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';

class spcThemeUpgrader extends WP_Upgrader {
	/**
	 *
	 * @var bool    success or failure
	 *
	 * @since 6.0
	 */
	public $result;

	/**
	 * This method replaces the WP ugprader class upgrade_strings() with our theme upgrade strings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function upgrade_strings() {
		$this->strings['up_to_date']          = SP()->primitives->admin_text('The theme is at the latest version');
		$this->strings['no_package']          = SP()->primitives->admin_text('Update package not available');
		$this->strings['downloading_package'] = SP()->primitives->admin_text('Downloading update from %s');
		$this->strings['unpack_package']      = SP()->primitives->admin_text('Unpacking the update...');
		$this->strings['remove_old']          = SP()->primitives->admin_text('Removing the old version of the theme...');
		$this->strings['remove_old_failed']   = SP()->primitives->admin_text('Could not remove the old theme');
		$this->strings['process_failed']      = SP()->primitives->admin_text('Theme update failed');
		$this->strings['process_success']     = SP()->primitives->admin_text('Theme updated successfully');
	}

	/**
	 * This method replaces the WP upgrader class install_strings() with our theme upgrade strings.
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
		$this->strings['installing_package'] = SP()->primitives->admin_text('Installing the theme...');
		$this->strings['process_failed']     = SP()->primitives->admin_text('SP Theme install failed');
		$this->strings['process_success']    = SP()->primitives->admin_text('SP Theme installed successfully');
	}

	/**
	 * This method replaces the WP upgrader class install() with our theme upgrade code.
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

		$info    = pathinfo($package);
		$options = array('package'       => $package, 'destination' => SPTHEMEBASEDIR.$info['filename'], 'clear_destination' => false, # Do not overwrite files.
						 'clear_working' => true);

		$this->run($options);

		remove_filter('upgrader_source_selection', array(&$this, 'check_package'));

		if (!$this->result || is_wp_error($this->result)) return $this->result;

		# Force refresh of theme update information
		delete_site_transient('sp_update_themes');

		return true;
	}

	/**
	 * This method replaces the WP upgrader class bulk_upgrade() with our plugin theme upgrade code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $themes themes being bulk upgraded
	 *
	 * @return array|bool
	 */
	public function bulk_upgrade($themes) {
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

		$this->update_count   = count($themes);
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

			require_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
			$theme_file             = SPTHEMEBASEDIR.$theme.'/spTheme.txt';
			$this->skin->theme_info = SP()->theme->get_data($theme_file);

			$options = array('package' => $r->package, 'destination' => dirname($theme_file), 'clear_destination' => true, 'clear_working' => true, 'hook_extra' => array('theme' => $theme));

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
		remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10);

		# Force refresh of theme update information
		delete_site_transient('sp_update_themes');

		return $results;
	}

	/**
	 * This method replaces the WP upgrader class check_package() with our theme package checking code.
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

		# Check the folder contains a valid theme
		$working_directory = str_replace($wp_filesystem->wp_content_dir(), trailingslashit(WP_CONTENT_DIR), $source);
		if (!is_dir($working_directory)) return $source;# Sanity check, if the above fails, lets not prevent installation.
		# A proper archive should have an spTheme.txt file in the single subdirectory
		if (!file_exists($working_directory.'spTheme.txt')) return new WP_Error('incompatible_archive', $this->strings['incompatible_archive'], SP()->primitives->admin_text('The theme is missing the spTheme.txt file'));

		$info = get_file_data($working_directory.'spTheme.txt', array('Name' => 'Simple:Press Theme Title'));

		if (empty($info['Name'])) return new WP_Error('incompatible_archive', $this->strings['incompatible_archive'], SP()->primitives->admin_text("The spTheme.txt stylesheet doesn't contain a valid theme header"));

		return $source;
	}

	/**
	 * This method replaces the WP upgrader class theme_info() with our theme info code.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return array|bool
	 */
	public function theme_info($theme = null) {
		if (empty($theme)) {
			if (!empty($this->result['destination_name'])) {
				$theme = $this->result['destination_name'];
			} else {
				return false;
			}
		}

		return SP()->theme->get_data($theme);
	}

	/**
	 * This method replaces the WP upgrader class delete_old_theme() with our theme delete code.
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
	 * @return bool
	 */
	public function delete_old_theme($removed, $local_destination, $remote_destination, $theme) {
		global $wp_filesystem;

		$theme = isset($theme['theme']) ? $theme['theme'] : '';

		if (is_wp_error($removed) || empty($theme)) return $removed;# Pass errors through

		$themes_dir = $wp_filesystem->find_folder(SPTHEMEBASEDIR);
		if ($wp_filesystem->exists($themes_dir.$theme)) {
			if (!$wp_filesystem->delete($themes_dir.$theme, true)) return false;
		}

		return true;
	}
}