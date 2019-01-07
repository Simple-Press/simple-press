<?php
/*
Simple:Press
Topic View Class
$LastChangedDate: 2017-01-27 11:05:53 -0600 (Fri, 27 Jan 2017) $
$Rev: 15112 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#	Version: 5.0
#
#	sp_this_topic()
#
#	Topic load function from the TopicView template
#
# --------------------------------------------------------------------------------------

function sp_this_topic($id=0, $page=0) {
	global $spTopicView, $spThisTopic;
	$spTopicView = new spTopicView($id, $page);
	$spThisTopic = $spTopicView->sp_this_topic();
	return $spThisTopic;
}

# --------------------------------------------------------------------------------------

# --------------------------------------------------------------------------------------
#
#	sp_has_posts()
#	sp_loop_posts()
#	sp_the_posts()
#
#	Post Loop functions from the TopicView template
#
# --------------------------------------------------------------------------------------

function sp_has_posts() {
	global $spTopicView;
	return $spTopicView->sp_has_posts();
}

function sp_loop_posts() {
	global $spTopicView;
	return $spTopicView->sp_loop_posts();
}

function sp_the_post() {
	global $spTopicView, $spThisPost, $spThisPostUser;
	$spThisPost = $spTopicView->sp_the_post();
	$spThisPostUser = $spThisPost->postUser;
	sp_display_inspector('tv_spThisPostUser', $spThisPostUser);
}

# --------------------------------------------------------------------------------------


# ==========================================================================================
#
#	Topic View. Topic and Posts Listing Class
#
# ==========================================================================================

class spTopicView {
	# Status: 'data', 'no access', 'no data', 'sneak peek'
	var $topicViewStatus = '';

	# The parent forum id
	var $parentForum = 0;

	# True while the post loop is being rendered
	var $inPostLoop = false;

	# Topic View DB query result set
	var $pageData = array();

	# Topic single row object
	var $topicData = '';

	# The topic id
	var $topicId = 0;

	# The PAGE being requested (page ID)
	var $topicPage = 0;

	# Topic View DB Posts result set
	var $pagePostData = array();

	# Post single row object
	var $postData = '';

	# Internal counter
	var $currentPost = 0;

	# Count of post records
	var $postCount = 0;

	# Run in class instantiation - populates data
	function __construct($id=0, $page=0) {
		global $spVars;
		if (($id==0) && (!empty($spVars['topicid']))) $id = $spVars['topicid'];
		$this->topicId = $id;
		$this->parentForum = $spVars['forumid'];

		if (($page==0) && (!empty($spVars['page']))) $page = $spVars['page'];
		$this->topicPage = $page;
		$this->pageData = $this->sp_topicview_query($this->topicId, $this->topicPage, $this->parentForum);
		sp_display_inspector('tv_spTopicView', $this->pageData);
	}

	# Return status and returns Topic data
	function sp_this_topic() {
		# Check for no access to topic or no data
		if ($this->topicViewStatus != 'data') return false;
		reset($this->pageData);
		$this->topicData = current($this->pageData);
		sp_display_inspector('tv_spThisTopic', $this->topicData);
		return $this->topicData;
	}

	# True if there are Post records
	function sp_has_posts() {
		if (!empty($this->topicData->posts)) {
			$this->pagePostData = $this->topicData->posts;
			$this->postCount = count($this->pagePostData);
			$this->inPostLoop = true;
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Post records
	function sp_loop_posts() {
		if ($this->currentPost > 0) do_action_ref_array('sph_after_post', array(&$this));
		$this->currentPost++;
		if ($this->currentPost <= $this->postCount) {
			do_action_ref_array('sph_before_post', array(&$this));
			return true;
		} else {
			$this->inPostLoop = false;
			$this->currentPost = 0;
			$this->postCount = 0;
			unset($this->pagePostData);
			return false;
		}
	}

	# Sets array pointer and returns current Post data
	function sp_the_post() {
		$this->postData = current($this->pagePostData);
		sp_display_inspector('tv_spThisPost', $this->postData);
		next($this->pagePostData);
		return $this->postData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_topicview_query()
	#	Builds the data structure for the TopicView template
	#
	#	$forumid:
	#	$topicid:
	#	$page:		What oage are we calling for
	#
	#	Internally calls:
	#
	# --------------------------------------------------------------------------------------
	function sp_topicview_query($topicid=0, $cPage=1, $forumid=0) {
		global $spGlobals, $spThisUser, $spVars;

		# do we have a valid topic id
		if ($topicid == 0) {
			$this->topicViewStatus = 'no data';
			return;
		} else {
			$WHERE = SFTOPICS.'.topic_id='.$topicid;
		}

		# default to no access
		$this->topicViewStatus = 'no access';

		# some setup vars
		$startlimit = 0;
		$lastpage = 0;

		# how many posts per page?
		$ppaged = $spGlobals['display']['posts']['perpage'];
		if (!$ppaged) $ppaged = 10;

		# setup where we are in the post list (paging)
		if ($cPage != 1) $startlimit = ((($cPage-1) * $ppaged));
		$LIMIT = $startlimit.', '.$ppaged;

		# Set up order by
		$setSort = false;
		$reverse = false;
		$setSort = $spGlobals['display']['posts']['sortdesc'];
		if (isset($spGlobals['sort_order']['topic'])) {
			$reverse = (array_search($topicid, (array) $spGlobals['sort_order']['topic']) !== false) ? true : false;
		}
		if (isset($spThisUser->postDESC) && $spThisUser->postDESC) {
			$reverse = !$reverse;
		}
		if ($setSort XOR $reverse) {
			$ORDER = 'post_pinned DESC, '.SFPOSTS.".post_id DESC";
		} else {
			$ORDER = 'post_pinned DESC, '.SFPOSTS.".post_id ASC";
		}

		# Discover if this topic is in users new post list
		$maybeNewPost = false;
		if ($spThisUser->member && sp_is_in_users_newposts($topicid)) $maybeNewPost=true;

		# retrieve topic and post records
		$spdb = new spdbComplex;
			$spdb->table	= SFTOPICS;
			$spdb->found_rows	= true;
			$spdb->fields	= 'group_id, '.SFTOPICS.'.topic_id, '.SFTOPICS.'.forum_id, topic_name, topic_slug, topic_status, topic_pinned, topic_icon, topic_opened, '.SFTOPICS.'.post_count, forum_name, forum_slug, forum_status,
							  forum_disabled, forum_rss_private, '.SFPOSTS.'.post_id, '.spdb_zone_datetime('post_date').', '.SFPOSTS.'.user_id, '.SFTOPICS.'.user_id AS topic_starter,
							  guest_name, guest_email, post_status, post_pinned, post_index, post_edit, poster_ip, source, post_content, NULL AS new_post';
			$spdb->join		= array(SFPOSTS.' ON '.SFTOPICS.'.topic_id='.SFPOSTS.'.topic_id',
									SFFORUMS.' ON '.SFTOPICS.'.forum_id='.SFFORUMS.'.forum_id');
			$spdb->where	= $WHERE;
			$spdb->orderby	= $ORDER;
			$spdb->limits	= $LIMIT;

		$spdb = apply_filters('sph_topicview_query', $spdb, $this);

		if (!empty($spThisUser->inspect['q_spTopicView'])) {
			$spdb->inspect = 'spTopicView';
			$spdb->show = true;
		}

		$records = $spdb->select();

		$t = array();
		if ($records) {
			$tidx = $topicid;
			$pidx = 0;

			$r = current($records);
			if (sp_get_auth('view_forum', $r->forum_id)) {
				$this->topicViewStatus = 'data';

				# construct the parent topic object
				$t[$tidx] = new stdClass();
				$t[$tidx]->topic_id				= $r->topic_id;
				$t[$tidx]->forum_id				= $r->forum_id;
				$t[$tidx]->group_id				= $r->group_id;
				$t[$tidx]->forum_name			= sp_filter_title_display($r->forum_name);
				$t[$tidx]->topic_name			= sp_filter_title_display($r->topic_name);
				$t[$tidx]->topic_slug			= $r->topic_slug;
				$t[$tidx]->topic_opened			= $r->topic_opened;
				$t[$tidx]->forum_status			= $r->forum_status;
				$t[$tidx]->topic_pinned			= $r->topic_pinned;
				$t[$tidx]->forum_disabled		= $r->forum_disabled;
				$t[$tidx]->forum_slug			= $r->forum_slug;
				$t[$tidx]->forum_rss_private	= $r->forum_rss_private;
				$t[$tidx]->topic_permalink		= sp_build_url($r->forum_slug, $r->topic_slug, 1, 0);
				$t[$tidx]->topic_status			= $r->topic_status;
				$t[$tidx]->topic_icon			= sanitize_file_name($r->topic_icon);
				$t[$tidx]->rss					= '';
				$t[$tidx]->editmode				= 0;
				$t[$tidx]->tools_flag			= 1;
				$t[$tidx]->display_page			= $this->topicPage;
				$t[$tidx]->posts_per_page		= $ppaged;
				$t[$tidx]->unread				= 0;

				# user calc_rows and nor post_count as - for example - some posts may be hiodden by choice.
				$t[$tidx]->post_count			= spdb_select('var', 'SELECT FOUND_ROWS()');

				# Can the user create new topics or should we lock the forum?
				$t[$tidx]->start_topics			= sp_get_auth('start_topics', $r->forum_id);
				$t[$tidx]->reply_topics			= sp_get_auth('reply_topics', $r->forum_id);
				$t[$tidx]->reply_own_topics		= sp_get_auth('reply_own_topics', $r->forum_id);

				# grab topic start info
				$t[$tidx]->topic_starter = $r->topic_starter;

				$totalPages = ($r->post_count / $ppaged);
				if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
				$t[$tidx]->total_pages			= $totalPages;

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
				$pinned = 0;

				# if tiopic in new post list then grab read to post_id
				$newPostId = 0;
				if ($spThisUser->member && in_array($tidx, $spThisUser->newposts['topics'])) {
					$newPostId = $spThisUser->newposts['post'][array_search($tidx, $spThisUser->newposts['topics'])];
				}

				# define post id and post user id arrays for plugins to use in combined filter
				$p = array();
				$u = array();

				foreach ($records as $r) {
					$pidx = $r->post_id;
					$p[] = $pidx;
					# prepare for user object
					$cUser = ($spThisUser->ID == $r->user_id);

					$cSmall = (!$cUser);
					$t[$tidx]->posts[$pidx] = new stdClass();
					$t[$tidx]->posts[$pidx]->post_id			= $r->post_id;
					$t[$tidx]->posts[$pidx]->post_date			= $r->post_date;
					$t[$tidx]->posts[$pidx]->user_id			= $r->user_id;
					$t[$tidx]->posts[$pidx]->guest_name			= sp_filter_name_display($r->guest_name);
					$t[$tidx]->posts[$pidx]->guest_email		= sp_filter_email_display($r->guest_email);
					$t[$tidx]->posts[$pidx]->post_status		= $r->post_status;
					$t[$tidx]->posts[$pidx]->post_pinned		= $r->post_pinned;
					$t[$tidx]->posts[$pidx]->post_index			= $r->post_index;
					$t[$tidx]->posts[$pidx]->poster_ip			= $r->poster_ip;
					$t[$tidx]->posts[$pidx]->source				= $r->source;
					$t[$tidx]->posts[$pidx]->post_permalink		= sp_build_url($r->forum_slug, $r->topic_slug, $cPage, $r->post_id);
					$t[$tidx]->posts[$pidx]->edits				= '';
					$t[$tidx]->posts[$pidx]->last_post			= 0;
					$t[$tidx]->posts[$pidx]->last_post_on_page	= 0;
					$t[$tidx]->posts[$pidx]->first_post_on_page = $firstPostPage;
					$t[$tidx]->posts[$pidx]->editmode			= 0;
					$t[$tidx]->posts[$pidx]->post_content		= sp_filter_content_display($r->post_content);
					$t[$tidx]->posts[$pidx]->first_pinned		= 0;
					$t[$tidx]->posts[$pidx]->last_pinned		= 0;
					$t[$tidx]->posts[$pidx]->postUser			= new stdClass();
					$t[$tidx]->posts[$pidx]->postUser			= clone sp_get_user($r->user_id, $cUser, $cSmall);
					$t[$tidx]->posts[$pidx]->new_post			= 0;

					# populate the user guest name and email in case the poster is a guest
					if ($r->user_id == 0) {
						$t[$tidx]->posts[$pidx]->postUser->guest_name	= $t[$tidx]->posts[$pidx]->guest_name;
						$t[$tidx]->posts[$pidx]->postUser->guest_email	= $t[$tidx]->posts[$pidx]->guest_email;
						$t[$tidx]->posts[$pidx]->postUser->display_name = $t[$tidx]->posts[$pidx]->guest_name;
						$t[$tidx]->posts[$pidx]->postUser->ip 			= $t[$tidx]->posts[$pidx]->poster_ip;
					}

					# pinned status
					if ($firstPostPage == 1 && $r->post_pinned) {
						$t[$tidx]->posts[$pidx]->first_pinned = true;
						$pinned = $pidx;
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
					if (!sp_get_auth('view_admin_posts', $r->forum_id) && sp_is_forum_admin($r->user_id)) {
						$adminview = sp_get_sfmeta('adminview', 'message');
						if ($adminview) {
							$t[$tidx]->posts[$pidx]->post_content = '<div class="spMessage">';
							$t[$tidx]->posts[$pidx]->post_content.= sp_filter_text_display($adminview[0]['meta_value']);
							$t[$tidx]->posts[$pidx]->post_content.= '</div>';
						} else {
							$t[$tidx]->posts[$pidx]->post_content = '';
						}
					}

					# do we need to hide an others posts?
					if (sp_get_auth('view_own_admin_posts', $r->forum_id) && !sp_is_forum_admin($r->user_id) && !sp_is_forum_mod($r->user_id) && $spThisUser->ID != $r->user_id) {
						$userview = sp_get_sfmeta('userview', 'message');
						if ($userview) {
							$t[$tidx]->posts[$pidx]->post_content = '<div class="spMessage">';
							$t[$tidx]->posts[$pidx]->post_content.= sp_filter_text_display($userview[0]['meta_value']);
							$t[$tidx]->posts[$pidx]->post_content.= '</div>';
						} else {
							$t[$tidx]->posts[$pidx]->post_content = '';
						}
					}

					# Is this post to be edited?
					if ($spVars['displaymode'] == 'edit' && $spVars['postedit'] == $r->post_id) {
						$t[$tidx]->editmode						= 1;
						$t[$tidx]->editpost_id					= $r->post_id;
						$t[$tidx]->editpost_content				= sp_filter_content_edit($r->post_content);
						$t[$tidx]->posts[$pidx]->editmode		= 1;
					}

					# Add edit history
					if (!empty($r->post_edit) && is_serialized($r->post_edit)) {
						$t[$tidx]->posts[$pidx]->edits = array();
						$edits = unserialize($r->post_edit);
						$eidx = 0;
						foreach ($edits as $e) {
							$t[$tidx]->posts[$pidx]->edits[$eidx] = new stdClass();
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

				$t[$tidx]->posts[$pidx]->last_post = $lastpage;
				$t[$tidx]->posts[$pidx]->last_post_on_page = 1;

				# save last post on page id
				$t[$tidx]->last_post_id			= $r->post_id;

				# adjust this users newpost post marker
				if ($spThisUser->member == true && $newPostId != 0) {
					if ($lastpage == true) {
						# topic can be removed from new post list
						sp_remove_users_newposts($tidx, $spThisUser->ID, true);
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
				if (sp_can_view($r->forum_id, 'forum-title')) $this->topicViewStatus = 'sneak peek';
			}
		}

		return $t;
	}
}

?>
