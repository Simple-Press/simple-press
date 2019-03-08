<?php
/**
 * Core Support Functions
 * This file loads at core level - all page loads for admin and front
 *
 *  $LastChangedDate: 2019-01-30 16:40:00 -0600 (Wed, 30 Jan 2019) $
 *  $Rev: 15817 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function gets the current forum system status.
 * Possible status is Ok, Install, Upgrade though plugins can filter
 *
 * @since 6.0
 *
 * @return string    current system status (Ok, Install or Upgrade)
 */
function sp_get_system_status() {
	global $wpdb;

	$current_version = SP()->options->get('sfversion');
	$current_build   = SP()->options->get('sfbuild');

	SP()->core->forumData['version'] = $current_version;
	SP()->core->forumData['build']   = $current_build;

	SP()->error->setRecording(false);

	$spError = SP()->options->get('spErrorOptions');
	SP()->error->setNotices($spError['spNoticesOff']);

	# Is Simple:Press actually installed yet?
	if (empty($current_version) || $current_build == false) {
		SP()->core->status = 'Install';

		return SP()->core->status;
	}

	# check if user is attempting to 'downgrade'
	# if so flag as upgrade and catch the downgrade in the load install routine
	if (SPBUILD < $current_build || SPVERSION < $current_version) {
		if (!defined('SPADMINFORUM')) define('SPADMINFORUM', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-forums/spa-forums.php'));
		SP()->core->status = 'Upgrade';

		return SP()->core->status;
	}

	# SP already installed - so perhaps an uograde needed
	if (($current_build < SPBUILD) || ($current_version != SPVERSION)) {
		# see if this is a upgrade to version 6+ coming from less than 5.7.2 which we dont allow automatically (must be manual)
		if ($current_version < SPVERSION && version_compare($current_version, '5.7.2') == -1) {
			SP()->core->status = 'Unallowed 6.0 Upgrade';
			return SP()->core->status;
		}

		# first check that an uograde is actually necessary or whether we can do it silently
		if (SP()->options->get('sfforceupgrade') == false && $current_build >= SPSILENT) {
			# we can do it sliently...
			require_once SP_PLUGIN_DIR.'/sp-startup/install/sp-upgrade-support.php';
			require_once SP_PLUGIN_DIR.'/sp-startup/install/sp-install-support.php';
			sp_silent_upgrade();
		} else {
			if (!defined('SPADMINFORUM')) define('SPADMINFORUM', admin_url('admin.php?page='.SP_FOLDER_NAME.'/admin/panel-forums/spa-forums.php'));
			SP()->core->status = 'Upgrade';

			return SP()->core->status;
		}
	}

	SP()->core->status = apply_filters('sph_system_status', 'ok');

	# if status is OK and the error table exists then trap php errors...
	if (SP()->core->status == 'ok' && $current_build > 6624 && !$spError['spErrorLogOff']) {
		# Set up error reporting
		SP()->error->setRecording(true);
		$wpdb->hide_errors();
		set_error_handler(array(SP()->error,
		                        'errorPHP'));
	}

	return SP()->core->status;
}

/**
 * This function is used to determine if current display is a mobile device.
 * Main plugin spMobile element is set vice anything returned.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_mobile_check() {
	SP()->core->device = sp_detect_device();
	if (SP()->core->device == 'mobile' || SP()->core->device == 'tablet') SP()->core->mobile = true;
	SP()->core->mobile = apply_filters('sph_mobile_check', SP()->core->mobile);
	SP()->core->device = apply_filters('sph_device_check', SP()->core->device);
}

/**
 * This function determines the localization of the front end and admin nd the loads that text domain.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_localisation() {
	$locale = get_locale();

	$bothSpecial  = apply_filters('sph_load_both_textdomain', array('action=permissions&',
	                                                                'action=spAckPopup'));
	$adminSpecial = apply_filters('sph_load_admin_textdomain', array('&loadform',
	                                                                 'action=forums&',
	                                                                 'action=components&',
	                                                                 'action=usergroups&',
	                                                                 'action=usermapping',
	                                                                 'action=memberships',
	                                                                 'action=integration-perm',
	                                                                 'action=integration-langs',
	                                                                 'action=profiles',
	                                                                 'action=help',
	                                                                 'action=multiselect'));

	if (SP()->primitives->strpos_array($_SERVER['QUERY_STRING'], $bothSpecial) !== false || wp_doing_ajax()) {
		$mofile = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp'].'/spa-'.$locale.'.mo';
		load_textdomain('spa', $mofile);
		$mofile = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp'].'/sp-'.$locale.'.mo';
		$mofile = apply_filters('sph_localization_mo', $mofile);
		load_textdomain('sp', $mofile);
	} else if (is_admin() || SP()->primitives->strpos_array($_SERVER['QUERY_STRING'], $adminSpecial) !== false) {
		$mofile = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp'].'/spa-'.$locale.'.mo';
		load_textdomain('spa', $mofile);
	} else {
		$mofile = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp'].'/sp-'.$locale.'.mo';
		$mofile = apply_filters('sph_localization_mo', $mofile);
		load_textdomain('sp', $mofile);
	}
}

/**
 * This function is used to determine the localization for specified plugin and loads the textdomain for it.
 *
 * @since 6.0
 *
 * @param string $domain plugin text domain
 *
 * @return void
 */
function sp_plugin_localisation($domain) {
	$locale = get_locale();
	$mofile = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp-plugins'].'/'.$domain.'-'.$locale.'.mo';
	$mofile = apply_filters('sph_localization_plugin_mo', $mofile, $domain);
	load_textdomain($domain, $mofile);
}

/**
 * This function is used to determine the localization for active theme and loads the textdomain for it.
 *
 * @since 6.0
 *
 * @param string $domain theme text domain
 *
 * @return void
 */
function sp_theme_localisation($domain) {
	$locale = get_locale();
	$mofile = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp-themes'].'/'.$domain.'-'.$locale.'.mo';
	$mofile = apply_filters('sph_localization_theme_mo', $mofile, $domain);
	load_textdomain($domain, $mofile);
	SP()->core->forumData['themedomain'] = $domain;
}

/**
 * This function gets the language code WP is using for admin pages.
 *
 * @since 6.0
 *
 * @return string    language code (ie en for English)
 */
function spa_get_language_code() {
	global $locale;

	$locale = get_locale();
	if (!empty($locale)) {
		return $locale;
	} else {
		return 'en';
	}
}

/**
 * This function reaches out to the Simple Press API to fetch the latest version XML file
 * so that version checks for updates on plugin core, plugins and themes can be done.
 *
 * @since 6.0
 *
 * @param bool $showError display error message if trouble communicating with our server
 * @param bool $usecache  return the cached xml file.  false will force new xml file to be retrieved
 *
 * @return mixed    the XML file retrieved from Simple Press API
 */
function sp_load_version_xml($showError = true, $usecache = true) {
	# clean out cache since most of this loading occurs outside of forum page
	if ($usecache) SP()->cache->clean();

	# grab a cached copy of xml file if valid - otherwise load through our api
	$xml = ($usecache) ? SP()->cache->get('xml') : '';
	if (empty($xml)) {
		$url      = 'https://simple-press.com/downloads/simple-press/simple-press_6.0.xml';
		$options  = array('timeout' => 10);
		$response = wp_remote_get($url, $options);
		$code     = wp_remote_retrieve_response_code($response);

		if (is_wp_error($response) || $code != 200) {
			if ($showError) {
				$out = '<div style="padding:0 10px;margin:10px 55px;background:white;border:1px solid red">';
				$out .= '<p style="font-size:13px;font-weight:bold;line-height:1.1em;">'.SP()->primitives->admin_text('Your server has returned a status code of');
				$out .= $code;
				$out .= SP()->primitives->admin_text('while attempting to communicate with the Simple:Press server to establish up to date version information.').'<br />';

				if (is_wp_error($response)) {
					$errs = $response->get_error_messages();
					if (!empty($errs)) {
						$errs = htmlspecialchars(implode('; ', $errs));
						$out .= SP()->primitives->admin_text('Additionally, WordPress reported the following').': '.$errs.'<br />';
					}
				}

				$out .= SP()->primitives->admin_text('WordPress will retry this operation automatically but if the condition persists we firstly
						recommend seeking assistance from your hosting support team and then contacting
						Simple:Press support if they are unable to help.').'</p>';
				$out .= '</div>';
				echo $out;
			}

			return false;
		}
		$body = wp_remote_retrieve_body($response);
		if (!$body) return '';
		$xml = new SimpleXMLElement($body);

		# Now cache off the xml file
		$data    = array();
		$data[0] = $xml->asXML();;
		if ($usecache) SP()->cache->add('xml', $data);
	} else {
		$xml = simplexml_load_string($xml[0]);
	}

	return $xml;
}

/**
 * This function determines if there is an update available to the core Simple Press plugin.
 *
 * @since 6.0
 *
 * @param string $domain theme text domain
 *                       * @return void
 */
function sp_check_for_updates() {
	$xml = sp_load_version_xml();
	if ($xml) {
		# make sure SP is installed
		$installed_build = SP()->options->get('sfbuild');
		if (empty($installed_build)) return;

		$update  = false;
		$plugins = SP()->plugin->get_list();
		if (!empty($plugins)) {
			$up = new stdClass;
			foreach ($plugins as $file => $installed) {
				foreach ($xml->plugins->plugin as $latest) {
					if ($installed['Name'] == $latest->name) {
						if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
							$data                = new stdClass;
							$data->slug          = $file;
							$data->new_version   = (string)$latest->version;
							$data->url           = 'https://simple-press.com';
							$data->package       = ((string)$latest->archive).'&wpupdate=1';
							$up->response[$file] = $data;
							$update              = true;
						}
					}
				}
			}
		}

		if ($update) {
			set_site_transient('sp_update_plugins', $up);
		} else {
			delete_site_transient('sp_update_plugins');
		}

		require_once SP_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
		$update = false;
		$themes = SP()->theme->get_list();
		if (!empty($themes)) {
			$up = new stdClass;
			foreach ($themes as $file => $installed) {
				foreach ($xml->themes->theme as $latest) {
					if ($installed['Name'] == $latest->name) {
						if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
							$data                = new stdClass;
							$data->slug          = $file;
							$data->stylesheet    = $installed['Stylesheet'];
							$data->new_version   = (string)$latest->version;
							$data->url           = 'https://simple-press.com';
							$data->package       = ((string)$latest->archive).'&wpupdate=1';
							$up->response[$file] = $data;
							$update              = true;
						}
					}
				}
			}
		}

		if ($update) {
			set_site_transient('sp_update_themes', $up);
		} else {
			delete_site_transient('sp_update_themes');
		}
	}
}

