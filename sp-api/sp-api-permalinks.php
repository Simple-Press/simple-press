<?php
/*
Simple:Press
Forum Permalink Functions
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	SP Permalink Building Routines
#
# ==================================================================

# --------------------------------------------------------------
# sp_build_url()
#
# Version: 5.0
# Main URL building routine
# To use pass forum and topic slugs. Page if not known should
# always be 1 or 0. If a post id is passed (else use zero), the
# routine will go and get the correct page number for the post
# within the topic
#	$forumslug:		forum link
#	$topicslug:		topic link
#	$pageid:		page (if know - if post will calculate)
#	$postid:		post link if relevant (or zero)
#	$postindex:		sequence number of post if relevant
#
# CHANGE IN 4.2.0:
# ==============
# If the POST ID is passed AND a positive PAGE ID then it WILL
# compile a url with that page number. if the page
# number is NOT known when passing a Post ID then page must be
# passed as zero
# --------------------------------------------------------------
function sp_build_url($forumslug, $topicslug, $pageid, $postid=0, $postindex=0, $rss=0) {
	if ($postid != 0 && $pageid == 0) $pageid = sp_determine_page($forumslug, $topicslug, sp_esc_int($postid), sp_esc_int($postindex));
	$url = trailingslashit(sp_url());
	if ($forumslug) $url.= $forumslug;
	if ($topicslug) $url.= '/'.$topicslug;
	if ($rss) {
		if (!empty($forumslug) || !empty($topicslug)) $url.= '/';
		$url.= 'rss';
	}
	if ($pageid > 1) $url.= '/page-'.$pageid;
	$url = user_trailingslashit($url);
	if ($postid) $url.= '#p'.$postid;
	return esc_url(apply_filters('sph_forum_url', $url));
}

# --------------------------------------------------------------
# sp_url()
#
# Version: 5.0
# URL building routine based off of SF Base url for permalinks
# if link arg is present its appended to SF base url
# helper function to keep from multitude of user_trailingslashit() calls
# --------------------------------------------------------------
function sp_url($link='') {
	$url = sp_get_option('sfpermalink');
	if (!empty($link)) $url = trailingslashit($url).$link;
	$url = user_trailingslashit($url);
	return esc_url($url);
}

# ------------------------------------------------------------------
# sp_get_sfqurl()
#
# Version: 5.0
# Build a forum query url ready for parameters
# ------------------------------------------------------------------
function sp_get_sfqurl($url) {
	# if no ? then add one on the end
	$url = user_trailingslashit($url);
	if (strpos($url, '?') === false) {
		$url .= '?';
	} else {
		$url .= '&amp;';
	}
	return $url;
}

# --------------------------------------------------------------
# sp_permalink_from_postid()
#
# Version: 5.0
# Returns permalink for topic from only the post id
# --------------------------------------------------------------
function sp_permalink_from_postid($postid) {
	$url = '';
	if (!empty($postid)) {
		$slugs = sp_get_slugs_from_postid($postid);
		$url = sp_build_url($slugs->forum_slug, $slugs->topic_slug, 0, $postid, $slugs->post_index);
	}
	return $url;
}

# --------------------------------------------------------------
# sp_determine_page()
#
# Version: 5.0
# Determines the correct page with a topic that the post
# will be displayed on based on current settings
#	$topicslug:		to look up topic id if needed
#	$postid:		the post to calculate page for
#	$postindex:		post sequence ig known
# --------------------------------------------------------------
function sp_determine_page($forumslug, $topicslug, $postid, $postindex) {
	global $spGlobals;

	# establish paging count - can sometimes be out of scope so check
	$ppaged=$spGlobals['display']['posts']['perpage'];
	if (empty($ppaged) || $ppaged == 0) $ppaged = 20;

	# establish topic sort order
	$order = 'ASC'; # default
	if ($spGlobals['display']['posts']['sortdesc']) $order = 'DESC'; # global override

	# If we do not have the postindex then we have to go and get it
	if ($postindex == 0 || empty($postindex)) {
		$postindex = spdb_table(SFPOSTS, "post_id=$postid", 'post_index');

		# In the remote possibility postindex is 0 or empty then...
		if ($postindex == 0 || empty($postindex)) {
			$forumrecord = spdb_table(SFFORUMS, "forum_slug='$forumslug'", 'row');
			$topicrecord = spdb_table(SFTOPICS, "topic_slug='$topicslug'", 'row');

			sp_build_post_index($topicrecord->topic_id);
			sp_build_forum_index($forumrecord->forum_id);
			$postindex = spdb_table(SFPOSTS, "post_id=$postid", 'post_index');
		}
	}

	# Now we have what we need to do the math
	if ($order == 'ASC') {
		$page = $postindex / $ppaged;
		if (!is_int($page)) $page = intval($page+1);
	} else {
		if (!isset($topicrecord)) $topicrecord = spdb_table(SFTOPICS, "topic_slug='$topicslug'", 'row');

		$page = $topicrecord->post_count - $postindex;
		$page = $page / $ppaged;
		$page = intval($page+1);
	}
	return $page;
}

# Version: 5.0
function sp_add_get() {
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		return '?';
	} else {
		return '&amp;';
	}
}

# ------------------------------------------------------------------
# sp_filter_wp_ampersand()
#
# Version: 5.0
# Replace & with &amp; in urls
#	$url:		url to be filtered
# ------------------------------------------------------------------
function sp_filter_wp_ampersand($url) {
	return str_replace('&', '&amp;', $url);
}

# ------------------------------------------------------------------
# sp_get_slugs_from_postid()
#
# Version: 5.0
# Returns forum and topic slugs when only the post id is known
#	$postid:		Post to lookup
# ------------------------------------------------------------------
function sp_get_slugs_from_postid($postid) {
	if (!$postid) return '';

	return spdb_select('row',
			'SELECT forum_slug, topic_slug, post_index
			 FROM '.SFPOSTS.'
			 JOIN '.SFFORUMS.' ON '.SFPOSTS.'.forum_id = '.SFFORUMS.'.forum_id
			 JOIN '.SFTOPICS.' ON '.SFPOSTS.'.topic_id = '.SFTOPICS.'.topic_id
			 WHERE '.SFPOSTS.".post_id=$postid");
}

# ------------------------------------------------------------------
# sp_redirect()
#
# Version: 5.0
# Redirect using javascript since headers may be started already
#	$url:		url to redirect to
# ------------------------------------------------------------------
function sp_redirect($url) {
?>
	<script type="text/javascript">window.location= "<?php echo $url; ?>";</script>
<?php
	exit();
}

# ------------------------------------------------------------------
# sp_update_post_urls()
#
# Version: 5.0
# Updates slugs in posts for forum or topic if they are changed
#	$old		old slug
#	$new		replacement slug
# ------------------------------------------------------------------
function sp_update_post_urls($old, $new) {
    global $wpdb;

	if (empty($old) || empty($new)) return;
	$posts = spdb_table(SFPOSTS, 'post_content LIKE "%/'.sp_esc_sql($wpdb->esc_like($old)).'%"', '');
	if (!empty($posts)) {
		foreach ($posts as $p) {
			$pc = str_replace('/'.$old, '/'.$new, sp_filter_content_edit($p->post_content));
			$pc = sp_filter_content_save($pc, 'edit');
			spdb_query('UPDATE '.SFPOSTS." SET post_content = '$pc' WHERE post_id=".$p->post_id);
		}
	}
}

?>