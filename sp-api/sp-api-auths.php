<?php
/*
Simple:Press
Auths Model forum rendering helper functions
$LastChangedDate: 2017-01-25 11:21:06 -0600 (Wed, 25 Jan 2017) $
$Rev: 15097 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	SP Authorisation and Permission Routines
#
#   $user may be user object or user id (best to pass object if available to save query)
# ==================================================================
#	Version: 5.0
function sp_get_auth($check, $id='global', $user='') {
    global $spGlobals, $spThisUser, $spStatus;

	if ($spStatus != 'ok') return 0;

	if (empty($id)) $id = 'global';

	# check if for current user or specified user
    if (empty($user) || (isset($spThisUser) && $user == $spThisUser->ID)) {
		# retrieve the current user auth
        if (empty($spThisUser->auths[$id][$spGlobals['auths_map'][$check]])) {
            $auth = 0;
        } else {
            $auth = $spThisUser->auths[$id][$spGlobals['auths_map'][$check]];
        }
		# is this a guest and auth should be ignored?
		if (empty($spThisUser->ID) && $spGlobals['auths'][$spGlobals['auths_map'][$check]]->ignored) $auth = 0;
	} else {
		# see if we have a user object passed in with auths defined
		if (is_object($user) && is_array($user->auths)) {
			$user_auths = $user->auths;
		} else  {
			#retrieve auth for specified user
			$user_auths = sp_get_member_item($user, 'auths');
			if (empty($user_auths)) $user_auths = sp_rebuild_user_auths($user);
		}
		$auth = (empty($user_auths[$id][$spGlobals['auths_map'][$check]])) ? 0 : $user_auths[$id][$spGlobals['auths_map'][$check]];
	}

	return ((int) $auth == 1);
}

#	Version: 5.0
function sp_reset_auths($userid='') {
	# reset all the members auths
	$where = '';
	if (!empty($userid)) $where = ' WHERE user_id='.$userid;

	spdb_query('UPDATE '.SFMEMBERS." SET auths=''".$where);

	# reset guest auths if global update
	if (empty($userid)) sp_update_option('sf_guest_auths', '');
}

#	Version: 5.0
function sp_rebuild_user_auths($userid) {
	global $spGlobals;

	$user_auths = array();
    $user_auths['global'] = array();

	if (sp_is_forum_admin($userid)) {
		# forum admins get full auths
		$forums = spdb_table(SFFORUMS);
		if ($forums) {
			foreach ($forums as $forum) {
				foreach ($spGlobals['auths_map'] as $auth) {
				    if ($spGlobals['auths'][$auth]->admin_negate) {
                        $user_auths[$forum->forum_id][$auth] = 0;
					    $user_auths['global'][$auth] = 0;
                    } else {
                        $user_auths[$forum->forum_id][$auth] = 1;
					    $user_auths['global'][$auth] = 1;
                    }
				}
			}
		}
	} else {
		$memberships = sp_get_user_memberships($userid);
		if (empty($memberships)) {
			$value = sp_get_sfmeta('default usergroup', 'sfguests');
			$memberships = array();
			$memberships[0]['usergroup_id'] = $value[0]['meta_value'];
		}

		# no memberships means no permissions
		if (empty($memberships)) return;

		# get the roles
		$roles_data = spdb_table(SFROLES, 0);
		foreach ($roles_data as $role) {
			$roles[$role->role_id] = unserialize($role->role_auths);
		}

		# now build auths for user
		foreach ($memberships as $membership) {
			# get the permissions for the membership
			$permissions = spdb_table(SFPERMISSIONS, 'usergroup_id='.$membership['usergroup_id']);
			if ($permissions) {
				foreach ($permissions as $permission) {
					if (!isset($user_auths[$permission->forum_id])) {
                        $user_auths[$permission->forum_id] = $roles[$permission->permission_role];
					} else {
                        foreach (array_keys($roles[$permission->permission_role]) as $auth_id) {
                            if (!isset($user_auths[$permission->forum_id][$auth_id])) {
                                $user_auths[$permission->forum_id][$auth_id] = $roles[$permission->permission_role][$auth_id];
                            } else {
                                $user_auths[$permission->forum_id][$auth_id] |= $roles[$permission->permission_role][$auth_id];
                            }
					   }
					}
					foreach ($roles[$permission->permission_role] as $auth_id => $auth) {
                        if (empty($user_auths['global'][$auth_id])) {
                            $user_auths['global'][$auth_id] = $auth;
                        } else {
                            $user_auths['global'][$auth_id] |= $auth;
                        }
					}
				}
			}
		}
	}

	# now save the user auths
	if (!empty($user_auths)) {
		if (!empty($userid)) {
			sp_update_member_item($userid, 'auths', $user_auths);
		} else {
			sp_update_option('sf_guest_auths', $user_auths);
		}
	}

	return $user_auths;
}

#	Version: 5.0
function sp_is_forum_admin($userid) {
	global $spGlobals;
	$is_admin = 0;
	if ($userid) {
		if (is_multisite() && is_super_admin($userid)) {
			$is_admin = 1;
		} else {
			# in case we need this too early...
			if (!isset($spGlobals['forum-admins']) || empty($spGlobals['forum-admins'])) {
				$spGlobals['forum-admins'] = sp_get_admins();
			}
			$is_admin = array_key_exists($userid, $spGlobals['forum-admins']);
		}
	}
	return $is_admin;
}

#	Version: 5.2
function sp_is_forum_mod($userid) {
	global $spGlobals;
	$is_mod = 0;
	if ($userid && isset($spGlobals['forum_moderators']) && !empty($spGlobals['forum_moderators'])) {
		foreach ($spGlobals['forum_moderators'] as $x) {
			foreach ($x as $y) {
				foreach ($y as $z) {
					if ($z['user_id'] == $userid) $is_mod=true;
				}
			}
		}
	}
	return $is_mod;
}


# returns false if current user can view multiple forums
# returns forum id if there is only one forum user can see
#	Version: 5.0
function sp_single_forum_user() {
	global $spThisUser, $spGlobals;
	$fid = '';
	$cnt = 0;
	$auth = $spGlobals['auths_map']['view_forum'];
	if ($spThisUser->auths) {
		foreach ($spThisUser->auths as $key => $set) {
			if (is_numeric($key)) {
				if ($set[$auth]) {
					$fid = $key;
					$cnt++;
				}
			}
		}
	}
	if ($cnt == 1) {
		return $fid;
	} else {
		return false;
	}
}

# ------------------------------------------------------------------
# sp_add_auth()
#
# Version: 5.0
# Allows plugins to create new auth
# new auth_id is available in $spVars['insertid'] after success
#
# Version: 5.0
# Returns true if successful
# Returns false if failed and displays error if sql invalid
#
#	name:		name of new auth - meet title reqs
#	desc:		desc of new auth - no html and meet title reqs
#	active:		is the auth active
#	ignored:	is the auth ignored for guests
#	enabling:	in addition to the auth, is enabling of the feature reqd
# ------------------------------------------------------------------
function sp_add_auth($name, $desc, $active=1, $ignored=0, $enabling=0, $negate=0, $auth_cat=1, $warning='') {
	global $spVars;

	$success = false;

    # make sure the auth doesnt already exist before we create it
	$name = sp_filter_title_save($name);
	$auth = spdb_table(SFAUTHS, 'auth_name="'.$name.'"', 'auth_id');
	if (empty($auth)) {
		# make auth cats array
		$cats = array(
			1 => 'general',
			2 => 'viewing',
			3 => 'creating',
			4 => 'editing',
			5 => 'deleting',
			6 => 'moderation',
			7 => 'tools',
			8 => 'uploading'
		);
		$cats = apply_filters('sph_auth_cat_list', $cats);

		# ensure we get the right auth cat id in case users are ordered in a non-standard sequence
		$thisCat = spdb_table(SFAUTHCATS, "authcat_slug='".$cats[$auth_cat]."'", 'authcat_id');
		if (empty($thisCat)) $thisCat = 1;
		$desc = sp_filter_title_save($desc);
		$sql = 'INSERT INTO '.SFAUTHS." (auth_name, auth_desc, active, ignored, enabling, admin_negate, auth_cat, warning) VALUES ('$name', '$desc', $active, $ignored, $enabling, $negate, $thisCat, '$warning')";
		$success = spdb_query($sql);

		# if successful, lets add it to the roles to keep things in sync
		if ($success) {
			$auth_id = $spVars['insertid'];
			$roles = spdb_table(SFROLES);
			foreach ($roles as $role) {
				$actions = unserialize($role->role_auths);
				$actions[$auth_id] = 0;
				spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
			}

			# reset auths if new auth added successfully
			sp_reset_auths();
		}
	}
	return $success;
}

# ------------------------------------------------------------------
# sp_delete_auth()
#
# Version: 5.0
# Allows plugins to delete an existing auth
#
# Returns true if successful
# Returns false if failed and displays error if sql invalid
#
#	$id_or_name:	id or name of auth to delete
# ------------------------------------------------------------------
function sp_delete_auth($id_or_name) {
	# if its not id, lets get the id for easy removal of auth from roles
	if (!is_numeric($id_or_name)) $id_or_name = spdb_table(SFAUTHS, 'auth_name="'.$id_or_name.'"', 'auth_id');

    # now lets delete the auth
   	$success = spdb_query('DELETE FROM '.SFAUTHS." WHERE auth_id=$id_or_name");

	# if successful, need to remove that auth from the roles
	if ($success) {
		$roles = spdb_table(SFROLES);
		foreach ($roles as $role) {
			$actions = unserialize($role->role_auths);
			unset($actions[$id_or_name]);
			spdb_query('UPDATE '.SFROLES." SET role_auths='".serialize($actions)."' WHERE role_id=$role->role_id");
		}

		# reset auths if auth was deleted
		sp_reset_auths();
	}
	return $success;
}

# ------------------------------------------------------------------
# sp_activate_auth()
#
# Version: 5.0
# Allows plugins to activate an auth that has already been created
# but may have been deactivated because the plugin was deactivate
#
# Returns true if successful
# Returns false if failed and displays error if sql invalid
#
#	name:		name of auth to activate
# ------------------------------------------------------------------
function sp_activate_auth($name) {
	$success = spdb_query('UPDATE '.SFAUTHS." SET active=1 WHERE auth_name='$name'");
	if ($success) sp_reset_auths();

	return $success;
}

# ------------------------------------------------------------------
# sp_deactivate_auth()
#
# Version: 5.0
# Allows plugins to deactivate an auth that has already been created
# and activated
#
# Returns true if successful
# Returns false if failed and displays error if sql invalid
#
#	name:		name of auth to deactivate
# ------------------------------------------------------------------
function sp_deactivate_auth($name) {
	$success = spdb_query('UPDATE '.SFAUTHS." SET active=0 WHERE auth_name='$name'");
	if ($success) sp_reset_auths();

	return $success;
}

# Version: 5.0
function sp_current_user_can($cap) {
	global $spThisUser, $spGlobals;

	# if there are no SPF admins defined, revert to allowing all WP admins so forum admin isn't locked out
	$allow_wp_admins = (empty($spGlobals['forum-admins']) && is_super_admin()) ? true : false;

	if (current_user_can($cap) || $allow_wp_admins)
		return true;
	else
		return false;
}

# Version: 5.0
function sp_get_admins() {
	$administrators = array();

	# get all the administrators
	$admins = spdb_table(SFMEMBERS, 'admin=1');
	if (!empty($admins)) {
		foreach ($admins as $admin) {
			if (!empty($admin)) $administrators[$admin->user_id] = $admin->display_name;
		}
	}
	return $administrators;
}

# Version: 5.0
function sp_get_all_roles() {
	return spdb_table(SFROLES, '', '', 'role_id');
}

# Version: 5.0
function sp_get_forum_permissions($forum_id) {
	return spdb_table(SFPERMISSIONS, "forum_id=$forum_id", '', 'permission_role');
}

# Version: 5.2
function sp_create_auth_cat($name, $desc) {
	global $spVars;

	$success = false;

    # make sure the auth category doesnt already exist before we create it
	$name = sp_filter_title_save($name);
	$auth = spdb_table(SFAUTHCATS, "authcat_name='$name'", 'authcat_id');
	if (empty($auth)) {
		$desc = sp_filter_title_save($desc);
        $slug = sp_create_slug($name, true, SFAUTHCATS, 'authcat_slug');
		$sql = 'INSERT INTO '.SFAUTHCATS." (authcat_name, authcat_slug, authcat_desc) VALUES ('$name', '$slug', '$desc')";
		$success = spdb_query($sql);
	}
	return $success;
}

# Version: 5.2
function sp_delete_auth_cat($id_or_name) {
	# if its not id, lets get the id for easy removal of auth cat from auths
	if (!is_numeric($id_or_name)) {
       $slug = sp_create_slug($id_or_name, true, SFAUTHCATS, 'authcat_slug');
	   $id_or_name = spdb_table(SFAUTHCATS, 'authcat_slug="'.$slug.'"', 'authcat_id');
    }

    # now lets delete the auth cat
   	$success = spdb_query('DELETE FROM '.SFAUTHCATS." WHERE authcat_id=$id_or_name");

	# if successful, need to remove that cat from the auths and replace with default
	if ($success) spdb_query('UPDATE '.SFAUTHS." SET auth_cat=0 WHERE authcat_id=$id_or_name");

	return $success;
}

# Version: 5.2
function sp_can_view($forumid, $view, $userid=0, $posterid=0, $topicid=0, $postid=0) {
    global $spGlobals, $spThisUser, $spStatus;

    # bail if awaiting upgrade since no forums are visible
	if (!isset($spStatus) || (isset($spStatus) && $spStatus != 'ok')) return;

    # return false for any disabled forums since they are not shown on front end
    if (in_array($forumid, $spGlobals['disabled_forums'])) return false;

    # make sure we at least use the current user
    if (empty($userid)) $userid = $spThisUser->ID;

    $auth = false;

    switch ($view) {
        case 'forum-title':
            $auth = (sp_get_auth('view_forum', $forumid, $userid) || sp_get_auth('view_forum_lists', $forumid, $userid) || sp_get_auth('view_forum_topic_lists', $forumid, $userid));
            $auth = apply_filters('sph_auth_view_forum_title', $auth, $forumid, $view, $userid, $posterid);
            break;

        case 'topic-title':
            $auth = (sp_get_auth('view_forum', $forumid, $userid) || sp_get_auth('view_forum_topic_lists', $forumid, $userid));
            $auth = apply_filters('sph_auth_view_topic_title', $auth, $forumid, $view, $userid, $posterid);
            break;

        case 'post-content':
			$auth = (sp_get_auth('view_forum', $forumid, $userid) &&
                    (!sp_is_forum_admin($posterid) || sp_get_auth('view_admin_posts', $forumid, $userid)) &&
                    (sp_is_forum_admin($posterid) || sp_is_forum_mod($posterid) || $userid == $posterid || !sp_get_auth('view_own_admin_posts', $forumid, $userid)));
            $auth = apply_filters('sph_auth_view_post_content', $auth, $forumid, $view, $userid, $posterid, $topicid, $postid);
            break;

        default:
            $auth = apply_filters('sph_auth_view_'.$view, $auth, $forumid, $view, $userid, $posterid, $topicid, $postid);
            break;
    }

    $auth = apply_filters('sph_auth_view', $auth, $forumid, $view, $userid, $posterid);
    return $auth;
}
?>