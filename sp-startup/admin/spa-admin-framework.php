<?php
/**
 * Admin framework functions
 * Loads for all forum admin pages.
 *
 * $LastChangedDate: 2018-11-13 22:52:58 -0600 (Tue, 13 Nov 2018) $
 * $Rev: 15821 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');


function spa_enqueue_datepicker() {
	
	$spAdminUIStyleUrl = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPADMINCSS.'jquery-ui.css' : SPADMINCSS.'jquery-ui.min.css';
	wp_register_style('spAdminUIStyle', $spAdminUIStyleUrl, array(), SP_SCRIPTS_VERSION);
	wp_enqueue_style('spAdminUIStyle');
	
	wp_enqueue_script( 'jquery-ui-datepicker', false, array('jquery') );
}


function spa_enqueue_font_icon_picker() {
	
	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPAJSCRIPT.'jquery.fonticonpicker.js' : SPAJSCRIPT.'jquery.fonticonpicker.min.js';
	
	
	wp_enqueue_script('sffonticonpicker', $script, array(
			'jquery',
			), SP_SCRIPTS_VERSION, false);
	
	
	$theme_css = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPADMINCSS . 'bootstrap-theme/jquery.fonticonpicker.bootstrap.css' : SPADMINCSS . 'bootstrap-theme/jquery.fonticonpicker.bootstrap.min.css';
	
	wp_enqueue_style( 'jquery.fonticonpicker-css', SPADMINCSS . 'jquery.fonticonpicker.min.css', array(), SP_SCRIPTS_VERSION );
	wp_enqueue_style( 'jquery.fonticonpicker.bootstrap-css', $theme_css, array(), SP_SCRIPTS_VERSION );
	
}

/**
 * This function registers and enqueues the admin CSS style.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_load_admin_css() {
	$spAdminStyleUrl = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPADMINCSS.'spa-admin.css' : SPADMINCSS.'spa-admin.min.css';
	wp_register_style('spAdminStyle', $spAdminStyleUrl, array(), SP_SCRIPTS_VERSION);
	
	wp_enqueue_style('spAdminStyle');
	wp_enqueue_style('farbtastic');
}

/**
 * This function registers and enqueues the admin javascript files.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_load_admin_scripts() {
	global $activePanel;

	if (!SP()->isForumAdmin) return;

	SP()->admin->adminPage = spa_extract_admin_page();

	if (isset($_GET['panel'])) $activePanel = urldecode(SP()->filters->str($_GET['panel']));

	if (SP()->core->status == 'ok') {
		if (SP()->admin->adminPage != 'notice') {
			do_action('sph_scripts_admin_start');

			$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPAJSCRIPT.'ajaxupload/ajaxupload.js' : SPAJSCRIPT.'ajaxupload/ajaxupload.min.js';
			wp_enqueue_script('sfajaxupload', $script, array(
				'jquery'), SP_SCRIPTS_VERSION, false);

			$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPAJSCRIPT.'nested-sortable/jquery.ui.nested.js' : SPAJSCRIPT.'nested-sortable/jquery.ui.nested.min.js';
			wp_enqueue_script('sfanestedsortable', $script, array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-sortable'), SP_SCRIPTS_VERSION, false);
		}
		
		wp_enqueue_editor();
		
		spa_enqueue_datepicker();
		
		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPAJSCRIPT.'spa-admin.js' : SPAJSCRIPT.'spa-admin.min.js';
		wp_enqueue_script('sfadmin', $script, array(
			'jquery',
			'jquery-form',
			'jquery-ui-accordion',
			'jquery-ui-sortable',
			'jquery-ui-tooltip'), SP_SCRIPTS_VERSION, false);
                
		$platform = array(
			'focus'			 => 'admin',
			'mobile'		 => SP()->core->mobile,
			'device'		 => SP()->core->device,
			'tooltips'		 => '1',
			'mobiletheme'	 => '0',
			'pWait'			 => '<img src="'.SPCOMMONIMAGES.'working.gif" />'.SP()->primitives->admin_text('Please Wait...')
		);
		$platform = apply_filters('sph_platform_vars', $platform);
		wp_localize_script('sfadmin', 'sp_platform_vars', $platform);

		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPCJSCRIPT.'sp-common.js' : SPCJSCRIPT.'sp-common.min.js';
		wp_enqueue_script('spcommon', $script, array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-dialog',
			'jquery-ui-progressbar'), SP_SCRIPTS_VERSION, false);
		wp_enqueue_script('jquery-touch-punch', false, array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-mouse'), false, false);

		# load up admin event handlers
		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPAJSCRIPT.'spa-admin-events.js' : SPAJSCRIPT.'spa-admin-events.min.js';
		wp_enqueue_script('spadminevents', $script, array(
			'jquery',
			'sfadmin',
			'spcommon'), SP_SCRIPTS_VERSION, false);
			
		wp_enqueue_script('farbtastic');

		do_action('sph_scripts_admin_end');

		# Add help text to WP admin bar help 'slider'
		get_current_screen()->add_help_tab(array(
			'id'		 => 'overview',
			'title'		 => 'overview',
			'callback'	 => 'spa_add_slider_help'));
	} else {
		# Install and Upgrade
		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SP_PLUGIN_URL.'/sp-startup/install/resources/jscript/sp-install.js' : SP_PLUGIN_URL.'/sp-startup/install/resources/jscript/sp-install.min.js';
		wp_enqueue_script('sfjs', $script, array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-progressbar'), SP_SCRIPTS_VERSION, false);
	}
	
	spa_enqueue_font_icon_picker();
}

/**
 * This function adds elements to the admin page head tags.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_admin_header() {
	if (!SP()->isForumAdmin) return;

	if (SP()->core->status == 'ok') {
		do_action('sph_admin_head_start');

		if (is_rtl()) {
			?>
			<link rel="stylesheet" href="<?php echo SPADMINCSS; ?>spa-admin-rtl.css" />
		<?php } ?>
		<style>
		<?php
		do_action('sph_add_style');
		?>
		</style>
		<?php
		do_action('sph_admin_head_end');
	}
}

/**
 * This function displays the panel header at the top of all forum admin pages.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_panel_header() {
	global $spNews;

	echo '<!-- Common wrapper and header -->';
	echo '<div class="wrap nosubsub">';
	echo '<div class="mainicon icon-forums"></div>';
	echo '<h1>'.SP()->primitives->admin_text('Simple:Press Administration').'</h1>';
	echo '<div class="clearboth"></div>';

	echo '<table class="sfamenutable" style="width:100%">';
	echo '<tr><td style="text-align:right">';

	$site = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'troubleshooting', 'troubleshooting'));
	$target = 'sfmaincontainer';
	echo '<input style="width: 175px; font-weight:bold;" type="button" id="spHelp" class="sf-button-primary spLeft spTroubleshoot" value="'.SP()->primitives->admin_text('Help & Troubleshooting').'" data-url="'.$site.'" data-target="'.$target.'" />&nbsp;&nbsp;&nbsp;';

	$site = wp_nonce_url(SPAJAXURL.'adminsearch', 'adminsearch');
	$target = 'sfmaincontainer';

	echo '<img style="margin: -1px 0 0 20px;float:left;" src="'.SPADMINIMAGES.'sp_Help32.png" alt="" />';
	echo '<input style="margin-left: 5px;font-weight:bold;" type="button" id="spSearch" class="sf-button-primary spLeft spTroubleshoot" value="'.SP()->primitives->admin_text('What do you need to do?').'" data-url="'.$site.'" data-target="'.$target.'" />&nbsp;&nbsp;&nbsp;';

	echo '<a class="sf-button" target="_blank" href="https://simple-press.com/documentation/installation/">'.SP()->primitives->admin_text('Simple:Press Online Documentation').'</a>&nbsp;&nbsp;&nbsp;';

	$site = wp_nonce_url(SPAJAXURL.'spAckPopup', 'spAckPopup');
	$title = SP()->primitives->admin_text('About Simple:Press');
	echo '<a class="sf-button spOpenDialog" data-site="'.$site.'" data-label="'.$title.'" data-width="600" data-height="0" data-align="center">'.$title.'</a>&nbsp;&nbsp;&nbsp;';
	
	echo '<a class="sf-button" target="_blank" href="https://wordpress.org/support/plugin/simplepress/reviews/#new-post">'.SP()->primitives->admin_text('Review Simple:Press').'</a>&nbsp;&nbsp;&nbsp;';	
	
	echo '<a class="sf-button" href="'.SP()->spPermalinks->get_url().'">'.SP()->primitives->admin_text('Go To Forum').'</a>';

	echo '</td>';
	echo '</tr></table></div><div class="clearboth"></div>';

	# define container for the dialog box popup
	echo '<div id="dialogcontainer" style="display:none;"></div>';

	# display any warning messages and global 'cleanups'
	echo spa_check_warnings();

	# News update widget
	$spNews = spa_check_for_news();
	if (!empty($spNews)) {
		add_action('in_admin_footer', 'spa_remove_news');
		spa_dashboard_news();
	}

	do_action('sph_admin_panel_header');
}

/**
 * This function outputs display code in the admin footer displayed at bottom of forum admin pages.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_admin_footer() {

	if (SP()->isForumAdmin) echo SPPLUGHOME.' | '.SP()->primitives->admin_text('Version').' '.SPVERSION.'<br />';

	do_action('sph_admin_footer');
}

/**
 * This function registers and enqueues the admin javascript needed in the page footer.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_admin_footer_scripts() {
	global $sfactivepanels, $activePanel, $sfadminpanels;

	if (!SP()->isForumAdmin) return;

	if (SP()->core->status == 'ok') {
		if (SP()->admin->adminPage != 'notice') {
			/*if (isset($activePanel)) {
				$panel = (!empty($sfactivepanels[$activePanel])) ? $sfactivepanels[$activePanel] : 0;
			} else {
				$panel = (!empty($sfactivepanels[SP()->admin->adminPage])) ? $sfactivepanels[SP()->admin->adminPage] : 0;
			}*/

			$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SPAJSCRIPT.'spa-admin-footer.js' : SPAJSCRIPT.'spa-admin-footer.min.js';
			wp_enqueue_script('sfadminfooter', $script, array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-accordion',
				'jquery-ui-tooltip'), SP_SCRIPTS_VERSION, true);

			/*$admin = array(
				'panel'		 => $panel,
				'panel_name' => $sfadminpanels[$panel][0]
			);
			$admin = apply_filters('sp_admin_footer_vars', $admin);
			wp_localize_script('sfadminfooter', 'sp_admin_footer_vars', $admin);*/
		}
	}
}

