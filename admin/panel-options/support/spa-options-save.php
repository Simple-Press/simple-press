<?php
/*
Simple:Press
Admin Options Save Options Support Functions
$LastChangedDate: 2017-01-27 15:45:24 -0600 (Fri, 27 Jan 2017) $
$Rev: 15120 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_global_data()
{
	global $wp_roles;

	check_admin_referer('forum-adminform_global', 'forum-adminform_global');
	$mess = spa_text('Options updated');

	spa_update_check_option('sflockdown');

	# auto update
	$sfauto = array();
    $sfauto['sfautoupdate'] = isset($_POST['sfautoupdate']);
	$sfauto['sfautotime'] = sp_esc_int($_POST['sfautotime']);
	if (empty($sfauto['sfautotime']) || $sfauto['sfautotime'] == 0) $sfauto['sfautotime'] = 300;
	sp_update_option('sfauto', $sfauto);

	$sfrss = array();
    $sfrss['sfrsscount'] = sp_esc_int($_POST['sfrsscount']);
	$sfrss['sfrsswords'] = sp_esc_int($_POST['sfrsswords']);
    $sfrss['sfrssfeedkey'] = isset($_POST['sfrssfeedkey']);
    $sfrss['sfrsstopicname'] = isset($_POST['sfrsstopicname']);
	sp_update_option('sfrss', $sfrss);

	$sfblock = array();
    $sfblock['blockadmin'] = isset($_POST['blockadmin']);
    $sfblock['blockprofile'] = isset($_POST['blockprofile']);
	$sfblock['blockredirect'] = sp_filter_save_cleanurl($_POST['blockredirect']);
    if ($sfblock['blockadmin']) {
        $sfblock['blockroles'] = array();
		$roles = array_keys($wp_roles->role_names);
		if ($roles) {
			foreach ($roles as $index => $role) {
			    $sfblock['blockroles'][$role] = isset($_POST['role-'.$index]);
            }
            # always allow admin
            $sfblock['blockroles']['administrator'] = true;
        }
    }

	sp_update_option('sfblockadmin', $sfblock);

	sp_update_option('speditor', sp_esc_int($_POST['editor']));
	sp_update_option('editpostdays', max((int) $_POST['editpostdays'], 1));

	$spError = array();
	$spError['spErrorLogOff'] = isset($_POST['errorlog']);
	$spError['spNoticesOff'] = isset($_POST['notices']);
	sp_update_option('spErrorOptions', $spError);

    $old = sp_get_option('combinecss');
	sp_update_option('combinecss', isset($_POST['combinecss']));
    if (!$old && isset($_POST['combinecss'])) {
        sp_clear_combined_css('all');
        sp_clear_combined_css('mobile');
        sp_clear_combined_css('tablet');
    }

    $old = sp_get_option('combinejs');
	sp_update_option('combinejs', isset($_POST['combinejs']));
    if (!$old && isset($_POST['combinejs'])) {
		sp_clear_combined_scripts('desktop');
		sp_clear_combined_scripts('mobile');
		sp_clear_combined_scripts('tablet');
    }

	sp_update_option('floodcontrol', max(sp_esc_int($_POST['floodcontrol']), 0));

    do_action('sph_option_global_save');

	return $mess;
}

function spa_save_display_data() {
	check_admin_referer('forum-adminform_display', 'forum-adminform_display');
	$mess = spa_text('Display options updated');

	$sfdisplay = sp_get_option('sfdisplay');
	$sfcontrols = sp_get_option('sfcontrols');

	# Page Title
    $sfdisplay['pagetitle']['notitle'] = isset($_POST['sfnotitle']);
	$sfdisplay['pagetitle']['banner'] = sp_filter_save_cleanurl($_POST['sfbanner']);

	# Stats
	$sfcontrols['shownewcount'] = (isset($_POST['shownewcount'])) ? sp_esc_int($_POST['shownewcount']) : 6;
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		$ccount = count($newuserlist);
		while ($ccount > ($sfcontrols['shownewcount'])) {
			array_pop($newuserlist);
			$ccount--;
		}
		sp_update_option('spRecentMembers', $newuserlist);
	}
	$sfcontrols['showtopcount'] = (isset($_POST['showtopcount'])) ? sp_esc_int($_POST['showtopcount']) : 6;
	$sfcontrols['hidemembers'] = isset($_POST['hidemembers']);

    # adjust stats interval
	$statsInterval = (!empty($_POST['statsinterval'])) ? sp_esc_str($_POST['statsinterval']) * 3600 : 3600;
	$oldStatsInterval = sp_get_option('sp_stats_interval') * 3600;
    if ($statsInterval != $oldStatsInterval) {
    	sp_update_option('sp_stats_interval', $statsInterval);
        wp_clear_scheduled_hook('sph_stats_cron');
    	wp_schedule_event(time(), 'sp_stats_interval', 'sph_stats_cron');
    }

	include_once SF_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';

	$topPosters = sp_get_top_poster_stats((int) $sfcontrols['showtopcount']);
	sp_update_option('spPosterStats', $topPosters);

    $sfdisplay['forums']['singleforum'] = isset($_POST['sfsingleforum']);

	$sfdisplay['topics']['perpage'] = (isset($_POST['sfpagedtopics'])) ? sp_esc_int($_POST['sfpagedtopics']) : 20;
    $sfdisplay['topics']['sortnewtop'] = isset($_POST['sftopicsort']);

	$sfdisplay['posts']['perpage'] = (isset($_POST['sfpagedposts'])) ? sp_esc_int($_POST['sfpagedposts']) : 20;
    $sfdisplay['posts']['sortdesc'] = isset($_POST['sfsortdesc']);

	$sfdisplay['editor']['toolbar'] = isset($_POST['sftoolbar']);

	sp_update_option('sfcontrols', $sfcontrols);
	sp_update_option('sfdisplay', $sfdisplay);

    do_action('sph_option_display_save');

	return $mess;
}

function spa_save_content_data() {
	check_admin_referer('forum-adminform_content', 'forum-adminform_content');
	$mess = spa_text('Options updated');

	# Save Image resizing
	$sfimage = array();
	$sfimage = sp_get_option('sfimage');
    $sfimage['enlarge'] = isset($_POST['sfimgenlarge']);
    $sfimage['process'] = isset($_POST['process']);
    $sfimage['constrain'] = isset($_POST['constrain']);
    $sfimage['forceclear'] = isset($_POST['forceclear']);

	$thumb = sp_esc_int($_POST['sfthumbsize']);
	if ($thumb < 100) {
		$thumb = 100;
		$mess.= '<br />* '.spa_text('Image thumbsize reset to minimum 100px');
	}
	$sfimage['thumbsize'] = $thumb;
	$sfimage['style'] = sp_esc_str($_POST['style']);

	sp_update_option('sfimage', $sfimage);

	sp_update_option('sfdates', sp_filter_title_save(trim($_POST['sfdates'])));
	sp_update_option('sftimes', sp_filter_title_save(trim($_POST['sftimes'])));

	# link filters
	$sffilters = array();
    $sffilters['sfnofollow'] = isset($_POST['sfnofollow']);
    $sffilters['sftarget'] = isset($_POST['sftarget']);
    $sffilters['sffilterpre'] = isset($_POST['sffilterpre']);
    $sffilters['sfdupemember'] = isset($_POST['sfdupemember']);
    $sffilters['sfdupeguest'] = isset($_POST['sfdupeguest']);
	$sffilters['sfurlchars'] = sp_esc_int($_POST['sfurlchars']);
	$sffilters['sfmaxlinks'] = sp_esc_int($_POST['sfmaxlinks']);
	if (empty($sffilters['sfmaxlinks'])) $sffilters['sfmaxlinks'] = 0;
	$sffilters['sfmaxsmileys'] = sp_esc_int($_POST['sfmaxsmileys']);
	if (empty($sffilters['sfmaxsmileys'])) $sffilters['sfmaxsmileys'] = 0;

	$sffilters['sfnolinksmsg'] = sp_filter_text_save(trim($_POST['sfnolinksmsg']));
	sp_update_option('sffilters', $sffilters);

	spa_update_check_option('sffiltershortcodes');
	sp_update_option('sfshortcodes', sp_filter_text_save(trim($_POST['sfshortcodes'])));

    do_action('sph_option_content_save');

	return $mess;
}

function spa_save_members_data() {
	check_admin_referer('forum-adminform_members', 'forum-adminform_members');
	$mess = spa_text('Options updated');

	$sfmemberopts = sp_get_option('sfmemberopts');
    $sfmemberopts['sfcheckformember'] = isset($_POST['sfcheckformember']);
    $sfmemberopts['sfhidestatus'] = isset($_POST['sfhidestatus']);
	sp_update_option('sfmemberopts', $sfmemberopts);

	$sfguests = array();
    $sfguests['reqemail'] = isset($_POST['reqemail']);
    $sfguests['storecookie'] = isset($_POST['storecookie']);
	sp_update_option('sfguests', $sfguests);

	$sfuser = array();
    $sfuser['sfuserinactive'] = isset($_POST['sfuserinactive']);
    $sfuser['sfusernoposts'] = isset($_POST['sfusernoposts']);
	if (isset($_POST['sfuserperiod']) && $_POST['sfuserperiod'] > 0) {
		$sfuser['sfuserperiod'] = intval($_POST['sfuserperiod']);
	} else {
		$sfuser['sfuserperiod'] = 365; # if not filled in make it one year
	}

	sp_update_option('account-name', sp_filter_name_save(trim($_POST['account-name'])));
	sp_update_option('display-name', sp_filter_name_save(trim($_POST['display-name'])));
	sp_update_option('guest-name', sp_filter_name_save(trim($_POST['guest-name'])));

	# auto removal cron job
	wp_clear_scheduled_hook('sph_cron_user');
	if (isset($_POST['sfuserremove'])) {
		$sfuser['sfuserremove'] = true;
		wp_schedule_event(time(), 'daily', 'sph_cron_user');
	} else {
		$sfuser['sfuserremove'] = false;
	}
	sp_update_option('sfuserremoval', $sfuser);

 	sp_update_option('post_count_delete', isset($_POST['post_count_delete']));

	$sfprofile = sp_get_option('sfprofile');
	$sfprofile['namelink'] = sp_esc_int($_POST['namelink']);
	sp_update_option('sfprofile', $sfprofile);

    do_action('sph_option_members_save');

	return $mess;
}

function spa_save_email_data() {
	check_admin_referer('forum-adminform_email', 'forum-adminform_email');
	$mess = spa_text('Options updated');

	# Save Email Options
	# Thanks to Andrew Hamilton for these routines (mail-from plugion)
	# Remove any illegal characters and convert to lowercase both the user name and domain name
	$domain_input_errors = array('http://', 'https://', 'ftp://', 'www.');
	$domainname = strtolower(sp_filter_title_save(trim($_POST['sfmaildomain'])));
	$domainname = str_replace ($domain_input_errors, '', $domainname);
	$domainname = preg_replace('/[^0-9a-z\-\.]/i','',$domainname);

	$illegal_chars_username = array('(', ')', '<', '>', ',', ';', ':', '\\', '"', '[', ']', '@', ' ');
	$username = strtolower(sp_filter_name_save(trim($_POST['sfmailfrom'])));
	$username = str_replace ($illegal_chars_username, '', $username);

	$sfmail = array();
	$sfmail['sfmailsender'] = sp_filter_name_save(trim($_POST['sfmailsender']));
	$sfmail['sfmailfrom'] = $username;
	$sfmail['sfmaildomain'] = $domainname;
    $sfmail['sfmailuse'] = isset($_POST['sfmailuse']);
	sp_update_option('sfmail', $sfmail);

	# Save new user mail options
	$sfmail = array();
    $sfmail['sfusespfreg'] = isset($_POST['sfusespfreg']);
	$sfmail['sfnewusersubject'] = sp_filter_title_save(trim($_POST['sfnewusersubject']));
	$sfmail['sfnewusertext'] = sp_filter_title_save(trim($_POST['sfnewusertext']));
	sp_update_option('sfnewusermail', $sfmail);

    do_action('sph_option_email_save');

	return $mess;
}

function spa_save_newposts_data() {
	check_admin_referer('forum-adminform_newposts', 'forum-adminform_newposts');
	$mess = spa_text('New Post Handling options updated');

	$sfcontrols = sp_get_option('sfcontrols');

    # unread posts
	$sfcontrols['sfdefunreadposts'] = (is_numeric($_POST['sfdefunreadposts'])) ? max(0, sp_esc_int($_POST['sfdefunreadposts'])) : 50;
    $sfcontrols['sfusersunread'] = isset($_POST['sfusersunread']);
	$sfcontrols['sfmaxunreadposts'] = (is_numeric($_POST['sfmaxunreadposts'])) ? max(0, sp_esc_int($_POST['sfmaxunreadposts'])) : $sfcontrols['sfdefunreadposts'];

	$sfcontrols['flagsuse'] = isset($_POST['flagsuse']);
	$sfcontrols['flagstext'] = sp_filter_title_save(trim($_POST['flagstext']));
	$sfcontrols['flagsbground'] = substr(sp_filter_title_save(trim($_POST['flagsbground'])), 1);
	$sfcontrols['flagscolor'] = substr(sp_filter_title_save(trim($_POST['flagscolor'])), 1);

	sp_update_option('sfcontrols', $sfcontrols);

	sp_update_option('topic_cache', sp_esc_int($_POST['topiccache']));

    do_action('sph_option_newposts_save');

	return $mess;
}

?>