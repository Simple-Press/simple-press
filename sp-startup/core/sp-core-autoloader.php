<?php
/**
 * Core autoloader
 * This file registers our php autoloader for classes.
 *
 *  $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 *  $Rev: 15704 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function runs the php autoloader registration
 *
 * @since 6.0
 *
 * @return void
 */
function sp_autoloader() {
	# register our php class autoloading
	spl_autoload_register(function ($class) {
		# autload loader classes
		if (is_file(SPBOOT.'sp-load-class-'.strtolower($class).'.php')) {
			require_once SPBOOT.'sp-load-class-'.strtolower($class).'.php';
		}

		# admin upgarder classes
		if (is_file(SPBOOT.'admin/spa-admin-class-'.strtolower($class).'.php')) {
			require_once SPBOOT.'admin/spa-admin-class-'.strtolower($class).'.php';
		}

		# autload API classes
		if (is_file(SPAPI.'sp-api-class-'.strtolower($class).'.php')) {
			require_once SPAPI.'sp-api-class-'.strtolower($class).'.php';
		}

		# autload view classes
		if (is_file(SP_PLUGIN_DIR.'/forum/content/classes/sp-view-class-'.strtolower($class).'.php')) {
			require_once SP_PLUGIN_DIR.'/forum/content/classes/sp-view-class-'.strtolower($class).'.php';
		}
	});
}
