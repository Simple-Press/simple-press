<?php
/**
 * Admin updater support functions
 * Loads when a user upgrading Simple Press plugins or themes.
 *
 * $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 * $Rev: 15817 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function checks and displays messages when install, upgrade or uninstall is pending.
 *
 * @since 6.0
 *
 * @param string	$plugin		current plugin
 *
 * @return void
 */
function sp_plugins_check_sp_version($plugin) {
	if ($plugin == SP_FOLDER_NAME.'/sp-control.php') {
		# get wp admin screen type
		$screen = get_current_screen();

		$active_class = is_plugin_active($plugin) ? ' active' : '';

		if (SP()->options->get('sfuninstall')) {
			if (!$screen->is_network) {
				echo '<tr class="plugin-update-tr'.$active_class.'">';
				echo '<td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt"><p>';
				echo '<span style="color:red;font-weight:bold;">'.SP()->primitives->admin_text('Simple:Press is READY TO BE REMOVED. When you deactivate it ALL DATA WILL BE DELETED').'</span>';
				echo '</p></div></td></tr>';
			}
		} else if (SP()->core->status == 'Upgrade') {
			if (!$screen->is_network) {
				echo '<tr class="plugin-update-tr'.$active_class.'">';
				echo '<td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';
				echo SP()->primitives->admin_text_noesc('Your Simple:Press version needs updating. <a href="'.SPADMINUPGRADE.'">Upgrade now</a>.');
				echo '</p></div></td></tr>';
			}
		} else {
			$flag = (SP()->core->status == 'Install') ? false : true;
			$xml = sp_load_version_xml($flag, $flag);
			if (empty($xml)) return;

			$installed_build = SP()->options->get('sfbuild');
			if (empty($installed_build)) return;

			if ($xml->core->build > $installed_build) {
				echo '<tr class="plugin-update-tr'.$active_class.'" id="simple-press-update" data-slug="simple-press" data-plugin="'.$plugin.'">';
				echo '<td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>';

				$details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin='.SP_FOLDER_NAME.'&section=changelog&TB_iframe=true&width=600&height=800');

				printf(SP()->primitives->admin_text_noesc('There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.'), 'Simple:Press', esc_url($details_url), sprintf('class="thickbox open-plugin-details-modal" aria-label="%s"', esc_attr(sprintf(SP()->primitives->admin_text_noesc('View %1$s version %2$s details'), 'Simple:Press', $xml->core->version))
					), $xml->core->version, wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=').$plugin, 'upgrade-plugin_'.$plugin), sprintf('class="update-link" aria-label="%s"', esc_attr(sprintf(SP()->primitives->admin_text_noesc('Update %s now'), 'Simple:Press'))
					)
				);

				echo '</p></div></td></tr>';
			}
		}
	}
}

/**
 * This function updates the number of plugin and theme updates available to account
 * for Simple Press plugins and themes.
 *
 * @since 6.0
 *
 * @return array
 */
function sp_update_wp_counts($counts, $titles) {
	$pup = get_site_transient('sp_update_plugins');
	$tup = get_site_transient('sp_update_themes');
	if (!empty($pup) || !empty($tup)) {
		if (!empty($pup->response)) {
			$num = count($pup->response);
			$counts['counts']['plugins'] = $counts['counts']['plugins'] + $num;
			$titles['sp_plugins'] = sprintf(_n('%d Simple:Press Plugin Update', '%d Simple:Press Plugin Updates', $num), $num);
		}
		if (!empty($tup->response)) {
			$num = count($tup->response);
			$counts['counts']['themes'] = $counts['counts']['themes'] + $num;
			$titles['sp_themes'] = sprintf(_n('%d Simple:Press Theme Update', '%d Simple:press Theme Updates', $num), $num);
		}

		$counts['counts']['total'] = $counts['counts']['plugins'] + $counts['counts']['themes'] + $counts['counts']['wordpress'];
		$counts['title'] = ($titles) ? esc_attr(implode(', ', $titles)) : '';
	}

	return $counts;
}

