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

$startSQL = sp_esc_int($_GET['startNum']);
$batchSQL = sp_esc_int($_GET['batchNum']);

$where = ' WHERE admin=0';
if ($_GET['ignoremods']) $where.= ' AND moderator=0';

$users = spdb_select('col', 'SELECT user_id FROM '.SFMEMBERS.$where.' ORDER BY user_id LIMIT '.$startSQL.', '.$batchSQL);

if ($users) {
	$value = sp_get_sfmeta('default usergroup', 'sfmembers');
	$defaultUG = $value[0]['meta_value'];
	foreach ($users as $thisUser) {
		if ($_GET['mapoption'] == 2) spdb_query('DELETE FROM '.SFMEMBERSHIPS.' WHERE user_id='.$thisUser);
		$user = new WP_User($thisUser);
		if (!empty($user->roles ) && is_array($user->roles)) {
			foreach ($user->roles as $role) {
				$value = sp_get_sfmeta('default usergroup', $role);
				if (!empty($value)) {
					$ug = $value[0]['meta_value'];
				} else {
					$ug = $defaultUG;
				}
				sp_add_membership($ug, $thisUser);
			}
		}
	}

	# clean up
	sp_reset_memberships();
	sp_reset_auths();
}

die();

?>