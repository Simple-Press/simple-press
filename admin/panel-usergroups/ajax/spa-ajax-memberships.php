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

$action = SP()->filters->str($_GET['targetaction']);
$startNum = SP()->filters->integer($_GET['startNum']);
$batchNum = SP()->filters->integer($_GET['batchNum']);

if ($action == 'add') {
	//check_admin_referer('forum-adminform_membernew', 'forum-adminform_membernew');
	# add the users to the user group membership
	$usergroup_id = SP()->filters->integer($_GET['usergroup_id']);
	if (isset($_GET['amid'])) $user_id_list = array_map('intval', array_unique($_GET['amid']));

	if (isset($user_id_list)) {
		for ($x = $startNum; $x < ($startNum + $batchNum); $x++) {
			if (isset($user_id_list[$x])) {
				SP()->user->add_membership($usergroup_id, $user_id_list[$x]);
			}
		}
	}
}

if ($action == 'del') {
    //check_admin_referer('forum-adminform_memberdel', 'forum-adminform_memberdel');

    $usergroup_id = SP()->filters->integer($_GET['usergroupid']);
    $new_usergroup_id = SP()->filters->integer($_GET['usergroup_id']);
    if (isset($_GET['dmid'])) $user_id_list = array_map('intval', array_unique($_GET['dmid']));

	# make sure not moving to same user group
	if (!isset($user_id_list) || $usergroup_id == $new_usergroup_id) {
		die();
	}

	for ($x = $startNum; $x < ($startNum + $batchNum); $x++) {
		if (isset($user_id_list[$x])) {
			$user_id = $user_id_list[$x];
			$success = SP()->DB->execute('DELETE FROM '.SPMEMBERSHIPS." WHERE user_id=$user_id AND usergroup_id=$usergroup_id");

			if ($new_usergroup_id != -1) $success = SP()->user->add_membership($new_usergroup_id, $user_id);

			# reset auths and memberships for added user
			SP()->user->reset_memberships($user_id);
			SP()->auths->reset_cache($user_id);

			# update mod flag
			SP()->user->update_moderator_flag($user_id);
		}
	}
}

# rebuiuld the new user list just in case
SP()->user->rebuild_new();

die();
