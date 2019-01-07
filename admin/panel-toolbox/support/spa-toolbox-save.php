<?php
/*
Simple:Press
Admin Toolbox Update Options Support Functions
$LastChangedDate: 2018-11-02 12:30:34 -0500 (Fri, 02 Nov 2018) $
$Rev: 15793 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_toolbox_data() {
	check_admin_referer('forum-adminform_toolbox', 'forum-adminform_toolbox');

	$mess = SP()->primitives->admin_text('Options Updated');

	# build number update
	if (empty($_POST['sfbuild']) || (int) $_POST['sfbuild'] == 0) {
		SP()->options->update('sfbuild', SPBUILD);
	} else {
		if ((int) $_POST['sfbuild'] != SPBUILD && isset($_POST['sfforceupgrade'])) SP()->options->update('sfbuild', SP()->filters->integer($_POST['sfbuild']));
	}

	SP()->options->update('sfforceupgrade', isset($_POST['sfforceupgrade']));

	do_action('sph_toolbox_save');

	return $mess;
}

function spa_save_toolbox_clearlog() {
	check_admin_referer('forum-adminform_clearlog', 'forum-adminform_clearlog');
	$mess = SP()->primitives->admin_text('Log Emptied');

	# Clear out the error log table
	SP()->DB->truncate(SPERRORLOG);

	do_action('sph_toolbox_log_clear');

	return $mess;
}

function spa_save_uninstall_data() {
	check_admin_referer('forum-adminform_uninstall', 'forum-adminform_uninstall');
	$mess = SP()->primitives->admin_text('Options Updated');

	# Are we setting the uninstall flag?
	spa_update_check_option('sfuninstall');
	if (isset($_POST['sfuninstall'])) $mess = SP()->primitives->admin_text('Simple:Press database entries will be removed when de-activated');

	# Are we setting the remove storage locations flag?
	spa_update_check_option('removestorage');
	if (isset($_POST['removestorage'])) $mess = SP()->primitives->admin_text('Simple:Press storage locations will be removed when de-activated');

	do_action('sph_toolbox_uninstall_save');

	return $mess;
}

function spa_save_housekeeping_data() {
	check_admin_referer('forum-adminform_housekeeping', 'forum-adminform_housekeeping');

	$mess = '';
	if (isset($_POST['rebuild-fidx'])) {
		$forumid = (int) $_POST['forum_id'];
		if (is_numeric($forumid)) {
			$topics = SP()->DB->table(SPTOPICS, "forum_id=$forumid");
			if ($topics) {
				require_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';
				foreach ($topics as $topic) {
					sp_build_post_index($topic->topic_id);
				}
				# after reubuilding post indexes, rebuild the forum indexes
				sp_build_forum_index($forumid);

				do_action('sph_toolbox_housekeeping_forum_index');
				$mess = SP()->primitives->admin_text('Forum indexes rebuilt');
			} else {
				$mess = SP()->primitives->admin_text('Forum index rebuild failed - no topics in selected forum');
			}
		} else {
			$mess = SP()->primitives->admin_text('Forum index rebuild failed - no forum selected');
		}
	}

	if (isset($_POST['transient-cleanup'])) {
		require_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';
		sp_transient_cleanup();
		do_action('sph_toolbox_housekeeping_transient');
		$mess = SP()->primitives->admin_text('WP transients cleaned');
	}

	if (isset($_POST['clean-newposts'])) {
		$days = isset($_POST['sfdays']) ? max(SP()->filters->integer($_POST['sfdays']), 0) : 30;

		$list = array();
		$list['topics'] = array();
		$list['forums'] = array();
		$list['post'] = array();
		$newpostlist = serialize($list);

		$query = new stdClass;
			$query->table	= SPMEMBERS;
			$query->fields	= array('newposts');
			$query->data	= array($newpostlist);
			$query->where	= "lastvisit < DATE_SUB(CURDATE(), INTERVAL ".$days." DAY)";
		SP()->DB->update($query);

		do_action('sph_toolbox_housekeeping_newpost');
		$mess = SP()->primitives->admin_text('New posts lists cleaned');
	}

	if (isset($_POST['postcount-cleanup'])) {
		SP()->DB->execute('UPDATE '.SPMEMBERS.' SET posts = (SELECT COUNT(*) FROM '.SPPOSTS.' WHERE '.SPPOSTS.'.user_id = '.SPMEMBERS.'.user_id)');

        # force stats to update
        do_action('sph_stats_cron');

		do_action('sph_toolbox_housekeeping_postcount');
		$mess = SP()->primitives->admin_text('User post counts calculated');
	}

	if (isset($_POST['reset-tabs'])) {
		# clear out current tabs
		$tabs = SP()->meta->get('profile', 'tabs');
		SP()->meta->delete($tabs[0]['meta_id']);

		# start adding new ones
		spa_new_profile_setup();

		do_action('sph_toolbox_housekeeping_profile_tabs');
		$mess = SP()->primitives->admin_text('Profile tabs reset');
	}

	if (isset($_POST['reset-auths'])) {
		SP()->auths->reset_cache();
		do_action('sph_toolbox_housekeeping_auths');
		$mess = SP()->primitives->admin_text('Auths caches cleaned');
	}

	if (isset($_POST['reset-plugin-data'])) {
		SP()->memberData->reset_plugin_data();
		do_action('sph_toolbox_housekeeping_plugindata');
		$mess = SP()->primitives->admin_text('Users Plugin Data reset');
	}

	if (isset($_POST['reset-combinedcss'])) {
		SP()->plugin->clear_css_cache('all');
		SP()->plugin->clear_css_cache('mobile');
		SP()->plugin->clear_css_cache('tablet');

        SP()->options->delete('sp_css_concat');
        SP()->options->delete('sp_css_concat_mobile');
        SP()->options->delete('sp_css_concat_tablet');

		do_action('sph_toolbox_housekeeping_ccombined_css');
		$mess = SP()->primitives->admin_text('Combined CSS cache file removed');
	}

	if (isset($_POST['reset-combinedjs'])) {
		SP()->plugin->clear_scripts_cache('desktop');
		SP()->plugin->clear_scripts_cache('mobile');
		SP()->plugin->clear_scripts_cache('tablet');

        SP()->options->delete('sp_js_concat');
        SP()->options->delete('sp_js_concat_mobile');
        SP()->options->delete('sp_js_concat_tablet');

		do_action('sph_toolbox_housekeeping_combined_js');
		$mess = SP()->primitives->admin_text('Combined scripts cache files removed');
	}

	if (isset($_POST['flushcache'])) {
		SP()->cache->flush('all');
		do_action('sph_toolbox_housekeeping_flush_cache');
		$mess = SP()->primitives->admin_text('General cache flushed');
	}

	if (isset($_POST['flushxmlcache'])) {
		SP()->cache->flush('xml');
		do_action('sph_toolbox_housekeeping_flush_xml_cache');
		$mess = SP()->primitives->admin_text('XML API cache flushed');
	}

	do_action('sph_toolbox_housekeeping_save');

	return $mess;
}

function spa_save_inspector_data() {
	check_admin_referer('forum-adminform_inspector', 'forum-adminform_inspector');

	$mess = SP()->primitives->admin_text('Options Updated');

	$i = SP()->user->thisUser->ID;
	$ins = array();
	$ins = SP()->options->get('spInspect');

	$ins[$i]['con_pageData'] = isset($_POST['con_pageData']);
	$ins[$i]['con_forumData'] = isset($_POST['con_forumData']);
	$ins[$i]['con_thisUser'] = isset($_POST['con_thisUser']);
	$ins[$i]['con_device'] = isset($_POST['con_device']);

	$ins[$i]['gv_groups'] = isset($_POST['gv_groups']);
	$ins[$i]['gv_thisGroup'] = isset($_POST['gv_thisGroup']);
	$ins[$i]['gv_thisForum'] = isset($_POST['gv_thisForum']);
	$ins[$i]['gv_thisForumSubs'] = isset($_POST['gv_thisForumSubs']);
	$ins[$i]['q_GroupView'] = isset($_POST['q_GroupView']);
	$ins[$i]['q_GroupViewStats'] = isset($_POST['q_GroupViewStats']);

	$ins[$i]['fv_forums'] = isset($_POST['fv_forums']);
	$ins[$i]['fv_thisForum'] = isset($_POST['fv_thisForum']);
	$ins[$i]['fv_thisForumSubs'] = isset($_POST['fv_thisForumSubs']);
	$ins[$i]['fv_thisSubForum'] = isset($_POST['fv_thisSubForum']);
	$ins[$i]['fv_thisTopic'] = isset($_POST['fv_thisTopic']);
	$ins[$i]['q_ForumView'] = isset($_POST['q_ForumView']);
	$ins[$i]['q_ForumViewStats'] = isset($_POST['q_ForumViewStats']);

	$ins[$i]['tv_topics'] = isset($_POST['tv_topics']);
	$ins[$i]['tv_thisTopic'] = isset($_POST['tv_thisTopic']);
	$ins[$i]['tv_thisPost'] = isset($_POST['tv_thisPost']);
	$ins[$i]['tv_thisPostUser'] = isset($_POST['tv_thisPostUser']);
	$ins[$i]['q_TopicView'] = isset($_POST['q_TopicView']);

	$ins[$i]['mv_members'] = isset($_POST['mv_members']);
	$ins[$i]['mv_thisMemberGroup'] = isset($_POST['mv_thisMemberGroup']);
	$ins[$i]['mv_thisMember'] = isset($_POST['mv_thisMember']);
	$ins[$i]['q_MembersView'] = isset($_POST['q_MembersView']);

	$ins[$i]['tlv_listTopics'] = isset($_POST['tlv_listTopics']);
	$ins[$i]['tlv_thisListTopic'] = isset($_POST['tlv_thisListTopic']);
	$ins[$i]['q_ListTopicView'] = isset($_POST['q_ListTopicView']);
	$ins[$i]['q_ListTopicViewNew'] = isset($_POST['q_ListTopicViewNew']);
	$ins[$i]['q_ListTopicViewFirst'] = isset($_POST['q_ListTopicViewFirst']);

	$ins[$i]['plv_listPosts'] = isset($_POST['plv_listPosts']);
	$ins[$i]['plv_thisListPost'] = isset($_POST['plv_thisListPost']);
	$ins[$i]['q_ListPostView'] = isset($_POST['q_ListPostView']);

	$ins[$i]['sv_search'] = isset($_POST['sv_search']);
	$ins[$i]['q_SearchView'] = isset($_POST['q_SearchView']);

	$ins[$i]['pro_profileUser'] = isset($_POST['pro_profileUser']);

	SP()->options->update('spInspect', $ins);

	do_action('sph_toolbox_inspector_save');

	return $mess;
}

function spa_save_cron_data() {
	check_admin_referer('forum-adminform_cron', 'forum-adminform_cron');
	$mess = '';

	# see if adding an cron
	$addTime = (!empty($_POST['add-timestamp'])) ? SP()->filters->integer($_POST['add-timestamp']) : current_time('timestamp');
	$addInterval = (!empty($_POST['add-interval'])) ? SP()->filters->str($_POST['add-interval']) : '';
	$addHook = (!empty($_POST['add-hook'])) ? SP()->filters->str($_POST['add-hook']) : '';
	$addArgs = (!empty($_POST['add-args'])) ? SP()->filters->str($_POST['add-args']) : array();
	if ($addTime != '' && $addHook != '') {
        if ($addInterval == '') {
			wp_schedule_single_event($addTime, $addHook, (array) $addArgs);
		} else {
			wp_schedule_event($addTime, $addInterval, $addHook, $addArgs);
		}
		$mess.= SP()->primitives->admin_text('Cron added');
	}

	# see if deleting an cron
	$delTime = (!empty($_POST['del-timestamp'])) ? SP()->filters->integer($_POST['del-timestamp']) : '';
	$delHook = (!empty($_POST['del-hook'])) ? SP()->filters->str($_POST['del-hook']) : '';
	$delArgs = (!empty($_POST['del-args'])) ? SP()->filters->str($_POST['del-args']) : array();
	if ($delTime != '' && $delHook != '') {
		wp_unschedule_event($delTime, $delHook, $delArgs);
		$mess.= SP()->primitives->admin_text('Cron deleted');
	}

	# see if running a cron
	$runHook = (!empty($_POST['run-hook'])) ? SP()->filters->str($_POST['run-hook']) : '';
	if ($runHook != '') {
		do_action(trim($runHook));
		$mess.= SP()->primitives->admin_text('Cron run');
	}

	if (empty($mess)) $mess = SP()->primitives->admin_text('No CRON updates');
	return $mess;
}
