<?php
/*
Simple:Press
Forum View Class
$LastChangedDate: 2016-03-07 10:53:55 -0600 (Mon, 07 Mar 2016) $
$Rev: 14036 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#	Version: 5.0
#
#	sp_this_forum()
#
#	Forum load function from the ForumView template
#
# --------------------------------------------------------------------------------------

function sp_this_forum($id=0, $page=0) {
	global $spForumView, $spThisForum, $spThisSubForum, $spThisForumSubs;
	$spForumView = new spForumView($id, $page);
	$spThisForum = $spForumView->sp_this_forum();
	$spThisForumSubs = (isset($spThisForum->subforums)) ? $spThisForum->subforums : '';
	return $spThisForum;
}

# --------------------------------------------------------------------------------------

# --------------------------------------------------------------------------------------
#
#	sp_has_subforums()
#	sp_loop_subforums()
#	sp_the_subforum()
#
#	SubForum Loop functions from the ForumView template
#
# --------------------------------------------------------------------------------------

function sp_has_subforums() {
	global $spForumView, $spThisForum, $spThisSubForum;
	return $spForumView->sp_has_subforums();
}

function sp_loop_subforums() {
	global $spForumView, $spThisForum, $spThisSubForum;
	return $spForumView->sp_loop_subforums();
}

function sp_the_subforum() {
	global $spForumView, $spThisForum, $spThisSubForum;
	$spThisSubForum = $spForumView->sp_the_subforum();
	if ($spThisSubForum->parent == $spThisForum->forum_id) {
		$spForumView->currentChild++;
	}
}

# --------------------------------------------------------------------------------------

# --------------------------------------------------------------------------------------
#
#	sp_has_topics()
#	sp_loop_topics()
#	sp_the_topics()
#
#	Topic Loop functions from the ForumView template
#
# --------------------------------------------------------------------------------------

function sp_has_topics() {
	global $spForumView;
	return $spForumView->sp_has_topics();
}

function sp_loop_topics() {
	global $spForumView;
	return $spForumView->sp_loop_topics();
}

function sp_the_topic() {
	global $spForumView, $spThisTopic;
	$spThisTopic = $spForumView->sp_the_topic();
}

function sp_is_child_subforum() {
	global $spThisForum, $spThisSubForum;
	return ($spThisForum->forum_id == $spThisSubForum->parent);
}

# --------------------------------------------------------------------------------------

# ==========================================================================================
#
#	Forum View. Forum and Topics Listing Class
#
# ==========================================================================================

class spForumView {
	# Status: 'data', 'no access', 'no data', 'sneak peek'
	var $forumViewStatus = '';

	# Forum View DB query result set
	var $pageData = array();

	# Forum single row object
	var $forumData = '';

	# The forum id passed in
	var $forumId = 0;

	# The PAGE being requested (page ID)
	var $forumPage = 0;

	# True while the subforum loop is being rendered
	var $inSubForumLoop = false;

	# Forum View DB Subforum result set
	var $pageSubForumData = array();

	# SubForum single row object
	var $subForumData = '';

	# Internal counter
	var $currentSubForum = 0;

	# Progressive count of direct children
	var $currentChild = 0;

	# Count of topic records
	var $SubForumCount = 0;

	# True while the topic loop is being rendered
	var $inTopicLoop = false;

	# Forum View DB Topics result set
	var $pageTopicData = array();

	# Topic single row object
	var $topicData = '';

	# Internal counter
	var $currentTopic = 0;

	# Count of topic records
	var $topicCount = 0;

	# Run in class instantiation - populates data
	function __construct($id=0, $page=0) {
		global $spVars;
		if (($id == 0) && (!empty($spVars['forumid']))) $id = $spVars['forumid'];
		$this->forumId = $id;

		if (($page == 0) && (!empty($spVars['page']))) $page = $spVars['page'];
		$this->forumPage = $page;

		$this->pageData = $this->sp_forumview_query($this->forumId, $this->forumPage);
		sp_display_inspector('fv_spForumView', $this->pageData);
	}

	# Return status and returns Forum data
	function sp_this_forum() {
		# Check for no access to forums or no data
		if ($this->forumViewStatus != 'data') return false;
		reset($this->pageData);
		$this->forumData = current($this->pageData);
		sp_display_inspector('fv_spThisForum', $this->forumData);
		return $this->forumData;
	}

	# True if there are Subforum records
	function sp_has_subforums() {
		if (!empty($this->forumData->subforums)) {
			$this->pageSubForumData = $this->forumData->subforums;
			$this->subForumCount = count($this->pageSubForumData);
			$this->inSubForumLoop = true;
			sp_display_inspector('fv_spThisForumSubs', $this->pageSubForumData);
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Subforum records
	function sp_loop_subforums() {
		if ($this->currentSubForum > 0) do_action_ref_array('sph_after_subforum', array(&$this));
		$this->currentSubForum++;
		if ($this->currentSubForum <= $this->subForumCount) {
			do_action_ref_array('sph_before_subforum', array(&$this));
			return true;
		} else {
			$this->inSubForumLoop = false;
			$this->currentSubForum = 0;
			$this->subForumCount = 0;
			unset($this->pageSubForumData);
			return false;
		}
	}

	# Sets array pointer and returns current SubForum data
	function sp_the_subforum() {
		$this->subForumData = current($this->pageSubForumData);
		sp_display_inspector('fv_spThisSubForum', $this->subForumData);
		next($this->pageSubForumData);
		return $this->subForumData;
	}

	# True if there are Topic records
	function sp_has_topics() {
		if (isset($this->forumData->topics) && $this->forumData->topics) {
			$this->pageTopicData = $this->forumData->topics;
			$this->topicCount = count($this->pageTopicData);
			$this->inTopicLoop = true;
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Topic records
	function sp_loop_topics() {
		if ($this->currentTopic > 0) do_action_ref_array('sph_after_topic', array(&$this));
		$this->currentTopic++;
		if ($this->currentTopic <= $this->topicCount) {
			do_action_ref_array('sph_before_topic', array(&$this));
			return true;
		} else {
			$this->inTopicLoop = false;
			$this->currentTopic = 0;
			$this->topicCount = 0;
			unset($this->pageTopicData);
			return false;
		}
	}

	# Sets array pointer and returns current Topic data
	function sp_the_topic() {
		$this->topicData = current($this->pageTopicData);
		sp_display_inspector('fv_spThisTopic', $this->topicData);
		next($this->pageTopicData);
		return $this->topicData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_forumview_query()
	#	Builds the data structure for the ForumView template
	#
	#	$forumid:	Can pass an id (or will pick up from $spVars if available)
	#	$page:		What oage are we calling for
	#
	#	Internally calls the sp_forumview_stats_query() to populate forum stats
	#
	# --------------------------------------------------------------------------------------
	function sp_forumview_query($forumid=0, $cPage=1) {
		global $spGlobals, $spThisUser;

		# do we have a valid forum id
		if ($forumid == 0) {
			$this->forumViewStatus = 'no data';
			return;
		} else {
			$this->forumViewStatus = 'no access';
			$BASEWHERE = SFFORUMS.".forum_id=$forumid";
		}

		# some setup vars
		$startlimit = 0;

		# how many topics per page?
		$tpaged = $spGlobals['display']['topics']['perpage'];
		if (!$tpaged) $tpaged = 20;

		# setup where we are in the topic list (paging)
		if ($cPage != 1) $startlimit = ((($cPage-1) * $tpaged));
		$LIMIT = $startlimit.', '.$tpaged;

		# Set up where clause
		if (sp_get_auth('moderate_posts', $forumid)) {
			$COLUMN = SFTOPICS.'.post_id';
			$WHERE = $BASEWHERE;
		} else {
			$COLUMN = SFTOPICS.'.post_id_held';
			$WHERE = $BASEWHERE.' AND '.SFTOPICS.'.post_count_held > 0';
		}

		# Set up order by
		$reverse = false;
		$setSort = $spGlobals['display']['topics']['sortnewtop'];
		if (isset($spGlobals['sort_order']['forum'])) {
			$reverse = (array_search($forumid, (array) $spGlobals['sort_order']['forum']) !== false) ? true : false;
		}
		if (isset($spThisUser->topicASC) && $spThisUser->topicASC) {
			$reverse = !$reverse;
		}
		if ($setSort XOR $reverse) {
			$ORDER = 'topic_pinned DESC, '.$COLUMN.' DESC';
		} else {
			$ORDER = 'topic_pinned DESC, '.$COLUMN.' ASC';
		}

		# retrieve forum and topic records
		$spdb = new spdbComplex;
			$spdb->table	= SFTOPICS;
			$spdb->fields	= SFTOPICS.'.forum_id, forum_slug, forum_name, forum_status, group_id, topic_count, forum_icon, topic_icon, topic_icon_new, topic_icon_locked, topic_icon_pinned, topic_icon_pinned_new, forum_desc, forum_rss,
							forum_rss_private, parent, children, forum_message, forum_disabled, keywords,
							'.SFTOPICS.'.topic_id, topic_slug, topic_name, topic_status, topic_pinned,
							topic_opened, '.SFTOPICS.'.post_id, '.SFTOPICS.'.post_count';
			$spdb->join		= array(SFFORUMS.' ON '.SFTOPICS.'.forum_id = '.SFFORUMS.'.forum_id');
			$spdb->where	= $WHERE;
			$spdb->orderby	= $ORDER;
			$spdb->limits	= $LIMIT;
		$spdb = apply_filters('sph_forumview_query', $spdb, $this);

		if (!empty($spThisUser->inspect['q_spForumView'])) {
			$spdb->inspect = 'spForumView';
			$spdb->show = true;
		}

		$records = $spdb->select();

		$f = array();
		if ($records) {
			$this->forumViewStatus = 'no access';
			$fidx = $forumid;
			$tidx = 0;

			# define topic id array to collect forum stats and tags
			$t = array();

			if (sp_can_view($forumid, 'topic-title')) {
				$this->forumViewStatus = 'data';

				# construct the parent forum object
				$r = current($records);
				$f[$fidx] = new stdClass();
				$f[$fidx]->forum_id				= $r->forum_id;
				$f[$fidx]->forum_slug			= $r->forum_slug;
				$f[$fidx]->forum_name			= sp_filter_title_display($r->forum_name);
				$f[$fidx]->forum_permalink		= sp_build_url($r->forum_slug, '', 0, 0);
				$f[$fidx]->forum_desc			= sp_filter_title_display($r->forum_desc);
				$f[$fidx]->forum_status			= $r->forum_status;
				$f[$fidx]->forum_disabled		= $r->forum_disabled;
				$f[$fidx]->group_id				= $r->group_id;
				$f[$fidx]->topic_count			= $r->topic_count;
				$f[$fidx]->forum_icon			= sanitize_file_name($r->forum_icon);
				$f[$fidx]->topic_icon			= sanitize_file_name($r->topic_icon);
				$f[$fidx]->topic_icon_new		= sanitize_file_name($r->topic_icon_new);
				$f[$fidx]->topic_icon_locked	= sanitize_file_name($r->topic_icon_locked);
				$f[$fidx]->topic_icon_pinned	= sanitize_file_name($r->topic_icon_pinned);
				$f[$fidx]->topic_icon_pinned_new= sanitize_file_name($r->topic_icon_pinned_new);
				$f[$fidx]->parent				= $r->parent;
				$f[$fidx]->children				= $r->children;
				$f[$fidx]->forum_message		= sp_filter_text_display($r->forum_message);
				$f[$fidx]->forum_keywords		= sp_filter_title_display($r->keywords);
				$f[$fidx]->forum_rss			= esc_url($r->forum_rss);
				$f[$fidx]->forum_rss_private	= $r->forum_rss_private;
				$f[$fidx]->display_page			= $this->forumPage;
				$f[$fidx]->tools_flag			= 1;
				$f[$fidx]->unread				= 0;

				# Can the user create new topics or should we lock the forum?
				$f[$fidx]->start_topics			= sp_get_auth('start_topics', $r->forum_id);

				$f[$fidx] = apply_filters('sph_forumview_forum_record', $f[$fidx], $r);

				reset($records);

				# now loop through the topic records
				$firstTopicPage = 1;
				$pinned = 0;
				foreach ($records as $r) {
					$tidx = $r->topic_id;
					$t[] = $tidx;
					$f[$fidx]->topics[$tidx] = new stdClass();
					$f[$fidx]->topics[$tidx]->topic_id			= $r->topic_id;
					$f[$fidx]->topics[$tidx]->topic_slug		= $r->topic_slug;
					$f[$fidx]->topics[$tidx]->topic_name		= sp_filter_title_display($r->topic_name);
					$f[$fidx]->topics[$tidx]->topic_permalink	= sp_build_url($r->forum_slug, $r->topic_slug, 1, 0);
					$f[$fidx]->topics[$tidx]->topic_status		= $r->topic_status;
					$f[$fidx]->topics[$tidx]->topic_pinned		= $r->topic_pinned;
					$f[$fidx]->topics[$tidx]->topic_opened		= $r->topic_opened;
					$f[$fidx]->topics[$tidx]->post_id			= $r->post_id;
					$f[$fidx]->topics[$tidx]->post_count		= $r->post_count;
					$f[$fidx]->topics[$tidx]->unread			= 0;
					$f[$fidx]->topics[$tidx]->last_topic_on_page= 0;
					$f[$fidx]->topics[$tidx]->first_topic_on_page=$firstTopicPage;
					$f[$fidx]->topics[$tidx]->first_pinned		= 0;
					$f[$fidx]->topics[$tidx]->last_pinned		= 0;

					# Can the user create new topics or should we lock the forum?
					$f[$fidx]->topics[$tidx]->reply_topics		= sp_get_auth('reply_topics', $fidx);

					# pinned status
					if ($firstTopicPage == 1 && $r->topic_pinned) {
						$f[$fidx]->topics[$tidx]->first_pinned = true;
						$pinned = $tidx;
					}
					if ($firstTopicPage == 0 && $pinned > 0 && $r->topic_pinned == false) {
						$f[$fidx]->topics[$pinned]->last_pinned = true;
					} elseif ($r->topic_pinned) {
						$pinned = $tidx;
					}

					$firstTopicPage = 0;

					# See if this topic is in the current users newpost list
					if ($spThisUser->member && !empty($spThisUser->newposts) && is_array($spThisUser->newposts['topics']) && in_array($tidx, $spThisUser->newposts['topics'])) $f[$fidx]->topics[$tidx]->unread = 1;

					$f[$fidx]->topics[$tidx] = apply_filters('sph_forumview_topic_records', $f[$fidx]->topics[$tidx], $r);
				}
				$f[$fidx]->topics[$tidx]->last_topic_on_page = 1;
				unset($records);

				# Collect any forum subforms that may exist
				if ($f[$fidx]->children) {
					$topSubs = unserialize($f[$fidx]->children);
					foreach ($topSubs as $topSub) {
						$topSubA = array();
						$topSubA[] = $topSub;
						$subs = $this->sp_forumview_subforums_query($topSubA, true);
					}
					if ($subs) $f = $this->sp_forumview_build_subforums($forumid, $f, $fidx, $subs);
				}

				# allow plugins to add more data to combined forum/topic data structure
				$f[$fidx] = apply_filters('sph_forumview_combined_data', $f[$fidx], $t);

				# Collect first and last post stats for each topic
				$stats = $this->sp_forumview_stats_query($t, $forumid);
				if ($stats) {
					foreach ($stats as $s) {
						if ($s->post_index == 1) {
							$f[$fidx]->topics[$s->topic_id]->first_post_id			= $s->post_id;
							$f[$fidx]->topics[$s->topic_id]->first_post_permalink	= sp_build_url($f[$fidx]->forum_slug, $f[$fidx]->topics[$s->topic_id]->topic_slug, 0, $s->post_id, $s->post_index);
							$f[$fidx]->topics[$s->topic_id]->first_post_date		= $s->post_date;
							$f[$fidx]->topics[$s->topic_id]->first_post_status		= $s->post_status;
							$f[$fidx]->topics[$s->topic_id]->first_post_index		= $s->post_index;
							$f[$fidx]->topics[$s->topic_id]->first_user_id			= $s->user_id;
							$f[$fidx]->topics[$s->topic_id]->first_display_name		= sp_filter_name_display($s->display_name);
							$f[$fidx]->topics[$s->topic_id]->first_guest_name		= sp_filter_name_display($s->guest_name);

							# see if we can display the tooltip
							if (sp_can_view($forumid, 'post-content', $spThisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
								$f[$fidx]->topics[$s->topic_id]->first_post_tip = ($s->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($s->post_content, $s->post_status);
							} else {
								$f[$fidx]->topics[$s->topic_id]->first_post_tip = '';
							}
						}
						if ($s->post_index > 1 || $f[$fidx]->topics[$s->topic_id]->post_count == 1) {
							$f[$fidx]->topics[$s->topic_id]->last_post_id			= $s->post_id;
							$f[$fidx]->topics[$s->topic_id]->last_post_permalink	= sp_build_url($f[$fidx]->forum_slug, $f[$fidx]->topics[$s->topic_id]->topic_slug, 0, $s->post_id, $s->post_index);
							$f[$fidx]->topics[$s->topic_id]->last_post_date			= $s->post_date;
							$f[$fidx]->topics[$s->topic_id]->last_post_status		= $s->post_status;
							$f[$fidx]->topics[$s->topic_id]->last_post_index		= $s->post_index;
							$f[$fidx]->topics[$s->topic_id]->last_user_id			= $s->user_id;
							$f[$fidx]->topics[$s->topic_id]->last_display_name		= sp_filter_name_display($s->display_name);
							$f[$fidx]->topics[$s->topic_id]->last_guest_name		= sp_filter_name_display($s->guest_name);

							# see if we can display the tooltip
							if (sp_can_view($forumid, 'post-content', $spThisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
								$f[$fidx]->topics[$s->topic_id]->last_post_tip = ($s->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($s->post_content, $s->post_status);
							} else {
								$f[$fidx]->topics[$s->topic_id]->last_post_tip = '';
							}
						}
						$f[$fidx]->topics[$s->topic_id] = apply_filters('sph_forumview_stats_records', $f[$fidx]->topics[$s->topic_id], $s);
					}
					unset($stats);
				}
			} else {
				# check for view forum lists but not topic lists
				if (sp_can_view($forumid, 'forum-title')) $this->forumViewStatus = 'sneak peek';
			}
		} else {
			$records = spdb_table(SFFORUMS, $BASEWHERE);
			$r = current($records);
			if ($r) {
				if (sp_can_view($forumid, 'topic-title')) {
					$this->forumViewStatus = 'data';
					$f[$forumid] = new stdClass();
					$f[$forumid]->forum_id				= $r->forum_id;
					$f[$forumid]->forum_slug			= $r->forum_slug;
					$f[$forumid]->forum_name			= sp_filter_title_display($r->forum_name);
					$f[$forumid]->forum_permalink		= sp_build_url($r->forum_slug, '', 0, 0);
					$f[$forumid]->forum_desc			= sp_filter_title_display($r->forum_desc);
					$f[$forumid]->forum_status			= $r->forum_status;
					$f[$forumid]->forum_disabled		= $r->forum_disabled;
					$f[$forumid]->group_id				= $r->group_id;
					$f[$forumid]->topic_count			= $r->topic_count;
					$f[$forumid]->forum_icon			= sanitize_file_name($r->forum_icon);
					$f[$forumid]->forum_icon_new		= sanitize_file_name($r->forum_icon_new);
					$f[$forumid]->topic_icon			= sanitize_file_name($r->topic_icon);
					$f[$forumid]->topic_icon_new		= sanitize_file_name($r->topic_icon_new);
					$f[$forumid]->topic_icon_locked		= sanitize_file_name($r->topic_icon_locked);
					$f[$forumid]->topic_icon_pinned		= sanitize_file_name($r->topic_icon_pinned);
					$f[$forumid]->topic_icon_pinned_new	= sanitize_file_name($r->topic_icon_pinned_new);
					$f[$forumid]->parent				= $r->parent;
					$f[$forumid]->children				= $r->children;
					$f[$forumid]->forum_message			= sp_filter_text_display($r->forum_message);
					$f[$forumid]->forum_keywords		= sp_filter_title_display($r->keywords);
					$f[$forumid]->forum_rss				= esc_url($r->forum_rss);
					$f[$forumid]->forum_rss_private		= $r->forum_rss_private;

					# Can the user create new topics or should we lock the forum?
					$f[$forumid]->start_topics			= sp_get_auth('start_topics', $r->forum_id);

					$f[$forumid] = apply_filters('sph_forumview_forum_record', $f[$forumid], $r);
				} else {
					# check for view forum lists but not topic lists
					if (sp_can_view($forumid, 'forum-title')) $this->forumViewStatus = 'sneak peek';
				}


				# Collect any forum subforms that may exist
				if (isset($f[$forumid]->children) && $f[$forumid]->children) {
					$topSubs = unserialize($f[$forumid]->children);
					foreach ($topSubs as $topSub) {
						$topSubA = array();
						$topSubA[] = $topSub;
						$subs = $this->sp_forumview_subforums_query($topSubA, true);
					}
					if ($subs) {
						$f = $this->sp_forumview_build_subforums($forumid, $f, $forumid, $subs);
					}
				}

				# allow plugins to add more data to combined forum/topic data structure
				$f[$forumid] = apply_filters('sph_forumview_combined_data', $f[$forumid], array());
			} else {
				# reset status to 'no data'
				$this->forumViewStatus = 'no data';
			}
		}
		return $f;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_forumview_build_subforums()
	#	Builds sub-forum object array for the ForumView template
	#
	#	$subs:	Array of the children/sub forum ids for the forum in forum view
	#
	# --------------------------------------------------------------------------------------
	function sp_forumview_build_subforums($forumid, $f, $fidx, $subs) {
		global $spThisUser;

		ksort($subs);

		foreach ($subs as $sub) {
			if (sp_can_view($sub->forum_id, 'topic-title')) {
				$f[$fidx]->subforums[$sub->forum_id] = new stdClass();
				$f[$fidx]->subforums[$sub->forum_id]->top_parent		= $fidx;
				$f[$fidx]->subforums[$sub->forum_id]->top_sub_parent	= $sub->topSubParent;
				$f[$fidx]->subforums[$sub->forum_id]->forum_id			= $sub->forum_id;
				$f[$fidx]->subforums[$sub->forum_id]->forum_id_sub		= 0;
				$f[$fidx]->subforums[$sub->forum_id]->forum_name		= sp_filter_title_display($sub->forum_name);
				$f[$fidx]->subforums[$sub->forum_id]->forum_permalink	= sp_build_url($sub->forum_slug, '', 1, 0);
				$f[$fidx]->subforums[$sub->forum_id]->forum_slug		= $sub->forum_slug;
				$f[$fidx]->subforums[$sub->forum_id]->forum_desc		= sp_filter_title_display($sub->forum_desc);
				$f[$fidx]->subforums[$sub->forum_id]->forum_status		= $sub->forum_status;
				$f[$fidx]->subforums[$sub->forum_id]->forum_disabled	= $sub->forum_disabled;
				$f[$fidx]->subforums[$sub->forum_id]->forum_icon		= sanitize_file_name($sub->forum_icon);
				$f[$fidx]->subforums[$sub->forum_id]->forum_icon_new	= sanitize_file_name($sub->forum_icon_new);
				$f[$fidx]->subforums[$sub->forum_id]->topic_icon		= sanitize_file_name($sub->topic_icon);
				$f[$fidx]->subforums[$sub->forum_id]->topic_icon_new	= sanitize_file_name($sub->topic_icon_new);
				$f[$fidx]->subforums[$sub->forum_id]->topic_icon_locked = sanitize_file_name($sub->topic_icon_locked);
				$f[$fidx]->subforums[$sub->forum_id]->topic_icon_pinned = sanitize_file_name($sub->topic_icon_pinned);
				$f[$fidx]->subforums[$sub->forum_id]->topic_icon_pinned_new = sanitize_file_name($sub->topic_icon_pinned_new);
				$f[$fidx]->subforums[$sub->forum_id]->forum_rss_private = $sub->forum_rss_private;
				$f[$fidx]->subforums[$sub->forum_id]->post_id			= $sub->post_id;
				$f[$fidx]->subforums[$sub->forum_id]->post_id_held		= $sub->post_id_held;
				$f[$fidx]->subforums[$sub->forum_id]->topic_count		= $sub->topic_count;
				$f[$fidx]->subforums[$sub->forum_id]->topic_count_sub	= $sub->topic_count;
				$f[$fidx]->subforums[$sub->forum_id]->post_count		= $sub->post_count;
				$f[$fidx]->subforums[$sub->forum_id]->post_count_sub	= $sub->post_count;
				$f[$fidx]->subforums[$sub->forum_id]->post_count_held	= $sub->post_count_held;
				$f[$fidx]->subforums[$sub->forum_id]->parent			= $sub->parent;
				$f[$fidx]->subforums[$sub->forum_id]->children			= $sub->children;
				$f[$fidx]->subforums[$sub->forum_id]->unread			= 0;

				# Can the user create new topics or should we lock the forum?
				$f[$fidx]->subforums[$sub->forum_id]->start_topics		= sp_get_auth('start_topics', $sub->forum_id);

				# See if any forums are in the current users newpost list
				if ($spThisUser->member) {
					$c = 0;
					if ($spThisUser->newposts && $spThisUser->newposts['forums']) {
						foreach ($spThisUser->newposts['forums'] as $fnp) {
							if ($fnp == $sub->forum_id) $c++;
						}
					}
					$f[$fidx]->subforums[$sub->forum_id]->unread = $c;
				}

				# check if we can look at posts in moderation - if not swap for 'held' values
				if (!sp_get_auth('moderate_posts', $sub->forum_id)) {
					$f[$fidx]->subforums[$sub->forum_id]->post_id		= $sub->post_id_held;
					$f[$fidx]->subforums[$sub->forum_id]->post_count	= $sub->post_count_held;
					$f[$fidx]->subforums[$sub->forum_id]->post_count_sub= $sub->post_count_held;
					$thisPostid = $sub->post_id_held;
				} else {
					$thisPostid = $sub->post_id;
				}

				# Build post id array for collecting stats at the end
				if (!empty($thisPostid)) $p[$sub->forum_id] = $thisPostid;

				# if this subforum has a parent that is differemt to the main forum being dislayed in the view
				# then it has to be a nested subforum so do we need to merge the numbers?
				if ($sub->parent != $forumid) {
					$f[$fidx]->subforums[$sub->parent]->topic_count_sub += $f[$fidx]->subforums[$sub->forum_id]->topic_count;
					$f[$fidx]->subforums[$sub->parent]->post_count_sub += $f[$fidx]->subforums[$sub->forum_id]->post_count;

					# and what about the most recent post? Is this in a nested subforum?
					if ($f[$fidx]->subforums[$sub->forum_id]->post_id > $f[$fidx]->subforums[$sub->parent]->post_id) {
						# store the alternative forum id in case we need to display the topic data for this one if inc. subs
						$f[$fidx]->subforums[$sub->parent]->forum_id_sub = $sub->forum_id;
					}
				}
			}
		}

		# Go grab the sub forum stats and data
		if (!empty($p)) {
			$stats = $this->sp_subforumview_stats_query($p);
			if ($stats) {
				$s = '';
				foreach ($subs as $sub) {
					if (!empty($stats[$sub->forum_id])) {
						$s = $stats[$sub->forum_id];
						$f[$fidx]->subforums[$sub->forum_id]->topic_id			= $s->topic_id;
						$f[$fidx]->subforums[$sub->forum_id]->topic_name		= sp_filter_title_display($s->topic_name);
						$f[$fidx]->subforums[$sub->forum_id]->topic_slug		= $s->topic_slug;
						$f[$fidx]->subforums[$sub->forum_id]->post_id			= $s->post_id;
						$f[$fidx]->subforums[$sub->forum_id]->post_permalink	= sp_build_url($f[$fidx]->subforums[$sub->forum_id]->forum_slug, $s->topic_slug, 0, $s->post_id, $s->post_index);
						$f[$fidx]->subforums[$sub->forum_id]->post_date			= $s->post_date;
						$f[$fidx]->subforums[$sub->forum_id]->post_status		= $s->post_status;
						$f[$fidx]->subforums[$sub->forum_id]->post_index		= $s->post_index;

						# see if we can display the tooltip
						if (sp_can_view($sub->forum_id, 'post-content', $spThisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
							$f[$fidx]->subforums[$sub->forum_id]->post_tip = ($s->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($s->post_content, $s->post_status);
						} else {
							$f[$fidx]->subforums[$sub->forum_id]->post_tip = '';
						}

						$f[$fidx]->subforums[$sub->forum_id]->user_id			= $s->user_id;
						$f[$fidx]->subforums[$sub->forum_id]->display_name		= sp_filter_name_display($s->display_name);
						$f[$fidx]->subforums[$sub->forum_id]->guest_name		= sp_filter_name_display($s->guest_name);
					}
					# do we need to record a possible subforum substitute topic?
					$fsub = (isset($f[$fidx]->subforums[$sub->forum_id]->forum_id_sub)) ? $f[$fidx]->subforums[$sub->forum_id]->forum_id_sub : 0;

					if ($fsub != 0 && !empty($stats[$fsub])) {
						$s = $stats[$fsub];
						$f[$fidx]->subforums[$sub->forum_id]->topic_id_sub		= $s->topic_id;
						$f[$fidx]->subforums[$sub->forum_id]->topic_name_sub	= sp_filter_title_display($s->topic_name);
						$f[$fidx]->subforums[$sub->forum_id]->topic_slug_sub	= $s->topic_slug;
						$f[$fidx]->subforums[$sub->forum_id]->post_id_sub		= $s->post_id;
						$f[$fidx]->subforums[$sub->forum_id]->post_permalink_sub= sp_build_url($f[$fidx]->subforums[$fsub]->forum_slug, $s->topic_slug, 0, $s->post_id, $s->post_index);
						$f[$fidx]->subforums[$sub->forum_id]->post_date_sub		= $s->post_date;
						$f[$fidx]->subforums[$sub->forum_id]->post_status_sub	= $s->post_status;
						$f[$fidx]->subforums[$sub->forum_id]->post_index_sub	= $s->post_index;

						# see if we can display the tooltip
						if (sp_can_view($fsub, 'post-content', $spThisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
							$f[$fidx]->subforums[$sub->forum_id]->post_tip_sub = ($s->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($s->post_content, $s->post_status);
						} else {
							$f[$fidx]->subforums[$sub->forum_id]->post_tip_sub = '';
						}

						$f[$fidx]->subforums[$sub->forum_id]->user_id_sub		= $s->user_id;
						$f[$fidx]->subforums[$sub->forum_id]->display_name_sub	= sp_filter_name_display($s->display_name);
						$f[$fidx]->subforums[$sub->forum_id]->guest_name_sub	= sp_filter_name_display($s->guest_name);
					}
					# allow plugins to add more data to combined subforum/post data structure
					$f[$fidx]->subforums[$sub->forum_id] = apply_filters('sph_forumview_subforum_records', $f[$fidx]->subforums[$sub->forum_id], $s);
				}
			}
			unset($subs);
			unset($stats);
		}
		return $f;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_forumview_subforums_query($subs, $top)
	#	Builds sub-forum list for the ForumView template
	#
	#	$subs:	Array of the children/sub forum ids for the forum in forum view
	#	$top:	Set to true if the call is for the first child from a main parent
	# --------------------------------------------------------------------------------------
	function sp_forumview_subforums_query($subs, $top = false) {

		if (empty($subs)) return;

		static $subList;
		static $topSubParent;
		global $spGlobals;

		if ($top) $topSubParent = $subs[0];

		$s = array();
		if (!empty($spGlobals['disabled_forums'])) {
			foreach ($subs as $thisSub) {
				if (!in_array($thisSub, $spGlobals['disabled_forums'])) $s[] = $thisSub;
			}
		} else {
			$s = $subs;
		}
		if (empty($s)) return;

		$s = implode(',', $s);

		$spdb = new spdbComplex;
			$spdb->table	= SFFORUMS;
			$spdb->fields	= 'forum_id, forum_name, forum_slug, forum_desc, forum_seq, forum_status, forum_disabled, forum_icon, forum_icon_new, topic_icon, topic_icon_new, topic_icon_locked, topic_icon_pinned, topic_icon_pinned_new, forum_rss_private,
							post_id, post_id_held, topic_count, post_count, post_count_held, parent, children';
			$spdb->where	= "forum_id IN ($s)";
			$spdb->orderby	= 'forum_seq';

		$spdb = apply_filters('sph_forumview_subforums_query', $spdb, $this);
		$records = $spdb->select();

		if ($records) {
			unset($subs);
			foreach ($records as $r) {
				$r->topSubParent = $topSubParent;
				$subList[$r->forum_seq]=$r;
				if (!empty($r->children)) {
					$subs=unserialize($r->children);
					$temp = $this->sp_forumview_subforums_query($subs);
				}
			}
		}
		return $subList;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_forumview_stats_query($topics)
	#	Builds the topic stats data structure for the ForumView template
	#
	#	$topics:	Array of the first and last post data from each topic
	#
	# --------------------------------------------------------------------------------------
	function sp_forumview_stats_query($topics, $forumid) {
		if (empty($topics)) return;
		global $spThisUser;

		$t = implode(',', $topics);

		$spdb = new spdbComplex;
			$spdb->table		= SFPOSTS;
			$spdb->fields		= SFPOSTS.'.post_id, '.SFPOSTS.'.topic_id, '.spdb_zone_datetime('post_date').',
								guest_name, '.SFPOSTS.'.user_id, post_content, post_status, '.SFMEMBERS.'.display_name, post_index';
			$spdb->join			= array(SFTOPICS.' ON '.SFTOPICS.'.topic_id = '.SFPOSTS.'.topic_id');
			$spdb->left_join	= array(SFMEMBERS.' ON '.SFPOSTS.'.user_id = '.SFMEMBERS.'.user_id');

			# only show posts awaiting moderation to admins/mods
			if (sp_get_auth('moderate_posts', $forumid)) {
				$spdb->where	= SFPOSTS.'.topic_id IN ('.$t.') AND (post_index = 1 OR '.SFPOSTS.'.post_id = '.SFTOPICS.'.post_id)';
			} else {
				$spdb->where	= SFPOSTS.'.topic_id IN ('.$t.') AND (post_index = 1 OR '.SFPOSTS.'.post_id = '.SFTOPICS.'.post_id_held)';
			}
			$spdb->orderby		= SFPOSTS.'.topic_id, '.SFPOSTS.'.post_id';
		$spdb = apply_filters('sph_forumview_stats_query', $spdb, $this);

		if (!empty($spThisUser->inspect['q_spForumViewStats'])) {
			$spdb->inspect = 'spForumViewStats';
			$spdb->show = true;
		}

		$records = $spdb->select();

		return $records;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_subforumview_stats_query($posts)
	#	Builds the forum stats data structure for the subforums in Forum View template
	#
	#	$posts: Array of the last post_id from each forum
	#
	# --------------------------------------------------------------------------------------
	function sp_subforumview_stats_query($posts) {
		if (empty($posts)) return;
		global $spThisUser;

		$WHERE = SFPOSTS.'.post_id IN (';
		$pcount = count($posts);
		$done = 0;
		foreach ($posts as $post) {
			$WHERE.= $post;
			$done++;
			if ($done < $pcount) $WHERE.= ',';
		}
		$WHERE .= ')';
		$spdb = new spdbComplex;
			$spdb->table		= SFPOSTS;
			$spdb->fields		= SFPOSTS.'.post_id, '.SFPOSTS.'.topic_id, topic_name, '.SFPOSTS.'.forum_id, '.spdb_zone_datetime('post_date').',
								guest_name, guest_email, '.SFPOSTS.'.user_id, post_content, post_status, '.SFMEMBERS.'.display_name,
								post_index, topic_slug';
			$spdb->left_join	= array(SFTOPICS.' ON '.SFPOSTS.'.topic_id = '.SFTOPICS.'.topic_id',
										SFMEMBERS.' ON '.SFPOSTS.'.user_id = '.SFMEMBERS.'.user_id');
			$spdb->where		= $WHERE;
		$spdb = apply_filters('sph_groupview_stats_query', $spdb, $this);
		$records = $spdb->select();

		if ($records) {
			# sort them into forum ids
			foreach ($records as $r) {
				$f[$r->forum_id] = $r;
			}
		}
		return $f;
	}
}

?>