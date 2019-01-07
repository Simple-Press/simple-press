<?php
/*
Simple:Press
Install & Upgrade Support Routines
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================
#
# GLOBAL UPDATE/INSTALL ROUTINES
#
# ==========================================

# Called at the end of each upgrade section
function sp_response($section, $die=true, $status='success', $error='') {
	global $wpdb, $current_user;

    $response = array('status' => '', 'type' => '', 'section' => '', 'response' => '', 'error' => '');

    # log the build section and status in the response
    echo "Build upgrade section $section executing.  Status: $status <br />";
    if ($status == 'error' && !empty($error)) echo "Error: $error <br />";

    # build the response
    $response['status'] = $status;
    $response['type'] = 'upgrade';
    $response['section'] = $section;
    $response['error'] = $error;
    $response['response'] = ob_get_contents();

    # save as log meta data if table exists! (Need to check if installed yet sadly)
	$go = $wpdb->get_var("SHOW TABLES LIKE '".SFLOGMETA."'");
	if ($go) {
		$sql = '
			INSERT INTO '.SFLOGMETA." (version, log_data)
			VALUES (
			'".SPVERSION."',
			'".serialize($response)."')";
		spdb_query($sql);
	}

    ob_end_clean();

    # send the response (mark with tags so we can extract only the response)
    echo '%%%marker%%%';
    print json_encode($response);
    echo '%%%marker%%%';

	# and if this is the last update in build finish off...
	if (SPBUILD == $section) {
		# let plugins know
		do_action('sph_upgrade_done', SPBUILD);
		# Finished Upgrades ===============================================================================
		sp_log_event(SPRELEASE, SPVERSION, SPBUILD, $current_user->ID);

		delete_option('sfInstallID'); # use wp option table

		# and some final cleanuop tasks
		sp_reset_auths();

		sp_clear_combined_css('all');
		sp_clear_combined_css('mobile');
		sp_clear_combined_css('tablet');

		sp_clear_combined_scripts('desktop');
		sp_clear_combined_scripts('mobile');
		sp_clear_combined_scripts('tablet');

		sp_flush_cache('all');
		sp_reset_member_plugindata();

		sp_update_option('sfforceupgrade', false);

        # force a rewrite rules flush on next page load
		sp_update_option('sfflushrules', true);
	}

    if ($die) die();
}

# Called to log updates.
function sp_log_event($release, $version, $build, $user) {
	$now = current_time('mysql');
	# check if already an entry for this version
	$check = spdb_table(SFLOG, "version='".SPVERSION."'");
	if ($check) {
		# we need an update query
		$sql = '
			UPDATE '.SFLOG." SET user_id=$user, install_date='$now',
			release_type='$release', build=$build WHERE version='".SPVERSION."'";
	} else {
		# we need an insert query
		$sql = '
			INSERT INTO '.SFLOG." (user_id, install_date, release_type, version, build)
			VALUES (
			$user,
			'$now',
			'$release',
			'$version',
			$build)";
	}
	spdb_query($sql);

	sp_update_option('sfversion', $version);
	sp_update_option('sfbuild', $build);
}

# Called to update build nimber wjen no other tasks to perform
# such as the start and end of each release section (since 5.5.1)
function sp_bump_build($build, $section) {
	if ($build < $section) {
		# bump the build number
		sp_response($section);
	}
}

function sp_build_base_smileys() {
	$smileys = array(
	'Confused' => 	array (	0 => 'sf-confused.gif',		1 => ':???:',   2 => 1, 3 => 0, 4 => 0 ),
	'Cool' =>		array (	0 => 'sf-cool.gif',			1 => ':cool:',  2 => 1, 3 => 1, 4 => 0 ),
	'Cry' =>		array (	0 => 'sf-cry.gif',			1 => ':cry:',   2 => 1, 3 => 2, 4 => 0 ),
	'Embarassed' =>	array (	0 => 'sf-embarassed.gif',	1 => ':oops:',  2 => 1, 3 => 3, 4 => 0 ),
	'Frown' =>		array (	0 => 'sf-frown.gif',		1 => ':frown:', 2 => 1, 3 => 4, 4 => 0 ),
	'Kiss' =>		array (	0 => 'sf-kiss.gif',			1 => ':kiss:',  2 => 1, 3 => 5, 4 => 0 ),
	'Laugh' =>		array (	0 => 'sf-laugh.gif',		1 => ':lol:',   2 => 1, 3 => 6, 4 => 0 ),
	'Smile' =>		array (	0 => 'sf-smile.gif',		1 => ':smile:', 2 => 1, 3 => 7, 4 => 0 ),
	'Surprised' =>	array (	0 => 'sf-surprised.gif',	1 => ':eek:',   2 => 1, 3 => 8, 4 => 0 ),
	'Wink' =>		array (	0 => 'sf-wink.gif',			1 => ':wink:',  2 => 1, 3 => 9, 4 => 0 ),
	'Yell' =>		array (	0 => 'sf-yell.gif',			1 => ':yell:',  2 => 1, 3 => 10, 4 => 0 )
	);

	sp_add_sfmeta('smileys', 'smileys', $smileys, 1);
}

function sp_create_usergroup_meta($members) {
	global $wp_roles;

	$roles = array_keys($wp_roles->role_names);
	if ($roles) {
		foreach ($roles as $role) {
			sp_add_sfmeta('default usergroup', $role, $members); # initally set each role to members usergroup
		}
	}
}

function sp_install_members_table($subphase) {
	global $wpdb, $current_user;

	# get limits for installs
	if ($subphase != 0) $limit = ' LIMIT 200 OFFSET '.(($subphase - 1) * 200);

	# select all users
	$sql = 'SELECT ID FROM '.SFUSERS.$limit;
	$members = $wpdb->get_results($sql);

	if ($members) {
		foreach ($members as $member) {
			# Check ID exists and is not zero
			if (is_numeric($member->ID) && $member->ID > 0) {
                sp_create_member_data($member->ID, true);

                # for the admin installer, remove any usergroup membership added by create member function
				if ($current_user->ID == $member->ID) $wpdb->query('DELETE FROM '.$wpdb->prefix."sfmemberships WHERE user_id=$member->ID");
			}
		}
	}
}

# ==================================
# MAY BE BOTH INSTALL AND UPGRADE
# ==================================

# 5.0 options update
function sp_new_options_update() {
	sp_delete_option('sfshowicon');

	$sfdisplay = sp_get_option('sfdisplay');
	unset($sfdisplay['breadcrumbs']);
    unset($sfdisplay['search']);
    unset($sfdisplay['quicklinks']);
    unset($sfdisplay['pagelinks']);
    unset($sfdisplay['firstlast']);
    unset($sfdisplay['unreadcount']);
    unset($sfdisplay['groups']['description']);
    unset($sfdisplay['groups']['showsubforums']);
    unset($sfdisplay['groups']['combinesubcount']);
    unset($sfdisplay['groups']['showallsubs']);
    unset($sfdisplay['forums']['description']);
    unset($sfdisplay['forums']['newposticon']);
    unset($sfdisplay['forums']['pagelinks']);
    unset($sfdisplay['forums']['newcount']);
    unset($sfdisplay['forums']['newposts']);
    unset($sfdisplay['forums']['newabove']);
    unset($sfdisplay['forums']['sortinforum']);
    unset($sfdisplay['forums']['topiccol']);
    unset($sfdisplay['forums']['postcol']);
    unset($sfdisplay['forums']['lastcol']);
    unset($sfdisplay['forums']['showtitle']);
    unset($sfdisplay['forums']['showtitletop']);
    unset($sfdisplay['forums']['pinned']);
	unset($sfdisplay['topics']['numpagelinks']);
	unset($sfdisplay['topics']['firstcol']);
	unset($sfdisplay['topics']['lastcol']);
	unset($sfdisplay['topics']['postcol']);
	unset($sfdisplay['topics']['viewcol']);
	unset($sfdisplay['topics']['pagelinks']);
	unset($sfdisplay['topics']['statusicons']);
	unset($sfdisplay['topics']['postrating']);
	unset($sfdisplay['topics']['topicstatus']);
	unset($sfdisplay['topics']['print']);
	unset($sfdisplay['topics']['showsubforums']);
	unset($sfdisplay['topics']['posttip']);
	unset($sfdisplay['posts']['numpagelinks']);
	unset($sfdisplay['posts']['userabove']);
	unset($sfdisplay['posts']['showedits']);
	unset($sfdisplay['posts']['showlastedit']);
	unset($sfdisplay['posts']['topicstatushead']);
	unset($sfdisplay['posts']['topicstatuschanger']);
	unset($sfdisplay['posts']['online']);
	unset($sfdisplay['posts']['time']);
	unset($sfdisplay['posts']['date']);
	unset($sfdisplay['posts']['usertype']);
	unset($sfdisplay['posts']['rankdisplay']);
	unset($sfdisplay['posts']['location']);
	unset($sfdisplay['posts']['postcount']);
	unset($sfdisplay['posts']['permalink']);
	unset($sfdisplay['posts']['print']);
	unset($sfdisplay['posts']['sffbconnect']);
	unset($sfdisplay['posts']['sfmyspace']);
	unset($sfdisplay['posts']['sflinkedin']);
	sp_update_option('sfdisplay', $sfdisplay);

	sp_delete_option('sftwitter');
	sp_delete_option('sfstyle');
	sp_delete_option('sffloatclear');

	sp_delete_option('sfcheck');

	$sfprofile = sp_get_option('sfprofile');
	unset($sfprofile['require']);
	unset($sfprofile['include']);
	unset($sfprofile['display']);
	unset($sfprofile['system']);
	unset($sfprofile['label']);
	unset($sfprofile['displayinforum']);
	unset($sfprofile['forminforum']);
	sp_update_option('sfprofile', $sfprofile);

	$sfuploads = sp_get_option('sfuploads');
	unset($sfuploads['privatefolder']);
	sp_update_option('sfuploads', $sfuploads);

	$sflogin = sp_get_option('sflogin');
	unset($sflogin['sfloginskin']);
	unset($sflogin['sfshowavatar']);
	unset($sflogin['sflostpassurl']);
	sp_update_option('sflogin', $sflogin);

	$sfmemberopts = sp_get_option('sfmemberopts');
	unset($sfmembersopt['sfshowmemberlist']);
	unset($sfmembersopt['sflimitmemberlist']);
	unset($sfmembersopt['sfviewperm']);
	sp_update_option('sfmemberopts', $sfmemberopts);

	$sfadminsettings = sp_get_option('sfadminsettings');
	unset($sfadminsettings['sftools']);
	sp_update_option('sfadminsettings', $sfadminsettings);

	sp_delete_option('sfuseannounce');
	sp_delete_option('sfannouncecount');
	sp_delete_option('sfannouncehead');
	sp_delete_option('sfannounceauto');
	sp_delete_option('sfannouncetime');
	sp_delete_option('sfannouncetext');
	sp_delete_option('sfannouncelist');
}

# 5.0 convert permissions to authorizations
function sp_convert_perms_to_auths() {
	if (!spdb_column_exists(SFMEMBERS, 'auths')) {
		spdb_query('ALTER TABLE '.SFMEMBERS.' ADD (auths longtext)');
	}
	spdb_query('ALTER TABLE '.SFROLES.' CHANGE role_actions role_auths longtext');

	# populate with existing permissions
	spa_setup_auths();

	# now we need to convert existing roles
	$roles = spdb_table(SFROLES);
	if ($roles) {
		foreach ($roles as $role) {
			$actions = unserialize($role->role_auths);
			if ($actions) {
				$new_actions = spa_convert_action_to_auth($actions);
				spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($new_actions)."' WHERE role_id=$role->role_id");
			}
		}
	}
}

# sp_move_storage_locations()
function sp_move_storage_locations() {
	$sfconfig = array();
	$sfconfig = sp_get_option('sfconfig');

	$targets = array('policies', 'custom-icons', 'ranks', 'avatars', 'avatar-pool');
	foreach ($targets as $target) {
		if (file_exists(SF_STORE_DIR.'/'.$sfconfig[$target])) {
			if (@rename(SF_STORE_DIR.'/'.$sfconfig[$target], SF_STORE_DIR.'/sp-resources/'.$sfconfig[$target])) {
				$sfconfig[$target] = 'sp-resources/'.$sfconfig[$target];
			}
		}
	}

	sp_update_option('sfconfig', $sfconfig);
}

# 5.1 move ranks from sfmeta to new table and sfmembers
function sp_convert_ranks() {
	spdb_query('ALTER TABLE '.SFMEMBERS.' ADD (special_ranks text default NULL)');

    # convert special rank users to new column in sfmembers
	$special_rankings = sp_get_sfmeta('special_rank');
	if ($special_rankings) {
		foreach ($special_rankings as $rank) {
            if (empty($rank['meta_value']['users'])) continue;
            $users = $rank['meta_value']['users'];
            foreach ($users as $user) {
                $memberData = sp_get_member_item($user, 'special_ranks');
                $memberData[] = $rank['meta_key'];
                sp_update_member_item($user, 'special_ranks', $memberData);
            }
            unset($rank['meta_value']['users']);
            sp_update_sfmeta('special_rank', $rank['meta_key'], $rank['meta_value'], $rank['meta_id'], 1);
        }
   }
}

function sp_create_installed_tables() {
    # create an array for holding tables
    $tables = array();

    # core tables
	$tables[] = SFGROUPS;
	$tables[] = SFFORUMS;
	$tables[] = SFTOPICS;
	$tables[] = SFPOSTS;
	$tables[] = SFWAITING;
	$tables[] = SFTRACK;
	$tables[] = SFUSERGROUPS;
	$tables[] = SFPERMISSIONS;
	$tables[] = SFROLES;
	$tables[] = SFMEMBERS;
	$tables[] = SFMEMBERSHIPS;
	$tables[] = SFMETA;
	$tables[] = SFDEFPERMISSIONS;
	$tables[] = SFLOG;
	$tables[] = SFOPTIONS;
	$tables[] = SFERRORLOG;
	$tables[] = SFAUTHS;
	$tables[] = SFAUTHCATS;
	$tables[] = SFNOTICES;

    # add in known plugin tables that might exist
	if (!defined('SFMESSAGES'))	       define('SPPMMESSAGES', 		SF_PREFIX.'sfmessages');
	if (!defined('SPPMADVERSARIES'))   define('SPPMADVERSARIES', 	SF_PREFIX.'sfadversaries');
	if (!defined('SPPMATTACHMENTS'))   define('SPPMATTACHMENTS', 	SF_PREFIX.'sfpmattachments');
    if (!defined('SPDIGEST'))	       define('SPDIGEST', 	        SF_PREFIX.'sfdigest');
    if (!defined('SFMAILLOG'))	       define('SFMAILLOG',		    SF_PREFIX.'sfmaillog');
    if (!defined('SPPOLLS'))	       define('SPPOLLS', 	        SF_PREFIX.'sfpolls');
    if (!defined('SPPOLLSANSWERS'))	   define('SPPOLLSANSWERS',     SF_PREFIX.'sfpollsanswers');
    if (!defined('SPPOLLSVOTERS'))	   define('SPPOLLSVOTERS',      SF_PREFIX.'sfpollsvoters');
	if (!defined('SFPOSTRATINGS'))	   define('SFPOSTRATINGS',      SF_PREFIX.'sfpostratings');
	if (!defined('SFTAGS'))			   define('SFTAGS',		        SF_PREFIX.'sftags');
	if (!defined('SFTAGMETA'))		   define('SFTAGMETA',		    SF_PREFIX.'sftagmeta');
	if (!defined('SFLINKS'))		   define('SFLINKS',		    SF_PREFIX.'sflinks');

	$tables[] = SFMESSAGES;
	$tables[] = SPPMADVERSARIES;
	$tables[] = SPPMATTACHMENTS;
	$tables[] = SPDIGEST;
	$tables[] = SFMAILLOG;
	$tables[] = SPPOLLS;
	$tables[] = SPPOLLSANSWERS;
	$tables[] = SPPOLLSVOTERS;
	$tables[] = SFPOSTRATINGS;
	$tables[] = SFTAGS;
	$tables[] = SFTAGMETA;
	$tables[] = SFLINKS;

    # now save off installed tables
    sp_add_option('installed_tables', $tables);
}

/*
    For migrating from Simple:Press (WordPress forum plugin) 4.5 to 5.1.3 and cleaning up link shortening
    In 4.5, links were shortened on save
    In 5.1.3, links are shortened on display
*/
function sp_fix_shortened_links() {
    $postCount = spdb_count(SFPOSTS);
    $limit = 1000;
    for ($offset = 0; $offset < $postCount; $offset+= $limit) {
        $posts = spdb_select('set', 'SELECT post_id, post_content FROM '.SFPOSTS." ORDER BY post_id ASC LIMIT $offset, $limit");
        foreach ($posts as $post) {
            /*
                Matches would be:
                0 = The entire string
                1 = The link in the <a> tag
                2 = The rest of the parameters in the <a> tag (nofollow, target, etc.)
                3 = The part after the 5 periods

                So then we want to replace 0 with 1 for each result

                We are assuming that the links have 5 consecutive periods
            */

            $postContent = stripslashes($post->post_content);
            preg_match_all("/<a href=\"(.*?)\"(.*?)\.\.\.\.\.(.*?)<\/a>/is", $postContent, $linkMatches);
            if (!empty($linkMatches[0])) {
                foreach ($linkMatches[0] as $index => $stringMatch) {
                    $postContent = str_replace($stringMatch, $linkMatches[1][$index], $postContent);
                }
                $postContent = sp_esc_sql($postContent);
                spdb_query('UPDATE '.SFPOSTS." SET post_content = '$postContent' WHERE post_id = $post->post_id");
            }
        }
    }
}

