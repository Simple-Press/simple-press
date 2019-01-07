<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	GLOBAL Database Module
# 	New Post Database Routines
#
#	sp_update_users_newposts()
#	sp_remove_users_newposts()
#	sp_destroy_users_newposts()
#	sp_is_in_users_newposts()
#	sp_combined_new_posts_list()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_update_users_newposts()
#
# Updates the CURRENT users new-post-list on subsequent page loads
#	$newPostList:		new-post-list
# ------------------------------------------------------------------
function sp_update_users_newposts() {
	# Check the users checktime against the last post timestamp to see if we need to do this
	$checkTime = spdb_zone_mysql_checkdate(SP()->user->thisUser->checktime);
	$postTime  = SP()->options->get('poststamp');
	if ((strtotime($checkTime) > strtotime($postTime)) && !isset($_GET['mark-read'])) return;

	# so there must have been a new post since the last page load for this user
	$newPostList = SP()->user->thisUser->newposts;
	if (empty($newPostList['topics'])) {
		# clean it up to be on the safe side
		unset($newPostList);
		$newPostList           = array();
		$newPostList['topics'] = array();
		$newPostList['forums'] = array();
		$newPostList['post']   = array();
	}

	# create new holding array and new checktime (now)
	$addPostList           = array();
	$addPostList['topics'] = array();
	$addPostList['forums'] = array();
	$addPostList['post']   = array();
	SP()->dateTime->set_timezone();
	$newCheckTime = SP()->dateTime->apply_timezone(time(), 'mysql');

	# Use the current checktime for any new posts since users session began
	$query             = new stdClass();
	$query->table      = SPPOSTS;
	$query->distinct   = true;
	$query->fields     = 'topic_id, forum_id, post_id';
	$query->where      = "post_status = 0 AND post_date > '$checkTime' AND user_id != ".SP()->user->thisUser->ID;
	$query->groupby    = 'topic_id';
	$query->orderby    = 'post_id DESC';
	$query->limits     = SP()->user->thisUser->unreadposts;
	$query             = apply_filters('sph_update_newposts_query', $query);
	$query->resultType = ARRAY_A;
	$records           = SP()->DB->select($query);

	if ($records) {
		$x = 0;
		foreach ($records as $r) {
			if (SP()->auths->get('view_forum', $r['forum_id']) && !in_array($r['topic_id'], $newPostList['topics'])) {
				$addPostList['forums'][$x] = $r['forum_id'];
				$addPostList['topics'][$x] = $r['topic_id'];
				$addPostList['post'][$x]   = $r['post_id'];
				$x++;
			}
		}
	}

	$addPostList = apply_filters('sph_new_post_list', $addPostList, $newPostList);

	# now merge the arrays and truncate if necessary
	$newPostList['topics'] = array_merge($addPostList['topics'], $newPostList['topics']);
	$newPostList['forums'] = array_merge($addPostList['forums'], $newPostList['forums']);
	$newPostList['post']   = array_merge($addPostList['post'], $newPostList['post']);
	if (count($newPostList['topics']) > SP()->user->thisUser->unreadposts) {
		array_splice($newPostList['topics'], SP()->user->thisUser->unreadposts);
		array_splice($newPostList['forums'], SP()->user->thisUser->unreadposts);
		array_splice($newPostList['post'], SP()->user->thisUser->unreadposts);
	}

	# update sfmembers - do it here to ensure both are updated together
	SP()->DB->execute("UPDATE ".SPMEMBERS." SET newposts='".serialize($newPostList)."', checktime='".$newCheckTime."' WHERE user_id=".SP()->user->thisUser->ID);
	SP()->user->thisUser->newpostlist = true;
	SP()->user->thisUser->checktime   = $newCheckTime;
	SP()->user->thisUser->newposts    = $newPostList;
}

# ---------------------------------------------------------------------
# sp_bump_users_newposts()
#
# Bumps the post marker for a topic in the CURRENT users new-post-list
#	$newPostList:		new-post-list
# ---------------------------------------------------------------------
function sp_bump_users_newposts($topicId, $postIndex) {
	$where                                                                                 = 'topic_id = '.$topicId.' AND post_index = '.($postIndex + 1);
	$updatePostId                                                                          = SP()->DB->table(SPPOSTS, $where, 'post_id');
	SP()->user->thisUser->newposts['post'][array_search($topicId, SP()->user->thisUser->newposts['topics'])] = $updatePostId;
	SP()->memberData->update(SP()->user->thisUser->ID, 'newposts', SP()->user->thisUser->newposts);
}

