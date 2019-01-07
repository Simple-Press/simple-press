<?php
/*
Simple:Press
Forum RSS Feeds
$LastChangedDate: 2015-05-16 14:09:51 -0500 (Sat, 16 May 2015) $
$Rev: 12891 $
*/
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

global $spVars, $spThisUser, $spPostList, $spThisPostList;

# check installed version is correct
if (sp_get_system_status() != 'ok') {
	$out.= '<img style="style="vertical-align:bottom;border:none;"" src="'.sp_paint_file_icon(SPTHEMEICONSURL, 'sp_Information.png').'" alt="" />'."\n";
	$out.= '&nbsp;&nbsp;'.sp_text('The forum is temporarily unavailable while being upgraded to a new version');
	echo $out;
}

# are we doing feed keys? If so reset user to that f the passed feedkey - else leave as guest
$rssopt = sp_get_option('sfrss');
if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey']) {
	# get the user requesting feed
	$feedkey = $spVars['feedkey'];
	$userid = spdb_table(SFMEMBERS, "feedkey='$feedkey'", 'user_id');
	$spThisUser = sp_get_user($userid, true);
}

# = Support Functions ===========================
function sp_rss_filter($text) {
  echo convert_chars(ent2ncr($text));
}

function sp_rss_excerpt($text) {
    $rssopt = sp_get_option('sfrss');
	$max = $rssopt['sfrsswords'];
	if ($max == 0) return $text;
	$bits = explode(" ", $text);
	$text = '';
	$end = '';
	if (count($bits) < $max) {
		$max = count($bits);
	} else {
		$end = '...';
	}
	$text = '';
	for ($x=0; $x<$max; $x++) {
		$text.= $bits[$x].' ';
	}
	return $text.$end;
}

# Get the options and the feed type
$limit = $rssopt['sfrsscount'];
if (!isset($limit)) $limit = 15;
$order = SFPOSTS.'.post_id DESC';

$feed = $spVars['feed'];

# Set up the where clauses
switch ($feed) {
	case 'all':
		$where = SFFORUMS.'.forum_rss_private = 0';
        break;

	case 'group':
		$groupid = sp_esc_int($_GET['group']);
		$where = SFFORUMS.".group_id=$groupid AND ".SFFORUMS.".forum_rss_private = 0";
        break;

	case 'forum':
		$forumid = $spVars['forumid'];
		$where = SFPOSTS.".forum_id=$forumid AND ".SFFORUMS.".forum_rss_private = 0";
        break;

	case 'topic':
		$topicid = $spVars['topicid'];
		$where = SFPOSTS.".topic_id=$topicid AND ".SFFORUMS.".forum_rss_private = 0";
        break;
}

$where = apply_filters('sph_rss_where', $where, $feed);

$rssItem = array();
$rssTitle = $rssLink = $rssDescription = $rssGenerator = $atomLink = '';