/**
 * This function hooks into the wp_avatar function and replaces with the user's Simple Press avatar.
 *
 * @since 6.0
 *
 * @param string       $avatar      wp avatar to be loaded
 * @param int | string $id_or_email either user object, user id or user email
 * @param int          $size        size of avatar to be displayed
 *
 * @return array    current theme to be displayed
 */
function sp_wp_avatar($avatar, $id_or_email, $size) {
	require_once SP_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';

	# this could be user id, email or comment object
	# if comment object want a user id or email address
	# pass other two striaght through
	if (is_object($id_or_email)) { # comment object passed in
		if (!empty($id_or_email->user_id)) {
			$id   = (int)$id_or_email->user_id;
			$user = get_userdata($id);
			$arg  = ($user) ? $id : '';
		} else if (!empty($id_or_email->comment_author_email)) {
			$arg = $id_or_email->comment_author_email;
		}
	} else {
		$arg = $id_or_email;
	}

	# replace the wp avatar image src with our sp img src
	$pattern  = '/<img[^>]+src[\\s=\'"]+([^"\'>\\s]+)/is';
	$sfavatar = sp_UserAvatar("echo=0&link=none&size=$size&context=user&wp=$avatar", $arg);
	preg_match($pattern, $sfavatar, $sfmatch);
	preg_match($pattern, $avatar, $wpmatch);
	$avatar = str_replace($wpmatch[1], $sfmatch[1], $avatar);

	return $avatar;
}

