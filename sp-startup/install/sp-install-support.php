<?php
/*
  Simple:Press
  Install & Upgrade Support Routines
  $LastChangedDate: 2016-12-28 17:30:33 +0000 (Wed, 28 Dec 2016) $
  $Rev: 14925 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================
#
# GLOBAL UPDATE/INSTALL ROUTINES
#
# ==========================================

# Called to log updates.

function sp_log_event($release, $version, $build, $user) {
	$now	 = current_time('mysql');
	# check if already an entry for this version
	$check	 = SP()->DB->table(SPLOG, "version='".SPVERSION."'");
	if ($check) {
		# we need an update query
		$sql = '
			UPDATE '.SPLOG." SET user_id=$user, install_date='$now',
			release_type='$release', build=$build WHERE version='".SPVERSION."'";
	} else {
		# we need an insert query
		$sql = '
			INSERT INTO '.SPLOG." (user_id, install_date, release_type, version, build)
			VALUES (
			$user,
			'$now',
			'$release',
			'$version',
			$build)";
	}
	SP()->DB->execute($sql);

	SP()->options->update('sfversion', $version);
	SP()->options->update('sfbuild', $build);
}

function sp_build_base_smileys() {
	$smileys = array(
		'Confused'	 => array(
			0	 => 'sf-confused.gif',
			1	 => ':???:',
			2	 => 1,
			3	 => 0,
			4	 => 0),
		'Cool'		 => array(
			0	 => 'sf-cool.gif',
			1	 => ':cool:',
			2	 => 1,
			3	 => 1,
			4	 => 0),
		'Cry'		 => array(
			0	 => 'sf-cry.gif',
			1	 => ':cry:',
			2	 => 1,
			3	 => 2,
			4	 => 0),
		'Embarassed' => array(
			0	 => 'sf-embarassed.gif',
			1	 => ':oops:',
			2	 => 1,
			3	 => 3,
			4	 => 0),
		'Frown'		 => array(
			0	 => 'sf-frown.gif',
			1	 => ':frown:',
			2	 => 1,
			3	 => 4,
			4	 => 0),
		'Kiss'		 => array(
			0	 => 'sf-kiss.gif',
			1	 => ':kiss:',
			2	 => 1,
			3	 => 5,
			4	 => 0),
		'Laugh'		 => array(
			0	 => 'sf-laugh.gif',
			1	 => ':lol:',
			2	 => 1,
			3	 => 6,
			4	 => 0),
		'Smile'		 => array(
			0	 => 'sf-smile.gif',
			1	 => ':smile:',
			2	 => 1,
			3	 => 7,
			4	 => 0),
		'Surprised'	 => array(
			0	 => 'sf-surprised.gif',
			1	 => ':eek:',
			2	 => 1,
			3	 => 8,
			4	 => 0),
		'Wink'		 => array(
			0	 => 'sf-wink.gif',
			1	 => ':wink:',
			2	 => 1,
			3	 => 9,
			4	 => 0),
		'Yell'		 => array(
			0	 => 'sf-yell.gif',
			1	 => ':yell:',
			2	 => 1,
			3	 => 10,
			4	 => 0)
	);

	SP()->meta->add('smileys', 'smileys', $smileys);
}

function sp_create_usergroup_meta($members) {
	global $wp_roles;

	$roles = array_keys($wp_roles->role_names);
	if ($roles) {
		foreach ($roles as $role) {
			SP()->meta->add('default usergroup', $role, $members); # initally set each role to members usergroup
		}
	}
}

function sp_install_members_table($subphase) {
	global $wpdb, $current_user;

	# get limits for installs
	$limit = ($subphase != 0) ? ' LIMIT 200 OFFSET '.(($subphase - 1) * 200) : '';

	# select all users
	$sql	 = 'SELECT ID FROM '.SPUSERS.$limit;
	$members = $wpdb->get_results($sql);

	if ($members) {
		foreach ($members as $member) {
			# Check ID exists and is not zero
			if (is_numeric($member->ID) && $member->ID > 0) {
				SP()->user->create_data($member->ID, true);

				# for the admin installer, remove any usergroup membership added by create member function
				if ($current_user->ID == $member->ID) $wpdb->query('DELETE FROM '.$wpdb->prefix."sfmemberships WHERE user_id=$member->ID");
			}
		}
	}
}

# 5.5.5 pre-create inspector array

function sp_create_inspectors() {
	$ins = array(
		'con_pageData'			 => 0,
		'con_forumData'			 => 0,
		'con_thisUser'		 => 0,
		'con_device'			 => 0,
		'gv_groups'		 => 0,
		'gv_thisGroup'		 => 0,
		'gv_thisForum'		 => 0,
		'gv_thisForumSubs'	 => 0,
		'fv_forums'		 => 0,
		'fv_thisForum'		 => 0,
		'fv_thisForumSubs'	 => 0,
		'fv_thisSubForum'		 => 0,
		'fv_thisTopic'		 => 0,
		'tv_topics'		 => 0,
		'tv_thisTopic'		 => 0,
		'tv_thisPost'			 => 0,
		'tv_thisPostUser'		 => 0,
		'mv_members'		 => 0,
		'mv_thisMemberGroup'	 => 0,
		'mv_thisMember'		 => 0,
		'tlv_listTopics'	 => 0,
		'tlv_thisListTopic'	 => 0,
		'plv_listPosts'	 => 0,
		'plv_thisListPost'	 => 0,
		'pro_profileUser'		 => 0,
		'q_GroupView'			 => 0,
		'q_GroupViewStats'	 => 0,
		'q_ForumView'			 => 0,
		'q_ForumViewStats'	 => 0,
		'q_TopicView'			 => 0,
		'q_MembersView'		 => 0,
		'q_ListTopicView'		 => 0,
		'q_ListTopicViewNew'	 => 0,
		'q_ListTopicViewFirst' => 0,
		'q_ListPostView'		 => 0,
		'q_search'		 => 0,
		'q_SearchView'		 => 0
	);
	SP()->options->update('spInspect', $ins);
}




function sp_install_iconsets() {
	
	
	
	$iconsets = array(
		'afiado',
		'brankic-1979',
		'broccolidry',
		'eighty-shades',
		'elegant-themes',
		'entypo',
		'feather',
		'fontawesome' => array(
			'active' => true
		),
		'hawcons',
		'iconic',
		'linecons',
		'material',
		'meteocons',
		'typicons',
		'wpzoom',
		'zondicons'
	);
	
	
	
	
	$sfconfig          = SP()->options->get('sfconfig');
 
	$iconsets_base_dir = SP_STORE_DIR . '/' . $sfconfig['iconsets'] . '/';
	
	
	foreach( $iconsets as $iconset_id => $iconset ) {
		
		$iconset_id = is_array( $iconset ) ? $iconset_id : $iconset;
		
		if( !is_array( $iconset ) ) {
			$iconset = array();
		}
		
		$default = array(
			'id' => $iconset_id,
			'active' => false
		);
		
		$iconset = wp_parse_args( $iconset, $default );
		
		spa_add_iconset( $iconset );
	}
	
	
	
}