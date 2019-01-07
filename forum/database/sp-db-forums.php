<?php
/*
Simple:Press
Main database routines
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	GLOBAL Database Module
# 	Main Forum Select Database Routines
#
#	sp_get_group_name_from_forum()
#	sp_get_group_record_from_slug()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_get_group_name_from_forum()
#
# Returns the Group Name when only the forum id is known
#	$forumid:		forum to lookup for group name
# ------------------------------------------------------------------
function sp_get_group_name_from_forum($forumid) {
	if (!$forumid) return '';

	return SP()->DB->select("SELECT ".SPGROUPS.".group_name
			 FROM ".SPGROUPS."
			 JOIN ".SPFORUMS." ON ".SPFORUMS.".group_id = ".SPGROUPS.".group_id
			 WHERE ".SPFORUMS.".forum_id=".$forumid, 'var');
}

# ------------------------------------------------------------------
# sp_get_group_record_from_slug()
#
# Returns a single group and forum row
#	$forumslug:		forum_slug of group and forum to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sp_get_group_record_from_slug($forumslug, $asArray = false) {
	if (!$forumslug) return '';

	$sql = ("SELECT *
			 FROM ".SPFORUMS."
			 JOIN ".SPGROUPS." ON ".SPFORUMS.".group_id = ".SPGROUPS.".group_id
			 WHERE forum_slug='".$forumslug."';");
	if ($asArray) return SP()->DB->select($sql, 'row', ARRAY_A);

	return SP()->DB->select($sql, 'row');
}
