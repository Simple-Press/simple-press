<?php
/**
 * Admin support functions
 * Loads for all forum admin pages and provides general support functions across the admin
 *
 * $LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
 * $Rev: 15817 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function registers and enqueues the admin CSS style for the dashboard.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_load_dashboard_css() {
	$spDashStyleUrl = SPADMINCSS.'spa-dashboard.css';
	wp_register_style('spDashStyle', $spDashStyleUrl);
	wp_enqueue_style('spDashStyle');
}

/**
 * This function checks if the user has asked that Simple Press be uninstalled and set up the options if so.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_check_removal() {
	if (isset($_GET['spf']) && sanitize_text_field($_GET['spf']) == 'uninstall') SP()->options->update('sfuninstall', true);
	if (isset($_GET['remove']) && sanitize_text_field($_GET['remove'] == 'storage')) SP()->options->update('removestorage', true);
}

/**
 * This function blocks users from accessing the WP admin area.
 * Which users are blocked is determined by option settings.
 *
 * @since 6.0
 *
 * @return void
 */
function spa_block_admin() {
	global $current_user;

	# Is this the admin interface?
	if (strstr(strtolower($_SERVER['REQUEST_URI']), '/wp-admin/') && !strstr(strtolower($_SERVER['REQUEST_URI']), 'async-upload.php') && !strstr(strtolower($_SERVER['REQUEST_URI']), 'admin-ajax.php')) {
		# get the user level and required level to access admin pages
		$sfblock = SP()->options->get('sfblockadmin');
		if ($sfblock['blockadmin'] && !empty($sfblock['blockroles'])) {
			$role_matches = array_intersect_key($sfblock['blockroles'], array_flip($current_user->roles));
			$access       = in_array(1, $role_matches);
			# block admin if required
			$is_moderator = SP()->memberData->get($current_user->ID, 'moderator');
			if (!SP()->auths->current_user_can('SPF Manage Options') && !SP()->auths->current_user_can('SPF Manage Forums') && !SP()->auths->current_user_can('SPF Manage Components') && !SP()->auths->current_user_can('SPF Manage User Groups') && !SP()->auths->current_user_can('SPF Manage Permissions') && !SP()->auths->current_user_can('SPF Manage Tags') && !SP()->auths->current_user_can('SPF Manage Users') && !SP()->auths->current_user_can('SPF Manage Profiles') && !SP()->auths->current_user_can('SPF Manage Admins') && !SP()->auths->current_user_can('SPF Manage Toolbox') && !$is_moderator && !$access) {
				if ($sfblock['blockprofile']) {
					$redirect = SP()->spPermalinks->get_url('profile');
				} else {
					$redirect = $sfblock['blockredirect'];
				}
				wp_redirect($redirect, 302);
			}
		}
	}
}

/**
 * This function checks is the forum permalink has changed.
 *
 * @access public
 *
 * @since 6.0
 *
 * @param string $old old permalink
 * @param string $new updated permalnk
 *
 * @return void
 */
function spa_permalink_changed($old, $new) {
	if (empty($new)) {
		$perm = user_trailingslashit(SPSITEURL).'?page_id='.SP()->options->get('sfpage');
		SP()->options->update('sfpermalink', $perm);
	} else {
		$perm = user_trailingslashit(SPSITEURL.SP()->options->get('sfslug'));
		SP()->options->update('sfpermalink', $perm);
		flush_rewrite_rules();
	}
	SP()->spPermalinks->update_permalink();
}

/**
 * This function checks if the WP page assigned to the forum has been changed and updates
 * the forum permalink if it has been changed.
 *
 * @since 6.0
 *
 * @param int    $postid current wp page ID that forum is assigned to
 * @param object $pObj   wp page object
 *
 * @return void
 */
function spa_check_page_change($postid, $pObj) {
	$spPage = SP()->options->get('sfpage');
	if ($spPage == $postid) {
		$perm    = get_permalink($postid);
		$setslug = $pObj->post_name;
		if ($pObj->post_parent) {
			$parent = $pObj->post_parent;
			while ($parent) {
				$thispage = SP()->DB->table(SPWPPOSTS, "ID=$parent", 'row');
				$setslug  = $thispage->post_name.'/'.$setslug;
				$parent   = $thispage->post_parent;
			}
		}
		SP()->options->update('sfpermalink', $perm);
		SP()->options->update('sfslug', $setslug);

		SP()->spPermalinks->update_permalink(true);
	}
}

/**
 * This function add extra Simple Press links underneath the WP plugins page display for out plugin.
 *
 * @access public
 *
 * @since 6.0
 *
 * @param array  $links  current text and links to be displayed for current plugin
 * @param string $plugin current plugin being displayed on plugins admin page
 *
 * @return string    update text and links for current plugin
 */
