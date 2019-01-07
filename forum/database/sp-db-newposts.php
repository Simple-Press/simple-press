<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2016-11-05 16:57:39 -0500 (Sat, 05 Nov 2016) $
$Rev: 14708 $
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
	global $spThisUser;

	# Check the users checktime against the last post timestamp to see if we need to do this
	$checkTime = spdb_zone_mysql_checkdate($spThisUser->checktime);
	$postTime = sp_get_option('poststamp');
	if ((strtotime($checkTime) > strtotime($postTime)) && !isset($_GET['mark-read'])) return;

	# so there must have been a new post since the last page load for this user
	$newPostList = $spThisUser->newposts;
	if (empty($newPostList['topics'])) {
		# clean it up to be on the safe side
		unset($newPostList);
		$newPostList = array();
		$newPostList['topics'] = array();
		$newPostList['forums'] = array();
		$newPostList['post'] = array();
	}

	# create new holding array and new checktime (now)
	$addPostList = array();
	$addPostList['topics'] = array();
	$addPostList['forums'] = array();
	$addPostList['post'] = array();
	sp_set_server_timezone();
	$newCheckTime = sp_apply_timezone(time(), 'mysql');

	# Use the current checktime for any new posts since users session began
	$spdb = new spdbComplex;
		$spdb->table	= SFPOSTS;
		$spdb->distinct = true;
		$spdb->fields	= 'topic_id, forum_id, post_id';
		$spdb->where	= "post_status = 0 AND post_date > '$checkTime' AND user_id != $spThisUser->ID";
		$spdb->groupby  = 'topic_id';
		$spdb->orderby	= 'post_id DESC';
		$spdb->limits	= $spThisUser->unreadposts;
	$spdb = apply_filters('sph_update_newposts_query', $spdb);

	$records = $spdb->select('set', ARRAY_A);

	if ($records) {
		$x = 0;
		foreach ($records as $r) {
			if (sp_get_auth('view_forum', $r['forum_id']) && !in_array($r['topic_id'], $newPostList['topics'])) {
				$addPostList['forums'][$x] = $r['forum_id'];
				$addPostList['topics'][$x] = $r['topic_id'];
				$addPostList['post'][$x] = $r['post_id'];
				$x++;
			}
		}
	}

	$addPostList = apply_filters('sph_new_post_list', $addPostList, $newPostList);

	# now merge the arrays and truncate if necessary
	$newPostList['topics'] = array_merge($addPostList['topics'], $newPostList['topics']);
	$newPostList['forums'] = array_merge($addPostList['forums'], $newPostList['forums']);
	$newPostList['post'] = array_merge($addPostList['post'], $newPostList['post']);
	if (count($newPostList['topics']) > $spThisUser->unreadposts) {
		array_splice($newPostList['topics'], $spThisUser->unreadposts);
		array_splice($newPostList['forums'], $spThisUser->unreadposts);
		array_splice($newPostList['post'], $spThisUser->unreadposts);
	}

	# update sfmembers - do it here to ensure both are updated together
	spdb_query("UPDATE ".SFMEMBERS." SET newposts='".serialize($newPostList)."', checktime='".$newCheckTime."' WHERE user_id=".$spThisUser->ID);
	$spThisUser->newpostlist = true;
	$spThisUser->checktime = $newCheckTime;
	$spThisUser->newposts = $newPostList;
}

# ---------------------------------------------------------------------
# sp_bump_users_newposts()
#
# Bumps the post marker for a topic in the CURRENT users new-post-list
#	$newPostList:		new-post-list
# ---------------------------------------------------------------------
function sp_bump_users_newposts($topicId, $postIndex) {
	global $spThisUser;

	$where	= 'topic_id = '.$topicId.' AND post_index = '.($postIndex+1);
	$updatePostId = spdb_table(SFPOSTS, $where, 'post_id');
	$spThisUser->newposts['post'][array_search($topicId, $spThisUser->newposts['topics'])] = $updatePostId;
	sp_update_member_item($spThisUser->ID, 'newposts', $spThisUser->newposts);
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
	global $spThisUser;

	if (empty($userid)) return;

	if (isset($spThisUser) && $spThisUser->ID == $userid) {
		$newPostList = $spThisUser->newposts;
	} else {
		$newPostList = sp_get_member_item($userid, 'newposts');
	}

	if ($newPostList && !empty($newPostList)) {
		if ((count($newPostList['topics']) == 1) && ($newPostList['topics'][0] == $topicid)) {
            $remove = -99;
			unset($newPostList);
			$newPostList = array();
			$newPostList['topics'] = array();
			$newPostList['forums'] = array();
			$newPostList['post'] = array();
		} else {
        	$remove = -1;
			for ($x=0; $x < count($newPostList['topics']); $x++) {
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
			sp_update_member_item($userid, 'newposts', $newPostList);
			if ($spThisUser->ID == $userid) {
				$spThisUser->newposts = $newPostList;
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
function sp_destroy_users_newposts($forumid='') {
	global $spThisUser;

    if (empty($forumid) || empty($spThisUser->newposts['topics'])) {
    	$newPostList = array();
    	$newPostList['topics'] = array();
    	$newPostList['forums'] = array();
    	$newPostList['post'] = array();

        $spThisUser->newposts = $newPostList;
    } else {
        $newPostList = $spThisUser->newposts;
        foreach ($spThisUser->newposts['forums'] as $index => $forum) {
            if ($forum == $forumid) {
                unset($newPostList['topics'][$index]);
                unset($newPostList['forums'][$index]);
                unset($newPostList['post'][$index]);
            }
        }
        $newPostList['topics'] = array_values($newPostList['topics']);
        $newPostList['forums'] = array_values($newPostList['forums']);
        $newPostList['post'] = array_values($newPostList['post']);

        $spThisUser->newposts = $newPostList;
    }

	sp_update_member_item($spThisUser->ID, 'newposts', $newPostList);
	sp_update_member_item($spThisUser->ID, 'checktime', 0);
	sp_set_server_timezone();
    $spThisUser->checktime = sp_apply_timezone(time(), 'mysql');
}

# ------------------------------------------------------------------
# sp_is_in_users_newposts()
#
# Determines if topic is in CURRENT users new-post-list
#	$topicid:		the topic to look for
# ------------------------------------------------------------------
function sp_is_in_users_newposts($topicid) {
	global $spThisUser;

	$newPostList = ($spThisUser->member) ? $spThisUser->newposts : '';
	$found = false;
	if (!empty($newPostList['topics']) && $newPostList['topics']) {
		if (in_array($topicid, $newPostList['topics'])) $found=true;
	}
	return $found;
}

# ------------------------------------------------------------------
# sp_mark_all_read()
#
# Marks CURRENT users posts as read
# ------------------------------------------------------------------
function sp_mark_all_read() {
	global $spThisUser;

	# just to be safe, make sure a member called
	if ($spThisUser->member) {
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
	global $spThisUser;

	# just to be safe, make sure a member called
	if ($spThisUser->member) {
		sp_destroy_users_newposts($forumid);
		sp_update_users_newposts();
	}
}

add_action('sph_remove_a_newpost', 'sp_bump_newpost_count');
function sp_bump_newpost_count() {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var c = new Number(jQuery('#spUnreadCount').html());
			if (c > 0) {
				c--;
				jQuery('#spUnreadCount').html(c.toString());
			}
		});
	</script>
<?php
}

?>