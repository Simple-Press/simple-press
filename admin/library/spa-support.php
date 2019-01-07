<?php
/*
Simple:Press
Admin Support Routines
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_forums_in_group($groupid) {
	return spdb_table(SFFORUMS, "group_id=$groupid", '', 'forum_seq');
}

function spa_get_group_forums_by_parent($groupid, $parentid) {
	return spdb_table(SFFORUMS, "group_id=$groupid AND parent=$parentid", '', 'forum_seq');
}

function spa_get_forums_all() {
	return spdb_select('set',
		'SELECT forum_id, forum_name, '.SFGROUPS.'.group_id, group_name
		 FROM '.SFFORUMS.'
		 JOIN '.SFGROUPS.' ON '.SFFORUMS.'.group_id = '.SFGROUPS.'.group_id
		 ORDER BY group_seq, forum_seq');
}

function spa_create_group_select($groupid=0, $label=false) {
	$groups = spdb_table(SFGROUPS, '', '', 'group_seq');
	$out = '';
	$default = '';

	if ($groups) {
		if ($label) {
			$out.= '<option value="">'.spa_text('Select forum group:').'</option>';
		}
		foreach ($groups as $group) {
			if ($group->group_id == $groupid) {
				$default = 'selected="selected" ';
			} else {
				$default = null;
			}
			$out.= '<option '.$default.'value="'.$group->group_id.'">'.sp_filter_title_display($group->group_name).'</option>'."\n";
			$default='';
		}
	}
	return $out;
}

function spa_create_forum_select($forumid) {
	$forums = spa_get_forums_all();
	$out = '';
	if ($forums) {
		foreach ($forums as $forum) {
			if ($forum->forum_id == $forumid) {
				$default = 'selected="selected" ';
			} else {
				$default = '';
			}
			$out.= '<option '.$default.'value="'.$forum->forum_id.'">'.sp_filter_title_display($forum->forum_name).'</option>'."\n";
			$default='';
		}
	}
	return $out;
}

function spa_update_check_option($key) {
	if (isset($_POST[$key])) {
		sp_update_option($key, true);
	} else {
		sp_update_option($key, false);
	}
}

function spa_get_usergroups_all($usergroupid=null) {
	$where = '';
	if (!is_null($usergroupid)) $where = "usergroup_id=$usergroupid";
	return spdb_table(SFUSERGROUPS, $where);
}

function spa_get_usergroups_row($usergroup_id) {
	return spdb_table(SFUSERGROUPS, "usergroup_id=$usergroup_id", 'row');
}

function spa_create_usergroup_row($usergroupname, $usergroupdesc, $usergroupbadge, $usergroupjoin, $hide_stats, $usergroupismod, $report_failure=false) {
	global $spVars;

	# first check to see if user group name exists
	$exists = spdb_table(SFUSERGROUPS, "usergroup_name='$usergroupname'", 'usergroup_id');
	if ($exists) {
		if ($report_failure == true) {
			return false;
		} else {
			return $exists;
		}
	}

	# go on and create the new user group
	$sql = 'INSERT INTO '.SFUSERGROUPS.' (usergroup_name, usergroup_desc, usergroup_badge, usergroup_join, hide_stats, usergroup_is_moderator) ';
	$sql.= "VALUES ('$usergroupname', '$usergroupdesc', '$usergroupbadge', '$usergroupjoin', '$hide_stats', '$usergroupismod')";

	if (spdb_query($sql)) {
		return $spVars['insertid'];
	} else {
		return false;
	}
}


function spa_remove_permission_data($permission_id) {
	return spdb_query('DELETE FROM '.SFPERMISSIONS." WHERE permission_id=$permission_id");
}


function spa_create_role_row($role_name, $role_desc, $auths, $report_failure=false) {
	global $spVars;

	# first check to see if rolename exists
	$exists = spdb_table(SFROLES, "role_name='$role_name'", 'role_id');
	if ($exists) {
		if ($report_failure == true) {
			return false;
		} else {
			return $exists;
		}
	}

	# go on and create the new role
	$sql = 'INSERT INTO '.SFROLES.' (role_name, role_desc, role_auths) ';
	$sql.= "VALUES ('$role_name', '$role_desc', '$auths')";

	if (spdb_query($sql)) {
		return $spVars['insertid'];
	} else {
		return false;
	}
}

function spa_get_role_row($role_id) {
	return spdb_table(SFROLES, "role_id=$role_id", 'row');
}

function spa_get_defpermissions($group_id) {
	return spdb_select('set', '
		SELECT permission_id, '.SFUSERGROUPS.'.usergroup_id, permission_role, usergroup_name
		FROM '.SFDEFPERMISSIONS.'
		JOIN '.SFUSERGROUPS.' ON '.SFDEFPERMISSIONS.'.usergroup_id = '.SFUSERGROUPS.".usergroup_id
		WHERE group_id=$group_id");
}

function spa_get_defpermissions_role($group_id, $usergroup_id) {
	return spdb_table(SFDEFPERMISSIONS, "group_id=$group_id AND usergroup_id=$usergroup_id", 'permission_role');
}

function spa_display_usergroup_select($filter=false, $forum_id=0, $showSelect=true) {
	$usergroups = spa_get_usergroups_all();
	if ($showSelect) echo spa_text('Select usergroup').':&nbsp;&nbsp;';
	if ($showSelect) { ?>
		<select style="width:145px" class='sfacontrol' name='usergroup_id'>
<?php
		}
		$out = '<option value="-1">'.spa_text('Select usergroup').'</option>';
		if ($filter) $perms = sp_get_forum_permissions($forum_id);
		foreach ($usergroups as $usergroup) {
			$disabled = '';
			if ($filter == 1 && $perms) {
				foreach ($perms as $perm) {
					if ($perm->usergroup_id == $usergroup->usergroup_id) {
						$disabled = 'disabled="disabled" ';
						continue;
					}
				}
			}
			$out.= '<option '.$disabled.'value="'.$usergroup->usergroup_id.'">'.sp_filter_title_display($usergroup->usergroup_name).'</option>'."\n";
			$default='';
		}
		echo $out;
	if ($showSelect) {
?>
	</select>
<?php
	}
}

function spa_display_permission_select($cur_perm=0, $showSelect=true) {
?>
	<?php $roles = sp_get_all_roles(); ?>
	<?php if ($showSelect) { ?>
		<select style="width:165px" class='sfacontrol' name='role'>
<?php
	}
		$out = '';
		if ($cur_perm == 0) $out.= '<option value="-1">'.spa_text('Select permission set').'</option>';
		foreach ($roles as $role) {
			$selected = '';
			if ($cur_perm == $role->role_id) $selected = 'selected = "selected" ';
			$out.= '<option '.$selected.'value="'.$role->role_id.'">'.sp_filter_title_display($role->role_name).'</option>'."\n";
		}
		echo $out;
	if ($showSelect) {
?>
		</select>
<?php
	}
}

function spa_select_icon_dropdown($name, $label, $path, $cur, $showSelect=true, $width=0) {
	# Open folder and get cntents for matching
	$dlist = @opendir($path);
	if (!$dlist) return;

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);
	if (empty($files)) return;
	sort($files);

	$w = '';
	if ($width > 0) $w = 'width:'.$width.'px;';
	if ($showSelect) echo '<select name="'.$name.'" class="sfcontrol" style="vertical-align:middle;'.$w.'">';
	if ($cur != '') $label = spa_text('Remove');
	echo '<option value="">'.$label.'</option>';

	foreach ($files as $file) {
		$selected = '';
		if ($file == $cur) $selected = ' selected="selected"';
		echo '<option'.$selected.' value="'.esc_attr($file).'">'.esc_html($file).'</option>';
	}
	if ($showSelect) echo '</select>';
}

function spa_setup_permissions() {
	# Create default role data
    # NOTE that the auths do not use action names like this, but its pretty unreadable the way its stored
    # so use action names here for readability and maintainability. we will convert the actions to auths before storing

	$actions = array();
	$actions['Can view forum'] = 0;
	$actions['Can view a list of forums only'] = 0;
	$actions['Can view a list of forums and list of topics only'] = 0;
	$actions['Can view posts by an administrator'] = 0;
	$actions['Can view only own posts and admin/mod posts'] = 0;
	$actions['Can view email and IP addresses of members'] = 0;
	$actions['Can view profiles of members'] = 0;
	$actions['Can view the members lists'] = 0;
	$actions['Can view links within posts'] = 0;
	$actions['Can start new topics in a forum'] = 0;
	$actions['Can reply to existing topics in a forum'] = 0;
	$actions['Can only reply to own topics'] = 0;
	$actions['Can bypass wait time between posts'] = 0;
	$actions['Can use spoilers in posts'] = 0;
	$actions['Can attach a signature to posts'] = 0;
	$actions['Can create links in posts'] = 0;
	$actions['Can use smileys in posts'] = 0;
	$actions['Can use iframes in posts'] = 0;
	$actions['Can edit own topic titles'] = 0;
	$actions['Can edit any topic title'] = 0;
	$actions['Can edit own posts for time period'] = 0;
	$actions['Can edit own posts forever'] = 0;
	$actions['Can edit own posts until there has been a reply'] = 0;
	$actions['Can edit any post'] = 0;
	$actions['Can delete topics in forum'] = 0;
	$actions['Can delete own posts'] = 0;
	$actions['Can delete any post'] = 0;
	$actions['Can bypass the math question'] = 0;
	$actions['Can bypass all post moderation'] = 0;
	$actions['Can bypass first post moderation'] = 0;
	$actions['Can moderate pending posts'] = 0;
	$actions['Can pin topics in a forum'] = 0;
	$actions['Can move topics from a forum'] = 0;
	$actions['Can move posts from a topic'] = 0;
	$actions['Can lock topics in a forum'] = 0;
	$actions['Can pin posts within a topic'] = 0;
	$actions['Can reassign posts to a different user'] = 0;
	$actions['Can upload avatars'] = 0;
	$actions['Can view images in posts'] = 0;
	$actions['Can view media in posts'] = 0;

	$role_name = 'No Access';
	$role_desc = 'Permission with no access to any Forum features';
    $new_actions = spa_convert_action_to_auth($actions);
	spa_create_role_row($role_name, $role_desc, serialize($new_actions));

	$actions = array();
	$actions['Can view forum'] = 1;
	$actions['Can view a list of forums only'] = 0;
	$actions['Can view a list of forums and list of topics only'] = 0;
	$actions['Can view posts by an administrator'] = 1;
	$actions['Can view only own posts and admin/mod posts'] = 0;
	$actions['Can view email and IP addresses of members'] = 0;
	$actions['Can view profiles of members'] = 0;
	$actions['Can view the members lists'] = 0;
	$actions['Can view links within posts'] = 1;
	$actions['Can start new topics in a forum'] = 0;
	$actions['Can reply to existing topics in a forum'] = 0;
	$actions['Can only reply to own topics'] = 0;
	$actions['Can bypass wait time between posts'] = 0;
	$actions['Can use spoilers in posts'] = 1;
	$actions['Can attach a signature to posts'] = 0;
	$actions['Can create links in posts'] = 0;
	$actions['Can use smileys in posts'] = 0;
	$actions['Can use iframes in posts'] = 0;
	$actions['Can edit own topic titles'] = 0;
	$actions['Can edit any topic title'] = 0;
	$actions['Can edit own posts for time period'] = 0;
	$actions['Can edit own posts forever'] = 0;
	$actions['Can edit own posts until there has been a reply'] = 0;
	$actions['Can edit any post'] = 0;
	$actions['Can delete topics in forum'] = 0;
	$actions['Can delete own posts'] = 0;
	$actions['Can delete any post'] = 0;
	$actions['Can bypass the math question'] = 0;
	$actions['Can bypass all post moderation'] = 0;
	$actions['Can bypass first post moderation'] = 0;
	$actions['Can moderate pending posts'] = 0;
	$actions['Can pin topics in a forum'] = 0;
	$actions['Can move topics from a forum'] = 0;
	$actions['Can move posts from a topic'] = 0;
	$actions['Can lock topics in a forum'] = 0;
	$actions['Can pin posts within a topic'] = 0;
	$actions['Can reassign posts to a different user'] = 0;
	$actions['Can upload avatars'] = 0;
	$actions['Can view images in posts'] = 1;
	$actions['Can view media in posts'] = 1;

	$role_name = 'Read Only Access';
	$role_desc = 'Permission with access to only view the Forum';
    $new_actions = spa_convert_action_to_auth($actions);
	spa_create_role_row($role_name, $role_desc, serialize($new_actions));

	$actions = array();
	$actions['Can view forum'] = 1;
	$actions['Can view a list of forums only'] = 0;
	$actions['Can view a list of forums and list of topics only'] = 0;
	$actions['Can view posts by an administrator'] = 1;
	$actions['Can view only own posts and admin/mod posts'] = 0;
	$actions['Can view email and IP addresses of members'] = 0;
	$actions['Can view profiles of members'] = 1;
	$actions['Can view the members lists'] = 1;
	$actions['Can view links within posts'] = 1;
	$actions['Can start new topics in a forum'] = 1;
	$actions['Can reply to existing topics in a forum'] = 1;
	$actions['Can only reply to own topics'] = 0;
	$actions['Can bypass wait time between posts'] = 0;
	$actions['Can use spoilers in posts'] = 1;
	$actions['Can attach a signature to posts'] = 0;
	$actions['Can create links in posts'] = 1;
	$actions['Can use smileys in posts'] = 1;
	$actions['Can use iframes in posts'] = 0;
	$actions['Can edit own topic titles'] = 0;
	$actions['Can edit any topic title'] = 0;
	$actions['Can edit own posts for time period'] = 0;
	$actions['Can edit own posts forever'] = 0;
	$actions['Can edit own posts until there has been a reply'] = 0;
	$actions['Can edit any post'] = 0;
	$actions['Can delete topics in forum'] = 0;
	$actions['Can delete own posts'] = 0;
	$actions['Can delete any post'] = 0;
	$actions['Can bypass the math question'] = 0;
	$actions['Can bypass all post moderation'] = 0;
	$actions['Can bypass first post moderation'] = 0;
	$actions['Can moderate pending posts'] = 0;
	$actions['Can pin topics in a forum'] = 0;
	$actions['Can move topics from a forum'] = 0;
	$actions['Can move posts from a topic'] = 0;
	$actions['Can lock topics in a forum'] = 0;
	$actions['Can pin posts within a topic'] = 0;
	$actions['Can reassign posts to a different user'] = 0;
	$actions['Can upload avatars'] = 1;
	$actions['Can view images in posts'] = 1;
	$actions['Can view media in posts'] = 1;

	$role_name = 'Limited Access';
	$role_desc = 'Permission with access to reply and start topics but with limited features';
    $new_actions = spa_convert_action_to_auth($actions);
	spa_create_role_row($role_name, $role_desc, serialize($new_actions));

	$actions = array();
	$actions['Can view forum'] = 1;
	$actions['Can view a list of forums only'] = 0;
	$actions['Can view a list of forums and list of topics only'] = 0;
	$actions['Can view posts by an administrator'] = 1;
	$actions['Can view only own posts and admin/mod posts'] = 0;
	$actions['Can view email and IP addresses of members'] = 0;
	$actions['Can view profiles of members'] = 1;
	$actions['Can view the members lists'] = 1;
	$actions['Can view links within posts'] = 1;
	$actions['Can start new topics in a forum'] = 1;
	$actions['Can reply to existing topics in a forum'] = 1;
	$actions['Can only reply to own topics'] = 0;
	$actions['Can bypass wait time between posts'] = 0;
	$actions['Can use spoilers in posts'] = 1;
	$actions['Can attach a signature to posts'] = 1;
	$actions['Can create links in posts'] = 1;
	$actions['Can use smileys in posts'] = 1;
	$actions['Can use iframes in posts'] = 0;
	$actions['Can edit own topic titles'] = 0;
	$actions['Can edit any topic title'] = 0;
	$actions['Can edit own posts for time period'] = 0;
	$actions['Can edit own posts forever'] = 0;
	$actions['Can edit own posts until there has been a reply'] = 1;
	$actions['Can edit any post'] = 0;
	$actions['Can delete topics in forum'] = 0;
	$actions['Can delete own posts'] = 0;
	$actions['Can delete any post'] = 0;
	$actions['Can bypass the math question'] = 0;
	$actions['Can bypass all post moderation'] = 1;
	$actions['Can bypass first post moderation'] = 1;
	$actions['Can moderate pending posts'] = 0;
	$actions['Can pin topics in a forum'] = 0;
	$actions['Can move topics from a forum'] = 0;
	$actions['Can move posts from a topic'] = 0;
	$actions['Can lock topics in a forum'] = 0;
	$actions['Can pin posts within a topic'] = 0;
	$actions['Can reassign posts to a different user'] = 0;
	$actions['Can upload avatars'] = 1;
	$actions['Can view images in posts'] = 1;
	$actions['Can view media in posts'] = 1;

	$role_name = 'Standard Access';
	$role_desc = 'Permission with access to reply and start topics with advanced features such as signatures';
    $new_actions = spa_convert_action_to_auth($actions);
	spa_create_role_row($role_name, $role_desc, serialize($new_actions));

	$actions = array();
	$actions['Can view forum'] = 1;
	$actions['Can view a list of forums only'] = 0;
	$actions['Can view a list of forums and list of topics only'] = 0;
	$actions['Can view posts by an administrator'] = 1;
	$actions['Can view only own posts and admin/mod posts'] = 0;
	$actions['Can view email and IP addresses of members'] = 0;
	$actions['Can view profiles of members'] = 1;
	$actions['Can view the members lists'] = 1;
	$actions['Can view links within posts'] = 1;
	$actions['Can start new topics in a forum'] = 1;
	$actions['Can reply to existing topics in a forum'] = 1;
	$actions['Can only reply to own topics'] = 0;
	$actions['Can bypass wait time between posts'] = 1;
	$actions['Can use spoilers in posts'] = 1;
	$actions['Can attach a signature to posts'] = 1;
	$actions['Can create links in posts'] = 1;
	$actions['Can use smileys in posts'] = 1;
	$actions['Can use iframes in posts'] = 0;
	$actions['Can edit own topic titles'] = 1;
	$actions['Can edit any topic title'] = 0;
	$actions['Can edit own posts for time period'] = 0;
	$actions['Can edit own posts forever'] = 1;
	$actions['Can edit own posts until there has been a reply'] = 1;
	$actions['Can edit any post'] = 0;
	$actions['Can delete topics in forum'] = 0;
	$actions['Can delete own posts'] = 0;
	$actions['Can delete any post'] = 0;
	$actions['Can bypass the math question'] = 1;
	$actions['Can bypass all post moderation'] = 1;
	$actions['Can bypass first post moderation'] = 1;
	$actions['Can moderate pending posts'] = 0;
	$actions['Can pin topics in a forum'] = 0;
	$actions['Can move topics from a forum'] = 0;
	$actions['Can move posts from a topic'] = 0;
	$actions['Can lock topics in a forum'] = 0;
	$actions['Can pin posts within a topic'] = 0;
	$actions['Can reassign posts to a different user'] = 0;
	$actions['Can upload avatars'] = 1;
	$actions['Can view images in posts'] = 1;
	$actions['Can view media in posts'] = 1;

	$role_name = 'Full Access';
	$role_desc = 'Permission with Standard Access features and math question bypass';
    $new_actions = spa_convert_action_to_auth($actions);
	spa_create_role_row($role_name, $role_desc, serialize($new_actions));

	$actions = array();
	$actions['Can view forum'] = 1;
	$actions['Can view a list of forums only'] = 0;
	$actions['Can view a list of forums and list of topics only'] = 0;
	$actions['Can view posts by an administrator'] = 1;
	$actions['Can view only own posts and admin/mod posts'] = 0;
	$actions['Can view email and IP addresses of members'] = 1;
	$actions['Can view profiles of members'] = 1;
	$actions['Can view the members lists'] = 1;
	$actions['Can view links within posts'] = 1;
	$actions['Can start new topics in a forum'] = 1;
	$actions['Can reply to existing topics in a forum'] = 1;
	$actions['Can only reply to own topics'] = 0;
	$actions['Can bypass wait time between posts'] = 1;
	$actions['Can use spoilers in posts'] = 1;
	$actions['Can attach a signature to posts'] = 1;
	$actions['Can create links in posts'] = 1;
	$actions['Can use smileys in posts'] = 1;
	$actions['Can use iframes in posts'] = 0;
	$actions['Can edit own topic titles'] = 1;
	$actions['Can edit any topic title'] = 1;
	$actions['Can edit own posts for time period'] = 0;
	$actions['Can edit own posts forever'] = 1;
	$actions['Can edit own posts until there has been a reply'] = 1;
	$actions['Can edit any post'] = 1;
	$actions['Can delete topics in forum'] = 1;
	$actions['Can delete own posts'] = 0;
	$actions['Can delete any post'] = 1;
	$actions['Can bypass the math question'] = 1;
	$actions['Can bypass all post moderation'] = 1;
	$actions['Can bypass first post moderation'] = 1;
	$actions['Can moderate pending posts'] = 1;
	$actions['Can pin topics in a forum'] = 1;
	$actions['Can move topics from a forum'] = 1;
	$actions['Can move posts from a topic'] = 1;
	$actions['Can lock topics in a forum'] = 1;
	$actions['Can pin posts within a topic'] = 1;
	$actions['Can reassign posts to a different user'] = 1;
	$actions['Can upload avatars'] = 1;
	$actions['Can view images in posts'] = 1;
	$actions['Can view media in posts'] = 1;

	$role_name = 'Moderator Access';
	$role_desc = 'Permission with access to all Forum features';
    $new_actions = spa_convert_action_to_auth($actions);
	spa_create_role_row($role_name, $role_desc, serialize($new_actions));
}

function spa_convert_action_to_auth($actions) {
	$new_actions = array();
	foreach ($actions as $index => $action) {
		if ($index == 'Can view forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_forum"')] = (int) $action;
		if ($index == 'Can view a list of forums only') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_forum_lists"')] = (int) $action;
		if ($index == 'Can view a list of forums and list of topics only') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_forum_topic_lists"')] = (int) $action;
		if ($index == 'Can view posts by an administrator') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_admin_posts"')] = (int) $action;
		if ($index == 'Can view only own posts and admin/mod posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_own_admin_posts"')] = (int) $action;
		if ($index == 'Can view email and IP addresses of members') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_email"')] = (int) $action;
		if ($index == 'Can view profiles of members') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_profiles"')] = (int) $action;
		if ($index == 'Can view the members lists') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_members_list"')] = (int) $action;
		if ($index == 'Can view links within posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "view_links"')] = (int) $action;
		if ($index == 'Can start new topics in a forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "start_topics"')] = (int) $action;
		if ($index == 'Can reply to existing topics in a forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "reply_topics"')] = (int) $action;
		if ($index == 'Can only reply to own topics') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "reply_own_topics"')] = (int) $action;
		if ($index == 'Can bypass wait time between posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "bypass_flood_control"')] = (int) $action;
		if ($index == 'Can use spoilers in posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "use_spoilers"')] = (int) $action;
		if ($index == 'Can attach a signature to posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "use_signatures"')] = (int) $action;
		if ($index == 'Can create links in posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "create_links"')] = (int) $action;
		if ($index == 'Can use smileys in posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "can_use_smileys"')] = (int) $action;
		if ($index == 'Can use iframes in posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "can_use_iframes"')] = (int) $action;
		if ($index == 'Can edit own topic titles') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "edit_own_topic_titles"')] = (int) $action;
		if ($index == 'Can edit any topic title') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "edit_any_topic_titles"')] = (int) $action;
		if ($index == 'Can edit own posts for time period') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "edit_own_posts_for_time"')] = (int) $action;
		if ($index == 'Can edit own posts forever') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "edit_own_posts_forever"')] = (int) $action;
		if ($index == 'Can edit own posts until there has been a reply') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "edit_own_posts_reply"')] = (int) $action;
		if ($index == 'Can edit any post') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "edit_any_post"')] = (int) $action;
		if ($index == 'Can delete topics in forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "delete_topics"')] = (int) $action;
		if ($index == 'Can delete own posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "delete_own_posts"')] = (int) $action;
		if ($index == 'Can delete any post') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "delete_any_post"')] = (int) $action;
		if ($index == 'Can bypass the math question') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "bypass_math_question"')] = (int) $action;
		if ($index == 'Can bypass all post moderation') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "bypass_moderation"')] = (int) $action;
		if ($index == 'Can bypass first post moderation') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "bypass_moderation_once"')] = (int) $action;
		if ($index == 'Can moderate pending posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "moderate_posts"')] = (int) $action;
		if ($index == 'Can pin topics in a forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "pin_topics"')] = (int) $action;
		if ($index == 'Can move topics from a forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "move_topics"')] = (int) $action;
		if ($index == 'Can move posts from a topic') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "move_posts"')] = (int) $action;
		if ($index == 'Can lock topics in a forum') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "lock_topics"')] = (int) $action;
		if ($index == 'Can pin posts within a topic') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "pin_posts"')] = (int) $action;
		if ($index == 'Can reassign posts to a different user') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "reassign_posts"')] = (int) $action;
		if ($index == 'Can upload avatars') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "upload_avatars"')] = (int) $action;
		if ($index == 'Can view images in posts') $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "can_view_images"')] = (int) $action;
		if ($index == 'Can view media in posts')  $new_actions[spdb_select('var', 'SELECT auth_id FROM '.SFAUTHS.' where auth_name = "can_view_media"')] = (int) $action;
	}
	return $new_actions;
}

function spa_setup_auths() {
    # create the auths
	sp_add_auth('view_forum', sp_esc_sql(spa_text_noesc('Can view a forum')), 1, 0, 0, 0, 2, '');
	sp_add_auth('view_forum_lists', sp_esc_sql(spa_text_noesc('Can view a list of forums only')), 1, 0, 0, 0, 2, '');
	sp_add_auth('view_forum_topic_lists', sp_esc_sql(spa_text_noesc('Can view a list of forums and list of topics only')), 1, 0, 0, 0, 2, '');
	sp_add_auth('view_admin_posts', sp_esc_sql(spa_text_noesc('Can view posts by an administrator')), 1, 0, 0, 0, 2, '');
	sp_add_auth('view_own_admin_posts', sp_esc_sql(spa_text_noesc('Can view only own posts and admin/mod posts')), 1, 1, 0, 1, 2, '');
	sp_add_auth('view_email', sp_esc_sql(spa_text_noesc('Can view email and IP addresses of members')), 1, 1, 0, 0, 2, '');
	sp_add_auth('view_profiles', sp_esc_sql(spa_text_noesc('Can view profiles of members')), 1, 0, 0, 0, 2, '');
	sp_add_auth('view_members_list', sp_esc_sql(spa_text_noesc('Can view the members lists')), 1, 0, 0, 0, 2, '');
	sp_add_auth('view_links', sp_esc_sql(spa_text_noesc('Can view links within posts')), 1, 0, 0, 0, 2, '');
	sp_add_auth('start_topics', sp_esc_sql(spa_text_noesc('Can start new topics in a forum')), 1, 0, 0, 0, 3, '');
	sp_add_auth('reply_topics', sp_esc_sql(spa_text_noesc('Can reply to existing topics in a forum')), 1, 0, 0, 0, 3, '');
	sp_add_auth('reply_own_topics', sp_esc_sql(spa_text_noesc('Can only reply to own topics')), 1, 1, 0, 1, 3, '');
	sp_add_auth('bypass_flood_control', sp_esc_sql(spa_text_noesc('Can bypass wait time between posts')), 1, 0, 0, 0, 3, '');
	sp_add_auth('use_spoilers', sp_esc_sql(spa_text_noesc('Can use spoilers in posts in posts')), 1, 0, 0, 0, 3, '');
	sp_add_auth('use_signatures', sp_esc_sql(spa_text_noesc('Can attach a signature to posts')), 1, 1, 0, 0, 3, '');
	sp_add_auth('create_links', sp_esc_sql(spa_text_noesc('Can create links in posts')), 1, 0, 0, 0, 3, '');
	sp_add_auth('can_use_smileys', sp_esc_sql(spa_text_noesc('Can use smileys in posts')), 1, 0, 0, 0, 3, '');
	sp_add_auth('can_use_iframes', sp_esc_sql(spa_text_noesc('Can use iframes in posts')), 1, 1, 0, 0, 3, spa_text('*** WARNING *** The use of iframes is dangerous. Allowing users to create iframes enables them to launch a potential security threat against your website. Enabling iframes requires your trust in your users. Turn on with care.'));
	sp_add_auth('edit_own_topic_titles', sp_esc_sql(spa_text_noesc('Can edit own topic titles')), 1, 1, 0, 0, 4, '');
	sp_add_auth('edit_any_topic_titles', sp_esc_sql(spa_text_noesc('Can edit any topic title')), 1, 1, 0, 0, 4, '');
	sp_add_auth('edit_own_posts_for_time', sp_esc_sql(spa_text_noesc('Can edit own posts for time period')), 1, 1, 0, 0, 4, '');
	sp_add_auth('edit_own_posts_forever', sp_esc_sql(spa_text_noesc('Can edit own posts forever')), 1, 1, 0, 0, 4, '');
	sp_add_auth('edit_own_posts_reply', sp_esc_sql(spa_text_noesc('Can edit own posts until there has been a reply')), 1, 1, 0, 0, 4, '');
	sp_add_auth('edit_any_post', sp_esc_sql(spa_text_noesc('Can edit any post')), 1, 1, 0, 0, 4, '');
	sp_add_auth('delete_topics', sp_esc_sql(spa_text_noesc('Can delete topics in forum')), 1, 1, 0, 0, 5, '');
	sp_add_auth('delete_own_posts', sp_esc_sql(spa_text_noesc('Can delete own posts')), 1, 1, 0, 0, 5, '');
	sp_add_auth('delete_any_post', sp_esc_sql(spa_text_noesc('Can delete any post')), 1, 1, 0, 0, 5, '');
	sp_add_auth('bypass_math_question', sp_esc_sql(spa_text_noesc('Can bypass the math question')), 1, 0, 0, 0, 6, '');
	sp_add_auth('bypass_moderation', sp_esc_sql(spa_text_noesc('Can bypass all post moderation')), 1, 0, 0, 0, 6, 0);
	sp_add_auth('bypass_moderation_once', sp_esc_sql(spa_text_noesc('Can bypass first post moderation')), 1, 0, 0, 0, 6, '');
	sp_add_auth('moderate_posts', sp_esc_sql(spa_text_noesc('Can moderate pending posts')), 1, 1, 0, 0, 6, '');
	sp_add_auth('pin_topics', sp_esc_sql(spa_text_noesc('Can pin topics in a forum')), 1, 0, 0, 0, 7, '');
	sp_add_auth('move_topics', sp_esc_sql(spa_text_noesc('Can move topics from a forum')), 1, 0, 0, 0, 7, '');
	sp_add_auth('move_posts', sp_esc_sql(spa_text_noesc('Can move posts from a topic')), 1, 0, 0, 0, 7, '');
	sp_add_auth('lock_topics', sp_esc_sql(spa_text_noesc('Can lock topics in a forum')), 1, 0, 0, 0, 7, '');
	sp_add_auth('pin_posts', sp_esc_sql(spa_text_noesc('Can pin posts within a topic')), 1, 0, 0, 0, 7, '');
	sp_add_auth('reassign_posts', sp_esc_sql(spa_text_noesc('Can reassign posts to a different user')), 1, 0, 0, 0, 7, '');
	sp_add_auth('upload_avatars', sp_esc_sql(spa_text_noesc('Can upload avatars')), 1, 1, 1, 0, 8, '');
	sp_add_auth('can_view_images', sp_esc_sql(spa_text_noesc('Can view images in posts')), 1, 0, 0, 0, 2, '');
	sp_add_auth('can_view_media', sp_esc_sql(spa_text_noesc('Can view media in posts')), 1, 0, 0, 0, 2, '');
}

# 5.2 add new auth categories for grouping of auths
function spa_setup_auth_cats() {
    global $spVars;

    # have the auths tables been created?
	$auths = spdb_select('var', "SHOW TABLES LIKE '".SFAUTHS."'");

    # default auths
    sp_create_auth_cat(spa_text('General'), spa_text('auth category for general auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='use_pm'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='rate_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='watch'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='subscribe'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='report_posts'");
    }

    # viewing auths
    sp_create_auth_cat(spa_text('Viewing'), spa_text('auth category for viewing auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_forum'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_forum_lists'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_forum_topic_lists'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_admin_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_own_admin_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_email'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_profiles'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_members_list'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_links'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='view_online_activity'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='can_view_images'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='can_view_media'");
    }

    # creating auths
    sp_create_auth_cat(spa_text('Creating'), spa_text('auth category for creating auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='start_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='reply_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='bypass_flood_control'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='reply_own_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='use_spoilers'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='use_signatures'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='create_links'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='create_linked_topics'");
    }

    # editing auths
    sp_create_auth_cat(spa_text('Editing'), spa_text('auth category for editing auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_own_topic_titles'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_any_topic_titles'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_own_posts_for_time'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_own_posts_forever'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_own_posts_reply'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_any_post'");
    }

    # deleting auths
    sp_create_auth_cat(spa_text('Deleting'), spa_text('auth category for deleting auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='delete_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='delete_own_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='delete_any_post'");
    }

    # moderation auths
    sp_create_auth_cat(spa_text('Moderation'), spa_text('auth category for moderation auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='bypass_math_question'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='bypass_moderation'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='bypass_moderation_once'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='moderate_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='bypass_captcha'");
    }

    # tools auths
    sp_create_auth_cat(spa_text('Tools'), spa_text('auth category for tools auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='pin_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='move_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='move_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='lock_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='pin_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='reassign_posts'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='break_linked_topics'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='edit_tags'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='change_topic_status'");
    }

    # uploading auths
    sp_create_auth_cat(spa_text('Uploading'), spa_text('auth category for uploading auths'));
    $auth_cat = $spVars['insertid'];
    if ($auths) {
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='upload_avatars'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='upload_images'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='upload_media'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='upload_files'");
    	spdb_query('UPDATE '.SFAUTHS." SET auth_cat=$auth_cat WHERE auth_name='upload_signatures'");
    }
}

# 5.0 set up stuff for new profile tabs
function spa_new_profile_setup() {
	# set up tabs and menus
    sp_profile_add_tab('Profile');
	sp_profile_add_menu('Profile', 'Overview', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-overview.php');
	sp_profile_add_menu('Profile', 'Edit Profile', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-profile.php');
	sp_profile_add_menu('Profile', 'Edit Identities', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-identities.php');
	sp_profile_add_menu('Profile', 'Edit Avatar', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-avatar.php');
	sp_profile_add_menu('Profile', 'Edit Signature', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-signature.php', 0, 1, 'use_signatures');
	sp_profile_add_menu('Profile', 'Edit Photos', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-photos.php');
	sp_profile_add_menu('Profile', 'Account Settings', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-account.php');

    sp_profile_add_tab('Options');
	sp_profile_add_menu('Options', 'Edit Global Options', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-global-options.php');
	sp_profile_add_menu('Options', 'Edit Posting Options', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-posting-options.php');
	sp_profile_add_menu('Options', 'Edit Display Options', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-display-options.php');

    sp_profile_add_tab('Usergroups');
	sp_profile_add_menu('Usergroups', 'Show Memberships', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-memberships.php');

    sp_profile_add_tab('Permissions');
	sp_profile_add_menu('Permissions', 'Show Permissions', SF_PLUGIN_DIR.'/forum/profile/forms/sp-form-permissions.php');

	# overview message
	$spProfile = sp_get_option('sfprofile');
	if (empty($spProfile['sfprofiletext'])) {
		$spProfile['sfprofiletext'] = 'Welcome to the User Profile Overview Panel. From here you can view and update your profile and options as well as view your Usergroup Memberships and Permissions.';
		sp_update_option('sfprofile', $spProfile);
	}
}

# 5.5.6
function sp_add_caps() {
	global $wp_roles;
	if (class_exists('WP_Roles') && !isset($wp_roles)) $wp_roles = new WP_Roles();

    $wp_roles->add_cap('administrator', 'SPF Manage Options', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Forums', false);
    $wp_roles->add_cap('administrator', 'SPF Manage User Groups', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Permissions', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Components', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Admins', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Users', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Profiles', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Toolbox', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Plugins', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Themes', false);
    $wp_roles->add_cap('administrator', 'SPF Manage Integration', false);
}

# 5.5.3 - get and display simple stats for admin items
function sp_display_item_stats($table, $key, $value, $label) {
	$c = spdb_count($table, "$key = $value");
	echo '<span class="spItemStat">'.$label.' <b>'.$c.'</b></span>';
}
?>