function spa_add_plugin_action($links, $plugin) {
	if ($plugin == SP_FOLDER_NAME.'/sp-control.php') {
		if (SP()->core->status != 'ok') {
			# Install or Upgrade
			$actionlink = '<a href="'.admin_url('admin.php?page='.SPINSTALLPATH).'">'.SP()->primitives->admin_text(SP()->core->status).'</a>';
			array_unshift($links, $actionlink);
		} else {
			# Uninstall
			if (!SP()->options->get('sfuninstall')) {
				$param        = array();
				$param['spf'] = 'uninstall';
				$passURL      = add_query_arg($param, esc_url($_SERVER['REQUEST_URI']));
				?>
                <script>
					(function(spj, $, undefined) {
						$(document).ready(function () {
							$('#sp-uninstall-link').click(function () {
								var answer = $("#spuninstalldialog").dialog({width: 600})
									.find(':checkbox').off('change').on('change', function (e) {
										if (this.checked)
											$('#sp-url').val('<?php echo "$passURL&remove=storage"; ?>');
										else
											$('#sp-url').val('<?php echo $passURL; ?>');
									});
							});
						});
					}(window.spj = window.spj || {}, jQuery));
                </script>
				<?php
				$actionlink = '<div id="spuninstalldialog" style="display:none;border:2px solid red;width:auto;" title="'.SP()->primitives->admin_text('Uninstall Simple:Press').'">';
				$actionlink .= '<input type="hidden" id="sp-url" name="sp-url" value="'.$passURL.'" />';
				$actionlink .= '<p style="font-weight:bold">'.SP()->primitives->admin_text('Are you sure you want to uninstall Simple:Press?').'</p>';
				$actionlink .= '<p style="font-weight:bold">'.SP()->primitives->admin_text('This option will REMOVE ALL FORUM DATA after deactivating Simple:Press.').'</p>';
				$actionlink .= '<p style="font-weight:bold">'.SP()->primitives->admin_text('Press CONFIRM to prepare for Simple:Press removal.	Press the X at top to cancel without proceeding.').'</p>';
				$actionlink .= '<p style="font-weight:bold">'.SP()->primitives->admin_text('If after enabling, you wish to cancel the uninstall, visit the forum admin - toolbox - uninstall panel.').'</p>';
				$actionlink .= '<label for="sp-storage"><input type="checkbox" id="sp-storage" />Remove SP Storage Locations on uninstall.</label><br /><br />';
				$actionlink .= '<div style="text-align:center;"><input type="button" class="button-primary" onclick="javascript:loc=jQuery(\'#sp-url\').val();window.location = loc;" value="'.SP()->primitives->admin_text('Confirm').'" /></div>';
				$actionlink .= '<br /><hr /><p><u>'.SP()->primitives->admin_text('HELP US TO IMPROVE Simple:Press').'</u></p>';
				$actionlink .= '<p style="font-weight:bold"><i>'.SP()->primitives->admin_text('We continually strive to improve and enhance Simple:Press to meet our users requirements and we are sorry to see you go.  We would very much welcome your feedback').'</i></p>';
				$actionlink .= '<p style="font-weight:bold">'.SP()->primitives->admin_text('Please do send us an').' <a href="mailto:support@simple-press.com?subject=Why%20we%20are%20unintsalling%20Simple:Press">'.SP()->primitives->admin_text('email').'</a> '.SP()->primitives->admin_text('with your comments.').'</p>';
				$actionlink .= '</div>';
				$actionlink .= '<a id="sp-uninstall-link">'.SP()->primitives->admin_text('Uninstall').'</a>';
				array_unshift($links, $actionlink);
			}

			$actionlink = '<a href="https://simple-press.com/membership/">'.SP()->primitives->admin_text('Premium Support').'</a>';
			array_push($links, $actionlink);

			$actionlink = '<a href="https://simple-press.com/documentation/">'.SP()->primitives->admin_text('Documentation').'</a>';
			array_push($links, $actionlink);
		}
	}

	return $links;
}

