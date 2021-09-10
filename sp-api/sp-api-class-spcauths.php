<?php

/**
 * Core class used for utilizing Simple Press authorizations.
 *
 * This class is used to access the authorization code within Simple:Press
 *
 * @since 6.0
 *
 * Public methods available:
 *------------------------
 * add($name, $desc, $active, $ignored, $enabling, $negate, $auth_cat, $warning)
 * delete($id_or_name)
 * activate($name)
 * deactivate($name)
 * get($check, $id, $user)
 * reset_cache($userid)
 * rebuild_cache($userid)
 * forum_admin($userid)
 * forum_mod($userid)
 * single_forum_user()
 * current_user_can($cap)
 * can_view($forumid, $view, $userid = 0, $posterid = 0, $topicid = 0, $postid = 0)
 * create_cat($name, $desc)
 * delete_cat($id_or_name)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcAuths {
	/**
	 *
	 * @var array    default auth categories
	 *
	 * @since 6.0
	 */
	private $auth_cats = array(1 => 'general', 2 => 'viewing', 3 => 'creating', 4 => 'editing', 5 => 'deleting', 6 => 'moderation', 7 => 'tools', 8 => 'uploading');

	/**
	 * This method creates a new authorization that can be applied to various features and functions.
	 * After creating the auth, the auths cache is reset.
	 *
	 * After creating the auth, the auth_id is available in SP()->pageData['insertid']
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string  $name     name of new auth - meet title reqs
	 * @param string  $desc     description of new auth - no html and meet title reqs
	 * @param bool    $active   whether create auth should also be activated
	 * @param bool    $ignored  should the new auth be ignored for guests
	 * @param bool    $enable   does the new auth require activating of a feature for it to work
	 * @param bool    $negate   does the auth require negating for effect on admins
	 * @param integer $auth_cat authorization category to include this auth in
	 * @param string  $warning  if included, this warning string will appear on the permission panel in the admin
	 *
	 * @returns    bool    true if successful, otherwise false
	 */
	public function add($name, $desc, $active = 1, $ignored = 0, $enabling = 0, $negate = 0, $auth_cat = 1, $warning = '') {
		$success = false;

		# make sure the auth doesnt already exist before we create it
		$name = SP()->saveFilters->title($name);

		$query         = new stdClass();
		$query->type   = 'var';
		$query->table  = SPAUTHS;
		$query->fields = 'auth_id';
		$query->where  = "auth_name='$name'";
		$auth          = SP()->DB->select($query);
		if (empty($auth)) {
			
			/* -- 05-05-2019: Removing this section because it doesn't seem to be required.  Why check for a slug when we've already checked for the keyname?
			# ensure we get the right auth cat id in case users are ordered in a non-standard sequence
			$query         = new stdClass();
			$query->type   = 'var';
			$query->table  = SPAUTHCATS;
			$query->fields = 'authcat_id';
			$query->where  = "authcat_slug='".$this->auth_cats[$auth_cat]."'";
			$thisCat       = SP()->DB->select($query);
			if (empty($thisCat)) $thisCat = 1;
			*/
			$thisCat = $auth_cat ;
			
			$desc = SP()->saveFilters->title($desc);

			# insert the new auth into the database
			$query         = new stdClass();
			$query->table  = SPAUTHS;
			$query->fields = array('auth_name', 'auth_desc', 'active', 'ignored', 'enabling', 'admin_negate', 'auth_cat', 'warning');
			$query->data   = array($name, $desc, $active, $ignored, $enabling, $negate, $thisCat, $warning);
			$success       = SP()->DB->insert($query);

			# if successful, lets add it to the roles to keep things in sync
			if ($success) {
				$auth_id      = SP()->rewrites->pageData['insertid'];
				$query        = new stdClass();
				$query->type  = 'set';
				$query->table = SPROLES;
				$roles        = SP()->DB->select($query);
				foreach ($roles as $role) {
					$actions           = unserialize($role->role_auths);
					$actions[$auth_id] = 0;

					$query         = new stdClass;
					$query->table  = SPROLES;
					$query->fields = array('role_auths');
					$query->data   = serialize($actions);
					$query->where  = "role_id=$role->role_id";
					SP()->DB->update($query);
				}

				# reset auths after adding new auth
				$this->reset_cache();
			}
		}

		return $success;
	}

	/**
	 * This method deletes a current authorization. After deleting the auth, the auths cache is reset.
	 * This method creates a new authorization that can be applied to various features and functions
	 *
	 * After creating the auth, the auth_id is available in SP()->pageData['insertid']
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int /string    $id_or_name        contains the auth id or the auth name
	 *
	 * @returns    bool    true if successful, otherwise false
	 */
	public function delete($id_or_name) {
		# if its not id, lets get the id for easy removal of auth from roles
		if (!is_numeric($id_or_name)) $id_or_name = SP()->DB->table(SPAUTHS, 'auth_name="'.$id_or_name.'"', 'auth_id');

		# if we have an empty id or name just return success.
		if (empty($id_or_name)) return true ;
		
		# otherwise, proceed - lets delete the auth
		$query        = new stdClass();
		$query->table = SPAUTHS;
		$query->where = "auth_id=$id_or_name";
		$success      = SP()->DB->delete($query);

		# if successful, need to remove that auth from the roles
		if ($success) {
			$query        = new stdClass();
			$query->type  = 'set';
			$query->table = SPROLES;
			$roles        = SP()->DB->select($query);
			foreach ($roles as $role) {
				$actions = unserialize($role->role_auths);
				unset($actions[$id_or_name]);

				$query         = new stdClass;
				$query->table  = SPROLES;
				$query->fields = 'role_auths';
				$query->data   = serialize($actions);
				$query->where  = "role_id=$role->role_id";
				SP()->DB->update($query);
			}

			# reset auths after deleting
			$this->reset_cache();
		}

		return $success;
	}

	/**
	 * This method activates the specified auth.  After activation, the auths cache is reset.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name the auth name to be activated
	 *
	 * @returns    bool    true if successful, otherwise false
	 */
	public function activate($name) {
		$query         = new stdClass;
		$query->table  = SPAUTHS;
		$query->fields = array('active');
		$query->data   = array(1);
		$query->where  = "auth_name='$name'";
		$success       = SP()->DB->update($query);

		if ($success) $this->reset_cache();

		return $success;
	}

	/**
	 * This method deactivates the specified auth.  After deactivation, the auths cache is reset.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name the auth name to be deactivated
	 *
	 * @returns    bool    true if successful, otherwise false
	 */
	public function deactivate($name) {
		$query         = new stdClass;
		$query->table  = SPAUTHS;
		$query->fields = array('active');
		$query->data   = array(0);
		$query->where  = "auth_name='$name'";
		$success       = SP()->DB->update($query);

		if ($success) $this->reset_cache();

		return $success;
	}

	/**
	 * This method tests the specified user for the specified forum for the specified auth.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string     $check  auth name to be checked
	 * @param int|string $id     | global    forum id to use for auth check.  If 'global' the combined auth is used.
	 * @param int        $userid user id to have auth checked.  If empty, the current user is used.
	 *
	 * @returns    int        returns 1 if auth check passed, otherwise 0
	 */
	public function get($check, $id = 'global', $user = '') {
		if (SP()->core->status != 'ok') return 0;

		if (empty($id)) $id = 'global';

		# check if for current user or specified user
		if (empty($user) || (isset(SP()->user->thisUser) && $user == SP()->user->thisUser->ID)) {
			# retrieve the current user auth
			if (empty(SP()->user->thisUser->auths[$id][SP()->core->forumData['auths_map'][$check]])) {
				$auth = 0;
			} else {
				$auth = SP()->user->thisUser->auths[$id][SP()->core->forumData['auths_map'][$check]];
			}
			# is this a guest and auth should be ignored?
			if (empty(SP()->user->thisUser->ID) && SP()->core->forumData['auths'][SP()->core->forumData['auths_map'][$check]]->ignored) $auth = 0;
		} else {
			# see if we have a user object passed in with auths defined
			if (is_object($user) && is_array($user->auths)) {
				$user_auths = $user->auths;
			} else {
				#retrieve auth for specified user
				$user_auths = SP()->memberData->get($user, 'auths');
				if (empty($user_auths)) $user_auths = $this->rebuild_cache($user);
			}
			$auth = (empty($user_auths[$id][SP()->core->forumData['auths_map'][$check]])) ? 0 : $user_auths[$id][SP()->core->forumData['auths_map'][$check]];
		}

		return apply_filters("sp_get_auth_{$check}" , ((int)$auth == 1), $id, $user );
	}

	/**
	 * This method resets (empties) the auths cache.  On next auth get, the cache will be rebuilt.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid the user id to have auths reset.  If empty, all user auths are reset.
	 *
	 * @returns    bool    true if reset cache cleared, otherwise false
	 */
	public function reset_cache($userid = '') {
		$query         = new stdClass;
		$query->table  = SPMEMBERS;
		$query->fields = array('auths');
		$query->data   = array('');
		if (!empty($userid)) $query->where = "user_id=$userid";
		$success = SP()->DB->update($query);

		# reset guest auths if global update
		if (empty($userid)) SP()->options->update('sf_guest_auths', '');

		return $success;
	}

	/**
	 * This method rebuilds the auths cache for the specified user.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid the user id to have auths rebuilt.
	 *
	 * @returns    array    the rebuilt auths cache
	 */
	public function rebuild_cache($userid) {
		$user_auths           = array();
		$user_auths['global'] = array();

		if (SP()->auths->forum_admin($userid)) {
			# forum admins get full auths
			$query        = new stdClass();
			$query->type  = 'set';
			$query->table = SPFORUMS;
			$forums       = SP()->DB->select($query);
			if ($forums) {
				foreach ($forums as $forum) {
					foreach (SP()->core->forumData['auths_map'] as $auth) {
						if (SP()->core->forumData['auths'][$auth]->admin_negate) {
							$user_auths[$forum->forum_id][$auth] = 0;
							$user_auths['global'][$auth]         = 0;
						} else {
							$user_auths[$forum->forum_id][$auth] = 1;
							$user_auths['global'][$auth]         = 1;
						}
					}
				}
			}
		} else {
			$memberships = SP()->user->get_memberships($userid);
			if (empty($memberships)) {
				$value                          = SP()->meta->get('default usergroup', 'sfguests');
				$memberships = array();
				$memberships[0]['usergroup_id'] = $value[0]['meta_value'];
			}

			# no memberships means no permissions
			if (empty($memberships)) return array();

			# get the roles
			$query        = new stdClass();
			$query->type  = 'set';
			$query->table = SPROLES;
			$roles_data   = SP()->DB->select($query);
			$roles        = array();
			foreach ($roles_data as $role) {
				$roles[$role->role_id] = unserialize($role->role_auths);
			}

			# now build auths for user
			foreach ($memberships as $membership) {
				# get the permissions for the membership
				$query        = new stdClass();
				$query->type  = 'set';
				$query->table = SPPERMISSIONS;
				$query->where = 'usergroup_id='.$membership['usergroup_id'];
				$permissions  = SP()->DB->select($query);
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
				SP()->memberData->update($userid, 'auths', $user_auths);
			} else {
				SP()->options->update('sf_guest_auths', $user_auths);
			}
		}

		return $user_auths;
	}

	/**
	 * This method checks if the specificed user is a forum admin.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid the user id to check if forum admin.
	 *
	 * @returns    bool    returns true if the user is a forum admin, otherwise false
	 */
	public function forum_admin($userid) {
		$is_admin = 0;
		if ($userid) {
			if (is_multisite() && is_super_admin($userid)) {
				$is_admin = 1;
			} else {
				# in case we need this too early...
				if (!isset(SP()->core->forumData['forum-admins']) || empty(SP()->core->forumData['forum-admins'])) {
					SP()->core->forumData['forum-admins'] = sp_get_admins();
				}
				$is_admin = array_key_exists($userid, SP()->core->forumData['forum-admins']);
			}
		}

		return $is_admin;
	}

	/**
	 * This method check if the specified user is a forum moderator.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid the user id to check is forum moderator.
	 *
	 * @returns    bool    returns true if the user is a forum moderator, otherwise false
	 */
	public function forum_mod($userid) {
		$is_mod = 0;

		$mods = SP()->meta->get_value('forum_moderators');
		if ($userid && !empty($mods)) {
			foreach ($mods as $x) {
				foreach ($x as $y) {
					if ($y['user_id'] == $userid) $is_mod = true;
				}
			}
		}

		return $is_mod;
	}

	/**
	 * This method checks to see if the current user has permission to only view a single forum.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @returns    bool    returns true if the current user can only view a single foruum, otherwise false
	 */
	public function single_forum_user() {
		$fid  = '';
		$cnt  = 0;
		$auth = SP()->core->forumData['auths_map']['view_forum'];
		if (SP()->user->thisUser->auths) {
			foreach (SP()->user->thisUser->auths as $key => $set) {
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

	/**
	 * This method extends the WP current_user_can() function for checking admin capabilities.
	 * If there are no wp admins defined, this capability check will allow all WP admins
	 * to have SP admin capabilities.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $cap capability to check for on the current user.
	 *
	 *     * @returns    bool    returns true if the current user has the specified capability, otherwise false
	 */
	public function current_user_can($cap) {
		# if there are no SPF admins defined, revert to allowing all WP admins so forum admin isn't locked out
		$allow_wp_admins = (empty(SP()->core->forumData['forum-admins']) && is_super_admin()) ? true : false;

		return (current_user_can($cap) || $allow_wp_admins);
	}

	/**
	 * This method is used to determine if a user has authorization to view forum elements in support of the
	 * sneak peek feature.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $forumid  forum id where can_view() is being checked.
	 * @param int $view     forum element to check for authorization.
	 * @param int $userid   user id to check for authorization - uses current user if not given.
	 * @param int $posterid user id of the post author.
	 * @param int $topicid  topic id where can_view() is being checked.
	 * @param int $postid   post id where can_view() is being checked.
	 *
	 * @returns    bool    returns true if the current user has the specified capability, otherwise false
	 */
	public function can_view($forumid, $view, $userid = 0, $posterid = 0, $topicid = 0, $postid = 0) {
		# bail if awaiting upgrade since no forums are visible
		if (!isset(SP()->core->status) || (isset(SP()->core->status) && SP()->core->status != 'ok')) return false;

		# return false for any disabled forums since they are not shown on front end
		if (in_array($forumid, SP()->core->forumData['disabled_forums'])) return false;

		# make sure we at least use the current user
		if (empty($userid)) $userid = SP()->user->thisUser->ID;

		$auth = false;

		switch ($view) {
			case 'forum-title':
				$auth = (SP()->auths->get('view_forum', $forumid, $userid) || SP()->auths->get('view_forum_lists', $forumid, $userid) || SP()->auths->get('view_forum_topic_lists', $forumid, $userid));
				$auth = apply_filters('sph_auth_view_forum_title', $auth, $forumid, $view, $userid, $posterid);
				break;

			case 'topic-title':
				$auth = (SP()->auths->get('view_forum', $forumid, $userid) || SP()->auths->get('view_forum_topic_lists', $forumid, $userid));
				$auth = apply_filters('sph_auth_view_topic_title', $auth, $forumid, $view, $userid, $posterid);
				break;

			case 'post-content':
				$auth = (SP()->auths->get('view_forum', $forumid, $userid) && (!SP()->auths->forum_admin($posterid) || SP()->auths->get('view_admin_posts', $forumid, $userid)) && (SP()->auths->forum_admin($posterid) || SP()->auths->forum_mod($posterid) || $userid == $posterid || !SP()->auths->get('view_own_admin_posts', $forumid, $userid)));
				$auth = apply_filters('sph_auth_view_post_content', $auth, $forumid, $view, $userid, $posterid, $topicid, $postid);
				break;

			default:
				$auth = apply_filters('sph_auth_view_'.$view, $auth, $forumid, $view, $userid, $posterid, $topicid, $postid);
				break;
		}

		$auth = apply_filters('sph_auth_view', $auth, $forumid, $view, $userid, $posterid);

		return $auth;
	}

	/**
	 * This method creates a new auth category used when displaying permission sets.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string  $name  name to be used for the new auth cagetgory.
	 * @param string  $descr description to be used for the new auth category.
	 * @param integer $id    the number being assigned to this auth cat
	 *
	 *     * @returns    bool    returns true if the new auth category was created, otherwise false
	 */
	public function create_cat($name, $desc, $id) {
		$success = false;

		# make sure the auth category doesnt already exist before we create it
		$query         = new stdClass();
		$query->type   = 'var';
		$query->table  = SPAUTHCATS;
		$query->fields = 'authcat_id';
		$query->where  = "authcat_name='".SP()->saveFilters->title($name)."'";
		$auth          = SP()->DB->select($query);

		if (empty($auth)) {
			$desc = SP()->saveFilters->title($desc);
			$slug = sp_create_slug($name, true, SPAUTHCATS, 'authcat_slug');

			$query         = new stdClass();
			$query->table  = SPAUTHCATS;
			$query->fields = array('authcat_id', 'authcat_name', 'authcat_slug', 'authcat_desc');
			$query->data   = array($id, $name, $slug, $desc);
			$success       = SP()->DB->insert($query);
		}

		return $success;
	}

	/**
	 * This method deletes an existing auth category used when displaying permission sets.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int | string $id_or_name auth categroy id or name tha is to be deleted.
	 *
	 *     * @returns    bool    returns true if the auth category was deleted, otherwise false
	 */
	public function delete_cat($id_or_name) {
		# if its not id, lets get the id for easy removal of auth cat from auths
		if (!is_numeric($id_or_name)) {
			$slug = sp_create_slug($id_or_name, true, SPAUTHCATS, 'authcat_slug');

			$query         = new stdClass();
			$query->type   = 'var';
			$query->table  = SPAUTHCATS;
			$query->fields = 'authcat_id';
			$query->where  = "authcat_slug='$slug'";
			$id_or_name    = SP()->DB->select($query);
		}

		# now lets delete the auth cat
		$query        = new stdClass();
		$query->table = SPAUTHCATS;
		$query->where = "authcat_id=$id_or_name";
		$success      = SP()->DB->delete($query);

		# if successful, need to remove that cat from the auths and replace with default
		if ($success) {
			$query         = new stdClass;
			$query->table  = SPAUTHS;
			$query->fields = array('auth_cat');
			$query->data   = array(0);
			$query->where  = "auth_cat=$id_or_name";			
			SP()->DB->update($query);
		}

		return $success;
	}
}