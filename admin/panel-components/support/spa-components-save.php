<?php
/*
  Simple:Press
  Admin Options Save Options Support Functions
  $LastChangedDate: 2018-11-02 12:07:31 -0500 (Fri, 02 Nov 2018) $
  $Rev: 15788 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

#= Save and Upload Smmileys ===============================

function spa_save_smileys_data() {
	check_admin_referer('forum-adminform_smileys', 'forum-adminform_smileys');

	$mess = '';

	# save the smileys
	$sfsmileys = array();
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['smileys'].'/';

	$allSmileys = array_map(array(SP()->filters, 'str'), $_POST['smname']);
	$numSmileys = SP()->filters->integer($_POST['smiley-count']);

	if ($numSmileys) {
		for ($x = 0; $x < ($numSmileys+1); $x++) {
			$smileyName = $allSmileys[$x];
			$file = SP()->filters->str($_POST['smfile'][$x]);
			$path_info = pathinfo($path.$file);
			$fn = strtolower($path_info['filename']);
			if (file_exists($path.$file)) {
				if (empty($smileyName)) $smileyName = $fn;
				$thisname = urldecode(sp_create_slug($smileyName, false));

				$code = (empty($_POST['smcode'][$x]) ? $fn : SP()->filters->str($_POST['smcode'][$x]));
				$code = sp_create_slug($code, false);
				trim($code, ':');
				if (empty($code)) $code = $thisname;
				$code = ':'.$code.':';

				$sfsmileys[$thisname][] = SP()->saveFilters->filename($file);
				$sfsmileys[$thisname][] = $code;
				if (!empty($_POST['sminuse-'.$smileyName])) {
					$sfsmileys[$thisname][] = isset($_POST['sminuse-'.$smileyName]) ? 1 : 0;
				} else {
					$sfsmileys[$thisname][] = 0;
				}
				$sfsmileys[$thisname][] = $x;
				if (!empty($_POST['smbreak-'.$smileyName])) {
					$sfsmileys[$thisname][] = isset($_POST['smbreak-'.$smileyName]) ? 1 : 0;
				} else {
					$sfsmileys[$thisname][] =  0;
				}
			}
		}
	}

	# load current saved smileys to get meta id
	$meta = SP()->meta->get('smileys', 'smileys');
	SP()->meta->update('smileys', 'smileys', $sfsmileys, $meta[0]['meta_id']);

	do_action('sph_component_smileys_save');

	$mess .= SP()->primitives->admin_text('Smileys component updated');
	return $mess;
}

#= Save Login Options ===============================

function spa_save_login_data() {
	check_admin_referer('forum-adminform_login', 'forum-adminform_login');

	# login
	$sflogin = SP()->options->get('sflogin');
	$sflogin['sfregmath'] = isset($_POST['sfregmath']);

	if (!empty($_POST['sfloginurl'])) $sflogin['sfloginurl'] = SP()->saveFilters->cleanurl($_POST['sfloginurl']);
	else $sflogin['sfloginurl'] = '';

	if (!empty($_POST['sflogouturl'])) $sflogin['sflogouturl'] = SP()->saveFilters->cleanurl($_POST['sflogouturl']);
	else $sflogin['sflogouturl'] = '';

	if (!empty($_POST['sfregisterurl'])) $sflogin['sfregisterurl'] = SP()->saveFilters->cleanurl($_POST['sfregisterurl']);
	else $sflogin['sfregisterurl'] = '';

	if (!empty($_POST['sfloginemailurl'])) $sflogin['sfloginemailurl'] = SP()->saveFilters->cleanurl($_POST['sfloginemailurl']);
	else $sflogin['sfloginemailurl'] = esc_url(wp_login_url(SP()->spPermalinks->get_url()));


	if (!empty($_POST['spaltloginurl'])) $sflogin['spaltloginurl'] = SP()->saveFilters->cleanurl($_POST['spaltloginurl']);
	else $sflogin['spaltloginurl'] = '';

	if (!empty($_POST['spaltlogouturl'])) $sflogin['spaltlogouturl'] = SP()->saveFilters->cleanurl($_POST['spaltlogouturl']);
	else $sflogin['spaltlogouturl'] = '';

	if (!empty($_POST['spaltregisterurl'])) $sflogin['spaltregisterurl'] = SP()->saveFilters->cleanurl($_POST['spaltregisterurl']);
	else $sflogin['spaltregisterurl'] = '';

	$sflogin['spshowlogin'] = isset($_POST['spshowlogin']);
	$sflogin['spshowregister'] = isset($_POST['spshowregister']);

	if (!empty($_POST['sptimeout'])) $timeout = SP()->filters->integer($_POST['sptimeout']);
	if (!$timeout) $timeout = 20;
	$sflogin['sptimeout'] = $timeout;

	SP()->options->update('sflogin', $sflogin);

	# RPX support
	$sfrpx = SP()->options->get('sfrpx');
	$oldrpx = false;
	if(!empty($sfrpx)){
		$oldrpx  = $sfrpx['sfrpxenable'];
		$sfrpx['sfrpxenable'] = isset($_POST['sfrpxenable']);
		$sfrpx['sfrpxkey'] = SP()->filters->str($_POST['sfrpxkey']);
		$sfrpx['sfrpxredirect'] = SP()->saveFilters->cleanurl(isset($_POST['sfrpxredirect']) ? $_POST['sfrpxredirect'] : "" );
	}

	# change in RPX support?
	if($sfrpx){
		if (!$oldrpx && $sfrpx['sfrpxenable']) {
			require_once SPBOOT.'core/credentials/sp-rpx.php';
	
			$post_data = array(
				'apiKey' => SP()->filters->str($_POST['sfrpxkey']),
				'format' => 'json');
			$raw = sp_rpx_http_post('https://rpxnow.com/plugin/lookup_rp', $post_data);
			$r = sp_rpx_parse_lookup_rp($raw);
			if ($r) {
				$sfrpx['sfrpxrealm'] = $r['realm'];
			} else {
				$mess = SP()->primitives->admin_text('Error in RPX API data!');
				return $mess;
			}
	}
}

	SP()->options->update('sfrpx', $sfrpx);

	do_action('sph_component_login_save');

	$mess = SP()->primitives->admin_text('Login and registration component updated');
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
	$sfseo['sfseo_sep'] = SP()->saveFilters->title(trim($_POST['sfseo_sep']));
	$sfseo['sfseo_og'] = isset($_POST['sfseo_og']);
	$sfseo['seo_og_attachment'] = isset($_POST['seo_og_attachment']);
	$sfseo['seo_og_type'] = empty($_POST['seo_og_type']) ? 'website' : SP()->saveFilters->title(trim($_POST['seo_og_type']));

	SP()->options->update('sfseo', $sfseo);

	# meta tags
	$sfmetatags = array();
	$sfmetatags['sfdescr'] = SP()->saveFilters->title(trim($_POST['sfdescr']));
	$sfmetatags['sfdescruse'] = SP()->filters->integer($_POST['sfdescruse']);
	$sfmetatags['sfusekeywords'] = SP()->filters->integer($_POST['sfusekeywords']);
	$sfmetatags['sfkeywords'] = SP()->saveFilters->title(trim($_POST['sfkeywords']));
	SP()->options->update('sfmetatags', $sfmetatags);

	# auto removal cron job
	if (isset($_POST['sfuserremove'])) {
		$sfuser['sfuserremove'] = true;
	} else {
		$sfuser['sfuserremove'] = false;
	}

	do_action('sph_component_seo_save');

	$mess .= '<br />'.SP()->primitives->admin_text('SEO components updated').$mess;
	return $mess;
}

#= Save Forum Rankings ===============================

function spa_save_forumranks_data() {
	check_admin_referer('forum-adminform_forumranks', 'forum-adminform_forumranks');

	# save forum ranks
	for ($x = 0; $x < count($_POST['rankdesc']); $x++) {
		if (!empty($_POST['rankdesc'][$x])) {
			$rankdata = array();
			$rankdata['posts'] = SP()->filters->integer($_POST['rankpost'][$x]);
			$rankdata['usergroup'] = (int) $_POST['rankug'][$x];
			
			$badge = spa_get_selected_icon( $_POST['rankbadge'][$x], 'filename' );
			$rankdata['badge'] = $badge['value'];
			
			if ((int) $_POST['rankid'][$x] == -1) {
				SP()->meta->add('forum_rank', SP()->saveFilters->title(trim($_POST['rankdesc'][$x])), $rankdata);
			} else {
				SP()->meta->update('forum_rank', SP()->saveFilters->title(trim($_POST['rankdesc'][$x])), $rankdata, SP()->filters->integer($_POST['rankid'][$x]));
			}
		}
	}

	do_action('sph_component_ranks_save');

	$mess = SP()->primitives->admin_text('Forum ranks updated');
	return $mess;
}

#= Save Special Ranks ===============================

function spa_add_specialrank() {
	check_admin_referer('special-rank-new', 'special-rank-new');

	# save special forum ranks
	if (!empty($_POST['specialrank'])) {
		$rankdata = array();
		$rankdata['badge'] = '';
		SP()->meta->add('special_rank', SP()->saveFilters->title(trim($_POST['specialrank'])), $rankdata);
	}

	do_action('sph_component_srank_new_save');

	$mess = SP()->primitives->admin_text('Special rank added');
	return $mess;
}

#= Save Special Ranks ===============================

function spa_update_specialrank($id) {
	check_admin_referer('special-rank-update', 'special-rank-update');

	# save special forum ranks
	if (!empty($_POST['specialrankdesc'])) {
		$desc =  array_map('sanitize_text_field', $_POST['specialrankdesc']);
		$badge = array_map('sanitize_text_field', $_POST['specialrankbadge']);
		
		$badge = spa_get_selected_icon( $badge[$id], 'filename' );
		
		$rank = SP()->meta->get('special_rank', false, $id);
		$rank[0]['meta_value']['badge'] = $badge['value'];
		SP()->meta->update('special_rank', SP()->saveFilters->title(trim($desc[$id])), $rank[0]['meta_value'], $id);
		if (sanitize_text_field($_POST['currentname'][$id]) != $desc[$id]) {
			SP()->DB->execute("UPDATE ".SPSPECIALRANKS."
						SET special_rank = '".SP()->saveFilters->title($desc[$id])."'
						WHERE special_rank = '".SP()->saveFilters->title($_POST['currentname'][$id])."'");
		}
	}

	do_action('sph_component_srank_update_save');

	$mess = SP()->primitives->admin_text('Special ranks updated');
	return $mess;
}

function spa_add_special_rank_member($id) {
	check_admin_referer('special-rank-add', 'special-rank-add');

	$user_id_list = array_map('intval', array_unique($_POST['amember_id']));
	if (empty($user_id_list)) return '';

	# get the special rank
	$rank = SP()->meta->get('special_rank', false, $id);

	# add the new users
	for ($x = 0; $x < count($user_id_list); $x++) {
		sp_add_special_rank($user_id_list[$x], $rank[0]['meta_key']);
	}

	do_action('sph_component_srank_add_save');

	$mess = SP()->primitives->admin_text('User(s) added to special forum ranks');
	return $mess;
}

function spa_del_special_rank_member($id) {
	check_admin_referer('special-rank-del', 'special-rank-del');

	$user_id_list = array_map('intval', array_unique($_POST['dmember_id']));
	if (empty($user_id_list)) return '';

	# get the special rank
	$rank = SP()->meta->get('special_rank', false, $id);

	for ($x = 0; $x < count($user_id_list); $x++) {
		sp_delete_special_rank($user_id_list[$x], $rank[0]['meta_key']);
	}

	do_action('sph_component_srank_del_save');

	$mess = SP()->primitives->admin_text('User(s) deleted from special forum ranks');
	return $mess;
}

#= Save Custom	Messages ===============================

function spa_save_messages_data() {
	check_admin_referer('forum-adminform_messages', 'forum-adminform_messages');

	# custom message for editor
	$sfpostmsg = array();
	$sfpostmsg['sfpostmsgtext'] = SP()->saveFilters->text(trim($_POST['sfpostmsgtext']));
	$sfpostmsg['sfpostmsgtopic'] = isset($_POST['sfpostmsgtopic']);
	$sfpostmsg['sfpostmsgpost'] = isset($_POST['sfpostmsgpost']);
	$sfpostmsg['sfpostmsgtext2'] = SP()->saveFilters->text(trim($_POST['sfpostmsgtext2']));
	$sfpostmsg['sfpostmsgtopic2'] = isset($_POST['sfpostmsgtopic2']);
	$sfpostmsg['sfpostmsgpost2'] = isset($_POST['sfpostmsgpost2']);	
	SP()->options->update('sfpostmsg', $sfpostmsg);

	SP()->options->update('sfeditormsg', SP()->saveFilters->text(trim($_POST['sfeditormsg'])));

	# if set update, otherwise its empty, so remove
	if ($_POST['sfsneakpeek'] != '') {
		SP()->meta->add('sneakpeek', 'message', SP()->saveFilters->text(trim($_POST['sfsneakpeek'])));
	} else {
		$msg = SP()->meta->get('sneakpeek', 'message');
		if (!empty($msg)) SP()->meta->delete($msg[0]['meta_id']);
	}

	$sflogin = array();
	$sflogin = SP()->options->get('sflogin');
	$sflogin['sfsneakredirect'] = SP()->saveFilters->cleanurl($_POST['sfsneakredirect']);
	SP()->options->update('sflogin', $sflogin);

	# if set update, otherwise its empty, so remove
	if ($_POST['sfadminview'] != '') {
		SP()->meta->add('adminview', 'message', SP()->saveFilters->text(trim($_POST['sfadminview'])));
	} else {
		$msg = SP()->meta->get('adminview', 'message');
		if (!empty($msg)) SP()->meta->delete($msg[0]['meta_id']);
	}

	# if set update, otherwise its empty, so remove
	if ($_POST['sfuserview'] != '') {
		SP()->meta->add('userview', 'message', SP()->saveFilters->text(trim($_POST['sfuserview'])));
	} else {
		$msg = SP()->meta->get('userview', 'message');
		if (!empty($msg)) SP()->meta->delete($msg[0]['meta_id']);
	}

	do_action('sph_component_messages_save');

	$mess = SP()->primitives->admin_text('Custom messages updated');
	return $mess;
}

function sp_add_special_rank($userid, $rank) {
	$userid = (int) $userid;

	if (!SP()->user->special_ranks_col($userid, $rank)) {
		SP()->DB->execute('INSERT INTO '.SPSPECIALRANKS.' (user_id, special_rank) VALUES ('.$userid.', "'.$rank.'")');
	}
}

function sp_delete_special_rank($userid, $rank = '') {
	$userid = (int) $userid;

	$where = ' WHERE user_id='.$userid;
	if ($rank != '') $where .= ' AND special_rank="'.$rank.'"';
	SP()->DB->execute('DELETE FROM '.SPSPECIALRANKS.$where);
}
