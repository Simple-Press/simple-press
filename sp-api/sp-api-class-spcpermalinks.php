<?php

/**
 * Core class used for permalinks.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * build_url($forumslug, $topicslug, $pageid, $postid, $postindex, $rss)
 * get_url($link)
 * get_query_url($url)
 * permalink_from_postid($postid)
 * get_page($forumslug, $topicslug, $postid, $postindex)
 * get_query_char()
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcPermalinks {
	/**
	 * Holds a cached list of forum slugs to be used for url generation.
	 *
	 * @var array
	 */
	private $forum_slugs = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * This method creates a forum url based on the argument data.
	 * To use pass forum and topic slugs as well as page number.  If not known, pass a 0.
	 * If a post id is passed (else use zero), the routine will get the correct page number for the post
	 * within the topic.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $forumslug slug of the forum you want the url for
	 * @param string $topicslug slug of the topic you want the url for
	 * @param int    $pageid    page within forum and topic that you want the url for
	 * @param int    $postindex post index with in the topic
	 * @param bool   $rss       true if rss feed url
	 *
	 * @return string    generated url
	 *
	 */
	public function build_url($forumslug, $topicslug, $pageid, $postid = 0, $postindex = 0, $rss = 0) {
		# base url
		$url = trailingslashit(SP()->spPermalinks->get_url());

		# add in forum(s)
		if ($forumslug && $forumslug != 'all') {
			# make sure the slugs have been filled for ajax calls
			if (empty($this->forum_slugs[$forumslug])) {
				# forum doesnt exist, so just use it in permalink
				$url .= $forumslug;
			} else {
				# put the forum slug in the permalink
				$url .= $this->forum_slugs[$forumslug]->permalink_slug;
			}
		}

		# add in topic
		if ($topicslug) $url .= '/'.$topicslug;

		# do we need RSS in url?
		if ($rss) {
			if (!empty($forumslug) || !empty($topicslug)) $url .= '/';
			$url .= 'rss';
		}

		# add the page number
		if ($postid != 0 && $pageid == 0) $pageid = SP()->spPermalinks->get_page($forumslug, $topicslug, SP()->filters->integer($postid), SP()->filters->integer($postindex));
		if ($pageid > 1) $url .= '/page-'.$pageid;

		$url = user_trailingslashit($url);

		# add the post id
		if ($postid) $url .= '#p'.$postid;

		return esc_url(apply_filters('sph_forum_url', $url));
	}

	/**
	 * This method creates a URL based off of Simple Press base url for permalinks.
	 * If the link arg is present, its appended to Simple Press base url helper function.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $link string to append to end of base url
	 *
	 * @return string    generated url
	 */
	public function get_url($link = '') {
		$url = SP()->options->get('sfpermalink');
		if (!empty($link)) $url = trailingslashit($url).$link;
		$url = user_trailingslashit($url);

		return esc_url($url);
	}

	/**
	 * This method builds a forum query url ready for query parameters to be added.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $url forum url
	 *
	 * @return string    generated url
	 */
	public function get_query_url($url) {
		$url = user_trailingslashit($url);
		if (strpos($url, '?') === false) {
			$url .= '?';
		} else {
			$url .= '&amp;';
		}

		return $url;
	}

	/**
	 * This method builds a permalink for a topic from only the post id.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $postid ID of the post
	 *
	 * @return string    generated url
	 */
	public function permalink_from_postid($postid) {
		$url = '';
		if (!empty($postid)) {
			$slugs = SP()->DB->select('SELECT forum_slug, topic_slug, post_index
										FROM '.SPPOSTS.'
										JOIN '.SPFORUMS.' ON '.SPPOSTS.'.forum_id = '.SPFORUMS.'.forum_id
										JOIN '.SPTOPICS.' ON '.SPPOSTS.'.topic_id = '.SPTOPICS.'.topic_id
										WHERE '.SPPOSTS.".post_id=$postid", 'row');

			$url = SP()->spPermalinks->build_url($slugs->forum_slug, $slugs->topic_slug, 0, $postid, $slugs->post_index);
		}

		return $url;
	}

	/**
	 * This method determines the correct page for a topic post will be displayed on based on current settings.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $forumslug slug of the forum you want the url for
	 * @param string $topicslug slug of the topic you want the url for
	 * @param int    $pageid    page within forum and topic that you want the url for
	 * @param int    $postindex post index with in the topic
	 *
	 * @return int        page for the post id
	 */
	function get_page($forumslug, $topicslug, $postid, $postindex) {
		# establish paging count - can sometimes be out of scope so check
		$ppaged = SP()->core->forumData['display']['posts']['perpage'];
		if (empty($ppaged) || $ppaged == 0) $ppaged = 20;

		# establish topic sort order
		$order = 'ASC'; # default
		if (SP()->core->forumData['display']['posts']['sortdesc']) $order = 'DESC';# global override
		# If we do not have the postindex then we have to go and get it
		if ($postindex == 0 || empty($postindex)) {
			$postindex = SP()->DB->table(SPPOSTS, "post_id=$postid", 'post_index');

			# In the remote possibility postindex is 0 or empty then...
			if ($postindex == 0 || empty($postindex)) {
				$forumrecord = SP()->DB->table(SPFORUMS, "forum_slug='$forumslug'", 'row');
				$topicrecord = SP()->DB->table(SPTOPICS, "topic_slug='$topicslug'", 'row');

				sp_build_post_index($topicrecord->topic_id);
				sp_build_forum_index($forumrecord->forum_id);
				$postindex = SP()->DB->table(SPPOSTS, "post_id=$postid", 'post_index');
			}
		}

		# Now we have what we need to do the math
		if ($order == 'ASC') {
			$page = $postindex / $ppaged;
			if (!is_int($page)) $page = intval($page + 1);
		} else {
			if (!isset($topicrecord)) $topicrecord = SP()->DB->table(SPTOPICS, "topic_slug='$topicslug'", 'row');

			$page = $topicrecord->post_count - $postindex;
			$page = $page / $ppaged;
			$page = intval($page + 1);
		}

		return $page;
	}

	/**
	 * This method returns the starting query arg character to be used for adding args to a query.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 *
	 * @return string    user object for requested user
	 */
	public function get_query_char() {
		global $wp_rewrite;
		$char = ($wp_rewrite->using_permalinks()) ? '?' : '&amp;';

		return $char;
	}

	/**
	 * This function updates the forum permalink and flushed with WP rewrite rules so
	 * our rewrite rules get reapplied.
	 *
	 * @since 6.0
	 *
	 * @param bool $autoflush force a flush regardless if permalink changed
	 *
	 * @return string        updated forum permalink
	 */
	public function update_permalink($autoflush = false) {
		global $wp_rewrite;

		$sfperm = '';

		$slug = SP()->options->get('sfslug');
		if ($slug) {
			$sfperm = SP()->options->get('sfpermalink');

			# go for whole row to ensure it is cached
			$pageslug = basename($slug);
			$page     = SP()->DB->table(SPWPPOSTS, "post_name='$pageslug' AND post_status='publish' AND post_type='page'", 'row');
			if ($page) {
				SP()->options->update('sfpage', $page->ID);

				# get scheme for front end
				# get_permalink() returns scheme for existing page so adjust to front
				$perm   = get_permalink($page->ID);
				$scheme = parse_url(get_option('siteurl'), PHP_URL_SCHEME); # get front end scheme
				$perm   = set_url_scheme($perm, $scheme); # update permalink with proper front end scheme
				if (get_option('page_on_front') == $page->ID && get_option('show_on_front') == 'page') {
					$perm = rtrim($perm, '/');
					if ($wp_rewrite->using_permalinks()) {
						$perm .= '/'.$slug;
					} else {
						$perm .= '/?page_id='.$page->ID;
					}
				}
				# only update it if base permalink has been changed
				if ($sfperm != $perm) {
					SP()->options->update('sfpermalink', $perm);
					$sfperm    = $perm;
					$autoflush = true;
				}
			}
		}

		if ($autoflush) flush_rewrite_rules(true);

		return $sfperm;
	}

	/**
	 * This function updates
	 *
	 * @since 6.0
	 *
	 *
	 * @return string        topic url
	 */
	public function get_topic_url($forumslug, $topicslug, $topicname) {
		$out       = '';
		$topicname = SP()->displayFilters->title($topicname);
		if (isset(SP()->rewrites->pageData['searchvalue']) && SP()->rewrites->pageData['searchvalue']) {
			$out .= '<a href="'.SP()->spPermalinks->build_url($forumslug, $topicslug, 1, 0);
			if (strpos(SP()->spPermalinks->get_url(), '?') === false) {
				$out .= '?value';
			} else {
				$out .= '&amp;value';
			}
			$out .= '='.SP()->rewrites->pageData['searchvalue'].'&amp;type='.SP()->rewrites->pageData['searchtype'].'&amp;include='.SP()->rewrites->pageData['searchinclude'].'&amp;scope='.'&amp;search='.SP()->rewrites->pageData['searchpage'].'">'.$topicname."</a>\n";
		} else {
			$out = '<a href="'.SP()->spPermalinks->build_url($forumslug, $topicslug, 1, 0).'">'.SP()->displayFilters->title($topicname)."</a>\n";
		}

		return $out;
	}

	/**
	 * Loads cached object of all forum permalink slugs.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function load() {
		# make sure the forum table exists
		$exist = SP()->DB->tableExists(SPFORUMS);
		if (!$exist) return;

		# grab the forum permalink slugs from the database
		$query             = new stdClass();
		$query->resultType = OBJECT_K;
		$query->type       = 'set';
		$query->table      = SPFORUMS;
		$query->fields     = 'forum_slug, permalink_slug';
		$this->forum_slugs = SP()->DB->select($query);
	}
}