# 5.2.3 - to clear out extraneous entries in install log
function sp_tidy_install_log() {
	$log = spdb_table(SFLOG, '', '', 'id DESC');
	$ver = 0;
	if ($log) {
		foreach ($log as $l) {
			if ($l->version != $ver) {
				$ver = $l->version;
			} else {
				spdb_query('DELETE FROM '.SFLOG.' WHERE id='.$l->id);
			}
		}
	}
}

# 5.3.2 - create the storage liocation 'cache'
function sp_create_cache_location() {
	# storage location
	$perms = fileperms(SF_STORE_DIR);
	$owners = stat(SF_STORE_DIR);
	if ($perms === false) $perms = 0755;

	$basepath = 'sp-resources';
	if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);

	if (file_exists(SF_STORE_DIR.'/'.$basepath)) {
		# Is the ownership correct?
		$newowners = stat(SF_STORE_DIR.'/'.$basepath);
		if ($newowners['uid']!=$owners['uid'] || $newowners['gid']!=$owners['gid']) {
			@chown(SF_STORE_DIR.'/'.$basepath, $owners['uid']);
			@chgrp(SF_STORE_DIR.'/'.$basepath, $owners['gid']);
		}
	}

	$newpath = SF_STORE_DIR.'/'.$basepath.'/forum-cache';
	if (!file_exists($newpath)) {
		$sfconfig = sp_get_option('sfconfig');
		$sfconfig['cache'] = $basepath.'/forum-cache';
		sp_update_option('sfconfig', $sfconfig);
		@mkdir($newpath, $perms);
	}
}

