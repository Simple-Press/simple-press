<?php
/*
Simple:Press
User Group Specials
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('usergroups')) die();

# Check Whether User Can Manage User Groups
if (!SP()->auths->current_user_can('SPF Manage User Groups')) die();

include_once SP_PLUGIN_DIR.'/admin/panel-usergroups/support/spa-usergroups-prepare.php';

if(isset($_GET['ug_no'])) {
    spa_members_not_belonging_to_any_usergroup(
        array_key_exists('page', $_GET) ? (int) $_GET['page'] : 0,
        array_key_exists('filter', $_GET) ? $_GET['filter'] : null
    );
    die();
}

if (isset($_GET['ug'])) {
	$usergroup_id = SP()->filters->integer($_GET['ug']);
	if ($usergroup_id == 0) {
		$sql = 'SELECT '.SPMEMBERS.'.user_id, display_name
				FROM '.SPMEMBERS.'
				LEFT JOIN '.SPMEMBERSHIPS.' ON '.SPMEMBERS.'.user_id = '.SPMEMBERSHIPS.'.user_id
				WHERE '.SPMEMBERSHIPS.'.usergroup_id IS NULL AND admin=0
				ORDER BY display_name;';
		$members = SP()->DB->select($sql);
		$text1 = SP()->primitives->admin_text('Members With No Memberships');
		$text2 = SP()->primitives->admin_text('All members have a usergroup membership.');
	} else {
		$sql = 'SELECT '.SPMEMBERSHIPS.'.user_id, display_name
				FROM '.SPMEMBERSHIPS.'
				JOIN '.SPMEMBERS.' ON '.SPMEMBERS.'.user_id = '.SPMEMBERSHIPS.'.user_id
				WHERE '.SPMEMBERSHIPS.".usergroup_id=$usergroup_id
				ORDER BY display_name";
		$members = SP()->DB->select($sql);
		$text1 = SP()->primitives->admin_text('User Group Members');
		$text2 = SP()->primitives->admin_text('No Members in this User Group.');
	}
	echo spa_display_member_roll($members, $text1, $text2);
	die();
}

function spa_display_member_roll($members, $text1, $text2) {
	$out = '';
	$cap = '';
	$first = true;
	$out.= '<div class="sf-form">';
	$out.= '<h4>'.$text1.'</h4>';
	if ($members) {
		$out.= '<p><b>'.count($members).' '.SP()->primitives->admin_text('member(s) in this user group').'</b></p>';
		for ($x = 0; $x < count($members); $x++) {
			if (strncasecmp($members[$x]->display_name, $cap, 1) != 0) {
				if (!$first) $out.= '</ul>';

				$cap = substr($members[$x]->display_name, 0, 2);
				if (function_exists('mb_strwidth')) {
					if (mb_strwidth($cap) == 2) $cap = substr($cap, 0, 1);
				} else {
					$cap = substr($cap, 0, 1);
				}

				$out.= '<p style="clear:both;"></p><hr /><h4>'.strtoupper($cap).'</h4>';
				$out.= '<ul class="memberlist">';
				$first = false;
			}
			$out.= '<li>'.SP()->displayFilters->name($members[$x]->display_name).'</li>';
		}
		$out.= '</ul>';
	} else {
		$out.= $text2;
	}
	$out.= '</div>';

	return $out;
}
