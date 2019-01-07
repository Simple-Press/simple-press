<?php
/*
Simple:Press
DESC:
$LastChangedDate: 2018-08-24 21:05:16 -0500 (Fri, 24 Aug 2018) $
$Rev: 15719 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE - This file loads at core level - all page loads
#	SP Base User handling Rourtines
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_load_current_user()
#
# Version: 5.0
# Filter Call
# Create the spThisUser object (the current user)
# ------------------------------------------------------------------
function sp_load_current_user() {
    global $current_user, $spThisUser, $spGuestCookie;

	if (empty($current_user)) $current_user = wp_get_current_user();
	$spThisUser = sp_get_user($current_user->ID, true);

	# check for a cookie if a guest
	$spGuestCookie = new stdClass();
	$spGuestCookie->guest_name = '';
	$spGuestCookie->guest_email = '';
	$spGuestCookie->display_name = '';
	if ($spThisUser->guest && empty($spThisUser->offmember)) {
		# so no record of them being a current member
		$sfguests = sp_get_option('sfguests');
		if ($sfguests['storecookie']) {
			if (isset($_COOKIE['guestname_'.COOKIEHASH])) $spGuestCookie->guest_name = sp_filter_name_display($_COOKIE['guestname_'.COOKIEHASH]);
			if (isset($_COOKIE['guestemail_'.COOKIEHASH])) $spGuestCookie->guest_email = sp_filter_email_display($_COOKIE['guestemail_'.COOKIEHASH]);
			$spGuestCookie->display_name = $spGuestCookie->guest_name;
		}
	}
}

# ------------------------------------------------------------------
# sp_create_member_data()
#
# Version: 5.0
# Filter Call
# On user registration sets up the new 'members' data row
#	$userid:		Passed in to filter
#	$install:		Set to true if initial installation
# ------------------------------------------------------------------
function sp_create_member_data($userid, $install=false) {
	global $current_user;

	if (!$userid) return;

	if (!$install) {
	    # see if member has already been created since wp multisite can fire both user creation hooks in some cases
		$user = spdb_table(SFMEMBERS, "user_id=$userid", 'row');
    	if ($user) return;
	}

	# Grab the data we need
	$user = spdb_table(SFUSERS, "ID=$userid", 'row');

	# Display Name validation
	if (!$install) {
		$display_name = '';
		$sfprofile = sp_get_option('sfprofile');

		if ($sfprofile['nameformat']) {
			$display_name = $user->display_name;
		} else {
			$first_name = get_user_meta($userid, 'first_name', true);
			$last_name  = get_user_meta($userid, 'last_name', true);
			switch ($sfprofile['fixeddisplayformat']) {
				default:
				case '0':
					$display_name = $user->display_name;
					break;
				case '1':
					$display_name = $user->user_login;
					break;
				case '2':
					$display_name = $first_name;
					break;
				case '3':
					$display_name = $last_name;
					break;
				case '4':
					$display_name = $first_name.' '.$last_name;
					break;
				case '5':
					$display_name = $last_name.', '.$first_name;
					break;
				case '6':
					$display_name = $first_name[0].' '.$last_name;
					break;
				case '7':
					$display_name = $first_name.' '.$last_name[0];
					break;
				case '8':
					$display_name = $first_name[0].$last_name[0];
					break;
			}
		}
	} else {
		$display_name = $user->display_name;
	}

	# If the display name is empty for any reason, default to the username
	if (empty($display_name)) $display_name = $user->user_login;

	$display_name = apply_filters('sph_set_display_name', $display_name, $userid);
	$display_name = sp_filter_name_save($display_name);

	# now ensure it is unique
	$display_name = sp_unique_display_name($display_name, $display_name);

	if (!$install) {
	    # do we need to force user to change password?
		if ($sfprofile['forcepw']) add_user_meta($userid, 'sp_change_pw', true, true);
	}

	$admin = 0;
	$moderator = 0;
	$avatar = 'a:1:{s:8:"uploaded";s:0:"";}';
	$signature = '';
	$posts = -1;
	$lastvisit = current_time('mysql');
	$checktime = current_time('mysql');
	$admin_options = '';
	$newposts = 'a:3:{s:6:"topics";a:0:{}s:6:"forums";a:0:{}s:4:"post";a:0:{}}';

	$useropts = array();
	$useropts['hidestatus'] = 0;
	$useropts['timezone'] = get_option('gmt_offset');
	if (empty($useropts['timezone'])) $useropts['timezone'] = 0;
	$tz = get_option('timezone_string');
	if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
	$useropts['timezone_string'] = $tz;
	$useropts['editor'] = 1;
	$useropts['namesync'] = 1;

    # unread posts
    if (!$install) {
		$sfcontrols = sp_get_option('sfcontrols');
		$useropts['unreadposts'] = $sfcontrols['sfdefunreadposts'];
	} else {
    	$useropts['unreadposts'] = 50;
	}

	$user_options = serialize($useropts);

	# generate feedkey
	$feedkey = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );

	# save initial record
	$sql = 'INSERT INTO '.SFMEMBERS."
		(user_id, display_name, admin, moderator, avatar, signature, posts, lastvisit, checktime, newposts, admin_options, user_options, feedkey)
		VALUES
		($userid, '$display_name', $admin, $moderator, '$avatar', '$signature', $posts, '$lastvisit', '$checktime', '$newposts', '$admin_options', '$user_options', '$feedkey')";
	spdb_query($sql);

	if (!$install) {
		# update stats status and recent member list
		if (sp_user_stats_status($userid) == 0) {
			sp_push_newuser($userid, $display_name);
		}
	} else {
		if ($current_user->ID != $userid) {
			$ug = spdb_table(SFUSERGROUPS, "usergroup_name='Members'", 'usergroup_id');
			if (!$ug) $ug = 2;
			$sql = 'INSERT INTO '.SFMEMBERSHIPS.' (user_id, usergroup_id) ';
			$sql.= "VALUES ($userid, $ug);";
			$success = spdb_query($sql);
		}
	}

    do_action('sph_member_created', $userid);
}

# ------------------------------------------------------------------
# sp_update_member_data()
#
# Version: 5.0
# Filter Call
# On user wp profile updates, check if any spf stuff needs updating
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sp_update_member_data($userid) {
	if (!$userid) return '';

	# are we syncing display names between WP and SPF?
	$member = sp_get_member_row($userid);
    $options = unserialize($member['user_options']);
	if ($options['namesync']) {
		$display_name = sp_filter_name_save(spdb_table(SFUSERS, "ID=$userid", 'display_name'));
		sp_update_member_item($userid, 'display_name', $display_name);

        # update recent members list
		sp_update_newuser_name($member['display_name'], $display_name);
	}
}

function sp_set_role_to_ug($userid, $role, $old_roles = '') {
	# remove any mapped memberships based on old roles
	if (!empty($old_roles)) {
		foreach ($old_roles as $old_role) {
			# remove any mapped roles
			sp_remove_role_to_ug($userid, $old_role);
		}
	}

	# check for mapped membership for this role
	sp_add_role_to_ug($userid, $role);
}

function sp_add_role_to_ug($userid, $role) {
	# see if their is a mapped membership for this role
	$ug = sp_get_sfmeta('default usergroup', $role);
	if (empty($ug)) $ug = sp_get_sfmeta('default usergroup', 'sfmembers');
	sp_add_membership($ug[0]['meta_value'], $userid);
}

function sp_remove_role_to_ug($userid, $role) {
	# see if their is a mapped membership for this role
	$ug = sp_get_sfmeta('default usergroup', $role);
	if (!empty($ug)) sp_remove_membership($ug[0]['meta_value'], $userid);
}

# ------------------------------------------------------------------
# sp_delete_member_data()
#
# Version: 5.0
# Filter Call
# On user deletion remove 'members' data row
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sp_delete_member_data($userid, $blog_id='', $delete_option='spguest', $reassign=0) {
	if (!$userid) return '';

    global $wpdb;

    # if removing user from network site, make sure sp installed on that network site
    if (!empty($blog_id)) {
    	$optionstable = $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."sfoptions'");
        if (empty($optionstable)) return;
    }

    # let plugins clean up from member removal first
    do_action('sph_member_deleted', $userid);

    # remove member from core
    $option = (isset($_POST['sp_delete_option'])) ? sp_esc_str($_POST['sp_delete_option']) : $delete_option;
    switch ($option) {
        case 'spreassign':
            $newuser = (isset($_POST['sp_reassign_user'])) ? sp_esc_int($_POST['sp_reassign_user']) : $reassign;

        	# Set poster ID to the new user id
        	$wpdb->query('UPDATE '.$wpdb->prefix."sfposts SET user_id=$newuser WHERE user_id=$userid");
        	$wpdb->query('UPDATE '.$wpdb->prefix."sftopics SET user_id=$newuser WHERE user_id=$userid");
            break;

        case 'spdelete':
            # need to get topics for user posts to see if topic will be empty after deleting posts
			$topics = spdb_select('set', 'SELECT DISTINCT topic_id, forum_id FROM '.SFPOSTS." WHERE user_id=$userid");

            # delete all the user posts
            spdb_query('DELETE FROM '.SFPOSTS." WHERE user_id=$userid");

            # if any topics are now empty of posts, lets remove the topic and update the forum
            if (!empty($topics)) {
                foreach ($topics as $topic) {
                    $posts = spdb_table(SFPOSTS, "topic_id=$topic->topic_id");
                    if (empty($posts)) {
                        spdb_query('DELETE FROM '.SFTOPICS." WHERE topic_id=$topic->topic_id");
                    } else {
                        sp_build_post_index($topic->topic_id);
                    }
                    sp_build_forum_index($topic->forum_id);
                }
            }
            break;

        case 'spguest':
        default:
        	# get users email address
        	$user_email = sp_filter_email_save($wpdb->get_var('SELECT user_email from '.$wpdb->prefix."users WHERE ID=$userid"));

        	# get the users display name from members table
        	$display_name = $wpdb->get_var('SELECT display_name FROM '.$wpdb->prefix."sfmembers WHERE user_id = $userid");
        	$display_name = sp_filter_name_save(maybe_unserialize($display_name));

        	# Set user name and email to guest name and meail in all of their posts
        	$wpdb->query('UPDATE '.$wpdb->prefix."sfposts SET user_id=0, guest_name='$display_name', guest_email='$user_email' WHERE user_id=$userid");
        	$wpdb->query('UPDATE '.$wpdb->prefix."sftopics SET user_id=0 WHERE user_id=$userid");
    }

    # flush and rebuild topic cache
	sp_rebuild_topic_cache();

	# remove from various core tables
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sfmembers WHERE user_id=$userid");
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sfmemberships WHERE user_id=$userid");
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sfspecialranks WHERE user_id=$userid");
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sftrack WHERE trackuserid=$userid");
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sfnotices WHERE user_id=$userid");
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sfuseractivity WHERE user_id=$userid");
	$wpdb->query('DELETE FROM '.$wpdb->prefix."sfwaiting WHERE user_id=$userid");

	# eemove from recent members list if present
	sp_remove_newuser($userid);

    # check if forum moderator list needs updating
    sp_update_forum_moderators();
}

# ------------------------------------------------------------------
# sp_add_membership()
#
# Version: 5.0
# Adds the specified user to the specified user group
#	$usergroup_id:		user group to which to add the user
#	$userid:			user to be added
# ------------------------------------------------------------------
function sp_add_membership($usergroup_id, $user_id) {
	# make sure we have valid membership to set
	if (empty($usergroup_id) || empty($user_id)) return false;

    # dont allow admins to be added to user groups
    if (sp_is_forum_admin($user_id)) return false;
	$success = false;

	# if only one membership allowed, remove all current memberships
	$sfmemberopts = sp_get_option('sfmemberopts');
	if (isset($sfmemberopts['sfsinglemembership']) && $sfmemberopts['sfsinglemembership']) spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$user_id");

	# dont add membership if it already exists
	$check = sp_check_membership($usergroup_id, $user_id);
	if (empty($check)) {
		$sql = 'INSERT INTO '.SFMEMBERSHIPS.' (user_id, usergroup_id) ';
		$sql.= "VALUES ('$user_id', '$usergroup_id');";
		$success = spdb_query($sql);

        # reset auths and memberships for added user
        sp_reset_memberships($user_id);
        sp_reset_auths($user_id);

	    sp_update_member_moderator_flag($user_id);
	}
	return $success;
}

# ------------------------------------------------------------------
# sp_remove_membership()
#
# Version: 5.0
# Removes the specified user from the specified user group
#	$usergroup_id:		user group to which to add the user
#	$userid:			user to be added
# ------------------------------------------------------------------
function sp_remove_membership($usergroup_id, $user_id) {
    spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$user_id AND usergroup_id=$usergroup_id");

    # reset auths and memberships for added user
    sp_reset_memberships($user_id);
    sp_reset_auths($user_id);

    sp_update_member_moderator_flag($user_id);

	return true;
}

# Version: 5.0
function sp_check_membership($usergroup_id, $user_id) {
	if (!$usergroup_id || !$user_id) return '';
	return spdb_table(SFMEMBERSHIPS, "user_id=$user_id AND usergroup_id=$usergroup_id", '', '', '', ARRAY_A);
}

# Version: 5.0
function sp_reset_memberships($userid='') {
    # reset all the members memberships
    $where = '';
    if (!empty($userid)) $where = ' WHERE user_id='.$userid;

	spdb_query('UPDATE '.SFMEMBERS." SET memberships=''".$where);

    # reset guest auths if global update
    if (empty($userid)) sp_update_option('sf_guest_memberships', '');
}

# ------------------------------------------------------------------
# sp_update_member_moderator_flag()
#
# Version: 5.0
# checks an updates moderator flag for specified user
#	$userid:		User to lookup
# ------------------------------------------------------------------
function sp_update_member_moderator_flag($userid) {
	$ugs = sp_get_user_memberships($userid);
	if ($ugs) {
		foreach ($ugs as $ug) {
			$mod = spdb_table(SFUSERGROUPS, "usergroup_id={$ug['usergroup_id']}", 'usergroup_is_moderator');
			if ($mod) {
				sp_update_member_item($userid, 'moderator', 1);

                # see if our forum moderator list changed
                sp_update_forum_moderators();
				return;
			}
		}
	}

	# not a moderator if we get here
	sp_update_member_item($userid, 'moderator', 0);

}

# ------------------------------------------------------------------
# sp_update_forum_moderators()
#
# Version: 5.0
# updates the list of moderators for each forum
#	$forumid:		specific forum to update; otherwise does all
# ------------------------------------------------------------------
function sp_update_forum_moderators($forumid='') {
    if (empty($forumid)) {
        $forums = spdb_select('col', 'SELECT forum_id FROM '.SFFORUMS, ARRAY_A);
    } else {
        $forums = (array) $forumid;
    }
    if (empty($forums)) return;

    # udpate moderators list for each forum
    $mods = array();
    foreach ($forums as $forum) {
    	$sql = 'SELECT DISTINCT '.SFMEMBERSHIPS.'.user_id, display_name
    			FROM '.SFMEMBERSHIPS.'
    			JOIN '.SFUSERGROUPS.' ON '.SFUSERGROUPS.'.usergroup_id = '.SFMEMBERSHIPS.'.usergroup_id
    			JOIN '.SFPERMISSIONS.' ON '.SFPERMISSIONS.".forum_id = $forum AND ".SFMEMBERSHIPS.'.usergroup_id = '.SFUSERGROUPS.'.usergroup_id
    			JOIN '.SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id
    			WHERE usergroup_is_moderator=1';
        $mods[$forum] = spdb_select('set', $sql, ARRAY_A);
    }

    sp_add_sfmeta('forum_moderators', 'users', $mods, 1);
}

# Version: 5.0
function sp_get_user_memberships($user_id) {
	if (!$user_id) return '';

	$sql = 'SELECT '.SFMEMBERSHIPS.'.usergroup_id, usergroup_name, usergroup_desc, usergroup_badge, usergroup_join, hide_stats
			FROM '.SFMEMBERSHIPS.'
			JOIN '.SFUSERGROUPS.' ON '.SFUSERGROUPS.'.usergroup_id = '.SFMEMBERSHIPS.".usergroup_id
			WHERE user_id=$user_id";
	return spdb_select('set', $sql, ARRAY_A);
}

# ------------------------------------------------------------------
# sp_get_forum_memberships()
#
# Version: 5.0
# Returns an indexed array of all forum ids the current user is
# allowed to view. Note: This includes admins.
# Uses the spThisUser object so is only valid for current user
# ------------------------------------------------------------------
function sp_get_forum_memberships() {
	global $spThisUser;
	if ($spThisUser->admin) {
		$sql = 'SELECT forum_id FROM '.SFFORUMS;
	} else if ($spThisUser->guest) {
		$value = sp_get_sfmeta('default usergroup', 'sfguests');
		$sql = 'SELECT forum_id FROM '.SFPERMISSIONS." WHERE usergroup_id={$value[0]['meta_value']}";
	} else {
		$sql = 'SELECT forum_id
				FROM '.SFPERMISSIONS.'
				JOIN '.SFMEMBERSHIPS.' ON '.SFPERMISSIONS.'.usergroup_id = '.SFMEMBERSHIPS.'.usergroup_id
				WHERE user_id='.$spThisUser->ID;
	}
	$forums = spdb_select('set', $sql);
	$fids = array();
	if ($forums) {
		foreach ($forums as $thisForum) {
			if (sp_get_auth('view_forum', $thisForum->forum_id) ||
                sp_get_auth('view_forum_lists', $thisForum->forum_id) ||
                sp_get_auth('view_forum_topic_lists', $thisForum->forum_id)) {
                $fids[] = $thisForum->forum_id;
            }
		}
	}
	return $fids;
}

# ------------------------------------------------------------------
# sp_push_newuser()
#
# Version: 5.0
# Adds new user stats new user list
#	$name:		new users display name
# ------------------------------------------------------------------
function sp_push_newuser($id, $name) {
	$spControls = sp_get_option('sfcontrols');
	$num = $spControls['shownewcount'];
	if (empty($num)) $num = 0;

	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		# is this name already listed?
		foreach ($newuserlist as $user) {
			if ($user['name'] == $name) return;
		}

		# is the array full? if so pop one off
		$ccount = count($newuserlist);
		while ($ccount > ($num-1)) {
			array_pop($newuserlist);
			$ccount--;
		}

		# add new user
		array_unshift($newuserlist, array('id' => sp_esc_sql($id), 'name' => sp_esc_sql($name)));
	} else {
		# first name nto the emoty array
		$newuserlist[0]['id'] = sp_esc_sql($id);
		$newuserlist[0]['name'] = sp_esc_sql($name);
	}
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_remove_newuser()
#
# Version: 5.0
# Removes new user from new user list
#	$id:		new users id
# ------------------------------------------------------------------
function sp_remove_newuser($id) {
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		# remove the user if present
		foreach ($newuserlist as $index => $user) {
			if ($user['id'] == $id) unset($newuserlist[$index]);
		}
		$newuserlist = array_values($newuserlist);
	}
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_rebuild_newuser()
#
# Version: 5.7.3
# Rebuilds the new user list
# ------------------------------------------------------------------
function sp_rebuild_newuser() {
	# how many to show...
	$spControls = sp_get_option('sfcontrols');
	$num = $spControls['shownewcount'];
	if (empty($num)) $num = 10;

	# select the right number...
	$spdb = new spdbComplex;
		$spdb->table		= SFMEMBERS;
		$spdb->distinctrow	= true;
		$spdb->fields		= SFMEMBERS.'.user_id AS id, display_name AS name';
		$spdb->join			= array(SFMEMBERSHIPS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id',
									SFUSERGROUPS.' ON '.SFMEMBERSHIPS.'.usergroup_id = '.SFUSERGROUPS.'.usergroup_id');
		$spdb->where		= SFUSERGROUPS.'.hide_stats = 0';
		$spdb->orderby		= SFMEMBERS.'.user_id DESC LIMIT '.$num;
	$list = $spdb->select('set', ARRAY_A);

	# save the resultant array
	sp_update_option('spRecentMembers', $list);
}

# ------------------------------------------------------------------
# sp_update_newuser_name()
#
# Version: 5.0
# Updates display name of recent members if profile updated
#	$oldname:		users old name
#	$newname:		users new name
# ------------------------------------------------------------------
function sp_update_newuser_name($oldname, $newname) {
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		for ($x = 0; $x < count($newuserlist); $x++) {
			if ($newuserlist[$x]['name'] == $oldname) $newuserlist[$x]['name'] = $newname;
		}
	}
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_update_recent_members()
#
# Version: 5.3
# Updates display name of recent members list if profile option display name format changed
# -----------------------------------------------------------------------------------------
function sp_update_recent_members () {
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		for ($x = 0; $x < count($newuserlist); $x++) {
            $newuserlist[$x]['name'] = sp_get_member_item($newuserlist[$x]['id'], 'display_name');
        }
    }
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_check_unlogged_user()
#
# Version: 5.0
# checks if 'guest' is a user not logged in and returns their name
# ------------------------------------------------------------------
function sp_check_unlogged_user() {
	if (is_user_logged_in() == true) return;
	$sfmemberopts = sp_get_option('sfmemberopts');
	if (isset($_COOKIE['sforum_'.COOKIEHASH]) && $sfmemberopts['sfcheckformember']) {
		# Yes it is - a user not logged in
		$username = $_COOKIE['sforum_'.COOKIEHASH];
		return $username;
	}
	return '';
}

# Version: 5.0
function sp_user_visible_forums($view='forum-title') {
	global $spThisUser, $spGlobals;

	if (empty($spThisUser->auths)) return '';

	$forum_ids = array();
	foreach ($spThisUser->auths as $forum => $forum_auth) {
		if ($forum != 'global' && sp_can_view($forum, $view)) $forum_ids[] = $forum;
	}
	return $forum_ids;
}

# ------------------------------------------------------------------
# sp_validate_user()
#
# Version: 5.2
# checks account name user is attempting to regsiter against a blacklist of unallowed account names
# ------------------------------------------------------------------
function sp_validate_registration($errors, $sanitized_user_login, $user_email) {
    $blockedAccounts = sp_get_option('account-name');
    if (!empty($blockedAccounts)) {
        $names = explode(',', $blockedAccounts);
        foreach ($names as $name) {
            if (strtolower(trim($name)) == strtolower($sanitized_user_login)) {
                $errors->add('login_blacklisted', '<strong>'.sp_text('ERROR').'</strong>: '.sp_text('The account name you have chosen is not allowed on this site'));
                break;
            }
        }
    }
    return $errors;
}

# ------------------------------------------------------------------
# sp_validate_display_name()
#
# Version: 5.2
# checks account name user is attempting to regsiter against a blacklist of unallowed account names
# ------------------------------------------------------------------
function sp_validate_display_name($errors, $update, $user) {
    $blockedDisplay = sp_get_option('display-name');
    if (!empty($blockedDisplay)) {
        $names = explode(',', $blockedDisplay);
        foreach ($names as $name) {
            if (strtolower(trim($name)) == strtolower($user->display_name)) {
                $errors->add('display_name_blacklisted', '<strong>'.sp_text('ERROR').'</strong>: '.sp_text('The display name you have chosen is not allowed on this site'));
                break;
            }
        }
    }
    return $errors;
}

# ------------------------------------------------------------------
# sp_unique_display_name()
#
# Version: 5.3.2
# checks display name is unique and if not adds a number on the end
# ------------------------------------------------------------------
function sp_unique_display_name($startname, $modname, $suffix=1) {
	$check = true;
	while ($check) {
		$check = spdb_table(SFMEMBERS, "display_name='$modname'");
		if ($check) {
			$modname = $startname.'_'.$suffix;
			$suffix++;
		}
	}
	return $modname;
}

# ------------------------------------------------------------------
# sp_delete_user_form()
#
# Version: 5.5.2
# Adds user deletion option when user deleted from wp
# ------------------------------------------------------------------
function sp_delete_user_form($user, $userids) {
?>
	<fieldset>
    <?php
        foreach ($userids as $id) {
            if (sp_is_forum_admin($id)) {
                echo '<div class="error"><p>'.spa_text('Warning:  You are about to delete a Simple:Press Admin user. This could have consequences for administration of your forum. Please ensure you really want to do this.').'</p></div>';
                break;
            }
        }
    ?>
    <p><legend><?php echo spa_text( 'What should be done with the user(s) forum posts?'); ?></legend></p>
	<ul style="list-style:none;">
		<li><label><input type="radio" id="sp_guest_option" name="sp_delete_option" value="spguest" checked="checked" />
		<?php echo spa_text('Change all posts to be from a guest.'); ?></label></li>
		<li><label><input type="radio" id="sp_delete_option" name="sp_delete_option" value="spdelete" />
		<?php echo spa_text('Delete all the posts (warning - may take time and resources if lots of posts).'); ?></label></li>
		<li><input type="radio" id="sp_reassign_option" name="sp_delete_option" value="spreassign" />
		<?php echo '<label for="sp_reassign_option">'.spa_text('Reassign all the posts to:').'</label> ';
		wp_dropdown_users(array('name' => 'sp_reassign_user', 'exclude' => array($user->ID))); ?></li>
	</ul></fieldset>
<?php
}

# ------------------------------------------------------------------
# sp_user_stats_status()
#
# Version: 5.6.6
# Gets whether a member should be seen in stats
# Saves user memberships at the same time if needed - may as well
# ------------------------------------------------------------------
function sp_user_stats_status($userid, $memberships='') {
	if (empty($memberships)) {
		$memberships = sp_get_user_memberships($userid);
		sp_update_member_item($userid, 'memberships', wp_slash($memberships));
	}
	if ($memberships) {
		$hide = 1;
		foreach ($memberships as $membership) {
			if ($membership['hide_stats'] == 0) $hide = 0;
		}
	} else {
		$hide = 0;
	}
	return $hide;
}

?>