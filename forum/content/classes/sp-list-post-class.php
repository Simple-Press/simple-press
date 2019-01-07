<?php
/*
Simple:Press
List Post Class
$LastChangedDate: 2016-03-15 03:33:43 -0500 (Tue, 15 Mar 2016) $
$Rev: 14065 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	Returns flat object of posts but with rich data
#	Intended for simple listings of posts - like RSS feeds for example
#
#	Version: 5.0
#
# ==========================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_has_postlist()
#	sp_loop_postlist()
#	sp_the_postlist()
#
#	Returns rich data object of posts using the passed WHERE and ORDER clauses and Count value.
#
#	Instantiate spPostList - The WHERE argument is required
#
#	Pass:	$where:		A complete and valid WHERE clause. For safety always include
#						any table names in full. Do NOT include WHERE keyword
#			$order:		Optional ORDER BY clause. If not passed ordering will be
#						post_id DESC. Do NOT include ORDER BY keywords
#			$count:		Optional count of how many rows to return
#						If not set or zero all resuts of $where will be returned.
#						Do NOT include LIMIT keyword
#			$view:		Auth check styring - see sp_can_view() function
#			$type:		Optional documentation text to use in filters so plugins know usage
#
#	Returns a data object based upon the post ids
#
# --------------------------------------------------------------------------------------

function sp_has_postlist() {
	global $list, $spPostList;
	return $spPostList->sp_has_postlist();
}

function sp_loop_postlist() {
	global $spPostList;
	return $spPostList->sp_loop_postlist();
}

function sp_the_postlist() {
	global $spPostList, $spThisPostList;
	$spThisPostList = $spPostList->sp_the_postlist();
}

# --------------------------------------------------------------------------------------

# ==========================================================================================
#
#	Post List. Post Listing Class
#
# ==========================================================================================

class spPostList {
	# DB query result set
	var $listData = array();

	# Post single row object
	var $postData = '';

	# Internal counter
	var $currentPost = 0;

	# Count of post records
	var $listCount = 0;

	# Run in class instantiation - populates data
	function __construct($where='', $order='', $count=0, $view='forum-title', $type='') {
		$this->listData = $this->sp_postlistview_query($where, $order, $count, $view, $type);
		sp_display_inspector('plv_spPostListView', $this->listData);
	}

	# True if there are Post records
	function sp_has_postlist() {
		if (!empty($this->listData)) {
			$this->listCount = count($this->listData);
			reset($this->listData);
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Post records
	function sp_loop_postlist() {
		if ($this->currentPost > 0) do_action_ref_array('sph_after_post_list', array(&$this));
		$this->currentPost++;
		if ($this->currentPost <= $this->listCount) {
			do_action_ref_array('sph_before_post_list', array(&$this));
			return true;
		} else {
			$this->currentPost = 0;
			$this->listCount = 0;
			unset($this->listData);
			return false;
		}
	}

	# Sets array pointer and returns current Post data
	function sp_the_postlist() {
		$this->postData = current($this->listData);
		sp_display_inspector('plv_spThisListPost', $this->postData);
		next($this->listData);
		return $this->postData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_postlistview_query()
	#	Builds the data structure for the Listview data object
	#
	# --------------------------------------------------------------------------------------
	function sp_postlistview_query($where, $order, $count, $view, $type) {
		global $spGlobals, $spThisUser;

		# If no WHERE clause then return empty
		if (empty($where)) return;

        # build list of forums user can view
        $fids = sp_user_visible_forums($view);
        if (!empty($fids)) {
            $fids = implode(',', $fids);
            $where.= ' AND '.SFPOSTS.".forum_id IN ($fids)";
        }

		# Check order
		if (empty($order)) $order = SFPOSTS.'.post_id DESC';

		$spdb = new spdbComplex;
			$spdb->table		= SFPOSTS;
			$spdb->fields		= SFPOSTS.'.post_id, post_content, '.spdb_zone_datetime('post_date').', '.SFPOSTS.'.topic_id, '.SFPOSTS.'.forum_id,
								  '.SFPOSTS.'.user_id, guest_name, post_status, post_index, forum_name, forum_slug, forum_disabled, '.SFFORUMS.'.group_id, group_name,
								  topic_name, topic_slug, '.SFTOPICS.'.post_count, topic_opened, display_name';
			$spdb->join			= array(SFFORUMS.' ON '.SFFORUMS.'.forum_id = '.SFPOSTS.'.forum_id',
                                        SFGROUPS.' ON '.SFGROUPS.'.group_id = '.SFFORUMS.'.group_id',
										SFTOPICS.' ON '.SFTOPICS.'.topic_id = '.SFPOSTS.'.topic_id');
			$spdb->left_join	= array(SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFPOSTS.'.user_id');
			$spdb->where		= $where;
			$spdb->orderby		= $order;
			if ($count) $spdb->limits = $count;

		$spdb = apply_filters('sph_post_list_query', $spdb, $this, $type);

		if (!empty($spThisUser->inspect['q_spPostListView'])) {
			$spdb->inspect = 'spPostListView';
			$spdb->show = true;
		}

		$records = $spdb->select();

		# Now check authorisations and clean up the object
		$list = array();

		# Some values we need
		# How many topics to a page?
		$ppaged = $spGlobals['display']['posts']['perpage'];
		if (empty($ppaged) || $ppaged == 0) $ppaged = 20;
		# establish topic sort order
		$porder = 'ASC'; # default
		if ($spGlobals['display']['posts']['sortdesc']) $porder = 'DESC'; # global override

		if ($records) {
			$listPos = 1;
			foreach ($records as $r) {
				if (sp_can_view($r->forum_id, 'forum-title')) {
					if ($r->post_status == 0 || sp_get_auth('moderate_posts', $r->forum_id)) {
						$p = $r->post_id;
						$list[$p] = $r;
						# Now apply any necessary filters and data changes
						$list[$p]->post_content		= sp_filter_content_display($r->post_content);
						$list[$p]->post_content_raw	= $r->post_content;
						$list[$p]->forum_name		= sp_filter_title_display($r->forum_name);
						$list[$p]->forum_disabled	= $r->forum_disabled;
					    $list[$p]->forum_permalink	= sp_build_url($r->forum_slug, '', 1, 0);
					    $list[$p]->topic_permalink	= sp_build_url($r->forum_slug, $r->topic_slug, 1, 0);
						$list[$p]->topic_name		= sp_filter_title_display($r->topic_name);
						$list[$p]->topic_opened		= $r->topic_opened;
						$list[$p]->group_name		= sp_filter_title_display($r->group_name);

    					if (sp_can_view($r->forum_id, 'post-content', $spThisUser->ID, $r->user_id, $r->topic_id, $r->post_id)) {
    						$list[$p]->post_tip = ($r->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($r->post_content, $r->post_status);
    					} else {
    						$list[$p]->post_tip = '';
    					}

						# Ensure display name is populated
						if (empty($r->display_name)) $list[$p]->display_name = $list[$p]->guest_name;
						$list[$p]->display_name	= sp_filter_name_display($list[$p]->display_name);

						# determine the page for the post permalink
						if ($porder == 'ASC') {
							$page = $r->post_index / $ppaged;
							if (!is_int($page)) $page = intval($page+1);
						} else {
							$page = $r->post_count - $r->post_index;
							$page = $page / $ppaged;
							$page = intval($page+1);
						}
						$list[$p]->post_permalink = sp_build_url($r->forum_slug, $r->topic_slug, $page, $r->post_id, $r->post_index);

    					$list[$p]->list_position = $listPos;

    					$list[$p] = apply_filters('sph_post_list_record', $list[$p], $r, $type);
					}
				}

				$listPos++;
			}
		}
		return $list;
	}
}
?>