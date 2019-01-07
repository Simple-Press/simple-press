<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2018-08-11 20:19:32 -0500 (Sat, 11 Aug 2018) $
$Rev: 15697 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	GLOBAL Database Module
# 	Forum Management Database Routines
#
#		sp_save_edited_post()
#		sp_save_edited_topic()
#		sp_move_topic()
#		sp_move_post()
#		sp_reassign_post()
#		sp_change_topic_status()
#		sp_update_opened()
#		sp_delete_topic()
#		sp_delete_post()
#		sp_lock_topic_toggle()
#		sp_pin_topic_toggle()
#		sp_pin_post_toggle()
#		sp_mark_all_read()
#		sp_approve_post()
#		sp_remove_from_waiting()
#		sp_remove_waiting_queue()
#		sp_build_post_index()
#		sp_build_forum_index()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_save_edited_post()
#
# Saves a forum post following an edit in the UI
# Values in POST variables
# ------------------------------------------------------------------
function sp_save_edited_post() {
	global $spThisUser, $spVars, $spGlobals;

	# post id of edited post
    $newpost = array();
    $newpost['postid'] = sp_esc_int($_POST['pid']);

    # no post editng if guest, in post edit mode or lockdwon
	if ($spVars['displaymode'] == 'edit' && $spVars['postedit'] == $newpost['postid']) return;
	if ($spGlobals['lockdown']) return;

    # data for the post - want to ensure absolute forum id plus used for notifications later
    $post = spdb_table(SFPOSTS, "post_id={$newpost['postid']}", 'row');
    $topic = spdb_table(SFTOPICS, "topic_id=$post->topic_id", 'row');

    # verify we can edit this post
	$canEdit = false;
	if (sp_get_auth('edit_any_post', $post->forum_id)) {
		$canEdit = true;
	} else {
		if ($post->user_id == $spThisUser->ID) {
            $last_post = ($newpost['postid'] == $topic->post_id) || ($post->post_status == 1 && ($newpost['postid'] == $topic->post_id_held));

        	$edit_days = sp_get_option('editpostdays');
            $post_date = strtotime(sp_date('d', $post->post_date));
            $date_diff = floor((time() - $post_date) / (60 * 60 * 24));

            if (sp_get_auth('edit_own_posts_forever', $post->forum_id) ||
			   (sp_get_auth('edit_own_posts_reply', $post->forum_id) && $last_post) ||
               (sp_get_auth('edit_own_posts_for_time', $post->forum_id) && $date_diff <= $edit_days)) {
                $canEdit = true;
            }
		}
	}
	if (!$canEdit) {
        sp_notify(SPFAILURE, sp_text('Edit failed - you do not have permission'));
        return;
    }

	# post info
	$newpost['postcontent'] = $_POST['postitem'];
	$newpost['postcontent'] = sp_filter_content_save($newpost['postcontent'], 'edit', true, SFPOSTS, 'post_content');

    $newpost['forumid'] = sp_esc_int($_POST['forumid']);
    $newpost['forumslug'] = sp_esc_str($_POST['forumslug']);
	$newpost['topicid'] = sp_esc_int($_POST['topicid']);
	$newpostt['topicslug'] = sp_esc_str($_POST['topicslug']);

	# post edit array
	$history = spdb_select('var', 'SELECT post_edit FROM '.SFPOSTS." WHERE post_id='{$newpost['postid']}'", ARRAY_A);
	$postedits = (!empty($history)) ? unserialize($history) : array();
	$x = count($postedits);

	$edittime = current_time('mysql');
	$postedits[$x]['by'] = sp_filter_name_save($spThisUser->display_name);
	$postedits[$x]['at'] = strtotime($edittime);
	$newpost['postedits'] = serialize($postedits);

	$newpost['postcontent'] = apply_filters('sph_post_edit_data', $newpost['postcontent'], $newpost['postid'], $spThisUser->ID);

    $date_update = '';
    if (!empty($_POST['editTimestamp'])) {
    	$yy = sp_esc_int($_POST['tsYear']);
    	$mm = sp_esc_int($_POST['tsMonth']);
    	$dd = sp_esc_int($_POST['tsDay']);
    	$hh = sp_esc_int($_POST['tsHour']);
    	$mn = sp_esc_int($_POST['tsMinute']);
    	$ss = sp_esc_int($_POST['tsSecond']);
    	$dd = ($dd > 31 ) ? 31 : $dd;
    	$hh = ($hh > 23 ) ? $hh -24 : $hh;
    	$mn = ($mn > 59 ) ? $mn -60 : $mn;
    	$ss = ($ss > 59 ) ? $ss -60 : $ss;
    	$newpost['postdate'] = sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $yy, $mm, $dd, $hh, $mn, $ss );
        $date_update = ', post_date = "'.$newpost['postdate'].'"';
    }

	$sql = 'UPDATE '.SFPOSTS." SET post_content='{$newpost['postcontent']}', post_edit='{$newpost['postedits']}'$date_update WHERE post_id={$newpost['postid']}";

	if (spdb_query($sql) == false) {
		sp_notify(SPFAILURE, sp_text('Update failed'));
	} else {
		sp_notify(SPSUCCESS, sp_text('Updated post saved'));

        # set up some data for notifications
        $link = sp_permalink_from_postid($newpost['postid']);

        # notify admins/mods of edit
        $users = spdb_select('set', 'SELECT user_id, admin_options FROM '.SFMEMBERS." WHERE admin=1 OR moderator=1");
        if ($users) {
            $time = time() + (7 * 24 * 60 * 60);
            $text = sp_text('A user has edited the post');
            foreach ($users as $user) {
                $options = unserialize($user->admin_options);
                if ($options['notify-edited'] && $spThisUser->ID != $user->user_id) { # dont notify self
        			$nData = array();
        			$nData['user_id']		= $user->user_id;
        			$nData['guest_email']	= '';
        			$nData['post_id']		= $newpost['postid'];
        			$nData['link']			= $link;
        			$nData['link_text']		= $topic->topic_name;
        			$nData['message']		= $text;
        			$nData['expires']		= $time; # 7 days; 24 hours; 60 mins; 60secs
        			sp_add_notice($nData);
                }
            }
        }

        # notify author of change
    	$sfadminsettings = sp_get_option('sfadminsettings');
        if ($sfadminsettings['editnotice'] && $spThisUser->ID != $post->user_id) {
			$nData = array();
			$nData['user_id']		= $post->user_id;
			$nData['guest_email']	= $post->guest_email;
			$nData['post_id']		= $newpost['postid'];
			$nData['link']			= $link;
			$nData['link_text']		= $topic->topic_name;
			$nData['message']		= sp_text('An edit has been made to your post');
			$nData['expires']		= time() + (30 * 24 * 60 * 60); # 30 days; 24 hours; 60 mins; 60secs
			sp_add_notice($nData);
        }
	}

    $newpost['userid'] = $spThisUser->ID;
    $newpost['action'] = 'edit';

	do_action('sph_post_edit_after_save', $newpost);
}

