<?php
/*
Simple:Press
Admin Forums Data Sae Support Functions
$LastChangedDate: 2018-11-02 11:09:55 -0500 (Fri, 02 Nov 2018) $
$Rev: 15787 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_forums_create_group() {
	check_admin_referer('forum-adminform_groupnew', 'forum-adminform_groupnew');

	$ug_list   = array_map('intval', array_unique($_POST['usergroup_id']));
	$perm_list = array_map('intval', $_POST['role']);

	$groupdata = array();

	$groupdata['group_seq'] = SP()->DB->maxNumber(SPGROUPS, 'group_seq') + 1;

	if (empty($_POST['group_name'])) {
		$groupdata['group_name'] = SP()->primitives->admin_text('New forum group');
	} else {
		$groupdata['group_name'] = SP()->saveFilters->title(trim($_POST['group_name']));
	}

	if (!empty($_POST['group_icon'])) {
		# Check new icon exists
		
		$group_icon = spa_get_selected_icon( $_POST['group_icon'] );
		$groupdata['group_icon'] = $group_icon;
		
		if( 'file' === $group_icon['type'] ) {
			$path                    = SPCUSTOMDIR.$groupdata['group_icon']['icon'];
			if (!file_exists($path)) {
				$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $groupdata['group_icon']['icon']);
				return $mess;
			}
		}
		
	} else {
		$groupdata['group_icon'] = null;
	}

	$groupdata['group_desc']    = SP()->saveFilters->text(trim($_POST['group_desc']));
	$groupdata['group_message'] = SP()->saveFilters->text(trim($_POST['group_message']));

	# create the group
	$sql = 'INSERT INTO '.SPGROUPS.' (group_name, group_desc, group_seq, group_icon, group_message) ';
	$sql .= "VALUES ('".$groupdata['group_name']."', '".$groupdata['group_desc']."', ".$groupdata['group_seq'].", '".$groupdata['group_icon']['value']."', '".$groupdata['group_message']."')";
	$success  = SP()->DB->execute($sql);
	$group_id = SP()->rewrites->pageData['insertid'];

	# save the default permissions for the group
	for ($x = 0; $x < count($ug_list); $x++) {
		if ($perm_list[$x] != -1) spa_add_defpermission_row($group_id, $ug_list[$x], $perm_list[$x]);
	}

	if ($success == false) {
		$mess = SP()->primitives->admin_text('New group creation failed');
	} else {
		$mess = SP()->primitives->admin_text('New group created');

		do_action('sph_forum_group_create', $group_id);
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_create_forum() {
	check_admin_referer('forum-adminform_forumnew', 'forum-adminform_forumnew');

	$forumdata = array();

	if ($_POST['forumtype'] == 1) {
		# Standard forum
		$forumdata['group_id'] = SP()->filters->integer($_POST['group_id']);
	} else {
		# Sub forum
		$parentforum           = SP()->DB->table(SPFORUMS, 'forum_id='.SP()->filters->integer($_POST['forum_id']), 'row');
		$forumdata['group_id'] = $parentforum->group_id;
	}

	$forumdata['forum_seq'] = (SP()->DB->maxNumber(SPFORUMS, 'forum_seq', 'group_id='.$forumdata['group_id']) + 1);

	$forumdata['forum_desc'] = SP()->saveFilters->text(trim($_POST['forum_desc']));

	$forumdata['forum_status'] = 0;
	if (isset($_POST['forum_status'])) $forumdata['forum_status'] = 1;

	$forumdata['forum_rss_private'] = 0;
	if (isset($_POST['forum_private'])) $forumdata['forum_rss_private'] = 1;

	if (empty($_POST['forum_name'])) {
		$forumdata['forum_name'] = SP()->primitives->admin_text('New forum');
	} else {
		$forumdata['forum_name'] = SP()->saveFilters->title(trim($_POST['forum_name']));
	}

	$forumdata['forum_keywords'] = SP()->saveFilters->title(trim($_POST['forum_keywords']));

	$forumdata['forum_message'] = SP()->saveFilters->text(trim($_POST['forum_message']));

	if (!empty($_POST['forum_icon'])) {
		# Check new icon exists
		
		$forum_icon = spa_get_selected_icon( $_POST['forum_icon'] );
		$forumdata['forum_icon'] = $forum_icon;
		
		$path                    = SPCUSTOMDIR.$forumdata['forum_icon']['icon'];
		
		if( 'file' === $forum_icon['type'] && !file_exists($path) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['forum_icon']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['forum_icon'] = null;
	}

	if (!empty($_POST['forum_icon_new'])) {
		# Check new icon exists
		
		$forum_icon_new = spa_get_selected_icon( $_POST['forum_icon_new'] );
		$forumdata['forum_icon_new'] = $forum_icon_new;
		
		$path                        = SPCUSTOMDIR.$forumdata['forum_icon_new']['icon'];
		if( 'file' === $forum_icon_new['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['forum_icon_new']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['forum_icon_new'] = null;
	}

	if (!empty($_POST['forum_icon_locked'])) {
		# Check new icon exists
		
		$forum_icon_locked = spa_get_selected_icon( $_POST['forum_icon_locked'] );
		$forumdata['forum_icon_locked'] = $forum_icon_locked;
		
		$path                           = SPCUSTOMDIR.$forumdata['forum_icon_locked']['icon'];
		if( 'file' === $forum_icon_locked['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['forum_icon_locked']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['forum_icon_locked'] = null;
	}

	if (!empty($_POST['topic_icon'])) {
		# Check new icon exists
		
		
		$topic_icon = spa_get_selected_icon( $_POST['topic_icon'] );
		$forumdata['topic_icon'] = $topic_icon;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon']['icon'];
		if( 'file' === $topic_icon['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon'] = null;
	}

	if (!empty($_POST['topic_icon_new'])) {
		# Check new icon exists
		
		$topic_icon_new = spa_get_selected_icon( $_POST['topic_icon_new'] );
		$forumdata['topic_icon_new'] = $topic_icon_new;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_new']['icon'];
		if( 'file' === $topic_icon_new['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_new']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_new'] = null;
	}

	if (!empty($_POST['topic_icon_locked'])) {
		# Check new icon exists
		
		$topic_icon_locked = spa_get_selected_icon( $_POST['topic_icon_locked'] );
		$forumdata['topic_icon_locked'] = $topic_icon_locked;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_locked']['icon'];
		if( 'file' === $topic_icon_locked['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_locked']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_locked'] = null;
	}

	if (!empty($_POST['topic_icon_pinned'])) {
		# Check new icon exists
		
		$topic_icon_pinned = spa_get_selected_icon( $_POST['topic_icon_pinned'] );
		$forumdata['topic_icon_pinned'] = $topic_icon_pinned;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_pinned']['icon'];
		if( 'file' === $topic_icon_pinned['type'] && !file_exists( $path ) ) {
			
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_pinned']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_pinned'] = null;
	}

	if (!empty($_POST['topic_icon_pinned_new'])) {
		# Check new icon exists
		
		$topic_icon_pinned_new = spa_get_selected_icon( $_POST['topic_icon_pinned_new'] );
		$forumdata['topic_icon_pinned_new'] = $topic_icon_pinned_new;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_pinned_new']['icon'];
		if( 'file' === $topic_icon_pinned_new['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_pinned_new']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_pinned_new'] = null;
	}

	if (!empty($_POST['feature_image'])) {
		# Check new image exists
		$forumdata['feature_image'] = SP()->saveFilters->title(trim($_POST['feature_image']));
		$path                       = SPOGIMAGEDIR.$forumdata['feature_image'];
		if (!file_exists($path)) {
			$mess = sprintf(SP()->primitives->admin_text('Featured Image %s does not exist'), $forumdata['feature_image']);

			return $mess;
		}
	} else {
		$forumdata['feature_image'] = null;
	}

	# create the forum
	if ($_POST['forumtype'] == 2) {
		$parentdata = $parentforum->forum_id;
	} else {
		$parentdata = '0';
	}

	# do slug
	if (!isset($_POST['thisforumslug']) || empty($_POST['thisforumslug'])) {
		$forumslug = sp_create_slug($forumdata['forum_name'], true, SPFORUMS, 'forum_slug');
		$forumslug = sp_create_slug($forumslug, true, SPWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
	} else {
		$forumslug = SP()->filters->str($_POST['thisforumslug']);
	}

	$sql = 'INSERT INTO '.SPFORUMS.' (forum_name, forum_slug, forum_desc, group_id, forum_status, forum_seq, forum_rss_private, forum_icon, forum_icon_new, forum_icon_locked, topic_icon, topic_icon_new, topic_icon_locked, topic_icon_pinned, topic_icon_pinned_new, feature_image, parent, forum_message, keywords) ';
	$sql .= "VALUES ('".$forumdata['forum_name']."', '".$forumslug."', '".$forumdata['forum_desc']."', ".$forumdata['group_id'].", ".$forumdata['forum_status'].", ".$forumdata['forum_seq'].", ".$forumdata['forum_rss_private'].", '".$forumdata['forum_icon']['value']."', '".$forumdata['forum_icon_new']['value']."', '".$forumdata['forum_icon_locked']['value']."', '".$forumdata['topic_icon']['value']."', '".$forumdata['topic_icon_new']['value']."', '".$forumdata['topic_icon_locked']['value']."', '".$forumdata['topic_icon_pinned']['value']."', '".$forumdata['topic_icon_pinned_new']['value']."', '".$forumdata['feature_image']."', ".$parentdata.", '".$forumdata['forum_message']."', '".$forumdata['forum_keywords']."');";
	$thisforum = SP()->DB->execute($sql);
	$forum_id  = SP()->rewrites->pageData['insertid'];

	# now check the slug was populated and if not replace with forum id
	if (empty($forumslug)) {
		$forumslug = 'forum-'.$forum_id;
		$thisforum = SP()->DB->execute('UPDATE '.SPFORUMS." SET forum_slug='$forumslug' WHERE forum_id=$forum_id");
	}
	$success = $thisforum;

	# add the user group permission sets
	$usergroup_id_list = array_map('intval', array_unique($_POST['usergroup_id']));
	$role_list         = array_map('intval', $_POST['role']);
	$perm_prob         = false;
	for ($x = 0; $x < count($usergroup_id_list); $x++) {
		$usergroup_id = $usergroup_id_list[$x];
		$role         = $role_list[$x];
		if ($role == -1) {
			$defrole = spa_get_defpermissions_role($forumdata['group_id'], $usergroup_id);
			if ($defrole == '') {
				$perm_prob = true;
			} else {
				spa_add_permission_data($forum_id, $usergroup_id, $defrole);
			}
		} else {
			spa_add_permission_data($forum_id, $usergroup_id, $role);
		}
	}

	# reset auths and memberships for everyone
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();

	# if the forum was created, signal success - doesnt check user group permission set though
	if ($success == false) {
		$mess = SP()->primitives->admin_text('New forum creation failed');
	} else {
		if ($perm_prob) {
			$mess = SP()->primitives->admin_text('New forum created but permission sets not set for all usergroups');
		} else {
			$mess = SP()->primitives->admin_text('New forum created');
		}

		do_action('sph_forum_forum_create', $forum_id);
	}

	spa_clean_forum_children();
	spa_resequence_forums($forumdata['group_id'], 0);

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	# forum and ancestor relationships have changed so rebuild the permallink slugs
	spa_build_forum_permalink_slugs();

	return $mess;
}

# function to add a permission set globally to all forum
function spa_save_forums_global_perm() {
	check_admin_referer('forum-adminform_globalpermissionnew', 'forum-adminform_globalpermissionnew');

	if ($_POST['usergroup_id'] != -1 && $_POST['role'] != -1) {
		$usergroup_id = SP()->filters->integer($_POST['usergroup_id']);
		$permission   = SP()->filters->integer($_POST['role']);

		# loop through all the groups
		$groups = SP()->DB->table(SPGROUPS, '', '', 'group_seq');
		if ($groups) {
			$mess = '';
			foreach ($groups as $group) {
				# use group permission set helper function to actually set the permission set
				$mess .= spa_set_group_permission($group->group_id, $usergroup_id, $permission);
			}

			# reset auths and memberships for everyone
			SP()->user->reset_memberships();
			SP()->auths->reset_cache();

			do_action('sph_forum_global_permission');
		} else {
			$mess = SP()->primitives->admin_text('There are no groups or gorums so no permission set was added');
		}
	} else {
		$mess = SP()->primitives->admin_text('Adding usergroup permission set failed');
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

# function to add a permission set to every forum within a group
function spa_save_forums_group_perm() {
	check_admin_referer('forum-adminform_grouppermissionnew', 'forum-adminform_grouppermissionnew');

	if (isset($_POST['group_id']) && $_POST['usergroup_id'] != -1 && $_POST['role'] != -1) {
		$group_id     = SP()->filters->integer($_POST['group_id']);
		$usergroup_id = SP()->filters->integer($_POST['usergroup_id']);
		$permission   = SP()->filters->integer($_POST['role']);

		# reset auths and memberships for everyone
		SP()->user->reset_memberships();
		SP()->auths->reset_cache();

		$mess = spa_set_group_permission($group_id, $usergroup_id, $permission);

		if (isset($_POST['adddef'])) {
			if (spa_get_defpermissions_role($group_id, $usergroup_id)) {
				$sql = 'UPDATE '.SPDEFPERMISSIONS."
						SET permission_role=$permission
						WHERE group_id=$group_id AND usergroup_id=$usergroup_id";
				SP()->DB->execute($sql);
			} else {
				if ($permission != -1) spa_add_defpermission_row($group_id, $usergroup_id, $permission);
			}
		}

		do_action('sph_forum_group_permission', $group_id);
	} else {
		$mess = SP()->primitives->admin_text('Adding usergroup permission set failed');
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

# helper function to loop through all forum in a group and add a permission set
function spa_set_group_permission($group_id, $usergroup_id, $permission) {
	$forums = spa_get_forums_in_group($group_id);

	if ($forums) {
		$mess = '';
		foreach ($forums as $forum) {
			# If user group has a current permission set for this forum, remove the old one before adding the new one
			$current = SP()->DB->table(SPPERMISSIONS, "forum_id=$forum->forum_id AND usergroup_id=$usergroup_id", 'row');

			if ($current) spa_remove_permission_data($current->permission_id);

			# add the new permission set
			$success = spa_add_permission_data($forum->forum_id, $usergroup_id, $permission);

			if ($success == false) {
				$mess .= SP()->displayFilters->title($forum->forum_name).': '.SP()->primitives->admin_text('Adding usergroup permission set failed').'<br />';
			} else {
				$mess .= SP()->displayFilters->title($forum->forum_name).': '.SP()->primitives->admin_text('Usergroup permission set added to forum').'<br />';
			}
		}
	} else {
		$mess = SP()->primitives->admin_text('Group has no forums so no permission sets were added');
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

# function to remove all permission set from all forum
function spa_save_forums_remove_perms() {
	check_admin_referer('forum-adminform_allpermissionsdelete', 'forum-adminform_allpermissionsdelete');

	# remove all permission set
	SP()->DB->truncate(SPPERMISSIONS);

	# reset auths and memberships for everyone
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();

	do_action('sph_forum_remove_perms');

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	$mess = SP()->primitives->admin_text('All permission sets removed');

	return $mess;
}

# function to add a new permission set to a forum
function spa_save_forums_forum_perm() {
	check_admin_referer('forum-adminform_permissionnew', 'forum-adminform_permissionnew');

	if (isset($_POST['forum_id']) && $_POST['usergroup_id'] != -1 && $_POST['role'] != -1) {
		$usergroup_id = SP()->filters->integer($_POST['usergroup_id']);
		$forum_id     = SP()->filters->integer($_POST['forum_id']);
		$permission   = SP()->filters->integer($_POST['role']);

		# If user group has a current permission set for this forum, remove the old one before adding the new one
		$current = SP()->DB->table(SPPERMISSIONS, "forum_id=$forum_id.AND usergroup_id=$usergroup_id", 'row');

		if ($current) spa_remove_permission_data($current->permission_id);

		# add the new permission set
		$success = spa_add_permission_data($forum_id, $usergroup_id, $permission);
		if ($success == false) {
			$mess = SP()->primitives->admin_text('Adding usergroup permission set failed');
		} else {
			$mess = SP()->primitives->admin_text('Usergroup permission set added to forum');

			# reset auths and permissions for everyone
			SP()->user->reset_memberships($usergroup_id);
			SP()->auths->reset_cache();

			do_action('sph_forum_perm_add', $forum_id, $usergroup_id, $permission);
		}
	} else {
		$mess = SP()->primitives->admin_text('Adding usergroup permission set failed');
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_delete_forum() {
	check_admin_referer('forum-adminform_forumdelete', 'forum-adminform_forumdelete');

	$group_id = SP()->filters->integer($_POST['group_id']);
	$forum_id = SP()->filters->integer($_POST['forum_id']);
	$cseq     = SP()->filters->integer($_POST['cforum_seq']);

	# If subforum or parent remove the relationship first.
	# Read the 'children' from the database because it is serialised

	$children = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'children');
	if ($children) {
		$children = unserialize($children);
		foreach ($children as $child) {
			SP()->DB->execute('UPDATE '.SPFORUMS.' SET parent=null WHERE forum_id='.SP()->filters->integer($child));
		}
	}

	# need to delete all topics in the forum using standard routine to clean up behind it
	$topics = SP()->DB->table(SPTOPICS, "forum_id=$forum_id");
	if ($topics) {
		foreach ($topics as $topic) {
			sp_delete_topic($topic->topic_id, $forum_id, false);
		}
	}

	# now delete the forum itself
	$thisForum = SP()->DB->table(SPFORUMS, "forum_id=$forum_id");
	SP()->DB->execute('DELETE FROM '.SPFORUMS." WHERE forum_id=$forum_id");

	# remove permissions for this forum
	$perms = sp_get_forum_permissions($forum_id);
	if ($perms) {
		foreach ($perms as $perm) {
			spa_remove_permission_data($perm->permission_id);
		}
	}

	# reset auths and memberships and pluginb data for everyone
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();
	SP()->memberData->reset_plugin_data();

	# need to iterate through the groups
	$forums = spa_get_forums_in_group($group_id);
	foreach ($forums as $forum) {
		if ($forum->forum_seq > $cseq) spa_bump_forum_seq($forum->forum_id, ($forum->forum_seq - 1));
	}

	$mess = 'Forum deleted';

	spa_clean_forum_children();
	spa_resequence_forums($group_id, 0);

	do_action('sph_forum_forum_del', $thisForum);

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	# forum and ancestor relationships may have changed so rebuild the permallink slugs
	spa_build_forum_permalink_slugs();

	return $mess;
}

function spa_save_forums_disable_forum() {
	check_admin_referer('forum-adminform_forumdisable', 'forum-adminform_forumdisable');

	$forum_id = SP()->filters->integer($_POST['forum_id']);

	$sql     = 'UPDATE '.SPFORUMS." SET forum_disabled=1 WHERE forum_id=$forum_id";
	$success = SP()->DB->execute($sql);
	// if disable parent forum, disable child. 
	$children = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'children');
	if($children){
		$children = unserialize($children);
		$sql     = 'UPDATE '.SPFORUMS." SET forum_disabled=1 WHERE forum_id=$children[0]";
		$success = SP()->DB->execute($sql);
	}

	if ($success) {
		$mess = SP()->primitives->admin_text('Forum disabled');
		do_action('sph_forum_forum_disable', $forum_id);
	} else {
		$mess = SP()->primitives->admin_text('Forum disable failed');
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_enable_forum() {
	check_admin_referer('forum-adminform_forumenable', 'forum-adminform_forumenable');

	$forum_id = SP()->filters->integer($_POST['forum_id']);

	$sql     = 'UPDATE '.SPFORUMS." SET forum_disabled=0 WHERE forum_id=$forum_id";
	$success = SP()->DB->execute($sql);

	// if enable child, enable parent as well
	$parent = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'parent');
	if($parent){
		$sql     = 'UPDATE '.SPFORUMS." SET forum_disabled=0 WHERE forum_id=$parent";
		$success = SP()->DB->execute($sql);
	}

	if ($success) {
		$mess = SP()->primitives->admin_text('Forum enabled');
		do_action('sph_forum_forum_enable', $forum_id);
	} else {
		$mess = SP()->primitives->admin_text('Forum enable failed');
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_delete_group() {
	check_admin_referer('forum-adminform_groupdelete', 'forum-adminform_groupdelete');

	$group_id = SP()->filters->integer($_POST['group_id']);
	$cseq     = SP()->filters->integer($_POST['cgroup_seq']);

	# remove permissions for each forum in group
	$forums = spa_get_forums_in_group($group_id);
	if ($forums) {
		foreach ($forums as $forum) {
			# remove permissions for this forum
			$perms = sp_get_forum_permissions($forum->forum_id);
			if ($perms) {
				foreach ($perms as $perm) {
					spa_remove_permission_data($perm->permission_id);
				}
			}
		}
	}

	# reset auths and memberships for everyone
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();

	# select all the forums in the group
	$forums = spa_get_forums_in_group($group_id);

	# remove the topics and posts in each forum
	foreach ($forums as $forum) {
		# need to delete all topics in the forum using standard routine to clean up behind it
		$topics = SP()->DB->table(SPTOPICS, "forum_id=$forum->forum_id");
		if ($topics) {
			foreach ($topics as $topic) {
				sp_delete_topic($topic->topic_id, $forum->forum_id, false);
			}
		}
	}

	#now remove the forums themselves
	SP()->DB->execute('DELETE FROM '.SPFORUMS." WHERE group_id=$group_id");

	# and finaly remove the group
	SP()->DB->execute('DELETE FROM '.SPGROUPS." WHERE group_id=$group_id");

	# need to iterate through the groups
	$groups = SP()->DB->table(SPGROUPS, '', '', 'group_seq');
	foreach ($groups as $group) {
		if ($group->group_seq > $cseq) spa_bump_group_seq($group->group_id, ($group->group_seq - 1));
	}

	# remove the default permissions for the group being deleted
	SP()->DB->execute('DELETE FROM '.SPDEFPERMISSIONS." WHERE group_id=$group_id");

	do_action('sph_forum_group_del', $group_id);

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	$mess = SP()->primitives->admin_text('Group Deleted');

	return $mess;
}

# function to delete an existing permission set for a forum
function spa_save_forums_delete_perm() {
	check_admin_referer('forum-adminform_permissiondelete', 'forum-adminform_permissiondelete');

	$permission_id = SP()->filters->integer($_POST['permission_id']);

	# remove the permission set from the forum
	$success = spa_remove_permission_data($permission_id);
	if ($success == false) {
		$mess = SP()->primitives->admin_text('Permission set delete failed');
	} else {
		$mess = SP()->primitives->admin_text('Permission set deleted');

		# reset auths and memberships for everyone
		SP()->user->reset_memberships();
		SP()->auths->reset_cache();

		do_action('sph_forum_perm_del', $permission_id);
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_edit_forum() {
	check_admin_referer('forum-adminform_forumedit', 'forum-adminform_forumedit');

	$forumdata = array();

	$forumdata['group_id'] = SP()->filters->integer($_POST['group_id']);

	if ($_POST['cparent'] == 0) {
		$forumdata['parent'] = 0;
	} else {
		$forumdata['parent'] = SP()->filters->integer($_POST['parent']);
	}

	if ($forumdata['parent'] != $_POST['cparent']) {
		$forumdata['group_id'] = SP()->DB->table(SPFORUMS, 'forum_id='.$forumdata['parent'], 'group_id');
	}

	$forum_id                = SP()->filters->integer($_POST['forum_id']);
	$forumdata['forum_name'] = SP()->saveFilters->title(trim($_POST['forum_name']));
	if (!empty($_POST['cforum_slug'])) {
		$forumdata['forum_slug'] = sp_create_slug($_POST['cforum_slug'], false);
	} else {
		$forumdata['forum_slug'] = sp_create_slug($forumdata['forum_name'], true, SPFORUMS, 'forum_slug');
		$forumdata['forum_slug'] = sp_create_slug($forumdata['forum_slug'], true, SPWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
	}
	$forumdata['forum_desc'] = SP()->saveFilters->text(trim($_POST['forum_desc']));

	$forumdata['forum_status'] = 0;
	if (isset($_POST['forum_status'])) $forumdata['forum_status'] = 1;

	$forumdata['forum_rss_private'] = 0;
	if (isset($_POST['forum_private'])) $forumdata['forum_rss_private'] = 1;

	$forumdata['forum_keywords'] = SP()->saveFilters->title(trim($_POST['forum_keywords']));

	if (!empty($_POST['forum_icon'])) {
		# Check new icon exists
		
		$forum_icon = spa_get_selected_icon( $_POST['forum_icon'] );
		$forumdata['forum_icon'] = $forum_icon;
		
		$path                           = SPCUSTOMDIR.$forumdata['forum_icon']['icon'];
		if( 'file' === $forum_icon['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['forum_icon']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['forum_icon'] = null;
	}

	if (!empty($_POST['forum_icon_new'])) {
		# Check new icon exists
		
		$forum_icon_new = spa_get_selected_icon( $_POST['forum_icon_new'] );
		$forumdata['forum_icon_new'] = $forum_icon_new;
		
		$path                           = SPCUSTOMDIR.$forumdata['forum_icon_new']['icon'];
		if( 'file' === $forum_icon_new['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['forum_icon_new']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['forum_icon_new'] = null;
	}

	if (!empty($_POST['forum_icon_locked'])) {
		# Check new icon exists
		
		$forum_icon_locked = spa_get_selected_icon( $_POST['forum_icon_locked'] );
		$forumdata['forum_icon_locked'] = $forum_icon_locked;
		
		$path                           = SPCUSTOMDIR.$forumdata['forum_icon_locked']['icon'];
		if( 'file' === $forum_icon_locked['type'] && !file_exists( $path ) ) {
			
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['forum_icon_locked']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['forum_icon_locked'] = null;
	}

	if (!empty($_POST['topic_icon'])) {
		# Check new icon exists
		
		$topic_icon = spa_get_selected_icon( $_POST['topic_icon'] );
		$forumdata['topic_icon'] = $topic_icon;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon']['icon'];
		if( 'file' === $topic_icon['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon'] = null;
	}

	if (!empty($_POST['topic_icon_new'])) {
		# Check new icon exists
		$topic_icon_new = spa_get_selected_icon( $_POST['topic_icon_new'] );
		$forumdata['topic_icon_new'] = $topic_icon_new;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_new']['icon'];
		if( 'file' === $topic_icon_new['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_new']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_new'] = null;
	}

	if (!empty($_POST['topic_icon_locked'])) {
		# Check new icon exists
		$topic_icon_locked = spa_get_selected_icon( $_POST['topic_icon_locked'] );
		$forumdata['topic_icon_locked'] = $topic_icon_locked;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_locked']['icon'];
		if( 'file' === $topic_icon_locked['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_locked']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_locked'] = null;
	}

	if (!empty($_POST['topic_icon_pinned'])) {
		# Check new icon exists
		$topic_icon_pinned = spa_get_selected_icon( $_POST['topic_icon_pinned'] );
		$forumdata['topic_icon_pinned'] = $topic_icon_pinned;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_pinned']['icon'];
		if( 'file' === $topic_icon_pinned['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_pinned']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_pinned'] = null;
	}

	if (!empty($_POST['topic_icon_pinned_new'])) {
		# Check new icon exists
		$topic_icon_pinned_new = spa_get_selected_icon( $_POST['topic_icon_pinned_new'] );
		$forumdata['topic_icon_pinned_new'] = $topic_icon_pinned_new;
		
		$path                           = SPCUSTOMDIR.$forumdata['topic_icon_pinned_new']['icon'];
		if( 'file' === $topic_icon_pinned_new['type'] && !file_exists( $path ) ) {
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $forumdata['topic_icon_pinned_new']['icon'] );

			return $mess;
		}
	} else {
		$forumdata['topic_icon_pinned_new'] = null;
	}

	if (!empty($_POST['feature_image'])) {
		# Check new icon exists
		$forumdata['feature_image'] = SP()->saveFilters->title(trim($_POST['feature_image']));
		$path                       = SPOGIMAGEDIR.$forumdata['feature_image'];
		if (!file_exists($path)) {
			$mess = sprintf(SP()->primitives->admin_text('Featured Image %s does not exist'), $forumdata['feature_image']);

			return $mess;
		}
	} else {
		$forumdata['feature_image'] = null;
	}

	if (isset($_POST['forum_rss'])) {
		$forumdata['forum_rss'] = SP()->saveFilters->cleanurl($_POST['forum_rss']);
	} else {
		$forumdata['forum_rss'] = SP()->saveFilters->cleanurl($_POST['cforum_rss']);
	}

	$forumdata['forum_message'] = SP()->saveFilters->text(trim($_POST['forum_message']));

	# has the forum changed to a new group
	if (($forumdata['group_id'] != $_POST['cgroup_id']) && (!empty($_POST['cchildren']))) {
		spa_update_parent_group(SP()->filters->integer($_POST['cgroup_id']), $forumdata['group_id'], $forum_id);
	}

	# Finally - we can save the updated forum record!
	if (empty($forumdata['forum_slug'])) {
		$forumslug = sp_create_slug($forumdata['forum_name'], true, SPFORUMS, 'forum_slug');
		$forumslug = sp_create_slug($forumslug, true, SPWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
		if (empty($forumslug)) $forumslug = 'forum-'.$forum_id;
	} else {
		$forumslug = $forumdata['forum_slug'];
	}

	$sql = 'UPDATE '.SPFORUMS.' SET ';
	$sql .= 'forum_name="'.$forumdata['forum_name'].'", ';
	$sql .= 'forum_slug="'.$forumslug.'", ';
	$sql .= 'forum_desc="'.$forumdata['forum_desc'].'", ';
	$sql .= 'group_id='.$forumdata['group_id'].', ';
	$sql .= 'forum_status='.$forumdata['forum_status'].', ';
	$sql .= 'forum_rss_private='.$forumdata['forum_rss_private'].', ';
	$sql .= 'forum_icon="'.$forumdata['forum_icon']['value'].'", ';
	$sql .= 'forum_icon_new="'.$forumdata['forum_icon_new']['value'].'", ';
	$sql .= 'forum_icon_locked="'.$forumdata['forum_icon_locked']['value'].'", ';
	$sql .= 'topic_icon="'.$forumdata['topic_icon']['value'].'", ';
	$sql .= 'topic_icon_new="'.$forumdata['topic_icon_new']['value'].'", ';
	$sql .= 'topic_icon_locked="'.$forumdata['topic_icon_locked']['value'].'", ';
	$sql .= 'topic_icon_pinned="'.$forumdata['topic_icon_pinned']['value'].'", ';
	$sql .= 'topic_icon_pinned_new="'.$forumdata['topic_icon_pinned_new']['value'].'", ';
	$sql .= 'feature_image="'.$forumdata['feature_image'].'", ';
	$sql .= 'forum_rss="'.$forumdata['forum_rss'].'", ';
	$sql .= 'parent='.$forumdata['parent'].', ';
	$sql .= 'forum_message="'.$forumdata['forum_message'].'", ';
	$sql .= 'keywords="'.$forumdata['forum_keywords'].'" ';
	$sql .= "WHERE forum_id=$forum_id";
	$success = SP()->DB->execute($sql);
	if ($success == false) {
		$mess = SP()->primitives->admin_text('Forum record update failed');
	} else {
		if ($forumdata['parent'] != $_POST['cparent']) {
			spa_clean_forum_children();
		}
		$mess = SP()->primitives->admin_text('Forum record update');
		do_action('sph_forum_forum_edit', $forum_id);
	}

	# if the slug as changed we can try and update internal links in posts
	if ($_POST['cforum_slug'] != $forumslug) {
		sp_update_post_urls(SP()->filters->str($_POST['cforum_slug']), $forumslug);
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	# forum and ancestor relationships may have changed so rebuild the permallink slugs
	spa_build_forum_permalink_slugs();

	return $mess;
}

function spa_save_forums_edit_group() {
	check_admin_referer('forum-adminform_groupedit', 'forum-adminform_groupedit');

	$groupdata                  = array();
	$group_id                   = SP()->filters->integer($_POST['group_id']);
	$groupdata['group_name']    = SP()->saveFilters->title(trim($_POST['group_name']));
	$groupdata['group_desc']    = SP()->saveFilters->text(trim($_POST['group_desc']));
	$groupdata['group_message'] = SP()->saveFilters->text(trim($_POST['group_message']));

	$ug_list   = array_map('intval', array_unique($_POST['usergroup_id']));
	$perm_list = array_map('intval', $_POST['role']);

	if (!empty($_POST['group_icon'])) {
		# Check new icon exists
		
		$group_icon = spa_get_selected_icon( $_POST['group_icon'] );
		$groupdata['group_icon'] = $group_icon;
		
		$path                           = SPCUSTOMDIR.$groupdata['group_icon']['icon'];
		
		if( 'file' === $group_icon['type'] && !file_exists( $path ) ) {
		
			$mess = sprintf(SP()->primitives->admin_text('Custom icon %s does not exist'), $groupdata['group_icon']['icon'] );

			return $mess;
		}
	} else {
		$groupdata['group_icon'] = null;
	}

	if (isset($_POST['group_rss'])) {
		$groupdata['group_rss'] = SP()->saveFilters->cleanurl($_POST['group_rss']);
	} else {
		$groupdata['group_rss'] = SP()->saveFilters->cleanurl($_POST['cgroup_rss']);
	}

	# save the default permissions for the group
	for ($x = 0; $x < count($ug_list); $x++) {
		$ug   = $ug_list[$x];
		$perm = $perm_list[$x];
		if (spa_get_defpermissions_role($group_id, $ug)) {
			$sql = 'UPDATE '.SPDEFPERMISSIONS."
					SET permission_role=$perm
					WHERE group_id=$group_id AND usergroup_id=$ug";
			SP()->DB->execute($sql);
		} else {
			if ($perm != -1) spa_add_defpermission_row($group_id, $ug, $perm);
		}
	}

	if ($groupdata['group_name'] == sanitize_text_field($_POST['cgroup_name']) &&
		$groupdata['group_desc'] == sanitize_text_field($_POST['cgroup_desc']) &&
		$groupdata['group_rss'] == sanitize_text_field($_POST['cgroup_rss']) &&
		$groupdata['group_message'] == sanitize_text_field($_POST['cgroup_message']) &&
		$groupdata['group_icon']['value'] == sanitize_text_field($_POST['cgroup_icon'])) {
		$mess = SP()->primitives->admin_text('No data changed');
	} else {
		$sql = 'UPDATE '.SPGROUPS.' SET ';
		$sql .= 'group_name="'.$groupdata['group_name'].'", ';
		$sql .= 'group_desc="'.$groupdata['group_desc'].'", ';
		$sql .= 'group_icon="'.$groupdata['group_icon']['value'].'", ';
		$sql .= 'group_rss="'.$groupdata['group_rss'].'", ';
		$sql .= 'group_message="'.$groupdata['group_message'].'" ';
		$sql .= "WHERE group_id=$group_id";
		$success = SP()->DB->execute($sql);
		if ($success == false) {
			$mess = SP()->primitives->admin_text('Group record update failed');

			do_action('sph_forum_group_edit', $group_id);
		} else {
			$mess = SP()->primitives->admin_text('Forum group record updated');
		}
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

# function to update an existing permission set for a forum
function spa_save_forums_edit_perm() {
	check_admin_referer('forum-adminform_permissionedit', 'forum-adminform_permissionedit');

	$permissiondata                    = array();
	$permission_id                     = SP()->filters->integer($_POST['permission_id']);
	$permissiondata['permission_role'] = SP()->filters->integer($_POST['role']);

	# dont do anything if the permission set wasnt actually updated
	if ($permissiondata['permission_role'] == sanitize_text_field($_POST['ugroup_perm'])) {
		$mess = SP()->primitives->admin_text('No data changed');

		return $mess;
	}

	# save the updated permission set info
	$sql = 'UPDATE '.SPPERMISSIONS.' SET ';
	$sql .= 'permission_role="'.$permissiondata['permission_role'].'" ';
	$sql .= "WHERE permission_id=$permission_id";
	$success = SP()->DB->execute($sql);
	if ($success == false) {
		$mess = SP()->primitives->admin_text('Permission set update failed');
	} else {
		$mess = SP()->primitives->admin_text('Permission set updated');

		# reset auths and memberships for everyone
		SP()->user->reset_memberships();
		SP()->auths->reset_cache();

		do_action('sph_forum_perm_edit', $permission_id);
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_bump_group_seq($id, $seq) {
	$sql = 'UPDATE '.SPGROUPS.' SET ';
	$sql .= "group_seq=$seq ";
	$sql .= "WHERE group_id=$id";

	SP()->DB->execute($sql);
}

function spa_bump_forum_seq($id, $seq) {
	$sql = 'UPDATE '.SPFORUMS.' SET ';
	$sql .= "forum_seq=$seq ";
	$sql .= "WHERE forum_id=$id";

	SP()->DB->execute($sql);
}

function spa_add_permission_data($forum_id, $usergroup_id, $permission) {
	$forumid     = SP()->filters->esc_sql($forum_id);
	$usergroupid = SP()->filters->esc_sql($usergroup_id);
	$perm        = SP()->filters->esc_sql($permission);

	$sql = 'INSERT INTO '.SPPERMISSIONS.' (forum_id, usergroup_id, permission_role) ';
	$sql .= "VALUES ('$forumid', '$usergroupid', '$perm')";

	return SP()->DB->execute($sql);
}

function spa_add_defpermission_row($group_id, $usergroup_id, $role) {
	$sql = 'INSERT INTO '.SPDEFPERMISSIONS." (group_id, usergroup_id, permission_role)
			VALUES ($group_id, $usergroup_id, $role)";

	return SP()->DB->execute($sql);
}

function spa_resequence_forums($groupid, $parent) {
	global $sequence;

	$forums = spa_get_group_forums_by_parent($groupid, $parent);
	if ($forums) {
		foreach ($forums as $forum) {
			$sequence++;
			spa_bump_forum_seq($forum->forum_id, $sequence);

			if ($forum->children) {
				$childlist = array(unserialize($forum->children));
				if (count($childlist) > 0) spa_resequence_forums($groupid, $forum->forum_id);
			}
		}
	}
}

function spa_clean_forum_children() {
	# Remove all child records from forums
	SP()->DB->execute('UPDATE '.SPFORUMS.' set children=""');

	# Now get ALL forums
	$forums = SP()->DB->table(SPFORUMS);
	if ($forums) {
		foreach ($forums as $forum) {
			if ($forum->parent != 0) {
				$query         = new stdClass;
				$query->table  = SPFORUMS;
				$query->fields = 'children, group_id';
				$query->where  = 'forum_id='.$forum->parent;
				$childlist     = SP()->DB->select($query);

				if (!empty($childlist[0]->children)) {
					$children = unserialize($childlist[0]->children);
				} else {
					$children = array();
				}
				$children[] = $forum->forum_id;
				SP()->DB->execute('UPDATE '.SPFORUMS." set children='".serialize($children)."' WHERE forum_id=$forum->parent");
				SP()->DB->execute('UPDATE '.SPFORUMS." set group_id=".$childlist[0]->group_id." WHERE forum_id=$forum->forum_id");
			}
		}
	}
}

function spa_save_forums_global_rss() {
	check_admin_referer('forum-adminform_globalrss', 'forum-adminform_globalrss');

	# update the globla rss replacement url
	SP()->options->update('sfallRSSurl', SP()->saveFilters->cleanurl($_POST['sfallrssurl']));
	$mess = SP()->primitives->admin_text('Global RSS settings updated');

	do_action('sph_forum_global_rss');

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_global_rssset() {
	check_admin_referer('forum-adminform_globalrssset', 'forum-adminform_globalrssset');

	$private = SP()->filters->integer($_POST['sfglobalrssset']);

	$sql = 'UPDATE '.SPFORUMS.' SET ';
	$sql .= "forum_rss_private=$private";
	SP()->DB->execute($sql);

	do_action('sph_forum_rss');

	$mess = SP()->primitives->admin_text('Global RSS settings updated');

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	return $mess;
}

function spa_save_forums_merge() {
	check_admin_referer('forum-adminform_mergeforums', 'forum-adminform_mergeforums');
	$source = $target = 0;
	if (isset($_POST['source'])) $source = (int) $_POST['source'];
	if (isset($_POST['target'])) $target = (int) $_POST['target'];
	if (empty($source) || empty($target) || ($source == $target)) {
		return SP()->primitives->admin_text('Selections invalid');
	}

	$sourceForum = SP()->DB->table(SPFORUMS, "forum_id=$source", 'row');
	$targetForum = SP()->DB->table(SPFORUMS, "forum_id=$target", 'row');

	# 1 - Move sub-forums
	if (!empty($sourceForum->children)) {
		SP()->DB->execute("UPDATE ".SPFORUMS." SET parent=$target WHERE parent=$source");
	}

	# 2 - Change forum ids in requirted tables
	SP()->DB->execute("UPDATE ".SPTOPICS." SET forum_id=$target WHERE forum_id=$source");
	SP()->DB->execute("UPDATE ".SPPOSTS." SET forum_id=$target WHERE forum_id=$source");
	SP()->DB->execute("UPDATE ".SPTRACK." SET forum_id=$target WHERE forum_id=$source");
	SP()->DB->execute("UPDATE ".SPWAITING." SET forum_id=$target WHERE forum_id=$source");

	# 3 - Delete forum id rows in following tables
	SP()->DB->execute("DELETE FROM ".SPPERMISSIONS." WHERE forum_id=$source");

	# 4 - Run clean up operations
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();
	sp_update_post_urls($sourceForum->forum_slug, $targetForum->forum_slug);
	sp_build_forum_index($target);

	# 5 - Delete the old forum record
	SP()->DB->execute("DELETE FROM ".SPFORUMS." WHERE forum_id=$source");
	spa_clean_forum_children();
	spa_resequence_forums($targetForum->group_id, 0);

	# 6 - Update Sitemap
	do_action('sm_rebuild');

	# 7 - Update Stats
	do_action('sph_stats_cron');

	# 8 - Let plugins in on the secret
	do_action('sph_merge_forums', $source, $target);

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	# forum and ancestor relationships may have changed so rebuild the permallink slugs
	spa_build_forum_permalink_slugs();

	$mess = SP()->primitives->admin_text('Forum Merge Completed');

	return $mess;
}

function spa_save_forums_order() {
	check_admin_referer('forum-adminform_forumorder', 'forum-adminform_forumorder');

	# get the sorted lst
	parse_str(SP()->filters->url($_POST['spForumsOrder']), $list);

	# make sure we have groups
	if (empty($list['group'])) return SP()->primitives->admin_text('Unable to save group/forum ordering');

	if ((int) $_POST['cgroup'] == 0) {
		# save group sequence
		$gseq = 1;
		foreach ($list['group'] as $gid => $group) {
			$gid = ltrim($gid, 'G');
			$sql = 'UPDATE '.SPGROUPS." SET group_seq=$gseq WHERE group_id=$gid";
			SP()->DB->execute($sql);
			$gseq++;
		}
	}

	# bail if we dont have any forums
	if (empty($list['forum'])) return SP()->primitives->admin_text('Groups have been reordered');

	# save forum sequence
	$group = 0;
	foreach ($list['forum'] as $id => $parent) {
		# check parent for group or forum
		if (substr($parent, 0, 1) == 'G') {
			$id     = ltrim($id, 'F');
			$parent = ltrim($parent, 'G');
			# restart forum sequence if new group id
			if ($group != $parent) {
				$fseq  = 1;
				$group = $parent;
			}

			$sql = 'UPDATE '.SPFORUMS." SET group_id=$parent, forum_seq=$fseq, parent=0, children='' WHERE forum_id=$id";
			SP()->DB->execute($sql);
		} else { # forum
			$id     = ltrim($id, 'F');
			$parent = ltrim($parent, 'F');

			$sql = 'UPDATE '.SPFORUMS." SET group_id=$group, forum_seq=$fseq, parent=$parent, children='' WHERE forum_id=$id";
			SP()->DB->execute($sql);

			# get all children for the parent forum
			$children = array();
			foreach ($list['forum'] as $cid => $cparent) {
				if (substr($cparent, 0, 1) == 'F' && substr($cparent, 1) == $parent) {
					$children[] = substr($cid, 1);
				}
			}
			$children = serialize($children);
			# update the parent with children info since there is at least one child
			$sql = 'UPDATE '.SPFORUMS." SET children='$children' WHERE forum_id=$parent";
			SP()->DB->execute($sql);
		}
		$fseq++;
	}

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	# forum and ancestor relationships may have changed so rebuild the permallink slugs
	spa_build_forum_permalink_slugs();

	# done, output message
	return SP()->primitives->admin_text('Groups and forums have been reordered');
}

function spa_update_parent_group($currentGroupId, $newGroupId, $forumParent) {
	$forums = SP()->DB->table(SPFORUMS, "group_id=$currentGroupId AND parent=$forumParent");
	if ($forums) {
		foreach ($forums as $forum) {
			# update the old group ID to new one
			$sql = 'UPDATE '.SPFORUMS." SET group_id=$newGroupId WHERE forum_id=$forum->forum_id";
			SP()->DB->execute($sql);
			if ($forum->children) {
				$childlist = array(unserialize($forum->children));
				if (count($childlist) > 0) spa_update_parent_group($currentGroupId, $newGroupId, $forum->forum_id);
			}
		}
	}
}

function spa_delete_sample($group_id) {
	check_admin_referer('forum-adminform_groupdelete', 'forum-adminform_groupdelete');

	# remove permissions for each forum in group
	$forums = spa_get_forums_in_group($group_id);
	if ($forums) {
		foreach ($forums as $forum) {
			# remove permissions for this forum
			$perms = sp_get_forum_permissions($forum->forum_id);
			if ($perms) {
				foreach ($perms as $perm) {
					spa_remove_permission_data($perm->permission_id);
				}
			}
		}
	}

	# reset auths and memberships for everyone
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();

	# select all the forums in the group
	$forums = spa_get_forums_in_group($group_id);

	# remove the topics and posts in each forum
	foreach ($forums as $forum) {
		# need to delete all topics in the forum using standard routine to clean up behind it
		$topics = SP()->DB->table(SPTOPICS, "forum_id=$forum->forum_id");
		if ($topics) {
			foreach ($topics as $topic) {
				sp_delete_topic($topic->topic_id, $forum->forum_id, false);
			}
		}
	}

	#now remove the forums themselves
	SP()->DB->execute('DELETE FROM '.SPFORUMS." WHERE group_id=$group_id");

	# and finaly remove the group
	SP()->DB->execute('DELETE FROM '.SPGROUPS." WHERE group_id=$group_id");

	# remove the default permissions for the group being deleted
	SP()->DB->execute('DELETE FROM '.SPDEFPERMISSIONS." WHERE group_id=$group_id");

	# clear out group cache tpo enable change_user
	SP()->cache->flush('group');

	require_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';
	$counts = sp_get_stats_counts();
	SP()->options->update('spForumStats', $counts);
}
