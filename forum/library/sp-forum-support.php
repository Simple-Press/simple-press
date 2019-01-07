<?php
/*
Simple:Press
Support Routines
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	FORUM
# 	This file loads the SP support needed by all front end forum page loads
#	This file should contain only functions not required by plugins or plugin authors
#	and is specific to foirum display only.
#
#	sp_push_topic_page()
#	sp_pop_topic_page()
#	sp_display_banner()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_push_topic_page()
#
# called on forum display to note current topic page user is viewing.
#	$forumid:
#	$page:
# ------------------------------------------------------------------
function sp_push_topic_page($forumid, $page) {
	SP()->cache->add('bookmark', $forumid.'@'.$page);
}

# ------------------------------------------------------------------
# sp_pop_topic_page()
#
# called on topic display to set breadcrumb to correct page
# if same forum
#	$forumid:
# ------------------------------------------------------------------
function sp_pop_topic_page($forumid) {
	$page  = 1;
	$check = SP()->cache->get('bookmark');

	# if no record then resort to page 1
	if ($check == '') return $page;
	$check = explode('@', $check);

	# is it the same forum?
	if ($check[0] == $forumid) $page = $check[1];

	return $page;
}

# ------------------------------------------------------------------
# sp_display_banner()
#
# displays optional banner instead of page title
# ------------------------------------------------------------------
function sp_display_banner() {
	$text = (!empty(SP()->core->forumData['display']['pagetitle']['banner'])) ? '<img id="sfbanner" src="'.esc_url(SP()->core->forumData['display']['pagetitle']['banner']).'" alt="" />' : '';

	return $text;
}