function sp_display_inspector($dName, $dObject) {
	if (empty(SP()->user->thisUser->inspect)) return;

	$i = SP()->user->thisUser->inspect;

	if ($dName == 'control') {
		if (array_key_exists('con_pageData', $i) && $i['con_pageData']) {
			ashow(SP()->rewrites->pageData, SP()->user->thisUser->ID, 'pageData');
		}
		if (array_key_exists('con_forumData', $i) && $i['con_forumData']) {
			ashow(SP()->core->forumData, SP()->user->thisUser->ID, 'forumData');
		}
		if (array_key_exists('con_thisUser', $i) && $i['con_thisUser']) {
			ashow(SP()->user->thisUser, SP()->user->thisUser->ID, 'spThisUser');
		}
		if (array_key_exists('con_device', $i) && $i['con_device']) {
			ashow(SP()->core->device, SP()->user->thisUser->ID, 'spDevice');
		}
	} else {
		# called direct from class file
		if (array_key_exists($dName, $i) && $i[$dName]) {
			if (!empty($dObject)) {
				$dName = ltrim(strrchr($dName, '_'), '_');
				ashow($dObject, SP()->user->thisUser->ID, $dName);
			}
		}
	}
}

function sp_php_overrides() {
	global $is_IIS;

	# hack for some IIS installations
	if ($is_IIS && @ini_get('error_log') == '') @ini_set('error_log', 'syslog');

	# try to increase backtrack limit
	if ((int)@ini_get('pcre.backtrack_limit') < 10000000000) @ini_set('pcre.backtrack_limit', 10000000000);

	# try to increase php memory
	if (function_exists('memory_get_usage') && ((int)@ini_get('memory_limit') < abs(intval('64M')))) @ini_set('memory_limit', '64M');

	# try to increase cpu time
	if ((int)@ini_get('max_execution_time') < 120) @ini_set('max_execution_time', '120');
}

