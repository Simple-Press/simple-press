<?php
/*
Simple:Press
Admin Toolbox Support Functions
$LastChangedDate: 2015-02-22 05:20:39 -0600 (Sun, 22 Feb 2015) $
$Rev: 12501 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_toolbox_data() {
	$sfoptions = array();

	$sfoptions['sfforceupgrade'] = sp_get_option('sfforceupgrade');

	if (sp_get_option('sfbuild') == SPBUILD) $sfoptions['sfforceupgrade'] = 0;

	return $sfoptions;
}

function spa_get_log_data() {
	$sflog = array();

	$sql = '
		SELECT install_date, release_type, version, build, display_name
		FROM '.SFLOG.'
		JOIN '.SFMEMBERS.' ON '.SFLOG.'.user_id='.SFMEMBERS.'.user_id
		ORDER BY id DESC;';

	$sflog = spdb_select('set', $sql, ARRAY_A);

	return $sflog;
}

function spa_get_errorlog_data() {
	$sflog = spdb_table(SFERRORLOG, '', '', 'id DESC', '', ARRAY_A);
	return $sflog;
}

function spa_get_uninstall_data() {
	$sfoptions = array();
	$sfoptions['sfuninstall'] = sp_get_option('sfuninstall');
	$sfoptions['removestorage'] = sp_get_option('removestorage');
	return $sfoptions;
}

function spa_get_inspector_data() {
	global $spThisUser;
	$ins = array();
	$ins = sp_get_option('spInspect');
	$i = $spThisUser->ID;
	if (empty($ins) || !array_key_exists($i, $ins)) {
		$ins[$i] = array('con_spVars' => 0,
						 'con_spGlobals' => 0,
						 'con_spThisUser' => 0,
						 'con_spDevice' => 0,
						 'gv_spGroupView' => 0,
						 'gv_spThisGroup' => 0,
						 'gv_spThisForum' => 0,
						 'gv_spThisForumSubs' => 0,
						 'fv_spForumView' => 0,
						 'fv_spThisForum' => 0,
						 'fv_spThisForumSubs' => 0,
						 'fv_spThisSubForum' => 0,
						 'fv_spThisTopic' => 0,
						 'tv_spTopicView' => 0,
						 'tv_spThisTopic' => 0,
						 'tv_spThisPost' => 0,
						 'tv_spThisPostUser' => 0,
						 'mv_spMembersList' => 0,
						 'mv_spThisMemberGroup' => 0,
						 'mv_spThisMember' => 0,
						 'tlv_spTopicListView' => 0,
						 'tlv_spThisListTopic' => 0,
						 'plv_spPostListView' => 0,
						 'plv_spThisListPost' => 0,
						 'pro_spProfileUser' => 0,
						 'q_spGroupView' => 0,
						 'q_spGroupViewStats' => 0,
						 'q_spForumView' => 0,
						 'q_spForumViewStats' => 0,
						 'q_spTopicView' => 0,
						 'q_spMembersView' => 0,
						 'q_spTopicListView' => 0,
						 'q_spTopicListViewNew' => 0,
						 'q_spTopicListViewFirst' => 0,
						 'q_spPostListView' => 0,
						 'q_spSearchView' => 0
					    );
	}
	return $ins[$i];
}

function spa_get_cron_data() {
    $data = new stdClass();
    $data->cron = _get_cron_array();
	foreach ($data->cron as $time => $hooks) {
		foreach ($hooks as $hook => $items) {
			foreach ($items as $key => $item) {
				$data->cron[$time][$hook][$key]['date'] = date_i18n(SFDATES, $time).' - '.date_i18n(SFTIMES, $time);
			}
		}
	}

    $data->schedules = wp_get_schedules();

    return $data;
}

?>