# 5.5.5 pre-create inspector array
function sp_create_inspectors() {
	$ins = array(
		'con_spVars' => 0,
		'con_spGlobals' => 0,
		'con_spThisUser' => 0,
		'con_spDevice' => 0,
		'gv_spGroupView' => 0,
		'gv_spThisGroup' => 0,
		'gv_spThisForum' => 0,
		'gv_spThisForumSubs' => 0,
		'fv_spForumView' => 0,
		'fv_spThisForum' => 0,
		'fv_spThisForumSubs' => 0,
		'fv_spThisSubForum' => 0,
		'fv_spThisTopic' => 0,
		'tv_spTopicView' => 0,
		'tv_spThisTopic' => 0,
		'tv_spThisPost' => 0,
		'tv_spThisPostUser' => 0,
		'mv_spMembersList' => 0,
		'mv_spThisMemberGroup' => 0,
		'mv_spThisMember' => 0,
		'tlv_spTopicListView' => 0,
		'tlv_spThisListTopic' => 0,
		'plv_spPostListView' => 0,
		'plv_spThisListPost' => 0,
		'pro_spProfileUser' => 0,
		'q_spGroupView' => 0,
		'q_spGroupViewStats' => 0,
		'q_spForumView' => 0,
		'q_spForumViewStats' => 0,
		'q_spTopicView' => 0,
		'q_spMembersView' => 0,
		'q_spTopicListView' => 0,
		'q_spTopicListViewNew' => 0,
		'q_spTopicListViewFirst' => 0,
		'q_spPostListView' => 0,
		'q_spSearchView' => 0
		);
	sp_update_option('spInspect', $ins);
}