function sp_build_site_auths_cache() {
	$auths = SP()->DB->table(SPAUTHS);
	foreach ($auths as $auth) {
		# is auth active?
		if ($auth->active) {
			# save auth name to auth id mapping for quick ref
			SP()->core->forumData['auths_map'][$auth->auth_name] = $auth->auth_id;

			# save off all auth info
			SP()->core->forumData['auths'][$auth->auth_id] = $auth;
		}
	}
}

function sp_setup_forum_data() {
	# Main admin options
	SP()->core->forumData['admin']    = SP()->options->get('sfadminsettings');
	SP()->core->forumData['lockdown'] = SP()->options->get('sflockdown');

	SP()->core->forumData['editor'] = 0;

	SP()->core->forumData['defAvatars'] = SP()->options->get('spDefAvatars');

	# Display array
	SP()->core->forumData['display'] = SP()->options->get('sfdisplay');

	# Current theme data
	SP()->core->forumData['theme'] = SP()->theme->get_current();

	# if mobile device then force integrated editor toolbar to on
	if (SP()->core->device == 'mobile' || SP()->core->device == 'tablet') {
		SP()->core->forumData['display']['editor']['toolbar'] = true;
		if (SP()->core->device == 'mobile') {
			SP()->core->forumData['mobile-display'] = SP()->options->get('sp_mobile_theme');
		} else {
			SP()->core->forumData['mobile-display'] = SP()->options->get('sp_tablet_theme');
		}
	}

	# Pre-define a few others
	SP()->core->forumData['canonicalurl'] = false;

	# set up array of disabled forums
	SP()->core->forumData['disabled_forums'] = SP()->DB->select('SELECT forum_id FROM '.SPFORUMS.' WHERE forum_disabled=1', 'col', ARRAY_A);

	SP()->core->forumData['forum-admins'] = sp_get_admins();
}

/**
 * This function returns display code for a hidden nonce field for ajax actions.
 *
 * @access public
 *
 * @since 6.0
 *
 * @return string
 */
function sp_create_nonce($action) {
	return '<input type="hidden" name="'.$action.'" value="'.wp_create_nonce($action).'" />'."\n";
}

function sp_get_admins() {
	$administrators = array();

	# get all the administrators
	$admins = SP()->DB->table(SPMEMBERS, 'admin=1');
	if (!empty($admins)) {
		foreach ($admins as $admin) {
			if (!empty($admin)) $administrators[$admin->user_id] = $admin->display_name;
		}
	}

	return $administrators;
}

function sp_get_all_roles() {
	return SP()->DB->table(SPROLES, '', '', 'role_id');
}

# Version: 5.0

function sp_get_forum_permissions($forum_id) {
	return SP()->DB->table(SPPERMISSIONS, "forum_id=$forum_id", '', 'permission_role');
}

function sp_send_email($mailto, $mailsubject, $mailtext, $replyto = '', $from = '') {
	SP()->core->forumData['fromAddress'] = $replyto;
	$sfmail                                = SP()->options->get('sfmail');
	if ((isset($sfmail['sfmailuse']) && $sfmail['sfmailuse']) || (!empty($from))) {
		add_filter('wp_mail_from', 'sp_mail_filter_from', 100);
		add_filter('wp_mail_from_name', 'sp_mail_filter_name', 100);
	}

	# reply-to goes in headers if provided
	$headers = (!empty($replyto)) ? "reply-to: $replyto" : '';

	# alert plugins before sending email
	do_action('sph_email_send_before');

	$email = wp_mail($mailto, $mailsubject, $mailtext, $headers);

	# alert plugins after sending email
	do_action('sph_email_send_after');

	# clear global from address
	SP()->core->forumData['fromAddress'] = '';

	# prepare email response
	$email_sent = array();
	if ($email == false) {
		$email_sent[0] = false;
		$email_sent[1] = SP()->primitives->front_text('Email notification failed');
	} else {
		$email_sent[0] = true;
		$email_sent[1] = SP()->primitives->front_text('Email notification sent');
	}

	return $email_sent;
}

# ------------------------------------------------------------------
# sp_mail_filter_from()
#
# Version: 5.0
# Filter Call
# Sets up the 'from' email options
#	$from:		Passed in to filter
# ------------------------------------------------------------------

function sp_mail_filter_from($from) {
	$replyAddress = SP()->core->forumData['fromAddress'];
	if (empty($replyAddress)) {
		$sfmail     = SP()->options->get('sfmail');
		$mailfrom   = isset($sfmail['sfmailfrom']) ? $sfmail['sfmailfrom'] : '';
		$maildomain = isset($sfmail['sfmaildomain']) ? $sfmail['sfmaildomain'] : '';
		if ((!empty($mailfrom)) && (!empty($maildomain))) $from = $mailfrom.'@'.$maildomain;
	} else {
		$from = $replyAddress;
	}

	# remove the fitler that got us here
	remove_filter('wp_mail_from', 'sp_mail_filter_from', 100);

	return $from;
}

