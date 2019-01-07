<?php
/*
Simple:Press
Admin User Groups Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to create a new user group
function spa_save_usergroups_new_usergroup() {
    check_admin_referer('forum-adminform_usergroupnew', 'forum-adminform_usergroupnew');

    # if no usergroup name supplied use a default name
    if (empty($_POST['usergroup_name'])) {
        $usergroupname = SP()->primitives->admin_text('New User Group');
    } else {
        $usergroupname = SP()->saveFilters->title(trim($_POST['usergroup_name']));
    }

    $usergroupdesc = SP()->saveFilters->title(trim($_POST['usergroup_desc']));
    $usergroupbadge = SP()->saveFilters->filename(trim($_POST['usergroup_badge']));

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

    SP()->user->reset_memberships();

	# rebuiuld the new user list just in case
	SP()->user->rebuild_new();

    if ($success == false) {
        $mess = SP()->primitives->admin_text('New user group creation failed');
    } else {
        $mess = SP()->primitives->admin_text('New user group created');
    }
    return $mess;
}

# function to update an existing user group
function spa_save_usergroups_edit_usergroup() {
    check_admin_referer('forum-adminform_usergroupedit', 'forum-adminform_usergroupedit');

    $usergroupdata = array();
    $usergroup_id = SP()->filters->integer($_POST['usergroup_id']);
    $usergroupdata['usergroup_name'] = SP()->saveFilters->title(trim($_POST['usergroup_name']));
    $usergroupdata['usergroup_desc'] = SP()->saveFilters->title(trim($_POST['usergroup_desc']));
    $usergroupdata['usergroup_badge'] = SP()->saveFilters->filename(trim($_POST['usergroup_badge']));
    if (isset($_POST['usergroup_join'])) { $usergroupdata['usergroup_join'] = 1; } else { $usergroupdata['usergroup_join'] = 0; }
    if (isset($_POST['hide_stats'])) { $usergroupdata['hide_stats'] = 1; } else { $usergroupdata['hide_stats'] = 0; }
    if (isset($_POST['usergroup_is_moderator'])) { $usergroupdata['usergroup_is_moderator'] = 1; } else { $usergroupdata['usergroup_is_moderator'] = 0; }

    # update the user group info
	$sql = 'UPDATE '.SPUSERGROUPS.' SET ';
	$sql.= 'usergroup_name="'.$usergroupdata['usergroup_name'].'", ';
	$sql.= 'usergroup_desc="'.$usergroupdata['usergroup_desc'].'", ';
	$sql.= 'usergroup_badge="'.$usergroupdata['usergroup_badge'].'", ';
	$sql.= 'usergroup_join="'.$usergroupdata['usergroup_join'].'", ';
	$sql.= 'hide_stats="'.$usergroupdata['hide_stats'].'", ';
	$sql.= 'usergroup_is_moderator="'.$usergroupdata['usergroup_is_moderator'].'" ';
	$sql.= "WHERE usergroup_id=$usergroup_id";
    $success = SP()->DB->execute($sql);

    SP()->user->reset_memberships();

	# rebuiuld the new user list just in case
	SP()->user->rebuild_new();

    if ($success == false) {
        $mess = SP()->primitives->admin_text('User group update failed');
    } else {
        $mess = SP()->primitives->admin_text('User group record updated');
        do_action('sph_usergroup_new', $usergroup_id);
    }
    return $mess;
}

function spa_save_usergroups_delete_usergroup() {
    check_admin_referer('forum-adminform_usergroupdelete', 'forum-adminform_usergroupdelete');

    $usergroup_id = SP()->filters->integer($_POST['usergroup_id']);

    # dont allow updates to the default user groups
    $usergroup = spa_get_usergroups_row($usergroup_id);
    if ($usergroup->usergroup_locked) {
        $mess = SP()->primitives->admin_text('Sorry, the default User Groups cannot be deleted');
        return $mess;
    }

    # remove all memberships for this user group
    SP()->DB->execute("DELETE FROM ".SPMEMBERSHIPS." WHERE usergroup_id=".$usergroup_id);

	# remove any permission sets using this user group
	$permissions = SP()->DB->table(SPPERMISSIONS, "usergroup_id=$usergroup_id");
	if ($permissions) {
		foreach ($permissions as $permission) {
			spa_remove_permission_data($permission->permission_id);
		}
	}

	# remove any group default permissions using this user group
	SP()->DB->execute("DELETE FROM ".SPDEFPERMISSIONS." WHERE usergroup_id=".$usergroup_id);

    # remove the user group
   	SP()->DB->execute("DELETE FROM ".SPMEMBERSHIPS." WHERE usergroup_id=".$usergroup_id);
    $success = SP()->DB->execute("DELETE FROM ".SPUSERGROUPS." WHERE usergroup_id=".$usergroup_id);
    if ($success == false) {
        $mess = SP()->primitives->admin_text('User group delete failed');
    } else {
        $mess = SP()->primitives->admin_text('User group deleted');

        # reset auths and memberships for everyone
        SP()->user->reset_memberships();
        SP()->auths->reset_cache();

		# rebuiuld the new user list just in case
		SP()->user->rebuild_new();

        do_action('sph_usergroup_del', $usergroup_id);
    }

    return $mess;
}

function spa_save_usergroups_add_members() {
	return SP()->primitives->admin_etext('Please Wait - Processing');
}

function spa_save_usergroups_delete_members() {
	return SP()->primitives->admin_etext('Please Wait - Processing');
}

function spa_save_usergroups_map_settings() {
	global $wp_roles;

	check_admin_referer('forum-adminform_mapusers', 'forum-adminform_mapusers');

	# save default usergroups
	SP()->meta->add('default usergroup', 'sfguests', SP()->filters->integer($_POST['sfguestsgroup'])); # default usergroup for guests
	SP()->meta->add('default usergroup', 'sfmembers', SP()->filters->integer($_POST['sfdefgroup'])); # default usergroup for members

	# check for changes in wp role usergroup assignments
	if (isset($_POST['sfrole'])) {
		$roles = array_keys($wp_roles->role_names);
		foreach ($_POST['sfrole'] as $index => $role) {
			if ($_POST['sfoldrole'][$index] != $role) SP()->meta->add('default usergroup', $roles[$index], SP()->filters->integer($role));
		}
	}

	$sfmemberopts = SP()->options->get('sfmemberopts');
    $sfmemberopts['sfsinglemembership'] = isset($_POST['sfsinglemembership']);
	SP()->options->update('sfmemberopts', $sfmemberopts);

	$mess = SP()->primitives->admin_text('User mapping settings saved');
    do_action('sph_option_map_settings_save');
	return $mess;
}

function spa_save_usergroups_map_users() {
	return SP()->primitives->admin_etext('Please Wait - Processing');
}
