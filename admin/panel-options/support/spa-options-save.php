<?php
/*
Simple:Press
Admin Options Save Options Support Functions
$LastChangedDate: 2018-12-03 11:05:54 -0600 (Mon, 03 Dec 2018) $
$Rev: 15840 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_global_data()
{
	global $wp_roles;

	check_admin_referer('forum-adminform_global', 'forum-adminform_global');
	$mess = SP()->primitives->admin_text('Options updated');

	spa_update_check_option('sflockdown');

	# auto update
	$sfauto = array();
    $sfauto['sfautoupdate'] = isset($_POST['sfautoupdate']);
	$sfauto['sfautotime'] = SP()->filters->integer($_POST['sfautotime']);
	if (empty($sfauto['sfautotime']) || $sfauto['sfautotime'] == 0) $sfauto['sfautotime'] = 300;
	SP()->options->update('sfauto', $sfauto);

	$sfrss = array();
    $sfrss['sfrsscount'] = SP()->filters->integer($_POST['sfrsscount']);
	$sfrss['sfrsswords'] = SP()->filters->integer($_POST['sfrsswords']);
    $sfrss['sfrssfeedkey'] = isset($_POST['sfrssfeedkey']);
    $sfrss['sfrsstopicname'] = isset($_POST['sfrsstopicname']);
	SP()->options->update('sfrss', $sfrss);

	$sfblock = array();
    $sfblock['blockadmin'] = isset($_POST['blockadmin']);
    $sfblock['blockprofile'] = isset($_POST['blockprofile']);
	$sfblock['blockredirect'] = SP()->saveFilters->cleanurl(isset($_POST['blockredirect']));
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

	SP()->options->update('sfblockadmin', $sfblock);

	SP()->options->update('speditor', SP()->filters->integer($_POST['editor']));
	SP()->options->update('editpostdays', max((int) $_POST['editpostdays'], 1));

	$spError = array();
	$spError['spErrorLogOff'] = isset($_POST['errorlog']);
	$spError['spNoticesOff'] = isset($_POST['notices']);
	SP()->options->update('spErrorOptions', $spError);

    $old = SP()->options->get('combinecss');
	SP()->options->update('combinecss', isset($_POST['combinecss']));
    if (!$old && isset($_POST['combinecss'])) {
        SP()->plugin->clear_css_cache('all');
        SP()->plugin->clear_css_cache('mobile');
        SP()->plugin->clear_css_cache('tablet');
    }

    $old = SP()->options->get('combinejs');
	SP()->options->update('combinejs', isset($_POST['combinejs']));
    if (!$old && isset($_POST['combinejs'])) {
		SP()->plugin->clear_scripts_cache('desktop');
		SP()->plugin->clear_scripts_cache('mobile');
		SP()->plugin->clear_scripts_cache('tablet');
    }

	SP()->options->update('floodcontrol', max(SP()->filters->integer($_POST['floodcontrol']), 0));

    do_action('sph_option_global_save');

	return $mess;
}

function spa_save_display_data() {
	check_admin_referer('forum-adminform_display', 'forum-adminform_display');
	$mess = SP()->primitives->admin_text('Display options updated');

	$sfdisplay = SP()->options->get('sfdisplay');
	$sfcontrols = SP()->options->get('sfcontrols');

	# Page Title
    $sfdisplay['pagetitle']['notitle'] = isset($_POST['sfnotitle']);
	$sfdisplay['pagetitle']['banner'] = SP()->saveFilters->cleanurl($_POST['sfbanner']);

    $sfdisplay['hideuserinfo'] = isset($_POST['sfhideuserinfo']) ;

	# Stats
	$sfcontrols['shownewcount'] = (isset($_POST['shownewcount'])) ? SP()->filters->integer($_POST['shownewcount']) : 6;
	$newuserlist = SP()->options->get('spRecentMembers');
	if (is_array($newuserlist)) {
		$ccount = count($newuserlist);
		while ($ccount > ($sfcontrols['shownewcount'])) {
			array_pop($newuserlist);
			$ccount--;
		}
		SP()->options->update('spRecentMembers', $newuserlist);
	}
	$sfcontrols['showtopcount'] = (isset($_POST['showtopcount'])) ? SP()->filters->integer($_POST['showtopcount']) : 6;
	$sfcontrols['hidemembers'] = isset($_POST['hidemembers']);

    # adjust stats interval
	$statsInterval = (!empty($_POST['statsinterval'])) ? SP()->filters->str($_POST['statsinterval']) * 3600 : 3600;
	$oldStatsInterval = SP()->options->get('sp_stats_interval') * 3600;
    if ($statsInterval != $oldStatsInterval) {
    	SP()->options->update('sp_stats_interval', $statsInterval);
        wp_clear_scheduled_hook('sph_stats_cron');
    	wp_schedule_event(time(), 'sp_stats_interval', 'sph_stats_cron');
    }

	require_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';

	$topPosters = sp_get_top_poster_stats((int) $sfcontrols['showtopcount']);
	SP()->options->update('spPosterStats', $topPosters);

    $sfdisplay['forums']['singleforum'] = isset($_POST['sfsingleforum']);

	$sfdisplay['topics']['perpage'] = (isset($_POST['sfpagedtopics'])) ? SP()->filters->integer($_POST['sfpagedtopics']) : 20;
    $sfdisplay['topics']['sortnewtop'] = isset($_POST['sftopicsort']);

	$sfdisplay['posts']['perpage'] = (isset($_POST['sfpagedposts'])) ? SP()->filters->integer($_POST['sfpagedposts']) : 20;
    $sfdisplay['posts']['sortdesc'] = isset($_POST['sfsortdesc']);

	$sfdisplay['editor']['toolbar'] = isset($_POST['sftoolbar']);

	SP()->options->update('sfcontrols', $sfcontrols);
	SP()->options->update('sfdisplay', $sfdisplay);

    do_action('sph_option_display_save');

	return $mess;
}

function spa_save_content_data() {
	check_admin_referer('forum-adminform_content', 'forum-adminform_content');
	$mess = SP()->primitives->admin_text('Options updated');

	# Save Image resizing
	$sfimage = [];
	$sfimage = SP()->options->get('sfimage');
    $sfimage['enlarge'] = isset($_POST['sfimgenlarge']);
    $sfimage['process'] = isset($_POST['process']);
    $sfimage['constrain'] = isset($_POST['constrain']);
    $sfimage['forceclear'] = isset($_POST['forceclear']);

	$thumb = SP()->filters->integer($_POST['sfthumbsize']);
	if ($thumb < 100) {
		$thumb = 100;
		$mess.= '<br />* '.SP()->primitives->admin_text('Image thumbsize reset to minimum 100px');
	}
	$sfimage['thumbsize'] = $thumb;
	$sfimage['style'] = SP()->filters->str($_POST['style']);

	SP()->options->update('sfimage', $sfimage);

	SP()->options->update('sfdates', SP()->saveFilters->title(trim($_POST['sfdates'])));
	SP()->options->update('sftimes', SP()->saveFilters->title(trim($_POST['sftimes'])));

	# link filters
	$sffilters = array();
    $sffilters['sfnofollow'] = isset($_POST['sfnofollow']);
    $sffilters['sftarget'] = isset($_POST['sftarget']);
    $sffilters['sffilterpre'] = isset($_POST['sffilterpre']);
    $sffilters['sfdupemember'] = isset($_POST['sfdupemember']);
    $sffilters['sfdupeguest'] = isset($_POST['sfdupeguest']);
	$sffilters['sfurlchars'] = SP()->filters->integer($_POST['sfurlchars']);
	$sffilters['sfmaxlinks'] = SP()->filters->integer($_POST['sfmaxlinks']);
	if (empty($sffilters['sfmaxlinks'])) $sffilters['sfmaxlinks'] = 0;
	$sffilters['sfmaxsmileys'] = SP()->filters->integer($_POST['sfmaxsmileys']);
	if (empty($sffilters['sfmaxsmileys'])) $sffilters['sfmaxsmileys'] = 0;

	$sffilters['sfnolinksmsg'] = SP()->saveFilters->text(trim($_POST['sfnolinksmsg']));
	SP()->options->update('sffilters', $sffilters);

	spa_update_check_option('sffiltershortcodes');
	SP()->options->update('sfshortcodes', SP()->saveFilters->text(trim($_POST['sfshortcodes'])));

    do_action('sph_option_content_save');

	return $mess;
}

function spa_save_members_data() {
	check_admin_referer('forum-adminform_members', 'forum-adminform_members');
	$mess = SP()->primitives->admin_text('Options updated');

	$sfmemberopts = SP()->options->get('sfmemberopts');
    $sfmemberopts['sfcheckformember'] = isset($_POST['sfcheckformember']);
    $sfmemberopts['sfhidestatus'] = isset($_POST['sfhidestatus']);
	SP()->options->update('sfmemberopts', $sfmemberopts);

	$sfguests = array();
    $sfguests['reqemail'] = isset($_POST['reqemail']);
    $sfguests['storecookie'] = isset($_POST['storecookie']);
	SP()->options->update('sfguests', $sfguests);

	$sfuser = array();
    $sfuser['sfuserinactive'] = isset($_POST['sfuserinactive']);
    $sfuser['sfusernoposts'] = isset($_POST['sfusernoposts']);
	if (isset($_POST['sfuserperiod']) && $_POST['sfuserperiod'] > 0) {
		$sfuser['sfuserperiod'] = intval($_POST['sfuserperiod']);
	} else {
		$sfuser['sfuserperiod'] = 365; # if not filled in make it one year
	}

	SP()->options->update('account-name', SP()->saveFilters->name(trim($_POST['account-name'])));
	SP()->options->update('display-name', SP()->saveFilters->name(trim($_POST['display-name'])));
	SP()->options->update('guest-name', SP()->saveFilters->name(trim($_POST['guest-name'])));

	# auto removal cron job
	wp_clear_scheduled_hook('sph_cron_user');
	if (isset($_POST['sfuserremove'])) {
		$sfuser['sfuserremove'] = true;
		wp_schedule_event(time(), 'daily', 'sph_cron_user');
	} else {
		$sfuser['sfuserremove'] = false;
	}
	SP()->options->update('sfuserremoval', $sfuser);

 	SP()->options->update('post_count_delete', isset($_POST['post_count_delete']));
	
	SP()->options->update('display_deprecated_identities', isset($_POST['sfdisplaydeprecatedidentities']));	

	$sfprofile = SP()->options->get('sfprofile');
	$sfprofile['namelink'] = SP()->filters->integer($_POST['namelink']);
	SP()->options->update('sfprofile', $sfprofile);

	$sfPrivacy = SP()->options->get('spPrivacy');
	$sfPrivacy['posts'] = isset($_POST['posts']);
	$sfPrivacy['number'] = SP()->filters->integer($_POST['number']);
	$sfPrivacy['erase'] = SP()->filters->integer($_POST['erase']);
	$sfPrivacy['mess'] = SP()->saveFilters->text(trim($_POST['mess']));

	SP()->options->update('spPrivacy', $sfPrivacy);

    do_action('sph_option_members_save');

	return $mess;
}

function spa_save_email_data() {
	check_admin_referer('forum-adminform_email', 'forum-adminform_email');
	$mess = SP()->primitives->admin_text('Options updated');

	# Save Email Options
	# Thanks to Andrew Hamilton for these routines (mail-from plugion)
	# Remove any illegal characters and convert to lowercase both the user name and domain name
	$domain_input_errors = array('http://', 'https://', 'ftp://', 'www.');
	$domainname = strtolower(SP()->saveFilters->title(trim($_POST['sfmaildomain'])));
	$domainname = str_replace ($domain_input_errors, '', $domainname);
	$domainname = preg_replace('/[^0-9a-z\-\.]/i','',$domainname);

	$illegal_chars_username = array('(', ')', '<', '>', ',', ';', ':', '\\', '"', '[', ']', '@', ' ');
	$username = strtolower(SP()->saveFilters->name(trim($_POST['sfmailfrom'])));
	$username = str_replace ($illegal_chars_username, '', $username);

	$sfmail = array();
	$sfmail['sfmailsender'] = SP()->saveFilters->name(trim($_POST['sfmailsender']));
	$sfmail['sfmailfrom'] = $username;
	$sfmail['sfmaildomain'] = $domainname;
    $sfmail['sfmailuse'] = isset($_POST['sfmailuse']);
	SP()->options->update('sfmail', $sfmail);

	# Save new user mail options
	$sfmail = array();
    $sfmail['sfusespfreg'] = isset($_POST['sfusespfreg']);
	$sfmail['sfnewusersubject'] = SP()->saveFilters->title(trim($_POST['sfnewusersubject']));
	$sfmail['sfnewusertext'] = SP()->saveFilters->title(trim($_POST['sfnewusertext']));
	SP()->options->update('sfnewusermail', $sfmail);

    do_action('sph_option_email_save');

	return $mess;
}

function spa_save_newposts_data() {
	check_admin_referer('forum-adminform_newposts', 'forum-adminform_newposts');
	$mess = SP()->primitives->admin_text('New Post Handling options updated');

	$sfcontrols = SP()->options->get('sfcontrols');

    # unread posts
	$sfcontrols['sfdefunreadposts'] = (is_numeric($_POST['sfdefunreadposts'])) ? max(0, SP()->filters->integer($_POST['sfdefunreadposts'])) : 50;
    $sfcontrols['sfusersunread'] = isset($_POST['sfusersunread']);
	$sfcontrols['sfmaxunreadposts'] = (is_numeric($_POST['sfmaxunreadposts'])) ? max(0, SP()->filters->integer($_POST['sfmaxunreadposts'])) : $sfcontrols['sfdefunreadposts'];

	$sfcontrols['flagsuse'] = isset($_POST['flagsuse']);
	$sfcontrols['flagstext'] = SP()->saveFilters->title(trim($_POST['flagstext']));
	$sfcontrols['flagsbground'] = substr(SP()->saveFilters->title(trim($_POST['flagsbground'])), 1);
	$sfcontrols['flagscolor'] = substr(SP()->saveFilters->title(trim($_POST['flagscolor'])), 1);

	SP()->options->update('sfcontrols', $sfcontrols);

	SP()->options->update('topic_cache', SP()->filters->integer($_POST['topiccache']));

    do_action('sph_option_newposts_save');

	return $mess;
}