# ------------------------------------------------------------------
# sp_mail_filter_name()
#
# Version: 5.0
# Filter Call
# Sets up the 'from' email options
#	$from:		Passed in to filter
# ------------------------------------------------------------------

function sp_mail_filter_name($from) {
	$sfmail     = SP()->options->get('sfmail');
	$mailsender = isset($sfmail['sfmailsender']) ? $sfmail['sfmailsender'] : '';
	if (!empty($mailsender)) $from = $mailsender;

	# remove the fitler that got us here
	remove_filter('wp_mail_from_name', 'sp_mail_filter_name', 100);

	return $from;
}

# ------------------------------------------------------------------
# sp_get_ip()
#
# Version: 5.0
# Return the IP address of the current user
# Checks HTTP_X_FORWARDED_FOR in case of proxy or load balancer
# ------------------------------------------------------------------

function sp_get_ip() {
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { # used by proxies and load balancers
	    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	    $ip = $_SERVER['HTTP_CLIENT_IP']; # client IP set
	} else if (!empty($_SERVER['REMOTE_ADDR'])) {
	    $ip = $_SERVER['REMOTE_ADDR']; # remmote address set
	} else {
	    $ip = ''; # general case - just return empty string
	}

	if (strpos($ip, ',') !== false) {
		$ip_array = explode(',', $ip);
		$ip       = array_pop($ip_array);
	}
	if ($ip != '') $ip = long2ip(ip2long($ip));

	return $ip;
}

/*
 * Based upon - Categorizr Version 1.1
 * http://www.brettjankord.com/2012/01/16/categorizr-a-modern-device-detection-script/
 * Written by Brett Jankord - Copyright © 2011
 * Thanks to Josh Eisma for helping with code review
 */

function sp_detect_device() {

	$d = 'desktop';
	$m = 'mobile';
	$t = 'tablet';

	# check for empty $_SERVER['HTTP_USER_AGENT']
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		return $d;
	}

	# Check user agents
	# Set User Agent = $ua
	$ua = $_SERVER['HTTP_USER_AGENT'];

	if (strpos($ua, ' CrOS ')) {
		# ua for a Google Chromebook
		return $d;
	} else if ((preg_match('/iP(a|ro)d/i', $ua)) || (preg_match('/tablet/i', $ua)) && (!preg_match('/RX-34/i', $ua)) || (preg_match('/FOLIO/i', $ua))) {
		# user agent is a Tablet
		return $t;
	} else if ((preg_match('/Linux/i', $ua)) && (preg_match('/Android/i', $ua)) && (!preg_match('/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i', $ua))) {
		# user agent is an Android Tablet
		return $t;
	} else if ((preg_match('/Kindle/i', $ua)) || (preg_match('/Mac.OS/i', $ua)) && (preg_match('/Silk/i', $ua))) {
		# user agent is a Kindle or Kindle Fire
		return $t;
	} else if ((preg_match('/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i', $ua)) || (preg_match('/MB511/i', $ua)) && (preg_match('/RUTEM/i', $ua))) {
		# user agent is a pre Android 3.0 Tablet
		return $t;
	} else if ((preg_match('/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder/i', $ua))) {
		# user agent is unique Mobile User Agent
		return $m;
	} else if ((preg_match('/Opera/i', $ua)) && (preg_match('/Windows.NT.5/i', $ua)) && (preg_match('/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i', $ua))) {
		# user agent is an odd Opera User Agent - http://goo.gl/nK90K
		return $m;
	} else if ((preg_match('/Windows.(NT|XP|ME|9)/', $ua)) && (!preg_match('/Phone/i', $ua)) || (preg_match('/Win(9|.9|NT)/i', $ua))) {
		# user agent is Windows Desktop
		return $d;
	} else if ((preg_match('/Macintosh|PowerPC/i', $ua)) && (!preg_match('/Silk/i', $ua))) {
		# user agent is Mac Desktop
		return $d;
	} else if ((preg_match('/Linux/i', $ua)) && (preg_match('/X11/i', $ua))) {
		# user agent is a Linux Desktop
		return $d;
	} else if ((preg_match('/Solaris|SunOS|BSD/i', $ua))) {
		# user agent is a Solaris, SunOS, BSD Desktop
		return $d;
	} else if ((preg_match('/Bot|Crawler|Spider|Yahoo|ia_archiver|Covario-IDS|findlinks|DataparkSearch|larbin|Mediapartners-Google|NG-Search|Snappy|Teoma|Jeeves|TinEye/i', $ua)) && (!preg_match('/Mobile/i', $ua))) {
		# user agent is a Desktop BOT/Crawler/Spider
		return $d;
	} else if ((preg_match('/GoogleTV|SmartTV|Internet.TV|NetCast|NETTV|AppleTV|boxee|Kylo|Roku|DLNADOC|CE\-HTML/i', $ua))) {
		# user agent is a smart TV - http://goo.gl/FocDk
		return $d;
	} else if ((preg_match('/Xbox|PLAYSTATION.3|Wii/i', $ua))) {
		# user agent is a TV Based Gaming Console
		return $d;
	} else {
		# assume it is a Mobile Device
		return $m;
	}
}