/**
 * This function runs when Simple Press is activated.  It sets up our cron schedules and events.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_activate_plugin() {
	if (SP()->core->status == 'ok') {
		# set up daily transient clean up cron
		wp_clear_scheduled_hook('sph_transient_cleanup_cron');
		wp_schedule_event(time(), 'daily', 'sph_transient_cleanup_cron');

		# set up hourly stats generation
		wp_clear_scheduled_hook('sph_stats_cron');
		wp_schedule_event(time(), 'sp_stats_interval', 'sph_stats_cron');

		# set up weekly news check
		wp_clear_scheduled_hook('sph_news_cron');
		wp_schedule_event(time(), 'sp_news_interval', 'sph_news_cron');

		# set up user auto removal cron job
		wp_clear_scheduled_hook('sph_cron_user');
		$sfuser = SP()->options->get('sfuserremoval');
		if ($sfuser['sfuserremove']) wp_schedule_event(time(), 'daily', 'sph_cron_user');
		
		# set up daily sp_check_addons_status clean up cron
		wp_clear_scheduled_hook('sph_check_addons_status_interval');
		wp_schedule_event(time(), 'ten_minutes', 'sph_check_addons_status_interval');

		SP()->spPermalinks->update_permalink(true);
	}

	do_action('sph_activated');
}

# ------------------------------------------------------------------
# spa_deactivate_plugin()
#
# Removes all forum data prior to uninstall
# Handles deactivation for cron jobs
# ------------------------------------------------------------------

/**
 * This function runs when the Simple Press plugin is deactivated.
 * It will cleanup our install database entries and server files.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_deactivate_plugin() {
	$uninstall = SP()->options->get('sfuninstall');
	if ($uninstall) { # uninstall - remove all data
		# remove any admin capabilities
		$admins = SP()->DB->table(SPMEMBERS, 'admin=1');
		foreach ($admins as $admin) {
			$user = new WP_User($admin->user_id);
			$user->remove_cap('SPF Manage Options');
			$user->remove_cap('SPF Manage Forums');
			$user->remove_cap('SPF Manage User Groups');
			$user->remove_cap('SPF Manage Permissions');
			$user->remove_cap('SPF Manage Tags');
			$user->remove_cap('SPF Manage Components');
			$user->remove_cap('SPF Manage Admins');
			$user->remove_cap('SPF Manage Profiles');
			$user->remove_cap('SPF Manage Users');
			$user->remove_cap('SPF Manage Toolbox');
			$user->remove_cap('SPF Manage Plugins');
			$user->remove_cap('SPF Manage Themes');
			$user->remove_cap('SPF Manage Integration');
			$user->remove_cap('SPF Manage Configuration'); # no longer used but some may still have it
		}

		# remove any installed tables
		$tables = SP()->options->get('installed_tables');
		if ($tables) {
			foreach ($tables as $table) {
				SP()->DB->execute("DROP TABLE IF EXISTS $table");
			}
		}

		# since we have removed our tables, need to turn off error logging to prevent onslaught of errors
		SP()->error->setRecording(false);

		# Remove the Page record
		$sfpage = SP()->options->get('sfpage');
		if (!empty($sfpage)) {
			SP()->DB->execute('DELETE FROM '.SPWPPOSTS.' WHERE ID='.SP()->options->get('sfpage'));
		}

		# remove widget data
		delete_option('widget_spf');
		delete_option('widget_sforum');

		# remove any wp options we might have set
		delete_option('sfInstallID');
		delete_option('sp_storage1');
		delete_option('sp_storage2');

		# Now remove user meta data
		$optionlist = array('sfadmin',
		                    'location',
		                    'msn',
		                    'skype',
		                    'icq',
		                    'facebook',
		                    'myspace',
		                    'twitter',
		                    'linkedin',
		                    'youtube',
		                    'googleplus',
		                    'sfuse_quicktags',
		                    'signature',
		                    'sigimage');

		foreach ($optionlist as $option) {
			SP()->DB->execute('DELETE FROM '.SPUSERMETA." WHERE meta_key='$option';");
		}

		# send our uninstall action
		do_action('sph_uninstalled', $admins);

		# remove storage locations if so directed
		if (SP()->options->get('removestorage')) {
			# let's remove our directories and storage
			if (!empty(SP()->plugin->storage)) {
				foreach (SP()->plugin->storage as $storage => $path) {
					# lets not remove plugins and themes
					if ($storage != 'plugins' && $storage != 'themes') SP()->primitives->remove_dir(SP_STORE_DIR.'/'.$path);
				}
			}

			# remove the languages folder if it exists
			# note the sp-resources dir may not exist - but its our default. if user creates other parent dir for languages, we wont know about it
			SP()->primitives->remove_dir(SP_STORE_DIR.'/sp-resources/forum-language');

			# now remove the barebones custom settings storage
			SP()->primitives->remove_dir(SP_STORE_DIR.'/sp-custom-settings');
		}
	}

	# remove the combined css and js cache files
	SP()->plugin->clear_css_cache('all');
	SP()->plugin->clear_css_cache('mobile');
	SP()->plugin->clear_css_cache('tablet');

	# remove cron jobs for deactivaton or uninstall
	wp_clear_scheduled_hook('spf_cron_pm'); # left here for 5.0 who doesnt upgrade
	wp_clear_scheduled_hook('spf_cron_sitemap'); # left here for 5.0 who doesnt upgrade

	wp_clear_scheduled_hook('sph_cron_user');
	wp_clear_scheduled_hook('sph_transient_cleanup_cron');
	wp_clear_scheduled_hook('sph_stats_cron');
	wp_clear_scheduled_hook('sph_news_cron');
	
	wp_clear_scheduled_hook('sph_check_addons_status_interval');

	# send deactivated action
	if (!$uninstall) do_action('sph_deactivated');
}

/**
 * This function adds a notice to the WP Discussion avatar section when WP avatars
 * are replaced by Simple Press avatars.
 *
 * @access public
 *
 * @since 6.0
 *
 * @param array $list unused
 *
 * @return void
 */
function spa_wp_discussion_avatar($list) {
	echo '<h3>'.SP()->primitives->admin_text('Currently, all WP avatars are being replaced by Simple:Press avatars. You can change this at');
	echo ': <a href="'.admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-profiles/spa-profiles.php&amp;tab=avatars').'">';
	echo SP()->primitives->admin_text('Forum - Profiles - Avatars');
	echo '</a>.';
	echo '</h3>';
}

/**
 * This function adds a nag message to header of admin pages when a
 * Simple Press core, plugin or them update is available..
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function sp_action_nag() {
	if (strpos($_SERVER['REQUEST_URI'], 'sp-load-install') == 0 && $_SERVER['REQUEST_URI'] != '/wp-admin/index.php') {
		echo '<div class="error highlight notice is-dismissible"><p><b>';
		echo '<img style="vertical-align:bottom;border:none;margin:0 8px 60px 0;float:left" src="'.SP()->theme->paint_file_icon(SPADMINIMAGES, 'sp_Information.png').'" alt="" />'."\n";
		if (SP()->core->status == 'Install') {
			echo sprintf(SP()->primitives->admin_text('Your Simple:Press forum is awaiting the initial database %s before it can be used'), strtolower(SP()->core->status));
		} else if (SP()->core->status == 'Upgrade') {
			echo sprintf(SP()->primitives->admin_text('The forum is temporarily unavailable while awaiting a database %s'), strtolower(SP()->core->status));
		}
		echo '<br /><a style="text-decoration: underline;" href="'.SPADMINUPGRADE.'">'.SP()->primitives->admin_text('Perform').' '.SP()->core->status.'</a>';
		echo '</b></p></div>';
    }

    # check for upgrades to 6.0+ from versions < 5.7.2 which is not allowed automatically (must be manual)
    if ($_SERVER['REQUEST_URI'] != '/wp-admin/index.php' && SP()->core->status == 'Unallowed 6.0 Upgrade') {
		echo '<div class="error highlight notice is-dismissible"><p><b>';
		echo '<img style="vertical-align:bottom;border:none;margin:0 8px 60px 0;float:left" src="'.SP()->theme->paint_file_icon(SPADMINIMAGES, 'sp_Information.png').'" alt="" />'."\n";
        # this is unallowed 6.0+ upgrade - version less than 5.7.2
        # uprading not allowed since its breaking changes and should be manually upgraded
        # since we dont allow, we need special messaging here
		echo SP()->primitives->admin_text('The forum is temporarily unavailable while awaiting a database upgrade.').'<br />';
		echo sprintf(SP()->primitives->admin_text('You are attempting to upgrade to version %s from you current version of %s.'), SPVERSION, SP()->options->get('sfversion')).'<br />';
		echo SP()->primitives->admin_text('Unfortunately, auto upgrades from versions prior to 5.7.2 are not allowd due to the complexity of the changes.').'<br />';
        echo SP()->primitives->admin_text('Please visit our ').'<a href="https://simple-press.com/documentation/installation/upgrading/previous-simplepress-versions/">'.SP()->primitives->admin_text('previous versions page').'</a>';
        echo sprintf(SP()->primitives->admin_text(', then download and upgrade to at least version 5.7.2 before attempting to upgrade to version %s.'), SPVERSION).'<br />';
    	echo '</b></p></div>';
   }
}

/**
 * This function sets up a forum section on the WP admin dashboard.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_dashboard_setup() {
	global $spNews;

	# If awaiting installation then dive out now to avoid errors
	if (SP()->core->status == 'Install' || SP()->user->thisUser->moderator == false) return;

	# standard forum widget
	wp_add_dashboard_widget('spa_dashboard_forum', SP()->primitives->admin_text('Forums'), 'spa_dashboard_forum');

	# News update widget
	$spNews = spa_check_for_news();
	if (!empty($spNews)) {
		wp_add_dashboard_widget('spa_dashboard_news', SP()->primitives->admin_text('Simple:Press News'), 'spa_dashboard_news');
		add_action('in_admin_footer', 'spa_remove_news');
	}
}

/**
 * This function checks to see if there is any news items to be shown in admin header.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return string
 */
