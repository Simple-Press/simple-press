<?php
/**
 * Plugin bulk upgrader class.
 * Extends the WP bulk upgrader class.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * add_strings()
 * before()
 * after()
 * bulk_footer()
 *
 * $LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
 * $Rev: 15817 $
 */

require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';

class spcPluginUpgraderSkin extends Bulk_Upgrader_Skin {
	/**
	 *
	 * @var array    current plugin being upgraded info
	 *
	 * @since 6.0
	 */
	public $plugin_info = array();

	/**
	 * Constructor
	 */
	public function __construct($args = array()) {
		parent::__construct($args);
	}

	/**
	 * This method replaces the WP bulk upgrade class add_strings() with our strings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function add_strings() {
		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = SP()->primitives->admin_text('Updating Plugin %1$s (%2$d/%3$d)');
	}

	/**
	 * This method replaces the WP bulk upgrade class before() with our strings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function before($title = '') {
		parent::before($this->plugin_info['Name']);
	}

	/**
	 * This method replaces the WP bulk upgrade class after() with our strings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function after($title = '') {
		parent::after($this->plugin_info['Name']);
	}

	/**
	 * This method replaces the WP bulk upgrade class bulk_footer() with our strings and upgrade DB url.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function bulk_footer() {
		parent::bulk_footer();
		$update_actions = array('plugins_page' => '<a href="'.admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins.php').'" title="'.SP()->primitives->admin_text('Go to SP plugins page').'" target="_parent">'.SP()->primitives->admin_text('Go to SP plugins page').'</a>', 'updates_page' => '<a href="'.self_admin_url('update-core.php').'" title="'.SP()->primitives->admin_text('Go to WordPress updates page').'" target="_parent">'.SP()->primitives->admin_text('Return to WordPress updates').'</a>');

		$update_actions = apply_filters('sph_update_bulk_plugins_complete_actions', $update_actions, $this->plugin_info);
		if (!empty($update_actions)) $this->feedback(implode(' | ', (array)$update_actions));
	}
}