/**
 * This function displays the button for updating Simple Press plugins.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_update_plugins() {
	if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();

	check_admin_referer('upgrade-core');

	if (isset($_GET['plugins'])) {
		$plugins = explode(',', sanitize_text_field($_GET['plugins']));
	} else if (isset($_POST['checked'])) {
		$plugins = array_map('sanitize_text_field', (array) $_POST['checked']);
	} else {
		wp_redirect(admin_url('update-core.php'));
		exit;
	}

	$url = 'update.php?action=update-sp-plugins&amp;plugins='.urlencode(implode(',', $plugins));
	$url = wp_nonce_url($url, 'bulk-update-sp-plugins');

	require_once ABSPATH.'wp-admin/admin-header.php';
	echo '<div class="wrap">';
	echo '<h2>'.SP()->primitives->admin_text('Update SP Plugins').'</h2>';
	echo "<iframe src='$url' style='width: 100%; height: 100%; min-height: 750px;' frameborder='0'></iframe>";
	echo '</div>';
	require_once ABSPATH.'wp-admin/admin-footer.php';
}

/**
 * This function kicks off the update of Simple Press plugins.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_do_plugins_update() {
	if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();

	check_admin_referer('bulk-update-sp-plugins');

	if (isset($_GET['plugins'])) {
		$plugins = explode(',', stripslashes($_GET['plugins']));
	} else if (isset($_POST['checked'])) {
		$plugins = array_map('sanitize_text_field', (array) $_POST['checked']);
	} else {
		$plugins = array();
	}

	$plugins = array_map('urldecode', $plugins);
	$url = 'update.php?action=update-sp-plugins&amp;plugins='.urlencode(implode(',', $plugins));
	$url = wp_nonce_url($url, 'bulk-update-sp-plugins');

	wp_enqueue_script('jquery');
	iframe_header();

	$upgrader = new spcPluginUpgrader(new spcPluginUpgraderSkin(compact('nonce', 'url')));
	$upgrader->bulk_upgrade($plugins);

	iframe_footer();
}

/**
 * This function displays the button for upgrading Simple Press themes.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_update_themes() {
	if (!SP()->auths->current_user_can('SPF Manage Themes')) die();

	check_admin_referer('upgrade-core');

	if (isset($_GET['themes'])) {
		$themes = explode(',', sanitize_text_field($_GET['themes']));
	} else if (isset($_POST['checked'])) {
		$themes = array_map('sanitize_text_field', (array) $_POST['checked']);
	} else {
		wp_redirect(admin_url('update-core.php'));
		exit;
	}

	$url = 'update.php?action=update-sp-themes&amp;themes='.urlencode(implode(',', $themes));
	$url = wp_nonce_url($url, 'bulk-update-sp-themes');

	require_once ABSPATH.'wp-admin/admin-header.php';
	echo '<div class="wrap">';
	echo '<h2>'.SP()->primitives->admin_text('Update SP Themes').'</h2>';
	echo "<iframe src='$url' style='width: 100%; height: 100%; min-height: 750px;' frameborder='0'></iframe>";
	echo '</div>';
	require_once ABSPATH.'wp-admin/admin-footer.php';
}

/**
 * This function kicks off the upgrade of Simple Press themes.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_do_themes_update() {
	if (!SP()->auths->current_user_can('SPF Manage Themes')) die();

	check_admin_referer('bulk-update-sp-themes');

	if (isset($_GET['themes'])) {
		$themes = explode(',', stripslashes($_GET['themes']));
	} else if (isset($_POST['checked'])) {
		$themes = array_map('sanitize_text_field', (array) $_POST['checked']);
	} else {
		$themes = array();
	}

	$themes = array_map('urldecode', $themes);

	$url = 'update.php?action=update-sp-themes&amp;plugins='.urlencode(implode(',', $themes));
	$url = wp_nonce_url($url, 'bulk-update-sp-themes');

	wp_enqueue_script('jquery');
	iframe_header();

	$upgrader = new spcThemeUpgrader(new spcThemeUpgraderSkin(compact('nonce', 'url')));
	$upgrader->bulk_upgrade($themes);

	iframe_footer();
}

/**
 * This function allow for uploading of a Simple Press plugin.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_do_plugin_upload() {
	if (!SP()->auths->current_user_can('SPF Manage Plugins')) die();

	check_admin_referer('forum-plugin_upload', 'forum-plugin_upload');

	$file_upload = new File_Upload_Upgrader('pluginzip', 'package');

	require_once ABSPATH.'wp-admin/admin-header.php';

	$title = sprintf(SP()->primitives->admin_text('Uploading SP Plugin from uploaded file: %s'), basename($file_upload->filename));
	$nonce = 'plugin-upload';
	$url = add_query_arg(array(
		'package' => $file_upload->id), 'update.php?action=upload-sp-plugin');
	$type = 'upload';
	$upgrader = new spcPluginUpgrader(new spcPluginInstallerSkin(compact('type', 'title', 'nonce', 'url')));
	$result = $upgrader->install($file_upload->package);

	if ($result || is_wp_error($result)) $file_upload->cleanup();

	# double check if we deleted the upload file and output message if not
	if (file_exists($file_upload->package)) echo sprintf(SP()->primitives->admin_text('Notice: Unable to remove the uploaded plugin zip archive: %s'), $file_upload->package);

	require_once ABSPATH.'wp-admin/admin-footer.php';
}

/**
 * This function allows for uploading of a Simple Press theme..
 *
 * @since 6.0
 *
 * @return void
 */
function sp_do_theme_upload() {
	if (!SP()->auths->current_user_can('SPF Manage Themes')) die();

	check_admin_referer('forum-theme_upload', 'forum-theme_upload');

	$file_upload = new File_Upload_Upgrader('themezip', 'package');

	require_once ABSPATH.'wp-admin/admin-header.php';

	$title = sprintf(SP()->primitives->admin_text('Uploading SP Theme from uploaded file: %s'), basename($file_upload->filename));
	$nonce = 'theme-upload';
	$url = add_query_arg(array(
		'package' => $file_upload->id), 'update.php?action=upload-sp-theme');
	$type = 'upload';
	$upgrader = new spcThemeUpgrader(new spcThemeInstallerSkin(compact('type', 'title', 'nonce', 'url')));
	$result = $upgrader->install($file_upload->package);

	if ($result || is_wp_error($result)) $file_upload->cleanup();

	# double check if we deleted the upload file and output message if not
	if (file_exists($file_upload->package)) echo sprintf(SP()->primitives->admin_text('Notice: Unable to remove the uploaded theme zip archive: %s'), $file_upload->package);

	require_once ABSPATH.'wp-admin/admin-footer.php';
}