# Execute the query
$spPostList = new spPostList($where, $order, $limit, 'post-content', 'feeds');
if (sp_has_postlist()) {
	$first = current($spPostList->listData);
	reset($spPostList->listData);

	# Define channel elements for each feed type
	switch ($feed) {
		case 'all':
			$rssTitle = get_bloginfo('name').' - '.sp_text('All Forums');
			$rssLink = sp_url();
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
				$atomLink = trailingslashit(sp_build_url('', '', 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey);
			} else {
				$atomLink = sp_build_url('', '', 0, 0, 0, 1);
			}
            break;

		case 'group':
			$rssTitle = get_bloginfo('name').' - '.sp_text('Group').': '.$first->group_name;
			$rssLink = add_query_arg(array('group'=>$groupid), sp_url());
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
				$atomLink = sp_get_sfqurl(trailingslashit(sp_build_url('', '', 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey)).'group='.$groupid;
			} else {
				$atomLink = sp_get_sfqurl(sp_build_url('', '', 0, 0, 0, 1)).'group='.$groupid;
			}
            break;

		case 'forum':
			$rssTitle = get_bloginfo('name').' - '.sp_text('Forum').': '.$first->forum_name;
			$rssLink = sp_build_url($first->forum_slug, '', 0, 0);
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
				$atomLink = trailingslashit(sp_build_url($first->forum_slug, '', 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey);
			} else {
				$atomLink = sp_build_url($first->forum_slug, '', 0, 0, 0, 1);
			}
            break;

		case 'topic':
			$rssTitle = get_bloginfo('name').' - '.sp_text('Topic').': '.$first->topic_name;
			$rssLink = sp_build_url($first->forum_slug, $first->topic_slug, 0, 0);
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
				$atomLink = trailingslashit(sp_build_url($first->forum_slug, $first->topic_slug, 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey);
			} else {
				$atomLink = sp_build_url($first->forum_slug, $first->topic_slug, 0, 0, 0, 1);
			}
            break;
	}

    # init rss info with filters
    $rssTitle = apply_filters('sph_feed_title', $rssTitle, $first);
	$rssDescription = apply_filters('sph_feed_description', get_bloginfo('description'));
	$rssGenerator = apply_filters('sph_feed_generator', sp_text('Simple:Press Version').' '.SPVERSION);

    # set up time for current user timezone
	$tz = get_option('timezone_string');
	if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
	$tzUser = (!empty($spThisUser->timezone_string)) ? $spThisUser->timezone_string : $tz;
	if (substr($tzUser, 0, 3) == 'UTC') $tzUser = 'UTC';
	date_default_timezone_set($tzUser);

	# Now loop through the post records
	while (sp_loop_postlist()) : sp_the_postlist();
		$item = new stdClass;
			$item->title 		= $spThisPostList->display_name.' '.sp_text('on').' '.$spThisPostList->topic_name;
			$item->link 		= $spThisPostList->post_permalink;
			$item->pubDate 		= date('r', strtotime($spThisPostList->post_date));
			$item->category 	= $spThisPostList->forum_name;
			$item->description 	= sp_rss_excerpt(sp_filter_rss_display($spThisPostList->post_content));
			$item->guid 		= $spThisPostList->post_permalink;

        # allow plugins/themes to modify feed item
        $item = apply_filters('sph_feed_item', $item, $spThisPostList);

		$rssItem[] = $item;
	endwhile;

    # restore timezone
	date_default_timezone_set('UTC');
}

do_action('sph_feed_before_headers', $rssItem);

# Send headers and XML
header("HTTP/1.1 200 OK");
header('Content-Type: application/xml');
header("Cache-control: max-age=3600");
header("Expires: ".date('r', time()+3600));
header("Pragma: ");
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<?php do_action('sph_feed_after_start'); ?>
<channel>
	<title><?php sp_rss_filter($rssTitle) ?></title>
	<link><?php sp_rss_filter($rssLink) ?></link>
	<description><![CDATA[<?php sp_rss_filter($rssDescription) ?>]]></description>
	<generator><?php sp_rss_filter($rssGenerator) ?></generator>
	<atom:link href="<?php sp_rss_filter($atomLink) ?>" rel="self" type="application/rss+xml" />
<?php
if (!empty($rssItem)) {
	foreach ($rssItem as $item) {
?>
        <item>
        	<title><?php sp_rss_filter($item->title) ?></title>
        	<link><?php sp_rss_filter($item->link) ?></link>
        	<category><?php sp_rss_filter($item->category) ?></category>
        	<guid isPermaLink="true"><?php sp_rss_filter($item->guid) ?></guid>
        	<?php if (!$rssopt['sfrsstopicname']) { ?>
        	<description><![CDATA[<?php sp_rss_filter($item->description) ?>]]></description>
        	<?php } ?>
        	<pubDate><?php sp_rss_filter($item->pubDate) ?></pubDate>
        </item>
<?php
        do_action('sph_feed_after_item', $item);
	}
}
?>
</channel>
<?php
    do_action('sph_feed_before_end');
?>
</rss>