# 5.5.7 - update table collation n line with WP
function sp_convert_table_to_utf8mb4( $table ) {
	global $wpdb;

	$results = $wpdb->get_results( "SHOW FULL COLUMNS FROM `$table`" );
	if ( ! $results ) {
		return false;
	}

	foreach ( $results as $column ) {
		if ( $column->Collation ) {
			list( $charset ) = explode( '_', $column->Collation );
			$charset = strtolower( $charset );
			if ( 'utf8' !== $charset && 'utf8mb4' !== $charset ) {
				# Don't upgrade tables that have non-utf8 columns.
				return false;
			}
		}
	}

	$table_details = $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" );
	if ( ! $table_details ) {
		return false;
	}

	list( $table_charset ) = explode( '_', $table_details->Collation );
	$table_charset = strtolower( $table_charset );
	if ( 'utf8mb4' === $table_charset ) {
		return true;
	}

	return $wpdb->query( "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
}

# 5.5.9 - create new forum image folder
function sp_create_forum_image_location() {
	# storage location
	$perms = fileperms(SF_STORE_DIR);
	$owners = stat(SF_STORE_DIR);
	if ($perms === false) $perms = 0755;

	$basepath = 'sp-resources';
	if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);

	if (file_exists(SF_STORE_DIR.'/'.$basepath)) {
		# Is the ownership correct?
		$newowners = stat(SF_STORE_DIR.'/'.$basepath);
		if ($newowners['uid']!=$owners['uid'] || $newowners['gid']!=$owners['gid']) {
			@chown(SF_STORE_DIR.'/'.$basepath, $owners['uid']);
			@chgrp(SF_STORE_DIR.'/'.$basepath, $owners['gid']);
		}
	}

	$newpath = SF_STORE_DIR.'/'.$basepath.'/forum-feature-images';
	if (!file_exists($newpath)) {
		$sfconfig = sp_get_option('sfconfig');
		$sfconfig['forum-images'] = $basepath.'/forum-feature-images';
		sp_update_option('sfconfig', $sfconfig);
		@mkdir($newpath, $perms);
	}
}


