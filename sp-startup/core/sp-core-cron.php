<?php
/**
 * Cron Functions
 * This file loads at core level - all page loads for admin and front
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * This function ensures our cron events are scheduled
 * Hooked into wp cron scheduler
 *
 * @since 6.0
 *
 * @return void
 */
function sp_cron_scheduler() {
	# make sure our core crons are schedule
	if (!wp_next_scheduled('sph_transient_cleanup_cron')) {
		wp_schedule_event(time(), 'daily', 'sph_transient_cleanup_cron');
	}

	if (!wp_next_scheduled('sph_news_cron')) {
		wp_schedule_event(time(), 'sp_news_interval', 'sph_news_cron');
	}

	if (!wp_next_scheduled('sph_stats_cron')) {
		wp_schedule_event(time(), 'sp_stats_interval', 'sph_stats_cron');
	}
	
	if (!wp_next_scheduled('sph_check_addons_status_interval')) {
		wp_schedule_event(time(), 'ten_minutes', 'sph_check_addons_status_interval');
	}

	$sfuser = SP()->options->get('sfuserremoval');
	if ($sfuser['sfuserremove'] && !wp_next_scheduled('sph_cron_user')) {
		wp_schedule_event(time(), 'daily', 'sph_cron_user');
	} else {
		wp_clear_scheduled_hook('sph_cron_user');
	}

	do_action('sph_stats_scheduler');
}

/**
 * This function adds additional cron schedules to the WP cron schedule list.
 *
 * @since 6.0
 *
 * @param $array	$schedules	current wp cron schedules
 *
 * @return array	cron schedules
 */
function sp_cron_schedules($schedules) {
	$schedules['sp_stats_interval'] = array(
		'interval'	 => SP()->options->get('sp_stats_interval'),
		'display'	 => __('SP Stats Interval'));
	$schedules['sp_news_interval'] = array(
		'interval'	 => (60 * 60 * 24 * 7),
		'display'	 => __('SP News Check Interval')); # weekly
		
	$schedules['sph_check_addons_status_interval'] = array(
		'interval'	 => (60 * 60 * 24),
		'display'	 => __('SP Addons Interval')); # daily
		
	return $schedules;
}

/**
 * This function runs by wp cron and removes any inactive users based on options
 *
 * @since 6.0
 *
 * @return void
 */
function sp_cron_remove_users() {
	require_once ABSPATH.'wp-admin/includes/user.php';

	# make sure auto removal is enabled
	$sfuser = SP()->options->get('sfuserremoval');
	if ($sfuser['sfuserremove']) {
		# see if removing users with no posts
		if ($sfuser['sfusernoposts']) {
			$users = SP()->DB->select('SELECT '.SPUSERS.'.ID FROM '.SPUSERS.'
										JOIN '.SPMEMBERS.' on '.SPUSERS.'.ID = '.SPMEMBERS.'.user_id
										LEFT JOIN '.SPWPPOSTS.' ON '.SPUSERS.'.ID = '.SPWPPOSTS.'.post_author
										WHERE user_registered < DATE_SUB(NOW(), INTERVAL '.$sfuser['sfuserperiod'].' DAY)
										AND post_author IS NULL
										AND posts < 1');

			if ($users) {
				foreach ($users as $user) {
					$userdata = get_userdata($user->ID);
					if (!in_array('administrator', $userdata->roles)) wp_delete_user($user->ID);
				}
			}
		}

		# see if removing inactive users
		if ($sfuser['sfuserinactive']) {
			$users = SP()->DB->table(SPMEMBERS, 'lastvisit < DATE_SUB(NOW(), INTERVAL '.$sfuser['sfuserperiod'].' DAY)');
			if ($users) {
				foreach ($users as $user) {
					$userdata = get_userdata($user->user_id);
					if (!in_array('administrator', $userdata->roles)) wp_delete_user($user->user_id);
				}
			}
		}
	} else {
		wp_clear_scheduled_hook('sph_cron_user');
	}

	do_action('sph_remove_users_cron');
}

/**
 * This function runs by WP cron and cleans up our expired transients.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_cron_transient_cleanup() {
	require_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';
	sp_transient_cleanup();

	do_action('sph_transient_cleanup');
}

/**
 * This function runs by WP cron and updates the forum statistics.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_cron_generate_stats() {
	require_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';

	$counts = sp_get_stats_counts();
	SP()->options->update('spForumStats', $counts);

	$stats = sp_get_membership_stats();
	SP()->options->update('spMembershipStats', $stats);

	$spControls = SP()->options->get('sfcontrols');
	$topPosters = sp_get_top_poster_stats((int) $spControls['showtopcount']);
	SP()->options->update('spPosterStats', $topPosters);

	$mods = sp_get_moderator_stats();
	SP()->options->update('spModStats', $mods);

	$admins = sp_get_admin_stats();
	SP()->options->update('spAdminStats', $admins);

	do_action('sph_stats_cron_run');
}

/**
 * This function runs by WP cron job to see if there are any new Simple Press news items.
 *
 * @since 6.0
 *
 * @return void
 */
function sp_cron_check_news() {
	$url = 'https://simple-press.com/downloads/simple-press/simple-press-news.xml';
	$response = wp_remote_get($url, array(
		'timeout' => 5));
	if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) return;
	$body = wp_remote_retrieve_body($response);
	if (!$body) return;
	$newNews = new SimpleXMLElement($body);
	if ($newNews) {
		$data = SP()->meta->get('news', 'news');
		$cur_id = (!empty($data[0]['meta_value'])) ? $data[0]['meta_value']['id'] : -999;
		if ($newNews->news->id != $cur_id) {
			$curNews = array();
			$curNews['id'] = (string) $newNews->news->id;
			$curNews['show'] = 1;
			$curNews['news'] = addslashes_gpc((string) $newNews->news[0]->message);
			SP()->meta->add('news', 'news', $curNews);
		}
	}
}
