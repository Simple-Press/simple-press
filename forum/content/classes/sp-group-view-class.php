<?php
/*
Simple:Press
Group View Class
$LastChangedDate: 2015-04-10 20:11:28 -0500 (Fri, 10 Apr 2015) $
$Rev: 12689 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#	Version: 5.0
#
#	sp_has_groups()
#	sp_loop_groups()
#	sp_the_group()
#
#	Group Loop functions from the GroupView template
#
# --------------------------------------------------------------------------------------

function sp_has_groups($ids='', $idOrder=false) {
	global $spGroupView;
	$spGroupView = new spGroupView($ids, true, $idOrder);
	return $spGroupView->sp_has_groups();
}

function sp_loop_groups() {
	global $spGroupView;
	return $spGroupView->sp_loop_groups();
}

function sp_the_group() {
	global $spGroupView, $spThisGroup;
	$spThisGroup = $spGroupView->sp_the_group();
}

# --------------------------------------------------------------------------------------

# --------------------------------------------------------------------------------------
#
#	sp_has_forums()
#	sp_loop_forums()
#	sp_the_forum()
#
#	Forum Loop functions from the GroupView template
#
# --------------------------------------------------------------------------------------

function sp_has_forums() {
	global $spGroupView;
	return $spGroupView->sp_has_forums();
}

function sp_loop_forums() {
	global $spGroupView;
	return $spGroupView->sp_loop_forums();
}

function sp_the_forum() {
	global $spGroupView, $spThisForum, $spThisForumSubs;
	$spThisForum = $spGroupView->sp_the_forum();
	$spThisForumSubs = $spGroupView->forumDataSubs;
}

# --------------------------------------------------------------------------------------


# ==========================================================================================
#
#	Group View. Groups and Forums Listing Class
#
# ==========================================================================================

class spGroupView {
	# Set to whether to include the stats in the query
	var $includeStats = true;

	# Status: 'data', 'no access', 'no data'
	var $groupViewStatus = '';

	# True while the group loop is being rendered
	var $inGroupLoop = false;

	# True while the forum loop is being rendered
	var $inForumLoop = false;

	# Group View DB query result set
	var $pageData = array();

	# Group single row object
	var $groupData = '';

	# The WHERE clause if group ids passed in
	var $groupWhere = array();

	# Internal counter
	var $currentGroup = 0;

	# Count of group records
	var $groupCount = 0;

	# Group View DB Forums result set
	var $pageForumData = array();

	# Forum single row object
	var $forumData = '';

	# Internal counter
	var $currentForum = 0;

	# Count of forum records
	var $forumCount = 0;

	# List of subforums
	var $thisForumSubs = array();

	# Run in class instantiation - populates data
	function __construct($ids='', $stats=true, $idOrder=false) {
		global $spVars;
		$this->includeStats = $stats;
		$gIds = array();
		if (!empty($ids)) $gIds = explode(',', $ids);
		if (!empty($spVars['singlegroupid']) && !in_array($spVars['singlegroupid'], $gIds)) $gIds[] = $spVars['singlegroupid'];
		$this->groupWhere = $gIds;
		$this->pageData = $this->sp_groupview_query($this->groupWhere, $idOrder);
		sp_display_inspector('gv_spGroupView', $this->pageData);
	}

	# Return status
	function sp_has_groups() {
		# Check for no access to any forums or no data
		if ($this->groupViewStatus != 'data') return false;

		$this->groupCount = count($this->pageData);
		reset($this->pageData);

		if ($this->groupCount) {
			$this->inGroupLoop = true;
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Group records
	function sp_loop_groups() {
		if ($this->currentGroup > 0) do_action_ref_array('sph_after_group', array(&$this));
		$this->currentGroup++;
		if ($this->currentGroup <= $this->groupCount) {
			do_action_ref_array('sph_before_group', array(&$this));
			return true;
		} else {
			$this->inGroupLoop = false;
			return false;
		}
	}

	# Sets array pointer and returns current Group data
	function sp_the_group() {
		$this->groupData = current($this->pageData);
		sp_display_inspector('gv_spThisGroup', $this->groupData);
		next($this->pageData);
		return $this->groupData;
	}

	# True if there are Forum records
	function sp_has_forums() {
		if ($this->groupData->forums) {
			$this->pageForumData = $this->groupData->forums;
			$this->forumCount = count($this->pageForumData);
			$this->inForumLoop = true;
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Forum records
	function sp_loop_forums() {
		if ($this->currentForum > 0) do_action_ref_array('sph_after_forum', array(&$this));
		$this->currentForum++;
		if ($this->currentForum <= $this->forumCount) {
			do_action_ref_array('sph_before_forum', array(&$this));
			return true;
		} else {
			$this->inForumLoop = false;
			$this->currentForum = 0;
			$this->forumCount = 0;
			unset($this->pageForumData);
			return false;
		}
	}

	# Sets array pointer and returns current Forum data
	function sp_the_forum() {
		$this->forumData = current($this->pageForumData);
		sp_display_inspector('gv_spThisForum', $this->forumData);
		$this->forumDataSubs = (isset($this->forumData->subforums)) ? $this->forumData->subforums : '';
		sp_display_inspector('gv_spThisForumSubs', $this->forumDataSubs);
		next($this->pageForumData);
		return $this->forumData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_groupview_query()
	#	Builds the data structure for the GroupView template
	#
	#	$groupid:	Can pass an array of group ids if set
	#	$idOrder:	If ids passed do we order by them
	#
	#	Internally calls the sp_groupview_stats_query() to populate forum stats
	#
	# --------------------------------------------------------------------------------------
	function sp_groupview_query($groupids='', $idOrder=false) {
		global $spThisUser;

		# can we get the results from the cache?
		$records = array();
		if (empty($spThisUser->inspect['q_spGroupView'])) {
			$records = sp_get_cache('group');
		}

		if (!$records) {
			$WHERE = '';
			if (!empty($groupids)) {
				$gcount = count($groupids);
				$done = 0;
				foreach ($groupids as $id) {
					$WHERE.= '('.SFGROUPS.".group_id=$id)";
					$done++;
					if ($done < $gcount) $WHERE.= ' OR ';
				}
			}

			$this->groupViewStatus = (empty($groupids)) ? 'no data' : 'no access';

			# retrieve group and forum records
			$spdb = new spdbComplex;
				$spdb->table	= SFGROUPS;
				$spdb->fields	= SFGROUPS.'.group_id, group_name, group_desc, group_rss, group_icon, group_message,
								forum_id, forum_name, forum_slug, forum_desc, forum_status, forum_disabled, forum_icon, forum_icon_new, forum_icon_locked, forum_rss_private,
								post_id, post_id_held, topic_count, post_count, post_count_held, parent, children';
				$spdb->join		= array(SFFORUMS.' ON '.SFGROUPS.'.group_id = '.SFFORUMS.'.group_id');
				$spdb->where	= $WHERE;
				$spdb->orderby	= 'group_seq, forum_seq';
			$spdb = apply_filters('sph_groupview_query', $spdb, $this);

			if (!empty($spThisUser->inspect['q_spGroupView'])) {
				$spdb->inspect = 'spGroupView';
				$spdb->show = true;
				$spThisUser->inspect['q_spGroupView'] = false;
			}

			$records = $spdb->select();

			if ($records) sp_add_cache('group', $records);
		}

		$g = '';
		if ($records) {
			# Set status initially to 'no access' in case current user can view no forums
			$this->groupViewStatus = 'no access';

			$gidx = 0;
			$fidx = 0;
			$sidx = 0;
			$cparent = 0;
			$subPostId = 0;

			# define array to collect data
			$p = array();
			$g = array();

			foreach ($records as $r) {
				$groupid = $r->group_id;
				$forumid = $r->forum_id;

				if (sp_can_view($forumid, 'forum-title')) {
					if ($gidx == 0 || $g[$gidx]->group_id != $groupid) {
						# reset status to 'data'
						$this->groupViewStatus = 'data';
						$gidx = $groupid;
						$fidx = 0;
						$g[$gidx] = new stdClass();
						$g[$gidx]->group_id			= $r->group_id;
						$g[$gidx]->group_name		= sp_filter_title_display($r->group_name);
						$g[$gidx]->group_desc		= sp_filter_title_display($r->group_desc);
						$g[$gidx]->group_rss		= esc_url($r->group_rss);
						$g[$gidx]->group_icon		= sanitize_file_name($r->group_icon);
						$g[$gidx]->group_message	= sp_filter_text_display($r->group_message);
						$g[$gidx]->group_rss_active = 0;

						$g[$gidx] = apply_filters('sph_groupview_group_records', $g[$gidx], $r);
					}
					if (isset($r->forum_id)) {
						# Is this a subform?
						if ($r->parent != 0) {
							$sidx = $r->forum_id;
							$g[$gidx]->forums[$cparent]->subforums[$sidx] = new stdClass();
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_id			 = $r->forum_id;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_name		 = sp_filter_title_display($r->forum_name);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_slug		 = $r->forum_slug;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_icon		 = sanitize_file_name($r->forum_icon);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_icon_new	 = sanitize_file_name($r->forum_icon_new);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_icon_locked = sanitize_file_name($r->forum_icon_locked);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_disabled	 = $r->forum_disabled;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_permalink	 = sp_build_url($r->forum_slug, '', 1, 0);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->topic_count		 = $r->topic_count;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_count		 = $r->post_count;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->parent			 = $r->parent;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->children			 = $r->children;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id			 = $r->post_id;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->unread			 = 0;

							# check if we can look at posts in moderation - if not swap for 'held' values
							if (!sp_get_auth('moderate_posts', $r->forum_id)) {
								$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_count		= $r->post_count_held;
								$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id			= $r->post_id_held;
							}

							# See if any forums are in the current users newpost list
							if ($spThisUser->member && isset($spThisUser->newposts['forums'])) {
								$c=0;
								if ($spThisUser->newposts['forums']) {
									foreach ($spThisUser->newposts['forums'] as $fnp) {
										if ($fnp == $sidx) $c++;
									}
								}

								# set the subforum unread count
								$g[$gidx]->forums[$cparent]->subforums[$sidx]->unread = $c;
							}

							# Update top parent counts with subforum counts
							$g[$gidx]->forums[$cparent]->topic_count_sub += $g[$gidx]->forums[$cparent]->subforums[$sidx]->topic_count;
							$g[$gidx]->forums[$cparent]->post_count_sub	 += $g[$gidx]->forums[$cparent]->subforums[$sidx]->post_count;

							# and what about the most recent post? Is this in a subforum?
							if ($g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id > $g[$gidx]->forums[$cparent]->post_id && $g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id > $subPostId) {
								# store the alternative forum id in case we need to display the topic data for this one if inc. subs
								$g[$gidx]->forums[$cparent]->forum_id_sub = $r->forum_id;
								# add the last post in subforum to the list for stats retrieval
								$subPostId=$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id;
								$p[$r->forum_id] = $subPostId;
							}
						} else {
							# it's a top level forum
							$subPostId=0;
							$fidx = $forumid;
							$g[$gidx]->forums[$fidx] = new stdClass();
							$g[$gidx]->forums[$fidx]->forum_id			= $r->forum_id;
							$g[$gidx]->forums[$fidx]->forum_id_sub		= 0;
							$g[$gidx]->forums[$fidx]->forum_name		= sp_filter_title_display($r->forum_name);
							$g[$gidx]->forums[$fidx]->forum_slug		= $r->forum_slug;
							$g[$gidx]->forums[$fidx]->forum_permalink	= sp_build_url($r->forum_slug, '', 1, 0);
							$g[$gidx]->forums[$fidx]->forum_desc		= sp_filter_title_display($r->forum_desc);
							$g[$gidx]->forums[$fidx]->forum_status		= $r->forum_status;
							$g[$gidx]->forums[$fidx]->forum_disabled	= $r->forum_disabled;
							$g[$gidx]->forums[$fidx]->forum_icon		= sanitize_file_name($r->forum_icon);
							$g[$gidx]->forums[$fidx]->forum_icon_new	= sanitize_file_name($r->forum_icon_new);
							$g[$gidx]->forums[$fidx]->forum_icon_locked	= sanitize_file_name($r->forum_icon_locked);
							$g[$gidx]->forums[$fidx]->forum_rss_private = $r->forum_rss_private;
							$g[$gidx]->forums[$fidx]->post_id			= $r->post_id;
							$g[$gidx]->forums[$fidx]->topic_count		= $r->topic_count;
							$g[$gidx]->forums[$fidx]->topic_count_sub	= $r->topic_count;
							$g[$gidx]->forums[$fidx]->post_count		= $r->post_count;
							$g[$gidx]->forums[$fidx]->post_count_sub	= $r->post_count;
							$g[$gidx]->forums[$fidx]->parent			= $r->parent;
							$g[$gidx]->forums[$fidx]->children			= $r->children;
							$g[$gidx]->forums[$fidx]->unread			= 0;

							if (empty($g[$gidx]->forums[$fidx]->post_id)) $g[$gidx]->forums[$fidx]->post_id=0;

							# Can the user create new topics or should we lock the forum?
							$g[$gidx]->forums[$fidx]->start_topics		= sp_get_auth('start_topics', $r->forum_id);

							# check if we can look at posts in moderation - if not swap for 'held' values
							if (!sp_get_auth('moderate_posts', $r->forum_id)) {
								$g[$gidx]->forums[$fidx]->post_id			= $r->post_id_held;
								$g[$gidx]->forums[$fidx]->post_count		= $r->post_count_held;
								$g[$gidx]->forums[$fidx]->post_count_sub	= $r->post_count_held;
								$thisPostid = $r->post_id_held;
							} else {
								$thisPostid = $r->post_id;
							}

							# See if any forums are in the current users newpost list
							if ($spThisUser->member && isset($spThisUser->newposts['forums'])) {
								$c = 0;
								if ($spThisUser->newposts['forums']) {
									foreach ($spThisUser->newposts['forums'] as $fnp) {
										if ($fnp == $fidx) $c++;
									}
								}
								$g[$gidx]->forums[$fidx]->unread = $c;
							}

							if (empty($r->children)) {
								$cparent = 0;
							} else {
								$cparent = $fidx;
								$sidx = 0;
							}

							# Build post id array for collecting stats at the end
							if (!empty($thisPostid)) $p[$fidx] = $thisPostid;

							$g[$gidx]->forums[$fidx] = apply_filters('sph_groupview_forum_records', $g[$gidx]->forums[$fidx], $r);
						}
						# Build special Group level flag on whether to show group RSS button or not (based on any forum in group having RSS access
						if (sp_get_auth('view_forum', $r->forum_id) && !$r->forum_rss_private) $g[$gidx]->group_rss_active = 1;
					}
				}
			}
		}

		if ($this->includeStats == true) {
			# Go grab the forum stats and data
			if (!empty($p)) {
				$stats = $this->sp_groupview_stats_query($p);
				if ($stats) {
					foreach ($g as $gr) {
						foreach ($gr->forums as $f) {
							if (!empty($stats[$f->forum_id])) {
								$s = $stats[$f->forum_id];
								$f->topic_id		= $s->topic_id;
								$f->topic_name		= sp_filter_title_display($s->topic_name);
								$f->topic_slug		= $s->topic_slug;
								$f->post_id			= $s->post_id;
								$f->post_permalink	= sp_build_url($f->forum_slug, $s->topic_slug, 0, $s->post_id, $s->post_index);
								$f->post_date		= $s->post_date;
								$f->post_status		= $s->post_status;
								$f->post_index		= $s->post_index;

								# see if we can display the tooltip
								if (sp_can_view($f->forum_id, 'post-content', $spThisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
									$f->post_tip = ($s->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($s->post_content, $s->post_status);
								} else {
									$f->post_tip = '';
								}

								$f->user_id			= $s->user_id;
								$f->display_name	= sp_filter_name_display($s->display_name);
								$f->guest_name		= sp_filter_name_display($s->guest_name);
							}
							# do we need to record a possible subforum substitute topic?
							$fsub = $f->forum_id_sub;
							if ($fsub != 0 && !empty($stats[$fsub])) {
								$s = $stats[$fsub];
								$f->topic_id_sub		= $s->topic_id;
								$f->topic_name_sub		= sp_filter_title_display($s->topic_name);
								$f->topic_slug_sub		= $s->topic_slug;
								$f->post_id_sub			= $s->post_id;
								$f->post_permalink_sub	= sp_build_url($f->subforums[$fsub]->forum_slug, $s->topic_slug, 0, $s->post_id, $s->post_index);
								$f->post_date_sub		= $s->post_date;
								$f->post_status_sub		= $s->post_status;
								$f->post_index_sub		= $s->post_index;

								# see if we can display the tooltip
								if (sp_can_view($fsub, 'post-content', $spThisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
									$f->post_tip_sub = ($s->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($s->post_content, $s->post_status);
								} else {
									$f->post_tip_sub = '';
								}

								$f->user_id_sub			= $s->user_id;
								$f->display_name_sub	= sp_filter_name_display($s->display_name);
								$f->guest_name_sub		= sp_filter_name_display($s->guest_name);
							}

							$f = apply_filters('sph_groupview_stats_records', $f, $s);
						}
					}
					unset($stats);
				}
			}
		}

		# Do we need to re-order IDs based on passed in IDs
		if ($groupids && $idOrder) {
			$n = array();
			foreach ($groupids as $gid) {
				if (array_key_exists($gid, $g)) $n[$gid] = $g[$gid];
			}
			$g = $n;
			unset($n);
		}

		return $g;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_groupview_stats_query($posts)
	#	Builds the forum stats data structure for the GroupView template
	#
	#	$posts: Array of the last post_id from each forum
	#
	# --------------------------------------------------------------------------------------
	function sp_groupview_stats_query($posts) {
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

		if (!empty($spThisUser->inspect['q_spGroupViewStats'])) {
			$spdb->inspect = 'spGroupViewStats';
			$spdb->show = true;
		}

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