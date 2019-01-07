<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2017-06-04 13:29:51 -0500 (Sun, 04 Jun 2017) $
$Rev: 15405 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	FORUM PAGE
#	This file loads for forum page loads only
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_front_page_redirect()
#
# gets around the default canonical url behaviour when the
# forum is set to be the front page of the site - normally the
# ful url wold be discarded leaving just the home url.
# ------------------------------------------------------------------
function sp_front_page_redirect($redirect) {
	global $wp_query;

	if ($wp_query->is_page) {
		if (isset($wp_query->queried_object) && 'page' == get_option('show_on_front') && $wp_query->queried_object->ID == get_option('page_on_front')) {
			if (sp_get_option('sfpage') == get_option('page_on_front')) return false;
		}
	}
	return $redirect;
}

# ------------------------------------------------------------------
# sp_populate_query_vars()
#
# Populate the forum query variables from the URL
# ------------------------------------------------------------------
function sp_populate_query_vars() {
	global $spVars, $spGlobals, $wp_rewrite, $spDevice;

	# load query vars
	if (isset($spGlobals['queryvarsdone']) && $spGlobals['queryvarsdone'] == true) return;
	$spGlobals['queryvarsdone'] = true;

	# initialize with some defaults
	$spVars['error'] = false;
	$spVars['groupid'] = 0;
	$spVars['forumid'] = 0;
	$spVars['topicid'] = 0;
	$spVars['displaymode'] = '';
	$spVars['pageview'] = '';
	$spVars['searchpage'] = 0;
	$spVars['searchtype'] = 0;
	$spVars['searchinclude'] = 0;
	$spVars['newsearch'] = 0;

	# user has permalinks
	if ($wp_rewrite->using_permalinks()) {
		# post V3 permalinks
		# using permalinks so get the values from the query vars

		if(isset($_GET['search']) && empty($_GET['forum'])) die();

		$spVars['forumslug'] = sp_esc_str(get_query_var('sf_forum'));
		if (empty($spVars['forumslug']) && isset($_GET['search'])) $spVars['forumslug'] = sp_esc_str($_GET['forum']);
		$spVars['topicslug'] = sp_esc_str(get_query_var('sf_topic'));
		$spVars['profile'] = sp_esc_str(get_query_var('sf_profile'));
		$spVars['member'] = sp_esc_str(get_query_var('sf_member'));
		$spVars['members'] = sp_esc_str(get_query_var('sf_members'));
		$spVars['box'] = sp_esc_str(get_query_var('sf_box'));
		$spVars['feed'] = sp_esc_str(get_query_var('sf_feed'));
		$spVars['feedkey'] = sp_esc_str(get_query_var('sf_feedkey'));
		$spVars['newposts'] = sp_esc_str(get_query_var('sf_newposts'));
		if (get_query_var('sf_page') != '') $spVars['page'] = sp_esc_int(get_query_var('sf_page'));

		do_action('sph_get_query_vars');

		sp_populate_support_vars();
	} else {
		# Not using permalinks so we need to parse the query string from the url and do it ourselves
		$stuff = array();
		$stuff = explode('/', urldecode($_SERVER['QUERY_STRING']));

		# deal with non-standard cases first
		if (isset($_GET['search'])) {
			sp_build_search_vars($stuff);
		} else {
			sp_build_standard_vars($stuff);
		}

		sp_populate_support_vars();

		if (empty($spVars['groupid'])) $spVars['groupid'] = 0;
		if (empty($spVars['forumid'])) $spVars['forumid'] = 0;
		if (empty($spVars['topicid'])) $spVars['topicid'] = 0;
		if (empty($spVars['profile'])) $spVars['profile'] = 0;
		if (empty($spVars['member'])) $spVars['member'] = 0;
		if (empty($spVars['members'])) $spVars['members'] = 0;
		if (empty($spVars['box'])) $spVars['box'] = 0;
		if (empty($spVars['feed'])) $spVars['feed'] = 0;
		if (empty($spVars['feedkey'])) $spVars['feedkey'] = 0;
		if (empty($spVars['newposts'])) $spVars['newposts'] = 0;

		do_action('sph_fill_query_vars');
	}
	sp_setup_page_type();

	# Now at this point spVars should be set up
	# So from this point on we can direct messages at the user
	sp_track_online();
}