function spa_check_for_news() {
	
	$news = SP()->meta->get('news', 'news');
		
	if (!empty($news)) {
		if ($news[0]['meta_value']['show']) return $news[0]['meta_value']['news'];
	}

	return '';
}

/**
 * This function displays forum information in the forum section of the WP admin dashboard.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_dashboard_forum() {
	$out = '';

	# check we have an installed version
	if (SP()->core->status != 'ok') {
		$out .= '<div style="border: 1px solid #ddd; padding: 10px; font-weight: bold;">'."\n";
		$out .= '<p><img style="vertical-align:bottom;border:none;margin:0 8px 30px 0;float:left" src="'.SP()->theme->paint_file_icon(SPADMINIMAGES, 'sp_Information.png').'" alt="" />'."\n";
		$out .= sprintf(SP()->primitives->admin_text('The forum is temporarily unavailable while awaiting a database %s'), strtolower(SP()->core->status));

		if (SP()->user->thisUser->admin) $out .= '<br /><a style="text-decoration: underline;" href="'.SPADMINUPGRADE.'">'.SP()->primitives->admin_text('Perform Upgrade').'</a>';
		$out .= '</p></div>';
		echo $out;

		return;
	}

	$out .= '<div id="sf-dashboard">';
	echo $out;
	do_action('sph_dashboard_start');

	if (SP()->core->forumData['admin']['sfdashboardstats']) {
		require_once SP_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';
		require_once SP_PLUGIN_DIR.'/forum/content/sp-template-control.php';
		echo '<br /><table class="sfdashtable">';
		echo '<tr>';
		echo '<td>';
		sp_OnlineStats('link_names=0', '<b>'.SP()->primitives->admin_text('Most Users Ever Online').': </b>', '<b>'.SP()->primitives->admin_text('Currently Online').': </b>', '<b>'.SP()->primitives->admin_text('Currently Browsing this Page').': </b>', SP()->primitives->admin_text('Guest(s)'));
		echo '</td>';
		echo '<td>';
		sp_ForumStats('', '<b>'.SP()->primitives->admin_text('Forum Stats').': </b>', SP()->primitives->admin_text('Groups').': ', SP()->primitives->admin_text('Forums').': ', SP()->primitives->admin_text('Topics').': ', SP()->primitives->admin_text('Posts').': ');
		echo '</td>';
		echo '<td>';
		sp_MembershipStats('', '<b>'.SP()->primitives->admin_text('Member Stats').': </b>', SP()->primitives->admin_text('There are %COUNT% Members'), SP()->primitives->admin_text('There have been %COUNT% Guest Posters'), SP()->primitives->admin_text('There are %COUNT% Moderators'), SP()->primitives->admin_text('There are %COUNT% Admins'));
		echo '</td>';
		echo '<td>';
		sp_TopPostersStats('link_names=0', '<b>'.SP()->primitives->admin_text('Top Posters').': </b>');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="4">';
		sp_NewMembers('link_names=0', '<b>'.SP()->primitives->admin_text('Newest Members').': </b>');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="4">';
		sp_ModsList('link_names=0', '<b>'.SP()->primitives->admin_text('Moderators').': </b>');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="4">';
		sp_AdminsList('link_names=0', '<b>'.SP()->primitives->admin_text('Administrators').': </b>');
		echo '</td>';
		echo '</tr></table><br />';
	}

	do_action('sph_dashboard_end');

	$out = '';
	$out .= '<p><br /><a href="'.SP()->spPermalinks->get_url().'">'.SP()->primitives->admin_text('Go To Forum').'</a></p>';
	$out .= '</div>';
	echo $out;
}

/**
 * This function any Simple Press news item in the admin header.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_dashboard_news() {
	global $spNews;
	$sp_update_plugins = get_site_transient('sp_update_plugins');
	$sp_update_themes = get_site_transient('sp_update_themes');
	?>
    <style>
        #spa_dashboard_news h4 {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 6px;
            line-height: 1.2em;
            border-bottom: 1px solid #ddd;
        }

        #spa_dashboard_news .spa_dashboard_text p {
            line-height: 1.3em;
            margin: 6px 0 0 0;
            font-size: 14px;
        }
        
         #spa_dashboard_news .spa_dashboard_text h3 {
        	
        	font-weight: bold;
        }
        
        #update-plugins-table{
        	
        	margin: 10px 0px;
        }
        
        #update-themes-table{
        	
        	margin: 10px 0px 0px;
        }

        ;
    </style>
	<?php
	echo '<div id="spa_dashboard_news" style="background:#FFFFEA;border:2px solid #666;border-radius:9px;margin:10px;padding:15px;">';
	echo '<div style="vertical-align:middle;border-bottom:1px solid #666;margin-bottom:16px;padding:7px 0;">';
	echo '<img src="'.SPCOMMONIMAGES.'sp-full-logo.png" alt="" style="vertical-align:middle;float:left;margin:0 15px 10px 0;padding-right:12px;border-right:1px solid #666;" />';
	echo '<p style="vertical-align:middle;margin:13px 0 0 0;font-weight:bold;font-size:20px;line-height:1em;">'.SP()->primitives->admin_text('Recent News').'</p>';
	echo '<div style="clear:both;"></div>';
	echo '</div>';
	echo '<div class="spa_dashboard_text">';
	echo SP()->displayFilters->text($spNews);
	spa_plugin_addon_dashboard_update();
	spa_theme_addon_dashboard_update();
	echo '</div>';
	echo '<div style="clear:both;"></div>';
	echo '<div style="border-top:1px solid #666;margin-top:18px;padding:10px 0;">';
	$site = wp_nonce_url(SPAJAXURL.'remove-news&amp;targetaction=news', 'remove-news');
	if(!empty($sp_update_plugins) || !empty($sp_update_themes)){
		echo '<a href="'.self_admin_url('update-core.php').'"><input type="button" value="'.SP()->primitives->admin_text('Go to Update page').'" class="button-primary" /></a> &nbsp; &nbsp;';	
	}
	echo '<input type="button" value="'.SP()->primitives->admin_text('Remove News Item').'" class="button-primary" onclick="spj.removeNews(\''.$site.'\')"/>';
	echo '</div>';
	echo '</div>';
}

/**
 * This function removes the news item when a user clicks on remove.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return void
 */
