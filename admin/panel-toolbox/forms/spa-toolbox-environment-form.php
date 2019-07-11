<?php
/*
Simple:Press
Admin Toolbox Environmental Info Form
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_environment_form() {
    #== ENVIRONMENT INFO Tab ==========================================================
	global $wp_version, $wpdb;

	require_once ABSPATH.'wp-admin/includes/plugin.php';

	$theme = wp_get_theme();
	$wp_plugins = get_plugins();
	$sp_plugins = SP()->plugin->get_list();

	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('Environment'), true);
		spa_paint_open_panel();
		spa_paint_open_fieldset(SP()->primitives->admin_text('Environment'), false);

			echo '<div id="sp-environment-data">';
			echo '<table class="widefat sf-table-small">';

			spa_env_open(SP()->primitives->admin_text('Simple:Press'));
			spa_env_info(SP()->primitives->admin_text('Version'), SPVERSION);
			spa_env_info(SP()->primitives->admin_text('Build'), SPBUILD);
			spa_env_info(SP()->primitives->admin_text('Release'), SPRELEASE);
			spa_env_close();

			spa_env_open(SP()->primitives->admin_text('WordPress'));
			spa_env_info(SP()->primitives->admin_text('Version'), $wp_version);
			spa_env_info(SP()->primitives->admin_text('Language'), get_bloginfo('language'));
			spa_env_info(SP()->primitives->admin_text('Character Set'), get_bloginfo('charset'));
			spa_env_info(SP()->primitives->admin_text('Theme'), $theme->get('Name'));
			spa_env_close();

			spa_env_open(SP()->primitives->admin_text('PHP'));
			spa_env_info(SP()->primitives->admin_text('Version'), phpversion());
			spa_env_info(SP()->primitives->admin_text('Memory'), ini_get('memory_limit'));
			spa_env_info(SP()->primitives->admin_text('Max Upload'), ini_get('upload_max_filesize'));
			spa_env_info(SP()->primitives->admin_text('Timeout'), ini_get('user_ini.cache_ttl'));
			spa_env_close();

			spa_env_open(SP()->primitives->admin_text('MySQL'));
			spa_env_info(SP()->primitives->admin_text('Version'), $wpdb->db_version());
			spa_env_info(SP()->primitives->admin_text('Prefix'), $wpdb->prefix);
			spa_env_close();

			spa_env_open(SP()->primitives->admin_text('Server'));
			spa_env_info(SP()->primitives->admin_text('Version'), $_SERVER['SERVER_SOFTWARE']);
			spa_env_close();

			spa_env_open(SP()->primitives->admin_text('WP Plugins'));
			foreach (array_keys($wp_plugins) as $key) {
				if (is_plugin_active($key)) {
				$plugin = $wp_plugins[$key];
					spa_env_list($plugin['Name'], $plugin['Version']);
				}
			}
			spa_env_close();

			spa_env_open(SP()->primitives->admin_text('SP Plugins'));
	    	foreach ((array) $sp_plugins as $plugin_file => $plugin_data) {
        	    if (SP()->plugin->is_active($plugin_file)) {
					spa_env_list($plugin_data['Name'], $plugin_data['Version']);
				}
			}
			spa_env_close();

			echo '</table>';
			echo '</div>';
		spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_toolbox_environment_panel');

		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}

function spa_env_open($text) {
	echo "<tr><td><p><b>$text</b></p><td><td><p>";
}

function spa_env_info($label, $text) {
	echo "$label: <b>$text</b><br />";
}

function spa_env_list($item, $version) {
	echo "<b>$item</b> ($version)<br />";
}

function spa_env_close() {
	echo '<br /></p></td></tr>';
}