# ------------------------------------------------------------------
# sp_populate_support_vars()
#
# Query Variables support routine
# ------------------------------------------------------------------
function sp_populate_support_vars() {
	global $spVars;

	# Populate the rest of spVars
	if (empty($spVars['page'])) $spVars['page'] = 1;
	if (!empty($spVars['forumslug']) && $spVars['forumslug'] != 'all') {
		$record = spdb_table(SFFORUMS, "forum_slug='".$spVars['forumslug']."'", 'row');
		if ($record) {
			$spVars['groupid'] = $record->group_id;
			$spVars['forumid'] = $record->forum_id;
			if (empty($spVars['groupid'])) $spVars['groupid'] = 0;
			if (empty($spVars['forumid'])) $spVars['forumid'] = 0;
			$spVars['forumname'] = $record->forum_name;
			$spVars['forumdesc'] = $record->forum_desc;
			$spVars['featureimage'] = $record->feature_image;

			# Is it a subforum?
			if (!empty($record->parent)) {
				$forumparent = $record->parent;
				while ($forumparent > 0) {
					$parent = spdb_table(SFFORUMS, "forum_id=$forumparent", 'row');
					if ($parent) {
						$spVars['parentforumid'][] = $forumparent;
						$spVars['parentforumslug'][] = $parent->forum_slug;
						$spVars['parentforumname'][] = $parent->forum_name;
						$forumparent = $parent->parent;
					} else {
						$forumparent = true;
					}
				}
			}
			$spVars = apply_filters('sph_spvars_forum', $spVars, $record);
		} else {
			$header = apply_filters('sph_404', 404);
			status_header($header);
		}
	}

	if (!empty($spVars['topicslug'])) {
		$record = spdb_table(SFTOPICS, "topic_slug='".$spVars['topicslug']."'", 'row');
		if ($record) {
			$spVars['topicid'] = $record->topic_id;
			if (empty($spVars['topicid'])) $spVars['topicid'] = 0;
			if ($record) $spVars['topicname'] = $record->topic_name;

			# verify forum slug matches forum slug based on topic and do canonical redirect if doesnt match (moved?)
			$forum = spdb_table(SFFORUMS, "forum_id='".$record->forum_id."'", 'row');
			if ($forum->forum_slug != $spVars['forumslug']) {
				$url = sp_build_url($forum->forum_slug, $spVars['topicslug'], $spVars['page'], 0);
				wp_redirect(esc_url($url), 301);
			}
			$spVars = apply_filters('sph_spvars_topic', $spVars, $record);
		} else {
			$header = apply_filters('sph_404', 404);
			status_header($header);
		}
	}

	# Add Search Vars
	if (isset($_GET['search'])) {
		if ($_GET['search'] != '') $spVars['searchpage'] = intval($_GET['search']);
		$spVars['searchpage'] = sp_esc_int($spVars['searchpage']);

		if (isset($_GET['type']) ? $spVars['searchtype'] = intval($_GET['type']) : $spVars['searchtype'] = 1);
		$spVars['searchtype'] = sp_esc_int($spVars['searchtype']);
		if ($spVars['searchtype'] == 0 || empty($spVars['searchtype'])) $spVars['searchtype'] = 1;

		if (isset($_GET['include']) ? $spVars['searchinclude'] = intval($_GET['include']) : $spVars['searchinclude'] = 1);
		$spVars['searchinclude'] = sp_esc_int($spVars['searchinclude']);
		if ($spVars['searchinclude'] == 0 || empty($spVars['searchinclude'])) $spVars['searchinclude'] = 1;

		if (isset($_GET['value']) ? $spVars['searchvalue'] = sp_filter_save_nohtml(urldecode($_GET['value'])) : $spVars['searchvalue'] = '');

		$spVars['searchvalue'] = sp_filter_table_prefix($spVars['searchvalue']);

		$spVars['newsearch'] = (isset($_GET['new'])) ? true : false;

		if (empty($spVars['searchvalue']) || $spVars['searchvalue'] == '') {
			$spVars['searchpage'] = 0;
			$spVars['searchtype'] = 0;
			$spVars['searchinclude'] = 0;
			sp_notify(SPFAILURE, sp_text('Invalid search query'));
			wp_redirect(sp_url());
		}
	} else {
		$spVars['searchpage'] = 0;
	}
	$spVars['searchresults'] = 0;

	$spVars = apply_filters('sph_support_vars', $spVars);
}