function spa_remove_news() {
	?>
    <script>
		(function(spj, $, undefined) {
			spj.removeNews = function(url) {
				$(document).ready(function () {
					$('#spa_dashboard_news').fadeOut('slow');
					$('#spa_dashboard_news').load(url);
				});
			};
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
}


/**
 * This function determines if there is an update available to the core Simple Press plugin and themes.
 *
 */

if ( ! function_exists( 'spa_plugin_updater_object' ) ) {
	
	function spa_plugin_updater_object($plugin_file, $plugin_data){

		$sp_plugin_name = sanitize_title_with_dashes($plugin_data['Name']);
		$get_key = SP()->options->get( 'plugin_'.$sp_plugin_name);
			
		$this_path = realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['plugins']).'/'.strtok(plugin_basename($plugin_file), '/');
		
		$api_data = array(
			
	        'version'   => $plugin_data['Version'],   // current version number
	        'license'   => $get_key,        // license key (used get_option above to retrieve from DB)
	        'author'    => $plugin_data['Author'],  // author of this plugin
	        'API_action' => 'get_version' // api action
	    );
		
		if($plugin_data['ItemId'] == ''){
			
			$api_data['item_name'] = $plugin_data['Name'];  // name of this plugin
			
		}else{
			
			$api_data['item_id'] = $plugin_data['ItemId'];  // id of this plugin
		}
		
		$sp_plugin_updater = new SPPluginUpdater( SP_Addon_STORE_URL, $plugin_file, $api_data);
		
		return $sp_plugin_updater;
	}
}

if ( ! function_exists( 'spa_theme_updater_object' ) ) {
	
	
	function spa_theme_updater_object($theme_file, $theme_data){
		
		$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
		
		$get_key = SP()->options->get( 'theme_'.$sp_theme_name);
		
		$this_path = realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['themes']).'/'.$theme_file;

		$api_data = array(
		
	        'version'   => $theme_data['Version'],   // current version number
	        'license'   => $get_key,        // license key (used get_option above to retrieve from DB)
	        'author'    => 'Simple:Press',  // author of this plugin
	        'API_action' => 'get_version' // action api
	    );
		
		if($theme_data['ItemId'] == ''){
			
			$api_data['item_name'] = $theme_data['Name'];  // name of this plugin
			
		}else{
			
			$api_data['item_id'] = $theme_data['ItemId'];  // id of this plugin
		}
		
		$sp_theme_updater = new SPPluginUpdater( SP_Addon_STORE_URL, $theme_file, $api_data);
		
		return $sp_theme_updater;
	}
}

