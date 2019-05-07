<?php
/*
Simple:Press
Topic View Class
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

class spcTopicView {
	# Status: 'data', 'no access', 'no data', 'sneak peek'
	public $topicViewStatus = '';

	# The parent forum id
	public $parentForum = 0;

	# True while the post loop is being rendered
	public $inPostLoop = false;

	# Topic View DB query result set
	public $pageData = array();

	# Topic single row object
	public $topicData = '';

	# The topic id
	public $topicId = 0;

	# The PAGE being requested (page ID)
	public $topicPage = 0;

	# Topic View DB Posts result set
	public $pagePostData = array();

	# Post single row object
	public $postData = '';

	# Internal counter
	public $currentPost = 0;

	# Count of post records
	public $postCount = 0;

	# Run in class instantiation - populates data
	public function __construct($id = 0, $page = 0) {
		if (($id == 0) && (!empty(SP()->rewrites->pageData['topicid']))) $id = SP()->rewrites->pageData['topicid'];
		$this->topicId     = $id;
		$this->parentForum = SP()->rewrites->pageData['forumid'];

		if (($page == 0) && (!empty(SP()->rewrites->pageData['page']))) $page = SP()->rewrites->pageData['page'];
		$this->topicPage = $page;
		$this->pageData  = $this->query($this->topicId, $this->topicPage, $this->parentForum);
		sp_display_inspector('tv_topics', $this->pageData);
	}

	# Return status and returns Topic data
	public function this_topic() {
		# Check for no access to topic or no data
		if ($this->topicViewStatus != 'data') return false;
		reset($this->pageData);
		$this->topicData = current($this->pageData);
		sp_display_inspector('tv_thisTopic', $this->topicData);

		return $this->topicData;
	}

	# True if there are Post records
	public function has_posts() {
		if (!empty($this->topicData->posts)) {
			$this->pagePostData = $this->topicData->posts;
			$this->postCount    = count($this->pagePostData);
			$this->inPostLoop   = true;

			return true;
		} else {
			return false;
		}
	}

	# Loop control on Post records
	public function loop_posts() {
		if ($this->currentPost > 0) do_action_ref_array('sph_after_post', array(&$this));
		$this->currentPost++;
		if ($this->currentPost <= $this->postCount) {
			do_action_ref_array('sph_before_post', array(&$this));

			return true;
		} else {
			$this->inPostLoop  = false;
			$this->currentPost = 0;
			$this->postCount   = 0;
			unset($this->pagePostData);

			return false;
		}
	}

	# Sets array pointer and returns current Post data
	public function the_post() {
		$this->postData = current($this->pagePostData);
		sp_display_inspector('tv_thisPost', $this->postData);
		next($this->pagePostData);

		return $this->postData;
	}

	private function query($topicid = 0, $cPage = 1, $forumid = 0) {
		# do we have a valid topic id
		if ($topicid == 0) {
			$this->topicViewStatus = 'no data';

			return array();
		} else {
			$WHERE = SPTOPICS.'.topic_id='.$topicid;
		}

		# default to no access
		$this->topicViewStatus = 'no access';

		# some setup vars
		$startlimit = 0;
		$lastpage   = 0;

		# how many posts per page?
		$ppaged = SP()->core->forumData['display']['posts']['perpage'];
		if (!$ppaged) $ppaged = 10;

		# setup where we are in the post list (paging)
		if ($cPage != 1) $startlimit = ((($cPage - 1) * $ppaged));
		$LIMIT = $startlimit.', '.$ppaged;

		# Set up order by
		$setSort   = false;
		$reverse   = false;
		$setSort   = SP()->core->forumData['display']['posts']['sortdesc'];
		$sort_data = SP()->meta->get_value('sort_order', 'topic');
		if (!empty($sort_data)) {
			$reverse = (array_search($topicid, (array) $sort_data) !== false) ? true : false;
		}
		if (isset(SP()->user->thisUser->postDESC) && SP()->user->thisUser->postDESC) {
			$reverse = !$reverse;
		}
		if ($setSort XOR $reverse) {
			$ORDER = 'post_pinned DESC, '.SPPOSTS.".post_id DESC";
		} else {
			$ORDER = 'post_pinned DESC, '.SPPOSTS.".post_id ASC";
		}

		# Discover if this topic is in users new post list
		$maybeNewPost = false;
		if (SP()->user->thisUser->member && sp_is_in_users_newposts($topicid)) $maybeNewPost = true;

		# retrieve topic and post records
		$query             = new stdClass();
		$query->table      = SPTOPICS;
		$query->found_rows = true;
		$query->fields     = 'group_id, '.SPTOPICS.'.topic_id, '.SPTOPICS.'.forum_id, topic_name, topic_slug, topic_status, topic_pinned, topic_icon, topic_opened, '.SPTOPICS.'.post_count, forum_name, forum_slug, forum_status,
								forum_disabled, forum_rss_private, '.SPPOSTS.'.post_id, '.SP()->DB->timezone('post_date').', '.SPPOSTS.'.user_id, '.SPTOPICS.'.user_id AS topic_starter,
								guest_name, guest_email, post_status, post_pinned, post_index, post_edit, poster_ip, source, post_content, NULL AS new_post';
		$query->join       = array(SPPOSTS.' ON '.SPTOPICS.'.topic_id='.SPPOSTS.'.topic_id',
		                           SPFORUMS.' ON '.SPTOPICS.'.forum_id='.SPFORUMS.'.forum_id');
		$query->where      = $WHERE;
		$query->orderby    = $ORDER;
		$query->limits     = $LIMIT;

		$query = apply_filters('sph_topicview_query', $query);

		if (!empty(SP()->user->thisUser->inspect['q_TopicView'])) {
			$query->inspect = 'spTopicView';
			$query->show    = true;
		}
		$records = SP()->DB->select($query);

		$t = array();
		if ($records) {
			$tidx = $topicid;
			$pidx = 0;

			$r = current($records);
			if (SP()->auths->get('view_forum', $r->forum_id)) {
				$this->topicViewStatus = 'data';

				# construct the parent topic object
				$t[$tidx]                    = new stdClass();
				$t[$tidx]->topic_id          = $r->topic_id;
				$t[$tidx]->forum_id          = $r->forum_id;
				$t[$tidx]->group_id          = $r->group_id;
				$t[$tidx]->forum_name        = SP()->displayFilters->title($r->forum_name);
				$t[$tidx]->topic_name        = SP()->displayFilters->title($r->topic_name);
				$t[$tidx]->topic_slug        = $r->topic_slug;
				$t[$tidx]->topic_opened      = $r->topic_opened;
				$t[$tidx]->forum_status      = $r->forum_status;
				$t[$tidx]->topic_pinned      = $r->topic_pinned;
				$t[$tidx]->forum_disabled    = $r->forum_disabled;
				$t[$tidx]->forum_slug        = $r->forum_slug;
				$t[$tidx]->forum_rss_private = $r->forum_rss_private;
				$t[$tidx]->topic_permalink   = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, 1, 0);
				$t[$tidx]->topic_status      = $r->topic_status;
				$t[$tidx]->topic_icon        = $r->topic_icon;
				$t[$tidx]->rss               = '';
				$t[$tidx]->editmode          = 0;
				$t[$tidx]->tools_flag        = 1;
				$t[$tidx]->display_page      = $this->topicPage;
				$t[$tidx]->posts_per_page    = $ppaged;
				$t[$tidx]->unread            = 0;

				# user calc_rows and nor post_count as - for example - some posts may be hiodden by choice.
				$t[$tidx]->post_count = SP()->DB->select('SELECT FOUND_ROWS()', 'var');

				# Can the user create new topics or should we lock the forum?
				$t[$tidx]->start_topics     = SP()->auths->get('start_topics', $r->forum_id);
				$t[$tidx]->reply_topics     = SP()->auths->get('reply_topics', $r->forum_id);
				$t[$tidx]->reply_own_topics = SP()->auths->get('reply_own_topics', $r->forum_id);

				# grab topic start info
				$t[$tidx]->topic_starter = $r->topic_starter;

				$totalPages = ($r->post_count / $ppaged);
				if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
				$t[$tidx]->total_pages = $totalPages;

				if ($setSort XOR $reverse) {
					if ($cPage == 1) $lastpage = true;
				} else {
					if ($cPage == $totalPages) $lastpage = true;
				}
				$t[$tidx]->last_page = $lastpage;

				$t[$tidx] = apply_filters('sph_topicview_topic_record', $t[$tidx], $r);

				reset($records);
				unset($r);

				# now loop through the post records
				$firstPostPage = 1;
				$pinned        = 0;

				# if tiopic in new post list then grab read to post_id
				$newPostId = 0;
				if (SP()->user->thisUser->member && in_array($tidx, SP()->user->thisUser->newposts['topics'])) {
					$newPostId = SP()->user->thisUser->newposts['post'][array_search($tidx, SP()->user->thisUser->newposts['topics'])];
				}

				# define post id and post user id arrays for plugins to use in combined filter
				$p = array();
				$u = array();

				foreach ($records as $r) {
					$pidx = $r->post_id;
					$p[]  = $pidx;
					# prepare for user object
					$cUser = (SP()->user->thisUser->ID == $r->user_id);

					$cSmall                                     = (!$cUser);
					$t[$tidx]->posts[$pidx]                     = new stdClass();
					$t[$tidx]->posts[$pidx]->post_id            = $r->post_id;
					$t[$tidx]->posts[$pidx]->post_date          = $r->post_date;
					$t[$tidx]->posts[$pidx]->user_id            = $r->user_id;
					$t[$tidx]->posts[$pidx]->guest_name         = SP()->displayFilters->name($r->guest_name);
					$t[$tidx]->posts[$pidx]->guest_email        = SP()->displayFilters->email($r->guest_email);
					$t[$tidx]->posts[$pidx]->post_status        = $r->post_status;
					$t[$tidx]->posts[$pidx]->post_pinned        = $r->post_pinned;
					$t[$tidx]->posts[$pidx]->post_index         = $r->post_index;
					$t[$tidx]->posts[$pidx]->poster_ip          = $r->poster_ip;
					$t[$tidx]->posts[$pidx]->source             = $r->source;
					$t[$tidx]->posts[$pidx]->post_permalink     = SP()->spPermalinks->build_url($r->forum_slug, $r->topic_slug, $cPage, $r->post_id);
					$t[$tidx]->posts[$pidx]->edits              = '';
					$t[$tidx]->posts[$pidx]->last_post          = 0;
					$t[$tidx]->posts[$pidx]->last_post_on_page  = 0;
					$t[$tidx]->posts[$pidx]->first_post_on_page = $firstPostPage;
					$t[$tidx]->posts[$pidx]->editmode           = 0;
					$t[$tidx]->posts[$pidx]->post_content       = SP()->displayFilters->content($r->post_content);
					$t[$tidx]->posts[$pidx]->first_pinned       = 0;
					$t[$tidx]->posts[$pidx]->last_pinned        = 0;
					$t[$tidx]->posts[$pidx]->postUser           = new stdClass();
					$t[$tidx]->posts[$pidx]->postUser           = clone SP()->user->get($r->user_id, $cUser, $cSmall);
					$t[$tidx]->posts[$pidx]->new_post           = 0;

					# populate the user guest name and email in case the poster is a guest
					if ($r->user_id == 0) {
						$t[$tidx]->posts[$pidx]->postUser->guest_name   = $t[$tidx]->posts[$pidx]->guest_name;
						$t[$tidx]->posts[$pidx]->postUser->guest_email  = $t[$tidx]->posts[$pidx]->guest_email;
						$t[$tidx]->posts[$pidx]->postUser->display_name = $t[$tidx]->posts[$pidx]->guest_name;
						$t[$tidx]->posts[$pidx]->postUser->ip           = $t[$tidx]->posts[$pidx]->poster_ip;
					}

					# pinned status
					if ($firstPostPage == 1 && $r->post_pinned) {
						$t[$tidx]->posts[$pidx]->first_pinned = true;
						$pinned                               = $pidx;
					}
					if ($firstPostPage == 0 && $pinned > 0 && $r->post_pinned == false) {
						$t[$tidx]->posts[$pinned]->last_pinned = true;
					} elseif ($r->post_pinned) {
						$pinned = $pidx;
					}

					$firstPostPage = 0;

					# Is this a new post for the current user?
					if ($newPostId != 0) {
						$t[$tidx]->posts[$pidx]->new_post = ($pidx >= $newPostId) ? true : false;
					}

					# do we need to hide an admin post?
					if (!SP()->auths->get('view_admin_posts', $r->forum_id) && SP()->auths->forum_admin($r->user_id)) {
						$adminview = SP()->meta->get('adminview', 'message');
						if ($adminview) {
							$t[$tidx]->posts[$pidx]->post_content = '<div class="spMessage">';
							$t[$tidx]->posts[$pidx]->post_content .= SP()->displayFilters->text($adminview[0]['meta_value']);
							$t[$tidx]->posts[$pidx]->post_content .= '</div>';
						} else {
							$t[$tidx]->posts[$pidx]->post_content = '';
						}
					}

					# do we need to hide an others posts?
					if (SP()->auths->get('view_own_admin_posts', $r->forum_id) && !SP()->auths->forum_admin($r->user_id) && !SP()->auths->forum_mod($r->user_id) && SP()->user->thisUser->ID != $r->user_id) {
						$userview = SP()->meta->get('userview', 'message');
						if ($userview) {
							$t[$tidx]->posts[$pidx]->post_content = '<div class="spMessage">';
							$t[$tidx]->posts[$pidx]->post_content .= SP()->displayFilters->text($userview[0]['meta_value']);
							$t[$tidx]->posts[$pidx]->post_content .= '</div>';
						} else {
							$t[$tidx]->posts[$pidx]->post_content = '';
						}
					}

					# Is this post to be edited?
					if (SP()->rewrites->pageData['displaymode'] == 'edit' && SP()->rewrites->pageData['postedit'] == $r->post_id) {
						$t[$tidx]->editmode               = 1;
						$t[$tidx]->editpost_id            = $r->post_id;
						$t[$tidx]->editpost_content       = SP()->editFilters->content($r->post_content);
						$t[$tidx]->posts[$pidx]->editmode = 1;
					}

					# Add edit history
					if (!empty($r->post_edit) && is_serialized($r->post_edit)) {
						$t[$tidx]->posts[$pidx]->edits = array();
						$edits = unserialize($r->post_edit);
						$eidx  = 0;
						foreach ($edits as $e) {
							$t[$tidx]->posts[$pidx]->edits[$eidx]     = new stdClass();
							$t[$tidx]->posts[$pidx]->edits[$eidx]->by = $e['by'];
							$t[$tidx]->posts[$pidx]->edits[$eidx]->at = $e['at'];
							$eidx++;
						}
					}

					if (!in_array($r->user_id, $u)) $u[] = $r->user_id;

					$t[$tidx]->posts[$pidx] = apply_filters('sph_topicview_post_records', $t[$tidx]->posts[$pidx], $r);
				}

				# index of post IDs with position in listing
				$t[$tidx]->post_keys = $p;

				$t[$tidx]->posts[$pidx]->last_post         = $lastpage;
				$t[$tidx]->posts[$pidx]->last_post_on_page = 1;

				# save last post on page id
				$t[$tidx]->last_post_id = $r->post_id;

				# adjust this users newpost post marker
				if (SP()->user->thisUser->member == true && $newPostId != 0) {
					if ($lastpage == true) {
						# topic can be removed from new post list
						sp_remove_users_newposts($tidx, SP()->user->thisUser->ID, true);
					} else {
						# move the post marker to the first post of the next page
						sp_bump_users_newposts($tidx, $r->post_index);
					}
				}

				# allow plugins to add more data to combined topic/post data structure
				$t[$tidx] = apply_filters('sph_topicview_combined_data', $t[$tidx], $p, $u);

				unset($records);
			} else {
				# check for view forum lists but not topic lists
				if (SP()->auths->can_view($r->forum_id, 'forum-title')) $this->topicViewStatus = 'sneak peek';
			}
		}

		return $t;
	}
}