# ------------------------------------------------------------------
# sp_build_search_vars()
#
# Query Variables support routine
# ------------------------------------------------------------------
function sp_build_search_vars($stuff) {
	global $spVars;

	if (isset($_GET['forum'])) {
		# means searching all
		$spVars['forumslug'] = sp_esc_str($_GET['forum']);
	} else {
		# searching single forum
		if (!empty($stuff[1])) $spVars['forumslug'] = $stuff[1];

		# (2) topic
		if (!empty($stuff[2])) {
			$parts = explode('&', $stuff[2]);
			$spVars['topicslug'] = $parts[0];
		}
	}
}

# ------------------------------------------------------------------
# sp_build_standard_vars()
#
# Query Variables support routine
# ------------------------------------------------------------------
function sp_build_standard_vars($stuff) {
	global $spVars, $current_user;

	# special forum page check
	if (!empty($stuff[1])) {
		# need to parse out query vars
		$substuff = explode('&', $stuff[1]);

		if ($stuff[1] == 'profile' || (!empty($substuff) && $substuff[0] == 'profile')) {
			if (empty($stuff[2])) {
				$spVars['member'] = $current_user->ID;
				$spVars['profile'] = 'edit';
			} else if (empty ($stuff[3])) {
				if (strpos($stuff[2], '&') === false) {
					$spVars['member'] = (int) $stuff[2];
					$spVars['profile'] = 'show';
				} else {
					$spVars['profile'] = 'edit';
				}
			} else {
				$spVars['member'] = (int) $stuff[2];
				$spVars['profile'] = 'edit';
			}
		} else if ($stuff[1] == 'members' || (!empty($substuff) && $substuff[0] == 'members')) {
			$spVars['members'] = 'list';
			if (isset($stuff[2]) && preg_match('/page-(\d+)/', $stuff[2], $matches)) $spVars['page'] = intval($matches[1]);
		} else if ($stuff[1] == 'newposts' || (!empty($substuff) && $substuff[0] == 'newposts')) {
			$spVars['newposts'] = 'all';
		} else if ($stuff[1] == 'rss') {
			$spVars['feed'] = 'all';
			$spVars['feedkey'] = (isset($stuff[2])) ? $stuff[2] : '';
		} else {
			$spVars['plugin-vars'] = false;
			do_action('sph_get_def_query_vars', $stuff);

			if (!$spVars['plugin-vars']) {
				# forum page
				$spVars['forumslug'] = $substuff[0];

				# topic, page or rss check
				if (!empty($stuff[2])) {
					$matches = array();
					if ($stuff[2] == 'rss') {
						# forum rss feed
						$spVars['feed'] = 'forum';
						$spVars['feedkey'] = $stuff[3];
					} elseif (preg_match('/page-(\d+)/', $stuff[2], $matches)) {
						# forum with page
						$spVars['page'] = intval($matches[1]);
					} else {
						# topic page
						$substuff = explode('&', $stuff[2]);
						$spVars['topicslug'] = $substuff[0];

						# page or rss check
						if (isset($stuff[3]) && $stuff[3] == 'rss') {
							# topic rss feed
							$spVars['feed'] = 'topic';
							$spVars['feedkey'] = $stuff[4];
						} elseif (isset($stuff[3]) && preg_match('/page-(\d+)/', $stuff[3], $matches)) {
							# topic with page
							$spVars['page'] = intval($matches[1]);
						}
					}
				}
			}
		}
	}
}

