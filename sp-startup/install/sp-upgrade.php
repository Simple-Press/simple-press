<?php
/*
Simple:Press
Upgrade Path Routines - Version 5.0
$LastChangedDate: 2018-08-11 19:52:41 -0500 (Sat, 11 Aug 2018) $
$Rev: 15696 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

global $current_user;

if (!sp_nonce('upgrade')) die();

ob_start();

$InstallID = get_option('sfInstallID'); # use wp option table
wp_set_current_user($InstallID);

require_once dirname(__file__).'/sp-upgrade-support.php';
require_once SF_PLUGIN_DIR.'/admin/library/spa-support.php';

# use WP check here since SPF stuff may not be set up
if (!current_user_can('activate_plugins')) {
	sp_response(0, true, 'error', spa_text('Access denied - Only Users who can Activate Plugins may perform this upgrade'));
	die();
}

if (!isset($_GET['start'])) {
	sp_response(0, true, 'error', spa_text('Start build number not provided to upgrade script'));
	die();
}

$checkval = $_GET['start'];
$build = intval($checkval);

# double check that the next build section has not reset for any reason - which it should not
$startUpgrade = sp_get_option('sfStartUpgrade');
$lastSection = sp_get_option('sfbuild');
if ($build < $startUpgrade) $build = $startUpgrade;
if ($build < $lastSection) $build = $lastSection;

# Start of Upgrade Routines - 5.0.0 ============================================================

# send out json header
header('Content-type: application/json; charset=' . SFCHARSET);

# DATABASE SCHEMA CHANGES
$section = 6624;
if ($build < $section) {

	# Tables to be removed

	spdb_query('DROP TABLE IF EXISTS '.SF_PREFIX.'sfsettings');
	spdb_query('DROP TABLE IF EXISTS '.SF_PREFIX.'sfnotice');

	# New Tables

	# create error log table
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFERRORLOG.' (
			id bigint(20) NOT NULL auto_increment,
			error_date datetime NOT NULL,
			error_type varchar(10) NOT NULL,
			error_text text,
			PRIMARY KEY (id)
		) '.spdb_charset();
	spdb_query($sql);

	# create new table for auths
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFAUTHS." (
			auth_id bigint(20) NOT NULL auto_increment,
			auth_name varchar(50) NOT NULL,
			auth_desc text,
			active smallint(1) NOT NULL default '0',
			ignored smallint(1) NOT NULL default '0',
			enabling smallint(1) NOT NULL default '0',
			PRIMARY KEY	 (auth_id),
			KEY auth_name_idx (auth_name)
		) ".spdb_charset();
	spdb_query($sql);
	sp_response($section);
}

$section = 6637;
if ($build < $section) {

	# Other DB Schema changes

	# add new column for user memberships in sfmember
	spdb_query('ALTER TABLE '.SFMEMBERS.' ADD (memberships longtext)');

	# add new column to sftrack for notifications
	spdb_query('ALTER TABLE '.SFTRACK.' ADD (notification varchar(1024) default NULL)');

	# change post_content column to long text type
	spdb_query('ALTER TABLE '.SFPOSTS.' CHANGE post_content post_content LONGTEXT;');

	# increase icons to length 50
	spdb_query('ALTER TABLE '.SFGROUPS.' MODIFY group_icon varchar(50) default NULL');
	spdb_query('ALTER TABLE '.SFFORUMS.' MODIFY forum_icon varchar(50) default NULL');

	# new option for user selectable usergroups
	spdb_query('ALTER TABLE '.SFUSERGROUPS." ADD (usergroup_join tinyint(4) unsigned NOT NULL default '0')");

	# add sfmeta autoload
	spdb_query('ALTER TABLE '.SFMETA." ADD (autoload tinyint(4) unsigned NOT NULL default '0')");

	# Remove pm flag from members
	spdb_query('ALTER TABLE '.SFMEMBERS.' DROP pm');
	sp_response($section);
}

$section = 6650;
if ($build < $section) {

	# Indexing and Column Def Changes

	# Primary Key ID consistency changes - Update fields to be bigint(20)
	spdb_query('ALTER TABLE '.SFERRORLOG.' MODIFY id bigint(20) auto_increment;');
	spdb_query('ALTER TABLE '.SFLOG.' MODIFY id bigint(20) auto_increment;');

	spdb_query('ALTER TABLE '.SFDEFPERMISSIONS.' MODIFY permission_id bigint(20) auto_increment;');
	spdb_query('ALTER TABLE '.SFPERMISSIONS.' MODIFY permission_id bigint(20) auto_increment;');
	spdb_query('ALTER TABLE '.SFMEMBERSHIPS.' MODIFY membership_id bigint(20) auto_increment;');
	spdb_query('ALTER TABLE '.SFROLES.' MODIFY role_id bigint(20) auto_increment;');
	spdb_query('ALTER TABLE '.SFUSERGROUPS.' MODIFY usergroup_id bigint(20) auto_increment;');
	spdb_query('ALTER TABLE '.SFOPTIONS.' MODIFY option_id bigint(20) auto_increment;');

	# Foreign Key ID consistency changes - Update fields to be bigint(20)
	spdb_query('ALTER TABLE '.SFDEFPERMISSIONS.' MODIFY group_id bigint(20), MODIFY usergroup_id bigint(20), MODIFY permission_role bigint(20);');
	spdb_query('ALTER TABLE '.SFPERMISSIONS.' MODIFY forum_id bigint(20), MODIFY usergroup_id bigint(20), MODIFY permission_role bigint(20);');
	spdb_query('ALTER TABLE '.SFMEMBERSHIPS.' MODIFY user_id bigint(20), MODIFY usergroup_id bigint(20);');

	# Indexing on Foreign Keys
	spdb_query('ALTER TABLE '.SFDEFPERMISSIONS.' ADD KEY group_idx (group_id), ADD key usergroup_idx (usergroup_id), ADD KEY perm_role_idx(permission_role);');
	spdb_query('ALTER TABLE '.SFLOG.' ADD KEY user_idx (user_id);');
	spdb_query('ALTER TABLE '.SFPERMISSIONS.' ADD KEY forum_idx (forum_id), ADD KEY usergroup_idx (usergroup_id), ADD KEY perm_role_idx(permission_role);');
	spdb_query('ALTER TABLE '.SFPOSTS.' ADD KEY user_idx (user_id), ADD KEY comment_idx (comment_id);');
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD KEY post_idx (post_id);');
	spdb_query('ALTER TABLE '.SFTOPICS.' ADD KEY user_idx (user_id), ADD KEY post_idx (post_id);');
	spdb_query('ALTER TABLE '.SFTRACK.' ADD KEY forum_idx (forum_id), ADD KEY topic_idx (topic_id);');
	spdb_query('ALTER TABLE '.SFWAITING.' ADD KEY forum_idx (forum_id), ADD KEY post_idx (post_id), ADD KEY user_idx (user_id);');
	sp_response($section);
}

# End of DB Schema Changes

$section = 6663;
# drop old tablea and remove old optioon records
if ($build < $section) {
	# remve unwanted option records
	sp_delete_option('sfcbexclusions');
	sp_response($section);
}

$section = 6689;
# move auto update stuff to sfmeta
if ($build < $section) {
	$autoup = array('spjUserUpdate', SFHOMEURL.'index.php?sp_ahah=autoupdate');
	sp_add_sfmeta('autoupdate', 'user', $autoup, 0);
	sp_response($section);
}

$section = 6695;
# move auto update stuff to sfmeta
if ($build < $section) {

	spdb_query('ALTER TABLE '.SFAUTHS." ADD (auth_cat bigint(20) NOT NULL default '1')");
	spdb_query('ALTER TABLE '.SFAUTHS." ADD (admin_negate smallint(1) NOT NULL default '0')");
	spdb_query('ALTER TABLE '.SFMEMBERS.' ADD (auths longtext)');

	# create new auth categories table
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFAUTHCATS.' (
			authcat_id tinyint(4) NOT NULL auto_increment,
			authcat_name varchar(50) NOT NULL,
			authcat_slug varchar(50) NOT NULL,
			authcat_desc tinytext,
			PRIMARY KEY	 (authcat_id),
			KEY authcat_slug_idx (authcat_slug)
		) '.spdb_charset();
	spdb_query($sql);

	# create default categories
	spa_setup_auth_cats();
	sp_response($section);
}

$section = 6702;
# convert permissions to auths
if ($build < $section) {
	sp_convert_perms_to_auths();
	sp_response($section);
}

$section = 6728;
# set up cron transient cleanup
if ($build < $section) {
	wp_schedule_event(time(), 'daily', 'sph_transient_cleanup_cron'); # new cron name
	sp_response($section);
}

$section = 6741;
# add new breadcrumb option
if ($build < $section) {
	$sfdisplay = array();
	$sfdisplay = sp_get_option('sfdisplay');
	$sfdisplay['breadcrumbs']['showpage'] = true;
	sp_update_option('sfdisplay', $sfdisplay);
	sp_response($section);
}

$section = 6754;
if ($build < $section) {
	sp_delete_option('sfeditor');
	sp_response($section);
}

$section = 6767;
if ($build < $section) {
	$sfsupport = array();
	$sfsupport = sp_get_option('sfsupport');
	unset($sfsupport['sfusingtagstags']);
	unset($sfsupport['sfusingpagestags']);
	sp_update_option('sfsupport', $sfsupport);
	sp_response($section);
}

$section = 6780;
if ($build < $section) {
	# config admin panel now gone
	sp_delete_option('sfsupport');
	sp_response($section);
}

$section = 6793;
if ($build < $section) {
	# update smileys for in_use flag
	$smileys = sp_get_sfmeta('smileys', 'smileys');
	if ($smileys) {
		foreach ($smileys[0]['meta_value'] as $smiley => $something) {
			$smileys[0]['meta_value'][$smiley][2] = 1;
		}
		sp_update_sfmeta('smileys', 'smileys', $smileys[0]['meta_value'], $smileys[0]['meta_id'], true);
	}
	sp_response($section);
}

$section = 6806;
if ($build < $section) {
	# new manage theme and plugin caps
	$admins = spdb_table(SFMEMBERS, 'admin = 1');
	if ($admins) {
	   foreach ($admins as $admin) {
			$user = new WP_User($admin->user_id);
			$user->add_cap('SPF Manage Themes');
			$user->add_cap('SPF Manage Plugins');
		}
	}
	sp_response($section);
}

$section = 6819;
if ($build < $section) {
	# move some stats to own options
	$spControls = sp_get_option('sfcontrols');
	sp_add_option('spMostOnline', $spControls['maxonline']);
	sp_add_option('spRecentMembers', $spControls['newuserlist']);
	sp_response($section);
}

$section = 6832;
if ($build < $section) {
	# set up hourly stats generation
	wp_schedule_event(time(), 'hourly', 'sph_stats_cron'); # new cron name
	sp_cron_generate_stats();
	sp_response($section);
}

$section = 6845;
if ($build < $section) {
	# new profile tabs
	spa_new_profile_setup();
	sp_response($section);
}

$section = 6858;
if ($build < $section) {
	# set required items to autoload
	spdb_query('UPDATE '.SFMETA." SET autoload = 1 WHERE meta_type IN ('smileys', 'topic-status', 'customProfileFields', 'forum_rank', 'special_rank')");
	sp_response($section);
}

$section = 6871;
if ($build < $section) {
	$sffilters = sp_get_option('sffilters');
	$sffilters['sfallowlinks'] = true;
	sp_update_option('sffilters', $sffilters);

	# update the options
	sp_new_options_update();
	sp_response($section);
}

$section = 6884;
if ($build < $section) {
	# default theme
	$theme = array();
	$theme['theme'] = 'default';
	$theme['style'] = 'default.php';
	$theme['color'] = 'silver';
	sp_add_option('sp_current_theme', $theme);
	sp_response($section);
}

$section = 6897;
if ($build < $section) {
	# Clean smiley options
	$sfsmileys = array();
	$sfsmileys = sp_get_option('sfsmileys');
	$setting = $sfsmileys['sfsmallow'];
	sp_update_option('sfsmileys', $setting);

	$sfrss = sp_get_option('sfrss');
	$sfrss['sfrsstopicname'] = false;
	sp_update_option('sfrss', $sfrss);
	sp_response($section);
}

##### Before this is start of alpha #####
$section = 6910;
if ($build < $section) {
	$sfprofile = sp_get_option('sfprofile');
	if ($sfprofile['nameformat'] == 3) {
		$sfprofile['nameformat'] = false;
		$sfprofile['fixeddisplayformat'] = 4;
	} else if ($sfprofile['nameformat'] == 2) {
		$sfprofile['nameformat'] = false;
		$sfprofile['fixeddisplayformat'] = 1;
	} else {
		$sfprofile['nameformat'] = true;
		$sfprofile['fixeddisplayformat'] = 0;
	}

	$sfseo = sp_get_option('sfseo');
	$sfseo['sfseo_overwrite'] = false;
	$sfseo['sfseo_blogname'] = false;
	$sfseo['sfseo_pagename'] = false;
	sp_update_option('sfseo', $sfseo);
	sp_response($section);
}

$section = 6923;
# create plugins, theme and language folder storage location (sp-resources)
if ($build < $section) {

	$sfconfig = array();
	$sfconfig = sp_get_option('sfconfig');

	# remove unrequired entries
	unset($sfconfig['hooks']);
	unset($sfconfig['help']);
	unset($sfconfig['styles']);
	unset($sfconfig['languages']);
	unset($sfconfig['pluggable']);
	unset($sfconfig['filters']);

	$perms = fileperms(SF_STORE_DIR);
	$owners = stat(SF_STORE_DIR);
	if ($perms === false) $perms = 0755;
	$basepath.= 'sp-resources';
	if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);

	# hive off the basepath for later use - use wp options
	$spStorage = SF_STORE_DIR.'/'.$basepath;

	# Did it get created?
	$success = true;
	if (!file_exists(SF_STORE_DIR.'/'.$basepath)) $success = false;
	sp_add_option('spStorageInstall2', $success);

	# Is the ownership correct?
	$ownersgood = false;
	if ($success) {
		$newowners = stat(SF_STORE_DIR.'/'.$basepath);
		if ($newowners['uid'] == $owners['uid'] && $newowners['gid'] == $owners['gid']) {
			$ownersgood = true;
		} else {
			@chown(SF_STORE_DIR.'/'.$basepath, $owners['uid']);
			@chgrp(SF_STORE_DIR.'/'.$basepath, $owners['gid']);
			$newowners = stat(SF_STORE_DIR.'/'.$basepath);
			if ($newowners['uid'] == $owners['uid'] && $newowners['gid'] == $owners['gid']) $ownersgood = true;
		}
	}
	sp_add_option('spOwnersInstall2', $ownersgood);

	$basepath .= '/';
	$sfconfig['plugins']				= $basepath.'forum-plugins';
	$sfconfig['themes']					= $basepath.'forum-themes';
	$sfconfig['language-sp']			= $basepath.'forum-language/simple-press';
	$sfconfig['language-sp-plugins']	= $basepath.'forum-language/sp-plugins';
	$sfconfig['language-sp-themes']		= $basepath.'forum-language/sp-themes';

	sp_update_option('sfconfig', $sfconfig);

	# Move and extract zip upgrade archive
	$successCopy2 = false;
	$successExtract2 = false;
	$zipfile = SF_PLUGIN_DIR.'/sp-startup/install/sp-resources-install-part2.zip';
	$extract_to = $spStorage;
	# Copy the zip file
	if (@copy($zipfile, $extract_to.'/sp-resources-install-part2.zip')) {
		$successCopy2 = true;
		# Now try and unzip it
		require_once ABSPATH.'wp-admin/includes/class-pclzip.php';
		$zipfile = $extract_to.'/sp-resources-install-part2.zip';
		$zipfile = str_replace('\\','/', $zipfile); # sanitize for Win32 installs
		$zipfile = preg_replace('|/+|','/', $zipfile); # remove any duplicate slash
		$extract_to = str_replace('\\','/', $extract_to); # sanitize for Win32 installs
		$extract_to = preg_replace('|/+|','/', $extract_to); # remove any duplicate slash
		$archive = new PclZip($zipfile);
		$archive->extract($extract_to);
		if ($archive->error_code == 0) {
			$successExtract2 = true;
			# Lets try and remove the zip as it seems to have worked
			@unlink($zipfile);
		} else {
			sp_add_option('ziperror', $archive->error_string);
		}
	}

	sp_add_option('spCopyZip2', $successCopy2);
	sp_add_option('spUnZip2', $successExtract2);
	sp_response($section);
}

$section = 6936;
if ($build < $section) {
	# Move storage location folders
	if (sp_get_option('V5DoStorage') == true) {
		sp_move_storage_locations();
	}
	sp_response($section);
}

$section = 6949;
if ($build < $section) {
	$sflogin = sp_get_option('sflogin');
	$sflogin['sfloginurl'] = sp_url();
	$sflogin['sflogouturl'] = sp_url();
	$sflogin['sfregisterurl'] = '';
	$sflogin['sfloginemailurl'] = esc_url(wp_login_url());
	sp_update_option('sflogin', $sflogin);
	sp_response($section);
}

$section = 6962;
if ($build < $section) {
	spdb_query('DELETE FROM '.SFMEMBERSHIPS.' WHERE usergroup_id=0');
	sp_response($section);
}

## after alpha start
$section = 7022;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFTRACK.' ADD (pageview varchar(50) NOT NULL)');
	sp_response($section);
}


$section = 7055;
if ($build < $section) {
	$auth = spdb_table(SFAUTHS, 'auth_name="view_admin_posts"', 'row');
	if ($auth) {
		$auth->auth_desc = sp_esc_sql(spa_text('Can view posts by an administrator'));
		spdb_query('UPDATE '.SFAUTHS." SET auth_desc='$auth->auth_desc' WHERE auth_id=$auth->auth_id");
	}
	sp_response($section);
}

$section = 7181;
if ($build < $section) {
	$curTheme = sp_get_option('sp_current_theme');
	$theme = array();
	$theme['active'] = false;
	$theme['theme'] = $curTheme['theme'];
	$theme['style'] = $curTheme['style'];
	$theme['color'] = $curTheme['color'];
	sp_add_option('sp_mobile_theme', $theme);
	sp_response($section);
}

$section = 7255;
if ($build < $section) {
	# ignore editing permissions for guests
	$auth = spdb_table(SFAUTHS, 'auth_name="edit_any_post"', 'row');
	if ($auth) spdb_query('UPDATE '.SFAUTHS." SET ignored=1 WHERE auth_id=$auth->auth_id");
	$auth = spdb_table(SFAUTHS, 'auth_name="edit_own_posts_forever"', 'row');
	if ($auth) spdb_query('UPDATE '.SFAUTHS." SET ignored=1 WHERE auth_id=$auth->auth_id");
	$auth = spdb_table(SFAUTHS, 'auth_name="edit_own_posts_reply"', 'row');
	if ($auth) spdb_query('UPDATE '.SFAUTHS." SET ignored=1 WHERE auth_id=$auth->auth_id");
	sp_response($section);
}

$section = 7420;
if ($build < $section) {
	# new user notices table
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFNOTICES." (
		notice_id bigint(20) NOT NULL auto_increment,
		user_id bigint(20) default NULL,
		guest_email varchar(50) default NULL,
		post_id bigint(20) default NULL,
		link varchar(255) default NULL,
		link_text varchar(200) default NULL,
		message varchar(255) NOT NULL default '',
		expires int(4) default NULL,
		PRIMARY KEY (notice_id),
		KEY user_id_idx (user_id)
		) ".spdb_charset();
	spdb_query($sql);
	sp_response($section);
}

$section = 7430;
if ($build < $section) {
	# creating new table columns for post moderation processing
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (post_id_held bigint(20) default NULL)');
	spdb_query('ALTER TABLE '.SFFORUMS." ADD (post_count_held mediumint(8) default '0')");
	spdb_query('ALTER TABLE '.SFTOPICS.' ADD (post_id_held bigint(20) default NULL)');
	spdb_query('ALTER TABLE '.SFTOPICS." ADD (post_count_held mediumint(8) default '0')");
	# pupulating with startup data

	spdb_query('UPDATE '.SFTOPICS.' SET post_id_held = post_id, post_count_held = post_count');
	spdb_query('UPDATE '.SFFORUMS.' SET post_id_held = post_id, post_count_held = post_count');
	sp_response($section);
}

$section = 7500;
if ($build < $section) {
	# Set up unique key
	$uKey = substr(chr(rand(97, 122)).md5(time()), 0, 10);
	sp_add_option('spukey', $uKey);
	sp_response($section);
}

$section = 7572;
if ($build < $section) {
	# Add post_date index
	spdb_query('ALTER TABLE '.SFPOSTS.' ADD KEY post_date_idx (post_date)');
	sp_response($section);
}

$section = 7579;
if ($build < $section) {
	# Add new column indexing
	spdb_query('ALTER TABLE '.SFMEMBERS.' ADD KEY admin_idx (admin)');
	spdb_query('ALTER TABLE '.SFMEMBERS.' ADD KEY moderator_idx (moderator)');
	spdb_query('ALTER TABLE '.SFMETA.' ADD KEY meta_type_idx (meta_type)');
	sp_response($section);
}

$section = 7750;
if ($build < $section) {
	# Add new column indexing
	spdb_query('ALTER TABLE '.SFMETA.' ADD KEY autoload_idx (autoload)');
	sp_response($section);
}

$section = 7826;
if ($build < $section) {
	# adjust log entries
	spdb_query('ALTER TABLE '.SFLOG.' MODIFY release_type varchar(20)');
	spdb_query('ALTER TABLE '.SFLOG.' MODIFY build int(6) NOT NULL');
	sp_response($section);
}

$section = 8033;
if ($build < $section) {
	$sfcontrols = sp_get_option('sfcontrols');
	$sfcontrols['sfdefunreadposts'] = 50;
	$sfcontrols['sfusersunread'] = false;
	$sfcontrols['sfmaxunreadposts'] = 50;
	sp_update_option('sfcontrols', $sfcontrols);
	sp_response($section);
}

$section = 8148;
if ($build < $section) {
	flush_rewrite_rules(); # flush rewrite rules to load newpost rule
	sp_response($section);
}

$section = 8214;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFUSERGROUPS.' ADD (usergroup_badge varchar(50) default NULL)');
	sp_reset_memberships();
	sp_response($section);
}

$section = 8219;
if ($build < $section) {
	sp_add_option('sp_stats_interval', 3600);
	sp_response($section);
}

$section = 8222;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (forum_icon_new varchar(50) default NULL)');
	sp_response($section);
}

$section = 8239;
if ($build < $section) {
	$profile = sp_get_sfmeta('profile', array());
	$tabs = (!empty($profile)) ? $profile[0]['meta_value'] : '';
	if ($tabs) {
		foreach ($tabs as $tindex => $tab) {
			if ($tab['slug'] == 'profile') {
				if ($tab['menus']) {
					foreach ($tab['menus'] as $mindex => $menu) {
						if ($menu['slug'] == 'edit-signature') {
							if (empty($tabs[$tindex]['menus'][$mindex]['auth'])) {
								$tabs[$tindex]['menus'][$mindex]['auth'] = 'use_signatures';
								break 2;
							}
						}
					}
				}
			}
		}
		sp_update_sfmeta('profile', 'tabs', $tabs, $profile[0]['meta_id']);
	}
	sp_response($section);
}

$section = 8240;
if ($build < $section) {
	$sflogin = sp_get_option('sflogin');
	$sflogin['sptimeout'] = 20;
	sp_update_option('sflogin', $sflogin);
	sp_response($section);
}

$section = 8284;
if ($build < $section) {
	$sfprofile = sp_get_option('sfprofile');
	$sfprofile['photosheight'] = $sfprofile['photoswidth'];
	sp_update_option('sfprofile', $sfprofile);
	sp_response($section);
}

# Start of Upgrade Routines - 5.1.0 ============================================================

$section = 8315;
if ($build < $section) {
	# Add new index to sfposts
	spdb_query('ALTER TABLE '.SFPOSTS.' ADD KEY guest_name_idx (guest_name);');
	# Remove mobile lost option
	sp_delete_option('sfmobile');
	sp_response($section);
}

$section = 8375;
if ($build < $section) {
	wp_schedule_event(time(), 'sp_news_interval', 'sph_news_cron');
	sp_add_sfmeta('news', 'news', array('id' => -999.999, 'show' => 0, 'news' => spa_text('Latest Simple Press News will be shown here')));
	sp_response($section);
}

$section = 8402;
if ($build < $section) {
	sp_cron_generate_stats();
	sp_response($section);
}

$section = 8440;
if ($build < $section) {
	sp_convert_ranks();
	sp_response($section);
}

$section = 8493;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (topic_icon varchar(50) default NULL)');
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (topic_icon_new varchar(50) default NULL)');
	sp_response($section);
}

$section = 8530;
if ($build < $section) {
	sp_add_sfmeta('sort_order', 'forum', '', 1);
	sp_add_sfmeta('sort_order', 'topic', '', 1);
	sp_response($section);
}

$section = 8552;
if ($build < $section) {
	sp_add_option('poststamp', current_time('mysql'));
	sp_delete_option('sfzone');
	sp_response($section);
}

$section = 8556;
if ($build < $section) {
	$sfadminsettings = sp_get_option('sfadminsettings');
	$sfadminsettings['sfadminapprove'] = false;
	$sfadminsettings['sfmoderapprove'] = false;
	sp_update_option('sfadminsettings', $sfadminsettings);
	sp_response($section);
}

# Start of Upgrade Routines - 5.1.1 ============================================================
$section = 8618;
if ($build < $section) {
	# image size constraint
	$sfimage = sp_get_option('sfimage');
	$sfimage['constrain'] = true;
	$sfimage['forceclear'] = false;
	sp_update_option('sfimage', $sfimage);
	sp_response($section);
}

$section = 8629;
if ($build < $section) {
	sp_update_forum_moderators(); # build the list of moderators per forum
	sp_response($section);
}

$section = 8655;
if ($build < $section) {
	sp_add_option('account-name', '');
	sp_add_option('display-name', '');
	sp_add_option('guest-name', '');
	sp_response($section);
}

$section = 8656;
if ($build < $section) {
	# create mysql search sfmeta row
	$s = array();
	$v = spdb_select('row', "SHOW VARIABLES LIKE 'ft_min_word_len'");
	(empty($v->Value) ? $s['min'] = 4 : $s['min'] = $v->Value);
	$v = spdb_select('row', "SHOW VARIABLES LIKE 'ft_max_word_len'");
	(empty($v->Value) ? $s['max'] = 84 : $s['max'] = $v->Value);
	sp_add_sfmeta('mysql', 'search', $s, true);
	sp_response($section);
}

# Start of Upgrade Routines - 5.1.2 ============================================================

$section = 8691;
if ($build < $section) {
	if (!spdb_column_exists(SFAUTHS, 'admin_negate')) {
		spdb_query('ALTER TABLE '.SFAUTHS." ADD (admin_negate smallint(1) NOT NULL default '0')");
	}
	sp_response($section);
}

$section = 8700;
if ($build < $section) {
	# Add source column to posts
	spdb_query('ALTER TABLE '.SFPOSTS." ADD (source smallint(1) NOT NULL default '0')");
	sp_response($section);
}

$section = 8712;
if ($build < $section) {
	# create auth cat column for auths
	if (!spdb_column_exists(SFAUTHS, 'auth_cat')) {
		spdb_query('ALTER TABLE '.SFAUTHS." ADD (auth_cat bigint(20) NOT NULL default '1')");
	}

	# create new auth categories table
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFAUTHCATS.' (
			authcat_id tinyint(4) NOT NULL auto_increment,
			authcat_name varchar(50) NOT NULL,
			authcat_slug varchar(50) NOT NULL,
			authcat_desc tinytext,
			PRIMARY KEY	 (authcat_id),
			KEY authcat_slug_idx (authcat_slug)
		) '.spdb_charset();
	spdb_query($sql);

	# lets rename bypass_spam_control auth to bypass_math_question
	spdb_query('UPDATE '.SFAUTHS." SET auth_name='bypass_math_question' WHERE auth_name='bypass_spam_control'");

	# create default categories
	if (spdb_count(SFAUTHCATS) == 0) {
		spa_setup_auth_cats();
	}
	sp_response($section);
}

$section = 8860;
if ($build < $section) {
	# clean out some leftover globals
	$sfdisplay = sp_get_option('sfdisplay');
	unset($sfdisplay['stats']);
	unset($sfdisplay['groups']);
	unset($sfdisplay['topics']['maxtags']);
	unset($sfdisplay['posts']['tagstop']);
	unset($sfdisplay['posts']['tagsbottom']);
	sp_update_option('sfdisplay', $sfdisplay);
	sp_response($section);
}

$section = 8870;
if ($build < $section) {
	# lets rename bypass_spam_control description as it wasn't done when the name was done
	spdb_query('UPDATE '.SFAUTHS." SET auth_desc='Can bypass the post math question' WHERE auth_name='bypass_math_question'");
	sp_response($section);
}

# Start of Upgrade Routines - 5.1.3 ============================================================

$section = 8927;
if ($build < $section) {
	$authslug = sp_create_slug(spa_text('Creating'), true, SFAUTHCATS, 'authcat_slug');
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='$authslug'", 'authcat_id');
	sp_add_auth('reply_own_topics', spa_text('Can only reply to own topics'), 1, 1, 0, 1, $cat);

	$authslug = sp_create_slug(spa_text('Viewing'), true, SFAUTHCATS, 'authcat_slug');
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='$authslug'", 'authcat_id');
	sp_add_auth('view_own_admin_posts', spa_text('Can view only own posts and admin/mod posts'), 1, 0, 0, 1, $cat);
	sp_response($section);
}

$section = 8964;
if ($build < $section) {
	# clean up the auths again
	spdb_query('UPDATE '.SFAUTHS.' SET admin_negate = 0;');
	spdb_query('UPDATE '.SFAUTHS." SET admin_negate = 1 WHERE auth_name IN ('view_own_admin_posts', 'reply_own_topics');");

	$cat = spdb_table(SFAUTHCATS, "authcat_slug='general'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('report_posts', 'subscribe', 'watch', 'rate_posts', 'use_pm', 'vote_poll');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='viewing'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('view_forum', 'view_forum_lists', 'view_forum_topic_lists', 'view_admin_posts', 'view_email', 'view_profiles', 'view_members_list', 'view_links', 'view_online_activity', 'download_attachments', 'view_own_admin_posts');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='creating'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('start_topics', 'reply_topics', 'use_spoilers', 'use_signatures', 'create_linked_topics', 'create_poll', 'reply_own_topics');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='editing'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('edit_own_topic_titles', 'edit_any_topic_titles', 'edit_own_posts_forever', 'edit_own_posts_reply', 'edit_any_post');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='deleting'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('delete_topics', 'delete_own_posts', 'delete_any_post', 'break_linked_topics');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='moderation'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('bypass_math_question', 'bypass_moderation', 'bypass_moderation_once', 'moderate_posts', 'bypass_captcha');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='tools'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('pin_topics', 'move_topics', 'move_posts', 'lock_topics', 'pin_posts', 'reassign_posts', 'change_topic_status', 'edit_tags');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='uploading'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('upload_images', 'upload_media', 'upload_files', 'upload_signatures', 'upload_avatars');");
	sp_response($section);
}

# Start of Upgrade Routines - 5.1.4 ============================================================

$section = 9068;
if ($build < $section) {
	$sfavatars = sp_get_option('sfavatars');
	$sfavatars['sfavatarresize'] = true;
	$sfavatars['sfavatarresizequality'] = 90;
	sp_update_option('sfavatars', $sfavatars);
	sp_response($section);
}

$section = 9086;
if ($build < $section) {
	sp_create_installed_tables();
	sp_response($section);
}

$section = 9136;
if ($build < $section) {
	sp_fix_shortened_links(); # fix 4.x style shortened urls as they were saved in db and we do it now on display
	sp_response($section);
}

$section = 9141;
if ($build < $section) {
	$users = spdb_select('set', 'SELECT user_id, admin_options FROM '.SFMEMBERS.' WHERE admin=1 OR moderator=1');
	if ($users) {
		foreach ($users as $user) {
			$options = unserialize($user->admin_options);
			unset($options['colors']);
			sp_update_member_item($user->user_id, 'admin_options', $options);
		}
	}
	sp_response($section);
}

# Start of Upgrade Routines - 5.2 ============================================================

$section = 9175;
if ($build < $section) {
	# create new permission for adding links to posts
	$authslug = sp_create_slug(spa_text('Creating'), false, SFAUTHCATS, '');
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='$authslug'", 'authcat_id');
	sp_add_auth('create_links', spa_text('Can create links in posts'), 1, 0, 0, 0, $cat);

	# enable permission in roles based on current global flag
	$sffilters = sp_get_option('sffilters');
	if ($sffilters['sfallowlinks']) {
		$roles = spdb_table(SFROLES);
		if ($roles) {
			$auth_id = spdb_table(SFAUTHS, 'auth_name="create_links"', 'auth_id');
			foreach ($roles as $role) {
				$actions = unserialize($role->role_auths);
				$actions[$auth_id] = 1;
				spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
			}

			# reset all the auths
			sp_reset_auths();
		}
	}

	#remove old create links global option
	unset($sffilters['sfallowlinks']);
	sp_update_option('sffilters', $sffilters);
	sp_response($section);
}

$section = 9176;
if ($build < $section) {
	$users = spdb_select('set', 'SELECT user_id, admin_options FROM '.SFMEMBERS.' WHERE admin=1 OR moderator=1');
	if ($users) {
		foreach ($users as $user) {
			$options = unserialize($user->admin_options);
			$options['notify-edited'] = true;
			sp_update_member_item($user->user_id, 'admin_options', $options);
		}
	}
	sp_response($section);
}

$section = 9216;
if ($build < $section) {
	# Add default to poster_ip in sfposts
	spdb_query('ALTER TABLE '.SFPOSTS." CHANGE poster_ip poster_ip VARCHAR(39) NOT NULL DEFAULT '0.0.0.0'");
	sp_response($section);
}

$section = 9285;
if ($build < $section) {
	sp_add_option('combinecss', false);
	sp_add_option('combinejs', false);
	sp_response($section);
}

$section = 9400;
if ($build < $section) {
	$sfdisplay = sp_get_option('sfdisplay');
	$sfdisplay['editor']['toolbar'] = 0;
	sp_update_option('sfdisplay', $sfdisplay);
	sp_response($section);
}

# Start of Upgrade Routines - 5.2.1 ============================================================

$section = 9543;
if ($build < $section) {
	spdb_query('UPDATE '.SFAUTHS.' SET auth_desc = "'.sp_esc_sql(spa_text_noesc('Can view email and IP addresses of members')).'" WHERE auth_desc = "'.sp_esc_sql(spa_text_noesc('Can view email addresses of members')).'"');
	sp_response($section);
}

$section = 9550;
if ($build < $section) {
	# Add default to poster_ip in sfposts - being rerun as was missed from Install in 5.2
	spdb_query('ALTER TABLE '.SFPOSTS." CHANGE poster_ip poster_ip VARCHAR(39) NOT NULL DEFAULT '0.0.0.0'");
	sp_response($section);
}

# Start of Upgrade Routines - 5.2.3 ============================================================

$section = 9652;
if ($build < $section) {
	$sfmetatags = sp_get_option('sfmetatags');
	$sfmetatags['sfusekeywords'] = ($sfmetatags['sfusekeywords']) ? 2 : 1;
	sp_update_option('sfmetatags', $sfmetatags);
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (keywords varchar(256) default NULL)');
	sp_response($section);
}

$section = 9688;
if ($build < $section) {
	# create upgrade log section table def
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFLOGMETA.' (
			id int(11) unsigned NOT NULL AUTO_INCREMENT,
			version varchar(10) DEFAULT NULL,
			log_data tinytext,
			PRIMARY KEY (id)
		) '.spdb_charset();
	spdb_query($sql);

	# add new table to installed list
	$tables = sp_get_option('installed_tables');
	if ($tables) {
		if (!in_array(SFLOGMETA, $tables)) $tables[] = SFLOGMETA;
		sp_update_option('installed_tables', $tables);
	}
	sp_response($section);
}

$section = 9690;
if ($build < $section) {
	# clear our old duplicate upgrade log entries - no longer needed
	sp_tidy_install_log();
	sp_response($section);
}

$section = 9744;
if ($build < $section) {
	$sfadminsettings = sp_get_option('sfadminsettings');
	$sfadminsettings['editnotice'] = true;
	$sfadminsettings['movenotice'] = true;
	sp_update_option('sfadminsettings', $sfadminsettings);
	sp_response($section);
}

# Start of Upgrade Routines - 5.3 ============================================================

$section = 9884;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFFORUMS." ADD (forum_disabled smallint(1) NOT NULL default '0')");
	sp_response($section);
}

$section = 9898;
if ($build < $section) {
	sp_add_option('post_count_delete', false);
	sp_response($section);
}

$section = 10302;
if ($build < $section) {
	$sffilters = sp_get_option('sffilters');
	$sffilters['sfmaxsmileys'] = 0;
	sp_update_option('sffilters', $sffilters);
	sp_response($section);
}

$section = 10322;
if ($build < $section) {
	$controls = sp_get_option('sfcontrols');
	unset($controls['fourofour']);
	sp_update_option('sfcontrols', $controls);
	sp_response($section);
}

$section = 10336;
if ($build < $section) {
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='general'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('report_posts', 'subscribe', 'watch', 'rate_posts', 'use_pm', 'vote_poll', 'thank_posts', 'blogsearch', 'hide_posters');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='viewing'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('view_forum', 'view_forum_lists', 'view_forum_topic_lists', 'view_admin_posts', 'view_email', 'view_profiles', 'view_members_list', 'view_links', 'view_online_activity', 'download_attachments', 'view_own_admin_posts', 'view_private_topics_only');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='creating'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('start_topics', 'reply_topics', 'use_spoilers', 'use_signatures', 'create_linked_topics', 'post_by_email_reply', 'post_by_email_start', 'create_poll', 'reply_own_topics', 'create_links', 'post_as_user', 'post_multiple', 'set_topic_expire');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='editing'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('edit_own_topic_titles', 'edit_any_topic_titles', 'edit_own_posts_forever', 'edit_own_posts_reply', 'edit_any_post');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='deleting'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('delete_topics', 'delete_own_posts', 'delete_any_post', 'break_linked_topics');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='moderation'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('bypass_math_question', 'bypass_moderation', 'bypass_moderation_once', 'moderate_posts', 'bypass_captcha');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='tools'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('pin_topics', 'move_topics', 'move_posts', 'lock_topics', 'pin_posts', 'reassign_posts', 'change_topic_status', 'edit_tags');");
	$cat = spdb_table(SFAUTHCATS, "authcat_slug='uploading'", 'authcat_id');
	spdb_query('UPDATE '.SFAUTHS." SET auth_cat = $cat WHERE auth_name IN ('upload_images', 'upload_media', 'upload_files', 'upload_signatures', 'upload_avatars');");
	sp_response($section);
}

$section = 10376;
if ($build < $section) {
	$template = spdb_table(SFWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".sp_get_option('sfpage'), 'meta_value');
	$theme = array();
	$theme = sp_get_option('sp_mobile_theme');
	$theme['usetemplate'] = false;
	$theme['pagetemplate'] = $template;
	$theme['notitle'] = true;
	sp_add_option('sp_mobile_theme', $theme);
	sp_response($section);
}

$section = 10427;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFTRACK." ADD (device char(1) NOT NULL default 'D')");
	sp_response($section);
}

# Start of Upgrade Routines - 5.3.1 ============================================================

$section = 10508;
if ($build < $section) {
	# create a new permission for using smileys
	sp_add_auth('can_use_smileys', spa_text('Can use smileys in posts'), 1, 0, 0, 0, 3);

	# enable permission in roles based on current global flag
	$sfsmileys = sp_get_option('sfsmileys');
	if ($sfsmileys) {
		$roles = spdb_table(SFROLES);
		if ($roles) {
			$auth_id = spdb_table(SFAUTHS, 'auth_name="can_use_smileys"', 'auth_id');
			foreach ($roles as $role) {
				$actions = unserialize($role->role_auths);
				$actions[$auth_id] = 1;
				spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
			}
			# reset all the auths
			sp_reset_auths();
		}
	}
	sp_delete_option('sfsmileys');
	sp_response($section);
}

$section = 10510;
if ($build < $section) {
	# Add tablet theme options
	$theme = sp_get_option('sp_current_theme');
	sp_add_option('sp_tablet_theme', $theme);
	sp_response($section);
}

$section = 10518;
if ($build < $section) {
	# create a new permission for using iframes
	sp_add_auth('can_use_iframes', spa_text('Can use iframes in posts'), 1, 0, 0, 0, 3);

	# enable permission in roles based on current global flag
	$roles = spdb_table(SFROLES);
	if ($roles) {
		$auth_id = spdb_table(SFAUTHS, 'auth_name="can_use_iframes"', 'auth_id');
		foreach ($roles as $role) {
			$actions = unserialize($role->role_auths);
			$actions[$auth_id] = 0;
			spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
		}
		# reset all the auths
		sp_reset_auths();
	}
	sp_response($section);
}

$section = 10520;
if ($build < $section) {
	# warning column in auths table
	spdb_query('ALTER TABLE '.SFAUTHS.' ADD (warning tinytext)');
	spdb_query('UPDATE '.SFAUTHS." SET warning='".spa_text('*** WARNING *** The use of iframes is dangerous. Allowing users to create iframes enables them to launch a potential security threat against your website. Enabling iframes requires your trust in your users. Turn on with care.')."' WHERE auth_name='can_use_iframes'");
	sp_response($section);
}

# Start of Upgrade Routines - 5.3.2 ============================================================

$section = 10628;
if ($build < $section) {
	# add category of error to error log
	spdb_query('TRUNCATE '.SFERRORLOG);
	spdb_query('ALTER TABLE '.SFERRORLOG." ADD (error_cat varchar(13) NOT NULL default 'spaErrOther')");
	spdb_query('ALTER TABLE '.SFERRORLOG.' ADD (keycheck varchar(45))');
	sp_response($section);
}

$section = 10633;
if ($build < $section) {
	# create cache storage location
	sp_create_cache_location();
	sp_clear_combined_css('all');
	sp_clear_combined_css('mobile');
	sp_clear_combined_css('tablet');
	sp_clear_combined_scripts();
	sp_response($section);
}

$section = 10693;
if ($build < $section) {
	# add count of error to error log
	spdb_query('ALTER TABLE '.SFERRORLOG.' ADD (error_count tinyint)');
	sp_response($section);
}

$section = 10707;
if ($build < $section) {
	# force rebuild of auths cache since users global auths may be wrong
	sp_reset_auths();
	sp_response($section);
}

$section = 10715;
if ($build < $section) {
	# create new table for special ranks
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFSPECIALRANKS.' (
		id int(11) unsigned NOT NULL AUTO_INCREMENT,
		user_id bigint(20) default NULL,
		special_rank varchar(100),
		PRIMARY KEY (id),
		KEY user_id_idx (user_id),
		KEY special_rank_idx (special_rank)
		) '.spdb_charset();
	spdb_query($sql);

	$sr = spdb_select('set', 'SELECT user_id, special_ranks FROM '.SFMEMBERS.' WHERE LENGTH(special_ranks) > 6');
	if ($sr) {
		foreach ($sr as $usr) {
			$ranks = unserialize($usr->special_ranks);
			foreach ($ranks as $rank) {
				sp_add_special_rank($usr->user_id, $rank);
			}
		}
	}
	spdb_query('ALTER TABLE '.SFMEMBERS.' DROP special_ranks');
	sp_response($section);
}

$section = 10733;
if ($build < $section) {
	# create new table for user activity (in preparation)
	$sql = 'CREATE TABLE IF NOT EXISTS '.SFUSERACTIVITY.' (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) NOT NULL,
				type_id smallint(4) NOT NULL,
				item_id bigint(20) NOT NULL,
				PRIMARY KEY (id),
				KEY type_id_idx (type_id),
				KEY user_id_idx (user_id)
			) '.spdb_charset();
	spdb_query($sql);
	sp_response($section);
}

# Start of Upgrade Routines - 5.4.0 ============================================================

$section = 10841;
if ($build < $section) {
	# change error count column to small int data type
	spdb_query('ALTER TABLE '.SFERRORLOG.' CHANGE error_count error_count SMALLINT;');
	$spError = array();
	$spError['spErrorLogOff'] = false;
	$spError['spNoticesOff'] = false;
	sp_update_option('spErrorOptions', $spError);
	sp_response($section);
}

$section = 10844;
if ($build < $section) {
	# new manage theme and plugin caps
	$admins = spdb_table(SFMEMBERS, 'admin = 1');
	if ($admins) {
	   foreach ($admins as $admin) {
			$user = new WP_User($admin->user_id);
			if ($user->has_cap('SPF Manage Options')) {
				$user->add_cap('SPF Manage Integration');
			}
		}
	}
	sp_response($section);
}

# Start of Upgrade Routines - 5.4.2 ============================================================

$section = 11048;
if ($build < $section) {
	# the cache table (5.4.2)
	$sql = '
		CREATE TABLE IF NOT EXISTS '.SFCACHE." (
			cache_id varchar(40) NOT NULL DEFAULT '',
			cache_out bigint(6) DEFAULT NULL,
			cache mediumtext,
			PRIMARY KEY (cache_id)
		) ".spdb_charset();
	spdb_query($sql);

	sp_response($section);
}

$section = 11051;
if ($build < $section) {
	# the cache table (5.4.2)
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (topic_icon_locked varchar(50) default NULL)');

	sp_response($section);
}

$section = 11061;
if ($build < $section) {
	# create recent topic cache record
	sp_add_option('topic_cache', 200);
	$sql = 'SELECT DISTINCTROW forum_id, topic_id, post_id, post_status
			FROM '.SFPOSTS.'
			ORDER BY post_id DESC
			LIMIT 200';

	$topics = spdb_select('set', $sql, ARRAY_N);
	if ($topics) {
		sp_add_sfmeta('topic_cache', 'new', $topics, true);
	}

	sp_response($section);
}

$section = 11071;
if ($build < $section) {
	# add missing tables to installed list
	$tables = sp_get_option('installed_tables');
	if ($tables) {
		if (!in_array(SFSPECIALRANKS, $tables)) $tables[] = SFSPECIALRANKS;
		if (!in_array(SFUSERACTIVITY, $tables)) $tables[] = SFUSERACTIVITY;
		if (!in_array(SFCACHE, $tables)) $tables[] = SFCACHE;
		sp_update_option('installed_tables', $tables);
	}

	sp_response($section);
}

$section = 11112;
if ($build < $section) {
	# Clean out the spInspect array as it may have become corrupted
	sp_update_option('spInspect', '');

	sp_response($section);
}

$section = 11167;
if ($build < $section) {
	$sfseo = sp_get_option('sfseo');
	$sfseo['sfseo_homepage'] = false;
	sp_update_option('sfseo', $sfseo);

	sp_response($section);
}

$section = 11183;
if ($build < $section) {
	# Add display column to tracking table
	spdb_query('ALTER TABLE '.SFTRACK.' ADD (display varchar(255) default NULL)');

	sp_response($section);
}

$section = 11207;
if ($build < $section) {
	$sfprofile = sp_get_option('sfprofile');
	if ($sfprofile['weblink'] == 1) {
		$sfprofile['namelink'] = 3;
	} else {
		$sfprofile['namelink'] = 2;
	}

	unset($sfprofile['profileinstats']);
	unset($sfprofile['profilelink']);
	unset($sfprofile['weblink']);
	sp_update_option('sfprofile', $sfprofile);

	sp_response($section);
}

# Start of Upgrade Routines - 5.5.0 ============================================================

$section = 11325;
if ($build < $section) {
	# correct error in mobile and table overlay saving - if not overlay, it saved overlay as desktop overlay
	$mobileTheme = sp_get_option('sp_mobile_theme');
	if ($mobileTheme && !file_exists(SPTHEMEBASEDIR.$mobileTheme['theme'].'/styles/overlays/'.$mobileTheme['color'].'.php')) {
		$mobileTheme['color'] = '';
		sp_update_option('sp_mobile_theme', $mobileTheme);
	}

	$tabletTheme = sp_get_option('sp_tablet_theme');
	if ($tabletTheme && !file_exists(SPTHEMEBASEDIR.$tabletTheme['theme'].'/styles/overlays/'.$tabletTheme['color'].'.php')) {
		$tabletTheme['color'] = '';
		sp_update_option('sp_tablet_theme', $tabletTheme);
	}

	sp_response($section);
}

$section = 11375;
if ($build < $section) {
	# create a new permission for using iframes
	sp_add_auth('bypass_flood_control', spa_text('Can bypass wait time between posts'), 1, 0, 0, 0, 3);
	sp_add_option('floodcontrol', 10);

	sp_response($section);
}

$section = 11388;
if ($build < $section) {
	$sffilters = sp_get_option('sffilters');
	if (!isset($sffilters['sfmaxsmileys'])) {
		$sffilters['sfmaxsmileys'] = 0;
		sp_update_option('sffilters', $sffilters);
	}

	# add new column for meta data to user activity table
	spdb_query('ALTER TABLE '.SFUSERACTIVITY.' ADD (meta_id BIGINT(20))');

	sp_response($section);
}

$section = 11423;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (forum_icon_locked varchar(50) default NULL)');

	sp_response($section);
}

$section = 11447;
if ($build < $section) {
	# set autoload flag to true for autoupdates
	$meta = sp_get_sfmeta('autoupdate', 'user');
	if (!empty($meta[0])) sp_update_sfmeta('autoupdate', 'user', $meta[0]['meta_value'], $meta[0]['meta_id'], 1);

	sp_response($section);
}

# Start of Upgrade Routines - 5.5.1 ============================================================

sp_bump_build($build, 11493);

$section = 11500;
if ($build < $section) {
	# Begin Database Clean-up routines
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_table_list();
	sp_response($section);
}

$section = 11502;
if ($build < $section) {
	# Database Table Clean-up routines (11502 - 11516)
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(0, 4);
	sp_response($section);
}

$section = 11504;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(5, 9);
	sp_response($section);
}

$section = 11506;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(10, 14);
	sp_response($section);
}

$section = 11508;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(15, 19);
	sp_response($section);
}

$section = 11510;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(20, 24);
	sp_response($section);
}

$section = 11512;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(25, 29);
	sp_response($section);
}

$section = 11514;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(30, 34);
	sp_response($section);
}

$section = 11516;
if ($build < $section) {
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_schema(35, 39);
	sp_response($section);
}

$section = 11548;
if ($build < $section) {
	# try to increase cpu time
    $time = @ini_get('max_execution_time');
	@ini_set('max_execution_time', '600');

	define('SPPOSTATTACHMENTS', SF_PREFIX.'sfpostattachments');
	$exists = spdb_query('SHOW COLUMNS FROM '.SFPOSTS.' LIKE "attachments"');
	if ($exists) {
		# convert post attachments to its own table
		$sql = '
			CREATE TABLE IF NOT EXISTS '.SPPOSTATTACHMENTS.' (
				attachment_id BIGINT(20) NOT NULL auto_increment,
				post_id BIGINT(20) NOT NULL,
				topic_id BIGINT(20) NOT NULL,
				type VARCHAR(10) NOT NULL,
				path VARCHAR(250) NOT NULL,
				filename VARCHAR(100) NOT NULL,
				size INT NOT NULL,
				PRIMARY KEY (attachment_id),
				KEY post_id_idx (post_id),
				KEY topic_id_idx (topic_id)
			) '.spdb_charset();
		spdb_query($sql);

		# add to installed tables
		$tables = sp_get_option('installed_tables');
		if ($tables) {
			if (!in_array(SPPOSTATTACHMENTS, $tables)) $tables[] = SPPOSTATTACHMENTS;
			sp_update_option('installed_tables', $tables);
		}

		# convert from old attachments column in post table to its own table
		$sfconfig = sp_get_option('sfconfig');
		$posts = spdb_select('set', 'SELECT post_id, topic_id, attachments FROM '.SFPOSTS.' WHERE attachments > ""');
		if ($posts) {
			foreach ($posts as $post) {
				$attachments = unserialize($post->attachments);
				if ($attachments) {
					foreach ($attachments as $attachment) {
						if ($attachment['type'] == 'image') {
							$match = $sfconfig['image-uploads'];
						} else if ($attachment['type'] == 'media') {
							$match = $sfconfig['media-uploads'];
						} else {
							$match = $sfconfig['file-uploads'];
						}
						$path = explode($match.'/', $attachment['path']);
						spdb_query('INSERT INTO '.SPPOSTATTACHMENTS." (post_id, topic_id, type, path, filename, size) VALUES ($post->post_id, $post->topic_id, '".$attachment['type']."', '$path[1]', '".$attachment['file']."', ".$attachment['size'].")");
					}
				}
			}
		}

		# drop old column in posts
		spdb_query('ALTER TABLE '.SFPOSTS.' DROP attachments');
	}

	# restore cpu time
	@ini_set($time);

	sp_response($section);
}

$section = 11662;
if ($build < $section) {
	sp_add_option('spwptexturize', false);

	sp_response($section);
}

$section = 11850;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFPOSTS.' MODIFY guest_name varchar(50) default NULL');
	spdb_query('ALTER TABLE '.SFPOSTS.' MODIFY guest_email varchar(75) default NULL');
	spdb_query('ALTER TABLE '.SFNOTICES.' MODIFY guest_email varchar(75) default NULL');

	sp_response($section);
}

$section = 11877;
if ($build < $section) {
	# change post width option setting
	sp_delete_option('sfpostwidth');
	$sfp = array();
	$sfp = sp_get_option('sfpostwrap');
	sp_update_option('sfpostwrap', $sfp['postwrap']);

	sp_response($section);
}

sp_bump_build($build, 11910);

# Start of Upgrade Routines - 5.5.2 ============================================================

sp_bump_build($build, 11930);

$section = 11999;
if ($build < $section) {
	# Repeat 5.5.1 Database Clean-up routines
	include SPBOOT.'install/sp-schema.php';
	sp_rebuild_table_list();
	sp_response($section);
}

sp_bump_build($build, 12017);

# Start of Upgrade Routines - 5.5.3 ============================================================

sp_bump_build($build, 12020);

sp_bump_build($build, 12176);

# Start of Upgrade Routines - 5.5.4 ============================================================

sp_bump_build($build, 12179);

$section = 12340;
if ($build < $section) {
    if (file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.'header.js')) @unlink(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.'header.js');
    if (file_exists(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.'footer.js')) @unlink(SP_COMBINED_CACHE_DIR.SP_COMBINED_SCRIPTS_BASE_NAME.'footer.js');
	sp_response($section);
}

$section = 12368;
if ($build < $section) {
	sp_add_option('spheaderspace', 0);
	sp_response($section);
}

sp_bump_build($build, 12424);

# Start of Upgrade Routines - 5.5.5 ============================================================

sp_bump_build($build, 12428);

$section = 12445;
if ($build < $section) {
	# add new column for plugin_data in sfmember
	spdb_query('ALTER TABLE '.SFMEMBERS.' ADD (plugin_data longtext)');
	sp_response($section);
}

$section = 12446;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (topic_icon_pinned varchar(50) default NULL)');
	sp_response($section);
}

$section = 12479;
if ($build < $section) {
	# add starting captcha value
	sp_add_option('captcha-value', time());
	sp_response($section);
}

$section = 12487;
if ($build < $section) {
	# create a new permission for using smileys
	sp_add_auth('edit_own_posts_for_time', spa_text('Can edit own posts for time period'), 1, 1, 0, 0, 4);

    sp_add_option('editpostdays', 7);
	sp_response($section);
}

$section = 12493;
if ($build < $section) {
	# Clean out the spInspect array as it has some new members
	sp_create_inspectors();
	sp_response($section);
}

$section = 12581;
if ($build < $section) {
	# Correct users auths records in case they are incorrect
	spdb_query("UPDATE ".SFAUTHS."
				SET ignored = 1
				WHERE auth_name IN
				('can_use_iframes','moderate_posts','delete_any_post','delete_own_posts','delete_topics','view_email','view_own_admin_posts','edit_own_topic_titles','edit_any_topic_titles','edit_own_posts_for_time','edit_own_posts_forever','edit_own_posts_reply','edit_any_post','upload_images','upload_media','upload_files','use_signatures','upload_signatures','upload_avatars','subscribe','watch','rate_posts','use_pm','create_linked_topics','break_linked_topics','reply_own_topics','thank_posts','manage_attachments','hide_posters','post_anonymous','post_as_user','post_multiple','set_topic_expire','create_topic_redirects','change_topic_status');");

	spdb_query("UPDATE ".SFAUTHS."
				SET enabling = 1
				WHERE auth_name IN
				('upload_avatars','use_pm','create_linked_topics','break_linked_topics','change_topic_status');");

	spdb_query("UPDATE ".SFAUTHS."
				SET admin_negate = 1
				WHERE auth_name IN
				('reply_own_topics','view_own_admin_posts');");
	spdb_query('UPDATE '.SFAUTHS." SET warning='".spa_text('*** WARNING *** The use of iframes is dangerous. Allowing users to create iframes enables them to launch a potential security threat against your website. Enabling iframes requires your trust in your users. Turn on with care.')."' WHERE auth_name='can_use_iframes'");
	sp_response($section);
}

sp_bump_build($build, 12610);

# Start of Upgrade Routines - 5.5.6 ============================================================

sp_bump_build($build, 12616);

$section = 12709;
if ($build < $section) {
    sp_add_caps();
	sp_response($section);
}

sp_bump_build($build, 12749);

# Start of Upgrade Routines - 5.5.7 ============================================================

sp_bump_build($build, 12756);

$section = 12758;
if ($build < $section) {
    # update autoupdate meta to not have full url, just the ahah loader info
	$spMeta = spdb_table(SFMETA, 'meta_type="autoupdate"');
	if (!empty($spMeta)) {
		foreach ($spMeta as $meta) {
			if (!empty($meta)) {
                $value = unserialize($meta->meta_value);
                $value[1] = str_replace(site_url('', 'http').'/index.php?', '', $value[1]);
                $value[1] = str_replace(site_url('', 'https').'/index.php?', '', $value[1]);
                sp_update_sfmeta('autoupdate', $meta->meta_key, $value, $meta->meta_id, $meta->autoload);
            }
		}
	}
	sp_response($section);
}

$section = 12760;
if ($build < $section) {
	# convert table collation
	$tables = sp_get_option('installed_tables');
	foreach($tables as $table) {
		sp_convert_table_to_utf8mb4($table);
	}
	sp_response($section);
}

sp_bump_build($build, 12968);

# Start of Upgrade Routines - 5.5.8 ============================================================

sp_bump_build($build, 13000);

# Start of Upgrade Routines - 5.5.9 ============================================================

sp_bump_build($build, 13001);

$section = 13042;
if ($build < $section) {
	# create forum festure images folder
	sp_create_forum_image_location();
	# add new column to forum table
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (feature_image varchar(50) default NULL)');
	# add new options to seo
	$sfseo = sp_get_option('sfseo');
	$sfseo['sfseo_og'] = false;
	$sfseo['seo_og_attachment'] = false;
	$sfseo['seo_og_type'] = 'website';
	sp_update_option('sfseo', $sfseo);

	sp_response($section);
}

sp_bump_build($build, 13315);

# Start of Upgrade Routines - 5.5.10 ============================================================

sp_bump_build($build, 13316);

$section = 13317;
if ($build < $section) {
	$sfmail = sp_get_option('sfnewusermail', $sfmail);
    $sfmail['sfnewusertext'] = str_replace('%NEWLINE%Password: %PASSWORD% ', '', $sfmail['sfnewusertext']);
    $sfmail['sfnewusertext'] = str_replace('%PASSWORD%', '', $sfmail['sfnewusertext']);
    $sfmail['sfnewusertext'].= ' %NEWLINE%Password Retrieval URL: %PWURL%';
	sp_update_option('sfnewusermail', $sfmail);

	sp_response($section);
}

# Start of Upgrade Routines - 5.5.11 ============================================================

sp_bump_build($build, 13318);

# Start of Upgrade Routines - 5.6 ============================================================

$section = 13495;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFMEMBERSHIPS.' ADD KEY user_id_idx (user_id);');

    sp_bump_build($build, 13495);
}

sp_bump_build($build, 13657);

# Start of Upgrade Routines - 5.6.1 ============================================================

sp_bump_build($build, 13700);

# Start of Upgrade Routines - 5.6.2 ============================================================

sp_bump_build($build, 13710);

$section = 13720;
if ($build < $section) {
	# default avatars
	global $spPaths;
	@mkdir(SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults', 0775);
	@copy (SF_STORE_DIR.'/'.$spPaths['avatars'].'/admindefault.png', SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/admindefault.png');
	@copy (SF_STORE_DIR.'/'.$spPaths['avatars'].'/guestdefault.png', SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/guestdefault.png');
	@copy (SF_STORE_DIR.'/'.$spPaths['avatars'].'/userdefault.png', SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/userdefault.png');

	$path = dirname(__file__).'/resources/objects/avatars/';
	$flist = @opendir($path);
	while (false !== ($file = readdir($flist))) {
		if ($file != "." && $file != "..") {
			@copy ($path.$file, SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/'.$file);
		}
	}
	closedir($flist);

	$defs = array();
	$defs['admin'] = 'admindefault.png';
	$defs['mod'] = 'moderatordefault.png';
	$defs['member'] = 'userdefault.png';
	$defs['guest'] = 'guestdefault.png';

	sp_add_option('spDefAvatars', $defs);
	sp_response($section);
}

$section = 13755;
if ($build < $section) {
	# admin search data
	include SPBOOT.'install/resources/objects/sql/sp-admin-glossary.php';

	# add new tables to installed list
	$tables = sp_get_option('installed_tables');
	if ($tables) {
		if (!in_array(SFADMINKEYWORDS, $tables)) $tables[] = SFADMINKEYWORDS;
		if (!in_array(SFADMINTASKS, $tables)) $tables[] = SFADMINTASKS;
		sp_update_option('installed_tables', $tables);
	}
	sp_response($section);
}

sp_bump_build($build, 13859);

# Start of Upgrade Routines - 5.6.3 ============================================================

sp_bump_build($build, 13875);

# Start of Upgrade Routines - 5.6.3.1 ============================================================

sp_bump_build($build, 13877);

# Start of Upgrade Routines - 5.6.4 ============================================================

sp_bump_build($build, 13880);

# Start of Upgrade Routines - 5.6.5 ============================================================

sp_bump_build($build, 13942);

$section = 14010;
if ($build < $section) {
	spdb_query('ALTER TABLE '.SFFORUMS." ADD (last_topic_id bigint(20) NOT NULL default '0')");
	$sql = "SELECT topic_id, ".SFFORUMS.".forum_id
			FROM ".SFTOPICS."
			JOIN ".SFFORUMS." ON ".SFTOPICS.".forum_id = ".SFFORUMS.".forum_id
			WHERE topic_id =
			(SELECT topic_id FROM ".SFTOPICS."
			WHERE ".SFTOPICS.".forum_id = ".SFFORUMS.".forum_id
			ORDER BY topic_id DESC
			LIMIT 1);";
	$forums = spdb_select('set', $sql);
	if($forums) {
		foreach($forums as $f) {
			spdb_query('UPDATE '.SFFORUMS.' SET last_topic_id='.$f->topic_id.' WHERE forum_id='.$f->forum_id);
		}
	}
	sp_response($section);
}

$section = 14030;
if ($build < $section) {
	# new pinned with new posts custom icon
	spdb_query('ALTER TABLE '.SFFORUMS.' ADD (topic_icon_pinned_new varchar(50) default NULL)');
	sp_response($section);
}

$section = 14070;
if ($build < $section) {
	# remove mysql min/max string values from sfmeta
	sp_delete_sfmeta(0, 'search', 'mysql');
	sp_response($section);
}

sp_bump_build($build, 14127);

# Start of Upgrade Routines - 5.6.6 ============================================================

sp_bump_build($build, 14135);

$section = 14150;
if ($build < $section) {
	# add hide user from stats
	spdb_query('ALTER TABLE '.SFUSERGROUPS." ADD (hide_stats tinyint(1) unsigned NOT NULL default '0')");
	sp_response($section);
}

sp_bump_build($build, 14173);

# Start of Upgrade Routines - 5.6.7 ============================================================

sp_bump_build($build, 14175);

$section = 14180;
if ($build < $section) {
	# add hide members in member total stat
	$sfcontrols = sp_get_option('sfcontrols');
	$sfcontrols['hidemembers'] = false;
	sp_update_option('sfcontrols', $sfcontrols);
	sp_response($section);
}

sp_bump_build($build, 14199);

# Start of Upgrade Routines - 5.7 ============================================================

sp_bump_build($build, 14201);

$section = 14290;
if ($build < $section) {
	# alter autoupdate records
	$recs = spdb_select('set', 'SELECT * FROM '.SFMETA.' WHERE meta_type="autoupdate"');
	if ($recs) {
		foreach ($recs as $rec) {
			$key = $rec->meta_key;
			if ($key == 'user') {
				sp_delete_sfmeta($rec->meta_id);
			} else {
				$data = unserialize($rec->meta_value);
				$data[1] = str_replace('sp_ahah=', '', $data[1]);
				$sql = "UPDATE ".SFMETA." SET meta_value = '".serialize($data)."' WHERE meta_key = '".$key."' AND meta_type='autoupdate'";
				spdb_query($sql);
			}
		}
	}
	sp_response($section);
}

sp_bump_build($build, 14429);

$section = 14430;
if ($build < $section) {
	# alter profile photos data
	$profile = sp_get_option('sfprofile');
	unset($profile['photoswidth']);
	unset($profile['photosheight']);
	$profile['photoscols'] = 3;
	sp_update_option('sfprofile', $profile);
	sp_response($section);
}

sp_bump_build($build, 14479);

# Start of Upgrade Routines - 5.7.1 ============================================================

sp_bump_build($build, 14485);

$section = 14492;
if ($build < $section) {
	sp_add_auth('can_view_images', sp_esc_sql(spa_text_noesc('Can view images in posts')), 1, 0, 0, 0, 2);
	sp_add_auth('can_view_media', sp_esc_sql(spa_text_noesc('Can view media in posts')), 1, 0, 0, 0, 2);

	$image = spdb_select('var', "SELECT auth_id FROM ".SFAUTHS." WHERE auth_name = 'can_view_images'");
	$media = spdb_select('var', "SELECT auth_id FROM ".SFAUTHS." WHERE auth_name = 'can_view_media'");

	$roles = spdb_table(SFROLES);
	foreach ($roles as $role) {
		$actions = unserialize($role->role_auths);
		$actions[$image] = 1;
		$actions[$media] = 1;
		spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
	}

	sp_response($section);
}

sp_bump_build($build, 14502);

# Start of Upgrade Routines - 5.7.2 ============================================================

sp_bump_build($build, 14508);

$section = 14512;
if ($build < $section) {
	# reset users new post arrays
	$list = array();
	$list['topics'] = array();
	$list['forums'] = array();
	$list['post'] = array();

	spdb_query("UPDATE ".SFMEMBERS." SET newposts='".serialize($list)."';");
	sp_response($section);
}

$section = 14520;
if ($build < $section) {
	# add new posts flag support
	$sfcontrols = sp_get_option('sfcontrols');
	$sfcontrols['flagsuse'] = true;
	$sfcontrols['flagstext'] = 'new';
	$sfcontrols['flagsbground'] = 'ff0000';
	$sfcontrols['flagscolor'] = 'ffffff';
	sp_update_option('sfcontrols', $sfcontrols);

	sp_response($section);
}

$section = 14600;
if ($build < $section) {
	# add new login/register options
	$splogin = array();
	$splogin = sp_get_option('sflogin');
	$splogin['spshowlogin'] = true;
	$splogin['spshowregister'] = true;
	$splogin['spaltloginurl'] = '';
	$splogin['spaltlogouturl'] = '';
	$splogin['spaltregisterurl'] = '';
	sp_update_option('sflogin', $splogin);

	sp_response($section);
}

sp_bump_build($build, 14623);

# Start of Upgrade Routines - 5.7.3 ============================================================

sp_bump_build($build, 14754);

# Start of Upgrade Routines - 5.7.4 ============================================================

sp_bump_build($build, 14760);

# Start of Upgrade Routines - 5.7.5 ============================================================

sp_bump_build($build, 14765);

# Start of Upgrade Routines - 5.7.5.1 ============================================================

sp_bump_build($build, 14770);

# Start of Upgrade Routines - 5.7.5.2 ============================================================

sp_bump_build($build, 14775);

# Start of Upgrade Routines - 5.7.5.3 ============================================================

sp_bump_build($build, 14776);

# ****** IMPORTANT: THE FINAL $section valuse MUST be the same as the SPBUILD constant
# ******			for the Upgrade to complete correctly

die();

?>