# ------------------------------------------------------------------
# sp_save_edited_topic()
#
# Saves a topic title following an edit in the UI
# Values in POST variables
# ------------------------------------------------------------------
function sp_save_edited_topic() {
	global $spThisUser;

	$topicid = sp_esc_int($_POST['tid']);
	$topicname = sp_filter_title_save($_POST['topicname'], SFTOPICS, 'topic_name');
	if (empty($topicname)) {
		sp_notify(SPFAILURE, sp_text('Update failed'));
		return;
	}
	# grab topic to check
	$topicrecord = spdb_table(SFTOPICS, "topic_id=$topicid", 'row');

	if (empty($_POST['topicslug']) && $topicname == $topicrecord->topic_name) {
		$topicslug = $topicrecord->topic_slug;
	} else {
		$topicslug = sp_esc_str($_POST['topicslug']);
	}

	if (empty($_POST['topicslug'])) {
		$topicslug = sp_create_slug($topicname, true, SFTOPICS, 'topic_slug');
	}
	if (empty($topicslug)) $topicslug = 'topic-'.$topicid;

	$sql = 'UPDATE '.SFTOPICS.' SET
			topic_name="'.$topicname.'",
			topic_slug="'.$topicslug.'"
			WHERE topic_id='.sp_esc_int($topicid);

	if (spdb_query($sql) == false) {
		sp_notify(SPFAILURE, sp_text('Update failed'));
	} else {
        do_action('sph_topic_title_edited', $topicid, $topicname, $spThisUser->ID);

		sp_notify(SPSUCCESS, sp_text('Updated topic title saved'));

        # set up some data for notifications
        $forumslug = spdb_table(SFFORUMS, "forum_id=$topicrecord->forum_id", 'forum_slug');
        $link = sp_build_url($forumslug, $topicslug, 1);

        # notify admins/mods of edit
        $users = spdb_select('set', 'SELECT user_id, admin_options FROM '.SFMEMBERS." WHERE admin=1 OR moderator=1");
        if ($users) {
            $time = time() + (7 * 24 * 60 * 60);
            $text = sp_text('A user has edit the topic title of');
            foreach ($users as $user) {
                $options = unserialize($user->admin_options);
                if ($options['notify-edited'] && $spThisUser->ID != $user->user_id) { # dont notify self
        			$nData = array();
        			$nData['user_id']		= $user->user_id;
        			$nData['guest_email']	= '';
        			$nData['post_id']		= 0;
        			$nData['link']			= $link;
        			$nData['link_text']		= $topicname;
        			$nData['message']		= $text;
        			$nData['expires']		= $time; # 7 days; 24 hours; 60 mins; 60secs
        			sp_add_notice($nData);
                }
            }
        }

        # notify author of change
    	$sfadminsettings = sp_get_option('sfadminsettings');
        if ($sfadminsettings['editnotice'] && $spThisUser->ID != $topicrecord->user_id) {
			$nData = array();
			$nData['user_id']		= $topicrecord->user_id;
			$nData['guest_email']	= '';
			$nData['post_id']		= 0;
			$nData['link']			= $link;
			$nData['link_text']		= $topicname;
			$nData['message']		= sp_text('The topic title was edited for your topic');
			$nData['expires']		= time() + (30 * 24 * 60 * 60); # 30 days; 24 hours; 60 mins; 60secs
			sp_add_notice($nData);
        }
	}

	# try and update unternal links in posts with new slug
	if ($topicrecord->topic_slug != $topicslug) sp_update_post_urls($topicrecord->topic_slug, $topicslug);
}

