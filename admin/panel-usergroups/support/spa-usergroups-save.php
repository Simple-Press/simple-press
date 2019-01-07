<?php
/*
Simple:Press
Admin User Groups Support Functions
$LastChangedDate: 2016-11-10 05:12:42 -0600 (Thu, 10 Nov 2016) $
$Rev: 14718 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to create a new user group
function spa_save_usergroups_new_usergroup() {
    check_admin_referer('forum-adminform_usergroupnew', 'forum-adminform_usergroupnew');

    # if no usergroup name supplied use a default name
    if (empty($_POST['usergroup_name'])) {
        $usergroupname = spa_text('New User Group');
    } else {
        $usergroupname = sp_filter_title_save(trim($_POST['usergroup_name']));
    }

    $usergroupdesc = sp_filter_title_save(trim($_POST['usergroup_desc']));
    $usergroupbadge = sp_filter_filename_save(trim($_POST['usergroup_badge']));

    if (isset($_POST['usergroup_join'])) {
		$usergroupjoin = 1;
	} else {
		$usergroupjoin = 0;
	}

    if (isset($_POST['hide_stats'])) {
		$hide_stats = 1;
	} else {
		$hide_stats = 0;
	}

    if (isset($_POST['usergroup_is_moderator'])) {
		$usergroupismod = 1;
	} else {
		$usergroupismod = 0;
	}

    # create the usergroup
    $success = spa_create_usergroup_row($usergroupname, $usergroupdesc, $usergroupbadge, $usergroupjoin, $hide_stats, $usergroupismod, true);

    sp_reset_memberships();

	# rebuiuld the new user list just in case
	sp_rebuild_newuser();

    if ($success == false) {
        $mess = spa_text('New user group creation failed');
    } else {
        $mess = spa_text('New user group created');
    }
    return $mess;
}

# function to update an existing user group
function spa_save_usergroups_edit_usergroup() {
    check_admin_referer('forum-adminform_usergroupedit', 'forum-adminform_usergroupedit');

    $usergroupdata = array();
    $usergroup_id = sp_esc_int($_POST['usergroup_id']);
    $usergroupdata['usergroup_name'] = sp_filter_title_save(trim($_POST['usergroup_name']));
    $usergroupdata['usergroup_desc'] = sp_filter_title_save(trim($_POST['usergroup_desc']));
    $usergroupdata['usergroup_badge'] = sp_filter_filename_save(trim($_POST['usergroup_badge']));
    if (isset($_POST['usergroup_join'])) { $usergroupdata['usergroup_join'] = 1; } else { $usergroupdata['usergroup_join'] = 0; }
    if (isset($_POST['hide_stats'])) { $usergroupdata['hide_stats'] = 1; } else { $usergroupdata['hide_stats'] = 0; }
    if (isset($_POST['usergroup_is_moderator'])) { $usergroupdata['usergroup_is_moderator'] = 1; } else { $usergroupdata['usergroup_is_moderator'] = 0; }

    # update the user group info
	$sql = 'UPDATE '.SFUSERGROUPS.' SET ';
	$sql.= 'usergroup_name="'.$usergroupdata['usergroup_name'].'", ';
	$sql.= 'usergroup_desc="'.$usergroupdata['usergroup_desc'].'", ';
	$sql.= 'usergroup_badge="'.$usergroupdata['usergroup_badge'].'", ';
	$sql.= 'usergroup_join="'.$usergroupdata['usergroup_join'].'", ';
	$sql.= 'hide_stats="'.$usergroupdata['hide_stats'].'", ';
	$sql.= 'usergroup_is_moderator="'.$usergroupdata['usergroup_is_moderator'].'" ';
	$sql.= "WHERE usergroup_id=$usergroup_id";
    $success = spdb_query($sql);

    sp_reset_memberships();

	# rebuiuld the new user list just in case
	sp_rebuild_newuser();

    if ($success == false) {
        $mess = spa_text('User group update failed');
    } else {
        $mess = spa_text('User group record updated');
        do_action('sph_usergroup_new', $usergroup_id);
    }
    return $mess;
}

function spa_save_usergroups_delete_usergroup() {
    check_admin_referer('forum-adminform_usergroupdelete', 'forum-adminform_usergroupdelete');

    $usergroup_id = sp_esc_int($_POST['usergroup_id']);

    # dont allow updates to the default user groups
    $usergroup = spa_get_usergroups_row($usergroup_id);
    if ($usergroup->usergroup_locked) {
        $mess = spa_text('Sorry, the default User Groups cannot be deleted');
        return $mess;
    }

    # remove all memberships for this user group
    spdb_query("DELETE FROM ".SFMEMBERSHIPS." WHERE usergroup_id=".$usergroup_id);

	# remove any permission sets using this user group
	$permissions = spdb_table(SFPERMISSIONS, "usergroup_id=$usergroup_id");
	if ($permissions) {
		foreach ($permissions as $permission) {
			spa_remove_permission_data($permission->permission_id);
		}
	}

	# remove any group default permissions using this user group
	spdb_query("DELETE FROM ".SFDEFPERMISSIONS." WHERE usergroup_id=".$usergroup_id);

    # remove the user group
   	spdb_query("DELETE FROM ".SFMEMBERSHIPS." WHERE usergroup_id=".$usergroup_id);
    $success = spdb_query("DELETE FROM ".SFUSERGROUPS." WHERE usergroup_id=".$usergroup_id);
    if ($success == false) {
        $mess = spa_text('User group delete failed');
    } else {
        $mess = spa_text('User group deleted');

        # reset auths and memberships for everyone
        sp_reset_memberships();
        sp_reset_auths();

		# rebuiuld the new user list just in case
		sp_rebuild_newuser();

        do_action('sph_usergroup_del', $usergroup_id);
    }

    return $mess;
}

function spa_save_usergroups_add_members() {
	return spa_etext('Please Wait - Processing');
}

function spa_save_usergroups_delete_members() {
	return spa_etext('Please Wait - Processing');
}

function spa_save_usergroups_map_settings() {
	global $wp_roles;

	check_admin_referer('forum-adminform_mapusers', 'forum-adminform_mapusers');

	# save default usergroups
	sp_add_sfmeta('default usergroup', 'sfguests', sp_esc_int($_POST['sfguestsgroup'])); # default usergroup for guests
	sp_add_sfmeta('default usergroup', 'sfmembers', sp_esc_int($_POST['sfdefgroup'])); # default usergroup for members

	# check for changes in wp role usergroup assignments
	if (isset($_POST['sfrole'])) {
		$roles = array_keys($wp_roles->role_names);
		foreach ($_POST['sfrole'] as $index => $role) {
			if ($_POST['sfoldrole'][$index] != $role) sp_add_sfmeta('default usergroup', $roles[$index], sp_esc_int($role));
		}
	}

	$sfmemberopts = sp_get_option('sfmemberopts');
    $sfmemberopts['sfsinglemembership'] = isset($_POST['sfsinglemembership']);
	sp_update_option('sfmemberopts', $sfmemberopts);

	$mess = spa_text('User mapping settings saved');
    do_action('sph_option_map_settings_save');
	return $mess;
}

function spa_save_usergroups_map_users() {
	return spa_etext('Please Wait - Processing');
}

?>