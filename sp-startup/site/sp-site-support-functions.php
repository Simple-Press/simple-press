<?php
/*
Simple:Press
DESC: Sitewide Functions back and front end
$LastChangedDate: 2017-05-20 17:44:46 -0500 (Sat, 20 May 2017) $
$Rev: 15386 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE
#	Loaded by core - globally required by back end/admin for all pages
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_set_rewrite_rules()
# Setup the forum rewrite rules
# ------------------------------------------------------------------
function sp_set_rewrite_rules($rules) {
	global $wp_rewrite;

	$slug = sp_get_option('sfslug');
	$slugmatch = $slug;
	if ($wp_rewrite->using_index_permalinks() && $wp_rewrite->root == 'index.php/') $slugmatch = 'index.php/'.$slugmatch; # handle PATHINFO permalinks

	$slugmatch = apply_filters('sph_rewrite_rules_slug', $slugmatch);

	$sf_rules = array();
	$sf_rules = apply_filters('sph_rewrite_rules_start', $sf_rules, $slugmatch, $slug);

	# admin new posts list
	$sf_rules[$slugmatch.'/newposts/?$'] = 'index.php?pagename='.$slug.'&sf_newposts=all';

	# members list?
	$sf_rules[$slugmatch.'/members/?$'] = 'index.php?pagename='.$slug.'&sf_members=list';
	$sf_rules[$slugmatch.'/members/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_members=list&sf_page=$matches[1]';

	# match profile?
	$sf_rules[$slugmatch.'/profile/?$'] = 'index.php?pagename='.$slug.'&sf_profile=edit';
	$sf_rules[$slugmatch.'/profile/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_profile=show&sf_member=$matches[1]';
	$sf_rules[$slugmatch.'/profile/([^/]+)/edit/?$'] = 'index.php?pagename='.$slug.'&sf_profile=edit&sf_member=$matches[1]';

	# match forum and topic with pages
	$sf_rules[$slugmatch.'/rss/?$'] = 'index.php?pagename='.$slug.'&sf_feed=all'; # match main rss feed
	$sf_rules[$slugmatch.'/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_feed=all&sf_feedkey=$matches[1]'; # match main rss feed with feedkey
	$sf_rules[$slugmatch.'/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]'; # match forum
	$sf_rules[$slugmatch.'/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_page=$matches[2]'; # match forum with page
	$sf_rules[$slugmatch.'/([^/]+)/rss/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_feed=forum'; # match forum rss feed
	$sf_rules[$slugmatch.'/([^/]+)/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_feed=forum&sf_feedkey=$matches[2]'; # match forum rss feed with feedkey
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]';	 # match topic
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_page=$matches[3]'; # match topic with page
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/rss/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_feed=topic'; # match topic rss feed
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_feed=topic&sf_feedkey=$matches[3]'; # match topic rss feed with feedkey

	$sf_rules = apply_filters('sph_rewrite_rules_end', $sf_rules, $slugmatch, $slug);
	$rules = array_merge($sf_rules, $rules);

	return $rules;
}

# ------------------------------------------------------------------
# sp_set_query_vars()
# Setup the forum query variables
# ------------------------------------------------------------------
function sp_set_query_vars($vars) {
	# forums and topics
	$vars[] = 'sf_forum';
	$vars[] = 'sf_topic';
	$vars[] = 'sf_page';

	# new posts
	$vars[] = 'sf_newposts';

	# members list
	$vars[] = 'sf_members';

	# profile
	$vars[] = 'sf_profile';
	$vars[] = 'sf_member';

	$vars[] = 'sf_feed';
	$vars[] = 'sf_feedkey';

	$vars = apply_filters('sph_query_vars', $vars);

	return $vars;
}

# ------------------------------------------------------------------
# sp_get_system_status()
# Determine if forum can be run or if it requires install/upgrade
# Sets $spStatus to 'ok', 'Install' or 'Upgrade'
# ------------------------------------------------------------------
function sp_get_system_status() {
	global $wpdb, $spStatus, $spGlobals;

	$current_version = sp_get_option('sfversion');
	$current_build = sp_get_option('sfbuild');

	$spGlobals['version'] = $current_version;
	$spGlobals['build'] = $current_build;
	$spGlobals['record-errors'] = false;

	$spError = sp_get_option('spErrorOptions');
	$spGlobals['error-log-off'] = $spError['spErrorLogOff'];
	$spGlobals['notices-off']	= $spError['spNoticesOff'];

	# Is Simple:Press actually installed yet?
	if (empty($current_version) || $current_build == false) {
		$spStatus = 'Install';
		return;
	}

	# check if user is attempting to 'downgrade'
	# if so flag as upgrade and catch the downgrade in the load install routine
	if (SPBUILD < $current_build || SPVERSION < $current_version) {
		if (!defined('SFADMINFORUM')) define('SFADMINFORUM', admin_url('admin.php?page=simple-press/admin/panel-forums/spa-forums.php'));
		$spStatus = 'Upgrade';
		return;
	}

	# SP already installed - so perhaps an uograde needed
	if (($current_build < SPBUILD) || ($current_version != SPVERSION)) {
		# first check that an uograde is actually necessary or whether we can do it silently
		if (sp_get_option('sfforceupgrade') == false && $current_build >= SPSILENT) {
			# we can do it sliently...
			require_once SF_PLUGIN_DIR.'/sp-startup/install/sp-upgrade-support.php';
			sp_silent_upgrade();
		} else {
			if (!defined('SFADMINFORUM')) define('SFADMINFORUM', admin_url('admin.php?page=simple-press/admin/panel-forums/spa-forums.php'));
			$spStatus = 'Upgrade';
			return;
		}
	}

	$spStatus = apply_filters('sph_system_status', 'ok');

	# if status is OK and the error table exists then trap php errors...
	if ($spStatus == 'ok' && $current_build > 6624 && $spGlobals['error-log-off'] == false) {
		# Set up error reporting
		$spGlobals['record-errors'] = true;
		$wpdb->hide_errors();
		set_error_handler('sp_construct_php_error');
	}
	return $spStatus;
}

# ------------------------------------------------------------------
# sp_localisation()
# Setup the forum localisation
# ------------------------------------------------------------------
function sp_localisation() {
	# i18n support
	global $spPaths;

	$locale = get_locale();

	$bothSpecial = apply_filters('sph_load_both_textdomain', array(
			'action=permissions&',
			'action=spAckPopup'
		));
	$adminSpecial = apply_filters('sph_load_admin_textdomain', array(
			'&loadform',
			'action=forums&',
			'action=components&',
			'action=usergroups&',
			'action=usermapping',
			'action=memberships',
			'action=integration-perm',
			'action=integration-langs',
			'action=profiles',
			'action=help',
			'action=multiselect'
		));

	if (sp_strpos_arr($_SERVER['QUERY_STRING'], $bothSpecial) !== false || wp_doing_ajax()) {
		$mofile = SF_STORE_DIR.'/'.$spPaths['language-sp'].'/spa-'.$locale.'.mo';
		load_textdomain('spa', $mofile);
		$mofile = SF_STORE_DIR.'/'.$spPaths['language-sp'].'/sp-'.$locale.'.mo';
		$mofile = apply_filters('sph_localization_mo', $mofile);
		load_textdomain('sp', $mofile);
	} else if (is_admin() || sp_strpos_arr($_SERVER['QUERY_STRING'], $adminSpecial) !== false) {
		$mofile = SF_STORE_DIR.'/'.$spPaths['language-sp'].'/spa-'.$locale.'.mo';
		load_textdomain('spa', $mofile);
	} else {
		$mofile = SF_STORE_DIR.'/'.$spPaths['language-sp'].'/sp-'.$locale.'.mo';
		$mofile = apply_filters('sph_localization_mo', $mofile);
		load_textdomain('sp', $mofile);
	}
}

function sp_plugin_localisation($domain) {
	global $spPaths;
	$locale = get_locale();
	$mofile = SF_STORE_DIR.'/'.$spPaths['language-sp-plugins'].'/'.$domain.'-'.$locale.'.mo';
	$mofile = apply_filters('sph_localization_plugin_mo', $mofile, $domain);
	load_textdomain($domain, $mofile);
}

function sp_theme_localisation($domain) {
	global $spPaths, $spGlobals;
	$locale = get_locale();
	$mofile = SF_STORE_DIR.'/'.$spPaths['language-sp-themes'].'/'.$domain.'-'.$locale.'.mo';
	$mofile = apply_filters('sph_localization_theme_mo', $mofile, $domain);
	load_textdomain($domain, $mofile);
	$spGlobals['themedomain'] = $domain;
}

# ------------------------------------------------------------------
# spa_get_language_code()
# Grab the users actual language country code
# ------------------------------------------------------------------
function spa_get_language_code() {
	global $locale;
	$locale = get_locale();
	if(!empty($locale)) {
		return $locale;
	} else {
		return 'en';
	}
}

# ------------------------------------------------------------------
# sp_feed()
# Redirects RSS feed requests
# ------------------------------------------------------------------
function sp_feed() {
	global $spVars, $wp_query;

	if (is_page() && $wp_query->post->ID == sp_get_option('sfpage') && !empty($spVars['feed'])) {
		include_once SPBOOT.'/sp-load-forum.php';

		#check for old style feed urls - load query args into spVars for new style
		if (isset($_GET['xfeed'])) {
			$spVars['feed'] = sp_esc_str($_GET['xfeed']);
			$spVars['feedkey'] = sp_esc_str($_GET['feedkey']);
			$spVars['forumslug'] = sp_esc_str($_GET['forum']);
			$spVars['topicslug'] = sp_esc_str($_GET['topic']);
		}

		# do we have the clunky group rss feed?
		if (isset($_GET['group'])) $spVars['feed'] = 'group';

		# new style rss feed urls
		if (!empty($spVars['feed'])) {
			include SF_PLUGIN_DIR.'/forum/feeds/sp-feeds.php';
			exit;
		}
	}
}

# lets make wp think our sp feeds are feeds to keep folks from mucking them
function sp_is_feed_check($query) {
	if (!is_admin() && $query->is_main_query() && !empty($query->query_vars['sf_feed'])) {
		$query->set('feed', 'forum');
		$query->is_feed = true;
	}
	return $query;
}

function sp_get_permalink($link, $id, $sample) {
	global $spIsForum;
	if ($spIsForum) {
		if ($id == sp_get_option('sfpage') && (sp_get_option('sfinloop') && in_the_loop())) $link = sp_canonical_url();
	}
	return $link;
}

# ------------------------------------------------------------------
# sp_check_page_change()
#
# Checks if forum page is being changed and resets slug and permalink
# just in case!
# ------------------------------------------------------------------
function sp_check_page_change($postid, $pObj) {
	$spPage = sp_get_option('sfpage');
	if ($spPage == $postid) {
		$perm = get_permalink($postid);
		$setslug = $pObj->post_name;
		if ($pObj->post_parent) {
			$parent = $pObj->post_parent;
			while ($parent) {
				$thispage = spdb_table(SFWPPOSTS, "ID=$parent", 'row');
				$setslug = $thispage->post_name.'/'.$setslug;
				$parent = $thispage->post_parent;
			}
		}
		sp_update_option('sfpermalink', $perm);
		sp_update_option('sfslug', $setslug);

		sp_update_permalink(true);
	}
}

# ------------------------------------------------------------------
# sp_rewrite_rules_flush_check()
#
# Checks if the rewrite rules need flushing. Typically will occur after upgrades
# ------------------------------------------------------------------
function sp_rewrite_rules_flush_check() {
    $flush = sp_get_option('sfflushrules');
    if ($flush) {
        flush_rewrite_rules(true);
        sp_update_option('sfflushrules', false);
    }
}

# ------------------------------------------------------------------
# sp_update_permalink()
#
# Updates the forum permalink. Called from plugin activation and
# upon each display of a forum admin page. If the permalink is
# found to have changed the rewrite rules are also flushed
# ------------------------------------------------------------------
function sp_update_permalink($autoflush=false) {
	global $wp_rewrite;

    $sfperm = '';

	$slug = sp_get_option('sfslug');
	if ($slug) {
		$sfperm = sp_get_option('sfpermalink');

		# go for whole row to ensure it is cached
		$pageslug = basename($slug);
		$page = spdb_table(SFWPPOSTS, "post_name='$pageslug' AND post_status='publish' AND post_type='page'", 'row');
		if ($page) {
			sp_update_option('sfpage', $page->ID);

			# get scheme for front end
			# get_permalink() returns scheme for existing page so adjust to front
			$perm = get_permalink($page->ID);
			$scheme = parse_url(get_option('siteurl'), PHP_URL_SCHEME); # get front end scheme
			$perm = set_url_scheme($perm, $scheme); # update permalink with proper front end scheme
			if (get_option('page_on_front') == $page->ID && get_option('show_on_front') == 'page') {
				$perm = rtrim($perm, '/');
				if ($wp_rewrite->using_permalinks()) {
					$perm.= '/'.$slug;
				} else {
					$perm.= '/?page_id='.$page->ID;
				}
			}
			# only update it if base permalink has been changed
			if ($sfperm != $perm) {
				sp_update_option('sfpermalink', $perm);
				$sfperm = $perm;
				$autoflush = true;
			}
		}
	}

	if ($autoflush) flush_rewrite_rules(true);

    return $sfperm;
}

# --------------------------------------------------------------------------------------
#
#	sp_wp_avatar()
#	hooks into the wp get_avatar() function return value
#	Scope:	Site
#
#	avatar:		 the wp (or wp plugin) avatar img tag
#	id_or_email: user id, email address, or comment object for avatar
#	size:		 Display size in pixels
#
# --------------------------------------------------------------------------------------
function sp_wp_avatar($avatar, $id_or_email, $size) {
	include_once SF_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';

	# this could be user id, email or comment object
	# if comment object want a user id or email address
	# pass other two striaght through
	if (is_object($id_or_email)) { # comment object passed in
		if (!empty($id_or_email->user_id)) {
			$id = (int) $id_or_email->user_id;
			$user = get_userdata($id);
			$arg = ($user) ? $id : '';
		} else if (!empty($id_or_email->comment_author_email)) {
			$arg = $id_or_email->comment_author_email;
		}
	} else {
		$arg = $id_or_email;
	}

	# replace the wp avatar image src with our sp img src
	$pattern = '/<img[^>]+src[\\s=\'"]+([^"\'>\\s]+)/is';
	$sfavatar = sp_UserAvatar("echo=0&link=none&size=$size&context=user&wp=$avatar", $arg);
	preg_match($pattern, $sfavatar, $sfmatch);
	preg_match($pattern, $avatar, $wpmatch);
	$avatar = str_replace($wpmatch[1], $sfmatch[1], $avatar);
	return $avatar;
}

# ------------------------------------------------------------------
# sp_mobile_check()
# Sets up spMobile abd spDevice globals
#	$spMobile can be true/false for both phone and tablets
#	$spDevice can be 'phone', 'tablet' or 'desktop'
# ------------------------------------------------------------------
function sp_mobile_check() {
	global $spMobile, $spDevice;

	$spDevice = sp_detect_device();
	if ($spDevice == 'mobile' || $spDevice == 'tablet') $spMobile = true;
	$spMobile = apply_filters('sph_mobile_check', $spMobile);
	$spDevice = apply_filters('sph_device_check', $spDevice);
}

# ------------------------------------------------------------------
# sp_load_blog_script()
# Loads any JS needed on blog when not a forum view
# ------------------------------------------------------------------
function sp_load_blog_script() {
	do_action('sph_blog_scripts_start');
	do_action('sph_blog_scripts_end');
}

# ------------------------------------------------------------------
# sp_load_blog_support()
# Loads any support needed on blog when not a forum view
# ------------------------------------------------------------------
function sp_load_blog_support() {
	global $wp_query, $spGlobals;

	# Grab WP post object for use in action
	$wpPost = $wp_query->get_queried_object();
	do_action('sph_blog_support_start', $wpPost);

	do_action('sph_blog_support_end', $wpPost);
}

# ------------------------------------------------------------------
# spa_register_math()
#
# Filter Call
# Sets up the spam math on registration form
# ------------------------------------------------------------------
function spa_register_math() {
	$sflogin = sp_get_option('sflogin');
	if ($sflogin['sfregmath']) {
		$spammath = sp_math_spam_build();
		$uKey = sp_get_option('spukey');
		$uKey1 = $uKey.'1';
		$uKey2 = $uKey.'2';

		$out = '<input type="hidden" size="30" name="url" value="" /></p>'."\n";
		$out.= '<label>'.sp_text('Math Required!').'<br />'."\n";
		$out.= sprintf(sp_text('What is the sum of: %s %s + %s %s'), '<strong>', $spammath[0], $spammath[1], '</strong>').'</label>'."\n";
		$out.= '<input class="input" type="text" id="'.$uKey1.'" name="'.$uKey1.'" value="" />'."\n";
		$out.= '<input type="hidden" name="'.$uKey2.'" value="'.$spammath[2].'" />'."\n";
		$out.= '<br />';
		echo $out;
	}
}

# ------------------------------------------------------------------
# spa_register_error()
#
# Filter Call
# Sets up the spam math error is required
#	$errors:	registration errors array
# ------------------------------------------------------------------
function spa_register_error($errors) {
	global $spIsForum;

	$sflogin = sp_get_option('sflogin');
	if ($sflogin['sfregmath']) {
		$spamtest = sp_spamcheck();
		if ($spamtest[0] == true) {
			$errormsg = '<b>'.sp_text('ERROR').'</b>: '.$spamtest[1];

			if ($spIsForum == false) {
				$errors->add('Bad Math', $errormsg);
			} else {
				$errors['math_check'] = $errormsg;
			}
		}
	}
	return $errors;
}

function sp_get_current_sp_theme() {
	global $spDevice;

	if ($spDevice == 'mobile') {
		$theme = sp_get_option('sp_mobile_theme');
		if (!empty($theme) && $theme['active']) return $theme;
	}

	if ($spDevice == 'tablet') {
		$theme = sp_get_option('sp_tablet_theme');
		if (!empty($theme) && $theme['active']) return $theme;
	}

	return sp_get_option('sp_current_theme');
}

# ------------------------------------------------------------------
# sp_set_image_array()
# Version 5.5.1
# Sets up the icon array for sp_paint_icon() to use
#	$curTheme	The array data of the current specified theme
# ------------------------------------------------------------------
function sp_set_image_array($curTheme) {
	global $spImages, $spDevice;

	$idx = 0;

	# Current theme special icons folder (overlay specified)
	if (!empty($curTheme['icons'])) {
		$p = ($spDevice == 'mobile') ? $curTheme['theme'].'/images/'.$curTheme['icons'].'/mobile/' : $curTheme['theme'].'/images/'.$curTheme['icons'].'/';
		$spImages[$idx]['dir'] = SPTHEMEBASEDIR.$p;
		$spImages[$idx]['url'] = SPTHEMEBASEURL.$p;
		$idx++;
	}
	# Current theme default images folder
	$p = ($spDevice == 'mobile') ? $curTheme['theme'].'/images/mobile/' : $curTheme['theme'].'/images/';
	$spImages[$idx]['dir'] = SPTHEMEBASEDIR.$p;
	$spImages[$idx]['url'] = SPTHEMEBASEURL.$p;
	$idx++;

	# if Child theme add porent locations
	if (!empty($curTheme['parent'])) {
		# Parent theme special icons folder (overlay specified)
		if (!empty($curTheme['icons'])) {
			$p = ($spDevice == 'mobile') ? $curTheme['parent'].'/images/'.$curTheme['icons'].'/mobile/' : $curTheme['parent'].'/images/'.$curTheme['icons'].'/';
			$spImages[$idx]['dir'] = SPTHEMEBASEDIR.$p;
			$spImages[$idx]['url'] = SPTHEMEBASEURL.$p;
			$idx++;
		}

		# Parent theme default images folder
		$p = ($spDevice == 'mobile') ? $curTheme['parent'].'/images/mobile/' : $curTheme['parent'].'/images/';
		$spImages[$idx]['dir'] = SPTHEMEBASEDIR.$p;
		$spImages[$idx]['url'] = SPTHEMEBASEURL.$p;
		$idx++;
	}
}

# ------------------------------------------------------------------
# sp_get_overlay_icons()
# Version 5.5.2
# Retrieves icon pack to be used with an overlay in case in cookie
#	$path	path to overlay file being used
# ------------------------------------------------------------------
function sp_get_overlay_icons($path) {
	if (empty($path)) return;

	$defaults = array(
		'Icons' => 'Icons'
	);

	$data = get_file_data($path, $defaults);

	if (empty($data['Icons'])) {
		return '';
	} else {
		return $data['Icons'];
	}
}

# ------------------------------------------------------------------
# sp_display_inspector()
#
# Displays data objects when needed
#	$dName		Object key name
#	$dObject	The data object to display
# ------------------------------------------------------------------
function sp_display_inspector($dName, $dObject) {
	global $spThisUser;

	if (empty($spThisUser->inspect)) return;

	$i = $spThisUser->inspect;

	if ($dName == 'control') {
		# spVars, spGlobals, spThisUser
		if (array_key_exists('con_spVars', $i) && $i['con_spVars']) {
			global $spVars;
			ashow($spVars, $spThisUser->ID, 'spVars');
		}
		if (array_key_exists('con_spGlobals', $i) && $i['con_spGlobals']) {
			global $spGlobals;
			ashow($spGlobals, $spThisUser->ID, 'spGlobals');
		}
		if (array_key_exists('con_spThisUser', $i) && $i['con_spThisUser']) {
			ashow($spThisUser, $spThisUser->ID, 'spThisUser');
		}
		if (array_key_exists('con_spDevice', $i) && $i['con_spDevice']) {
			global $spDevice;
			ashow($spDevice, $spThisUser->ID, 'spDevice');
		}
	} else {
		# called direct from class file
		if (array_key_exists($dName, $i) && $i[$dName]) {
			if (!empty($dObject)) {
				$dName = ltrim(strrchr($dName, '_'), '_');
				ashow($dObject, $spThisUser->ID, $dName);
			}
		}
	}
}

# ------------------------------------------------------------------
# sp_display_inspector_profile_XXX()
#
# Displays profile data objects when needed - special
# ------------------------------------------------------------------
add_filter('sph_ProfileShowHeader', 'sp_display_inspector_profile_popup', 1, 3);
function sp_display_inspector_profile_popup($out, $spProfileUser, $a) {
	sp_display_inspector('pro_spProfileUser', $spProfileUser);
	return $out;
}

add_action('sph_profile_edit_after_tabs', 'sp_display_inspector_profile_edit');
function sp_display_inspector_profile_edit() {
	global $spProfileUser;
	sp_display_inspector('pro_spProfileUser', $spProfileUser);
}

# 5.3
# function for aborting forum display and outputting a message instead (ie upgrade needed)
function sp_abort_display_forum() {
	global $spStatus;

	# are we awaiting an upgrade - outptut message and bail
	if ($spStatus != 'ok') {
	   $message = sp_forum_unavailable();
	   return $message;
	}

	# let plugins hook in
	$message = apply_filters('sph_alternate_forum_content', '');

	return $message;
}

function sp_forum_unavailable() {
	global $current_user;

	$out = '';
	$out.= '<div id="spMainContainer">';
	$out.= '<div class="spMessage">';
	$out.= '<p>'.sp_paint_icon('', SPTHEMEICONSURL, 'sp_Information.png').'</p>';
	$out.= '<p>'.sp_text('Sorry, the forum is temporarily unavailable while it is being upgraded to a new version.').'</p>';
	if (sp_is_forum_admin($current_user->ID)) $out.= '<a href="'.SFADMINUPGRADE.'">'.sp_text('Click here to perform the upgrade').'</a>';
	$out.= '</div>';
	$out.= '</div>';
	$out = apply_filters('sph_forum_unavailable', $out);
	return $out;
}

function sp_load_version_xml($showError=true, $usecache=true) {
    # clean out cache since most of this loading occurs outside of forum page
	if ($usecache) sp_clean_cache();

    # grab a cached copy of xml file if valid - otherwise load through our api
    $xml = ($usecache) ? sp_get_cache('xml') : '';
    if (empty($xml)) {
    	$url = 'https://simple-press.com/downloads/simple-press/simple-press.xml';
    	$options = array('timeout' => 10);
    	$response = wp_remote_get($url, $options);
    	$code = wp_remote_retrieve_response_code($response);

    	if (is_wp_error($response) || $code != 200) {
    		if ($showError) {
				$out = '<div style="padding:0 10px;margin:10px 55px;background:white;border:1px solid red">';
				$out.= '<p style="font-size:13px;font-weight:bold;line-height:1.1em;">'.spa_text('Your server has returned a status code of');
				$out.= $code;
				$out.= spa_text('while attempting to communicate with the Simple:Press server to establish up to date version information.').'<br />';

				if(is_wp_error($response)) {
					$errs = $response->get_error_messages();
					if(!empty($errs)) {
						$errs = htmlspecialchars(implode('; ', $errs));
						$out.= spa_text('Additionally, WordPress reported the following').': '.$errs.'<br />';
					}
				}

				$out.= spa_text('WordPress will retry this operation automatically but if the condition persists we firstly
						recommend seeking assistance from your hosting support team and then contacting
						Simple:Press support if they are unable to help.').'</p>';
				$out.= '</div>';
				echo $out;
			}
    		return false;
    	}
    	$body = wp_remote_retrieve_body($response);
    	if (!$body) return '';
    	$xml = new SimpleXMLElement($body);

		# Now cache off the xml file
        $data = array();
        $data[0] = $xml->asXML();;
		if ($usecache) sp_add_cache('xml', $data);
    } else {
        $xml = simplexml_load_string($xml[0]);
    }

	return $xml;
}

# check for new sp core, plugins and themes
# this common check of all runs wp cron update check runs
# individual checks for updates also runs on wp update page load in spa-admin-updater.php
function sp_check_for_updates() {
	$xml = sp_load_version_xml();
	if ($xml) {
		$installed_version = sp_get_option('sfversion');
		$installed_build = sp_get_option('sfbuild');
		if (empty($installed_build)) return;
		if ($xml->core->build > $installed_build) {
			$up = get_site_transient('update_plugins');
			$data = new stdClass;
			$data->slug = 'simple-press';
			$data->new_version = (string) $xml->core->version.' Build '.(string) $xml->core->build;
			$data->new_build = (string) $xml->core->build;
			$data->upgrade_notice = (string) $xml->core->message;
			$data->url = 'https://simple-press.com';
			$data->package = (string) $xml->core->archive;
			$up->response['simple-press/sp-control.php'] = $data;
			set_site_transient('update_plugins', $up);
		}

		$update = false;
		$plugins = sp_get_plugins();
		if (!empty($plugins)) {
			$up = new stdClass;
			foreach ($plugins as $file => $installed) {
				foreach ($xml->plugins->plugin as $latest) {
					if ($installed['Name'] == $latest->name) {
						if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
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
		}

		if ($update) {
			set_site_transient('sp_update_plugins', $up);
		} else {
			delete_site_transient('sp_update_plugins');
		}

		include_once SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php';
		$update = false;
		$themes = sp_get_themes();
		if (!empty($themes)) {
			$up = new stdClass;
			foreach ($themes as $file => $installed) {
				foreach ($xml->themes->theme as $latest) {
					if ($installed['Name'] == $latest->name) {
						if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
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
		}

		if ($update) {
			set_site_transient('sp_update_themes', $up);
		} else {
			delete_site_transient('sp_update_themes');
		}
	}
}

# 5.6.2 - glossary primitives
function sp_add_glossary_keyword($key, $plugin) {
	global $spVars;
	# does it exist already?
	$sql = "SELECT id FROM ".SFADMINKEYWORDS." WHERE keyword='$key'";
	$id = spdb_select('var', $sql);
	if($id) return $id;

	# we need to create it then
	$sql = "INSERT INTO ".SFADMINKEYWORDS." (`keyword`, `plugin`) VALUES ('$key','$plugin');";
	spdb_query($sql);
	return $spVars['insertid'];
}

function sp_remove_glossary_plugin($plugin) {
	$sql = "DELETE FROM ".SFADMINKEYWORDS." WHERE plugin='$plugin'";
	spdb_query($sql);
	$sql = "DELETE FROM ".SFADMINTASKS." WHERE plugin='$plugin'";
	spdb_query($sql);
}

?>