# ------------------------------------------------------------------
# sp_remove_users_newposts()
#
# Removes items from a users new-post-list upon viewing them
# IMPORTANT NOTE: THE USERS ID MUST BE PASSED...
# DOES NOT ASSUME CURRENT USER
#	$topicid:		the topic to remove from new-post-list
#	$userid:		id of user
# ------------------------------------------------------------------
function sp_remove_users_newposts($topicid, $userid, $changeCount) {
	if (empty($userid)) return;

	if (isset(SP()->user->thisUser) && SP()->user->thisUser->ID == $userid) {
		$newPostList = SP()->user->thisUser->newposts;
	} else {
		$newPostList = SP()->memberData->get($userid, 'newposts');
	}

	if ($newPostList && !empty($newPostList)) {
		if ((count($newPostList['topics']) == 1) && ($newPostList['topics'][0] == $topicid)) {
			$remove = -99;
			unset($newPostList);
			$newPostList           = array();
			$newPostList['topics'] = array();
			$newPostList['forums'] = array();
			$newPostList['post']   = array();
		} else {
			$remove = -1;
			for ($x = 0; $x < count($newPostList['topics']); $x++) {
				if ($newPostList['topics'][$x] == $topicid) {
					$remove = $x;
					break;
				}
			}
		}
		if ($remove != -1) {
			array_splice($newPostList['topics'], $remove, 1);
			array_splice($newPostList['forums'], $remove, 1);
			array_splice($newPostList['post'], $remove, 1);
			SP()->memberData->update($userid, 'newposts', $newPostList);
			if (SP()->user->thisUser->ID == $userid) {
				SP()->user->thisUser->newposts = $newPostList;
			}
		}
		# do we need to alter the unread post count?
		if ($changeCount) {
			do_action('sph_remove_a_newpost');
		}
	}
}

# ------------------------------------------------------------------
# sp_destroy_users_newposts()
#
# Destroy CURRENT users new-post-list
#	$userid:		Users ID
# ------------------------------------------------------------------
function sp_destroy_users_newposts($forumid = '') {
	if (empty($forumid) || empty(SP()->user->thisUser->newposts['topics'])) {
		$newPostList           = array();
		$newPostList['topics'] = array();
		$newPostList['forums'] = array();
		$newPostList['post']   = array();

		SP()->user->thisUser->newposts = $newPostList;
	} else {
		$newPostList = SP()->user->thisUser->newposts;
		foreach (SP()->user->thisUser->newposts['forums'] as $index => $forum) {
			if ($forum == $forumid) {
				unset($newPostList['topics'][$index]);
				unset($newPostList['forums'][$index]);
				unset($newPostList['post'][$index]);
			}
		}
		$newPostList['topics'] = array_values($newPostList['topics']);
		$newPostList['forums'] = array_values($newPostList['forums']);
		$newPostList['post']   = array_values($newPostList['post']);

		SP()->user->thisUser->newposts = $newPostList;
	}

	SP()->memberData->update(SP()->user->thisUser->ID, 'newposts', $newPostList);
	SP()->memberData->update(SP()->user->thisUser->ID, 'checktime', 0);
	SP()->dateTime->set_timezone();
	SP()->user->thisUser->checktime = SP()->dateTime->apply_timezone(time(), 'mysql');
}

# ------------------------------------------------------------------
# sp_is_in_users_newposts()
#
# Determines if topic is in CURRENT users new-post-list
#	$topicid:		the topic to look for
# ------------------------------------------------------------------
function sp_is_in_users_newposts($topicid) {
	$newPostList = (SP()->user->thisUser->member) ? SP()->user->thisUser->newposts : '';
	$found       = false;
	if (!empty($newPostList['topics']) && $newPostList['topics']) {
		if (in_array($topicid, $newPostList['topics'])) $found = true;
	}

	return $found;
}

# ------------------------------------------------------------------
# sp_mark_all_read()
#
# Marks CURRENT users posts as read
# ------------------------------------------------------------------
function sp_mark_all_read() {
	# just to be safe, make sure a member called
	if (SP()->user->thisUser->member) {
		sp_destroy_users_newposts();
		sp_update_users_newposts();
	}
}

# ------------------------------------------------------------------
# sp_mark_forum_read()
#
# Marks CURRENT users posts in specific forum as read
# ------------------------------------------------------------------
function sp_mark_forum_read($forumid) {
	# just to be safe, make sure a member called
	if (SP()->user->thisUser->member) {
		sp_destroy_users_newposts($forumid);
		sp_update_users_newposts();
	}
}

# ------------------------------------------------------------------
# spdb_zone_mysql_checkdate()
#
# Version: 5.0
# Sets time zone altered compare date time for sql queries
# Used by the newpost list building queries
#	$d:		date to be altered (last_visit or check_time)
# ------------------------------------------------------------------
function spdb_zone_mysql_checkdate($d) {
	$zone = (isset(SP()->user->thisUser->timezone)) ? SP()->user->thisUser->timezone : 0;

	if ($zone == 0) return $d;
	$ud = strtotime($d);
	if ($zone < 0 ? $ud = $ud + (abs($zone * 3600)) : $ud = $ud - (abs($zone * 3600))) ;

	return date('Y-n-d H:i:s', $ud);
}

add_action('sph_remove_a_newpost', 'sp_bump_newpost_count');
function sp_bump_newpost_count() {
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				var c = new Number($('#spUnreadCount').html());
				if (c > 0) {
					c--;
					$('#spUnreadCount').html(c.toString());
				}
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
}
