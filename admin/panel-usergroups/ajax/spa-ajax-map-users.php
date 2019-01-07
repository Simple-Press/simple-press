<?php
/*
Simple:Press
User Group Specials
$LastChangedDate: 2013-03-02 17:15:32 +0000 (Sat, 02 Mar 2013) $
$Rev: 9944 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('usermapping')) die();

check_admin_referer('forum-adminform_mapusers', 'forum-adminform_mapusers');

global $wp_roles;

$startSQL = SP()->filters->integer($_GET['startNum']);
$batchSQL = SP()->filters->integer($_GET['batchNum']);

$where = ' WHERE admin=0';
if ($_GET['ignoremods']) $where.= ' AND moderator=0';

$users = SP()->DB->select('SELECT user_id FROM '.SPMEMBERS.$where.' ORDER BY user_id LIMIT '.$startSQL.', '.$batchSQL, 'col');

if ($users) {
	$value = SP()->meta->get('default usergroup', 'sfmembers');
	$defaultUG = $value[0]['meta_value'];
	foreach ($users as $thisUser) {
		if ((int) $_GET['mapoption'] == 2) SP()->DB->execute('DELETE FROM '.SPMEMBERSHIPS.' WHERE user_id='.$thisUser);
		$user = new WP_User($thisUser);
		if (!empty($user->roles ) && is_array($user->roles)) {
			foreach ($user->roles as $role) {
				$value = SP()->meta->get('default usergroup', $role);
				if (!empty($value)) {
					$ug = $value[0]['meta_value'];
				} else {
					$ug = $defaultUG;
				}
				SP()->user->add_membership($ug, $thisUser);
			}
		}
	}

	# clean up
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();
}

die();
