<?php
/*
Simple:Press
Admin Permissions Support Functions
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to create a new permission set role
function spa_save_permissions_new_role() {
	global $spGlobals;

	sp_build_site_auths_cache();

	check_admin_referer('forum-adminform_rolenew', 'forum-adminform_rolenew');

	$new_auths = array();
	if (isset($_POST['role']) && $_POST['role'] != -1) {
		$role = spa_get_role_row(sp_esc_int($_POST['role']));
		$new_auths = $role->role_auths;
	} else {
		foreach ($spGlobals['auths_map'] as $auth_name => $auth_id) {
			$thisperm = (isset($_POST['b-'.$auth_id])) ? 1 : 0;
			$new_auths[$auth_id] = $thisperm;
		}
		$new_auths = serialize($new_auths);
	}

	$role_name = sp_filter_title_save(trim($_POST['role_name']));
	$role_desc = sp_filter_title_save(trim($_POST['role_desc']));

    if (empty($role_name)) {
		$mess = spa_text('New permission set creation failed - permission set name required');
    	return $mess;
    }

	# force max size
	$role_name = substr($role_name, 0, 50);
	$role_desc = substr($role_desc, 0, 150);

	# create the permission set
	$success = spa_create_role_row($role_name, $role_desc, $new_auths, true);
	if ($success == false) {
		$mess = spa_text('New permission set creation failed');
	} else {
		do_action('sph_perms_add', $role_id);

		$mess = spa_text('New permission set created');
	}

	return $mess;
}

# function to update a current permission set role
function spa_save_permissions_edit_role() {
	global $spGlobals;

	sp_build_site_auths_cache();

	check_admin_referer('forum-adminform_roleedit', 'forum-adminform_roleedit');

	$role_id = sp_esc_int($_POST['role_id']);
	$role_name = sp_filter_title_save(trim($_POST['role_name']));
	$role_desc = sp_filter_title_save(trim($_POST['role_desc']));

	# get old permissions to check role changes
	$old_roles = spa_get_role_row($role_id);
	$old_auths = unserialize($old_roles->role_auths);

	$new_auths = array();
	foreach ($spGlobals['auths_map'] as $auth_name => $auth_id) {
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
	$new_auths = sp_esc_sql($new_auths);
	$sql = 'UPDATE '.SFROLES.' SET ';
	$sql.= 'role_name="'.$roledata['role_name'].'", ';
	$sql.= 'role_desc="'.$roledata['role_desc'].'", ';
	$sql.= 'role_auths="'.$new_auths.'" ';
	$sql.= "WHERE role_id=$role_id";
	$success = spdb_query($sql);

	if ($success == false) {
		$mess = spa_text('Permission Set Update Failed!');
	} else {
		$mess = spa_text('Permission Set Updated');

		# reset auths and memberships for everyone
		sp_reset_memberships();
		sp_reset_auths();

		do_action('sph_perms_edit', $role_id);
	}

	return $mess;
}

# function to remove a permission set role
function spa_save_permissions_delete_role() {
	check_admin_referer('forum-adminform_roledelete', 'forum-adminform_roledelete');

	$role_id = sp_esc_int($_POST['role_id']);

	# remove all permission set that use the role we are deleting
	$permissions = spdb_table(SFPERMISSIONS, "permission_role=$role_id");
	if ($permissions) {
		foreach ($permissions as $permission) {
			spa_remove_permission_data($permission->permission_id);
		}
	}

	# reset auths and memberships for everyone
	sp_reset_memberships();
	sp_reset_auths();

	# remove the permission set role
	$success = spdb_query('DELETE FROM '.SFROLES." WHERE role_id=$role_id");
	if ($success == false) {
		$mess = spa_text('Permission det deletion failed');
	} else {
		do_action('sph_perms_del', $role_id);

		$mess = spa_text('Permission set deleted');
	}

	return $mess;
}

function spa_save_permissions_reset() {
	check_admin_referer('forum-adminform_resetpermissions', 'forum-adminform_resetpermissions');

	# remove existing auths and authcats
	spdb_query('TRUNCATE '.SFAUTHS);
	spdb_query('TRUNCATE '.SFAUTHCATS);

    # set up the default auths/authcats
    spa_setup_auth_cats();
    spa_setup_auths();

	# remove existing roles and permissions
	spdb_query('TRUNCATE '.SFROLES);
	spdb_query('TRUNCATE '.SFPERMISSIONS);
	spdb_query('TRUNCATE '.SFDEFPERMISSIONS);

    # set up the default permissions/roles
    spa_setup_permissions();

    # signal action for plugins
    do_action('sph_permissions_reset');

    # output status
	$mess = spa_text('Permissions reset');
	return $mess;
}

function spa_save_permissions_new_auth() {
	check_admin_referer('forum-adminform_authnew', 'forum-adminform_authnew');

	# create the auth
    if (!empty($_POST['auth_name'])) {
    	$active = (isset($_POST['auth_active'])) ? 1 : 0;
    	$ignored = (isset($_POST['auth_guests'])) ? 1 : 0;
    	$enabling = (isset($_POST['auth_enabling'])) ? 1 : 0;
        $result = sp_add_auth(sp_filter_title_save($_POST['auth_name']), sp_filter_title_save($_POST['auth_desc']), $active, $ignored, $enabling);
        if ($result) {
            # reset the auths to account for new auth
        	sp_reset_auths();

        	$mess = spa_text('New auth added');
        } else {
        	$mess = spa_text('New auth failed - duplicate auth?');
        }
    } else {
    	$mess = spa_text('New auth failed - missing data');
    }
	return $mess;
}?>