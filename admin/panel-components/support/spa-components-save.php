<?php
/*
Simple:Press
Admin Options Save Options Support Functions
$LastChangedDate: 2016-10-02 16:00:20 -0500 (Sun, 02 Oct 2016) $
$Rev: 14610 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

#= Save and Upload Smmileys ===============================
function spa_save_smileys_data() {
	global $spPaths;

	check_admin_referer('forum-adminform_smileys', 'forum-adminform_smileys');

	$mess = '';

	# save the smileys
	$sfsmileys = array();
	$path = SF_STORE_DIR.'/'.$spPaths['smileys'].'/';

	$smileyname = $_POST['smname'];

	for ($x = 0; $x < count($smileyname); $x++) {
		$file = $_POST['smfile'][$x];
		$path_info = pathinfo($path.$file);
		$fn = strtolower($path_info['filename']);
		if (file_exists($path.$file)) {
			if (empty($smileyname[$x])) $smileyname[$x] = $fn;
			$thisname = urldecode(sp_create_slug($smileyname[$x], false));

			$code = (empty($_POST['smcode'][$x]) ? $fn : $_POST['smcode'][$x]);
			$code = sp_create_slug($code, false);
			trim($code, ':');
			if (empty($code)) $code = $thisname;
			$code = ':'.$code.':';

			$sfsmileys[$thisname][] = sp_filter_filename_save($_POST['smfile'][$x]);
			$sfsmileys[$thisname][] = $code;
			$sfsmileys[$thisname][] = isset($_POST['sminuse-'.$smileyname[$x]]) ? 1 : 0;
			$sfsmileys[$thisname][] = $x;

			if (isset($_POST['smbreak-newbreak-'.$x])) $_POST['smbreak-'.$smileyname[$x]] = $_POST['smbreak-newbreak-'.$x];
			$sfsmileys[$thisname][] = isset($_POST['smbreak-'.$smileyname[$x]]) ? 1 : 0;
		}
	}

	# load current saved smileys to get meta id
	$meta = sp_get_sfmeta('smileys', 'smileys');
	sp_update_sfmeta('smileys', 'smileys', $sfsmileys, $meta[0]['meta_id'], true);

	do_action('sph_component_smileys_save');

	$mess.= spa_text('Smileys component updated');
	return $mess;
}

#= Save Login Options ===============================
function spa_save_login_data() {
	check_admin_referer('forum-adminform_login', 'forum-adminform_login');

	# login
	$sflogin = sp_get_option('sflogin');
	$sflogin['sfregmath'] = isset($_POST['sfregmath']);

	if (!empty($_POST['sfloginurl']))
		$sflogin['sfloginurl'] = sp_filter_save_cleanurl($_POST['sfloginurl']);
	else
		$sflogin['sfloginurl'] = '';

	if (!empty($_POST['sflogouturl']))
		$sflogin['sflogouturl'] = sp_filter_save_cleanurl($_POST['sflogouturl']);
	else
		$sflogin['sflogouturl'] = '';

	if (!empty($_POST['sfregisterurl']))
		$sflogin['sfregisterurl'] = sp_filter_save_cleanurl($_POST['sfregisterurl']);
	else
		$sflogin['sfregisterurl'] = '';

	if (!empty($_POST['sfloginemailurl']))
		$sflogin['sfloginemailurl'] = sp_filter_save_cleanurl($_POST['sfloginemailurl']);
	else
		$sflogin['sfloginemailurl'] = esc_url(wp_login_url(sp_url()));


	if (!empty($_POST['spaltloginurl']))
		$sflogin['spaltloginurl'] = sp_filter_save_cleanurl($_POST['spaltloginurl']);
	else
		$sflogin['spaltloginurl'] = '';

	if (!empty($_POST['spaltlogouturl']))
		$sflogin['spaltlogouturl'] = sp_filter_save_cleanurl($_POST['spaltlogouturl']);
	else
		$sflogin['spaltlogouturl'] = '';

	if (!empty($_POST['spaltregisterurl']))
		$sflogin['spaltregisterurl'] = sp_filter_save_cleanurl($_POST['spaltregisterurl']);
	else
		$sflogin['spaltregisterurl'] = '';

	$sflogin['spshowlogin'] = isset($_POST['spshowlogin']);
	$sflogin['spshowregister'] = isset($_POST['spshowregister']);

	if (!empty($_POST['sptimeout'])) $timeout = sp_esc_int($_POST['sptimeout']);
	if (!$timeout) $timeout = 20;
	$sflogin['sptimeout'] = $timeout;

	sp_update_option('sflogin', $sflogin);

	# RPX support
	$sfrpx = sp_get_option('sfrpx');
	$oldrpx = $sfrpx['sfrpxenable'];
	$sfrpx['sfrpxenable'] = isset($_POST['sfrpxenable']);
	$sfrpx['sfrpxkey'] = sp_esc_str($_POST['sfrpxkey']);
	$sfrpx['sfrpxredirect'] = sp_filter_save_cleanurl($_POST['sfrpxredirect']);

	# change in RPX support?
	if (!$oldrpx && $sfrpx['sfrpxenable']) {
		include_once SPBOOT.'site/credentials/sp-rpx.php';

		$post_data = array('apiKey' => $_POST['sfrpxkey'], 'format' => 'json');
		$raw = sp_rpx_http_post('https://rpxnow.com/plugin/lookup_rp', $post_data);
		$r = sp_rpx_parse_lookup_rp($raw);
		if ($r) {
			$sfrpx['sfrpxrealm'] = $r['realm'];
		} else {
			$mess = spa_text('Error in RPX API data!');
			return $mess;
		}
	}

	sp_update_option('sfrpx', $sfrpx);

	do_action('sph_component_login_save');

	$mess = spa_text('Login and registration component updated');
	return $mess;
}

#= Save Eextensions Options ===============================
function spa_save_seo_data() {
	check_admin_referer('forum-adminform_seo', 'forum-adminform_seo');

	$mess = '';

	# browser title
	$sfseo = array();
	$sfseo['sfseo_overwrite'] = isset($_POST['sfseo_overwrite']);
	$sfseo['sfseo_blogname'] = isset($_POST['sfseo_blogname']);
	$sfseo['sfseo_pagename'] = isset($_POST['sfseo_pagename']);
	$sfseo['sfseo_homepage'] = isset($_POST['sfseo_homepage']);
	$sfseo['sfseo_topic'] = isset($_POST['sfseo_topic']);
	$sfseo['sfseo_forum'] = isset($_POST['sfseo_forum']);
	$sfseo['sfseo_noforum'] = isset($_POST['sfseo_noforum']);
	$sfseo['sfseo_page'] = isset($_POST['sfseo_page']);
	$sfseo['sfseo_sep'] = sp_filter_title_save(trim($_POST['sfseo_sep']));
	$sfseo['sfseo_og'] = isset($_POST['sfseo_og']);
	$sfseo['seo_og_attachment'] = isset($_POST['seo_og_attachment']);
	$sfseo['seo_og_type'] = empty($_POST['seo_og_type']) ? 'website' : sp_filter_title_save(trim($_POST['seo_og_type']));

	sp_update_option('sfseo', $sfseo);

	# meta tags
	$sfmetatags = array();
	$sfmetatags['sfdescr'] = sp_filter_title_save(trim($_POST['sfdescr']));
	$sfmetatags['sfdescruse'] = sp_esc_int($_POST['sfdescruse']);
	$sfmetatags['sfusekeywords'] = sp_esc_int($_POST['sfusekeywords']);
	$sfmetatags['sfkeywords'] = sp_filter_title_save(trim($_POST['sfkeywords']));
	sp_update_option('sfmetatags', $sfmetatags);

	# auto removal cron job
	if (isset($_POST['sfuserremove'])) {
		$sfuser['sfuserremove'] = true;
	} else {
		$sfuser['sfuserremove'] = false;
	}

	do_action('sph_component_seo_save');

	$mess.= '<br />'.spa_text('SEO components updated').$mess;
	return $mess;
}

#= Save Forum Rankings ===============================
function spa_save_forumranks_data() {
    check_admin_referer('forum-adminform_forumranks', 'forum-adminform_forumranks');

	# save forum ranks
	for ($x = 0; $x < count($_POST['rankdesc']); $x++) {
		if (!empty($_POST['rankdesc'][$x])) {
			$rankdata = array();
			$rankdata['posts'] = sp_esc_int($_POST['rankpost'][$x]);
			$rankdata['usergroup'] = (int) $_POST['rankug'][$x];
			$rankdata['badge'] = sp_filter_filename_save($_POST['rankbadge'][$x]);
			if ($_POST['rankid'][$x] == -1) {
				sp_add_sfmeta('forum_rank', sp_filter_title_save(trim($_POST['rankdesc'][$x])), $rankdata, 1);
			} else {
				sp_update_sfmeta('forum_rank', sp_filter_title_save(trim($_POST['rankdesc'][$x])), $rankdata, sp_esc_int($_POST['rankid'][$x]), 1);
			}
		}
	}

	do_action('sph_component_ranks_save');

	$mess = spa_text('Forum ranks updated');
	return $mess;
}

#= Save Special Ranks ===============================
function spa_add_specialrank() {
   check_admin_referer('special-rank-new', 'special-rank-new');

	# save special forum ranks
	if (!empty($_POST['specialrank'])) {
		$rankdata = array();
		$rankdata['badge'] = '';
		sp_add_sfmeta('special_rank', sp_filter_title_save(trim($_POST['specialrank'])), $rankdata, 1);
	}

	do_action('sph_component_srank_new_save');

	$mess = spa_text('Special rank added');
	return $mess;
}

#= Save Special Ranks ===============================
function spa_update_specialrank($id) {
   check_admin_referer('special-rank-update', 'special-rank-update');

	# save special forum ranks
	if (!empty($_POST['specialrankdesc'])) {
		$desc = $_POST['specialrankdesc'];
		$badge = $_POST['specialrankbadge'];
		$rank = sp_get_sfmeta('special_rank', false, $id);
		$rank[0]['meta_value']['badge'] = sp_filter_filename_save($badge[$id]);
		sp_update_sfmeta('special_rank', sp_filter_title_save(trim($desc[$id])), $rank[0]['meta_value'], $id, 1);
		if($_POST['currentname'][$id] != $desc[$id]) {
			spdb_query("UPDATE ".SFSPECIALRANKS."
						SET special_rank = '".$desc[$id]."'
						WHERE special_rank = '".$_POST['currentname'][$id]."'");
		}
	}

	do_action('sph_component_srank_update_save');

	$mess = spa_text('Special ranks updated');
	return $mess;
}

function spa_add_special_rank_member($id) {
	check_admin_referer('special-rank-add', 'special-rank-add');

	$user_id_list = array_unique($_POST['amember_id']);
	if (empty($user_id_list)) return;

	# get the special rank
	$rank = sp_get_sfmeta('special_rank', false, $id);

	# add the new users
	for ($x = 0; $x < count($user_id_list); $x++) {
		sp_add_special_rank((int) $user_id_list[$x], $rank[0]['meta_key']);
	}

	do_action('sph_component_srank_add_save');

	$mess = spa_text('User(s) added to special forum ranks');
	return $mess;
}

function spa_del_special_rank_member($id) {
	check_admin_referer('special-rank-del', 'special-rank-del');

	$user_id_list = array_unique($_POST['dmember_id']);
	if (empty($user_id_list)) return;

	# get the special rank
	$rank = sp_get_sfmeta('special_rank', false, $id);

	for ($x = 0; $x < count($user_id_list); $x++) {
		sp_delete_special_rank((int) $user_id_list[$x], $rank[0]['meta_key']);
	}

	do_action('sph_component_srank_del_save');

	$mess = spa_text('User(s) deleted from special forum ranks');
	return $mess;
}

#= Save Custom	Messages ===============================
function spa_save_messages_data() {
	check_admin_referer('forum-adminform_messages', 'forum-adminform_messages');

	# custom message for editor
	$sfpostmsg = array();
	$sfpostmsg['sfpostmsgtext'] = sp_filter_text_save(trim($_POST['sfpostmsgtext']));
	$sfpostmsg['sfpostmsgtopic'] = isset($_POST['sfpostmsgtopic']);
	$sfpostmsg['sfpostmsgpost'] = isset($_POST['sfpostmsgpost']);
	sp_update_option('sfpostmsg', $sfpostmsg);

	sp_update_option('sfeditormsg', sp_filter_text_save(trim($_POST['sfeditormsg'])));

	# if set update, otherwise its empty, so remove
	if ($_POST['sfsneakpeek'] != '') {
		sp_add_sfmeta('sneakpeek', 'message', sp_filter_text_save(trim($_POST['sfsneakpeek'])));
	} else {
		$msg = sp_get_sfmeta('sneakpeek', 'message');
		if (!empty($msg)) sp_delete_sfmeta($msg[0]['meta_id']);
	}

	$sflogin = array();
	$sflogin = sp_get_option('sflogin');
	$sflogin['sfsneakredirect'] = sp_filter_save_cleanurl($_POST['sfsneakredirect']);
	sp_update_option('sflogin', $sflogin);

	# if set update, otherwise its empty, so remove
	if ($_POST['sfadminview'] != '') {
		sp_add_sfmeta('adminview', 'message', sp_filter_text_save(trim($_POST['sfadminview'])));
	} else {
		$msg = sp_get_sfmeta('adminview', 'message');
		if (!empty($msg)) sp_delete_sfmeta($msg[0]['meta_id']);
	}

	# if set update, otherwise its empty, so remove
	if ($_POST['sfuserview'] != '') {
		sp_add_sfmeta('userview', 'message', sp_filter_text_save(trim($_POST['sfuserview'])));
	} else {
		$msg = sp_get_sfmeta('userview', 'message');
		if (!empty($msg)) sp_delete_sfmeta($msg[0]['meta_id']);
	}

	do_action('sph_component_messages_save');

	$mess = spa_text('Custom messages updated');
	return $mess;
}
?>