<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-03-07 05:48:39 -0600 (Tue, 07 Mar 2017) $
$Rev: 15268 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	GLOBAL Database Module
#	Statistics Database Routines
#
#	sp_track_online()
#	sp_get_members_online()
#	sp_is_online()
#	sp_get_stats_counts()
#	sp_get_post_stats()
#	sp_guests_browsing()
#	sp_track_logout()
#	sp_set_last_visited()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_track_online()
#
# Tracks online users. Creates their new-post-list when they first
# appear through to saving their last visit date when they go again
# (either logout or time out - 20 minutes)
# ------------------------------------------------------------------
function sp_track_online() {
	global $spThisUser, $spVars, $spDevice;

	# dont track feed views
	if ($spVars['pageview'] == 'feed') return;

	# Update tracking
	if ($spThisUser->member) {
		# it's a member
		$trackUserId = $spThisUser->ID;
		$trackName = $spThisUser->user_login;
	} else {
		# Unknown guest
		$trackUserId = 0;
		$trackName = $spThisUser->ip;
	}
	$track = spdb_table(SFTRACK, "trackname='$trackName'", 'row');

	$now = current_time('mysql');

	$forumId = (isset($spVars['forumid'])) ? $spVars['forumid'] : 0;
	$topicId = (isset($spVars['topicid'])) ? $spVars['topicid'] : 0;
	$pageview = $spVars['pageview'];

	# handle sneak peek
	if (!empty($topicId)) {
		if (!sp_get_auth('view_forum', $forumId)) return;
	} else if (!empty($forumId)) {
		if (!sp_can_view($forumId, 'topic-title')) return;
	}

	# update or start tracking
	if ($track) {
		# they are still here
		spdb_query("UPDATE ".SFTRACK."
				   SET trackdate='".$now."', forum_id=".$forumId.",	 topic_id=".$topicId.", pageview='$pageview'
				   WHERE id=".$track->id);
		if ($spThisUser->member) sp_update_users_newposts();
		$spThisUser->trackid = $track->id;
		$spThisUser->session_first_visit = false;
		$spThisUser->notification = $track->notification;
	} else {
		# newly arrived
		# set deice being used
		$device = 'D';
		switch($spDevice) {
			case 'mobile':
				$device = 'M';
				break;
			case 'tablet':
				$device = 'T';
				break;
			case 'desktop':
				$device = 'D';
				break;
		}

		# display classes
		$display = 'spType-'.$spThisUser->usertype;
		if (!empty($spThisUser->rank)) $display.= ' spRank-'.sp_create_slug($spThisUser->rank[0]['name'], false);
		if (!empty($spThisUser->special_rank)) {
			foreach ($spThisUser->special_rank as $rank) {
				$display.= ' spSpecialRank-'.sp_create_slug($rank['name'], false);
			}
		}
		if (!empty($spThisUser->memberships)) {
			foreach ($spThisUser->memberships as $membership) {
				$display.= ' spUsergroup-'.sp_create_slug($membership['usergroup_name'], false);
			}
		}

		spdb_query("INSERT INTO ".SFTRACK."
				   (trackuserid, trackname, forum_id, topic_id, trackdate, pageview, device, display) VALUES
				   ($trackUserId, '$trackName', $forumId, $topicId, '$now', '$pageview', '$device', '$display')");
		$spThisUser->trackid = $spVars['insertid'];
		$spThisUser->session_first_visit = true;
		if ($spThisUser->member) sp_update_users_newposts();
	}

	# Check for expired tracking - some may have left the scene
	$splogin = sp_get_option('sflogin');
	$timeout = $splogin['sptimeout'];
	if (!$timeout) $timeout = 20;
	$expired = spdb_table(SFTRACK, "trackdate < DATE_SUB('$now', INTERVAL $timeout MINUTE)");
	if ($expired) {
		# if any Members expired - update user meta
		foreach ($expired as $expire) {
			if ($expire->trackuserid > 0) sp_set_last_visited($expire->trackuserid);
		}

		# finally delete them
		spdb_query("DELETE FROM ".SFTRACK."
					WHERE trackdate < DATE_SUB('$now', INTERVAL $timeout MINUTE)");
	}
}

# ------------------------------------------------------------------
# sp_get_track_id()
#
# Retrieves the track id for the current user. This function should
# only really be called from the sp_forum_ajax_support() function
# which should only ever be called from within the UI where we know
# there is a bona-fide user...
# ------------------------------------------------------------------
function sp_get_track_id() {
	global $spThisUser;

	# see if track id already set up
	if (isset($spThisUser->trackid) && $spThisUser->trackid >= 0) return ; # user class inits to -1

	# not set up, so grab the info
	if ($spThisUser->member) {
		# it's a member
		$trackUserId = $spThisUser->ID;
		$trackName = $spThisUser->user_login;
	} else {
		# Unknown guest
		$trackUserId = 0;
		$trackName = $spThisUser->ip;
	}
	$track = spdb_table(SFTRACK, "trackname='$trackName'", 'row');
	if ($track) $spThisUser->trackid = $track->id;
}

# ------------------------------------------------------------------
# sp_get_members_online()
#
# Returns list of members currently tagged as online
# ------------------------------------------------------------------
function sp_get_members_online()
{
	return spdb_select('set', "
			SELECT trackuserid, display_name, user_options, forum_id, topic_id, pageview, display FROM ".SFTRACK."
			JOIN ".SFMEMBERS." ON ".SFTRACK.".trackuserid = ".SFMEMBERS.".user_id
			ORDER BY trackuserid");
}

# ------------------------------------------------------------------
# sp_is_online()
#
# Returns true if member is currently tagged as online
# ------------------------------------------------------------------
function sp_is_online($userid) {
	global $session_online;

	if (!$userid) return false;
	if (!isset($session_online)) $session_online = spdb_select('col', "SELECT trackuserid FROM ".SFTRACK);
	if (in_array($userid, $session_online)) return true;

	return false;
}

# ------------------------------------------------------------------
# sp_get_stats_counts()
#
# Returns stats on group/forum/topic/post count
# ------------------------------------------------------------------
function sp_get_stats_counts() {
	$cnt = new stdClass();
	$cnt->groups = 0;
	$cnt->forums = 0;
	$cnt->topics = 0;
	$cnt->posts = 0;

	$groupid = '';

	$forums = spdb_table(SFFORUMS, '', '', 'group_id');
	if ($forums) {
		foreach ($forums as $forum) {
			if ($forum->group_id != $groupid) {
				$groupid = $forum->group_id;
				$cnt->groups++;
			}
			$cnt->forums++;
			$cnt->topics+= $forum->topic_count;
			$cnt->posts+= $forum->post_count;
		}
	}
	return $cnt;
}

# ------------------------------------------------------------------
# sp_get_membership_stats()
#
# Returns stats on posts (admins/moderators and members and updates
# the guest count
# ------------------------------------------------------------------
function sp_get_membership_stats() {
	$stats = array();

	$opts = sp_get_option('sfcontrols');

	$spdb = new spdbComplex;
	$spdb->table	= SFMEMBERS;
	$spdb->fields	= 'count(*) as count';
	$spdb->where	= 'admin=1';
	$spdb = apply_filters('sph_stats_admin_count_query', $spdb);
	$result = $spdb->select();
	$stats['admins'] = $result[0]->count;

	$spdb = new spdbComplex;
	$spdb->table   = SFMEMBERS;
	$spdb->fields  = 'count(*) as count';
	$spdb->where   = 'moderator=1';
	$spdb = apply_filters('sph_stats_mod_count_query', $spdb);
	$result = $spdb->select();
	$stats['mods'] = $result[0]->count;

	$spdb = new spdbComplex;
	$spdb->table	= SFMEMBERS;
	$spdb->fields	= 'count(*) as count';
	if ($opts['hidemembers']) {
		$spdb->join		= array(
							SFMEMBERSHIPS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id',
							SFUSERGROUPS.' ON '.SFMEMBERSHIPS.'.usergroup_id = '.SFUSERGROUPS.'.usergroup_id');
		$spdb->where	=	'hide_stats = 0';
	}
	$spdb = apply_filters('sph_stats_members_count_query', $spdb);
	$result = $spdb->select();
	$stats['members'] = $result[0]->count - ($stats['admins'] + $stats['mods']);

	$spdb = new spdbComplex;
	$spdb->table	= SFPOSTS;
	$spdb->fields	= 'COUNT(DISTINCT guest_name) AS count';
	$spdb->where	= "guest_name != ''";
	$spdb = apply_filters('sph_stats_guests_count_query', $spdb);
	$result = $spdb->select();
	$stats['guests'] = $result[0]->count;

	return $stats;
}

# ------------------------------------------------------------------
# sp_get_top_poster_stats()
#
# Returns stats on posts (admins/moderators and members and updates
# the guest count
# ------------------------------------------------------------------
function sp_get_top_poster_stats($count) {
	$spdb = new spdbComplex;

	$spdb->found_rows	= 	true;
	$spdb->table		=	SFMEMBERS;
	$spdb->fields		=	SFMEMBERS.'.user_id, display_name, posts';
	$spdb->join			=	array(
								SFMEMBERSHIPS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id',
								SFUSERGROUPS.' ON '.SFMEMBERSHIPS.'.usergroup_id = '.SFUSERGROUPS.'.usergroup_id');
	$spdb->where		=	'hide_stats = 0 AND admin=0 AND moderator=0 AND posts > -1';
	$spdb->groupby		=	SFMEMBERS.'.user_id';
	$spdb->orderby		=	'hide_stats ASC, posts DESC';
	$spdb->limits		= 	"0, $count";

	$spdb = apply_filters('sph_top_poster_stats_query', $spdb);
	$topPosters = $spdb->select();
	return $topPosters;
}

# ------------------------------------------------------------------
# sp_get_moderator_stats()
#
# Returns stats on posts (admins/moderators and members and updates
# the guest count
# ------------------------------------------------------------------
function sp_get_moderator_stats() {
	$spdb = new spdbComplex;
	$spdb->table		= SFMEMBERS;
	$spdb->fields		= 'user_id, display_name, posts, moderator';
	$spdb->where		= 'moderator=1';
	$spdb = apply_filters('sph_stats_mod_stats_query', $spdb);
	$mods = $spdb->select('set', ARRAY_A);
	return $mods;
}

# ------------------------------------------------------------------
# sp_get_admin_stats()
#
# Returns stats on posts (admins/moderators and members and updates
# the guest count
# ------------------------------------------------------------------
function sp_get_admin_stats() {
	$spdb = new spdbComplex;
	$spdb->table		= SFMEMBERS;
	$spdb->fields		= 'user_id, display_name, posts, admin';
	$spdb->where		= 'admin=1';
	$spdb = apply_filters('sph_stats_admin_stats_query', $spdb);
	$admins = $spdb->select('set', ARRAY_A);
	return $admins;
}

# ------------------------------------------------------------------
# sp_guests_browsing()
#
# Calculates how many guests are browsing current forum or topic
# ------------------------------------------------------------------
function sp_guests_browsing() {
	global $spVars;

	$where = '';
	# Check that pageview is  set as this might be called from outside of the forum
	if (!empty($spVars['pageview'])) {
		if ($spVars['pageview'] == 'forum') $where = "forum_id=".$spVars['forumid'];
		if ($spVars['pageview'] == 'topic') $where = "topic_id=".$spVars['topicid'];
	}
	if (empty($where)) return;

	return spdb_count(SFTRACK, "trackuserid = 0 AND ".$where);
}

# ------------------------------------------------------------------
# sp_track_login()
#
# Filter Call
# Removes any sftrack record created when user was guest
# ------------------------------------------------------------------
function sp_track_login() {
	# if user was logged as guest before logging in, remove the guest entry
	$ip = sp_get_ip();
	spdb_query("DELETE FROM ".SFTRACK." WHERE trackname='".$ip."'");
}

# ------------------------------------------------------------------
# sp_track_logout()
#
# Filter Call
# Sets up the last visited upon user logout
# ------------------------------------------------------------------
function sp_track_logout() {
	global $current_user;

	sp_set_last_visited($current_user->ID);
	spdb_query("DELETE FROM ".SFTRACK." WHERE trackuserid=".$current_user->ID);

	# clear the users search cache
	sp_delete_cache('search');
}

# ------------------------------------------------------------------
# sp_set_last_visited()
#
# Set the last visited timestamp after user has disappeared
#	$userid:		Users ID
# ------------------------------------------------------------------
function sp_set_last_visited($userid) {
	global $spThisUser;
	# before setting last visit check and save timezone difference just to be sure.
	$opts = sp_get_member_item($userid, 'user_options');
	if (!empty($opts['timezone_string'])) {
		if (preg_match('/^UTC[ \t+-]/', $opts['timezone_string'])) {
			# correct for manual UTC offets
			$userOffset = preg_replace('/UTC\+?/', '', $opts['timezone_string']) * 3600;
		} else {
			# get timezone offset for user
			$date_time_zone_selected = new DateTimeZone(sp_esc_str($opts['timezone_string']));
			$userOffset = timezone_offset_get($date_time_zone_selected, date_create());
		}
		$wptz = get_option('timezone_string');
		if (empty($wptz)) {
			$serverOffset = get_option('gmt_offset');
		} else {
			$date_time_zone_selected = new DateTimeZone($wptz);
			$serverOffset = timezone_offset_get($date_time_zone_selected, date_create());
		}
		# calculate time offset between user and server
		$ntz = (int) round(($userOffset - $serverOffset) / 3600, 2);
		if ($opts['timezone'] != $ntz) {
			$opts['timezone'] = $ntz;
			$spThisUser->timezone = $ntz;
			sp_update_member_item($userid, 'user_options', $opts);
			sp_update_member_item($userid, 'checktime', 0);
		}
	}

	# Now set the last visit date/time
	sp_update_member_item($userid, 'lastvisit', 0);
}

?>