function sp_setup_page_type() {
	global $spVars, $spGlobals, $spThisUser, $spBootCache, $spStatus;

	if ($spStatus != 'ok') return;

	if (isset($spGlobals['pagetypedone']) && $spGlobals['pagetypedone'] == true) return;
	$spGlobals['pagetypedone'] = true;

	# If user has made no posts yet optionaly load the profile form
	$pageview = '';
	$goProfile = false;
	if ($spThisUser->member && $spThisUser->posts == -1) {
		sp_update_member_item($spThisUser->ID, 'posts', 0); # reset posts to 0 on first visit

		# do new users need to visit profile first?
		$sfprofile = sp_get_option('sfprofile');
		$goProfile = $sfprofile['firstvisit'];
	}

	# do we need to redirec to profile for pw change or first visit?
	if ($spThisUser->member && ($goProfile || (isset($spThisUser->sp_change_pw) && $spThisUser->sp_change_pw))) {
		$spVars['member'] = (int) $spThisUser->ID;
		$pageview = 'profileedit';
		$spVars['forumslug'] = '';
		$spVars['topicslug'] = '';
	}

	if ($pageview == '') {
		if (!empty($spVars['feed'])) {
			$pageview = 'feed';
		} else if (!empty($spVars['forumslug'])) {
			$pageview = 'forum';
		} else if (!empty($spVars['profile'])) {
			if ($spVars['profile'] == 'edit') $pageview = 'profileedit';
			if ($spVars['profile'] == 'show') $pageview = 'profileshow';
		} else if (!empty($spVars['newposts'])) {
			$pageview = 'newposts';
		} else if (!empty($spVars['members'])) {
			$pageview = 'members';
		} else {
			$pageview = 'group';
			# and if a single group id is passed load ot ointo spVars
			if (isset($_GET['group'])) $spVars['singlegroupid'] = sp_esc_int($_GET['group']);

			# Check if single forum only is on
			if (isset($spGlobals['display']['forums']['singleforum']) && $spGlobals['display']['forums']['singleforum']) {
				$fid = sp_single_forum_user();
				if ($fid) {
					$cforum = spdb_table(SFFORUMS, "forum_id=$fid", 'row');
					$spVars['forumid'] = $fid;
					$spVars['forumslug'] = $cforum->forum_slug;
					$spVars['forumname'] = $cforum->forum_name;
					$spBootCache = '';
					$pageview = 'forum';
				}
			}
		}

		if (!empty($spVars['topicslug'])) $pageview = 'topic';
		if (isset($_GET['search']) && !empty($spVars['searchvalue'])) $pageview = 'search';
	}

	# profile via ssl if doing ssl logins
	if ($pageview == 'profileedit' && force_ssl_admin() && !is_ssl()) {
		if (sp_profile_tab_active('profile') && sp_profile_menu_active('account-settings')) {
			if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
				wp_redirect(preg_replace('|^http://|', 'https://', htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8')));
				exit();
			} else {
				wp_redirect('https://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8').htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'));
				exit();
			}
		}
	}

	$spVars['pageview'] = apply_filters('sph_pageview', $pageview);
}

function sp_canonical_url() {
	global $spVars;

	if ($spVars['pageview'] == 'profileshow' || $spVars['pageview'] == 'profileedit') {
		$url = sp_url('profile');
	} else if ($spVars['pageview'] == 'list') {
		$page = '';
		if ($spVars['page'] > 0) $page = '/page-'.$spVars['page'];
		$url = sp_url('members'.$page);
	} else {
		if (!empty($spVars['topicslug'])) {
			$url = sp_build_url($spVars['forumslug'], $spVars['topicslug'], $spVars['page'], 0);
		} else if (!empty($spVars['forumslug'])) {
			$url = sp_build_url($spVars['forumslug'], '', $spVars['page'], 0);
		} else {
			$url = sp_url();
		}
	}
	return apply_filters('sph_canonical_url', $url);
}

#
# get cononical url
# since 5.7
# ------------------------------------------------------------------
function sp_get_canonical_url($url, $post) {
	global $spIsForum;

	if ($spIsForum) $url = sp_canonical_url();
    return $url;
}

#
# Create canonical URL for AIOSEO
# ------------------------------------------------------------------
function sp_aioseo_canonical_url($url) {
	global $spVars, $spGlobals, $spIsForum, $wp_query;

	if ($spIsForum) {
		$url = sp_canonical_url();
	} else {
		# Do we need to change this from an SP perspective
		$wpPost = $wp_query->get_queried_object();
		$url = apply_filter('sph_aioseo_canonical_url', $url, $wpPost);
	}
	$spGlobals['canonicalurl'] = true;

	return $url;
}

#
# Create meta description for AIOSEO
# ------------------------------------------------------------------
function sp_aioseo_description($aioseo_descr) {
	global $spGlobals, $spIsForum;

	if ($spIsForum) {
		$spGlobals['metadescription'] = true;

		$description = sp_get_metadescription();
		if ($description != '') $aioseo_descr = $description;
	}
	return $aioseo_descr;
}

#
# Create meta keywords for AIOSEO
# ------------------------------------------------------------------
function sp_aioseo_keywords($aioseo_keywords) {
	global $spGlobals, $spIsForum;

	if ($spIsForum) {
		$spGlobals['metakeywords'] = true;
		$keywords = sp_get_metakeywords();
		if ($keywords != '') $aioseo_keywords = $keywords;
	}
	return $aioseo_keywords;
}

#
# Create homepage stuff for AISEO
# ------------------------------------------------------------------
function sp_aioseo_homepage($title) {
	global $spIsForum;

	if ($spIsForum) {
		$sfseo = array();
		$sfseo = sp_get_option('sfseo');
		$title = sp_setup_title($title, ' '.$sfseo['sfseo_sep'].' ');
	}
	return $title;
}

# handle wordprss seo (yoast) )stuff
function sp_wp_seo_hooks($url) {
	if (!defined('SP_USE_WPSEO_HEAD') || 'SP_USE_WPSEO_HEAD' != true) {
        if (defined('WPSEO_VERSION')) {
            $instance = WPSEO_Frontend::get_instance();
            remove_action('wpseo_head', array($instance, 'canonical'), 20);
            remove_action('wpseo_head', array($instance, 'metadesc'), 6);
            remove_action('wpseo_head', array($instance, 'metakeywords'), 11);
            remove_action('wpseo_head', array($instance, 'publisher'), 22);
            remove_action('wpseo_head', array($GLOBALS['wpseo_og'], 'opengraph'), 30);
            remove_action('wpseo_head', array('WPSEO_Twitter', 'get_instance'), 40);
        }
    }
}

# handle our meta stuff

function sp_get_metadescription() {
	global $spVars;

	$description = '';

	# do we need a meta description
	$sfmetatags = sp_get_option('sfmetatags');
	switch ($sfmetatags['sfdescruse']) {
		case 1:	 # no meta description
			break;

		case 2:	 # use custom meta description on all forum pages
			$description = sp_filter_title_display($sfmetatags['sfdescr']);
			break;

		case 3:	 # use custom meta description on group view and forum description on forum/topic pages
			if (($spVars['pageview'] == 'forum' || $spVars['pageview'] == 'topic') && !isset($_GET['search'])) {
				$forum = spdb_table(SFFORUMS, "forum_slug='".$spVars['forumslug']."'", 'row');
				$description = sp_filter_title_display($forum->forum_desc);
			} else {
				$description = sp_filter_title_display($sfmetatags['sfdescr']);
			}
			break;

		case 4:	 # use custom meta description on group view, forum description on forum pages and topic title on topic pages
		case 5:	 # use custom meta description on group view, forum description on forum pages and first post excerpt on topic pages
			if ($spVars['pageview'] == 'forum' && !isset($_GET['search'])) {
				$forum = spdb_table(SFFORUMS, "forum_slug='".$spVars['forumslug']."'", 'row');
				if ($forum) $description = sp_filter_title_display($forum->forum_desc);
			} else if ($spVars['pageview'] == 'topic' && !isset($_GET['search'])) {
				if ($sfmetatags['sfdescruse'] == 4) {
					$topic = spdb_table(SFTOPICS, "topic_slug='".$spVars['topicslug']."'", 'row');
					if ($topic) $description = sp_filter_title_display($topic->topic_name);
				} else {
					$content = spdb_table(SFPOSTS, "topic_id={$spVars['topicid']}", 'post_content', 'post_id ASC', 1);
					$description = wp_html_excerpt($content, 120);
				}
			} else {  # must be group or other
				$description = sp_filter_title_display($sfmetatags['sfdescr']);
			}
			break;
	}
	return apply_filters('sph_meta_description', $description);
}

function sp_get_metakeywords() {
	global $spVars;

	$keywords = '';
	$sfmetatags = sp_get_option('sfmetatags');
	$sfmetatags['sfkeywords'] = (isset($sfmetatags['sfkeywords'])) ? $sfmetatags['sfkeywords'] : '';

	if (!empty($sfmetatags['sfusekeywords'])) {
		if ($sfmetatags['sfusekeywords'] == 3) {
			if (($spVars['pageview'] == 'forum' || $spVars['pageview'] == 'topic')) {
				$keywords = sp_filter_title_display(spdb_table(SFFORUMS, 'forum_id='.$spVars['forumid'], 'keywords'));
			} else {
				$keywords = stripslashes($sfmetatags['sfkeywords']);
			}
		} else if ($sfmetatags['sfusekeywords'] == 2) {
			$keywords = stripslashes($sfmetatags['sfkeywords']);
		}
	}

	return apply_filters('sph_meta_keywords', $keywords);
}

# ------------------------------------------------------------------
# sp_setup_browser_title()
#
# This function will be used by themes that DO NOT support wp 4.4+ title generation
#
# Filter call
# Sets up the browser page title
#	$title		page title
# ------------------------------------------------------------------
function sp_setup_browser_title($title, $sep='|', $seplocation='') {
	global $wp_query;

	$sp_sep = '';
	$post = $wp_query->get_queried_object();
	if (isset($post->ID) && $post->ID == sp_get_option('sfpage')) {
		$sp_sep = ' '.$sep.' ';
		$title = preg_replace(($seplocation == 'right') ? '/'.preg_quote($sp_sep).'$/' : '/^'.preg_quote($sp_sep).'/', '', $title);
		$sfseo = sp_get_option('sfseo');
		$title = sp_setup_title($title, ' '.$sfseo['sfseo_sep'].' ');
	}
	$title = apply_filters('sph_browser_title', $title);

	return $title;
}

# ------------------------------------------------------------------
# sp_browser_title()
#
# This function will be used by themes that DO support wp 4.4+ title generation
#
# Filter call
# Sets up the browser page title
#	$title		page title
#
# since 5.6.1
# ------------------------------------------------------------------
function sp_browser_title($title) {
	global $wp_query;

	$post = $wp_query->get_queried_object();
	if (isset($post->ID) && $post->ID == sp_get_option('sfpage')) {
		$sfseo = sp_get_option('sfseo');
		$title = sp_setup_title($title, ' '.$sfseo['sfseo_sep'].' ');
	}

	$title = apply_filters('sph_browser_title', $title);
	return $title;
}


# ------------------------------------------------------------------
# sp_title_hook()
#
# called by start of the wp loop action to output hook data before the page title
# ------------------------------------------------------------------
function sp_title_hook() {
	$out = '';
	$out = apply_filters('sph_before_page_title', $out);
}

# ------------------------------------------------------------------
# sp_setup_page_title()
#
# Filter Call
# Sets up the page title option
#	$title: Page title
# ------------------------------------------------------------------
function sp_setup_page_title($title, $id) {
	global $spGlobals;
	if (trim($title) == trim(SFPAGETITLE)) {
        # dont modify nav menus
		if ($id != sp_get_option('sfpage')) return $title;

		# if a 'get_the_title' call then change if required
		if (!empty($spGlobals['display']['pagetitle']['banner']) || ($spGlobals['display']['pagetitle']['notitle'])) return '';

		$seo = array();
		$seo = sp_get_option('sfseo');
		$title = sp_setup_title($title, ' '.$seo['sfseo_sep'].' ');
	}
	return $title;
}

# ------------------------------------------------------------------
# sp_title_hook_remove()
#
# Filter Call
# Removes our title filter while WP is processing nav menus
# NOTE this filter must match the_title filter usage in sp-load-forum.php
# ------------------------------------------------------------------
function sp_title_hook_remove() {
   	remove_filter('the_title',    'sp_setup_page_title', 9999, 2);
    return null;
}

# ------------------------------------------------------------------
# sp_title_hook_add()
#
# Filter Call
# WP is about done processing nav menus so add our title filter back in
# NOTE this filter must match the_title filter usage in sp-load-forum.php
#	$nav_menu: current nav menu
# ------------------------------------------------------------------
function sp_title_hook_add($nav_menu) {
   	add_filter('the_title',    'sp_setup_page_title', 9999, 2);
    return $nav_menu;
}

# ------------------------------------------------------------------
# sp_setup_title()
#
# Support Routine
# Sets up the page title option
# ------------------------------------------------------------------
function sp_setup_title($title, $sep) {
	global $spVars;

	$pTitle = $title;
	$sfseo = sp_get_option('sfseo');

	if ($sfseo['sfseo_overwrite']) $title = '';

	if ($sfseo['sfseo_blogname']) $title = get_bloginfo('name').$sep.$title;

	if ($sfseo['sfseo_pagename']) $title = single_post_title('', false).$sep.$title;

	$page = (!empty($spVars['page']) && $spVars['page'] > 1) ? $sep.sp_text('Page').' '.$spVars['page'] : '';
	$forumslug = (!empty($spVars['forumslug'])) ? $spVars['forumslug'] : '';
	$topicslug = (!empty($spVars['topicslug'])) ? $spVars['topicslug'] : '';

	if (!empty($forumslug) && !empty($spVars['forumname']) && $forumslug != 'all' && $sfseo['sfseo_forum'] && (!$sfseo['sfseo_noforum'] || $spVars['pageview'] != 'topic')) {
		if (!empty($topicslug) && $sfseo['sfseo_topic']) {
			$title = $spVars['forumname'].$sep.$title;
		} else {
			$title = $spVars['forumname'].$page.$sep.$title;
		}
	}

	if (!empty($topicslug) && !empty($spVars['topicname']) && $sfseo['sfseo_topic']) $title = $spVars['topicname'].$page.$sep.$title;

	if ($sfseo['sfseo_page']) {
		$profile = (!empty($spVars['profile'])) ? $spVars['profile'] : '';
		if (!empty($profile) && $profile == 'edit') $title = sp_text('Edit Member Profile').$sep.$title;
		if (!empty($profile) && $profile == 'show') $title = sp_text('Member Profile').$sep.$title;

		$list = (!empty($spVars['members'])) ? $list = urlencode($spVars['members']) : '';
		if (!empty($list) && $list == 'list') $title = sp_text('Member List').$sep.$title;
	}

	if (!empty($spVars['searchpage']) && $spVars['searchpage'] > 0) $title = sp_text('Search').$sep.$title;

	# no separators at end
	$title = trim($title, $sep);

	if (empty($title) && $spVars['pageview'] == 'group' && $sfseo['sfseo_homepage']) $title = $pTitle;

	$title = apply_filters('sph_page_title', $title, $sep);

	$spVars['seotitle'] = $title;
    return $title;
}

# ???
function sp_setup_meta_tags() {
	global $spGlobals, $spVars;

    $spVars['seodescription'] = '';
    $spVars['seokeywords'] = '';
    $spVars['seourl'] = '';

	echo "\n\n";

	if (empty($spGlobals['metadescription'])) {
		$description = sp_get_metadescription();
		if ($description != '') {
			$description = str_replace('"', '', $description);
			echo "\t".'<meta name="description" content="'.$description.'" />'."\n";
		}
		# add to SEO data
		$spVars['seodescription'] = $description;
	}

	if (empty($spGlobals['metakeywords'])) {
		$keywords = sp_get_metakeywords();
		if ($keywords != '') {
			$keywords = str_replace('"', '', $keywords);
			echo "\t".'<meta name="keywords" content="'.$keywords.'" />'."\n";
		}
        $spVars['seokeywords'] = $keywords;
	}

	if (empty($spGlobals['canonicalurl'])) {
		# output the canonical url
		$url = sp_canonical_url();
		echo "\t".'<link rel="canonical" href="'.$url.'" />'."\n";
        $spVars['seourl'] = $url;
	}
}

# ------------------------------------------------------------------
# sp_og_namespace()
#
# Open Graph Namespace
# ------------------------------------------------------------------
function sp_og_namespace($out) {
	$sfseo = sp_get_option('sfseo');
	if(!isset($sfseo)) return $out;
	if(isset($sfseo['sfseo_og']) && $sfseo['sfseo_og'] && strpos($out, 'prefix=') === false) {
		$out.=' prefix="og: http://ogp.me/ns#"';
	}
	return $out;
}

# ------------------------------------------------------------------
# sp_og_meta()
#
# Output Open Graph meta tags
# ------------------------------------------------------------------
function sp_og_meta() {
	global $spVars, $spPaths, $post;

	$sfseo = sp_get_option('sfseo');
	if(!isset($sfseo)) return;

	if(isset($sfseo['sfseo_og']) && $sfseo['sfseo_og']) {
		$mp = "\t<meta property=";
		$out="\n";
		$out.= $mp.'"og:url" content="'.$spVars['seourl'].'"/>'."\n";
		$out.= $mp.'"og:title" content="'.$spVars['seotitle'].'"/>'."\n";
		$out.= $mp.'"og:site_name" content="'.get_option('blogname').'"/>'."\n";
		$out.= $mp.'"og:description" content="'.$spVars['seodescription'].'"/>'."\n";
		$out.= $mp.'"og:type" content="'.$sfseo['seo_og_type'].'"/>'."\n";
		$out.= $mp.'"og:locale" content="'.get_locale().'"/>'."\n";

        # image processing
        $link='';
		if($spVars['topicid'] && $sfseo['seo_og_attachment']) {
			# Topic View
			$link = apply_filters('sph_find_attachment', '');
		}
		# if no attachment then move on...
		if (empty($link)) {
			if(!empty($spVars['featureimage'])) {
				$file = sp_esc_str($spVars['featureimage']);
				$link = SF_STORE_URL.'/'.$spPaths['forum-images'].'/'.$file;
			} elseif(has_post_thumbnail($post->ID)) {
				$thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
				$link = $thumbnail_src[0];
				if (!preg_match('/^https?:\/\//', $link)) {
					$link = site_url('/').ltrim($link, '/');
				}
			}
		}
		if($link) $out.= $mp.'"og:image" content="'.$link.'"/>'."\n";

		echo $out."\n";
	}
}

# ------------------------------------------------------------------
# sp_load_editor()
#
# Loads appropriate editor suport
#	$override: Default or user selected can be overridden
#	$supportOnly:	Load editor support but not the editor scripts
# ------------------------------------------------------------------
function sp_load_editor($override=0, $supportOnly=0) {
	global $spVars, $spGlobals, $spMobile;
	# load editor if required

	if ($override != 0) $spGlobals['editor'] = $override;

	# allow plugins to control editor choice
	$spGlobals['editor'] = apply_filters('sph_this_editor', $spGlobals['editor'], $override, $supportOnly);

	# load editor support
	do_action('sph_load_editor_support', $spGlobals['editor']);

	# only load editor itself on required pages and if not a support only call
	$editorPage = apply_filters('sph_editor_check', 'forum topic profileedit');
	if (!empty($spVars['pageview']) && strpos($editorPage, $spVars['pageview']) !== false && !$supportOnly) {
		do_action('sph_load_editor', $spGlobals['editor']);
	}
}

# ------------------------------------------------------------------
# sp_load_mobile_template()
#
# Loads alternative mobile phone page template of set
# ------------------------------------------------------------------
function sp_load_mobile_template() {
	global $spGlobals, $spMobile;
	if (isset($spGlobals['mobile-display'])) $spGlobals['display']['pagetitle']['notitle'] = $spGlobals['mobile-display']['notitle'];
	if (isset($spGlobals['mobile-display']['usetemplate']) && $spGlobals['mobile-display']['usetemplate']) {
		include get_template_directory().'/'.$spGlobals['mobile-display']['pagetemplate'];
		exit();
	}
}

# ------------------------------------------------------------------
# sp_wp_list_pages()
#
# Filter Call
# Sorts bug in wp_list_pages by swapping out modified title
#	$ptext: Page titles html string
# ------------------------------------------------------------------
function sp_wp_list_pages($ptext) {
	global $spVars, $spGlobals;
	if (!empty($spVars['seotitle'])) {
		$seotitle = $spVars['seotitle'];
		$ptext = str_replace($seotitle, SFPAGETITLE, $ptext);
		$seotitle = html_entity_decode($seotitle, ENT_QUOTES);
		$seotitle = htmlspecialchars($seotitle, ENT_QUOTES, SFCHARSET);
		$ptext = str_replace($seotitle, SFPAGETITLE, $ptext);
		$seotitle = sp_filter_title_save($seotitle);
		$ptext = str_replace($seotitle, SFPAGETITLE, $ptext);
		$ptext = str_replace(strtoupper($seotitle), SFPAGETITLE, $ptext);
	} else {
		if ($spGlobals['display']['pagetitle']['banner'] || $spGlobals['display']['pagetitle']['notitle']) {
			$ptext = str_replace(sp_url().'"></a>', sp_url().'">'.SFPAGETITLE.'</a>', $ptext);
		}
	}
	return $ptext;
}

# ------------------------------------------------------------------
# sp_profile_masonry()
#
# Action Call
# Loads masonty JS lib if showing the profile page
# ------------------------------------------------------------------
function sp_profile_masonry($footer, $tooltips) {
	global $spVars;
	if ($spVars['pageview'] == 'profileshow') {
		wp_enqueue_script('jquery-masonry');
	}
}

# = JAVASCRIPT CHECK ==========================
function sp_js_check() {
	return '<noscript><div><pre><code>'.sp_text('This forum requires Javascript to be enabled for posting content').'</code></pre></div></noscript>'."\n";
}

?>