# ------------------------------------------------------------------
# sp_create_slug()
#
# Create a new slug
#	$title:		Forum or Topic title
#	$checkdup	Check for duplicates (optional)
#	$table:		db table for dupe check
#	$column:	db column for dupe check
# ------------------------------------------------------------------
# Version: 5.0

function sp_create_slug($title, $checkdup = true, $table = '', $column = '') {
	$slug = sanitize_title($title);
	if ($checkdup) $slug = sp_check_slug_unique($slug, $table, $column);
	$slug = apply_filters('sph_create_slug', $slug, $table, $column);

	return $slug;
}

# ------------------------------------------------------------------
# sp_check_slug_unique()
#
# Version: 5.0
# Check new slug is unique and not used. Add numeric suffix if
# exists. If slug receved is empty then return empty.
#	$title:		Forum or Topic title new slug
#	$table:		db table for dupe check
#	$column:	db column for dupe check
# ------------------------------------------------------------------

function sp_check_slug_unique($title, $table, $column) {
	if (empty($title) || empty($table) || empty($column)) return '';

	$suffix    = 1;
	$testtitle = $title;
	while (1) {
		$check = SP()->DB->table($table, "$column='$testtitle'", $column);
		if (empty($check)) break;
		$testtitle = $title.'-'.$suffix;
		$suffix++;
	}

	return $testtitle;
}

# ------------------------------------------------------------------
# sp_update_post_urls()
#
# Version: 5.0
# Updates slugs in posts for forum or topic if they are changed
#	$old		old slug
#	$new		replacement slug
# ------------------------------------------------------------------

function sp_update_post_urls($old, $new) {
	global $wpdb;

	if (empty($old) || empty($new)) return;
	$posts = SP()->DB->table(SPPOSTS, 'post_content LIKE "%/'.SP()->filters->esc_sql($wpdb->esc_like($old)).'%"', '');
	if (!empty($posts)) {
		foreach ($posts as $p) {
			$pc = str_replace('/'.$old, '/'.$new, SP()->editFilters->content($p->post_content));
			$pc = SP()->saveFilters->content($pc, 'edit');
			SP()->DB->execute('UPDATE '.SPPOSTS." SET post_content = '$pc' WHERE post_id=".$p->post_id);
		}
	}
}

/**
 * This function allows plugins to add glossary keywords.
 *
 * @since 6.0
 *
 * @param $string $key        glossary keyword to add
 * @param $string $plugin        plugin name for keyword reference
 *
 * @return int        glossary id for the keyword added
 */
function sp_add_glossary_keyword($key, $plugin) {
	# does it exist already?
	$sql = "SELECT id FROM ".SPADMINKEYWORDS." WHERE keyword='$key'";
	$id  = SP()->DB->select($sql, 'var');
	if ($id) return $id;

	# we need to create it then
	$sql = "INSERT INTO ".SPADMINKEYWORDS." (`keyword`, `plugin`) VALUES ('$key','$plugin');";
	SP()->DB->execute($sql);

	return SP()->rewrites->pageData['insertid'];
}

/**
 * This function allows plugins to remove their glossary keywords, normally when uninstalling.
 *
 * @since 6.0
 *
 * @param $string $plugin        plugin name for glossary keywords
 *
 * @return void
 */
function sp_remove_glossary_plugin($plugin) {
	$sql = "DELETE FROM ".SPADMINKEYWORDS." WHERE plugin='$plugin'";
	SP()->DB->execute($sql);
	$sql = "DELETE FROM ".SPADMINTASKS." WHERE plugin='$plugin'";
	SP()->DB->execute($sql);
}

