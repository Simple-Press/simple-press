<?php
/*
Simple:Press
User Group Specials
$LastChangedDate: 2013-03-02 17:15:32 +0000 (Sat, 02 Mar 2013) $
$Rev: 9944 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('memberships')) die();

$action = sp_esc_str($_GET['targetaction']);
$startNum = sp_esc_int($_GET['startNum']);
$batchNum = sp_esc_int($_GET['batchNum']);

if ($action == 'add') {
	check_admin_referer('forum-adminform_membernew', 'forum-adminform_membernew');
	# add the users to the user group membership
	$usergroup_id = sp_esc_int($_GET['usergroup_id']);
	if (isset($_GET['amid'])) $user_id_list = array_unique($_GET['amid']);

	if (isset($user_id_list)) {
		for ($x = $startNum; $x < ($startNum + $batchNum); $x++) {
			if (isset($user_id_list[$x])) {
				$user_id = sp_esc_int($user_id_list[$x]);
				sp_add_membership($usergroup_id, $user_id);
			}
		}
	}
}

if ($action == 'del') {
    check_admin_referer('forum-adminform_memberdel', 'forum-adminform_memberdel');

    $usergroup_id = sp_esc_int($_GET['usergroupid']);
    $new_usergroup_id = $_GET['usergroup_id'];
    if (isset($_GET['dmid'])) $user_id_list = array_unique($_GET['dmid']);

	# make sure not moving to same user group
	if (!isset($user_id_list) || $usergroup_id == $new_usergroup_id) {
		die();
	}

	for ($x = $startNum; $x < ($startNum + $batchNum); $x++) {
		if (isset($user_id_list[$x])) {
			$user_id = sp_esc_int($user_id_list[$x]);
			$success = spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$user_id AND usergroup_id=$usergroup_id");

			if ($new_usergroup_id != -1) $success = sp_add_membership($new_usergroup_id, $user_id);

			# reset auths and memberships for added user
			sp_reset_memberships($user_id);
			sp_reset_auths($user_id);

			# update mod flag
			sp_update_member_moderator_flag($user_id);
		}
	}
}

# rebuiuld the new user list just in case
sp_rebuild_newuser();

die();

?>