<?php
/*
Simple:Press
Admin Panels
$LastChangedDate: 2017-06-04 14:24:33 -0500 (Sun, 04 Jun 2017) $
$Rev: 15410 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	FORUM ADMIN
#	This file loads at Forum Admin
#
# ==========================================================================================

# ------------------------------------------------------------------
# spa_load_admin_css()
# Loads up the forum admin CSS
# ------------------------------------------------------------------
function spa_load_admin_css() {
	$spAdminStyleUrl = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFADMINCSS.'spa-admin-dev.css' : SFADMINCSS.'spa-admin.css';
	wp_register_style('spAdminStyle', $spAdminStyleUrl);
	wp_enqueue_style('spAdminStyle');
	wp_enqueue_style('farbtastic');
}

# ------------------------------------------------------------------
# spa_load_admin_scripts()
# Loads up the forum admin Javascript
# ------------------------------------------------------------------
function spa_load_admin_scripts() {
	global $spAPage, $spIsForumAdmin, $spStatus, $current_screen, $spMobile, $spDevice, $activePanel;

	if (!$spIsForumAdmin) return;

	$spAPage = spa_extract_admin_page();

    if (isset($_GET['panel'])) $activePanel = urldecode(sp_esc_str($_GET['panel']));

	if ($spStatus == 'ok') {
		if ($spAPage != 'notice') {
			do_action('sph_scripts_admin_start');

			$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFAJSCRIPT.'ajaxupload/ajaxupload-dev.js' : SFAJSCRIPT.'ajaxupload/ajaxupload.js';
			wp_enqueue_script('sfajaxupload', $script, array('jquery'), false, false);

			$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFAJSCRIPT.'nested-sortable/jquery.ui.nested.dev.js' : SFAJSCRIPT.'nested-sortable/jquery.ui.nested.js';
			wp_enqueue_script('sfanestedsortable', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-sortable'), false, false);
		}

		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFAJSCRIPT.'spa-admin-dev.js' : SFAJSCRIPT.'spa-admin.js';
		wp_enqueue_script('sfadmin', $script, array('jquery', 'jquery-form', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jquery-ui-tooltip'), false, false);

    	$platform = array(
			'focus'          =>   'admin',
			'mobile'         =>   $spMobile,
			'device'         =>   $spDevice,
			'tooltips'       =>   '1',
			'mobiletheme'    =>   '0',
    		'pWait'          =>   '<img src="'.SFCOMMONIMAGES.'working.gif" />'.spa_text('Please Wait...')
    	);
    	$platform = apply_filters('sph_platform_vars', $platform);
    	wp_localize_script('sfadmin', 'sp_platform_vars', $platform);

		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFCJSCRIPT.'sp-common-dev.js' : SFCJSCRIPT.'sp-common.js';
		wp_enqueue_script('spcommon', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-progressbar'), false, false);
		wp_enqueue_script('jquery-touch-punch', false, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse'), false, false);

    	# load up admin event handlers
    	$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFAJSCRIPT.'spa-admin-events-dev.js' : SFAJSCRIPT.'spa-admin-events.js';
    	wp_enqueue_script('spadminevents', $script, array('jquery', 'sfadmin', 'spcommon'), false, false);
		wp_enqueue_script('farbtastic');

		do_action('sph_scripts_admin_end');

		# Add help text to WP admin bar help 'slider'
		get_current_screen()->add_help_tab(array('id' => 'overview', 'title' => 'overview', 'callback' => 'spa_add_slider_help'));
	} else {
		# Install and Upgrade
		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SF_PLUGIN_URL.'/sp-startup/install/resources/jscript/sp-install-dev.js' : SF_PLUGIN_URL.'/sp-startup/install/resources/jscript/sp-install.js';
		wp_enqueue_script('sfjs', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-progressbar'), false, false);
	}
}

# ------------------------------------------------------------------
# spa_admin_header()
# Loads the forum page header
# ------------------------------------------------------------------
function spa_admin_header() {
	global $spIsForumAdmin, $spStatus, $spThisUser, $current_user, $spDevice;

	if (!$spIsForumAdmin) return;

	if ($spStatus == 'ok') {
		do_action('sph_admin_head_start');

		if (is_rtl()) {
?>
			<link rel="stylesheet" type="text/css" href="<?php echo SFADMINCSS;?>spa-admin-rtl.css" />
		<?php } ?>
		<style type="text/css">
			<?php if ($spDevice == 'mobile') { ?>
			#sfmaincontainer {margin:0 -10px 10px -15px;}
			<?php } else { ?>
			#sfmaincontainer {margin:0 0 10px 195px;}
			<?php }
			do_action('sph_add_style');
			?>
		</style>
<?php
		do_action('sph_admin_head_end');
	}
}

# ------------------------------------------------------------------
# spa_panel_header()
#
# Common admin header. Sets up main toolbar and content area.
#	$title:			admin panel title
#	$icon:			admin panel icon
# ------------------------------------------------------------------
function spa_panel_header() {
	global $spNews;

	echo '<!-- Common wrapper and header -->';
	echo '<div class="wrap nosubsub">';
	echo '<div class="mainicon icon-forums"></div>';
	echo '<h1>'.spa_text('Simple:Press Administration').'</h1>';
	echo '<div class="clearboth"></div>';

	echo '<table class="sfamenutable" style="width:100%">';
	echo '<tr><td style="text-align:right">';

	$site = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'troubleshooting', 'troubleshooting'));
	$target = 'sfmaincontainer';
	echo '<input style="width: 175px; font-weight:bold;" type="button" id="spHelp" class="button-primary spLeft spTroubleshoot" value="'.spa_text('Help & Troubleshooting').'" data-url="'.$site.'" data-target="'.$target.'" />&nbsp;&nbsp;&nbsp;';

	$site = wp_nonce_url(SPAJAXURL.'adminsearch', 'adminsearch');
	$target = 'sfmaincontainer';

	echo '<img style="margin: -1px 0 0 20px;float:left;" src="'.SFADMINIMAGES.'sp_Help32.png" alt="" />';
	echo '<input style="margin-left: 5px;font-weight:bold;" type="button" id="spSearch" class="button-primary spLeft spTroubleshoot" value="'.spa_text('What do you need to do?').'" data-url="'.$site.'" data-target="'.$target.'" />&nbsp;&nbsp;&nbsp;';

	echo '<a class="button" target="_blank" href="https://simple-press.com/documentation/codex/">'.spa_text('Simple:Press Codex').'</a>&nbsp;&nbsp;&nbsp;';

	$site = wp_nonce_url(SPAJAXURL.'spAckPopup', 'spAckPopup');
	$title = spa_text('About Simple:Press');
	echo '<a class="button spOpenDialog" data-site="'.$site.'" data-label="'.$title.'" data-width="600" data-height="0" data-align="center">'.$title.'</a>&nbsp;&nbsp;&nbsp;';
	echo '<a class="button" href="'.sp_url().'">'.spa_text('Go To Forum').'</a>';

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

# ------------------------------------------------------------------
# spa_admin_footer()
# Loads the forum page footer
# ------------------------------------------------------------------
function spa_admin_footer() {
	global $spIsForumAdmin;

	if ($spIsForumAdmin) echo SFPLUGHOME.' | '.spa_text('Version').' '.SPVERSION.'<br />';

	do_action('sph_admin_footer');
}

# ------------------------------------------------------------------
# spa_admin_init_scripts()
# Initialises admin script routines
# ------------------------------------------------------------------
function spa_admin_footer_scripts() {
	global $spStatus, $spAPage, $spIsForumAdmin, $sfactivepanels, $activePanel, $sfadminpanels;

	if (!$spIsForumAdmin) return;

	if ($spStatus == 'ok') {
		if ($spAPage != 'notice') {
            if (isset($activePanel)) {
                $panel = (!empty($sfactivepanels[$activePanel])) ? $sfactivepanels[$activePanel] : 0;
            } else {
                $panel = (!empty($sfactivepanels[$spAPage])) ? $sfactivepanels[$spAPage] : 0;
            }

    		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFAJSCRIPT.'spa-admin-footer-dev.js' : SFAJSCRIPT.'spa-admin-footer.js';
            wp_enqueue_script('sfadminfooter', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-accordion', 'jquery-ui-tooltip'), false, true);

        	$admin = array(
        		'panel' => $panel,
                'panel_name' => $sfadminpanels[$panel][0]
        	);
        	$admin = apply_filters('sp_admin_footer_vars', $admin);
        	wp_localize_script('sfadminfooter', 'sp_admin_footer_vars', $admin);
		}
	}
}

# ------------------------------------------------------------------
# spa_panel_footer()
#
# Common admin footer. Closes down content area and performs update
# check if option is turned on
# ------------------------------------------------------------------
function spa_panel_footer() {
}

function spa_render_sidemenu() {
	global $sfadminpanels, $spThisUser, $spDevice;

	$target = 'sfmaincontainer';
	$image = SFADMINIMAGES;
	$upgrade = admin_url('admin.php?page='.SPINSTALLPATH);

	if (isset($_GET['tab']) ? $formid = sp_esc_str($_GET['tab']) : $formid = '');

	if ($spDevice == 'mobile') {
		echo '<div id="spaMobileAdmin">'."\n";
		echo '<select class="wp-core-ui" onchange="location = this.options[this.selectedIndex].value;">'."\n";
		foreach ($sfadminpanels as $index => $panel) {
			if (sp_current_user_can($panel[1]) || ($panel[0] == 'Admins' && ($spThisUser->admin || $spThisUser->moderator))) {
				echo '<optgroup label="'.$panel[0].'">'."\n";
					foreach ($panel[6] as $label => $data) {
						foreach ($data as $formid => $reload) {
							# ignore user plugin data for menu
							if ($formid == 'admin' || $formid == 'save' || $formid == 'form') continue;
							$id = '';
							if ($reload != '') {
								$id = ' id="'.esc_attr($reload).'"';
							} else {
								$id = ' id="acc'.esc_attr($formid).'"';
							}
							$sel = '';
							if (isset($_GET['tab'])) {
								if ($_GET['tab'] == 'plugin') {
									if (isset($_GET['admin']) && isset($data['admin']) && $_GET['admin'] == $data['admin']) $sel = ' selected="selected" ';
								} else if ($_GET['tab'] == $formid) {
									$sel = ' selected="selected" ';
								}
							}
							echo "<option $id $sel";
							$admin = (!empty($data['admin']) ? '&admin='.$data['admin'] : '');
							$save  = (!empty($data['save']) ? '&save='.$data['save'] : '');
							$form  = (!empty($data['form']) ? '&form='.$data['form'] : '');

							if (empty($admin)) {
								$base = SFHOMEURL.'wp-admin/admin.php?page=';
							} else {
								$base = SFHOMEURL.'wp-admin/admin.php?page=simple-press/admin/panel-plugins/spa-plugins.php';
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
		echo '<a class="button button-secondary" href="'.sp_url().'">'.spa_text('Go To Forum').'</a>';
		echo '</div>'."\n";
	} else {
		echo '<div id="sfsidepanel">'."\n";
			echo '<div id="sfadminmenu">'."\n";
				foreach ($sfadminpanels as $index => $panel) {
					if (sp_current_user_can($panel[1]) || ($panel[0] == 'Admins' && ($spThisUser->admin || $spThisUser->moderator))) {
						$pName = str_replace(' ', '', $panel[0]);
						echo '<div class="sfsidebutton" id="sfacc'.$pName.'">'."\n";
						echo '<div class="" title="'.esc_attr($panel[3]).'"><span class="spa'.$panel[4].'"></span><a href="#">'.$panel[0].'</a></div>'."\n";
						echo '</div>'."\n";
						echo '<div class="sfmenublock">'."\n";

						foreach ($panel[6] as $label => $data) {
							foreach ($data as $formid => $reload) {
								# ignore user plugin data for menu
								if ($formid == 'admin' || $formid == 'save' || $formid == 'form') continue;
								echo '<div class="sfsideitem">'."\n";
								$id = '';
								if ($reload != '') {
									$id = ' id="'.esc_attr($reload).'"';
								} else {
									$id = ' id="acc'.esc_attr($formid).'"';
								}
								$base = esc_attr($panel[5]);
								$admin = (!empty($data['admin']) ? $data['admin'] : '');
								$save  = (!empty($data['save']) ? $data['save'] : '');
								$form  = (!empty($data['form']) ? $data['form'] : '');
								?>
								<a<?php echo $id; ?> href="#" class="spAccordionLoadForm" data-form="<?php echo $formid; ?>" data-url="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="" data-open="open" data-upgrade="<?php echo $upgrade; ?>" data-admin="<?php echo $admin; ?>" data-save="<?php echo $save; ?>" data-sform="<?php echo $form; ?>" data-reload="<?php echo $reload; ?>"><?php echo $label; ?></a><?php echo "\n"; ?>
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

function spa_check_warnings() {
	global $spGlobals;

	# not perfect but we can use this call tyo perform any minor
	# cleanups that may be necessary... so
	# drop any existing temp members table...
	spdb_query('DROP TABLE IF EXISTS sftempmembers');

	$mess = '';

	# check if sp core, plugins or themes update available
	$update = false;
	$update_msg = '';
	$up = get_site_transient('update_plugins');
	if (!empty($up->response)) {
		foreach ($up->response as $plugin) {
			if ($plugin->slug == 'simple-press' ) {
				$msg = apply_filters('sph_core_update_notice', spa_text('There is a Simple:Press core update available.'));
				if (!empty($msg)) {
					$update = true;
					$update_msg.= $msg.'<br />';
				}
				break;
			}
		}
	}

	$up = get_site_transient('sp_update_plugins');
	if (!empty($up)) {
		$msg = apply_filters('sph_plugins_update_notice', spa_text('There is one or more Simple:Press plugin updates available'));
		if (!empty($msg)) {
			$update = true;
			$update_msg.= $msg.'<br />';
		}
	}

	$up = get_site_transient('sp_update_themes');
	if (!empty($up)) {
		$msg = apply_filters('sph_themes_update_notice', spa_text('There is one or more Simple:Press theme updates available'));
		if (!empty($msg)) {
			$update = true;
			$update_msg.= $msg.'<br />';
		}
	}

	if ($update) {
		if (is_main_site()) {
			$mess.= apply_filters('sph_updates_notice', spa_message($update_msg.'<a href="'.self_admin_url('update-core.php').'">'.spa_text('Click here to view any updates.').'</a>'));
		} else {
			$mess.= apply_filters('sph_updates_notice', spa_message(spa_text('There are some Simple:Press updates avaialable. You may want to notify the network site admin.')));
		}
	}

	# output warning if no SPF admins are defined
	$a = $spGlobals['forum-admins'];
	if (empty($a)) $mess.= spa_message(spa_text('Warning - There are no SPF admins defined!	 All WP admins now have SP backend access'), 'error');

	# Check if	desktop, tablet and mobile themes are selected and available
	$cur = sp_get_option('sp_current_theme');
	if (empty($cur)) {
		$mess.= spa_message(spa_text('No main theme has been selected and SP will be unable to display correctly. Please select a theme from the Themes panel'), 'error');
	} else {
		$nostylesheet = !file_exists(SPTHEMEBASEDIR.$cur['theme'].'/styles/'.$cur['style']);
		$nooverlay = !empty($cur['color']) && !file_exists(SPTHEMEBASEDIR.$cur['theme'].'/styles/overlays/'.$cur['color'].'.php');
		$nopoverlay = !empty($cur['color']) && !empty($cur['parent']) && !file_exists(SPTHEMEBASEDIR.$cur['parent'].'/styles/overlays/'.$cur['color'].'.php');
		if ($nostylesheet || ($nooverlay && $nopoverlay)) {
			$mess.= spa_message(spa_text('Either the theme CSS file and/or color Overlay file from the selected theme is missing'), 'error');
		}
	}

	$mobile = sp_get_option('sp_mobile_theme');
	if (!empty($mobile) && $mobile['active']) {
		$nostylesheet = !file_exists(SPTHEMEBASEDIR.$mobile['theme'].'/styles/'.$mobile['style']);
		$nooverlay = !empty($mobile['color']) && !file_exists(SPTHEMEBASEDIR.$mobile['theme'].'/styles/overlays/'.$mobile['color'].'.php');
		$nopoverlay = !empty($mobile['color']) && !empty($mobile['parent']) && !file_exists(SPTHEMEBASEDIR.$mobile['parent'].'/styles/overlays/'.$mobile['color'].'.php');
		if ($nostylesheet || ($nooverlay && $nopoverlay)) {
			$mess.= spa_message(spa_text('Either the mobile theme CSS file and/or color Overlay file from the selected mobile theme is missing'), 'error');
		}
	}

	$tablet = sp_get_option('sp_tablet_theme');
	if (!empty($tablet) && $tablet['active']) {
		$nostylesheet = !file_exists(SPTHEMEBASEDIR.$tablet['theme'].'/styles/'.$tablet['style']);
		$nooverlay = !empty($tablet['color']) && !file_exists(SPTHEMEBASEDIR.$tablet['theme'].'/styles/overlays/'.$tablet['color'].'.php');
		$nopoverlay = !empty($tablet['color']) && !empty($tablet['parent']) && !file_exists(SPTHEMEBASEDIR.$tablet['parent'].'/styles/overlays/'.$tablet['color'].'.php');
		if ($nostylesheet || ($nooverlay && $nopoverlay)) {
			$mess.= spa_message(spa_text('Either the tablet theme CSS file and/or color Overlay file from the selected tablet theme is missing'), 'error');
		}
	}

	# check for missing default members user group
	$value = sp_get_sfmeta('default usergroup', 'sfmembers');
	$ugid = spdb_table(SFUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
	if (empty($ugid)) $mess.= spa_message(spa_text('Warning - The default user group for new members is undefined!	Please visit the SP usergroups admin page, map users to usergroups tab and set the default user group'), 'error');

	# check for missing default guest user group
	$value = sp_get_sfmeta('default usergroup', 'sfguests');
	$ugid = spdb_table(SFUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
	if (empty($ugid)) $mess.= spa_message(spa_text('Warning - The default user group for guests is undefined!  Please visit the SP usergroups admin page, map users to usergroups tab and set the default user group'), 'error');

	# check for unreachable forums because of permissions
	$usergroups = spdb_table(SFUSERGROUPS);
	if ($usergroups) {
		$has_members = false;
		foreach ($usergroups as $usergroup) {
			$members = spdb_table(SFMEMBERSHIPS, "usergroup_id=$usergroup->usergroup_id", 'row', '', '1');
			if ($members || $usergroup->usergroup_id == $value[0]['meta_value']) {
				$has_members = true;
				break;
			}
		}

		if (!$has_members) {
			$mess.= spa_message(spa_text('Warning - There are no usergroups that have members!	All forums may only be visible to SP admins'), 'error');
		}
	} else {
		$mess.= spa_message(spa_text('Warning - There are no usergroups defined!  All forums may only be visible to SP admins'), 'error');
	}

	$roles = sp_get_all_roles();
	if (!$roles) {
		$mess.= spa_message(spa_text('Warning - There are no permission sets defined!  All forums may only be visible to SP admins'), 'error');
	}

	# check if compatible with wp super cache
	if (function_exists('wp_cache_edit_rejected')) {
		global $cache_rejected_uri;
		$slug = '/'.sp_get_option('sfslug').'/';
		if (isset($cache_rejected_uri)) {
			$found = false;
			foreach ($cache_rejected_uri as $value) {
				if ($value == $slug) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				$string = spa_text('WP Super Cache is not properly configured to work with Simple:Press. Please visit your WP Super Cache settings page and in the accepted filenames & rejected URIs section for the pages not to be cached input field, add the following string');
				$string.= ':</p><p><em>'.$slug.'</em></p><p>';
				$string.= spa_text('Then, please clear your WP Super Cache cache to remove any cached Simple:Press pages');
				$string.= ':</p><p><em>'.spa_text('For more information please see this').' <a href="https://simple-press.com/documentation/codex/faq/troubleshooting/forum-displays-wrong-information/" target="_blank">'.spa_text('FAQ').'</a></p><p>';
				$mess.= spa_message($string, 'error');
			}
		}
	}

	# check if compatible with w3 total cache if installed (at leasst check for slug)
	if (defined('W3TC_CACHE_CONFIG_DIR') && function_exists('w3_get_blog_id')) {
		if (w3_get_blog_id() <= 0) {
			$f = W3TC_CACHE_CONFIG_DIR . '/master.php';
		} else {
			$f = W3TC_CACHE_CONFIG_DIR . '/' . sprintf('%06d', w3_get_blog_id()) . '/master.php';
		}
		if (file_exists($f) && is_readable($f)) {
			$content = file_get_contents($f);
			$content = substr($content,13);
			$config = array();
			if (is_serialized($content)) $config = @unserialize($content);
			if (is_array($config)) {
				if(key_exists('pgcache.reject.uri', $config) && !empty($config['pgcache.reject.uri'])) {
					$found = false;
					$slug = '/'.sp_get_option('sfslug').'/';
					foreach($config['pgcache.reject.uri'] as $i) {
						if($i == $slug) {
							$found = true;
							break;
						}
					}

					if (!$found) {
						$string = spa_text('W3 Total Cache is not properly configured to work with Simple:Press. Please visit your W3 Total Cache settings page and in the accepted filenames & rejected URIs in ALL sections, add the following string');
						$string.= ':</p><p><em>'.$slug.'</em></p><p>';
						$string.= spa_text('Then, please clear your W3 Total Cache cache to remove any cached Simple:Press pages');
						$string.= ':</p><p><em>'.spa_text('For more information please see this').' <a href="https://simple-press.com/documentation/codex/faq/troubleshooting/forum-displays-wrong-information/" target="_blank">'.spa_text('FAQ').'</a></em></p><p>';
						$mess.= spa_message($string, 'error');
					}
				}
			}
		}
	}

	# check for server-side UTC timezone
	$tz = get_option('timezone_string');
	if (empty($tz)) {
		$tz = 'UTC '.get_option('gmt_offset');
		$string = spa_text('You have set your server to use a UTC timezone setting');
		$string.= ':</p><p><em>'.$tz.'</em></p><p>';
		$string.= spa_text('UTC can give unpredictable results on forum post time stamps. Please select the city setting nearest to you in the WordPress - Settings - General admin page');
		$string.= ':</p><p><em>'.spa_text('For more information please see this').' <a href="https://simple-press.com/documentation/codex/faq/troubleshooting/why-do-my-new-posts-show-as-posted-minus-seconds-ago/" target="_blank">'.spa_text('FAQ').'</a></p><p>';
		$mess.= spa_message($string, 'error');
	}

	if ($mess != '') return $mess;
}

# ------------------------------------------------------------------
# spa_message()
#
# Common success/failure post-save messaging
# ------------------------------------------------------------------
function spa_message($message, $status='updated') {
	$out = "<div class='$status'>";
	if ($status == 'error') $out.= '<img class="spWait" src="'.SFADMINIMAGES.'sp_Message.png" alt="" />';
	$out.= "<p>$message</p>";
	$out.= "</div>";
	return $out;
}

# ------------------------------------------------------------------
# spa_extract_admin_page()
# Determines the forum admin panel being requested
# ------------------------------------------------------------------
function spa_extract_admin_page() {
	global $spAPage;

	if (strpos($_SERVER['QUERY_STRING'], 'spa-plugins.php') != 0) {
		$spAPage = '/spa-plugins.php';
	} else {
		$spAPage = strrchr($_SERVER['QUERY_STRING'], '/');
	}
	if (strpos($spAPage, '/spa-', 0) === false) return 'none';
	$spAPage = substr($spAPage, 5, 26);
	$x = explode('.php', $spAPage);
	$spAPage = reset($x);
	return $spAPage;
}

# ------------------------------------------------------------------
# spa_add_slider_help()
# Adds slider help to admin panels
# ------------------------------------------------------------------
function spa_add_slider_help() {
	global $spIsForumAdmin;

	if (!$spIsForumAdmin) return;
	$out = '';
	$out.= '<h5>'.spa_text('Simple:Press - Help Options').'</h5>';
	$out.= '<div class="metabox-prefs">';
	$out.= '<p>'.spa_text('For contextual help with Simple:Press, click on the Help buttons/links located on each administration panel').'<br />';
	$out.= spa_text('For Simple:Press information, troubleshooting, how-to, administration, our API and more, please visit our').' <a target="_blank" href="https://simple-press.com/documentation/codex/">'.spa_text('Simple:Press Codex').'</a><br />';
	$out.= spa_text('If you cannot find your answer and need extra help, please visit our').' <a href="'.SFHOMESITE.'/support-forum">'.spa_text('Support Forum').'</a></p>';
	$out.= '</div>';
	echo $out;
}

?>