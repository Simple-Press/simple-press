<?php
/*
Simple:Press
Users New Posts Popup
$LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
$Rev: 15797 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('spUnreadPostsPopup')) die();

$popup = 1;

if (!isset($_GET['targetaction'])) die();
if (isset($_GET['popup'])) $popup = SP()->filters->integer($_GET['popup']);
$count = (isset($_GET['count'])) ? SP()->filters->integer($_GET['count']) : 0;

# Individual forum new post listing
if (sanitize_text_field($_GET['targetaction']) == 'forum') {
	if (isset($_GET['id'])) {
		$fid = (int) $_GET['id'];
		$topics = array();
		for ($x=0; $x<count(SP()->user->thisUser->newposts['forums']); $x++) {
			if (SP()->user->thisUser->newposts['forums'][$x] == $fid) $topics[] = SP()->user->thisUser->newposts['topics'][$x];
		}

		if ($popup) echo '<div id="spMainContainer">';
        $first = SP()->filters->integer($_GET['first']);
        $group = isset($_GET['group']) ? SP()->filters->integer($_GET['group']) : false;
		SP()->forum->view->listTopics = new spcTopicList($topics, $count, $group, $fid, $first, $popup, 'forum unread posts');

		sp_load_template('spListView.php');
		if ($popup) echo '</div>';
	}
}

# All forums (users new post list)
if (sanitize_text_field($_GET['targetaction']) == 'all') {
	echo '<div id="spMainContainer">';
    $first = SP()->filters->integer($_GET['first']);
    $group = SP()->filters->integer($_GET['group']);
	SP()->forum->view->listTopics = new spcTopicList(SP()->user->thisUser->newposts['topics'], $count, $group, '', $first, $popup, 'all unread posts');

	sp_load_template('spListView.php');
	echo '</div>';
}

if (sanitize_text_field($_GET['targetaction']) == 'mark-read') {
    sp_mark_all_read();

    die();
}

if (sanitize_text_field($_GET['targetaction']) == 'mark-forum-read') {
    $forum = (empty($_GET['forum'])) ? '' : SP()->filters->integer($_GET['forum']);
    if (empty($forum)) die();

    sp_mark_forum_read($forum);

    die();
}

die();
