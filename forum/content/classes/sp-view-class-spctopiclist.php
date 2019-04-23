<?php
/*
Simple:Press
List Topic Class
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	Returns simplified but rich data based upon the Topic IDs passed in
#	Intended for simple listings
#
#	Version: 5.0
#
# ==========================================================================================

# --------------------------------------------------------------------------------------
#
#	List (Topic Loop) functions for ListView data objects
#
#	Instantiate spListView	All arguments are optional but 1 of the first two are required
#
#	Pass:	$topicIds:	Pass an array of TOPIC ids to specifically use in the list
#			$count:		Optional count of how many rows to return (will also pad IDs)
#			$group:		Boolean: Group/Order the results into forums (Default true)
#			$forumIds:	Optional array of FORUM ids to filter the topic selection by
#			$firstPost:	Include first post data - not just last post (Default false)
#			$popup:		New post list only - whether inline of in a popup
#			$type:		Optional documentation text to use in filters so plugins know usage
#
#	Returns a data object based upon the topic ids
#
#	IMPORTANT NOTES:
#
#	* If NO topic Ids are passed and a count of zero is passed - no data is returned.
#	* If topic Ids are passed with a count higher than the ids count then the object
#	will be padded to include the most recent topics updated as well as the ids passed in
#	* If forum Ids are passed in they will be used to filter the selection of topic Ids
#	to use in the returned data but will NOT verify that any topic Ids also passed in
#	belong within those forums.
#
# --------------------------------------------------------------------------------------

class spcTopicList {
	# Forum View DB query result set
	public $listData = array();

	# Topic single row object
	public $topicData = '';

	# Internal counter
	public $currentTopic = 0;

	# Count of topic records
	public $listCount = 0;

	# Whether inline or popup (new posts only)
	public $popup = 1;

	# Run in class instantiation - populates data
	public function __construct($topicIds = '', $count = 0, $group = true, $forumIds = '', $firstPost = 0, $popup = 1, $type = '') {
		$this->listData = $this->query($topicIds, $count, $group, $forumIds, $firstPost, $popup, $type);
		sp_display_inspector('tlv_listTopics', $this->listData);
	}

	# True if there are Topic records
	public function has_topiclist() {
		if (!empty($this->listData)) {
			$this->listCount = count($this->listData);
			reset($this->listData);

			return true;
		} else {
			return false;
		}
	}

	# Loop control on Topic records
	public function loop_topiclist() {
		if ($this->currentTopic > 0) do_action_ref_array('sph_after_list', array(&$this));
		$this->currentTopic++;
		if ($this->currentTopic <= $this->listCount) {
			do_action_ref_array('sph_before_list', array(&$this));

			return true;
		} else {
			$this->currentTopic = 0;
			$this->listCount    = 0;
			unset($this->listData);

			return false;
		}
	}

	# Sets array pointer and returns current Topic data
	public function the_topiclist() {
		$this->topicData = current($this->listData);
		sp_display_inspector('tlv_thisListTopic', $this->topicData);
		next($this->listData);

		return $this->topicData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	query()
	#	Builds the data structure for the Listview data object
	#
	# --------------------------------------------------------------------------------------
	private function query($topicIds, $count, $group, $forumIds, $firstPost, $popup, $type) {
		# If no topic ids and no count then nothjing to do - return empty
		if (empty($topicIds) && $count == 0) return array();

		# set popup flag for new posts
		$this->popup = $popup;

		# Do we have enough topic ids to satisfy count?
		if (empty($topicIds) || ($count != 0 && count($topicIds) < $count)) $topicIds = $this->topicids($topicIds, $forumIds, $count);

		# Do we havwe too many topic ids?
		if ($topicIds && ($count != 0 && count($topicIds) > $count)) $topicIds = array_slice($topicIds, 0, $count, true);

		if (empty($topicIds)) return array();

		# Construct the main WHERE clause and then main query
		$where = SPTOPICS.'.topic_id IN ('.implode(',', $topicIds).')';

		if ($group) {
			$orderby = 'group_seq, forum_seq, '.SPTOPICS.'.post_id DESC';
		} else {
			$orderby = SPTOPICS.'.post_id DESC';
		}

		$query            = new stdClass();
		$query->table     = SPTOPICS;
		$query->fields    = SPTOPICS.'.forum_id, forum_name, forum_slug, forum_disabled, '.SPTOPICS.'.topic_id, topic_name, topic_slug, topic_icon, topic_icon_new, '.SPTOPICS.'.post_count,
								'.SPTOPICS.'.post_id, post_status, post_index, '.SP()->DB->timezone('post_date').',
								guest_name, '.SPPOSTS.'.user_id, post_content, display_name';
		$query->join      = array(SPFORUMS.' ON '.SPFORUMS.'.forum_id = '.SPTOPICS.'.forum_id',
		                          SPGROUPS.' ON '.SPGROUPS.'.group_id = '.SPFORUMS.'.group_id',
		                          SPPOSTS.' ON '.SPPOSTS.'.post_id = '.SPTOPICS.'.post_id');
		$query->left_join = array(SPMEMBERS.' ON '.SPMEMBERS.'.user_id = '.SPPOSTS.'.user_id');
		$query->where     = $where;
		$query->orderby   = $orderby;
		$query            = apply_filters('sph_topic_list_query', $query, $type);
		if (!empty(SP()->user->thisUser->inspect['q_ListTopicView'])) {
			$query->inspect = 'spTopicListView';
			$query->show    = true;
		}
		$records = SP()->DB->select($query);

		# add filters where required plus extra data
		# And the new array
		$list = array();

		if ($records) {
			# check if all forum ids are the same
			$x      = current($records);
			$f      = $x->forum_id;
			$single = 1;
			foreach ($records as $r) {
				if ($r->forum_id != $f) $single = 0;
			}
			reset($records);

			# Now we can grab the supplementary post records where there may be new posts...
			if (SP()->user->thisUser->member) $new = $this->newposts($topicIds);

			# go and grab the first post info if desired
			if ($firstPost) $first = $this->firstposts($topicIds);

			# Some values we need
			# How many topics to a page?
			$ppaged = SP()->core->forumData['display']['posts']['perpage'];
			if (empty($ppaged) || $ppaged == 0) $ppaged = 20;
			# establish topic sort order
			$order = 'ASC'; # default
			if (SP()->core->forumData['display']['posts']['sortdesc']) $order = 'DESC'; # global override
			$listPos = 1;

			$firstforum = array();
			$curforum = 0;

			foreach ($records as $r) {
				$show = true;
				# can the user see this forum?
				if (!SP()->auths->can_view($r->forum_id, 'topic-title')) $show = false;
				# if in moderattion can this user approve posts?
				if ($r->post_status != 0 && !SP()->auths->get('moderate_posts', $r->forum_id)) $show = false;

				if ($show) {
					$t                         = $r->topic_id;
					$list[$t]                  = new stdClass();
					$list[$t]->forum_id        = $r->forum_id;
					$list[$t]->forum_name      = SP()->displayFilters->title($r->forum_name);
					$list[$t]->forum_disabled  = $r->forum_disabled;
					$list[$t]->forum_permalink = SP()->spPermalinks->build_url($r->forum_slug, '', 1, 0);
					$list[$t]->topic_id        = $r->topic_id;
					$list[$t]->topic_name      = SP()->displayFilters->title($r->topic_name);
					$list[$t]->topic_permalink = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, 1, 0);
					$list[$t]->topic_icon      = $r->topic_icon;
					$list[$t]->topic_icon_new  = $r->topic_icon_new;
					$list[$t]->post_count      = $r->post_count;
					$list[$t]->post_id         = $r->post_id;
					$list[$t]->post_status     = $r->post_status;
					$list[$t]->post_date       = $r->post_date;
					$list[$t]->user_id         = $r->user_id;
					$list[$t]->guest_name      = SP()->displayFilters->name($r->guest_name);
					$list[$t]->display_name    = SP()->displayFilters->name($r->display_name);
					if (SP()->auths->can_view($r->forum_id, 'post-content', SP()->user->thisUser->ID, $r->user_id, $r->topic_id, $r->post_id)) {
						$list[$t]->post_tip = ($r->post_status) ? SP()->primitives->front_text('Post awaiting moderation') : SP()->displayFilters->tooltip($r->post_content, $r->post_status);
					} else {
						$list[$t]->post_tip = '';
					}
					$list[$t]->list_position = $listPos;

					if (empty($r->display_name)) $list[$t]->display_name = $list[$t]->guest_name;

					# Lastly determine the page for the post permalink
					if ($order == 'ASC') {
						$page = $r->post_index / $ppaged;
						if (!is_int($page)) $page = intval($page + 1);
					} else {
						$page = $r->post_count - $r->post_index;
						$page = $page / $ppaged;
						$page = intval($page + 1);
					}
					$r->page                  = $page;
					$list[$t]->post_permalink = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, $r->page, $r->post_id, $r->post_index);

					$list[$t]->single_forum = $single;

					if ($group) {
						if (empty($firstforum[$r->forum_id])) {
							$firstforum[$r->forum_id] = 1;
							$list[$t]->new_forum      = 1;
						} else {
							$list[$t]->new_forum = 0;
						}
					} else {
						if ($curforum == $r->forum_id) {
							$list[$t]->new_forum = 0;
						} else {
							$list[$t]->new_forum = 1;
							$curforum = $r->forum_id;
						}
					}

					# add in any new post details if they exist
					if (!empty($new) && array_key_exists($t, $new)) {
						$list[$t]->new_post_count        = $new[$t]->new_post_count;
						$list[$t]->new_post_post_id      = $new[$t]->new_post_post_id;
						$list[$t]->new_post_post_index   = $new[$t]->new_post_post_index;
						$list[$t]->new_post_post_date    = $new[$t]->new_post_post_date;
						$list[$t]->new_post_user_id      = $new[$t]->new_post_user_id;
						$list[$t]->new_post_display_name = $new[$t]->new_post_display_name;
						$list[$t]->new_post_guest_name   = $new[$t]->new_post_guest_name;
						$list[$t]->new_post_permalink    = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, 0, $new[$t]->new_post_post_id, $new[$t]->new_post_post_index);
						if (empty($new[$t]->new_post_display_name)) $list[$t]->new_post_display_name = $new[$t]->new_post_guest_name;
					}

					# add the first post info if desired
					if ($firstPost) {
						$list[$t]->first_post_permalink = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, 0, $first[$t]->post_id, 1);
						$list[$t]->first_post_date      = $first[$t]->post_date;
						$list[$t]->first_user_id        = $first[$t]->user_id;
						$list[$t]->first_guest_name     = SP()->displayFilters->name($first[$t]->guest_name);
						$list[$t]->first_display_name   = SP()->displayFilters->name($first[$t]->display_name);
						if (SP()->auths->can_view($r->forum_id, 'post-content', SP()->user->thisUser->ID, $first[$t]->user_id, $first[$t]->topic_id, $first[$t]->post_id)) {
							$list[$t]->first_post_tip = ($first[$t]->post_status) ? SP()->primitives->front_text('Post awaiting moderation') : SP()->displayFilters->tooltip($first[$t]->post_content, $first[$t]->post_status);
						} else {
							$list[$t]->first_post_tip = '';
						}

						if (empty($list[$t]->first_display_name)) $list[$t]->first_display_name = $list[$t]->first_guest_name;
					} else {
						$list[$t]->first_post_permalink = '';
						$list[$t]->first_post_date      = '';
						$list[$t]->first_user_id        = '';
						$list[$t]->first_guest_name     = '';
						$list[$t]->first_display_name   = '';
						$list[$t]->first_post_tip       = '';
						$list[$t]->first_display_name   = '';
					}

					$list[$t] = apply_filters('sph_topic_list_record', $list[$t], $r, $type);

					$listPos++;
				}
			}
			unset($records);
			unset($new);
			unset($first);
		}

		return $list;
	}

	# --------------------------------------------------------------------------------------
	#
	#	newposts()
	#	Adds first new posts data into the result
	#
	# --------------------------------------------------------------------------------------
	private function newposts($topicIds) {
		$newList = array();

		# check user has new post list values
		if (empty(SP()->user->thisUser->newposts['topics'])) return $newList;

		# First filter topics by those in the users new post list
		$newTopicIds = array();
		$newPostIds  = array();
		foreach ($topicIds as $topic) {
			if (in_array($topic, SP()->user->thisUser->newposts['topics'])) {
				$newTopicIds[] = $topic;
				$newPostIds[]  = SP()->user->thisUser->newposts['post'][array_search($topic, SP()->user->thisUser->newposts['topics'])];
			}
		}

		if ($newTopicIds) {
			$where            = SPPOSTS.'.post_id IN ('.implode(',', $newPostIds).')';
			$query            = new stdClass();
			$query->table     = SPPOSTS;
			$query->fields    = SPPOSTS.'.topic_id, '.SPPOSTS.'.post_id, post_index, '.SP()->DB->timezone('post_date').',
									guest_name, '.SPPOSTS.'.user_id, display_name, (post_count-(post_index-1)) AS new_post_count';
			$query->left_join = array(SPMEMBERS.' ON '.SPMEMBERS.'.user_id = '.SPPOSTS.'.user_id');
			$query->join      = array(SPTOPICS.' ON '.SPPOSTS.'.topic_id = '.SPTOPICS.'.topic_id');
			$query->where     = $where;
			$query->orderby   = 'topic_id, post_id';
			$query            = apply_filters('sph_listview_newposts_query', $query);
			if (!empty(SP()->user->thisUser->inspect['q_ListTopicViewNew'])) {
				$query->inspect = 'spTopicListViewNew';
				$query->show    = true;
			}
			$postrecords = SP()->DB->select($query);

			if ($postrecords) {
				$cTopic = 0;
				foreach ($postrecords as $p) {
					if ($p->topic_id != $cTopic) {
						$cTopic                                  = $p->topic_id;
						$newList[$cTopic]                        = new stdClass();
						$newList[$cTopic]->topic_id              = $cTopic;
						$newList[$cTopic]->new_post_count        = $p->new_post_count;
						$newList[$cTopic]->new_post_post_id      = $p->post_id;
						$newList[$cTopic]->new_post_post_index   = $p->post_index;
						$newList[$cTopic]->new_post_post_date    = $p->post_date;
						$newList[$cTopic]->new_post_user_id      = $p->user_id;
						$newList[$cTopic]->new_post_display_name = SP()->displayFilters->name($p->display_name);
						$newList[$cTopic]->new_post_guest_name   = SP()->displayFilters->name($p->guest_name);
					}
				}
			}
		}

		return $newList;
	}

	# --------------------------------------------------------------------------------------
	#
	#	firstposts()
	#	Populates the first post ina  topic if requested for inclusion
	#
	# --------------------------------------------------------------------------------------
	private function firstposts($topicIds) {
		$first = array();

		$query            = new stdclass();
		$query->table     = SPPOSTS;
		$query->fields    = 'post_id, topic_id, '.SP()->DB->timezone('post_date').', '.SPPOSTS.'.user_id, guest_name, post_content, post_status, display_name';
		$query->left_join = array(SPMEMBERS.' ON '.SPMEMBERS.'.user_id = '.SPPOSTS.'.user_id');
		$query->where     = 'topic_id IN ('.implode(',', $topicIds).') AND post_index=1';
		$query            = apply_filters('sph_listview_firstposts_query', $query);
		if (!empty(SP()->user->thisUser->inspect['q_ListTopicViewFirst'])) {
			$query->inspect = 'spTopicListViewFirst';
			$query->show    = true;
		}
		$postrecords = SP()->DB->select($query);

		if ($postrecords) {
			foreach ($postrecords as $p) {
				$cTopic                       = $p->topic_id;
				$first[$cTopic]               = new stdClass();
				$first[$cTopic]->topic_id     = $cTopic;
				$first[$cTopic]->post_id      = $p->post_id;
				$first[$cTopic]->post_date    = $p->post_date;
				$first[$cTopic]->user_id      = $p->user_id;
				$first[$cTopic]->post_status  = $p->post_status;
				$first[$cTopic]->display_name = $p->display_name;
				$first[$cTopic]->guest_name   = $p->guest_name;
				$first[$cTopic]->post_content = $p->post_content;
			}
		}

		return $first;
	}

	# --------------------------------------------------------------------------------------
	#
	#	topicids()
	#	Populates the topic id list to satisfy required count
	#
	# --------------------------------------------------------------------------------------
	private function topicids($topicIds, $forumIds, $count) {
		if (empty($topicIds)) $topicIds = array();

		if (!empty($forumIds)) {
			if (!is_array($forumIds)) {
				$forumIds = explode(',', $forumIds);
			}
		} else {
			$forumIds = SP()->user->visible_forums();
		}

		# if NO forums ids then we should go no further
		if (empty($forumIds)) {
			return $topicIds;
		}

		# get the auths ID for moderating posts
		$mod = SP()->core->forumData['auths_map']['moderate_posts'];

		# so let's trawl the new topics/post cache
		$sort_data = SP()->meta->get_value('topic_cache', 'new');
		if (!empty($sort_data)) {
			foreach ($sort_data as $t) {

				# 1 - check if topic ID already in list
				if (in_array($t[LISTTOPIC], $topicIds)) continue;

				# 2 - check if topic n forum that can be viewed
				if (!in_array($t[LISTFORUM], $forumIds)) continue;

				# 3 - if post in moderation can user moderate
				if ($t[LISTSTATUS] == true && SP()->user->thisUser->auths[$t[LISTFORUM]][$mod] == false) continue;

				# 4 - so - we can add topic ID to list
				$topicIds[] = $t[LISTTOPIC];

				# 5 - and if we have enough then break the loop
				if (count($topicIds) == $count) break;
			}
		}

		# Only process if there are topics defined
		if (empty($topicIds)) return '';

		return $topicIds;
	}
}