/**
 * This function displays and desired footer information right below the current admin panel
 *
 * @since 6.0
 *
 * @return void
 */
function spa_panel_footer() {

}

/**
 * This function displays the forum admin accordion menu.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_render_sidemenu() {
	global $sfadminpanels;

	$target = 'sfmaincontainer';
	$image = SPADMINIMAGES;
	$upgrade = admin_url('admin.php?page='.SPINSTALLPATH);

	if (isset($_GET['tab']) ? $formid = SP()->filters->str($_GET['tab']) : $formid = '') ;

	if (SP()->core->device == '_mobile') { // @TODO admin design (no used)
		echo '<div id="spaMobileAdmin">'."\n";
		echo '<select class="wp-core-ui" onchange="location = this.options[this.selectedIndex].value;">'."\n";
		foreach ($sfadminpanels as $index => $panel) {
			if (SP()->auths->current_user_can($panel[1]) || ($panel[0] == 'Admins' && (SP()->user->thisUser->admin || SP()->user->thisUser->moderator))) {
				echo '<optgroup label="'.$panel[0].'">'."\n";
				foreach ($panel[6] as $label => $data) {
					foreach ($data as $formid => $reload) {
						# ignore user plugin data for menu
						if ($formid == 'admin' || $formid == 'save' || $formid == 'form') continue;
						if ($reload != '') {
							$id = ' id="'.esc_attr($reload).'"';
						} else {
							$id = ' id="acc'.esc_attr($formid).'"';
						}
						$sel = '';
						if (isset($_GET['tab'])) {
							if (sanitize_text_field($_GET['tab']) == 'plugin') {
								if (isset($_GET['admin']) && isset($data['admin']) && sanitize_text_field($_GET['admin']) == $data['admin']) $sel = ' selected="selected" ';
							} else if (sanitize_text_field($_GET['tab']) == $formid) {
								$sel = ' selected="selected" ';
							}
						}
						echo "<option $id $sel";
						$admin = (!empty($data['admin']) ? '&admin='.$data['admin'] : '');
						$save = (!empty($data['save']) ? '&save='.$data['save'] : '');
						$form = (!empty($data['form']) ? '&form='.$data['form'] : '');

						if (empty($admin)) {
							$base = SPHOMEURL.'wp-admin/admin.php?page=';
						} else {
							$base = SPHOMEURL.'wp-admin/admin.php?page='.SP_FOLDER_NAME.'/admin/panel-plugins/spa-plugins.php';
							$panel[2] = '';
						}

						$http = $base.$panel[2].'&tab='.$formid.$admin.$save.$form;
						echo 'value="'.$http.'">'.$label.'</option>'."\n";
					}
				}
				echo '</optgroup>'."\n";
			}
		}
		echo '</select>'."\n";
		echo '<a class="sf-button-secondary" href="'.SP()->spPermalinks->get_url().'">'.SP()->primitives->admin_text('Go To Forum').'</a>';
		echo '</div>'."\n";
	} else {
		echo '<div id="sfsidepanel">'."\n";
                
                echo '<span class="sf-tooggle-admin-menu">'."\n";
                echo '<span class="sf-button sf-hide">'."\n";
                echo __('Hide Admin Menu', 'sp');
                echo '</span>'."\n";
                echo '<span class="sf-button sf-show">'."\n";
                echo __('Show Admin Menu', 'sp');
                echo '</span>'."\n";
                echo '</span>'."\n";
                
		echo '<div id="sfadminmenu">'."\n";
		foreach ($sfadminpanels as $index => $panel) {
			if (SP()->auths->current_user_can($panel[1]) || ($panel[0] == 'Admins' && (SP()->user->thisUser->admin || SP()->user->thisUser->moderator))) {
				$pName = str_replace(' ', '', $panel[0]);
				echo '<div class="sfsidebutton" id="sfacc'.$pName.'">'."\n";
				echo '<div class="" title="'.esc_attr($panel[3]).'"><span class="sf-icon sf-'.$panel[4].' spa'.$panel[4].'"></span><a href="#">'.$panel[0].'</a></div>'."\n";
				echo '</div>'."\n";
				echo '<div class="sfmenublock">'."\n";

				foreach ($panel[6] as $label => $data) {
					foreach ($data as $formid => $reload) {
						# ignore user plugin data for menu
						if ($formid == 'admin' || $formid == 'save' || $formid == 'form') continue;
						echo '<div class="sfsideitem">'."\n";
						if ($reload != '') {
							$id = ' id="'.esc_attr($reload).'"';
						} else {
							$id = ' id="acc'.esc_attr($formid).'"';
						}
						$base = esc_attr($panel[5]);
						$admin = (!empty($data['admin']) ? $data['admin'] : '');
						$save = (!empty($data['save']) ? $data['save'] : '');
						$form = (!empty($data['form']) ? $data['form'] : '');
						?>
						<a<?php echo $id; ?> href="javascript:void(0);" class="spAccordionLoadForm" data-form="<?php echo $formid; ?>" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="" data-open="open" data-upgrade="<?php echo $upgrade; ?>" data-admin="<?php echo $admin; ?>" data-save="<?php echo $save; ?>" data-sform="<?php echo $form; ?>" data-reload="<?php echo $reload; ?>"><?php echo $label; ?></a><?php echo "\n"; ?>
						<?php
					}
					echo '</div>'."\n";
				}
				echo '</div>'."\n";
			}
		}
		echo '</div>'."\n";

		echo '</div>'."\n";
	}
}

/**
 * This function checks if any warnings need to be displayed at the top of each forum admin panel.
 *
 * @since 6.0
 *
 * @return string	string containing any warnings to be displayed
 */
