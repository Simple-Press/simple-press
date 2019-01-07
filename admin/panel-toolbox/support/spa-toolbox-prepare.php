<?php
/*
Simple:Press
Admin Toolbox Support Functions
$LastChangedDate: 2018-01-21 05:57:59 -0600 (Sun, 21 Jan 2018) $
$Rev: 15635 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_toolbox_data() {
	$sfoptions = array();

	$sfoptions['sfforceupgrade'] = SP()->options->get('sfforceupgrade');

	if (SP()->options->get('sfbuild') == SPBUILD) $sfoptions['sfforceupgrade'] = 0;

	return $sfoptions;
}

function spa_get_log_data() {
	$sflog = array();

	$sql = 'SELECT install_date, release_type, version, build, display_name
			FROM '.SPLOG.'
			JOIN '.SPMEMBERS.' ON '.SPLOG.'.user_id='.SPMEMBERS.'.user_id
			ORDER BY id DESC;';
	$sflog = SP()->DB->select($sql, 'set', ARRAY_A);
	return $sflog;
}

function spa_get_errorlog_data() {
	$sflog = SP()->DB->table(SPERRORLOG, '', '', 'id DESC', '', ARRAY_A);
	return $sflog;
}

function spa_get_uninstall_data() {
	$sfoptions = array();
	$sfoptions['sfuninstall'] = SP()->options->get('sfuninstall');
	$sfoptions['removestorage'] = SP()->options->get('removestorage');
	return $sfoptions;
}

function spa_get_inspector_data() {
	$ins = array();
	$ins = SP()->options->get('spInspect');
	$i = SP()->user->thisUser->ID;
	if (empty($ins) || !array_key_exists($i, $ins)) {
		$ins[$i] = array('con_pageData' => 0,
						 'con_forumData' => 0,
						 'con_thisUser' => 0,
						 'con_device' => 0,
						 'gv_groups' => 0,
						 'gv_thisGroup' => 0,
						 'gv_thisForum' => 0,
						 'gv_thisForumSubs' => 0,
						 'fv_forums' => 0,
						 'fv_thisForum' => 0,
						 'fv_thisForumSubs' => 0,
						 'fv_thisSubForum' => 0,
						 'fv_thisTopic' => 0,
						 'tv_topics' => 0,
						 'tv_thisTopic' => 0,
						 'tv_thisPost' => 0,
						 'tv_thisPostUser' => 0,
						 'mv_members' => 0,
						 'mv_thisMemberGroup' => 0,
						 'mv_thisMember' => 0,
						 'tlv_listTopics' => 0,
						 'tlv_thisListTopic' => 0,
						 'plv_listPosts' => 0,
						 'plv_thisListPost' => 0,
						 'pro_profileUser' => 0,
						 'q_GroupView' => 0,
						 'q_GroupViewStats' => 0,
						 'q_ForumView' => 0,
						 'q_ForumViewStats' => 0,
						 'q_TopicView' => 0,
						 'q_MembersView' => 0,
						 'q_ListTopicView' => 0,
						 'q_ListTopicViewNew' => 0,
						 'q_ListTopicViewFirst' => 0,
						 'q_ListPostView' => 0,
						 'sv_search' => 0,
						 'q_SearchView' => 0
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
				$data->cron[$time][$hook][$key]['date'] = date_i18n(SPDATES, $time).' - '.date_i18n(SPTIMES, $time);
			}
		}
	}

    $data->schedules = wp_get_schedules();

    return $data;
}
