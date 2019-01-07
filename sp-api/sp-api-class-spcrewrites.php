<?php

/**
 * Core class used for WP url rewriting.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcRewrites {
	/**
	 *
	 * @var array    some page data (spVars)
	 *
	 * @since 6.0
	 */
	public $pageData = array();

	/**
	 * This function sets up the rewrite rules for forum pages
	 *
	 * @since 6.0
	 *
	 * @param array $rules current list of WP rewrite rules
	 *
	 * @return array    Updated WP rewrite rules with forum rewrite rules added
	 */
	public function rules($rules) {
		global $wp_rewrite;

		$slug      = SP()->options->get('sfslug');
		$slugmatch = $slug;
		if ($wp_rewrite->using_index_permalinks() && $wp_rewrite->root == 'index.php/') $slugmatch = 'index.php/'.$slugmatch;# handle PATHINFO permalinks

		$slugmatch = apply_filters('sph_rewrite_rules_slug', $slugmatch);

		$sf_rules = array();
		$sf_rules = apply_filters('sph_rewrite_rules_start', $sf_rules, $slugmatch, $slug);

		# admin new posts list
		$sf_rules[$slugmatch.'/newposts/?$'] = 'index.php?pagename='.$slug.'&sf_newposts=all';

		# members list?
		$sf_rules[$slugmatch.'/members/?$']               = 'index.php?pagename='.$slug.'&sf_members=list';
		$sf_rules[$slugmatch.'/members/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_members=list&sf_page=$matches[1]';

		# match profile?
		$sf_rules[$slugmatch.'/profile/?$']              = 'index.php?pagename='.$slug.'&sf_profile=edit';
		$sf_rules[$slugmatch.'/profile/([^/]+)/?$']      = 'index.php?pagename='.$slug.'&sf_profile=show&sf_member=$matches[1]';
		$sf_rules[$slugmatch.'/profile/([^/]+)/edit/?$'] = 'index.php?pagename='.$slug.'&sf_profile=edit&sf_member=$matches[1]';

		# match main rss
		$sf_rules[$slugmatch.'/rss/?$']         = 'index.php?pagename='.$slug.'&sf_feed=all'; # match main rss feed
		$sf_rules[$slugmatch.'/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_feed=all&sf_feedkey=$matches[1]'; # match main rss feed with feedkey

		# match forums/topics with pages and rss
		$sf_rules[$slugmatch.'/(.+?)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_page=$matches[2]'; # match forum with page
		$sf_rules[$slugmatch.'/(.+?)/rss/([^/]+)/?$']   = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_feed=forum&sf_feedkey=$matches[2]'; # match forum rss feed with feedkey
		$sf_rules[$slugmatch.'/(.+?)/rss/?$']           = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_feed=forum'; # match forum rss feed
		$sf_rules[$slugmatch.'/(.+?)/?$']               = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]'; # match forum

		$sf_rules = apply_filters('sph_rewrite_rules_end', $sf_rules, $slugmatch, $slug);
		$rules    = array_merge($sf_rules, $rules);

		return $rules;
	}

	/**
	 * This function adds the forum query vars to the WP query vars
	 *
	 * @since 6.0
	 *
	 * @param array $vars list of WP query vars
	 *
	 * @return array    Updated WP query vars with forum query vars added
	 */
	public function query_vars($vars) {
		# forums and topics
		$vars[] = 'sf_forum';
		$vars[] = 'sf_topic';
		$vars[] = 'sf_page';

		# new posts
		$vars[] = 'sf_newposts';

		# members list
		$vars[] = 'sf_members';

		# profile
		$vars[] = 'sf_profile';
		$vars[] = 'sf_member';

		$vars[] = 'sf_feed';
		$vars[] = 'sf_feedkey';

		$vars = apply_filters('sph_query_vars', $vars);

		return $vars;
	}

	/**
	 * This function populates the forum page variables form the url query vars.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function page_vars() {
		global $wp_rewrite;

		# load query vars
		if (isset(SP()->core->forumData['queryvarsdone']) && SP()->core->forumData['queryvarsdone']) return;
		SP()->core->forumData['queryvarsdone'] = true;

		# initialize with some defaults
		SP()->rewrites->pageData['error']         = false;
		SP()->rewrites->pageData['groupid']       = 0;
		SP()->rewrites->pageData['forumid']       = 0;
		SP()->rewrites->pageData['topicid']       = 0;
		SP()->rewrites->pageData['displaymode']   = '';
		SP()->rewrites->pageData['pageview']      = '';
		SP()->rewrites->pageData['searchpage']    = 0;
		SP()->rewrites->pageData['searchtype']    = 0;
		SP()->rewrites->pageData['searchinclude'] = 0;
		SP()->rewrites->pageData['newsearch']     = 0;

		# user has permalinks
		if ($wp_rewrite->using_permalinks()) {
			# using permalinks so get the values from the query vars
			if (isset($_GET['search']) && empty($_GET['forum'])) die();

			# get the query vars for non forum pages
			SP()->rewrites->pageData['profile']  = SP()->filters->str(get_query_var('sf_profile'));
			SP()->rewrites->pageData['member']   = SP()->filters->str(get_query_var('sf_member'));
			SP()->rewrites->pageData['members']  = SP()->filters->str(get_query_var('sf_members'));
			SP()->rewrites->pageData['box']      = SP()->filters->str(get_query_var('sf_box'));
			SP()->rewrites->pageData['feed']     = SP()->filters->str(get_query_var('sf_feed'));
			SP()->rewrites->pageData['feedkey']  = SP()->filters->str(get_query_var('sf_feedkey'));
			SP()->rewrites->pageData['newposts'] = SP()->filters->str(get_query_var('sf_newposts'));

			# get the page number if in the query vars
			$page = get_query_var('sf_page');
			if ($page != '') SP()->rewrites->pageData['page'] = SP()->filters->integer($page);

			# figure out the forum(s) and topic
			$query_slugs = SP()->filters->str(get_query_var('sf_forum'));
			if (empty($query_slugs)) {
				# no forum, so cant have topic
				SP()->rewrites->pageData['forumslug'] = '';
				SP()->rewrites->pageData['topicslug'] = '';
			} else {
				$pieces = explode('/', $query_slugs);
				$num    = count($pieces);
				if ($num == 1) {
					# just a forum view
					SP()->rewrites->pageData['forumslug'] = $query_slugs;
					SP()->rewrites->pageData['topicslug'] = '';
				} else {
					# could have multiple forums or a topic
					$slug = end($pieces);

					# see if slug end is forum - if not, assume its a topic
					$query         = new stdClass();
					$query->type   = 'var';
					$query->table  = SPFORUMS;
					$query->fields = 'forum_id';
					$query->where  = "forum_slug='$slug'";
					$fid           = SP()->DB->select($query);
					if (!empty($fid)) {
						# last slug is forum
						SP()->rewrites->pageData['forumslug'] = $slug;
						SP()->rewrites->pageData['topicslug'] = '';
					} else {
						# last slug assumed to be topic
						SP()->rewrites->pageData['forumslug'] = $pieces[$num - 2]; # grab the last forum slug
						SP()->rewrites->pageData['topicslug'] = $slug;
					}
				}
			}

			# is this a search
			if (empty(SP()->rewrites->pageData['forumslug']) && isset($_GET['search'])) SP()->rewrites->pageData['forumslug'] = SP()->filters->str($_GET['forum']);

			do_action('sph_get_query_vars');

			$this->support_vars();
		} else {
			# Not using permalinks so we need to parse the query string from the url and do it ourselves
			$stuff = explode('/', urldecode($_SERVER['QUERY_STRING']));

			# deal with non-standard cases first
			if (isset($_GET['search'])) {
				$this->default_search_vars($stuff);
			} else {
				$this->default_support_vars($stuff);
			}

			$this->support_vars();

			if (empty(SP()->rewrites->pageData['groupid'])) SP()->rewrites->pageData['groupid'] = 0;
			if (empty(SP()->rewrites->pageData['forumid'])) SP()->rewrites->pageData['forumid'] = 0;
			if (empty(SP()->rewrites->pageData['topicid'])) SP()->rewrites->pageData['topicid'] = 0;
			if (empty(SP()->rewrites->pageData['profile'])) SP()->rewrites->pageData['profile'] = 0;
			if (empty(SP()->rewrites->pageData['member'])) SP()->rewrites->pageData['member'] = 0;
			if (empty(SP()->rewrites->pageData['members'])) SP()->rewrites->pageData['members'] = 0;
			if (empty(SP()->rewrites->pageData['box'])) SP()->rewrites->pageData['box'] = 0;
			if (empty(SP()->rewrites->pageData['feed'])) SP()->rewrites->pageData['feed'] = 0;
			if (empty(SP()->rewrites->pageData['feedkey'])) SP()->rewrites->pageData['feedkey'] = 0;
			if (empty(SP()->rewrites->pageData['newposts'])) SP()->rewrites->pageData['newposts'] = 0;

			do_action('sph_fill_query_vars');
		}

		# set up the forum page type
		$this->page_type();

		# Now at this point pageData should be set up
		# So from this point on we can direct messages at the user
		sp_track_online();
	}

	/**
	 * This function checks to see if a flushing of WP rewrite rules has been commanded.
	 * Typically, this would occur after upgrades.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function check_flush() {
		$flush = SP()->options->get('sfflushrules');
		if ($flush) {
			flush_rewrite_rules(true);
			SP()->options->update('sfflushrules', false);
		}
	}

	/**
	 * This function gets around the default canonical url behaviour when the forum is set to be the
	 * front page of the site - normally the full url wold be discarded leaving just the home url.
	 *
	 * @since 6.0
	 *
	 * @param string $redirect current front page redirect url
	 *
	 * @return string    Updated WP front page redirect url to handle forum pages
	 */
	public function front_page_redirect($redirect) {
		global $wp_query;

		if ($wp_query->is_page) {
			if (isset($wp_query->queried_object) && 'page' == get_option('show_on_front') && $wp_query->queried_object->ID == get_option('page_on_front')) {
				if (SP()->options->get('sfpage') == get_option('page_on_front')) return false;
			}
		}

		return $redirect;
	}

	/**
	 * This function populates the forum page support variables.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function support_vars() {
		# Populate the rest of pageData
		if (empty(SP()->rewrites->pageData['page'])) SP()->rewrites->pageData['page'] = 1;
		if (!empty(SP()->rewrites->pageData['forumslug']) && SP()->rewrites->pageData['forumslug'] != 'all') {
			$record = SP()->DB->table(SPFORUMS, "forum_slug='".SP()->rewrites->pageData['forumslug']."'", 'row');
			if ($record) {
				SP()->rewrites->pageData['groupid'] = $record->group_id;
				SP()->rewrites->pageData['forumid'] = $record->forum_id;
				if (empty(SP()->rewrites->pageData['groupid'])) SP()->rewrites->pageData['groupid'] = 0;
				if (empty(SP()->rewrites->pageData['forumid'])) SP()->rewrites->pageData['forumid'] = 0;
				SP()->rewrites->pageData['forumname']    = $record->forum_name;
				SP()->rewrites->pageData['forumdesc']    = $record->forum_desc;
				SP()->rewrites->pageData['featureimage'] = $record->feature_image;

				# Is it a subforum?
				if (!empty($record->parent)) {
					$forumparent = $record->parent;
					while ($forumparent > 0) {
						$parent = SP()->DB->table(SPFORUMS, "forum_id=$forumparent", 'row');
						if ($parent) {
							SP()->rewrites->pageData['parentforumid'][]   = $forumparent;
							SP()->rewrites->pageData['parentforumslug'][] = $parent->forum_slug;
							SP()->rewrites->pageData['parentforumname'][] = $parent->forum_name;
							$forumparent                                    = $parent->parent;
						} else {
							$forumparent = true;
						}
					}
				}
				SP()->rewrites->pageData = apply_filters('sph_page_data_forum', SP()->rewrites->pageData, $record);
			} else {
				$header = apply_filters('sph_404', 404);
				status_header($header);
			}
		}

		if (!empty(SP()->rewrites->pageData['topicslug'])) {
			$record = SP()->DB->table(SPTOPICS, "topic_slug='".SP()->rewrites->pageData['topicslug']."'", 'row');
			if ($record) {
				SP()->rewrites->pageData['topicid'] = $record->topic_id;
				if (empty(SP()->rewrites->pageData['topicid'])) SP()->rewrites->pageData['topicid'] = 0;
				if ($record) SP()->rewrites->pageData['topicname'] = $record->topic_name;

				# verify forum slug matches forum slug based on topic and do canonical redirect if doesnt match (moved?)
				$forum = SP()->DB->table(SPFORUMS, "forum_id='".$record->forum_id."'", 'row');
				if ($forum->forum_slug != SP()->rewrites->pageData['forumslug']) {
					$url = SP()->spPermalinks->build_url($forum->forum_slug, SP()->rewrites->pageData['topicslug'], SP()->rewrites->pageData['page'], 0);
					wp_redirect(esc_url($url), 301);
				}
				SP()->rewrites->pageData = apply_filters('sph_page_data_topic', SP()->rewrites->pageData, $record);
			} else {
				$header = apply_filters('sph_404', 404);
				status_header($header);
			}
		}

		# Add Search Vars
		if (isset($_GET['search'])) {
			if ($_GET['search'] != '') SP()->rewrites->pageData['searchpage'] = intval($_GET['search']);
			SP()->rewrites->pageData['searchpage'] = SP()->filters->integer(SP()->rewrites->pageData['searchpage']);

			if (isset($_GET['type']) ? SP()->rewrites->pageData['searchtype'] = intval($_GET['type']) : SP()->rewrites->pageData['searchtype'] = 1) ;
			SP()->rewrites->pageData['searchtype'] = SP()->filters->integer(SP()->rewrites->pageData['searchtype']);
			if (SP()->rewrites->pageData['searchtype'] == 0 || empty(SP()->rewrites->pageData['searchtype'])) SP()->rewrites->pageData['searchtype'] = 1;

			if (isset($_GET['include']) ? SP()->rewrites->pageData['searchinclude'] = intval($_GET['include']) : SP()->rewrites->pageData['searchinclude'] = 1) ;
			SP()->rewrites->pageData['searchinclude'] = SP()->filters->integer(SP()->rewrites->pageData['searchinclude']);
			if (SP()->rewrites->pageData['searchinclude'] == 0 || empty(SP()->rewrites->pageData['searchinclude'])) SP()->rewrites->pageData['searchinclude'] = 1;

			if (isset($_GET['value']) ? SP()->rewrites->pageData['searchvalue'] = SP()->saveFilters->nohtml(urldecode($_GET['value'])) : SP()->rewrites->pageData['searchvalue'] = '') ;

			SP()->rewrites->pageData['searchvalue'] = SP()->filters->table_prefix(SP()->rewrites->pageData['searchvalue']);

			SP()->rewrites->pageData['newsearch'] = (isset($_GET['new'])) ? true : false;

			if (empty(SP()->rewrites->pageData['searchvalue']) || SP()->rewrites->pageData['searchvalue'] == '') {
				SP()->rewrites->pageData['searchpage']    = 0;
				SP()->rewrites->pageData['searchtype']    = 0;
				SP()->rewrites->pageData['searchinclude'] = 0;
				SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid search query'));
				wp_redirect(SP()->spPermalinks->get_url());
			}
		} else {
			SP()->rewrites->pageData['searchpage'] = 0;
		}
		SP()->rewrites->pageData['searchresults'] = 0;

		SP()->rewrites->pageData = apply_filters('sph_support_vars', SP()->rewrites->pageData);
	}

	/**
	 * This function populates the forum page support variables when a search has been done in default permalinks.
	 *
	 * @since 6.0
	 *
	 * @param array $stuff query vars from the page url
	 *
	 * @return void
	 */
	private function default_search_vars($stuff) {
		if (isset($_GET['forum'])) {
			# means searching all
			SP()->rewrites->pageData['forumslug'] = SP()->filters->str($_GET['forum']);
		} else {
			# searching single forum
			if (!empty($stuff[1])) SP()->rewrites->pageData['forumslug'] = $stuff[1];

			# (2) topic
			if (!empty($stuff[2])) {
				$parts                                  = explode('&', $stuff[2]);
				SP()->rewrites->pageData['topicslug'] = $parts[0];
			}
		}
	}

	/**
	 * This function builds the forum page support variables when using default permalinks.
	 *
	 * @since 6.0
	 *
	 * @param array $stuff queary vars from the page url
	 *
	 * @return void
	 */
	private function default_support_vars($stuff) {
		global $current_user;

		# special forum page check
		if (!empty($stuff[1])) {
			# need to parse out query vars
			$substuff = explode('&', $stuff[1]);

			# lets figure out what kind of page we have
			if ($stuff[1] == 'profile' || (!empty($substuff) && $substuff[0] == 'profile')) {
				# its a profile view
				if (empty($stuff[2])) {
					SP()->rewrites->pageData['member']  = $current_user->ID;
					SP()->rewrites->pageData['profile'] = 'edit';
				} else if (empty($stuff[3])) {
					if (strpos($stuff[2], '&') === false) {
						SP()->rewrites->pageData['member']  = (int) $stuff[2];
						SP()->rewrites->pageData['profile'] = 'show';
					} else {
						SP()->rewrites->pageData['profile'] = 'edit';
					}
				} else {
					SP()->rewrites->pageData['member']  = (int) $stuff[2];
					SP()->rewrites->pageData['profile'] = 'edit';
				}
			} else if ($stuff[1] == 'members' || (!empty($substuff) && $substuff[0] == 'members')) {
				# its a members view
				SP()->rewrites->pageData['members'] = 'list';
				if (isset($stuff[2]) && preg_match('/page-(\d+)/', $stuff[2], $matches)) SP()->rewrites->pageData['page'] = intval($matches[1]);
			} else if ($stuff[1] == 'newposts' || (!empty($substuff) && $substuff[0] == 'newposts')) {
				SP()->rewrites->pageData['newposts'] = 'all';
			} else if ($stuff[1] == 'rss') {
				# its a main rss feed or maybe a group feed
				if (!empty($substuff[0])) {
					# must be group feed
					SP()->rewrites->pageData['feed'] = 'group';
				} else {
					# must be main feed
					SP()->rewrites->pageData['feed'] = 'all';
				}

				# do we need to handle feedkey for main or group rss?
				if (!empty($stuff[2])) {
					# strip off the group id if its there
					$pieces                               = explode('&', $stuff[2]);
					SP()->rewrites->pageData['feedkey'] = $pieces[0];
				} else {
					SP()->rewrites->pageData['feedkey'] = '';
				}
			} else {
				# its a forum or topic view
				SP()->rewrites->pageData['plugin-vars'] = false;
				do_action('sph_get_def_query_vars', $stuff);

				if (!SP()->rewrites->pageData['plugin-vars']) {
					# forum page
					$num = count($stuff);
					if ($num == 2) {
						# just a top level forum view
						SP()->rewrites->pageData['forumslug'] = $stuff[1];
						SP()->rewrites->pageData['topicslug'] = '';
					} else {
						# either a subforum or topic view
						$feed  = false;
						$check = $num - 1;
						if ($stuff[$num - 1] == 'rss') {
							# handle rss for forum/topic
							$feed                                 = true;
							$check                                = $num - 2;
							SP()->rewrites->pageData['feedkey'] = '';
						} else if ($stuff[$num - 2] == 'rss') {
							# handle rss feedkey for forum/topic
							$feed                                 = true;
							$check                                = $num - 3;
							SP()->rewrites->pageData['feedkey'] = $stuff[$num - 1];
						} else if (preg_match('/page-(\d+)/', $stuff[$num - 1], $matches)) {
							# handle pages for forum/topic
							SP()->rewrites->pageData['page'] = intval($matches[1]);
							$check                             = $num - 2;
						}

						# get the very last forum or topic slug so we can find out what is is
						$slug = $stuff[$check];

						$query         = new stdClass();
						$query->type   = 'var';
						$query->table  = SPFORUMS;
						$query->fields = 'forum_id';
						$query->where  = "forum_slug='$slug'";
						$fid           = SP()->DB->select($query);
						if (!empty($fid)) {
							# last slug is forum
							SP()->rewrites->pageData['forumslug'] = $slug;
							SP()->rewrites->pageData['topicslug'] = '';
							if ($feed) SP()->rewrites->pageData['feed'] = 'forum';
						} else {
							# last slug assumed to be topic
							SP()->rewrites->pageData['forumslug'] = $stuff[$check - 1]; # grab the last forum slug
							SP()->rewrites->pageData['topicslug'] = $slug;
							if ($feed) SP()->rewrites->pageData['feed'] = 'topic';
						}
					}
				}
			}
		}
	}

	/**
	 * This function processes the page url and query strings to determin the type of forum page to be displayed.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private function page_type() {
		if (SP()->core->status != 'ok') return;

		if (isset(SP()->core->forumData['pagetypedone']) && SP()->core->forumData['pagetypedone'] == true) return;
		SP()->core->forumData['pagetypedone'] = true;

		# If user has made no posts yet optionaly load the profile form
		$pageview  = '';
		$goProfile = false;
		if (SP()->user->thisUser->member && SP()->user->thisUser->posts == -1) {
			SP()->memberData->update(SP()->user->thisUser->ID, 'posts', 0); # reset posts to 0 on first visit
			# do new users need to visit profile first?
			$sfprofile = SP()->options->get('sfprofile');
			$goProfile = $sfprofile['firstvisit'];
		}

		# do we need to redirec to profile for pw change or first visit?
		if (SP()->user->thisUser->member && ($goProfile || (isset(SP()->user->thisUser->sp_change_pw) && SP()->user->thisUser->sp_change_pw))) {
			SP()->rewrites->pageData['member']    = (int) SP()->user->thisUser->ID;
			$pageview                               = 'profileedit';
			SP()->rewrites->pageData['forumslug'] = '';
			SP()->rewrites->pageData['topicslug'] = '';
		}

		if ($pageview == '') {
			if (!empty(SP()->rewrites->pageData['feed'])) {
				$pageview = 'feed';
			} else if (!empty(SP()->rewrites->pageData['forumslug'])) {
				$pageview = 'forum';
			} else if (!empty(SP()->rewrites->pageData['profile'])) {
				if (SP()->rewrites->pageData['profile'] == 'edit') $pageview = 'profileedit';
				if (SP()->rewrites->pageData['profile'] == 'show') $pageview = 'profileshow';
			} else if (!empty(SP()->rewrites->pageData['newposts'])) {
				$pageview = 'newposts';
			} else if (!empty(SP()->rewrites->pageData['members'])) {
				$pageview = 'members';
			} else {
				$pageview = 'group';
				# and if a single group id is passed load ot ointo pageData
				if (isset($_GET['group'])) SP()->rewrites->pageData['singlegroupid'] = SP()->filters->integer($_GET['group']);

				# Check if single forum only is on
				if (isset(SP()->core->forumData['display']['forums']['singleforum']) && SP()->core->forumData['display']['forums']['singleforum']) {
					$fid = SP()->auths->single_forum_user();
					if ($fid) {
						$cforum                                 = SP()->DB->table(SPFORUMS, "forum_id=$fid", 'row');
						SP()->rewrites->pageData['forumid']   = $fid;
						SP()->rewrites->pageData['forumslug'] = $cforum->forum_slug;
						SP()->rewrites->pageData['forumname'] = $cforum->forum_name;
						$pageview                               = 'forum';
					}
				}
			}

			if (!empty(SP()->rewrites->pageData['topicslug'])) $pageview = 'topic';
			if (isset($_GET['search']) && !empty(SP()->rewrites->pageData['searchvalue'])) $pageview = 'search';
		}

		# profile via ssl if doing ssl logins
		if ($pageview == 'profileedit' && force_ssl_admin() && !is_ssl()) {
			if (SP()->profile->is_tab_active('profile') && SP()->profile->is_menu_active('account-settings')) {
				if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
					wp_redirect(preg_replace('|^http://|', 'https://', htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8')));
					exit();
				} else {
					wp_redirect('https://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8').htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'));
					exit();
				}
			}
		}

		SP()->rewrites->pageData['pageview'] = apply_filters('sph_pageview', $pageview);
	}
}