<?php
/**
 * Theme installer class.
 * Extends the WP theme installer class.
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

class spcThemeInstallerSkin extends Theme_Installer_Skin {
	/**
	 * This method replaces the WP installer class after() method with option to return to SP themes admin panel.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function after() {
		$theme_info = $this->upgrader->theme_info();

		$install_actions                = array();
		$install_actions['themes_page'] = '<a href="'.SPADMINTHEMES.'" title="'.esc_attr(SP()->primitives->admin_text('Return to SP themes page')).'" target="_parent">'.SP()->primitives->admin_text('Return to SP themes page').'</a>';
		$install_actions                = apply_filters('sph_install_theme_actions', $install_actions, $theme_info);
		if (!empty($install_actions)) $this->feedback(implode(' | ', (array)$install_actions));
	}
}