<?php
/**
 * Admin updater support functions
 * Loads when a user upgrading Simple Press plugins or themes.
 *
 * $LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
 * $Rev: 15797 $
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
	if ($plugin == 'simple-press/sp-control.php') {
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

				$details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=simple-press&section=changelog&TB_iframe=true&width=600&height=800');

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
 * This function for any updates to Simple Press plugins.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_update_check_sp_plugins() {
	$xml = sp_load_version_xml();
	if ($xml) {
		$plugins = SP()->plugin->get_list();
		if (empty($plugins)) return;

		$up = new stdClass;
		$update = false;
		$header = true;
		foreach ($plugins as $file => $installed) {
			foreach ($xml->plugins->plugin as $latest) {
				if ($installed['Name'] == $latest->name) {
					if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
						if ($header) {
							$form_action = 'update-core.php?action=do-sp-plugin-upgrade';
							?>
							<h3><?php SP()->primitives->admin_etext('Simple:Press Plugins'); ?></h3>
							<p><?php SP()->primitives->admin_etext('The following plugins have new versions available. Check the ones you want to update and then click Update SP Plugin'); ?></p>
							<p><?php SP()->primitives->admin_etext('Please Note: Any customizations you have made to plugin files will be lost'); ?></p>
							<form method="post" action="<?php echo $form_action; ?>" name="upgrade-sp-plugins" class="upgrade">
								<?php wp_nonce_field('upgrade-core'); ?>
								<p><input id="upgrade-themes" class="button" type="submit" value="<?php SP()->primitives->admin_etext('Update SP Plugins'); ?>" name="upgrade" /></p>
								<table class="widefat" id="update-themes-table">
									<thead>
										<tr>
											<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all" /></th>
											<th scope="col" class="manage-column"><label for="themes-select-all"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-2" /></th>
											<th scope="col" class="manage-column"><label for="themes-select-all-2"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
										</tr>
									</tfoot>
									<tbody class="plugins">
										<?php
										$header = false;
									}
									echo "
									<tr class='active'>
									<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($file)."' /></th>
									<td><strong>{$installed['Name']}</strong><br />".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $installed['Version'], $latest->version, $latest->requires).'</td>
									</tr>';
									$data = new stdClass;
									$data->slug = $file;
									$data->new_version = (string) $latest->version;
									$data->url = 'https://simple-press.com';
									$data->package = ((string) $latest->archive).'&wpupdate=1';
									$up->response[$file] = $data;
									$update = true;
								}
							}
						}
					}

					# any plugins to update?
					if ($update) {
						set_site_transient('sp_update_plugins', $up);
					} else {
						delete_site_transient('sp_update_plugins');
					}

					if (!$header) {
						?>
					</tbody>
				</table>
				<p><input id="upgrade-themes-2" class="button" type="submit" value="<?php SP()->primitives->admin_etext('Update SP Plugins'); ?>" name="upgrade" /></p>
			</form>
			<?php
		} else {
			echo '<h3>'.SP()->primitives->admin_text('Simple:Press Plugins').'</h3>';
			echo '<p>'.SP()->primitives->admin_text('Your SP plugins are all up to date').'</p>';
		}
	}
}

/**
 * This function checks for any updates to Simple Press themes.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_update_check_sp_themes() {
	$xml = sp_load_version_xml();
	if ($xml) {
		require_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
		$themes = SP()->theme->get_list();
		if (empty($themes)) return;

		$up = new stdClass;
		$update = false;
		$header = true;
		foreach ($themes as $file => $installed) {
			foreach ($xml->themes->theme as $latest) {
				if ($installed['Name'] == $latest->name) {
					if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
						if ($header) {
							$form_action = 'update-core.php?action=do-sp-theme-upgrade';
							?>
							<h3><?php SP()->primitives->admin_etext('Simple:Press Themes'); ?></h3>
							<p><?php SP()->primitives->admin_etext('The following themes have new versions available. Check the ones you want to update and then click Update Themes.'); ?></p>
							<p><?php echo '<b>'.SP()->primitives->admin_text('Please Note:').'</b> '.SP()->primitives->admin_text('Any customizations you have made to theme files will be lost.'); ?></p>
							<form method="post" action="<?php echo $form_action; ?>" name="upgrade-themes" class="upgrade">
								<?php wp_nonce_field('upgrade-core'); ?>
								<p><input id="upgrade-themes" class="button" type="submit" value="<?php SP()->primitives->admin_etext('Update SP Themes'); ?>" name="upgrade" /></p>
								<table class="widefat" id="update-themes-table">
									<thead>
										<tr>
											<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all" /></th>
											<th scope="col" class="manage-column"><label for="themes-select-all"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-2" /></th>
											<th scope="col" class="manage-column"><label for="themes-select-all-2"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
										</tr>
									</tfoot>
									<tbody class="plugins">
										<?php
										$header = false;
									}
									$screenshot = SPTHEMEBASEURL.$file.'/'.$installed['Screenshot'];
									echo "
							<tr class='active'>
							<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($file)."' /></th>
							<td class='plugin-title'><img src='$screenshot' width='64' height='64' style='float:left; padding: 5px' /><strong>{$installed['Name']}</strong>".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $installed['Version'], $latest->version, $latest->requires)."</td>
							</tr>";
									$data = new stdClass;
									$data->slug = $file;
									$data->stylesheet = $installed['Stylesheet'];
									$data->new_version = (string) $latest->version;
									$data->url = 'https://simple-press.com';
									$data->package = ((string) $latest->archive).'&wpupdate=1';
									$up->response[$file] = $data;
									$update = true;
								}
							}
						}
					}

					# any themes to update?
					if ($update) {
						set_site_transient('sp_update_themes', $up);
					} else {
						delete_site_transient('sp_update_themes');
					}

					if (!$header) {
						?>
					</tbody>
				</table>
				<p><input id="upgrade-themes-2" class="button" type="submit" value="<?php SP()->primitives->admin_etext('Update SP Themes'); ?>" name="upgrade" /></p>
			</form>
			<?php
		} else {
			echo '<h3>'.SP()->primitives->admin_text('Simple:Press Themes').'</h3>';
			echo '<p>'.SP()->primitives->admin_text('Your SP themes are all up to date').'</p>';
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
