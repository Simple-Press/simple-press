<?php
/**
 * Plugin installer class.
 * Extends the WP plugin installer class.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * after()
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */

require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';

class spcPluginInstallerSkin extends Plugin_Installer_Skin {
	/**
	 * This method replaces the WP installer class after() method with option to return to SP plugins admin panel.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function after() {
		$plugin_file = $this->upgrader->plugin_info();

		$install_actions                 = array();
		$install_actions['plugins_page'] = '<a href="'.SPADMINPLUGINS.'" title="'.esc_attr(SP()->primitives->admin_text('Return to SP Plugins page')).'" target="_parent">'.SP()->primitives->admin_text('Return to SP plugins page').'</a>';
		$install_actions                 = apply_filters('sph_install_plugin_actions', $install_actions, $plugin_file);
		if (!empty($install_actions)) $this->feedback(implode(' | ', (array)$install_actions));
	}
}