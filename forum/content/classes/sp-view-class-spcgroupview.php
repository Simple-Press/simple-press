<?php
/*
Simple:Press
Group View Class
$LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
$Rev: 15704 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

class spcGroupView {
	# Set to whether to include the stats in the query
	public $includeStats = true;

	# Status: 'data', 'no access', 'no data'
	public $groupViewStatus = '';

	# True while the group loop is being rendered
	public $inGroupLoop = false;

	# True while the forum loop is being rendered
	public $inForumLoop = false;

	# Group View DB query result set
	public $pageData = array();

	# Group single row object
	public $groupData = '';

	# The WHERE clause if group ids passed in
	public $groupWhere = array();

	# Internal counter
	public $currentGroup = 0;

	# Count of group records
	public $groupCount = 0;

	# Group View DB Forums result set
	public $pageForumData = array();

	# Forum single row object
	public $forumData = '';

	# Internal counter
	public $currentForum = 0;

	# Count of forum records
	public $forumCount = 0;

	# List of subforums
	public $thisForumSubs = array();

	# Run in class instantiation - populates data
	public function __construct($ids = '', $stats = true, $idOrder = false) {
		$this->includeStats = $stats;
		$gIds               = array();
		if (!empty($ids)) $gIds = explode(',', $ids);
		if (!empty(SP()->rewrites->pageData['singlegroupid'])) {
			if (empty($gIds) || in_array(SP()->rewrites->pageData['singlegroupid'], $gIds)) {
				$gIds = array(); # reinstantiate
				$gIds[] = SP()->rewrites->pageData['singlegroupid'];
			}
		}

		$this->groupWhere = $gIds;
		$this->pageData   = $this->query($this->groupWhere, $idOrder);
		sp_display_inspector('gv_groups', $this->pageData);
	}

	/**
	 * This method indicates if there are group records.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    bool   true if another group set exists, false otherwise
	 */
	public function has_groups() {
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

	/**
	 * This method provides loop control on group records.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    bool   true if another group set exists, false otherwise
	 */
	public function loop_groups() {
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

	/**
	 * This method sets array pointer and returns current Group data.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    array   group data
	 */
	public function the_group() {
		$this->groupData = current($this->pageData);
		sp_display_inspector('gv_thisGroup', $this->groupData);
		next($this->pageData);

		return $this->groupData;
	}

	/**
	 * This method indicates if there are forum records.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    bool   true if another forum set exists, false otherwise
	 */
	public function has_forums() {
		if ($this->groupData->forums) {
			$this->pageForumData = $this->groupData->forums;
			$this->forumCount    = count($this->pageForumData);
			$this->inForumLoop   = true;

			return true;
		} else {
			return false;
		}
	}

	/**
	 * This method provides loop control on Forum records.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    bool   true if another forum set exists, false otherwise
	 */
	public function loop_forums() {
		if ($this->currentForum > 0) do_action_ref_array('sph_after_forum', array(&$this));
		$this->currentForum++;
		if ($this->currentForum <= $this->forumCount) {
			do_action_ref_array('sph_before_forum', array(&$this));

			return true;
		} else {
			$this->inForumLoop  = false;
			$this->currentForum = 0;
			$this->forumCount   = 0;
			unset($this->pageForumData);

			return false;
		}
	}

	/**
	 * This method sets array pointer and returns current Forum data.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    array   current forum data
	 */
	public function the_forum() {
		$this->forumData = current($this->pageForumData);
		sp_display_inspector('gv_thisForum', $this->forumData);
		$this->forumDataSubs = (isset($this->forumData->subforums)) ? $this->forumData->subforums : '';
		sp_display_inspector('gv_thisForumSubs', $this->forumDataSubs);
		next($this->pageForumData);

		return $this->forumData;
	}

	/**
	 * This method nuilds the data structure for the GroupView template.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $groupids array of group ids to include in query
	 * @param bool  $idOrder  if array of group ids passed, should we order like the array
	 *
	 * @returns    array   group data from the query
	 */
	private function query($groupids = '', $idOrder = false) {
		# can we get the results from the cache?
		$records = array();
		if (empty(SP()->user->thisUser->inspect['q_GroupView'])) {
			$records = SP()->cache->get('group');
		}

		if (!$records || !empty($groupids)) {
			$WHERE = '';
			if (!empty($groupids)) {
				$gcount = count($groupids);
				$done   = 0;
				foreach ($groupids as $id) {
					$WHERE .= '('.SPGROUPS.".group_id=$id)";
					$done++;
					if ($done < $gcount) $WHERE .= ' OR ';
				}
			}

			$this->groupViewStatus = (empty($groupids)) ? 'no data' : 'no access';

			# retrieve group and forum records
			$query          = new stdClass();
			$query->table   = SPGROUPS;
			$query->fields  = SPGROUPS.'.group_id, group_name, group_desc, group_rss, group_icon, group_message,
								forum_id, forum_name, forum_slug, forum_desc, forum_status, forum_disabled, forum_icon, forum_icon_new, forum_icon_locked, forum_rss_private,
								post_id, post_id_held, topic_count, post_count, post_count_held, parent, children';
			$query->join    = array(SPFORUMS.' ON '.SPGROUPS.'.group_id = '.SPFORUMS.'.group_id');
			$query->where   = $WHERE;
			$query->orderby = 'group_seq, forum_seq';
			$query          = apply_filters('sph_groupview_query', $query);
			if (!empty(SP()->user->thisUser->inspect['q_GroupView'])) {
				$query->inspect                       = 'spGroupView';
				$query->show                          = true;
				SP()->user->thisUser->inspect['q_GroupView'] = false;
			}
			$records = SP()->DB->select($query);
			if ($records && empty($groupids)) SP()->cache->add('group', $records);
		}

		$g = '';
		if ($records) {
			# Set status initially to 'no access' in case current user can view no forums
			$this->groupViewStatus = 'no access';

			$gidx      = 0;
			$fidx      = 0;
			$sidx      = 0;
			$cparent   = 0;
			$subPostId = 0;

			# define array to collect data
			$p = array();
			$g = array();

			foreach ($records as $r) {
				$groupid = $r->group_id;
				$forumid = $r->forum_id;

				if (SP()->auths->can_view($forumid, 'forum-title')) {
					if ($gidx == 0 || $g[$gidx]->group_id != $groupid) {
						# reset status to 'data'
						$this->groupViewStatus      = 'data';
						$gidx                       = $groupid;
						$fidx                       = 0;
						$g[$gidx]                   = new stdClass();
						$g[$gidx]->group_id         = $r->group_id;
						$g[$gidx]->group_name       = SP()->displayFilters->title($r->group_name);
						$g[$gidx]->group_desc       = SP()->displayFilters->title($r->group_desc);
						$g[$gidx]->group_rss        = esc_url($r->group_rss);
						$g[$gidx]->group_icon       = $r->group_icon;
						$g[$gidx]->group_message    = SP()->displayFilters->title($r->group_message);
						$g[$gidx]->group_rss_active = 0;

						$g[$gidx] = apply_filters('sph_groupview_group_records', $g[$gidx], $r);
					}
					if (isset($r->forum_id)) {
						# Is this a subform?
						if ($r->parent != 0) {
							$sidx                                                            = $r->forum_id;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]                    = new stdClass();
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_id          = $r->forum_id;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_name        = SP()->displayFilters->title($r->forum_name);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_slug        = $r->forum_slug;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_icon        = $r->forum_icon;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_icon_new    = $r->forum_icon_new;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_icon_locked = $r->forum_icon_locked;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_disabled    = $r->forum_disabled;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->forum_permalink   = SP()->spPermalinks->build_url($r->forum_slug, '', 1, 0);
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->topic_count       = $r->topic_count;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_count        = $r->post_count;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->parent            = $r->parent;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->children          = $r->children;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id           = $r->post_id;
							$g[$gidx]->forums[$cparent]->subforums[$sidx]->unread            = 0;

							# check if we can look at posts in moderation - if not swap for 'held' values
							if (!SP()->auths->get('moderate_posts', $r->forum_id)) {
								$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_count = $r->post_count_held;
								$g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id    = $r->post_id_held;
							}

							# See if any forums are in the current users newpost list
							if (SP()->user->thisUser->member && isset(SP()->user->thisUser->newposts['forums'])) {
								$c = 0;
								if (SP()->user->thisUser->newposts['forums']) {
									foreach (SP()->user->thisUser->newposts['forums'] as $fnp) {
										if ($fnp == $sidx) $c++;
									}
								}

								# set the subforum unread count
								$g[$gidx]->forums[$cparent]->subforums[$sidx]->unread = $c;
							}

							# Update top parent counts with subforum counts
							$g[$gidx]->forums[$cparent]->topic_count_sub += $g[$gidx]->forums[$cparent]->subforums[$sidx]->topic_count;
							$g[$gidx]->forums[$cparent]->post_count_sub += $g[$gidx]->forums[$cparent]->subforums[$sidx]->post_count;

							# and what about the most recent post? Is this in a subforum?
							if ($g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id > $g[$gidx]->forums[$cparent]->post_id && $g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id > $subPostId) {
								# store the alternative forum id in case we need to display the topic data for this one if inc. subs
								$g[$gidx]->forums[$cparent]->forum_id_sub = $r->forum_id;
								# add the last post in subforum to the list for stats retrieval
								$subPostId       = $g[$gidx]->forums[$cparent]->subforums[$sidx]->post_id;
								$p[$r->forum_id] = $subPostId;
							}
						} else {
							# it's a top level forum
							$subPostId                                  = 0;
							$fidx                                       = $forumid;
							$g[$gidx]->forums[$fidx]                    = new stdClass();
							$g[$gidx]->forums[$fidx]->forum_id          = $r->forum_id;
							$g[$gidx]->forums[$fidx]->forum_id_sub      = 0;
							$g[$gidx]->forums[$fidx]->forum_name        = SP()->displayFilters->title($r->forum_name);
							$g[$gidx]->forums[$fidx]->forum_slug        = $r->forum_slug;
							$g[$gidx]->forums[$fidx]->forum_permalink   = SP()->spPermalinks->build_url($r->forum_slug, '', 1, 0);
							$g[$gidx]->forums[$fidx]->forum_desc        = SP()->displayFilters->title($r->forum_desc);
							$g[$gidx]->forums[$fidx]->forum_status      = $r->forum_status;
							$g[$gidx]->forums[$fidx]->forum_disabled    = $r->forum_disabled;
							$g[$gidx]->forums[$fidx]->forum_icon        = $r->forum_icon;
							$g[$gidx]->forums[$fidx]->forum_icon_new    = $r->forum_icon_new;
							$g[$gidx]->forums[$fidx]->forum_icon_locked = $r->forum_icon_locked;
							$g[$gidx]->forums[$fidx]->forum_rss_private = $r->forum_rss_private;
							$g[$gidx]->forums[$fidx]->post_id           = $r->post_id;
							$g[$gidx]->forums[$fidx]->topic_count       = $r->topic_count;
							$g[$gidx]->forums[$fidx]->topic_count_sub   = $r->topic_count;
							$g[$gidx]->forums[$fidx]->post_count        = $r->post_count;
							$g[$gidx]->forums[$fidx]->post_count_sub    = $r->post_count;
							$g[$gidx]->forums[$fidx]->parent            = $r->parent;
							$g[$gidx]->forums[$fidx]->children          = $r->children;
							$g[$gidx]->forums[$fidx]->unread            = 0;

							if (empty($g[$gidx]->forums[$fidx]->post_id)) $g[$gidx]->forums[$fidx]->post_id = 0;

							# Can the user create new topics or should we lock the forum?
							$g[$gidx]->forums[$fidx]->start_topics = SP()->auths->get('start_topics', $r->forum_id);

							# check if we can look at posts in moderation - if not swap for 'held' values
							if (!SP()->auths->get('moderate_posts', $r->forum_id)) {
								$g[$gidx]->forums[$fidx]->post_id        = $r->post_id_held;
								$g[$gidx]->forums[$fidx]->post_count     = $r->post_count_held;
								$g[$gidx]->forums[$fidx]->post_count_sub = $r->post_count_held;
								$thisPostid                              = $r->post_id_held;
							} else {
								$thisPostid = $r->post_id;
							}

							# See if any forums are in the current users newpost list
							if (SP()->user->thisUser->member && isset(SP()->user->thisUser->newposts['forums'])) {
								$c = 0;
								if (SP()->user->thisUser->newposts['forums']) {
									foreach (SP()->user->thisUser->newposts['forums'] as $fnp) {
										if ($fnp == $fidx) $c++;
									}
								}
								$g[$gidx]->forums[$fidx]->unread = $c;
							}

							if (empty($r->children)) {
								$cparent = 0;
							} else {
								$cparent = $fidx;
								$sidx    = 0;
							}

							# Build post id array for collecting stats at the end
							if (!empty($thisPostid)) $p[$fidx] = $thisPostid;

							$g[$gidx]->forums[$fidx] = apply_filters('sph_groupview_forum_records', $g[$gidx]->forums[$fidx], $r);
						}
						# Build special Group level flag on whether to show group RSS button or not (based on any forum in group having RSS access
						if (SP()->auths->get('view_forum', $r->forum_id) && !$r->forum_rss_private) $g[$gidx]->group_rss_active = 1;
					}
				}
			}
		}

		if ($this->includeStats == true) {
			# Go grab the forum stats and data
			if (!empty($p)) {
				$stats = $this->stats_query($p);
				if ($stats) {
					foreach ($g as $gr) {
						foreach ($gr->forums as $f) {
							if (!empty($stats[$f->forum_id])) {
								$s                 = $stats[$f->forum_id];
								$f->topic_id       = $s->topic_id;
								$f->topic_name     = SP()->displayFilters->title($s->topic_name);
								$f->topic_slug     = $s->topic_slug;
								$f->post_id        = $s->post_id;
								$f->post_permalink = SP()->spPermalinks->build_url($f->forum_slug, $s->topic_slug, 0, $s->post_id, $s->post_index);
								$f->post_date      = $s->post_date;
								$f->post_status    = $s->post_status;
								$f->post_index     = $s->post_index;

								# see if we can display the tooltip
								if (SP()->auths->can_view($f->forum_id, 'post-content', SP()->user->thisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
									$f->post_tip = ($s->post_status) ? SP()->primitives->front_text('Post awaiting moderation') : SP()->displayFilters->tooltip($s->post_content, $s->post_status);
								} else {
									$f->post_tip = '';
								}

								$f->user_id      = $s->user_id;
								$f->display_name = SP()->displayFilters->name($s->display_name);
								$f->guest_name   = SP()->displayFilters->name($s->guest_name);
							}
							# do we need to record a possible subforum substitute topic?
							$fsub = $f->forum_id_sub;
							if ($fsub != 0 && !empty($stats[$fsub])) {
								$s                     = $stats[$fsub];
								$f->topic_id_sub       = $s->topic_id;
								$f->topic_name_sub     = SP()->displayFilters->title($s->topic_name);
								$f->topic_slug_sub     = $s->topic_slug;
								$f->post_id_sub        = $s->post_id;
								$f->post_permalink_sub = SP()->spPermalinks->build_url($f->subforums[$fsub]->forum_slug, $s->topic_slug, 0, $s->post_id, $s->post_index);
								$f->post_date_sub      = $s->post_date;
								$f->post_status_sub    = $s->post_status;
								$f->post_index_sub     = $s->post_index;

								# see if we can display the tooltip
								if (SP()->auths->can_view($fsub, 'post-content', SP()->user->thisUser->ID, $s->user_id, $s->topic_id, $s->post_id)) {
									$f->post_tip_sub = ($s->post_status) ? SP()->primitives->front_text('Post awaiting moderation') : SP()->displayFilters->tooltip($s->post_content, $s->post_status);
								} else {
									$f->post_tip_sub = '';
								}

								$f->user_id_sub      = $s->user_id;
								$f->display_name_sub = SP()->displayFilters->name($s->display_name);
								$f->guest_name_sub   = SP()->displayFilters->name($s->guest_name);
							}

							$f = apply_filters('sph_groupview_stats_records', $f, $s);
						}
					}
					unset($stats);
				}
			}
		}

		# Do we need to re-order IDs based on passed in IDs
		if ($records && $groupids && $idOrder) {
			$n = array();
			foreach ($groupids as $gid) {
				if (array_key_exists($gid, $g)) $n[$gid] = $g[$gid];
			}
			$g = $n;
			unset($n);
		}

		return $g;
	}

	/**
	 * This method builds the forum stats data structure for the GroupView template.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $posts last post id from each forum
	 *
	 * @returns    array   forum stats data
	 */
	private function stats_query($posts) {
		if (empty($posts)) return array();

		$WHERE  = SPPOSTS.'.post_id IN (';
		$pcount = count($posts);
		$done   = 0;
		foreach ($posts as $post) {
			$WHERE .= $post;
			$done++;
			if ($done < $pcount) $WHERE .= ',';
		}
		$WHERE .= ')';
		$query            = new stdClass();
		$query->table     = SPPOSTS;
		$query->fields    = SPPOSTS.'.post_id, '.SPPOSTS.'.topic_id, topic_name, '.SPPOSTS.'.forum_id, '.SP()->DB->timezone('post_date').',
								guest_name, guest_email, '.SPPOSTS.'.user_id, post_content, post_status, '.SPMEMBERS.'.display_name,
								post_index, topic_slug';
		$query->left_join = array(SPTOPICS.' ON '.SPPOSTS.'.topic_id = '.SPTOPICS.'.topic_id',
		                          SPMEMBERS.' ON '.SPPOSTS.'.user_id = '.SPMEMBERS.'.user_id');
		$query->where     = $WHERE;
		$query            = apply_filters('sph_groupview_stats_query', $query);

		if (!empty(SP()->user->thisUser->inspect['q_GroupViewStats'])) {
			$query->inspect = 'spGroupViewStats';
			$query->show    = true;
		}
		$records = SP()->DB->select($query);

		$f = array();
		if ($records) {
			# sort them into forum ids
			foreach ($records as $r) {
				$f[$r->forum_id] = $r;
			}
		}

		return $f;
	}
	
	/**
	 * This method returns the count of Forum records.
	 *
	 * @access public
	 *
	 * @since 6.5
	 *
	 * @param void
	 *
	 * @returns int
	 */
	public function forum_count() {
		return $this->forumCount;		
	}
}
