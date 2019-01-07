<?php
/*
Simple:Press
Admin Toolbox Update Options Support Functions
$LastChangedDate: 2016-10-23 12:01:49 -0500 (Sun, 23 Oct 2016) $
$Rev: 14664 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_toolbox_data() {
	check_admin_referer('forum-adminform_toolbox', 'forum-adminform_toolbox');

	$mess = spa_text('Options Updated');

	# build number update
	if (empty($_POST['sfbuild']) || $_POST['sfbuild'] == 0) {
		sp_update_option('sfbuild', SPBUILD);
	} else {
		if ($_POST['sfbuild'] != SPBUILD && isset($_POST['sfforceupgrade'])) sp_update_option('sfbuild', sp_esc_int($_POST['sfbuild']));
	}

	sp_update_option('sfforceupgrade', isset($_POST['sfforceupgrade']));

	do_action('sph_toolbox_save');

	return $mess;
}

function spa_save_toolbox_clearlog() {
	check_admin_referer('forum-adminform_clearlog', 'forum-adminform_clearlog');
	$mess = spa_text('Log Emptied');

	# Clear out the error log table
	spdb_query('TRUNCATE TABLE '.SFERRORLOG);

	do_action('sph_toolbox_log_clear');

	return $mess;
}

function spa_save_uninstall_data() {
	check_admin_referer('forum-adminform_uninstall', 'forum-adminform_uninstall');
	$mess = spa_text('Options Updated');

	# Are we setting the uninstall flag?
	spa_update_check_option('sfuninstall');
	if (isset($_POST['sfuninstall'])) $mess = spa_text('Simple:Press database entries will be removed when de-activated');

	# Are we setting the remove storage locations flag?
	spa_update_check_option('removestorage');
	if (isset($_POST['removestorage'])) $mess = spa_text('Simple:Press storage locations will be removed when de-activated');

	do_action('sph_toolbox_uninstall_save');

	return $mess;
}

function spa_save_housekeeping_data() {
	check_admin_referer('forum-adminform_housekeeping', 'forum-adminform_housekeeping');

	$mess = '';
	if (isset($_POST['rebuild-fidx'])) {
		$forumid = $_POST['forum_id'];
		if (is_numeric($forumid)) {
			$topics = spdb_table(SFTOPICS, "forum_id=$forumid");
			if ($topics) {
				include_once SF_PLUGIN_DIR.'/forum/database/sp-db-management.php';
				foreach ($topics as $topic) {
					sp_build_post_index($topic->topic_id);
				}
				# after reubuilding post indexes, rebuild the forum indexes
				sp_build_forum_index($forumid);

				do_action('sph_toolbox_housekeeping_forum_index');
				$mess = spa_text('Forum indexes rebuilt');
			} else {
				$mess = spa_text('Forum index rebuild failed - no topics in selected forum');
			}
		} else {
			$mess = spa_text('Forum index rebuild failed - no forum selected');
		}
	}

	if (isset($_POST['transient-cleanup'])) {
		include_once (SF_PLUGIN_DIR.'/forum/database/sp-db-management.php');
		sp_transient_cleanup();
		do_action('sph_toolbox_housekeeping_transient');
		$mess = spa_text('WP transients cleaned');
	}

	if (isset($_POST['clean-newposts'])) {
		$days = isset($_POST['sfdays']) ? max(sp_esc_int($_POST['sfdays']), 0) : 30;

		$list = array();
		$list['topics'] = array();
		$list['forums'] = array();
		$list['post'] = array();
		$newpostlist = serialize($list);

		$spdb = new spdbComplex;
			$spdb->table	= SFMEMBERS;
			$spdb->fields	= array('newposts');
			$spdb->data		= array($newpostlist);
			$spdb->where	= "lastvisit < DATE_SUB(CURDATE(), INTERVAL ".$days." DAY)";
		$spdb->update();

		do_action('sph_toolbox_housekeeping_newpost');
		$mess = spa_text('New posts lists cleaned');
	}

	if (isset($_POST['postcount-cleanup'])) {
		spdb_query('UPDATE '.SFMEMBERS.' SET posts = (SELECT COUNT(*) FROM '.SFPOSTS.' WHERE '.SFPOSTS.'.user_id = '.SFMEMBERS.'.user_id)');

        # force stats to update
        do_action('sph_stats_cron');

		do_action('sph_toolbox_housekeeping_postcount');
		$mess = spa_text('User post counts calculated');
	}

	if (isset($_POST['reset-tabs'])) {
		# clear out current tabs
		$tabs = sp_get_sfmeta('profile', 'tabs');
		sp_delete_sfmeta($tabs[0]['meta_id']);

		# start adding new ones
		spa_new_profile_setup();

		do_action('sph_toolbox_housekeeping_profile_tabs');
		$mess = spa_text('Profile tabs reset');
	}

	if (isset($_POST['reset-auths'])) {
		sp_reset_auths();
		do_action('sph_toolbox_housekeeping_auths');
		$mess = spa_text('Auths caches cleaned');
	}

	if (isset($_POST['reset-plugin-data'])) {
		sp_reset_member_plugindata();
		do_action('sph_toolbox_housekeeping_plugindata');
		$mess = spa_text('Users Plugin Data reset');
	}

	if (isset($_POST['reset-combinedcss'])) {
		sp_clear_combined_css('all');
		sp_clear_combined_css('mobile');
		sp_clear_combined_css('tablet');

        sp_delete_option('sp_css_concat');
        sp_delete_option('sp_css_concat_mobile');
        sp_delete_option('sp_css_concat_tablet');

		do_action('sph_toolbox_housekeeping_ccombined_css');
		$mess = spa_text('Combined CSS cache file removed');
	}

	if (isset($_POST['reset-combinedjs'])) {
		sp_clear_combined_scripts('desktop');
		sp_clear_combined_scripts('mobile');
		sp_clear_combined_scripts('tablet');

        sp_delete_option('sp_js_concat');
        sp_delete_option('sp_js_concat_mobile');
        sp_delete_option('sp_js_concat_tablet');

		do_action('sph_toolbox_housekeeping_combined_js');
		$mess = spa_text('Combined scripts cache files removed');
	}

	if (isset($_POST['flushcache'])) {
		sp_flush_cache('all');
		do_action('sph_toolbox_housekeeping_flush_cache');
		$mess = spa_text('General cache flushed');
	}

	if (isset($_POST['flushxmlcache'])) {
		sp_flush_cache('xml');
		do_action('sph_toolbox_housekeeping_flush_xml_cache');
		$mess = spa_text('XML API cache flushed');
	}

	do_action('sph_toolbox_housekeeping_save');

	return $mess;
}

function spa_save_inspector_data() {
	global $spThisUser;
	check_admin_referer('forum-adminform_inspector', 'forum-adminform_inspector');

	$mess = spa_text('Options Updated');

	$i = $spThisUser->ID;
	$ins = array();
	$ins = sp_get_option('spInspect');

	$ins[$i]['con_spVars'] = isset($_POST['con_spVars']);
	$ins[$i]['con_spGlobals'] = isset($_POST['con_spGlobals']);
	$ins[$i]['con_spThisUser'] = isset($_POST['con_spThisUser']);
	$ins[$i]['con_spDevice'] = isset($_POST['con_spDevice']);

	$ins[$i]['gv_spGroupView'] = isset($_POST['gv_spGroupView']);
	$ins[$i]['gv_spThisGroup'] = isset($_POST['gv_spThisGroup']);
	$ins[$i]['gv_spThisForum'] = isset($_POST['gv_spThisForum']);
	$ins[$i]['gv_spThisForumSubs'] = isset($_POST['gv_spThisForumSubs']);
	$ins[$i]['q_spGroupView'] = isset($_POST['q_spGroupView']);
	$ins[$i]['q_spGroupViewStats'] = isset($_POST['q_spGroupViewStats']);

	$ins[$i]['fv_spForumView'] = isset($_POST['fv_spForumView']);
	$ins[$i]['fv_spThisForum'] = isset($_POST['fv_spThisForum']);
	$ins[$i]['fv_spThisForumSubs'] = isset($_POST['fv_spThisForumSubs']);
	$ins[$i]['fv_spThisSubForum'] = isset($_POST['fv_spThisSubForum']);
	$ins[$i]['fv_spThisTopic'] = isset($_POST['fv_spThisTopic']);
	$ins[$i]['q_spForumView'] = isset($_POST['q_spForumView']);
	$ins[$i]['q_spForumViewStats'] = isset($_POST['q_spForumViewStats']);

	$ins[$i]['tv_spTopicView'] = isset($_POST['tv_spTopicView']);
	$ins[$i]['tv_spThisTopic'] = isset($_POST['tv_spThisTopic']);
	$ins[$i]['tv_spThisPost'] = isset($_POST['tv_spThisPost']);
	$ins[$i]['tv_spThisPostUser'] = isset($_POST['tv_spThisPostUser']);
	$ins[$i]['q_spTopicView'] = isset($_POST['q_spTopicView']);

	$ins[$i]['mv_spMembersList'] = isset($_POST['mv_spMembersList']);
	$ins[$i]['mv_spThisMemberGroup'] = isset($_POST['mv_spThisMemberGroup']);
	$ins[$i]['mv_spThisMember'] = isset($_POST['mv_spThisMember']);
	$ins[$i]['q_spMembersView'] = isset($_POST['q_spMembersView']);

	$ins[$i]['tlv_spTopicListView'] = isset($_POST['tlv_spTopicListView']);
	$ins[$i]['tlv_spThisListTopic'] = isset($_POST['tlv_spThisListTopic']);
	$ins[$i]['q_spTopicListView'] = isset($_POST['q_spTopicListView']);
	$ins[$i]['q_spTopicListViewNew'] = isset($_POST['q_spTopicListViewNew']);
	$ins[$i]['q_spTopicListViewFirst'] = isset($_POST['q_spTopicListViewFirst']);

	$ins[$i]['plv_spPostListView'] = isset($_POST['plv_spPostListView']);
	$ins[$i]['plv_spThisListPost'] = isset($_POST['plv_spThisListPost']);
	$ins[$i]['q_spPostListView'] = isset($_POST['q_spPostListView']);

	$ins[$i]['q_spSearchView'] = isset($_POST['q_spSearchView']);

	$ins[$i]['pro_spProfileUser'] = isset($_POST['pro_spProfileUser']);

	sp_update_option('spInspect', $ins);

	do_action('sph_toolbox_inspector_save');

	return $mess;
}

function spa_save_cron_data() {
	check_admin_referer('forum-adminform_cron', 'forum-adminform_cron');
	$mess = '';

	# see if adding an cron
	$addTime = (!empty($_POST['add-timestamp'])) ? sp_esc_int($_POST['add-timestamp']) : current_time('timestamp');
	$addInterval = (!empty($_POST['add-interval'])) ? sp_esc_str($_POST['add-interval']) : '';
	$addHook = (!empty($_POST['add-hook'])) ? sp_esc_str($_POST['add-hook']) : '';
	$addArgs = (!empty($_POST['add-args'])) ? sp_esc_str($_POST['add-args']) : array();
	if ($addTime != '' && $addHook != '') {
        if ($addInterval == '') {
			wp_schedule_single_event($addTime, $addHook, (array) $addArgs);
		} else {
			wp_schedule_event($addTime, $addInterval, $addHook, $addArgs);
		}
		$mess.= spa_text('Cron added');
	}

	# see if deleting an cron
	$delTime = (!empty($_POST['del-timestamp'])) ? sp_esc_int($_POST['del-timestamp']) : '';
	$delHook = (!empty($_POST['del-hook'])) ? sp_esc_str($_POST['del-hook']) : '';
	$delArgs = (!empty($_POST['del-args'])) ? sp_esc_str($_POST['del-args']) : array();
	if ($delTime != '' && $delHook != '') {
		wp_unschedule_event($delTime, $delHook, $delArgs);
		$mess.= spa_text('Cron deleted');
	}

	# see if running a cron
	$runHook = (!empty($_POST['run-hook'])) ? sp_esc_str($_POST['run-hook']) : '';
	if ($runHook != '') {
		do_action(trim($runHook));
		$mess.= spa_text('Cron run');
	}

	if (empty($mess)) $mess = spa_text('No CRON updates');
	return $mess;
}
?>