# Silent Upgrade ------------
function sp_silent_upgrade() {
	global $wpdb;

    # build the response
    $response['status'] = 'success';
    $response['type'] = 'upgrade';
    $response['section'] = SPBUILD;
    $response['error'] = '';
    $response['response'] = 'silent';

    # save as log meta data if table exists! (Need to check if installed yet sadly)
	$go = $wpdb->get_var("SHOW TABLES LIKE '".SFLOGMETA."'");
	if ($go) {
		$sql = '
			INSERT INTO '.SFLOGMETA." (version, log_data)
			VALUES (
			'".SPVERSION."',
			'".serialize($response)."')";
		spdb_query($sql);
	}

	# and if this is the last update in build finish off...
	# let plugins know
	do_action('sph_upgrade_done', SPBUILD);

	sp_log_event(SPRELEASE, SPVERSION, SPBUILD, 0);

	# and some final cleanuop tasks
	sp_reset_auths();

	sp_clear_combined_css('all');
	sp_clear_combined_css('mobile');
	sp_clear_combined_css('tablet');

	sp_clear_combined_scripts('desktop');
	sp_clear_combined_scripts('mobile');
	sp_clear_combined_scripts('tablet');

	sp_flush_cache('all');

	sp_reset_member_plugindata();

	sp_update_option('sfforceupgrade', false);
}

?>