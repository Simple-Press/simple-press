<?php
/*
Simple:Press
Admin Permissions Support Functions
$LastChangedDate: 2018-11-02 12:10:58 -0500 (Fri, 02 Nov 2018) $
$Rev: 15789 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to create a new permission set role
function spa_save_permissions_new_role() {
	sp_build_site_auths_cache();

	check_admin_referer('forum-adminform_rolenew', 'forum-adminform_rolenew');

	$new_auths = array();
	if (isset($_POST['role']) && (int) $_POST['role'] != -1) {
		$role = spa_get_role_row(SP()->filters->integer($_POST['role']));
		$new_auths = $role->role_auths;
	} else {
		foreach (SP()->core->forumData['auths_map'] as $auth_id) {
			$thisperm = (isset($_POST['b-'.$auth_id])) ? 1 : 0;
			$new_auths[$auth_id] = $thisperm;
		}
		$new_auths = serialize($new_auths);
	}

	$role_name = SP()->saveFilters->title(trim($_POST['role_name']));
	$role_desc = SP()->saveFilters->title(trim($_POST['role_desc']));

    if (empty($role_name)) {
		$mess = SP()->primitives->admin_text('New permission set creation failed - permission set name required');
    	return $mess;
    }

	# force max size
	$role_name = substr($role_name, 0, 50);
	$role_desc = substr($role_desc, 0, 150);

	# create the permission set
	$success = spa_create_role_row($role_name, $role_desc, $new_auths, true);
	if ($success == false) {
		$mess = SP()->primitives->admin_text('New permission set creation failed');
	} else {
		do_action('sph_perms_add');

		$mess = SP()->primitives->admin_text('New permission set created');
	}

	return $mess;
}

# function to update a current permission set role
function spa_save_permissions_edit_role() {
	sp_build_site_auths_cache();

	check_admin_referer('forum-adminform_roleedit', 'forum-adminform_roleedit');

	$role_id = SP()->filters->integer($_POST['role_id']);
	$role_name = SP()->saveFilters->title(trim($_POST['role_name']));
	$role_desc = SP()->saveFilters->title(trim($_POST['role_desc']));

	# get old permissions to check role changes
	$old_roles = spa_get_role_row($role_id);

	$new_auths = array();
	foreach (SP()->core->forumData['auths_map'] as $auth_id) {
		$thisperm = (isset($_POST['b-'.$auth_id])) ? 1 : 0;
		$new_auths[$auth_id] = $thisperm;
	}
	$new_auths = maybe_serialize($new_auths);

	$roledata = array();
	$roledata['role_name'] = $role_name;
	$roledata['role_desc'] = $role_desc;

	# force max size
	$roledata['role_name'] = substr($roledata['role_name'], 0, 50);
	$roledata['role_desc'] = substr($roledata['role_desc'], 0, 150);

	# save the permission set role updated information
	$new_auths = SP()->filters->esc_sql($new_auths);
	$sql = 'UPDATE '.SPROLES.' SET ';
	$sql.= 'role_name="'.$roledata['role_name'].'", ';
	$sql.= 'role_desc="'.$roledata['role_desc'].'", ';
	$sql.= 'role_auths="'.$new_auths.'" ';
	$sql.= "WHERE role_id=$role_id";
	$success = SP()->DB->execute($sql);

	if ($success == false) {
		$mess = SP()->primitives->admin_text('Permission Set Update Failed!');
	} else {
		$mess = SP()->primitives->admin_text('Permission Set Updated');

		# reset auths and memberships for everyone
		SP()->user->reset_memberships();
		SP()->auths->reset_cache();

		do_action('sph_perms_edit', $role_id);
	}

	return $mess;
}

# function to remove a permission set role
function spa_save_permissions_delete_role() {
	check_admin_referer('forum-adminform_roledelete', 'forum-adminform_roledelete');

	$role_id = SP()->filters->integer($_POST['role_id']);

	# remove all permission set that use the role we are deleting
	$permissions = SP()->DB->table(SPPERMISSIONS, "permission_role=$role_id");
	if ($permissions) {
		foreach ($permissions as $permission) {
			spa_remove_permission_data($permission->permission_id);
		}
	}

	# reset auths and memberships for everyone
	SP()->user->reset_memberships();
	SP()->auths->reset_cache();

	# remove the permission set role
	$success = SP()->DB->execute('DELETE FROM '.SPROLES." WHERE role_id=$role_id");
	if ($success == false) {
		$mess = SP()->primitives->admin_text('Permission det deletion failed');
	} else {
		do_action('sph_perms_del', $role_id);

		$mess = SP()->primitives->admin_text('Permission set deleted');
	}

	return $mess;
}

function spa_save_permissions_reset() {
	check_admin_referer('forum-adminform_resetpermissions', 'forum-adminform_resetpermissions');

	# remove existing auths and authcats
	SP()->DB->truncate(SPAUTHS);
	SP()->DB->truncate(SPAUTHCATS);

    # set up the default auths/authcats
    spa_setup_auth_cats();
    spa_setup_auths();

	# remove existing roles and permissions
	SP()->DB->truncate(SPROLES);
	SP()->DB->truncate(SPPERMISSIONS);
	SP()->DB->truncate(SPDEFPERMISSIONS);

    # set up the default permissions/roles
    spa_setup_permissions();

    # signal action for plugins
    do_action('sph_permissions_reset');

    # output status
	$mess = SP()->primitives->admin_text('Permissions reset');
	return $mess;
}

function spa_save_permissions_new_auth() {
	check_admin_referer('forum-adminform_authnew', 'forum-adminform_authnew');

	# create the auth
	if (!empty($_POST['auth_name'])) {
		$active   = (isset($_POST['auth_active'])) ? 1 : 0;
		$ignored  = (isset($_POST['auth_guests'])) ? 1 : 0;
		$enabling = (isset($_POST['auth_enabling'])) ? 1 : 0;
		$result   = SP()->auths->add(SP()->saveFilters->title($_POST['auth_name']), SP()->saveFilters->title($_POST['auth_desc']), $active, $ignored, $enabling);
		if ($result) {
			# reset the auths to account for new auth
			SP()->auths->reset_cache();

			$mess = SP()->primitives->admin_text('New auth added');
		} else {
			$mess = SP()->primitives->admin_text('New auth failed - duplicate auth?');
		}
	} else {
		$mess = SP()->primitives->admin_text('New auth failed - missing data');
	}

	return $mess;
}
