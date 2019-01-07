<?php
/*
Simple:Press
Insert Topic and Post Class
$LastChangedDate: 2016-10-20 04:20:12 -0500 (Thu, 20 Oct 2016) $
$Rev: 14642 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	spPost Class
#
#	Centralised class for creation of new topics and posts
#	Version: 5.1
#
# ==========================================================================================

# Lets just make sure some base files are included just in case...

include_once SF_PLUGIN_DIR.'/forum/library/sp-post-support.php';
include_once SF_PLUGIN_DIR.'/forum/database/sp-db-newposts.php';
include_once SF_PLUGIN_DIR.'/forum/database/sp-db-management.php';

class spPost {
	# contains the topic/post data
	var $newpost	= NULL;

	# type 'topic' or 'post'
	var $action		= '';

	# calling process
	var $call		= 'post';

	# failure flag and message
	var $abort		= false;
	var $message	= '';
	var $returnURL	= '';

	# Current user (to keep this user agnostic)
	var $userid		= 0;
	var $admin		= false;
	var $moderator	= false;
	var $member		= false;
	var $guest		= false;

	# Run in class instantiation - initialises data array
	function __construct() {
		# Initialise the newpost array
		$this->newpost = array(
			# Control data
			'action'		=> '',
			'error'			=> '',
			'db'			=> 0,
			'submsg'		=> '',
			'emailprefix'	=> '',
			'url'			=> '',
			# Required for all
			'userid'		=> 0,
			'forumid'		=> 0,
			'forumslug'		=> '',
			'forumname'		=> '',
			'groupname'		=> '',
			# Topic data
			'topicid'		=> 0,
			'topicname'		=> '',
			'topicslug'		=> '',
			'topicpinned'	=> 0,
			'topicstatus'	=> 0,
			# Post data
			'postid'		=> 0,
			'postcontent'	=> '',
			'postdate'		=> current_time('mysql'),
			'guestname'		=> '',
			'guestemail'	=> '',
			'poststatus'	=> 0,
			'postpinned'	=> 0,
			'postindex'		=> 1,
			'postedit'		=> '',
			'posterip'		=> '',
			'postername'	=> '',
			'posteremail'	=> '',
			'source'		=> 0
		);
		$this->returnURL	= sp_url();
	}

	# ==========================================================================================
	# validateHuman()
	# checks to try and weed out spam bot posts
	# ------------------------------------------------------------------------------------------
	function validateHuman($postVars) {

		$this->abort = false;

		# check for filled POST variables
		if (empty($postVars['forumid']) || empty($postVars['newaction']) || empty($postVars['postitem'])) {
			$this->abort = true;
			return;
		}

		# check the WP nonce
		if ($postVars['newaction'] == 'topic') {
			check_admin_referer('forum-userform_addtopic', 'forum-userform_addtopic');
		} else {
			check_admin_referer('forum-userform_addpost', 'forum-userform_addpost');
		}

		# check for other filled POST variables
		if ($postVars['newaction'] == 'topic' && empty($postVars['newtopicname']) || ($postVars['newaction'] == 'post' && empty($postVars['topicid']))) {
			$this->abort = true;
			return;
		}
		if ($this->guest) {
			$spGuests = sp_get_option('sfguests');
			if (empty($postVars['guestname']) || ($spGuests['reqemail'] && empty($postVars['guestemail']))) {
				$this->abort = true;
				return;
			}
		}

		# Chrck the captcha value is set correctly
		$captchaValue = sp_get_option('captcha-value');
		if ($postVars['captcha'] != $captchaValue) {
			$this->abort = true;
			return;
		}

		# See if any plugin decides this can die (pass back $this->abort OR true but never false)
		$this->abort = apply_filters('sph_validate_human', $this->abort, $postVars);
	}

	# ==========================================================================================
	# validatePermission()
	# checks for base data and authorisation
	# ------------------------------------------------------------------------------------------
	function validatePermission() {
		global $spGlobals;

		$this->abort = false;

		$this->newpost['action'] = $this->action;

		# If the forum is not set then this may be a back door approach
		if (!$this->newpost['forumid'] || empty($this->newpost['forumslug'])) {
			$this->abort = true;
			$this->message = sp_text('Forum not set - Unable to create post');
			return;
		}

		# If this is a new post check topic id and slug is set
		if ($this->action == 'post') {
			if (!$this->newpost['topicid'] || empty($this->newpost['topicslug'])) {
				$this->abort = true;
				$this->message = sp_text('Topic not set - Unable to create post');
				return;
			}
		}

		# Check that current user is actually allowed to do this
		$starter = spdb_table(SFTOPICS, 'topic_id='.$this->newpost['topicid'], 'user_id');
		if (($this->action == 'topic' && !sp_get_auth('start_topics', $this->newpost['forumid'], $this->userid)) ||
            ($this->action == 'post' && sp_get_auth('reply_own_topics', $this->newpost['forumid'], $this->userid) && $starter != $this->userid) ||
            ($this->action == 'post' && !sp_get_auth('reply_topics', $this->newpost['forumid'], $this->userid))) {
			$this->abort = true;
			$this->message = sp_text('Access denied - you do not have permission');
			return;
		}

		# If forum or system locked then refuse post unless admin
		if ($this->admin == false) {
			if ($spGlobals['lockdown'] ? $slock = true : $slock = false);
			if ($slock == false) {
				if (spdb_table(SFFORUMS, 'forum_id='.$this->newpost['forumid'], 'forum_status') ? $flock = true : $flock = false);
			}
			if ($slock || $flock) {
				$this->abort = true;
				$this->message = sp_text('This forum is currently locked - access is read only');
				return;
			}
		}

		# Good so far so set up new url to return to if save fails later
		if ($this->action == 'topic') {
			$this->returnURL = sp_build_url($this->newpost['forumslug'], '', 0, 0);
			$this->newpost['started_by'] = $starter;
		} else {
			$postid = spdb_table(SFTOPICS, 'topic_id = '.$this->newpost['topicid'], 'post_id');
			$this->returnURL = sp_build_url($this->newpost['forumslug'], $this->newpost['topicslug'], 0, $postid);
			$this->newpost['started_by'] = $this->userid;
		}

        $this->newpost = apply_filters('sph_post_permissions_validation', $this->newpost);
	}

	# ==========================================================================================
	# validateData()
	# checks the data items and rules
	# ------------------------------------------------------------------------------------------
	function validateData() {
		$this->abort = false;

		$this->newpost['action'] = $this->action;

		# Check flood control (done here vice validatePermission() so we can use the return to post feature)
		if (!sp_get_auth('bypass_flood_control', $this->newpost['forumid'], $this->userid)) {
  			$flood = sp_get_cache('floodcontrol');
			if (!empty($flood) && time() < $flood) {
				$this->abort = true;
				$this->message = sp_text('Flood control exceeded, please slow down - Post cannot be saved yet');
				return;
			}
		}

		# Check topic name
		if (empty($this->newpost['topicname'])) {
			$this->abort = true;
			$this->message = sp_text('No topic name has been entered and post cannot be saved');
			return;
		} else {
			$this->newpost['topicname'] = sp_filter_title_save($this->newpost['topicname'], SFTOPICS, 'topic_name');
		}

		# Check Post Content
		if (empty($this->newpost['postcontent'])) {
			$this->abort = true;
			$this->message = sp_text('No topic post has been entered and post cannot be saved');
			return;
		} else {
			$this->newpost['postcontent_unescaped'] = sp_filter_content_save($this->newpost['postcontent'], 'new', false, SFPOSTS, 'post_content');
			$this->newpost['postcontent'] = sp_filter_content_save($this->newpost['postcontent'], 'new', true, SFPOSTS, 'post_content');
		}

		# Check and set user names/ids etc
		if ($this->guest) {
			$sfguests = sp_get_option('sfguests');
			if (empty($this->newpost['guestname']) || ((empty($this->newpost['guestemail']) || !is_email($this->newpost['guestemail'])) && $sfguests['reqemail'])) {
				$this->abort = true;
				$this->message = sp_text('Guest name and valid email address required');
				return;
			}

			# force maximum lengths
			$this->newpost['guestname'] 	= substr(sp_filter_name_save($this->newpost['guestname']), 0, 20);
			$this->newpost['guestemail'] 	= substr(sp_filter_email_save($this->newpost['guestemail']), 0, 50);
			$this->newpost['postername']	= $this->newpost['guestname'];
			$this->newpost['posteremail'] 	= $this->newpost['guestemail'];

            # check for blacklisted guest name
            $blockedGuest = sp_get_option('guest-name');
            if (!empty($blockedGuest)) {
                $names = explode(',', $blockedGuest);
                foreach ($names as $name) {
                    if (strtolower(trim($name)) == strtolower($this->newpost['guestname'])) {
        				$this->abort = true;
        				$this->message = sp_text('The guest name you have chosen is not allowed on this site');
        				return;
                    }
                }
            }

			# check that the guest name is not the same as a current user
			$checkdupe = spdb_table(SFMEMBERS, "display_name='".$this->newpost['guestname']."'", 'display_name');
			if (!empty($checkdupe)) {
				$this->abort = true;
				$this->message = sp_text('This user name already belongs to a forum member');
				return;
			}
		}

		# Check if links allowed or if maxmium links have been exceeded
		$sffilters = sp_get_option('sffilters');
		if (!$this->admin) {
			$links = $this->count_links();
			if (sp_get_auth('create_links', $this->newpost['forumid'], $this->userid)) {
				if ($sffilters['sfmaxlinks'] > 0 && $links > $sffilters['sfmaxlinks']) {
					$this->abort = true;
					$this->message = sp_text('Maximum number of allowed links exceeded').': '.$sffilters['sfmaxlinks'].' '.sp_text('allowed');
					return;
				}
			} else {
				if ($links > 0) {
					$this->abort = true;
					$this->message = sp_text('You are not allowed to put links in post content');
					return;
				}
			}
		}

		# Check if maxmium smileys have been exceeded
		if (!$this->admin) {
			if (isset($sffilters['sfmaxsmileys']) && $sffilters['sfmaxsmileys'] > 0 && $this->count_smileys() > $sffilters['sfmaxsmileys']) {
				$this->abort = true;
				$this->message = sp_text('Maximum number of allowed smileys exceeded').': '.$sffilters['sfmaxsmileys'].' '.sp_text('allowed');
				return;
			}
		}

		# Check for duplicate post of option is set
		if (($this->member && $sffilters['sfdupemember'] == true) || ($this->guest && $sffilters['sfdupeguest'] == true)) {
			# But not admin or moderator
			if (!$this->admin && !$this->moderator) {
				$dupecheck = spdb_table(SFPOSTS, 'forum_id = '.$this->newpost['forumid'].' AND topic_id='.$this->newpost['topicid']." AND post_content='".$this->newpost['postcontent']."' AND poster_ip='".$this->newpost['posterip']."'", 'row', '', '', ARRAY_A);
				if ($dupecheck) {
					$this->abort = true;
					$this->message = sp_text('Duplicate post refused');
					return;
				}
			}
		}

		# Establish moderation status
		$bypassAll  = sp_get_auth('bypass_moderation', $this->newpost['forumid'], $this->userid);
		$bypassOnce = sp_get_auth('bypass_moderation_once', $this->newpost['forumid'], $this->userid);
		if ($bypassAll == true && $bypassOnce == true) {
			$this->newpost['poststatus'] = 0;
		} else if ($bypassAll == false && $bypassOnce == false) {
			$this->newpost['poststatus'] = 1;
		} else if ($bypassAll == true && $bypassOnce == false) {
			$this->newpost['poststatus'] = 1;
			if ($this->member) {
				$prior = spdb_table(SFPOSTS, 'user_id='.$this->newpost['userid'].' AND post_status=0', 'row', '', '1');
				if ($prior) $this->newpost['poststatus'] = 0;
			} else if ($this->guest) {
				$prior = spdb_table(SFPOSTS, "guest_name='".$this->newpost['guestname']."' AND guest_email='".$this->newpost['guestemail']."' AND post_status=0", 'row', '', '1');
				if ($prior) $this->newpost['poststatus'] = 0;
			}
		} else {
			$this->newpost['poststatus'] = 1;
		}

		# Finally one or two other data items
		if ($this->action == 'topic') {
			$this->newpost['topicslug'] = sp_create_slug($this->newpost['topicname'], true, SFTOPICS, 'topic_slug');
		} else {
			$this->newpost['emailprefix'] = 'Re: ';
		}

		$this->newpost['groupname'] = sp_get_group_name_from_forum($this->newpost['forumid']);
		if (empty($this->newpost['forumname'])) {
			$this->newpost['forumname'] = spdb_table(SFFORUMS, "forum_slug='".$this->newpost['forumslug']."'", 'forum_name');
		}

        $this->newpost = apply_filters('sph_post_data_validation', $this->newpost);

		do_action('sph_pre_post_create', $this->newpost);
		$this->newpost = apply_filters('sph_new_forum_post', $this->newpost);
	}

	# ==========================================================================================
	# saveData()
	# Save topic/post to the database
	# ------------------------------------------------------------------------------------------
	function saveData() {
	    global $spVars, $spGlobals;

		$this->abort = false;

	    $this->newpost['action'] = $this->action;

        # make the entire class object available for modification before saving
        # warning:  note the passing by reference.  other end could wreak havoc
        do_action_ref_array('sph_new_post_pre_save', array(&$this));

		# Write the topic if needed
		if ($this->action == 'topic') {
		    $this->newpost = apply_filters('sph_new_topic_pre_data_saved', $this->newpost);

			$spdb = new spdbComplex;
				$spdb->table	= SFTOPICS;
				$spdb->fields	= array('topic_name', 'topic_slug', 'topic_date', 'forum_id', 'topic_status', 'topic_pinned', 'user_id');
				$spdb->data		= array($this->newpost['topicname'], $this->newpost['topicslug'], $this->newpost['postdate'], $this->newpost['forumid'], $this->newpost['topicstatus'], $this->newpost['topicpinned'], $this->newpost['userid']);
			$spdb = apply_filters('sph_new_topic_data', $spdb, $this->newpost);

			$this->newpost['db'] = $spdb->insert();
			if ($this->newpost['db'] == true) {
				$this->newpost['topicid'] = $spVars['insertid'];
				$this->newpost = apply_filters('sph_new_topic_data_saved', $this->newpost);
			} else {
				$this->abort = true;
				$this->message = sp_text('Unable to save new topic record');
				return;
			}

			# failsafe: check the topic slug and if empty use the topic id
			if (empty($this->newpost['topicslug'])) {
				$this->newpost['topicslug'] = 'topic-'.$this->newpost['topicid'];
				spdb_query('UPDATE '.SFTOPICS." SET topic_slug='".$this->newpost['topicslug']."' WHERE topic_id=".$this->newpost['topicid']);
			}

			# add newest topic_id to sfforums record
			spdb_query('UPDATE '.SFFORUMS." SET last_topic_id=".$this->newpost['topicid']." WHERE forum_id=".$this->newpost['forumid']);
		}

		# Write the post
		# Double check forum id is correct - it has been known for a topic to have just been moved!
		$this->newpost['forumid'] = spdb_table(SFTOPICS, 'topic_id='.$this->newpost['topicid'], 'forum_id');

		# Get post count in topic to enable post index setting
		$index = spdb_count(SFPOSTS, 'topic_id = '.$this->newpost['topicid']);
		$index++;
		$this->newpost['postindex'] = $index;

		# if topic lock set in post reply update topic (post only)
		if ($this->action == 'post' && $this->newpost['topicstatus']) spdb_query('UPDATE '.SFTOPICS.' SET topic_status=1 WHERE topic_id='.$this->newpost['topicid']);

		$this->newpost = apply_filters('sph_new_post_pre_data_saved', $this->newpost);

		$spdb = new spdbComplex;
			$spdb->table	= SFPOSTS;
			$spdb->fields	= array('post_content', 'post_date', 'topic_id', 'forum_id', 'user_id', 'guest_name', 'guest_email', 'post_pinned', 'post_index', 'post_status', 'poster_ip', 'source');
			$spdb->data		= array($this->newpost['postcontent'], $this->newpost['postdate'], $this->newpost['topicid'], $this->newpost['forumid'], $this->newpost['userid'], $this->newpost['guestname'], $this->newpost['guestemail'], $this->newpost['postpinned'], $this->newpost['postindex'], $this->newpost['poststatus'], $this->newpost['posterip'], $this->newpost['source']);
		$spdb = apply_filters('sph_new_post_data', $spdb, $this->newpost);

		$this->newpost['db'] = $spdb->insert();

		if ($this->newpost['db'] == true) {
			$this->newpost['postid'] = $spVars['insertid'];
			$this->newpost = apply_filters('sph_new_post_data_saved', $this->newpost);
		} else {
			$this->abort = true;
			$this->message = sp_text('Unable to save new post message');
			return;
		}

		# Update the timestamp of the last post
		sp_update_option('poststamp', $this->newpost['postdate']);

		# Update forum, topic and post index data
		# NOTE: Moved to this location for 5.6 to allow for better threading experience
		sp_build_post_index($this->newpost['topicid']);
		sp_build_forum_index($this->newpost['forumid']);

		$this->returnURL = sp_build_url($this->newpost['forumslug'], $this->newpost['topicslug'], 0, $this->newpost['postid']);
		if ($this->newpost['poststatus']) $this->newpost['submsg'] .= ' - '.sp_text('placed in moderation').' ';

		# Now for all that post-save processing required
		if ($this->guest) {
    		$sfguests = sp_get_option('sfguests');
    		if ($sfguests['storecookie']) sp_write_guest_cookie($this->newpost['guestname'], $this->newpost['guestemail']);
		} else {
			$postcount = sp_get_member_item($this->newpost['userid'], 'posts');
			$postcount++;
			sp_update_member_item($this->newpost['userid'], 'posts', $postcount);

			# see if postcount qualifies member for new user group membership
			# get rankings information
			if (!$this->admin) { # ignore for admins as they dont belong to user groups
				global $spGlobals;
				if (!empty($spGlobals['forum_rank'])) {
					$index = 0;
					foreach ($spGlobals['forum_rank'] as $x => $info) {
						$rankdata['title'][$index] = $x;
						$rankdata['posts'][$index] = $info['posts'];
						$rankdata['usergroup'][$index] = $info['usergroup'];
						$index++;
					}

					# sort rankings
					array_multisort($rankdata['posts'], SORT_ASC, $rankdata['title'], $rankdata['usergroup']);

					# check for new ranking
					for ($x = 0; $x < count($rankdata['posts']); $x++) {
						if ($postcount <= $rankdata['posts'][$x] && !empty($rankdata['usergroup'][$x])) {
							# if a user group is tied to forum rank add member to the user group
							if ($rankdata['usergroup'][$x] != 'none') {
							    sp_add_membership($rankdata['usergroup'][$x], $this->newpost['userid']);
                            }
							break;  # only update highest rank
						}
					}
				}
			}
		}

		# set new url for email
		$this->newpost['url'] = $this->returnURL;

		# allow plugins to add to post message
		$this->newpost['submsg'] = apply_filters('sph_post_message', $this->newpost['submsg'], $this->newpost);

		# add to or remove from admins new post queue
		if ($this->admin || $this->moderator) {
			# remove topic from waiting...
			sp_remove_from_waiting(false, $this->newpost['topicid']);
		} else {
			# add topic to waiting
			sp_add_to_waiting($this->newpost['topicid'], $this->newpost['forumid'], $this->newpost['postid'], $this->newpost['userid']);
		}

		# if a new post remove topic from the users new post list if in it
		if ($this->action == 'post') {
			sp_remove_users_newposts($this->newpost['topicid'], $this->newpost['userid'], false);
		}

        # do we need to approve any posts in moderation in this topic?
        if (($this->admin && $spGlobals['admin']['sfadminapprove']) || ($this->moderator && $spGlobals['admin']['sfmoderapprove'])) {
            sp_approve_post(true, 0, $this->newpost['topicid'], false, $this->newpost['forumid']);
        }

		# if post in moderatiuon then add entry to notices
		if ($this->newpost['poststatus'] != 0) {
			$nData = array();
			$nData['user_id']		= $this->newpost['userid'];
			$nData['guest_email']	= $this->newpost['guestemail'];
			$nData['post_id']		= $this->newpost['postid'];
			$nData['link']			= $this->newpost['url'];
			$nData['link_text']		= $this->newpost['topicname'];
			$nData['message']		= sp_text('Your post is awaiting moderation in the topic');
			$nData['expires']		= time() + (30 * 24 * 60 * 60); # 30 days; 24 hours; 60 mins; 60secs
			sp_add_notice($nData);
		}

		# Add this new item to the new tpic/post cache
		$meta = sp_get_sfmeta_key('topic_cache', 'new');
		$cacheSize = sp_get_option('topic_cache');
		$a = array();
		$a[LISTFORUM]	= (int) $this->newpost['forumid'];
		$a[LISTTOPIC]	= (int) $this->newpost['topicid'];
		$a[LISTPOST]	= (int) $this->newpost['postid'];
		$a[LISTSTATUS]	= (int) $this->newpost['poststatus'];

		if (isset($spGlobals['topic_cache']['new']) && !empty($spGlobals['topic_cache']['new'])) {
			array_unshift($spGlobals['topic_cache']['new'], $a);
			if (count($spGlobals['topic_cache']['new']) > $cacheSize) {
				array_pop($spGlobals['topic_cache']['new']);
			}
		}
		sp_update_sfmeta('topic_cache', 'new', $spGlobals['topic_cache']['new'], $meta, true);

		# remove group level caches to accommodate new post
		spdb_query('DELETE FROM '.SFCACHE." WHERE cache_id LIKE '%*group'");

        # save post in cache for flood control
        sp_add_cache('floodcontrol', time() + sp_get_option('floodcontrol'));

		# send out email notifications
		sp_email_notifications($this->newpost);

		# one final filter - just in case
		do_action_ref_array('sph_post_new_completed', array(&$this));

		# and a final action hook
		do_action('sph_new_post', $this->newpost);
		do_action('sph_post_create', $this->newpost);
	}

	# ==========================================================================================
	# count_smileys()
	# determine smiley count
	# ------------------------------------------------------------------------------------------
	function count_smileys() {
		global $spPaths;
		$smileys = 0;
		$path = '/'.$spPaths['smileys'].'/';
		$smileys = substr_count($this->newpost['postcontent'], $path);

		return ($smileys);
	}

	# ==========================================================================================
	# count_links()
	# determine link count that are not smileys
	# ------------------------------------------------------------------------------------------
	function count_links() {
		global $spPaths;

		$links = 0;
		$smileys = 0;
		$links = substr_count($this->newpost['postcontent'], 'http');

		# exclude smileys
		$path = '/'.$spPaths['smileys'].'/';
		$smileys = substr_count($this->newpost['postcontent'], $path);

		return ($links - $smileys);
	}

	# ==========================================================================================
	# show()
	# for debug - display the class data object
	# ------------------------------------------------------------------------------------------
	function show() {
		ashow($this);
	}
}

?>