# ------------------------------------------------------------------
# sp_move_topic()
#
# Move topic from one forum to another
# Values in POST variables
# ------------------------------------------------------------------
function sp_move_topic() {
	global $spVars, $spThisUser;

	$currentforumid = sp_esc_int($_POST['currentforumid']);
	$currenttopicid = sp_esc_int($_POST['currenttopicid']);
	$targetforumid	= sp_esc_int($_POST['forumid']);

	if (!sp_get_auth('move_topics', $targetforumid)) return;

	# change topic record to new forum id
	$sql = 'UPDATE '.SFTOPICS.' SET
			forum_id = '.$targetforumid."
			WHERE topic_id=$currenttopicid";
	if (spdb_query($sql) == false) {
		sp_notify(SPFAILURE, sp_text('Topic move failed'));
		return;
	}

	# change posts record(s) to new forum
	spdb_query('UPDATE '.SFPOSTS." SET
				forum_id=$targetforumid
				WHERE topic_id=$currenttopicid");

	# update post if in sfwaiting
	spdb_query("UPDATE ".SFWAITING." SET forum_id=$targetforumid WHERE topic_id=$currenttopicid");

    # flush and rebuild topic cache (since one or more posts approved)
	sp_rebuild_topic_cache();

	# rebuild forum counts for old and new forums
	sp_build_forum_index($currentforumid);
	sp_build_forum_index($targetforumid);

	# Ok - do not like doing this but....
	# There seems to have been times when a new post is made to the old forum id so we will now double check...
	$checkposts = spdb_table(SFPOSTS, "forum_id=$currentforumid AND topic_id=$currenttopicid", 'post_id');
	if ($checkposts) {
		# made after most were moved
		sp_move_topic();
	} else {
		sp_notify(SPSUCCESS, sp_text('Topic moved'));

        # notify author of move
        $thisTopic = spdb_table(SFTOPICS, "topic_id=$currenttopicid", 'row');
    	$sfadminsettings = sp_get_option('sfadminsettings');
        if ($sfadminsettings['movenotice'] && $spThisUser->ID != $thisTopic->user_id) {
            $thisPost = spdb_table(SFPOSTS, "post_id=$thisTopic->post_id", 'row');
            $forumslug = spdb_table(SFFORUMS, "forum_id=$thisTopic->forum_id", 'forum_slug');

			$nData = array();
			$nData['user_id']		= $thisTopic->user_id;
			$nData['guest_email']	= $thisPost->guest_email;
			$nData['post_id']		= $thisPost->post_id;
			$nData['link']			= sp_build_url($forumslug, $thisTopic->topic_slug, 1);
			$nData['link_text']		= $thisTopic->topic_name;
			$nData['message']		= sp_text('A topic of yours was moved to');
			$nData['expires']		= time() + (30 * 24 * 60 * 60); # 30 days; 24 hours; 60 mins; 60secs
			sp_add_notice($nData);
        }
	}

	do_action('sph_move_topic', $currenttopicid, $currentforumid, $targetforumid, $spThisUser->ID);
}

# ------------------------------------------------------------------
# sp_move_post()
#
# Move posts
# 1 move to a new topic/2 move to an existing topic
# Values in POST variables
# ------------------------------------------------------------------
function sp_move_post() {
	global $spVars, $spGlobals, $spThisUser;

	# extract data from POST
	$postid		= sp_esc_int($_POST['postid']);
	$oldtopicid = sp_esc_int($_POST['oldtopicid']);
	$oldforumid = sp_esc_int($_POST['oldforumid']);
	$action 	= sp_esc_str($_POST['moveop']);

	# determine op type - new or exsiting topic
	if (isset($_POST['makepostmove1']) || isset($_POST['makepostmove3'])) {
		# make sure forum was selected
		if (!is_numeric($_POST['forumid'])) {
			sp_notify(SPFAILURE, sp_text('Post move abandoned as no forum was selected'));
			$returnURL = sp_permalink_from_postid($postid);
			$returnURL = strtok($returnURL, '#');
			sp_redirect($returnURL);
		}

		# new topic move or exsiting topic move called from notification
		# extract data from POST
		$newforumid = sp_esc_int($_POST['forumid']);
		if (!sp_get_auth('move_posts', $oldforumid) || !sp_get_auth('move_posts', $newforumid)) return;

		if (isset($_POST['makepostmove1'])) {
			# create new topic for a new topic post move only
			$newtopicname  = sp_filter_title_save(trim($_POST['newtopicname']), SFTOPICS, 'topic_name');
			if (empty($newtopicname)) {
				sp_notify(SPFAILURE, sp_text('Post move abandoned as no topic was defined'));
				return;
			}

			# start with creating the new topic
			$newtopicslug = sp_create_slug($newtopicname, true, SFTOPICS, 'topic_slug');

			# now create the topic and post records
			$sql = 'INSERT INTO '.SFTOPICS."
				 (topic_name, topic_slug, topic_date, forum_id, post_count, post_id, post_count_held, post_id_held)
				 VALUES
				 ('$newtopicname', '$newtopicslug', now(), $newforumid, 1, $postid, 1, $postid);";
			if (spdb_query($sql) == false) {
				sp_notify(SPFAILURE, sp_text('Post move failed'));
				return;
			}
			$newtopicid = $spVars['insertid'];

			# check the topic slug and if empty use the topic id
			if (empty($newtopicslug)) {
				$newtopicslug = 'topic-'.$newtopicid;
				$thistopic = spdb_query('UPDATE '.SFTOPICS." SET
										topic_slug='$newtopicslug'
										WHERE topic_id=$newtopicid");
			}
		} else {
			# it's a re-entry
			$newtopicid = sp_esc_int($_POST['newtopicid']);
		}

		# Now determine the list of post ids to move
		$posts = array();
		switch ($action) {

			case 'single':
				$posts[] = $postid;
			break;

			case 'tostart':
				$sql = "SELECT post_id FROM ".SFPOSTS." WHERE topic_id = $oldtopicid AND post_id <= $postid";
				$posts = spdb_select('col', $sql);
			break;

			case 'toend':
				$sql = "SELECT post_id FROM ".SFPOSTS." WHERE topic_id = $oldtopicid AND post_id >= $postid";
				$posts = spdb_select('col', $sql);
			break;

			case 'select':
				$idlist = sp_esc_str(trim($_POST['idlist'], ","));
				if (empty($idlist)) {
					$posts[] = $postid;
				} else {
					$where = "topic_id = $oldtopicid AND post_index IN ($idlist)";
					$sql = "SELECT post_id FROM ".SFPOSTS." WHERE topic_id = $oldtopicid AND post_index IN ($idlist)";
					$posts = spdb_select('col', $sql);
				}
			break;
		}

		if (empty($posts)) {
			sp_notify(SPFAILURE, sp_text('Post move abandoned as no posts were selected'));
			return;
		}

		# loop through and update post records and other housekeeping
		foreach ($posts as $post) {
			# update post record
			$sql = 'UPDATE '.SFPOSTS." SET
				 	topic_id=$newtopicid,
				 	forum_id=$newforumid,
				 	post_status=0
				 	WHERE post_id=$post";
			spdb_query($sql);

        	# update post if in sfwaiting
        	spdb_query("UPDATE ".SFWAITING." SET forum_id=$newforumid, topic_id=$newtopicid WHERE post_id=$post");

            # notify author of move
            $thisPost = spdb_table(SFPOSTS, "post_id=$post", 'row');
    	    $sfadminsettings = sp_get_option('sfadminsettings');
            if ($sfadminsettings['movenotice'] && $spThisUser->ID != $thisPost->user_id) {
    			$nData = array();
    			$nData['user_id']		= $thisPost->user_id;
    			$nData['guest_email']	= $thisPost->guest_email;
    			$nData['post_id']		= $post;
    			$nData['link']			= sp_permalink_from_postid($post);
    			$nData['link_text']		= spdb_table(SFTOPICS, "topic_id=$thisPost->topic_id", 'topic_name');
    			$nData['message']		= sp_text('A post of yours was moved to');
    			$nData['expires']		= time() + (30 * 24 * 60 * 60); # 30 days; 24 hours; 60 mins; 60secs
    			sp_add_notice($nData);
            }
		}

        # flush and rebuild topic cache (since one or more posts approved)
		sp_rebuild_topic_cache();

		# rebuild indexing on target topic and forum
		sp_build_post_index($newtopicid);
		sp_build_forum_index($newforumid);

		# determine if any posts left in old topic - just in case - delete or reindex
		$sql = "SELECT post_id FROM ".SFPOSTS." WHERE topic_id = $oldtopicid";
		$posts = spdb_select('col', $sql);
		if (empty($posts)) {
			spdb_query("DELETE FROM ".SFTOPICS." WHERE topic_id=".$oldtopicid);
		} else {
			sp_build_post_index($oldtopicid);
			sp_build_forum_index($oldforumid);
		}

		do_action('sph_move_post', $oldtopicid, $newtopicid, $newforumid, $oldforumid, $postid, $spThisUser->ID);

		sp_notify(SPSUCCESS, sp_text('Post moved'));
	} elseif (isset($_POST['makepostmove2'])) {
		# must be a move to an exisiting topic action
		sp_add_sfmeta('post_move', 'post_move', $_POST, true);
	}

	if (isset($_POST['makepostmove3'])) {
		# if a re-entry for move to exisiting - clear the sfmeta record
		$meta = sp_get_sfmeta('post_move', 'post_move');
		if ($meta) {
			$id = $meta[0]['meta_id'];
			sp_delete_sfmeta($id);
			unset($spGlobals['post_move']);
		}
	}
}

add_filter('sph_UserNotices_Custom', 'sp_move_post_notice', 1, 2);
function sp_move_post_notice($m, $a) {
	global $spGlobals, $spVars, $spThisUser;
	if (array_key_exists('post_move', $spGlobals) && sp_get_auth('move_posts', $spVars['forumid'])) {
		$m.= "<div id='spPostMove'>\n";
		$m.= "<p class='".$a['textClass']."'>";
		if ($spVars['pageview'] != 'topic' || ($spVars['pageview'] == 'topic' && $spVars['topicid'] == $spGlobals['post_move']['post_move']['oldtopicid'])) {
			$m.= sp_text('You have posts queued to be moved').' - '.sp_text('Navigate to the target topic to complete the move operation');
			$m.= '</p>';
			$m.= '<form action="'.sp_build_url($spVars['forumslug'], $spVars['topicslug'], 1, 0).'" method="post" name="movepostform">';
			$m.= '<span>';
			$m.= '<input type="submit" class="spSubmit" name="cancelpostmove" value="'.sp_text('Cancel').'" />';
			$m.= '</span></form></div>';
		} else {
			$p = $spGlobals['post_move']['post_move'];
			$m.= sp_text('You have posts queued to be moved').' - '.sp_text('Click on the move button to move to this topic');
			$m.= "</p>\n";

			# create hidden form
			$m.= '<form action="'.sp_build_url($spVars['forumslug'], $spVars['topicslug'], 1, 0).'" method="post" name="movepostform">';
			$m.= '<input type="hidden" name="postid" value="'.$p['postid'].'" />';
			$m.= '<input type="hidden" name="oldtopicid" value="'.$p['oldtopicid'].'" />';
			$m.= '<input type="hidden" name="oldforumid" value="'.$p['oldforumid'].'" />';
			$m.= '<input type="hidden" name="oldpostindex" value="'.$p['oldpostindex'].'" />';
			$m.= '<input type="hidden" name="moveop" value="'.$p['moveop'].'" />';
			$m.= '<input type="hidden" name="idlist" value="'.$p['idlist'].'" />';
			$m.= '<input type="hidden" name="moveop" value="'.$p['moveop'].'" />';
			$m.= '<input type="hidden" name="forumid" value="'.$spVars['forumid'].'" />';
			$m.= '<input type="hidden" name="newtopicid" value="'.$spVars['topicid'].'" />';
			$m.= '<span>';
			$m.= '<input type="submit" class="spSubmit" name="makepostmove3" value="'.sp_text('Move').'" />';
			$m.= '<input type="submit" class="spSubmit" name="cancelpostmove" value="'.sp_text('Cancel').'" />';
			$m.= '</span></form></div>';
		}
	}
	return $m;
}

# ------------------------------------------------------------------
# sp_reassign_post()
#
# Reassign post to different user
# ------------------------------------------------------------------
function sp_reassign_post() {
	global $spVars, $spThisUser;

	if (!sp_get_auth('reassign_posts', $spVars['forumid'])) return;

	$postid = sp_esc_int($_POST['postid']);
	$olduserid = sp_esc_int($_POST['olduserid']);
	$newuserid = sp_esc_int($_POST['newuserid']);

	# transfer the post
	$sql = 'UPDATE '.SFPOSTS." SET
			user_id=$newuserid
			WHERE post_id=$postid";
	if (spdb_query($sql) == false) {
		sp_notify(SPFAILURE, sp_text('Post reassign failed'));
	} else {
		sp_notify(SPSUCCESS, sp_text('Post reassigned'));
	}

	sp_delete_notice('post_id', $postid);

	# if old post was from a user (vs guest) update old user post counts
	if (!empty($olduserid)) {
		$count = sp_get_member_item($olduserid, 'posts') - 1;
		sp_update_member_item($olduserid, 'posts', $count);
	}

	# update new user post counts
	$count = sp_get_member_item($newuserid, 'posts') + 1;
	sp_update_member_item($newuserid, 'posts', $count);

    do_action('sph_reassign_post', $postid, $olduserid, $newuserid, $spThisUser->ID);
}

# ------------------------------------------------------------------
# sp_update_opened()
#
# Updates the number of times a topic is viewed
#	$topicid:		The topic being opened for view
# ------------------------------------------------------------------
function sp_update_opened($topicid) {
	global $spVars;

	if (empty($topicid)) return;

	$current = spdb_table(SFTOPICS, "topic_id=$topicid", 'topic_opened');
	if (!$current) $current = 0;
	$current++;
	spdb_query('UPDATE '.SFTOPICS." SET
				topic_opened=$current
				WHERE topic_id=$topicid");
}

# ******************************************************************
# DELETE ITEM FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sp_delete_topic()
#
# Delete a topic and all it;s posts
#	$topicid:		The topic being deleted
#	$show:			True/False: Whether to return message (for UI)
# ------------------------------------------------------------------
function sp_delete_topic($topicid, $forumid, $show=true) {
	global $spThisUser;

	if (empty($topicid) || empty($forumid)) return '';

	if (!sp_get_auth('delete_topics', $forumid) && !sp_is_forum_admin($spThisUser->ID) && !sp_get_auth('delete_own_posts', $forumid)) return;

	# Load topic record for later index rebuild
	$row = spdb_table(SFTOPICS, "topic_id=$topicid", 'row');

	# delete from waiting just in case
	spdb_query('DELETE FROM '.SFWAITING." WHERE topic_id=$topicid");

	# now delete from topic - but grab list of posts deleted in case plugins need to know
	$posts = spdb_table(SFPOSTS, "topic_id=$topicid");
	if (spdb_query('DELETE FROM '.SFTOPICS." WHERE topic_id=$topicid") == false) {
		if ($show) sp_notify(SPFAILURE, sp_text('Deletion failed'));
		return;
	}

	# remove any user notices associated with the topic and adjust post counts if needed
	if ($posts) {
		foreach ($posts as $post) {
            $adjust = sp_get_option('post_count_delete');
            if ($adjust) {
                $count = sp_get_member_item($post->user_id, 'posts') - 1;
                sp_update_member_item($post->user_id, 'posts', $count);
            }

			sp_delete_notice('post_id', $post->post_id);
		}
	}

	# grab the forum id
	do_action('sph_topic_delete', $posts, $topicid, $spThisUser->ID);

	# now delete all the posts on the topic
	if (spdb_query('DELETE FROM '.SFPOSTS." WHERE topic_id=$topicid") == false) {
		if ($show) sp_notify(SPFAILURE, sp_text('Deletion of posts in topic failed'));
	} else {
		if ($show) sp_notify(SPSUCCESS, sp_text('Topic deleted'));
	}

   	# clear out group cache
    sp_flush_cache('group');

	# delete from forums topic count
	sp_build_forum_index($row->forum_id);

	# rebuild topic id cache
	sp_rebuild_topic_cache();

	# reset all users plugin data just in case
	sp_reset_member_plugindata();
}

# ------------------------------------------------------------------
# sp_delete_post()
#
# Delete a post
#	$postid:		The post to be deleted
#	$topicid:		The topic post belongs to
#	$forumid:		The forum post belongs to
# ------------------------------------------------------------------
function sp_delete_post($postid, $topicid=0, $forumid=0, $show=true) {
	# leaving $topicid and $forumid function arguments above though not used anymore for backwards compat in case function called by others

	global $spThisUser;

	if (!$postid) return '';

	# grab info from database for the post id so we are sure we are using common info for checks in case someone dorked with the query
	$target = spdb_table(SFPOSTS, "post_id=$postid", 'row');

	# user must have delete any post permission in this forum or delete own posts permission and be the poster of the post being deleted
	if (sp_get_auth('delete_any_post', $target->forum_id) || (sp_get_auth('delete_own_posts', $target->forum_id) && $spThisUser->ID == $target->user_id)) {
		# Check post actually exsists - might be a browsser refresh!
		if (empty($target)) {
			if ($show) sp_notify(SPSUCCESS, sp_text('Post already deleted'));
			return;
		}

		$pcount = spdb_table(SFTOPICS, "topic_id=$target->topic_id", 'post_count');

        # see if plugin wants to override the deletion
        $skip_delete = apply_filters('sph_post_delete_pre', false, $target->forum_id, $target->topic_id, $postid, $pcount);

		# if just one post then remove topic as well
		if ($pcount == 1) {
			sp_delete_topic($target->topic_id, $target->forum_id, $show);
		} else {
            if (!$skip_delete) {
    			if (spdb_query('DELETE FROM '.SFPOSTS." WHERE post_id=$postid") == false) {
    				if ($show) sp_notify(SPFAILURE, sp_text('Deletion failed'));
    			} else {
    				if ($show) sp_notify(SPSUCCESS, sp_text('Post deleted'));
    			}
            }

            # adjust post count if needed
            $adjust = sp_get_option('post_count_delete');
            if ($adjust) {
                $count = sp_get_member_item($target->user_id, 'posts') - 1;
                sp_update_member_item($target->user_id, 'posts', $count);
            }

        	# clear out group cache
            sp_flush_cache('group');

			# re number post index
			sp_build_post_index($target->topic_id);
			sp_build_forum_index($target->forum_id);

            # flush and rebuild topic cache (since one or more posts approved)
			sp_rebuild_topic_cache();

        	# reset all users plugin data just in case
        	sp_reset_member_plugindata();

			# post delete hook
			do_action('sph_post_delete', $target, $spThisUser->ID);
		}

		# need to look in sfwaiting to see if it's in there
		sp_remove_from_waiting(true, $target->topic_id, $postid);
		sp_delete_notice('post_id', $postid);
	}
}

# ******************************************************************
# EDIT TOOL ICONS
# ******************************************************************

# ------------------------------------------------------------------
# sp_lock_topic_toggle()
#
# Toggle Topic Lock
#	Topicid:		Topic to lock/unlock
#	forumid:		forum id for auth check
# ------------------------------------------------------------------
function sp_lock_topic_toggle($topicid, $forumid='') {
	if (!$topicid) return;
	if (!sp_get_auth('lock_topics', $forumid)) return;

	$status = spdb_table(SFTOPICS, "topic_id=$topicid", 'topic_status');
	$status = ($status == 1) ? 0 : 1;

	spdb_query('UPDATE '.SFTOPICS." SET topic_status=$status WHERE topic_id=$topicid");
}

# ------------------------------------------------------------------
# sp_pin_topic_toggle()
#
# Toggle Topic Pin
#	Topicid:		Topic to pin/unpin
#	forumidL		Forum id for auth check
# ------------------------------------------------------------------
function sp_pin_topic_toggle($topicid, $forumid='') {

	if (!$topicid) return;
	if (!sp_get_auth('pin_topics', $forumid)) return;

	$status = spdb_table(SFTOPICS, "topic_id=$topicid", 'topic_pinned');
	$status = ($status > 0) ? 0 : 1;

	spdb_query('UPDATE '.SFTOPICS." SET topic_pinned=$status WHERE topic_id=$topicid");
}

# ------------------------------------------------------------------
# sp_promote_pinned_topic()
#
# Promote Topic Pin ro display earlier
#	Topicid:		Topic to pin/unpin
# Added: Versionb 5.2.3
# ------------------------------------------------------------------
function sp_promote_pinned_topic() {
	if (empty($_POST['orderpinsforumid'])) return '';
	$forumid = $_POST['orderpinsforumid'];

	if (!sp_get_auth('pin_topics', $forumid)) return;

	if (!empty($_POST['topicid'])) {
		for($x=0;$x<count($_POST['topicid']);$x++) {
			if (empty($_POST['porder'][$x]) || $_POST['porder'][$x]==0) {
				$o = 1;
			} else {
				$o = $_POST['porder'][$x];
			}

			if (spdb_query('UPDATE '.SFTOPICS." SET topic_pinned=$o WHERE topic_id=".sp_esc_int($_POST['topicid'][$x])) == false) {
				sp_notify(SPFAILURE, sp_text('Ordering of pinned topic failed'));
			} else {
				sp_notify(SPSUCCESS, sp_text('Pinned topics re-ordered'));
			}
		}
	}
}

# ------------------------------------------------------------------
# sp_pin_post_toggle()
#
# Toggle Post Pin
#	postid:		Post to pin/unpin
#	forumid:	Forum for auth check
# ------------------------------------------------------------------
function sp_pin_post_toggle($postid, $forumid='') {

	if (!$postid) return '';
	if (!sp_get_auth('pin_posts', $forumid)) return;

	$status = spdb_table(SFPOSTS, "post_id=$postid", 'post_pinned');
	$status = ($status == 1) ? 0 : 1;

	spdb_query('UPDATE '.SFPOSTS." SET post_pinned=$status WHERE post_id=$postid");
}

# ------------------------------------------------------------------
# sp_approve_post()
#
# Approve a post and take it out of moderation and the queue (if allowed)
# if postid is set then work on just that post and if topicid is set
# as well, then check with waiting for removal of the one post.
# if postid is zero and topicid is set - approve all in topic.
#	$moderation		Set to true if called from moderation action
#	$postid:		the post to approve
#	$topicid		the topic to approve (if set then 'all')
#	$show			true if no return message is required
#	$forumid		added for when $spVars is not available (5.3)
# ------------------------------------------------------------------
function sp_approve_post($moderation, $postid=0, $topicid=0, $show=true, $forumid=0) {
	global $spVars, $spThisUser;

	if ($postid == 0 && $topicid == 0) return;
	if (!isset($spVars['forumid']) && $forumid==0) return;
	$forumid = (isset($spVars['forumid'])) ? $spVars['forumid'] : $forumid;

	if (!sp_get_auth('moderate_posts', $forumid)) return;

	$success = true;
	$approved_posts = array();
	if ($postid != 0) {
		if (spdb_query('UPDATE '.SFPOSTS." SET
						post_status=0
						WHERE post_id=$postid") == false) $success = false;
		if ($success) $approved_posts = array($postid);
	}

	if ($postid == 0 && $topicid != 0) {
		# get all the topic
		$postlist = spdb_select('col', 'SELECT post_id FROM '.SFPOSTS." WHERE post_status<>0 AND topic_id=$topicid");
		if (spdb_query('UPDATE '.SFPOSTS." SET
						post_status=0
						WHERE topic_id=$topicid") == false) $success = false;
		if ($success) $approved_posts = $postlist;
	}

	if ($success == false) {
		if ($show) sp_notify(SPFAILURE, sp_text('Post approval failed'));
	} else {
		if ($show) sp_notify(SPSUCCESS, sp_text('Post approved'));

		# remove from waiting
		$remove = apply_filters('sph_approve_remove_waiting', true, $moderation);
		if ($remove) sp_remove_from_waiting($moderation, $topicid, $postid);

		# remove from notices
		foreach ($approved_posts as $pid) {
			sp_delete_notice('post_id', $pid);
		}

        # flush and rebuild topic cache (since one or more posts approved)
		sp_rebuild_topic_cache();

		# finally rebuild the indexing to correct latest counts and last post id
		$forumid = spdb_table(SFTOPICS, "topic_id=$topicid", 'forum_id');

		sp_build_post_index($topicid);
		sp_build_forum_index($forumid);

		do_action('sph_post_approved', $approved_posts, $spThisUser->ID);
	}
}

# ------------------------------------------------------------------
# sp_unapprove_post()
#
# Unapproves a post and puts it into moderation
#	$postid:		the post to approve
#	$topicid		the topic to approve (if set then 'all')
#	$show			true if no return message is required
# ------------------------------------------------------------------
function sp_unapprove_post($postid=0, $show=true) {
	global $spVars, $spThisUser;

	if ($postid == 0) return;
	if (!sp_get_auth('moderate_posts', $spVars['forumid'])) return;

    $success = spdb_query('UPDATE '.SFPOSTS." SET post_status=1 WHERE post_id=$postid");

	if ($success == false) {
		if ($show) sp_notify(SPFAILURE, sp_text('Post unapproval failed'));
	} else {
		if ($show) sp_notify(SPSUCCESS, sp_text('Post unapproved'));

		# add to waiting list
		$post = spdb_select('row', 'SELECT * FROM '.SFPOSTS." WHERE post_id=$postid");
		$topic = spdb_select('row', 'SELECT * FROM '.SFTOPICS." WHERE topic_id=$post->topic_id");
   		sp_add_to_waiting($post->topic_id, $post->forum_id, $post->post_id, $post->user_id);

		$nData = array();
		$nData['user_id']		= $post->user_id;
		$nData['guest_email']	= (isset($post->guestemail)) ? $post->guestemail : '';
		$nData['post_id']		= $post->post_id;
		$nData['link']			= sp_permalink_from_postid($post->post_id);
		$nData['link_text']		= $topic->topic_name;
		$nData['message']		= sp_text('Your post is awaiting moderation in the topic');
		$nData['expires']		= time() + (30 * 24 * 60 * 60); # 30 days; 24 hours; 60 mins; 60secs
		sp_add_notice($nData);

        # flush and rebuild topic cache
		sp_rebuild_topic_cache();

		sp_build_post_index($post->topic_id);
		sp_build_forum_index($post->forum_id);

		do_action('sph_post_unapproved', $post->post_id, $spThisUser->ID);
	}
}

# Save to Admins Queue if needed ---------------------------------------------------
function sp_add_to_waiting($topicid, $forumid, $postid, $userid) {
	global $spThisUser;

	$add = apply_filters('sph_add_to_waiting', false);
	if (!$add) return;

	if (empty($userid)) $userid = 0;

	# first is this topic already in waiting?
	$result = spdb_table(SFWAITING, "topic_id=$topicid", 'row');
	if ($result) {
		# add one to post count
		$pcount = ($result->post_count + 1);
		$sql = 'UPDATE '.SFWAITING.' SET ';
		$sql.= 'post_count='.$pcount." ".', user_id='.$userid.' ';
		$sql.= 'WHERE topic_id='.$topicid.';';
		spdb_query($sql);
	} else {
        # else a new record
		$pcount = 1;
		$sql =  "INSERT INTO ".SFWAITING." ";
		$sql.= "(topic_id, forum_id, post_id, user_id, post_count) ";
		$sql.= "VALUES (";
		$sql.= $topicid.", ";
		$sql.= $forumid.", ";
		$sql.= $postid.", ";
		$sql.= $userid.", ";
		$sql.= $pcount.");";
		spdb_query($sql);
	}
}

# ------------------------------------------------------------------
# sp_remove_from_waiting()
#
# Removes an item from admins queue when it is viewed (or from Bar)
#	$moderation		Set to true if called from moderation action
#	$topicid:		the topic to remove (all posts is postid of 0)
#	$postid:		if specified removed the one post from topic
# ------------------------------------------------------------------
function sp_remove_from_waiting($moderation, $topicid, $postid=0) {
	if (empty($topicid) || $topicid==0) return;

	$remove = apply_filters('sph_remove_from_waiting', true, $moderation);
	if ($remove == true) {
		# are we removing the whole topic?
		if ($postid == 0) {
			# first check there are no posts still to be moderated in this topic...
			$rows = spdb_table(SFPOSTS, "topic_id=$topicid AND post_status > 0");
			if ($rows) {
				return;
			} else {
				spdb_query('DELETE FROM '.SFWAITING." WHERE topic_id=$topicid");
			}
		} else {
			# get the current row to see if the postid matches - and the post count is more than 1)
			$current = spdb_table(SFWAITING, "topic_id=$topicid", 'row');
			if ($current) {
				# if post count is 1 may as well delete the row
				if ($current->post_count == 1) {
					spdb_query('DELETE FROM '.SFWAITING." WHERE topic_id=$topicid");
				} elseif ($current->post_id != $postid) {
					spdb_query('UPDATE '.SFWAITING.' SET post_count='.($current->post_count-1)." WHERE topic_id=$topicid");
				} else {
					$newpostid = spdb_table(SFPOSTS, "topic_id=$topicid AND post_id > $postid", 'post_id', 'post_id DESC', '1');
					if ($newpostid) {
						spdb_query('UPDATE '.SFWAITING.' SET post_count='.($current->post_count-1).", post_id=$newpostid WHERE topic_id=$topicid");
					} else {
						spdb_query('DELETE FROM '.SFWAITING." WHERE topic_id=$topicid");
					}
				}
			}
		}
	}
}

# ------------------------------------------------------------------
# sp_remove_waiting_queue()
#
# Removes the admin queue unless a post is awaiting approval
# ------------------------------------------------------------------
function sp_remove_waiting_queue() {
	$rows = spdb_select('col', 'SELECT topic_id FROM '.SFWAITING);
	if ($rows) {
		$queued = array();
		foreach ($rows as $row) {
			$queued[] = $row;
		}
		foreach ($queued as $topic) {
			sp_remove_from_waiting(true, $topic);
		}
	}
}

# ******************************************************************
# DATA INTEGRITY MANAGEMENT
# ******************************************************************

# ------------------------------------------------------------------
# sp_build_post_index()
#
# Rebuilds the post index column (post sequence) and also sets the
# last post id and post count into the parent topic record
#	$topicid:		topic whose posts are being re-indexed
# ------------------------------------------------------------------
function sp_build_post_index($topicid, $returnmsg=false) {
	if (!$topicid) return '';

	$lastpost = NULL;
	$lastpostheld = 0;
	$postcount = 0;
	$postcountheld = 0;

	# get topic posts is their display order
	$spdb = new spdbComplex;
		$spdb->table	= SFPOSTS;
		$spdb->fields	= 'post_id, post_pinned, post_index, post_status';
		$spdb->where	= 'topic_id='.$topicid;
		$spdb->orderby	= 'post_pinned DESC, post_id ASC';
	$spdb = apply_filters('sph_postindex_select', $spdb, $topicid);
	$posts = $spdb->select();

	if ($posts) {
		$index = 1;
		foreach ($posts as $post) {
			# update the post_index for each post to set display order
			$spdb = new spdbComplex;
				$spdb->table	= SFPOSTS;
				$spdb->fields	= array('post_index');
				$spdb->data		= array($index);
				$spdb->where	= 'post_id='.$post->post_id;
			$spdb = apply_filters('sph_postindex_update', $spdb, $post, $index);
			$spdb->update();

			$lastpost = $post->post_id;
			$postcount = $index;
			if ($post->post_status == 0) {
				$lastpostheld = $lastpost;
				$postcountheld = $postcount;
			}
			$index++;
		}
	}
	# update the topic with the last post id and the post count
	spdb_query('UPDATE '.SFTOPICS." SET
				post_id=$lastpost,
				post_count=$postcount,
				post_id_held=$lastpostheld,
				post_count_held=$postcountheld
				WHERE topic_id=$topicid");

	do_action('sph_build_post_index', $topicid);

	if ($returnmsg) sp_notify(SPSUCCESS, sp_text('Verification complete'));
}

# ------------------------------------------------------------------
# sp_build_forum_index()
#
# Rebuilds the topic count and last post id in a forum record
#	$forumid:		forum needing updating
# ------------------------------------------------------------------
function sp_build_forum_index($forumid, $returnmsg=false) {
	if (!$forumid) return '';

	# get the topic count for this forum
    $query = apply_filters('sph_index_topic_count_query', "forum_id=$forumid");
	$topiccount = spdb_count(SFTOPICS, $query);

	# get the post count and post count held
    $query = apply_filters('sph_index_post_count_query', "forum_id=$forumid");
	$postcount = spdb_sum(SFTOPICS, 'post_count', $query);

    $query = apply_filters('sph_index_post_count_held_query', "forum_id=$forumid");
	$postcountheld = spdb_sum(SFTOPICS, 'post_count_held', $query);

	# get the last post id and last post held id that appeared in a topic within this forum
    $query = apply_filters('sph_index_post_id_query', "forum_id=$forumid");
	$postid = spdb_table(SFPOSTS, $query, 'post_id', 'post_id DESC', '1');

    $query = apply_filters('sph_index_post_id_held_query', "forum_id=$forumid AND post_status=0");
	$postidheld = spdb_table(SFPOSTS, $query, 'post_id', 'post_id DESC', '1');

	if (!$topiccount)	 		$topiccount = 0;
	if (!$postcount)	 		$postcount = 0;
	if (!isset($postid)) 		$postid = 'NULL';
	if (!$postcountheld)		$postcountheld = 0;
	if (!isset($postidheld)) 	$postidheld = 'NULL';

	# update forum record
	spdb_query('UPDATE '.SFFORUMS." SET
				post_id=$postid,
				post_id_held=$postidheld,
				post_count=$postcount,
				post_count_held=$postcountheld,
				topic_count=$topiccount
				WHERE forum_id=$forumid");

	do_action('sph_build_forum_index', $forumid);

	if ($returnmsg) sp_notify(SPSUCCESS, sp_text('Verification complete'));
}

# ------------------------------------------------------------------
# sp_transient_cleanup()
#
# Cleans any outdated wp transients and sp notices
# ------------------------------------------------------------------
function sp_transient_cleanup() {
	global $wpdb;

	$time = time();

    # clean up wp transients
	$sql = 'SELECT * FROM '.SF_PREFIX."options
            WHERE (option_name LIKE '_transient_timeout_%url' AND option_value < $time) OR
       		      (option_name LIKE '_transient_timeout_%bookmark' AND option_value < $time) OR
           		  (option_name LIKE '_transient_timeout_%post' AND option_value < $time) OR
                  (option_name LIKE '_transient_timeout_%search' AND option_value < $time) OR
           		  (option_name LIKE '_transient_timeout_%reload' AND option_value < $time)";
	$records = $wpdb->get_results($sql);
	foreach ($records as $record) {
		$transient = explode('_transient_timeout_', $record->option_name);
		$wpdb->query('DELETE FROM '.SF_PREFIX."options WHERE option_name='_transient_timeout_$transient[1]'");
		$wpdb->query('DELETE FROM '.SF_PREFIX."options WHERE option_name='_transient_$transient[1]'");
	}

    # clean up our user notices
	$wpdb->query('DELETE FROM '.SFNOTICES." WHERE expires < $time");
}

function sp_post_notification($user, $message, $postid) {
    global $spThisUser;

	if (!$spThisUser->admin && !$spThisUser->moderator) return;

  	$userid = spdb_table(SFMEMBERS, "display_name='$user'", 'user_id');
    if (empty($userid)) return;

    $topic_id = spdb_table(SFPOSTS, "post_id=$postid", 'topic_id');

	$nData = array();
	$nData['user_id']		= $userid;
	$nData['guest_email']	= '';
	$nData['post_id']		= $postid;
	$nData['link']			= sp_permalink_from_postid($postid);
	$nData['link_text']		= spdb_table(SFTOPICS, "topic_id=$topic_id", 'topic_name');
	$nData['message']		= sp_filter_title_save($message);
	$nData['expires']		= $time; # 30 days; 24 hours; 60 mins; 60secs
	sp_add_notice($nData);
}
?>
