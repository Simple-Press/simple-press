<?php
/*
Simple:Press
Forum RSS Feeds
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# check installed version is correct
if (sp_get_system_status() != 'ok') {
	$out .= '<img style="style="vertical-align:bottom;border:none;"" src="'.SP()->theme->paint_file_icon(SPTHEMEICONSURL, 'sp_Information.png').'" alt="" />'."\n";
	$out .= '&nbsp;&nbsp;'.SP()->primitives->front_text('The forum is temporarily unavailable while being upgraded to a new version');
	echo $out;
}

# are we doing feed keys? If so reset user to that f the passed feedkey - else leave as guest
$rssopt = SP()->options->get('sfrss');
if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey']) {
	# get the user requesting feed
	$feedkey    = SP()->rewrites->pageData['feedkey'];
	$userid     = SP()->DB->table(SPMEMBERS, "feedkey='$feedkey'", 'user_id');
	SP()->user->thisUser = SP()->user->get($userid, true);
}

# = Support Functions ===========================
function sp_rss_filter($text) {
	echo convert_chars(ent2ncr($text));
}

function sp_rss_excerpt($text) {
	$rssopt = SP()->options->get('sfrss');
	$max    = $rssopt['sfrsswords'];
	if ($max == 0) return $text;
	$bits = explode(" ", $text);
	$end  = '';
	if (count($bits) < $max) {
		$max = count($bits);
	} else {
		$end = '...';
	}
	$text = '';
	for ($x = 0; $x < $max; $x++) {
		$text .= $bits[$x].' ';
	}

	return $text.$end;
}

# Get the options and the feed type
$limit = $rssopt['sfrsscount'];
if (!isset($limit)) $limit = 15;
$order = SPPOSTS.'.post_id DESC';

$feed = SP()->rewrites->pageData['feed'];

# Set up the where clauses
switch ($feed) {
	case 'all':
		$where = SPFORUMS.'.forum_rss_private = 0';
		break;

	case 'group':
		$groupid = SP()->filters->integer($_GET['group']);
		$where   = SPFORUMS.".group_id=$groupid AND ".SPFORUMS.".forum_rss_private = 0";
		break;

	case 'forum':
		$forumid = SP()->rewrites->pageData['forumid'];
		$where   = SPPOSTS.".forum_id=$forumid AND ".SPFORUMS.".forum_rss_private = 0";
		break;

	case 'topic':
		$topicid = SP()->rewrites->pageData['topicid'];
		$where   = SPPOSTS.".topic_id=$topicid AND ".SPFORUMS.".forum_rss_private = 0";
		break;
}

$where = apply_filters('sph_rss_where', $where, $feed);

$rssItem  = array();
$rssTitle = $rssLink = $rssDescription = $rssGenerator = $atomLink = '';

# Execute the query
SP()->forum->view->listPosts = new spcPostList($where, $order, $limit, 'post-content', 'feeds');
if (SP()->forum->view->has_postlist()) {
	$first = current(SP()->forum->view->listPosts->listData);
	reset(SP()->forum->view->listPosts->listData);

	# Define channel elements for each feed type
	switch ($feed) {
		case 'all':
			$rssTitle = get_bloginfo('name').' - '.SP()->primitives->front_text('All Forums');
			$rssLink  = SP()->spPermalinks->get_url();
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
				$atomLink = trailingslashit(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey);
			} else {
				$atomLink = SP()->spPermalinks->build_url('', '', 0, 0, 0, 1);
			}
			break;

		case 'group':
			$rssTitle = get_bloginfo('name').' - '.SP()->primitives->front_text('Group').': '.$first->group_name;
			$rssLink  = add_query_arg(array('group' => $groupid), SP()->spPermalinks->get_url());
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
				$atomLink = SP()->spPermalinks->get_query_url(trailingslashit(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey)).'group='.$groupid;
			} else {
				$atomLink = SP()->spPermalinks->get_query_url(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).'group='.$groupid;
			}
			break;

		case 'forum':
			$rssTitle = get_bloginfo('name').' - '.SP()->primitives->front_text('Forum').': '.$first->forum_name;
			$rssLink  = SP()->spPermalinks->build_url($first->forum_slug, '', 0, 0);
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
				$atomLink = trailingslashit(SP()->spPermalinks->build_url($first->forum_slug, '', 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey);
			} else {
				$atomLink = SP()->spPermalinks->build_url($first->forum_slug, '', 0, 0, 0, 1);
			}
			break;

		case 'topic':
			$rssTitle = get_bloginfo('name').' - '.SP()->primitives->front_text('Topic').': '.$first->topic_name;
			$rssLink  = SP()->spPermalinks->build_url($first->forum_slug, $first->topic_slug, 0, 0);
			if (isset($rssopt['sfrssfeedkey']) && $rssopt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
				$atomLink = trailingslashit(SP()->spPermalinks->build_url($first->forum_slug, $first->topic_slug, 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey);
			} else {
				$atomLink = SP()->spPermalinks->build_url($first->forum_slug, $first->topic_slug, 0, 0, 0, 1);
			}
			break;
	}

	# init rss info with filters
	$rssTitle       = apply_filters('sph_feed_title', $rssTitle, $first);
	$rssDescription = apply_filters('sph_feed_description', get_bloginfo('description'));
	$rssGenerator   = apply_filters('sph_feed_generator', SP()->primitives->front_text('Simple:Press Version').' '.SPVERSION);

	# set up time for current user timezone
	$tz = get_option('timezone_string');
	if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
	$tzUser = (!empty(SP()->user->thisUser->timezone_string)) ? SP()->user->thisUser->timezone_string : $tz;
	if (substr($tzUser, 0, 3) == 'UTC') $tzUser = 'UTC';
	date_default_timezone_set($tzUser);

	# Now loop through the post records
	while (SP()->forum->view->loop_postlist()) : SP()->forum->view->the_postlist();
		$item              = new stdClass;
		$item->title       = SP()->forum->view->thisListPost->display_name.' '.SP()->primitives->front_text('on').' '.SP()->forum->view->thisListPost->topic_name;
		$item->link        = SP()->forum->view->thisListPost->post_permalink;
		$item->pubDate     = date('r', strtotime(SP()->forum->view->thisListPost->post_date));
		$item->category    = SP()->forum->view->thisListPost->forum_name;
		$item->description = sp_rss_excerpt(SP()->displayFilters->rss(SP()->forum->view->thisListPost->post_content));
		$item->guid        = SP()->forum->view->thisListPost->post_permalink;

		# allow plugins/themes to modify feed item
		$item = apply_filters('sph_feed_item', $item, SP()->forum->view->thisListPost);

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
header("Expires: ".date('r', time() + 3600));
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
        <atom:link href="<?php sp_rss_filter($atomLink) ?>" rel="self" type="application/rss+xml"/>
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