function sp_render_group_forum_select($goURL = false, $valueURL = false, $showSelects = true, $showFirstRow = true, $firstRowLabel = '', $id = '', $class = '', $length = 40) {
	if (empty($firstRowLabel)) $firstRowLabel = SP()->primitives->front_text('Select Forum').':';
	if (empty($class)) {
		$class  = 'spControl';
		$indent = '&nbsp;&nbsp;';
	} else {
		$indent = '';
	}

	# load data and check if empty or denied
	$groups = new spcGroupView('', false);
	if ($groups->groupViewStatus == 'no access' || $groups->groupViewStatus == 'no data') return '';
	$level = 0;
	$out   = '';

	if ($groups->pageData) {
		if ($showSelects) {
			$out = "<select id='$id' class='$class' name='$id' ";
			if ($goURL) $out .= 'onchange="javascript:spj.changeUrl(this)"';
			$out .= ">\n";
		}
		if ($showFirstRow && $firstRowLabel) $out .= '<option>'.$firstRowLabel."</option>\n";
		foreach ($groups->pageData as $group) {
			$out .= '<optgroup class="spList" label="'.$indent.SP()->primitives->create_name_extract($group->group_name, $length).'">'."\n";
			if ($group->forums) {
				foreach ($group->forums as $forum) {
					if ($valueURL) {
						$out .= '<option value="'.$forum->forum_permalink.'">';
					} else {
						$out .= '<option value="'.$forum->forum_id.'">';
					}
					$out .= str_repeat($indent, $level).SP()->primitives->create_name_extract($forum->forum_name, $length)."</option>\n";
					if (!empty($forum->subforums)) $out .= sp_compile_forums($forum->subforums, $forum->forum_id, 1, $valueURL);
				}
			}
			$out .= '</optgroup>';
		}
		if ($showSelects) $out .= "</select>\n";
	}

	return $out;
}

# Version: 5.0

function sp_compile_forums($forums, $parent = 0, $level = 0, $valueURL = false) {
	$out    = '';
	$indent = '&nbsp;&rarr;&nbsp;';
	foreach ($forums as $forum) {
		if ($forum->parent == $parent && $forum->forum_id != $parent) {
			if ($valueURL) {
				$out .= '<option value="'.$forum->forum_permalink.'">';
			} else {
				$out .= '<option value="'.$forum->forum_id.'">';
			}
			$out .= str_repeat($indent, $level).SP()->primitives->create_name_extract($forum->forum_name)."</option>\n";
			if (!empty($forum->children)) $out .= sp_compile_forums($forums, $forum->forum_id, $level + 1, $valueURL);
		}
	}

	return $out;
}

# Version: 5.5.7

function sp_compile_forums_mobile($forums, $parent = 0, $level = 0, $valueURL = false) {
	$out    = '';
	$indent = '&nbsp;&nbsp;&nbsp;&nbsp;';
	foreach ($forums as $forum) {
		if ($forum->parent == $parent && $forum->forum_id != $parent) {
			$out .= '<p><a href="'.$forum->forum_permalink.'">';
			$out .= str_repeat($indent, $level).SP()->primitives->create_name_extract($forum->forum_name)."</a></p>\n";
			if (!empty($forum->children)) $out .= sp_compile_forums_mobile($forums, $forum->forum_id, $level + 1, $valueURL);
		}
	}

	return $out;
}

function sp_ten_minutes_cron_interval( $schedules ) {
	
    $schedules['ten_minutes'] = array(
    
        'interval' => 60 * 10,
        'display'  => esc_html__( 'Every Ten Minutes' ),
    );
 
    return $schedules;
}

/**
 * This function determines if there is an update available to the core Simple Press plugin and themes.
 *
 */

if ( ! function_exists( 'sp_plugin_updater_object' ) ) {
	
	function sp_plugin_updater_object($plugin_file, $plugin_data){
		
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
		
		$sp_plugin_updater = new SPPluginUpdater( SP_Addon_STORE_URL, $this_path, $api_data);
		
		return $sp_plugin_updater;
	}
}

if ( ! function_exists( 'sp_theme_updater_object' ) ) {
	
	
	function sp_theme_updater_object($theme_file, $theme_data){
		
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
		
		$sp_theme_updater = new SPPluginUpdater( SP_Addon_STORE_URL, $this_path, $api_data);
		
		return $sp_theme_updater;
	}
}

