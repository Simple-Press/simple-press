<?php
/*
Simple:Press
Cron - global code
$LastChangedDate: 2017-05-20 17:44:46 -0500 (Sat, 20 May 2017) $
$Rev: 15386 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE - This file loads at core level - all page loads
#	SP Cron Functions
#
# ==========================================================================================

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

	$sfuser = sp_get_option('sfuserremoval');
	if ($sfuser['sfuserremove'] && !wp_next_scheduled('sph_cron_user')) {
		wp_schedule_event(time(), 'daily', 'sph_cron_user');
    } else {
    	wp_clear_scheduled_hook('sph_cron_user');
    }

    do_action('sph_stats_scheduler');
}

function sp_cron_schedules($schedules) {
    $schedules['sp_stats_interval'] = array('interval' => sp_get_option('sp_stats_interval'), 'display' => __('SP Stats Interval'));
    $schedules['sp_news_interval'] = array('interval' => (60 * 60 * 24 * 7), 'display' => __('SP News Check Interval')); # weekly
    return $schedules;
}

function sp_cron_remove_users() {
	require_once ABSPATH.'wp-admin/includes/user.php';

	# make sure auto removal is enabled
	$sfuser = sp_get_option('sfuserremoval');
	if ($sfuser['sfuserremove']) {
		# see if removing users with no posts
		if ($sfuser['sfusernoposts']) {
			$users = spdb_select('set', 'SELECT '.SFUSERS.'.ID FROM '.SFUSERS.'
										JOIN '.SFMEMBERS.' on '.SFUSERS.'.ID = '.SFMEMBERS.'.user_id
										LEFT JOIN '.SFWPPOSTS.' ON '.SFUSERS.'.ID = '.SFWPPOSTS.'.post_author
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
			$users = spdb_table(SFMEMBERS, 'lastvisit < DATE_SUB(NOW(), INTERVAL '.$sfuser['sfuserperiod'].' DAY)');
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

function sp_cron_transient_cleanup() {
    include_once SF_PLUGIN_DIR.'/forum/database/sp-db-management.php';
	sp_transient_cleanup();
	do_action('sph_transient_cleanup');
}

function sp_cron_generate_stats() {
	$counts = sp_get_stats_counts();
	sp_update_option('spForumStats', $counts);

	$stats = sp_get_membership_stats();
	sp_update_option('spMembershipStats', $stats);

	$spControls = sp_get_option('sfcontrols');
	$topPosters = sp_get_top_poster_stats((int) $spControls['showtopcount']);
	sp_update_option('spPosterStats', $topPosters);

	$mods = sp_get_moderator_stats();
	sp_update_option('spModStats', $mods);

	$admins = sp_get_admin_stats();
	sp_update_option('spAdminStats', $admins);

	do_action('sph_stats_cron_run');
}

function sp_cron_check_news() {
    $url = 'https://simple-press.com/downloads/simple-press/simple-press-news.xml';
	$response = wp_remote_get($url, array('timeout' => 5));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) return;
    $body = wp_remote_retrieve_body($response);
    if (!$body) return;
	$newNews = new SimpleXMLElement($body);
	if ($newNews) {
        $data = sp_get_sfmeta('news', 'news');
    	$cur_id = (!empty($data[0]['meta_value'])) ? $data[0]['meta_value']['id'] : -999;
        if ($newNews->news->id != $cur_id) {
            $curNews = array();
            $curNews['id'] = (string) $newNews->news->id;
            $curNews['show'] = 1;
            $curNews['news'] = addslashes_gpc((string) $newNews->news[0]->message);
    		sp_add_sfmeta('news', 'news', $curNews, 0);
        }
	}
}
?>