function spa_addons_changelog($_data, $_action = '', $_args = null ){
	
	
	if ( $_action != 'plugin_information' ) {

		return $_data;
	}
	
	$plugins = SP()->plugin->get_list();
	
	foreach ($plugins as $plugin_file => $plugin_data) {
		
		$sp_plugin_name = sanitize_title_with_dashes($plugin_data['Name']);
		
		
		if (!empty($plugins) && isset($plugin_data['ItemId']) && $plugin_data['ItemId'] != '' && $sp_plugin_name === $_args->slug) {
			
			$check_for_addon_update = SP()->options->get( 'spl_plugin_versioninfo_'.$_args->slug);

			$_data = json_decode($check_for_addon_update);
			
			if ( $_data && isset( $_data->name ) ) {
				$_data->name = $plugin_data['Name'];
			}

			if ( $_data && isset( $_data->download_link ) ) {
				$_data->download_link = '';
			}
			
			if ( $_data && isset( $_data->sections ) ) {
				$_data->sections = maybe_unserialize( $_data->sections );
			} else {
				$_data = false;
			}
			
			if ( $_data && isset( $_data->banners ) ) {	
				$_data->banners = maybe_unserialize( $_data->banners );
			}
			return $_data;			
		}
	}
	
	$themes = SP()->theme->get_list();
	
	foreach ($themes as $theme_file => $theme_data) {
		
		$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
			
		if (!empty($themes) && isset($theme_data['ItemId']) && $theme_data['ItemId'] != '' && $sp_theme_name === $_args->slug) {
			
			$check_for_addon_update = SP()->options->get( 'spl_theme_versioninfo_'.$sp_theme_name);
	
			$_data = json_decode($check_for_addon_update);
			
			if ( $_data && isset( $_data->name ) ) {
				$_data->name = $theme_data['Name'];
			}

			if ( $_data && isset( $_data->download_link ) ) {
				$_data->download_link = '';
			}
			
			if ( $_data && isset( $_data->sections ) ) {
				$_data->sections = maybe_unserialize( $_data->sections );
			} else {
				$_data = false;
			}
			
			if ( $_data && isset( $_data->banners ) ) {
				$_data->banners = maybe_unserialize( $_data->banners );
			}
			return $_data;		
		}
	}
	return $_data;	
}

/**
 * This function determines if there is an update available to the core Simple Press themes and notify to admin.
 *
 */

function spa_plugin_addon_dashboard_update()
{
	
	$plugins = SP()->plugin->get_list();
	$header = true;
	$xml = sp_load_version_xml();
	
	foreach ($plugins as $plugin_file => $plugin_data) {
		
		if (!empty($plugins) && isset($plugin_data['ItemId']) && $plugin_data['ItemId'] != '') {
		
			$sp_plugin_name = sanitize_title_with_dashes($plugin_data['Name']);
			$check_for_addon_update = SP()->options->get( 'spl_plugin_versioninfo_'.$sp_plugin_name);
			$check_addons_status = SP()->options->get( 'spl_plugin_info_'.$sp_plugin_name);
			
			if($check_for_addon_update != '' && $check_addons_status != ''){
				
				$check_for_addon_update = json_decode($check_for_addon_update);
				$check_addons_status = json_decode($check_addons_status);
				
				$update_condition = isset($check_for_addon_update->new_version) && $check_for_addon_update->new_version != false;
				$status_condition = isset($check_addons_status->license) && $check_addons_status->license == 'valid';
				
				if ($update_condition && $status_condition && (version_compare($check_for_addon_update->new_version, $plugin_data['Version'], '>') == 1)) {
					
					if ($header) {
						
						?>
						<h3 style="margin: 10px 0px 0px 0px;"><?php SP()->primitives->admin_etext('Simple:Press Plugins'); ?></h3>
						<p><?php SP()->primitives->admin_etext('The following plugins have new versions available.'); ?></p>
							<table class="widefat" id="update-plugins-table">
								<tbody class="plugins">
									<?php
									$header = false;
					}
					echo "<tr class='active'><td><strong>{".$plugin_data['Name']."}</strong><br />".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $plugin_data['Version'], $check_for_addon_update->new_version, SPVERSION)."</td>
					</tr>";
				}
			
			}
		
		}else{

			if ($xml) {
					
				foreach ($xml->plugins->plugin as $latest) {
					
					if ($plugin_data['Name'] == $latest->name) {
						
						if ((version_compare($latest->version, $plugin_data['Version'], '>') == 1)) {

							if ($header) {
								?>
								<h3 style="margin: 10px 0px 0px 0px;"><?php SP()->primitives->admin_etext('Simple:Press Plugins'); ?></h3>
								<p><?php SP()->primitives->admin_etext('The following plugins have new versions available.'); ?></p>
									<table class="widefat" id="update-plugins-table">
										<tbody class="plugins">
											<?php
											$header = false;
							}
							echo "<tr class='active'><td><strong>{".$plugin_data['Name']."}</strong><br />".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $plugin_data['Version'], $latest->version, SPVERSION)."</td>
							</tr>";
						}
					}
				}
			}
		}
		
	}

	if (!$header) {
			?>
		</tbody>
	</table>
	<?php
	}
}

function spa_theme_addon_dashboard_update()
{
	$themes = SP()->theme->get_list();
	$header = true;
	$xml = sp_load_version_xml();
	
	foreach ($themes as $theme_file => $theme_data) {
		
		if (!empty($themes)) {
			
			$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
			$check_for_addon_update = SP()->options->get( 'spl_theme_versioninfo_'.$sp_theme_name);
			$check_addons_status = SP()->options->get( 'spl_theme_info_'.$sp_theme_name);
			
			if($check_for_addon_update != '' && $check_addons_status != ''){
					
				$check_for_addon_update = json_decode($check_for_addon_update);
				$check_addons_status = json_decode($check_addons_status);
				
				$update_condition = isset($check_for_addon_update->new_version) && $check_for_addon_update->new_version != false;
				$status_condition = isset($check_addons_status->license) && $check_addons_status->license == 'valid';
				
				if ($update_condition && $status_condition && (version_compare($check_for_addon_update->new_version, $theme_data['Version'], '>') == 1)) {
				
					if ($header) {
						?>
						<h3><?php SP()->primitives->admin_etext('Simple:Press Themes'); ?></h3>
						<p><?php SP()->primitives->admin_etext('The following themes have new versions available.'); ?></p>
							<table class="widefat" id="update-themes-table">
								<tbody class="plugins">
									<?php
									$header = false;
					}
					$screenshot = SPTHEMEBASEURL.$theme_file.'/'.$theme_data['Screenshot'];
					echo "
						<tr class='active'>
						<td class='plugin-title'><strong>{$theme_data['Name']}</strong>".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $theme_data['Version'], $check_for_addon_update->new_version, SPVERSION)."</td>
						</tr>";
				}
			}

		}else{

			if ($xml) {
					
				foreach ($xml->themes->theme as $latest) {
						
					if ($theme_data['Name'] == $latest->name) {
							
						if ((version_compare($latest->version, $theme_data['Version'], '>') == 1)) {

							if ($header) {
								?>
								<h3 style="margin: 10px 0px 0px 0px;"><?php SP()->primitives->admin_etext('Simple:Press Plugins'); ?></h3>
								<p><?php SP()->primitives->admin_etext('The following plugins have new versions available.'); ?></p>
									<table class="widefat" id="update-plugins-table">
										<tbody class="plugins">
											<?php
											$header = false;
							}
							echo "<tr class='active'><td><strong>{".$theme_data['Name']."}</strong><br />".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $theme_data['Version'], $latest->version, SPVERSION)."</td>
							</tr>";
						}
					}
				}
			}
		}
	}

	if (!$header) { ?>
		
			</tbody>
		</table>
	<?php
		
	}
}

