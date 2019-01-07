<?php
/*
Simple:Press
Users New Posts Popup
$LastChangedDate: 2016-09-09 17:09:02 -0500 (Fri, 09 Sep 2016) $
$Rev: 14564 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('spUnreadPostsPopup')) die();

global $spThisUser, $spListView, $spThisListTopic;

$popup = 1;

if (!isset($_GET['targetaction'])) die();
if (isset($_GET['popup'])) $popup = sp_esc_int($_GET['popup']);
$count = (isset($_GET['count'])) ? sp_esc_int($_GET['count']) : 0;

# Individual forum new post listing
if ($_GET['targetaction'] == 'forum') {
	if (isset($_GET['id'])) {
		$fid = (int) $_GET['id'];
		$topics = array();
		for ($x=0; $x<count($spThisUser->newposts['forums']); $x++) {
			if ($spThisUser->newposts['forums'][$x] == $fid) $topics[] = $spThisUser->newposts['topics'][$x];
		}

		if ($popup) echo '<div id="spMainContainer">';
        $first = sp_esc_int($_GET['first']);
        $group = isset($_GET['group']) ? sp_esc_int($_GET['group']) : false;
		$spListView = new spTopicList($topics, $count, $group, $fid, $first, $popup, 'forum unread posts');

		sp_load_template('spListView.php');
		if ($popup) echo '</div>';
	}
}

# All forums (users new post list)
if ($_GET['targetaction'] == 'all') {
	echo '<div id="spMainContainer">';
    $first = sp_esc_int($_GET['first']);
    $group = sp_esc_int($_GET['group']);
	$spListView = new spTopicList($spThisUser->newposts['topics'], $count, $group, '', $first, $popup, 'all unread posts');

	sp_load_template('spListView.php');
	echo '</div>';
}

if ($_GET['targetaction'] == 'mark-read') {
    sp_mark_all_read();

    die();
}

if ($_GET['targetaction'] == 'mark-forum-read') {
    $forum = (empty($_GET['forum'])) ? '' : sp_esc_int($_GET['forum']);
    if (empty($forum)) die();

    sp_mark_forum_read($forum);

    die();
}

die();
?>