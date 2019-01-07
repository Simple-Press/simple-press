<?php
/*
Simple:Press
Forum Topic/Post Saves
$LastChangedDate: 2018-10-24 12:03:43 -0500 (Wed, 24 Oct 2018) $
$Rev: 15768 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# set up required globals and load support files -----------------------------------
sp_forum_ajax_support();
sp_load_editor(0, 1);

require_once SP_PLUGIN_DIR.'/forum/library/sp-post-support.php';

# Initialise the class -------------------------------------------------------------
$p = new spcPost;

# Set up curret user details needed to keep class user agnostic
$p->userid    = SP()->user->thisUser->ID;
$p->admin     = SP()->user->thisUser->admin;
$p->moderator = SP()->user->thisUser->moderator;
$p->member    = SP()->user->thisUser->member;
$p->guest     = SP()->user->thisUser->guest;

$p->call = 'post';

# Set data items needed for initial needed permission checks -----------------------
if (isset($_POST['newaction'])) $p->action = SP()->filters->str($_POST['newaction']);

if (isset($_POST['forumid'])) $p->newpost['forumid'] = SP()->filters->integer($_POST['forumid']);
if (isset($_POST['forumslug'])) $p->newpost['forumslug'] = SP()->filters->str($_POST['forumslug']);

if ($p->action == 'post') {
	if (isset($_POST['topicid'])) $p->newpost['topicid'] = SP()->filters->integer($_POST['topicid']);
	if (isset($_POST['topicslug'])) $p->newpost['topicslug'] = SP()->filters->str($_POST['topicslug']);
}

# Anti-spam-bot/human checks come first ------------------------------------------------------
$p->validateHuman($_POST);
if ($p->abort) {
	# it the checks fail then just die.
	die();
}

# Permission checks on forum data --------------------------------------------------
$p->validatePermission();
if ($p->abort) {
	SP()->notifications->message(SPFAILURE, $p->message);
	wp_redirect($p->returnURL);
	die();
}

# setup and prepare post data ready for validation ---------------------------------
if ($p->action == 'topic') {
	$p->newpost['topicname']   = SP()->filters->str($_POST['newtopicname']);
	$p->newpost['topicpinned'] = isset($_POST['topicpin']);
}

if ($p->action == 'post') {
	$p->newpost['topicname']  = SP()->DB->table(SPTOPICS, 'topic_id='.$p->newpost['topicid'], 'topic_name');
	$p->newpost['postpinned'] = isset($_POST['postpin']);
}

# Both
if (SP()->user->thisUser->guest) {
	if (!empty($_POST['guestname'])) $p->newpost['guestname'] = SP()->filters->str($_POST['guestname']);
	if (!empty($_POST['guestemail'])) $p->newpost['guestemail'] = SP()->filters->str($_POST['guestemail']);
} else {
	$p->newpost['postername']  = SP()->user->thisUser->display_name;
	$p->newpost['posteremail'] = SP()->user->thisUser->user_email;
	$p->newpost['userid']      = SP()->user->thisUser->ID;
}

$p->newpost['postcontent'] = $_POST['postitem']; # Sanitised when used in the post class code
$p->newpost['posterip']    = sp_get_ip();

if (isset($_POST['topiclock'])) $p->newpost['topicstatus'] = 1;

if (!empty($_POST['editTimestamp'])) {
	$yy                     = SP()->filters->integer($_POST['tsYear']);
	$mm                     = SP()->filters->integer($_POST['tsMonth']);
	$dd                     = SP()->filters->integer($_POST['tsDay']);
	$hh                     = SP()->filters->integer($_POST['tsHour']);
	$mn                     = SP()->filters->integer($_POST['tsMinute']);
	$ss                     = SP()->filters->integer($_POST['tsSecond']);
	$dd                     = ($dd > 31) ? 31 : $dd;
	$hh                     = ($hh > 23) ? $hh - 24 : $hh;
	$mn                     = ($mn > 59) ? $mn - 60 : $mn;
	$ss                     = ($ss > 59) ? $ss - 60 : $ss;
	$p->newpost['postdate'] = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $yy, $mm, $dd, $hh, $mn, $ss);
}

# Permission checks on forum data --------------------------------------------------
$p->validateData();
if ($p->abort) {
	sp_return_to_post($p->returnURL, $p->message);
	die();
}

# let any plugins perform their stuff ----------------------------------------------
do_action('sph_editor_pre_post_create', $p->newpost);
$p->newpost = apply_filters('sph_editor_new_forum_post', $p->newpost);

# make sure plugin didnt cancel the save -------------------------------------------
if (!empty($p->newpost['error'])) {
	sp_return_to_post($p->returnURL, $p->newpost['error']);
	die();
}

# ready for some unique and topic/post form specific checks ------------------------
$spamcheck = sp_check_spammath($p->newpost['forumid']);
if ($spamcheck[0] == true) {
	sp_return_to_post($p->returnURL, $spamcheck[1]);
	die();
}

# now we can save to the database --------------------------------------------------
$p->saveData();
if ($p->abort) {
	sp_return_to_post($p->returnURL, $p->message);
	die();
} else {
	if ($p->action == 'topic') {
		SP()->notifications->message(SPSUCCESS, SP()->primitives->front_text('New topic saved').$p->newpost['submsg']);
	} else {
		SP()->notifications->message(SPSUCCESS, SP()->primitives->front_text('New post saved').$p->newpost['submsg']);
	}
}

do_action('sph_editor_post_create', $p->newpost);

$p->returnURL = apply_filters('sph_new_forum_post_returnurl', $p->returnURL, $p->newpost);

wp_redirect($p->returnURL);

die();

# ==================================================================================
# Return to editor if problem
function sp_return_to_post($returnURL, $message) {
	# place details in the cache
	$failure            = array();
	$failure['message'] = SP()->primitives->front_text('Unable to save').'<br>'.$message;
	if (isset($_POST['newtopicname']) ? $failure['newtopicname'] = SP()->filters->str($_POST['newtopicname']) : $failure['newtopicname'] = '') ;
	if (isset($_POST['guestname']) ? $failure['guestname'] = SP()->filters->str($_POST['guestname']) : $failure['guestname'] = '') ;
	if (isset($_POST['guestemail']) ? $failure['guestemail'] = SP()->filters->str($_POST['guestemail']) : $failure['guestemail'] = '') ;
	$failure['postitem'] = SP()->filters->str($_POST['postitem']);

	SP()->cache->add('post', $failure);
	wp_redirect($returnURL);
}
