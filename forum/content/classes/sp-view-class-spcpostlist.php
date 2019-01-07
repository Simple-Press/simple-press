<?php
/*
Simple:Press
List Post Class
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
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
#			$view:		Auth check styring - see SP()->auths->can_view() function
#			$type:		Optional documentation text to use in filters so plugins know usage
#
#	Returns a data object based upon the post ids
#
# --------------------------------------------------------------------------------------

class spcPostList {
	# DB query result set
	public $listData = array();

	# Post single row object
	public $postData = '';

	# Internal counter
	public $currentPost = 0;

	# Count of post records
	public $listCount = 0;

	# Run in class instantiation - populates data
	public function __construct($where = '', $order = '', $count = 0, $view = 'forum-title', $type = '') {
		$this->listData = $this->query($where, $order, $count, $view, $type);
		sp_display_inspector('plv_listPosts', $this->listData);
	}

	# True if there are Post records
	public function has_postlist() {
		if (!empty($this->listData)) {
			$this->listCount = count($this->listData);
			reset($this->listData);

			return true;
		} else {
			return false;
		}
	}

	# Loop control on Post records
	public function loop_postlist() {
		if ($this->currentPost > 0) do_action_ref_array('sph_after_post_list', array(&$this));
		$this->currentPost++;
		if ($this->currentPost <= $this->listCount) {
			do_action_ref_array('sph_before_post_list', array(&$this));

			return true;
		} else {
			$this->currentPost = 0;
			$this->listCount   = 0;
			unset($this->listData);

			return false;
		}
	}

	# Sets array pointer and returns current Post data
	public function the_postlist() {
		$this->postData = current($this->listData);
		sp_display_inspector('plv_thisListPost', $this->postData);
		next($this->listData);

		return $this->postData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_postlistview_query()
	#	Builds the data structure for the Listview data object
	#
	# --------------------------------------------------------------------------------------
	private function query($where, $order, $count, $view, $type) {
		# If no WHERE clause then return empty
		if (empty($where)) return array();

		# build list of forums user can view
		$fids = SP()->user->visible_forums($view);
		if (!empty($fids)) {
			$fids = implode(',', $fids);
			$where .= ' AND '.SPPOSTS.".forum_id IN ($fids)";
		}

		# Check order
		if (empty($order)) $order = SPPOSTS.'.post_id DESC';

		$query            = new stdClass();
		$query->table     = SPPOSTS;
		$query->fields    = SPPOSTS.'.post_id, post_content, '.SP()->DB->timezone('post_date').', '.SPPOSTS.'.topic_id, '.SPPOSTS.'.forum_id,
								'.SPPOSTS.'.user_id, guest_name, post_status, post_index, forum_name, forum_slug, forum_disabled, '.SPFORUMS.'.group_id, group_name,
								topic_name, topic_slug, '.SPTOPICS.'.post_count, topic_opened, display_name';
		$query->join      = array(SPFORUMS.' ON '.SPFORUMS.'.forum_id = '.SPPOSTS.'.forum_id',
		                          SPGROUPS.' ON '.SPGROUPS.'.group_id = '.SPFORUMS.'.group_id',
		                          SPTOPICS.' ON '.SPTOPICS.'.topic_id = '.SPPOSTS.'.topic_id');
		$query->left_join = array(SPMEMBERS.' ON '.SPMEMBERS.'.user_id = '.SPPOSTS.'.user_id');
		$query->where     = $where;
		$query->orderby   = $order;
		if ($count) $query->limits = $count;
		$query = apply_filters('sph_post_list_query', $query, $type);
		if (!empty(SP()->user->thisUser->inspect['q_ListPostView'])) {
			$query->inspect = 'spPostListView';
			$query->show    = true;
		}
		$records = SP()->DB->select($query);

		# Now check authorisations and clean up the object
		$list = array();

		# Some values we need
		# How many topics to a page?
		$ppaged = SP()->core->forumData['display']['posts']['perpage'];
		if (empty($ppaged) || $ppaged == 0) $ppaged = 20;
		# establish topic sort order
		$porder = 'ASC'; # default
		if (SP()->core->forumData['display']['posts']['sortdesc']) $porder = 'DESC'; # global override

		if ($records) {
			$listPos = 1;
			foreach ($records as $r) {
				if (SP()->auths->can_view($r->forum_id, 'forum-title')) {
					if ($r->post_status == 0 || SP()->auths->get('moderate_posts', $r->forum_id)) {
						$p        = $r->post_id;
						$list[$p] = $r;
						# Now apply any necessary filters and data changes
						$list[$p]->post_content     = SP()->displayFilters->content($r->post_content);
						$list[$p]->post_content_raw = $r->post_content;
						$list[$p]->forum_name       = SP()->displayFilters->title($r->forum_name);
						$list[$p]->forum_disabled   = $r->forum_disabled;
						$list[$p]->forum_permalink  = SP()->spPermalinks->build_url($r->forum_slug, '', 1, 0);
						$list[$p]->topic_permalink  = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, 1, 0);
						$list[$p]->topic_name       = SP()->displayFilters->title($r->topic_name);
						$list[$p]->topic_opened     = $r->topic_opened;
						$list[$p]->group_name       = SP()->displayFilters->title($r->group_name);

						if (SP()->auths->can_view($r->forum_id, 'post-content', SP()->user->thisUser->ID, $r->user_id, $r->topic_id, $r->post_id)) {
							$list[$p]->post_tip = ($r->post_status) ? SP()->primitives->front_text('Post awaiting moderation') : SP()->displayFilters->tooltip($r->post_content, $r->post_status);
						} else {
							$list[$p]->post_tip = '';
						}

						# Ensure display name is populated
						if (empty($r->display_name)) $list[$p]->display_name = $list[$p]->guest_name;
						$list[$p]->display_name = SP()->displayFilters->name($list[$p]->display_name);

						# determine the page for the post permalink
						if ($porder == 'ASC') {
							$page = $r->post_index / $ppaged;
							if (!is_int($page)) $page = intval($page + 1);
						} else {
							$page = $r->post_count - $r->post_index;
							$page = $page / $ppaged;
							$page = intval($page + 1);
						}
						$list[$p]->post_permalink = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, $page, $r->post_id, $r->post_index);

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
