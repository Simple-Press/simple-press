<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-12-31 05:54:59 -0600 (Sun, 31 Dec 2017) $
$Rev: 15609 $
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

	$now = current_time('mysql');

	# if in an AJAX call then do not go through tracking check
	# other than to delete expired entries
	if (!wp_doing_ajax()) {
		# dont track feed views
		if (SP()->rewrites->pageData['pageview'] == 'feed') return;

		# Update tracking
		if (SP()->user->thisUser->member) {
			# it's a member
			$trackUserId = SP()->user->thisUser->ID;
			$trackName   = SP()->user->thisUser->user_login;
		} else {
			# Unknown guest
			$trackUserId = 0;
			$trackName   = SP()->user->thisUser->ip;
		}
		$track = SP()->DB->table(SPTRACK, "trackname='$trackName'", 'row');

		$forumId  = (isset(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : 0;
		$topicId  = (isset(SP()->rewrites->pageData['topicid'])) ? SP()->rewrites->pageData['topicid'] : 0;
		$pageview = SP()->rewrites->pageData['pageview'];

		# handle sneak peek
		if (!empty($topicId)) {
			if (!SP()->auths->get('view_forum', $forumId)) return;
		} else if (!empty($forumId)) {
			if (!SP()->auths->can_view($forumId, 'topic-title')) return;
		}

		# update or start tracking
		if ($track) {
			# they are still here
			SP()->DB->execute("UPDATE ".SPTRACK."
					   SET trackdate='".$now."', forum_id=".$forumId.",	 topic_id=".$topicId.", pageview='$pageview'
					   WHERE id=".$track->id);
			if (SP()->user->thisUser->member) sp_update_users_newposts();
			SP()->user->thisUser->trackid             = $track->id;
			SP()->user->thisUser->session_first_visit = false;
			SP()->user->thisUser->notification        = $track->notification;
		} else {
			# newly arrived
			# set deice being used
			$device = 'D';
			switch (SP()->core->device) {
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
			$display = 'spType-'.SP()->user->thisUser->usertype;
			if (!empty(SP()->user->thisUser->rank)) $display .= ' spRank-'.sp_create_slug(SP()->user->thisUser->rank[0]['name'], false);
			if (!empty(SP()->user->thisUser->special_rank)) {
				foreach (SP()->user->thisUser->special_rank as $rank) {
					$display .= ' spSpecialRank-'.sp_create_slug($rank['name'], false);
				}
			}
			if (!empty(SP()->user->thisUser->memberships)) {
				foreach (SP()->user->thisUser->memberships as $membership) {
					$display .= ' spUsergroup-'.sp_create_slug($membership['usergroup_name'], false);
				}
			}

			SP()->DB->execute("INSERT INTO ".SPTRACK."
					   (trackuserid, trackname, forum_id, topic_id, trackdate, pageview, device, display) VALUES
					   ($trackUserId, '$trackName', $forumId, $topicId, '$now', '$pageview', '$device', '$display')");
			SP()->user->thisUser->trackid             = SP()->rewrites->pageData['insertid'];
			SP()->user->thisUser->session_first_visit = true;
			if (SP()->user->thisUser->member) sp_update_users_newposts();
		}
	}
	
	# Check for expired tracking - some may have left the scene
	$splogin = SP()->options->get('sflogin');
	$timeout = $splogin['sptimeout'];
	if (!$timeout) $timeout = 20;

	$expired = SP()->DB->table(SPTRACK, "trackdate < DATE_SUB('$now', INTERVAL $timeout MINUTE)");

	if ($expired) {
		# if any Members expired - update user meta
		foreach ($expired as $expire) {
			if ($expire->trackuserid > 0) sp_set_last_visited($expire->trackuserid);
		}

		# finally delete them
		SP()->DB->execute("DELETE FROM ".SPTRACK."
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
	# see if track id already set up
	if (isset(SP()->user->thisUser->trackid) && SP()->user->thisUser->trackid >= 0) return; # user class inits to -1

	# not set up, so grab the info
	if (SP()->user->thisUser->member) {
		# it's a member
		$trackName = SP()->user->thisUser->user_login;
	} else {
		# Unknown guest
		$trackName = SP()->user->thisUser->ip;
	}
	$track = SP()->DB->table(SPTRACK, "trackname='$trackName'", 'row');
	if ($track) SP()->user->thisUser->trackid = $track->id;
}

# ------------------------------------------------------------------
# sp_get_members_online()
#
# Returns list of members currently tagged as online
# ------------------------------------------------------------------
function sp_get_members_online() {
	return SP()->DB->select("SELECT trackuserid, display_name, user_options, forum_id, topic_id, pageview, display FROM ".SPTRACK."
			JOIN ".SPMEMBERS." ON ".SPTRACK.".trackuserid = ".SPMEMBERS.".user_id
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
	if (!isset($session_online)) $session_online = SP()->DB->select("SELECT trackuserid FROM ".SPTRACK, 'col');
	if (in_array($userid, $session_online)) return true;

	return false;
}

# ------------------------------------------------------------------
# sp_get_stats_counts()
#
# Returns stats on group/forum/topic/post count
# ------------------------------------------------------------------
function sp_get_stats_counts() {
	$cnt         = new stdClass();
	$cnt->groups = 0;
	$cnt->forums = 0;
	$cnt->topics = 0;
	$cnt->posts  = 0;

	$groupid = '';

	$forums = SP()->DB->table(SPFORUMS, '', '', 'group_id');
	if ($forums) {
		foreach ($forums as $forum) {
			if ($forum->group_id != $groupid) {
				$groupid = $forum->group_id;
				$cnt->groups++;
			}
			$cnt->forums++;
			$cnt->topics += $forum->topic_count;
			$cnt->posts += $forum->post_count;
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

	$opts = SP()->options->get('sfcontrols');

	$query           = new stdClass();
	$query->table    = SPMEMBERS;
	$query->fields   = 'count(*) as count';
	$query->where    = 'admin=1';
	$query           = apply_filters('sph_stats_admin_count_query', $query);
	$result          = SP()->DB->select($query);
	$stats['admins'] = $result[0]->count;

	$query         = new stdClass();
	$query->table  = SPMEMBERS;
	$query->fields = 'count(*) as count';
	$query->where  = 'moderator=1';
	$query         = apply_filters('sph_stats_mod_count_query', $query);
	$result        = SP()->DB->select($query);
	$stats['mods'] = $result[0]->count;

	$query         = new stdClass;
	$query->table  = SPMEMBERS;
	$query->fields = 'count(*) as count';
	if (isset($opts['hidemembers']) && $opts['hidemembers'] != false) {
		$query->join  = array(SPMEMBERSHIPS.' ON '.SPMEMBERS.'.user_id = '.SPMEMBERSHIPS.'.user_id', SPUSERGROUPS.' ON '.SPMEMBERSHIPS.'.usergroup_id = '.SPUSERGROUPS.'.usergroup_id');
		$query->where = 'hide_stats = 0';
	}
	$query            = apply_filters('sph_stats_members_count_query', $query);
	$result           = SP()->DB->select($query);
	$stats['members'] = $result[0]->count - ($stats['admins'] + $stats['mods']);

	$query         = new stdClass();
	$query->table  = SPPOSTS;
	$query->fields = 'COUNT(DISTINCT guest_name) AS count';
	$query->where  = "guest_name != ''";
	$query         = apply_filters('sph_stats_guests_count_query', $query);
	$result        = SP()->DB->select($query);;
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
	$query             = new stdClass();
	$query->found_rows = true;
	$query->table      = SPMEMBERS;
	$query->fields     = SPMEMBERS.'.user_id, display_name, posts';
	$query->join       = array(SPMEMBERSHIPS.' ON '.SPMEMBERS.'.user_id = '.SPMEMBERSHIPS.'.user_id', SPUSERGROUPS.' ON '.SPMEMBERSHIPS.'.usergroup_id = '.SPUSERGROUPS.'.usergroup_id');
	$query->where      = 'hide_stats = 0 AND admin=0 AND moderator=0 AND posts > -1';
	$query->groupby    = SPMEMBERS.'.user_id';
	$query->orderby    = 'hide_stats ASC, posts DESC';
	$query->limits     = "0, $count";
	$query             = apply_filters('sph_top_poster_stats_query', $query);
	$topPosters        = SP()->DB->select($query);

	return $topPosters;
}

# ------------------------------------------------------------------
# sp_get_moderator_stats()
#
# Returns stats on posts (admins/moderators and members and updates
# the guest count
# ------------------------------------------------------------------
function sp_get_moderator_stats() {
	$query             = new stdClass();
	$query->table      = SPMEMBERS;
	$query->fields     = 'user_id, display_name, posts, moderator';
	$query->where      = 'moderator=1';
	$query             = apply_filters('sph_stats_mod_stats_query', $query);
	$query->resultType = ARRAY_A;
	$mods              = SP()->DB->select($query);

	return $mods;
}

# ------------------------------------------------------------------
# sp_get_admin_stats()
#
# Returns stats on posts (admins/moderators and members and updates
# the guest count
# ------------------------------------------------------------------
function sp_get_admin_stats() {
	$query             = new stdClass();
	$query->table      = SPMEMBERS;
	$query->fields     = 'user_id, display_name, posts, admin';
	$query->where      = 'admin=1';
	$query             = apply_filters('sph_stats_admin_stats_query', $query);
	$query->resultType = ARRAY_A;
	$admins            = SP()->DB->select($query);

	return $admins;
}

# ------------------------------------------------------------------
# sp_guests_browsing()
#
# Calculates how many guests are browsing current forum or topic
# ------------------------------------------------------------------
function sp_guests_browsing() {
	$where = '';
	# Check that pageview is  set as this might be called from outside of the forum
	if (!empty(SP()->rewrites->pageData['pageview'])) {
		if (SP()->rewrites->pageData['pageview'] == 'forum') $where = "forum_id=".SP()->rewrites->pageData['forumid'];
		if (SP()->rewrites->pageData['pageview'] == 'topic') $where = "topic_id=".SP()->rewrites->pageData['topicid'];
	}
	if (empty($where)) return 0;

	return SP()->DB->count(SPTRACK, "trackuserid = 0 AND ".$where);
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
	SP()->DB->execute("DELETE FROM ".SPTRACK." WHERE trackname='".$ip."'");
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
	SP()->DB->execute("DELETE FROM ".SPTRACK." WHERE trackuserid=".$current_user->ID);

	# clear the users search cache
	SP()->cache->delete('search');
}

# ------------------------------------------------------------------
# sp_set_last_visited()
#
# Set the last visited timestamp after user has disappeared
#	$userid:		Users ID
# ------------------------------------------------------------------
function sp_set_last_visited($userid) {
	# before setting last visit check and save timezone difference just to be sure.
	$opts = SP()->memberData->get($userid, 'user_options');
	if (!empty($opts['timezone_string'])) {
		if (preg_match('/^UTC[ \t+-]/', $opts['timezone_string'])) {
			# correct for manual UTC offets
			$userOffset = preg_replace('/UTC\+?/', '', $opts['timezone_string']) * 3600;
		} else {
			# get timezone offset for user
			$date_time_zone_selected = new DateTimeZone(SP()->filters->str($opts['timezone_string']));
			$userOffset              = timezone_offset_get($date_time_zone_selected, date_create());
		}
		$wptz = get_option('timezone_string');
		if (empty($wptz)) {
			$serverOffset = get_option('gmt_offset');
		} else {
			$date_time_zone_selected = new DateTimeZone($wptz);
			$serverOffset            = timezone_offset_get($date_time_zone_selected, date_create());
		}
		# calculate time offset between user and server
		$ntz = (int)round(($userOffset - $serverOffset) / 3600, 2);
		if ($opts['timezone'] != $ntz) {
			$opts['timezone']     = $ntz;
			SP()->user->thisUser->timezone = $ntz;
			SP()->memberData->update($userid, 'user_options', $opts);
			SP()->memberData->update($userid, 'checktime', 0);
		}
	}

	# Now set the last visit date/time
	SP()->memberData->update($userid, 'lastvisit', 0);
}