function spa_check_warnings() {
	# not perfect but we can use this call tyo perform any minor
	# cleanups that may be necessary... so
	# drop any existing temp members table...
	SP()->DB->execute('DROP TABLE IF EXISTS sftempmembers');

	$mess = '';
	$update = false;
	$update_msg = '';

	# check for plugins with updates
	$up = get_site_transient('sp_update_plugins');
	if (!empty($up)) {
		$msg = apply_filters('sph_plugins_update_notice', SP()->primitives->admin_text('There is one or more Simple:Press plugin updates available'));
		if (!empty($msg)) {
			$update = true;
			$update_msg .= $msg.'<br />';
		}
	}

	# check for themes with updates
	$up = get_site_transient('sp_update_themes');
	if (!empty($up)) {
		$msg = apply_filters('sph_themes_update_notice', SP()->primitives->admin_text('There is one or more Simple:Press theme updates available'));
		if (!empty($msg)) {
			$update = true;
			$update_msg .= $msg.'<br />';
		}
	}

	if ($update) {
		if (is_main_site()) {
			$mess .= apply_filters('sph_updates_notice', spa_message($update_msg.'<a href="'.self_admin_url('update-core.php').'">'.SP()->primitives->admin_text('Click here to view any updates.').'</a>'));
		} else {
			$mess .= apply_filters('sph_updates_notice', spa_message(SP()->primitives->admin_text('There are some Simple:Press updates avaialable. You may want to notify the network site admin.')));
		}
	}

	# output warning if no SPF admins are defined
	$a = SP()->core->forumData['forum-admins'];
	if (empty($a)) $mess .= spa_message(SP()->primitives->admin_text('Warning - There are no SPF admins defined!	 All WP admins now have SP backend access'), 'error');

	# Check if	desktop, tablet and mobile themes are selected and available
	$cur = SP()->options->get('sp_current_theme');
	if (empty($cur)) {
		$mess .= spa_message(SP()->primitives->admin_text('No main theme has been selected and SP will be unable to display correctly. Please select a theme from the Themes panel'), 'error');
	} else {
		$nostylesheet = !file_exists(SPTHEMEBASEDIR.$cur['theme'].'/styles/'.$cur['style']);
		$nooverlay = !empty($cur['color']) && !file_exists(SPTHEMEBASEDIR.$cur['theme'].'/styles/overlays/'.$cur['color'].'.php');
		$nopoverlay = !empty($cur['color']) && !empty($cur['parent']) && !file_exists(SPTHEMEBASEDIR.$cur['parent'].'/styles/overlays/'.$cur['color'].'.php');
		if ($nostylesheet || ($nooverlay && $nopoverlay)) {
			$mess .= spa_message(SP()->primitives->admin_text('Either the theme CSS file and/or color Overlay file from the selected theme is missing'), 'error');
		}
	}

	$mobile = SP()->options->get('sp_mobile_theme');
	if (!empty($mobile) && $mobile['active']) {
		$nostylesheet = !file_exists(SPTHEMEBASEDIR.$mobile['theme'].'/styles/'.$mobile['style']);
		$nooverlay = !empty($mobile['color']) && !file_exists(SPTHEMEBASEDIR.$mobile['theme'].'/styles/overlays/'.$mobile['color'].'.php');
		$nopoverlay = !empty($mobile['color']) && !empty($mobile['parent']) && !file_exists(SPTHEMEBASEDIR.$mobile['parent'].'/styles/overlays/'.$mobile['color'].'.php');
		if ($nostylesheet || ($nooverlay && $nopoverlay)) {
			$mess .= spa_message(SP()->primitives->admin_text('Either the mobile theme CSS file and/or color Overlay file from the selected mobile theme is missing'), 'error');
		}
	}

	$tablet = SP()->options->get('sp_tablet_theme');
	if (!empty($tablet) && $tablet['active']) {
		$nostylesheet = !file_exists(SPTHEMEBASEDIR.$tablet['theme'].'/styles/'.$tablet['style']);
		$nooverlay = !empty($tablet['color']) && !file_exists(SPTHEMEBASEDIR.$tablet['theme'].'/styles/overlays/'.$tablet['color'].'.php');
		$nopoverlay = !empty($tablet['color']) && !empty($tablet['parent']) && !file_exists(SPTHEMEBASEDIR.$tablet['parent'].'/styles/overlays/'.$tablet['color'].'.php');
		if ($nostylesheet || ($nooverlay && $nopoverlay)) {
			$mess .= spa_message(SP()->primitives->admin_text('Either the tablet theme CSS file and/or color Overlay file from the selected tablet theme is missing'), 'error');
		}
	}

	# check for missing default members user group
	$value = SP()->meta->get('default usergroup', 'sfmembers');
	$ugid = SP()->DB->table(SPUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
	if (empty($ugid)) $mess .= spa_message(SP()->primitives->admin_text('Warning - The default user group for new members is undefined!	Please visit the SP usergroups admin page, map users to usergroups tab and set the default user group'), 'error');

	# check for missing default guest user group
	$value = SP()->meta->get('default usergroup', 'sfguests');
	$ugid = SP()->DB->table(SPUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
	if (empty($ugid)) $mess .= spa_message(SP()->primitives->admin_text('Warning - The default user group for guests is undefined!  Please visit the SP usergroups admin page, map users to usergroups tab and set the default user group'), 'error');

	# check for unreachable forums because of permissions
	$usergroups = SP()->DB->table(SPUSERGROUPS);
	if ($usergroups) {
		$has_members = false;
		foreach ($usergroups as $usergroup) {
			$members = SP()->DB->table(SPMEMBERSHIPS, "usergroup_id=$usergroup->usergroup_id", 'row', '', '1');
			if ($members || $usergroup->usergroup_id == $value[0]['meta_value']) {
				$has_members = true;
				break;
			}
		}

		if (!$has_members) {
			$mess .= spa_message(SP()->primitives->admin_text('Warning - There are no usergroups that have members!	All forums may only be visible to SP admins'), 'error');
		}
	} else {
		$mess .= spa_message(SP()->primitives->admin_text('Warning - There are no usergroups defined!  All forums may only be visible to SP admins'), 'error');
	}

	$roles = sp_get_all_roles();
	if (!$roles) {
		$mess .= spa_message(SP()->primitives->admin_text('Warning - There are no permission sets defined!  All forums may only be visible to SP admins'), 'error');
	}

	# check if compatible with wp super cache
	if (function_exists('wp_cache_edit_rejected')) {
		global $cache_rejected_uri;
		$slug = '/'.SP()->options->get('sfslug').'/';
		if (isset($cache_rejected_uri)) {
			$found = false;
			foreach ($cache_rejected_uri as $value) {
				if ($value == $slug) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				$string = SP()->primitives->admin_text('WP Super Cache is not properly configured to work with Simple:Press. Please visit your WP Super Cache settings page and in the accepted filenames & rejected URIs section for the pages not to be cached input field, add the following string');
				$string .= ':</p><p><em>'.$slug.'</em></p><p>';
				$string .= SP()->primitives->admin_text('Then, please clear your WP Super Cache cache to remove any cached Simple:Press pages');
				$string .= ':</p><p><em>'.SP()->primitives->admin_text('For more information please see this').' <a href="https://simple-press.com/documentation/faq/troubleshooting/forum-displays-wrong-information/" target="_blank">'.SP()->primitives->admin_text('FAQ').'</a></p><p>';
				$mess .= spa_message($string, 'error');
			}
		}
	}

	# check if compatible with w3 total cache if installed (at leasst check for slug)
	if (defined('W3TC_CACHE_CONFIG_DIR') && function_exists('w3_get_blog_id')) {
		if (w3_get_blog_id() <= 0) {
			$f = W3TC_CACHE_CONFIG_DIR.'/master.php';
		} else {
			$f = W3TC_CACHE_CONFIG_DIR.'/'.sprintf('%06d', w3_get_blog_id()).'/master.php';
		}
		if (file_exists($f) && is_readable($f)) {
			$content = file_get_contents($f);
			$content = substr($content, 13);
			$config = array();
			if (is_serialized($content)) $config = @unserialize($content);
			if (is_array($config)) {
				if (key_exists('pgcache.reject.uri', $config) && !empty($config['pgcache.reject.uri'])) {
					$found = false;
					$slug = '/'.SP()->options->get('sfslug').'/';
					foreach ($config['pgcache.reject.uri'] as $i) {
						if ($i == $slug) {
							$found = true;
							break;
						}
					}

					if (!$found) {
						$string = SP()->primitives->admin_text('W3 Total Cache is not properly configured to work with Simple:Press. Please visit your W3 Total Cache settings page and in the accepted filenames & rejected URIs in ALL sections, add the following string');
						$string .= ':</p><p><em>'.$slug.'</em></p><p>';
						$string .= SP()->primitives->admin_text('Then, please clear your W3 Total Cache cache to remove any cached Simple:Press pages');
						$string .= ':</p><p><em>'.SP()->primitives->admin_text('For more information please see this').' <a href="https://simple-press.com/documentation/faq/troubleshooting/forum-displays-wrong-information/" target="_blank">'.SP()->primitives->admin_text('FAQ').'</a></em></p><p>';
						$mess .= spa_message($string, 'error');
					}
				}
			}
		}
	}

	# check for server-side UTC timezone
	$tz = get_option('timezone_string');
	if (empty($tz)) {
		$tz = 'UTC '.get_option('gmt_offset');
		$string = SP()->primitives->admin_text('You have set your server to use a UTC timezone setting');
		$string .= ':</p><p><em>'.$tz.'</em></p><p>';
		$string .= SP()->primitives->admin_text('UTC can give unpredictable results on forum post time stamps. Please select the city setting nearest to you in the WordPress - Settings - General admin page');
		$string .= ':</p><p><em>'.SP()->primitives->admin_text('For more information please see this').' <a href="https://simple-press.com/documentation/faq/troubleshooting/why-do-my-new-posts-show-as-posted-minus-seconds-ago/" target="_blank">'.SP()->primitives->admin_text('FAQ').'</a></p><p>';
		$mess .= spa_message($string, 'error');
	}

	return $mess;
}

/**
 * This function displays success/failure message for admin panel saves.
 *
 * @since 6.0
 *
 * @param string	$message	current header message
 * @param string	$status		status class added to message (updated, error, etc)
 *
 * @return string	updated message to be delivered
 */
function spa_message($message, $status = 'updated') {
	$out = "<div class='$status'>";
	if ($status == 'error') $out .= '<img class="spWait" src="'.SPADMINIMAGES.'sp_Message.png" alt="" />';
	$out .= "<p>$message</p>";
	$out .= "</div>";
	return $out;
}

/**
 * This function determines the current Simple Presa admin panel being displayed.
 *
 * @since 6.0
 *
 * @return string
 */
function spa_extract_admin_page() {
	if (strpos($_SERVER['QUERY_STRING'], 'spa-plugins.php') != 0) {
		SP()->admin->adminPage = '/spa-plugins.php';
	} else {
		SP()->admin->adminPage = strrchr($_SERVER['QUERY_STRING'], '/');
	}
	if (strpos(SP()->admin->adminPage, '/spa-', 0) === false) return 'none';
	SP()->admin->adminPage = substr(SP()->admin->adminPage, 5, 26);
	$x = explode('.php', SP()->admin->adminPage);
	SP()->admin->adminPage = reset($x);
	return SP()->admin->adminPage;
}

/**
 * This function add some slider help to admin panels.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_add_slider_help() {

	if (!SP()->isForumAdmin) return;
	$out = '';
	$out .= '<h5>'.SP()->primitives->admin_text('Simple:Press - Help Options').'</h5>';
	$out .= '<div class="metabox-prefs">';
	$out .= '<p>'.SP()->primitives->admin_text('For contextual help with Simple:Press, click on the Help buttons/links located on each administration panel').'<br />';
	$out .= SP()->primitives->admin_text('For Simple:Press information, troubleshooting, how-to, administration, our API and more, please visit our').' <a target="_blank" href="https://simple-press.com/documentation/installation/">'.SP()->primitives->admin_text('Simple:Press Online Documentation').'</a><br />';
	$out .= SP()->primitives->admin_text('If you cannot find your answer and need extra help, please visit our').' <a href="'.SPHOMESITE.'/support-forum">'.SP()->primitives->admin_text('Support Forum').'</a></p>';
	$out .= '</div>';
	echo $out;
}
