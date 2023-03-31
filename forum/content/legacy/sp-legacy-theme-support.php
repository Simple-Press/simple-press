<?php
/*
Simple:Press
Legacy SP theme support - V1 themes
$LastChangedDate: 2017-08-12 10:30:12 +0100 (Sat, 12 Aug 2017) $
$Rev: 15504 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# These legacy 'translation' functions allow level/version 1 themes to be used
# in the 6.0 class based core.
#
# --------------------------------------------------------------------------------------

global $spThisPostUser;
global $spDevice;
global $spVars;
global $spThisMember;
global $spThisPost;
global $spThisUser;
global $spListView;
global $spProfileUser;
global $spThisPostList;

$spDevice = SP()->core->device;
$spVars['pageview'] = SP()->rewrites->pageData['pageview'];
$spVars['profile']	= SP()->rewrites->pageData['profile'];
$spVars['thread']	= isset(SP()->rewrites->pageData['thread']) ? SP()->rewrites->pageData['thread'] : "" ;

if ($spVars['pageview'] == 'newposts' || $spVars['pageview'] == 'pmthread') {
	$spThisUser = new stdClass();
	$spThisUser = SP()->user->thisUser;
}

# since we instantiated topic lists in theme, need to map for legacy
 sp_legacy_theme_list_setup();

#
# helper function since used from multiple spots
#

function sp_legacy_theme_list_setup() {
	global $spListView;

	if (!class_exists('spTopicList')) {
		SP()->forum->view->listTopics = &$spListView;
		class spTopicList extends spcTopicList {}
	}
}

//if ($tempName == 'spProfilePopupShow.php' || $tempName == 'spProfileShow.php') {
//	$spProfileUser = new stdclass();
//	$spProfileUser = SP()->user->profileUser;
//}

#
# need template name for profile popup
#

add_filter('sph_template_load_name', 'sp_legacy_profile_show');
function sp_legacy_profile_show($tempName) {
	global $spProfileUser;
	if ($tempName == 'spProfilePopupShow.php' || $tempName == 'spProfileShow.php') {
		$spProfileUser = new stdclass();
		$spProfileUser = SP()->user->profileUser;
	}
	return $tempName;
}


# --------------------------------------------------------------------------------------
#
# Various - all pre-class loop functions
#
# These original template loop functions allow for pre-6.0 themes to work
# but should be considered temporary and eventually should be phased out
#
# --------------------------------------------------------------------------------------

# GROUP VIEW FUNCTIONS
# --------------------

function sp_has_groups() {
	return SP()->forum->view->has_groups();
}

function sp_loop_groups() {
	while (SP()->forum->view->loop_groups()) return true;
}

function sp_the_group() {
	SP()->forum->view->the_group();
}

function sp_has_forums() {
	return SP()->forum->view->has_forums();
}

function sp_loop_forums() {
	while (SP()->forum->view->loop_forums()) return true;
}

function sp_the_forum() {
	SP()->forum->view->the_forum();
}

# FORUM VIEW FUNCTIONS
# --------------------

function sp_this_forum() {
	if (SP()->forum->view->this_forum()) return true;
}

function sp_has_subforums() {
	if (SP()->forum->view->has_subforums()) return true;
}

function sp_loop_subforums() {
	while (SP()->forum->view->loop_subforums()) return true;
}

function sp_the_subforum() {
	SP()->forum->view->the_subforum();
}

function sp_is_child_subforum() {
	return SP()->forum->view->is_child_subforum();
}

function sp_has_topics() {
	return SP()->forum->view->has_topics();
}

function sp_loop_topics() {
	while (SP()->forum->view->loop_topics()) return true;
}

function sp_the_topic() {
	SP()->forum->view->the_topic();
}

# TOPIC VIEW FUNCTIONS
# --------------------

function sp_this_topic() {
	if (SP()->forum->view->this_topic()) return true;
}

function sp_has_posts() {
	return SP()->forum->view->has_posts();
}

function sp_loop_posts() {
	while (SP()->forum->view->loop_posts()) return true;
}

function sp_the_post() {
	global $spThisPostUser;
	global $spThisPost;
	$spThisPostUser = new stdClass();
	$spThisPost = new stdClass();
	SP()->forum->view->the_post();
	$spThisPostUser = SP()->forum->view->thisPostUser;
	$spThisPost->post_index = SP()->forum->view->thisPost->post_index;
}

# SEARCH LIST VIEW
# ----------------

function sp_has_postlist() {
	return SP()->forum->view->has_postlist();
}

function sp_loop_postlist() {
	while (SP()->forum->view->loop_postlist()) return true;
}

function sp_the_postlist() {
	global $spThisPostList;
	$spThisPostList = new stdclass();
	SP()->forum->view->the_postlist();
	$spThisPostList = SP()->forum->view->thisListPost;
}

# LIST VIEW FUNCTIONS
# -------------------

function sp_has_list() {
	return SP()->forum->view->has_topiclist();
}

function sp_loop_list() {
	while (SP()->forum->view->loop_topiclist()) return true;
}

function sp_the_list() {
	SP()->forum->view->the_topiclist();
}

# MEMBERS LIST VIEW
# -----------------

function sp_has_member_groups($usergroup, $id, $asc, $num, $true) {
	if (SP()->forum->view->has_member_groups($usergroup, $id, $asc, $num, $true)) return true;
}

function sp_loop_member_groups() {
	while (SP()->forum->view->loop_member_groups()) return true;
}

function sp_the_member_group() {
	SP()->forum->view->the_member_group();
}

function sp_has_members() {
	return SP()->forum->view->has_members();
}

function sp_loop_members() {
	while (SP()->forum->view->loop_members()) return true;
}

function sp_the_member() {
	global $spThisMember;
	SP()->forum->view->the_member();
	$spThisMember = new stdClass();
	$spThisMember = SP()->forum->view->thisMember;
}

# OTHERS (Used in templates)
# --------------------------

function sp_get_auth($auth) {
	return SP()->auths->get($auth);
}

function sp_get_option($option) {
	return SP()->options->get($option);
}

function sp_esc_int($value) {
	return SP()->filters->integer($value);
}

function sp_url($target) {
	return SP()->spPermalinks->get_url($target);
}