function sph_check_addons_status(){
	
	$plugins = SP()->plugin->get_list();
	$sp_return_update_plugins = 0;
	
	if (!empty($plugins)) {
		
		$now = new DateTime();
		$now->format('Y-m-d H:i:s');
		SP()->options->update( 'spl_plugins_api_time',  $now->getTimestamp());
		
		foreach ($plugins as $plugin_file => $plugin_data) {
				
			if(isset($plugin_data['ItemId']) && $plugin_data['ItemId'] != ''){
				
				$sp_plugin_updater = sp_plugin_updater_object($plugin_file, $plugin_data);

				$sp_plugin_name = sanitize_title_with_dashes($plugin_data['Name']);			
				$update_status_option 	= 'spl_plugin_stats_'.$sp_plugin_name;
				$update_info_option 	= 'spl_plugin_info_'.$sp_plugin_name;
				$update_version_option 	= 'spl_plugin_versioninfo_'.$sp_plugin_name;
				
				$data = array('edd_action' => 'check_license', 'update_status_option'=>$update_status_option, 'update_info_option'=>$update_info_option, 'update_version_option'=>$update_version_option);
				
				$sp_return_plugin_updater = $sp_plugin_updater->check_addons_status($data);

				if($sp_return_plugin_updater && isset($sp_return_plugin_updater->license) && $sp_return_plugin_updater->license === 'valid'){

					$sp_return_update_plugins = 1;
				}
				
			}
		}
	
	}
	
	$themes = SP()->theme->get_list();
	$sp_return_update_themes = 0;
	
	if (!empty($themes)) {
		
		$now = new DateTime();
		$now->format('Y-m-d H:i:s');
		SP()->options->update( 'spl_themes_api_time',  $now->getTimestamp());
		
		foreach ($themes as $theme_file => $theme_data) {
			
			if(isset($theme_data['ItemId']) && $theme_data['ItemId'] != ''){
			
				$sp_theme_updater = sp_theme_updater_object($theme_file, $theme_data);
				
				$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
				$update_status_option 	= 'spl_theme_stats_'.$sp_theme_name;
				$update_info_option 	= 'spl_theme_info_'.$sp_theme_name;
				$update_version_option 	= 'spl_theme_versioninfo_'.$sp_theme_name;
				
				$data = array('edd_action' => 'check_license', 'update_status_option'=>$update_status_option, 'update_info_option'=>$update_info_option, 'update_version_option'=>$update_version_option);
				
				$sp_return_theme_updater = $sp_theme_updater->check_addons_status($data);

				if($sp_return_theme_updater && isset($sp_return_theme_updater->license) && $sp_return_theme_updater->license === 'valid'){

					$sp_return_update_themes = 1;
				}
			}
		}
	
	}

	$return = array('sp_return_update_themes' => $sp_return_update_themes,'sp_return_update_plugins' => $sp_return_update_plugins );
	return $return;
}


/**
 * This function determines if there is an update available to the core Simple Press plugins and notify to admin.
 *
 */
function sph_check_for_addons_updates() {
	
	$plugins = SP()->plugin->get_list();
	
	$update = false;
	
	foreach ($plugins as $plugin_file => $plugin_data) {
		
		if (!empty($plugins) && isset($plugin_data['ItemId']) && $plugin_data['ItemId'] != '') {
			
			$now = new DateTime();
			$now->format('Y-m-d H:i:s');
			SP()->options->update( 'spl_plugins_api_time',  $now->getTimestamp());
			
			$this_path = realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['plugins']).'/'.strtok(plugin_basename($plugin_file), '/');
			
			$up = new stdClass;
			
			$sp_plugin_updater = sp_plugin_updater_object($plugin_file, $plugin_data);
			
			$check_for_addon_update = $sp_plugin_updater->check_for_addon_update();
			
			if ((version_compare($check_for_addon_update->new_version, $plugin_data['Version'], '>') == 1)) {
				
				$data = new stdClass;
				$data->slug = $plugin_file;
				$data->new_version = (string) $check_for_addon_update->new_version;
				$data->url = SP_Addon_STORE_URL;
				$data->package = $check_for_addon_update->package;
				$up->response[$plugin_file] = $data;
				$update = true;
			}
		}
	}

	if ($update) {
		set_site_transient('sp_update_plugins', $up);
	} else {
		delete_site_transient('sp_update_plugins');
	}
	
	$update = false;
	$themes = SP()->theme->get_list();
	
	foreach ($themes as $theme_file => $theme_data) {
		
		if (!empty($themes) && isset($theme_data['ItemId']) && $theme_data['ItemId'] != '') {
			
			$now = new DateTime();
			$now->format('Y-m-d H:i:s');
			SP()->options->update( 'spl_themes_api_time',  $now->getTimestamp());
			
			$this_path = realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['themes']).'/'.$theme_file;
				
			$sp_theme_updater = sp_theme_updater_object($theme_file, $theme_data);
			
			$check_for_addon_update = $sp_theme_updater->check_for_addon_update();
			
			if ((version_compare($check_for_addon_update->new_version, $theme_data['Version'], '>') == 1)) {
				
				$data = new stdClass;
				$data->slug = $plugin_file;
				$data->new_version = (string) $check_for_addon_update->new_version;
				$data->url = SP_Addon_STORE_URL;
				$data->package = $check_for_addon_update->package;
				$up->response[$plugin_file] = $data;
				$update = true;
			}
		}
	}

	if ($update) {
		set_site_transient('sp_update_themes', $up);
	} else {
		delete_site_transient('sp_update_themes');
	}
	
}