/**
 * This function determines if there is an update available to the core Simple Press themes and notify to admin.
 *
 */

function spa_check_plugin_addon_update() {
	
	
	$plugins = SP()->plugin->get_list();
	$header = true;
	$update = false;
	$up = new stdClass;
	$xml = sp_load_version_xml();
	$plugin_count = 0;
	
	foreach ($plugins as $plugin_file => $plugin_data) {
		
		if (!empty($plugins) && isset($plugin_data['ItemId']) && $plugin_data['ItemId'] != '') {
			
			
			$this_path = realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['plugins']).'/'.strtok(plugin_basename($plugin_file), '/');
			
			$sp_plugin_name = sanitize_title_with_dashes($plugin_data['Name']);
			$check_for_addon_update = SP()->options->get( 'spl_plugin_versioninfo_'.$sp_plugin_name);
			$check_addons_status = SP()->options->get( 'spl_plugin_info_'.$sp_plugin_name);
			
			if($check_for_addon_update != '' && $check_addons_status != ''){
				
				$check_for_addon_update = json_decode($check_for_addon_update);
				$check_addons_status = json_decode($check_addons_status);
				
			}else{
				
				$sp_plugin_updater = spa_plugin_updater_object($plugin_file, $plugin_data);
				$check_for_addon_update = $sp_plugin_updater->check_for_addon_update();
				$data = array('edd_action' => 'check_license', 'status'=> 1);
				$check_addons_status = $sp_plugin_updater->check_addons_status($data);
				
			}
			
			$update_condition = $check_for_addon_update != '' && isset($check_for_addon_update->new_version) && $check_for_addon_update->new_version != false;
			$status_condition = $check_addons_status != '' && isset($check_addons_status->license) && $check_addons_status->license == 'valid';
			
			if ( $update_condition && $status_condition && (version_compare($check_for_addon_update->new_version, $plugin_data['Version'], '>') == 1)) {
				
				if ($header) {
					
					$plugin_count++;
					$form_action = 'update-core.php?action=do-sp-plugin-upgrade';
					?>
					<h3><?php SP()->primitives->admin_etext('Simple:Press Plugins'); ?></h3>
					<p><?php SP()->primitives->admin_etext('The following plugins have new versions available. Check the ones you want to update and then click Update SP Plugin'); ?></p>
					<p><?php SP()->primitives->admin_etext('Please Note: Any customizations you have made to plugin files will be lost'); ?></p>
					<form method="post" action="<?php echo $form_action; ?>" name="upgrade-sp-plugins" class="upgrade">
						<?php wp_nonce_field('upgrade-core'); ?>
						<p><input id="upgrade-themes" class="button" type="submit" value="<?php SP()->primitives->admin_etext('Update SP Plugins'); ?>" name="upgrade" /></p>
						<table class="widefat" id="update-plugins-table">
							<thead>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-4" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all-4"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-4" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all-4"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
								</tr>
							</tfoot>
							<tbody class="plugins">
								<?php
								$header = false;
				}
				echo "
				<tr class='active'>
				<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($plugin_file)."' /></th>
				<td><strong>{$plugin_data['Name']}</strong><br />".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $plugin_data['Version'], $check_for_addon_update->new_version, SPVERSION).'</td>
				</tr>';
				$data = new stdClass;
				$data->slug = $plugin_file;
				$data->new_version = (string) $check_for_addon_update->new_version;
				$data->url = SP_Addon_STORE_URL;
				$data->package = isset( $check_for_addon_update->package ) ? $check_for_addon_update->package : '';
				$up->response[$plugin_file] = $data;
				$update = true;
			}
		
		}else{
			
			if ($xml) {
					
				foreach ($xml->plugins->plugin as $latest) {
					
					if ($plugin_data['Name'] == $latest->name) {
						
						if ((version_compare($latest->version, $plugin_data['Version'], '>') == 1)) {
								
							if ($header) {
								$plugin_count++;
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
											<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-4" /></th>
											<th scope="col" class="manage-column"><label for="themes-select-all-4"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-4" /></th>
											<th scope="col" class="manage-column"><label for="themes-select-all-4"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
										</tr>
									</tfoot>
									<tbody class="plugins">
										<?php
										$header = false;
									}
									echo "
									<tr class='active'>
									<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($plugin_file)."' /></th>
									<td><strong>{$plugin_data['Name']}</strong><br />".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $plugin_data['Version'], $latest->version, $latest->requires).'</td>
									</tr>';
									$data = new stdClass;
									$data->slug = $plugin_file;
									$data->new_version = (string) $latest->version;
									$data->url = 'https://simple-press.com';
									$data->package = ((string) $latest->archive).'&wpupdate=1';
									$up->response[$plugin_file] = $data;
									$update = true;
						}
					}	
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

	if($plugin_count > 0){

		$sp_news = SP()->meta->get('news', 'news');
		if (empty($sp_news)) {
			$sp_news_meta = array('show' => 1, 'news' => 'There is one or more Simple:Press plugin updates available');
			SP()->meta->add('news', 'news', $sp_news_meta);
		}else{

			if(!isset($sp_news[0]['meta_value']['news'])){
				$sp_news[0]['meta_value']['show'] = 1;
				$sp_news[0]['meta_value']['news'] = 'There is one or more Simple:Press plugin updates available';
				SP()->meta->update('news', 'news', $sp_news[0]['meta_value'], $sp_news[0]['meta_id']);
			}
		}
	}
}

function spa_check_theme_addon_update(){
	
	$themes = SP()->theme->get_list();
	$header = true;
	$update = false;
	$up = new stdClass;
	$xml = sp_load_version_xml();
	$plugin_count = 0;
	
	foreach ($themes as $theme_file => $theme_data) {
		
		if (!empty($themes) && isset($theme_data['ItemId']) && $theme_data['ItemId'] != '') {
			
			$this_path = realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['themes']).'/'.$theme_file;
			
			$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
			$check_for_addon_update = SP()->options->get( 'spl_theme_versioninfo_'.$sp_theme_name);
			$check_addons_status = SP()->options->get( 'spl_theme_info_'.$sp_theme_name);
			
			if($check_for_addon_update != '' && $check_addons_status != ''){
				
				$check_addons_status = json_decode($check_addons_status);
				$check_for_addon_update = json_decode($check_for_addon_update);
				
			}else{
				
				$sp_theme_updater = spa_theme_updater_object($theme_file, $theme_data);
				$check_for_addon_update = $sp_theme_updater->check_for_addon_update();
				$data = array('edd_action' => 'check_license', 'status'=> 1);
				$check_addons_status = $sp_theme_updater->check_addons_status($data);
			}
			
			$update_condition = $check_for_addon_update != '' && isset($check_for_addon_update->new_version) && $check_for_addon_update->new_version != false;
			$status_condition = $check_addons_status != '' && isset($check_addons_status->license) && $check_addons_status->license == 'valid';
			
			if ( $update_condition && $status_condition && (version_compare($check_for_addon_update->new_version, $theme_data['Version'], '>') == 1)) {
			
				if ($header) {
					$plugin_count++;
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
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-3" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all-3"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-3" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all-3"><?php SP()->primitives->admin_etext('Select All'); ?></label></th>
								</tr>
							</tfoot>
							<tbody class="plugins">
								<?php
								$header = false;
				}
				$screenshot = SPTHEMEBASEURL.$theme_file.'/'.$theme_data['Screenshot'];
				echo "
					<tr class='active'>
					<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($theme_file)."' /></th>
					<td class='plugin-title'><img src='$screenshot' width='64' height='64' style='float:left; padding: 5px' /><strong>{$theme_data['Name']}</strong>".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $theme_data['Version'], $check_for_addon_update->new_version, SPVERSION)."</td>
					</tr>";
				$data = new stdClass;
				$data->slug = $theme_file;
				$data->stylesheet = $theme_data['Stylesheet'];
				$data->new_version = (string) $check_for_addon_update->new_version;
				$data->url = SP_Addon_STORE_URL;
				$data->package = isset( $check_for_addon_update->package ) ? $check_for_addon_update->package : '';
				$up->response[$theme_file] = $data;
				$update = true;
			}

		}else{
			
			if ($xml) {
				
				require_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
				
				foreach ($xml->themes->theme as $latest) {
						
					if ($theme_data['Name'] == $latest->name) {
							
						if ((version_compare($latest->version, $theme_data['Version'], '>') == 1)) {
							
							if ($header) {
								$plugin_count++;
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
										$screenshot = SPTHEMEBASEURL.$theme_file.'/'.$theme_data['Screenshot'];
										echo "<tr class='active'>
										<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($theme_file)."' /></th>
										<td class='plugin-title'><img src='$screenshot' width='64' height='64' style='float:left; padding: 5px' /><strong>{$theme_data['Name']}</strong>".sprintf(SP()->primitives->admin_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $theme_data['Version'], $latest->version, $latest->requires)."</td></tr>";
										$data = new stdClass;
										$data->slug = $theme_file;
										$data->stylesheet = $theme_data['Stylesheet'];
										$data->new_version = (string) $latest->version;
										$data->url = 'https://simple-press.com';
										$data->package = ((string) $latest->archive).'&wpupdate=1';
										$up->response[$theme_file] = $data;
										$update = true;
						}
					}
						
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

	if (!$header) { ?>
		
			</tbody>
		</table>
		<p><input id="upgrade-themes-2" class="button" type="submit" value="<?php SP()->primitives->admin_etext('Update SP Themes'); ?>" name="upgrade" /></p>
	</form>
	<?php
		
	} else {
		echo '<h3>'.SP()->primitives->admin_text('Simple:Press Themes').'</h3>';
		echo '<p>'.SP()->primitives->admin_text('Your SP themes are all up to date').'</p>';
	}

	if($plugin_count > 0){

		$sp_news = SP()->meta->get('news', 'news');
		if (empty($sp_news)) {
			$sp_news_meta = array('show' => 1, 'news' => 'There is one or more Simple:Press plugin updates available');
			SP()->meta->add('news', 'news', $sp_news_meta);
		}else{
			if(!isset($sp_news[0]['meta_value']['news'])){
				$sp_news[0]['meta_value']['show'] = 1;
				$sp_news[0]['meta_value']['news'] = 'There is one or more Simple:Press plugin updates available';
				SP()->meta->update('news', 'news', $sp_news[0]['meta_value'], $sp_news[0]['meta_id']);
			}
		}
	}
}