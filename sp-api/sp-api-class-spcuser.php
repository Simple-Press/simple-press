<?php

/**
 * Core class used for Simple Press user objects.  When a user object is requested through the primary
 * SP()-spcUser->get() method, a user object cache is checked for an already created object.  If it exists,
 * the cached user object is returned.  Otherwise, the user object is created and inserted into the user cache.
 * A standard user object or a small user object (contains less data) can be created depending upon the need.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * get($userid, $current, $small)
 * get_current_user()
 * create_data($userid, $install)
 * update_data($userid)
 * delete_data($userid, $blog_id, $delete_option, $reassign)
 * set_role_to_ug($userid, $role)
 * add_role_to_ug($userid, $role)
 * remove_role_to_ug($userid, $role)
 * add_membership($usergroup_id, $user_id)
 * remove_membership($usergroup_id, $user_id)
 * check_membership($usergroup_id, $user_id)
 * reset_memberships($userid)
 * get_memberships($userid)
 * get_forum_memberships()
 * update_moderator_flag($userid)
 * update_forum_moderators($forumid)
 * push_new($id, $name)
 * remove_new($id)
 * rebuild_new()
 * update_new_name($oldname, $newname)
 * update_recent()
 * visible_forums($view)
 * validate_registration($errors, $sanitized_user_login, $user_email)
 * validate_display_name($errors, $update, $user)
 * unique_display_name($startname, $modname, $suffix)
 * delete_form($user, $userids)
 * stats_status($userid, $memberships)
 *
 * $LastChangedDate: 2018-12-06 10:47:04 -0600 (Thu, 06 Dec 2018) $
 * $Rev: 15841 $
 */

include_once SP_PLUGIN_DIR.'/admin/library/spa-iconsets.php';

class spcUser {
	/**
	 *
	 * @var object    current user
	 *
	 * @since 6.0
	 */
	public $thisUser;

	/**
	 *
	 * @var object    profile user
	 *
	 * @since 6.0
	 */
	public $profileUser;

	/**
	 *
	 * @var object    guest cookie
	 *
	 * @since 6.0
	 */
	public $guest_cookie;

	/**
	 *
	 * @var array    cache of all users loaded
	 *
	 * @since 6.0
	 */
	private $data = array();

	/**
	 *
	 * @var bool    flag indicating if plugins can update data
	 *
	 * @since 6.0
	 */
	public $doPlugins = true;

	/**
	 * This method gets the requested user object.  This is the spcUser class main entry point.
	 * If it exists in the user cache, that cached object is returned, otherwise the user object is created
	 * and put in the user cache.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int    $userid  ID of the user to get the user object
	 * @param bool   $current true if the requested user is the current user
	 * @param object $small   allows for a smaller user object to be created
	 *
	 * @return object
	 */
	public function get($userid = 0, $current = false, $small = false) {
		if (!array_key_exists($userid, $this->data)) $this->data[$userid] = $this->load($userid, $current, $small);

		return (object)$this->data[$userid];
	}

	public function get_current_user() {
		global $current_user;

		if (empty($current_user)) $current_user = wp_get_current_user();
		$this->thisUser = $this->get($current_user->ID, true);

		# check for a cookie if a guest
		$this->guest_cookie               = new stdClass();
		$this->guest_cookie->name         = '';
		$this->guest_cookie->email        = '';
		$this->guest_cookie->display_name = '';

		if (!empty($this->thisUser->guest) && empty($this->thisUser->offmember)) {
			# so no record of them being a current member
			$sfguests = SP()->options->get('sfguests');
			if ($sfguests['storecookie']) {
				if (isset($_COOKIE['guestname_'.COOKIEHASH])) $this->guest_cookie->name = SP()->displayFilters->name($_COOKIE['guestname_'.COOKIEHASH]);
				if (isset($_COOKIE['guestemail_'.COOKIEHASH])) $this->guest_cookie->email = SP()->displayFilters->email($_COOKIE['guestemail_'.COOKIEHASH]);
				$this->guest_cookie->display_name = $this->guest_cookie->name;
			}
		}
	}

	/**
	 * This method creates a user object
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int    $ident   ID of the user to get the user object
	 * @param bool   $current true if the requested user is the current user
	 * @param object $small   allows for a smaller user object to be created
	 *
	 * @return object    user object for requested user
	 */
	private function load($ident = 0, $current = false, $small = false) {
		$user       = new stdClass();
		$pluginData = new stdClass();

		$id = 0;
		if (is_numeric($ident)) {
			$w = "ID=$ident";
		} else if ($ident != false) {
			$w = "user_login='$ident'";
		}
		if ($ident) {
			# Users data
			$d = SP()->DB->table(SPUSERS, $w, 'row');
			if ($d) {
				$user->ID = $d->ID;
				$id       = $d->ID;
			}
		}

		$pluginData->ID = $id;

		$includeList = $this->build_filter_list();
		if ($id) {
			# Others
			$user->member      = true;
			$user->guest       = 0;
			$user->guest_name  = '';
			$user->guest_email = '';
			$user->offmember   = false;
			$user->usertype    = 'User';

			# Users data
			foreach ($d as $key => $item) {
				if (array_key_exists($key, $includeList)) {
					$user->$key = $item;
				}
			}
			$user->user_registered = SP()->dateTime->registration_to_timezone($user->user_registered);

			# usermeta data - initialise our own ones first
			$user->location   = '';
			$user->msn        = '';
			$user->icq        = '';
			$user->skype      = '';
			$user->facebook   = '';
			$user->myspace    = '';
			$user->twitter    = '';
			$user->linkedin   = '';
			$user->youtube    = '';
			$user->googleplus = '';
			$user->instagram = '';

			$d = SP()->DB->table(SPUSERMETA, "user_id=$id");
			if ($d) {
				foreach ($d as $m) {
					$t = $m->meta_key;
					if (array_key_exists($t, $includeList)) {
						$user->$t = maybe_unserialize($m->meta_value);
					}
				}
			}

			# If awaiting installation then dive out now to avoid errors
			if (SP()->core->status == 'Install') return new StdClass;

			# sfmembers data
			$d = SP()->DB->table(SPMEMBERS, "user_id=$id", 'row');
			#check for ghost user
			if (empty($d)) {
				#create the member
				$this->create_data($id);
				$d = SP()->DB->table(SPMEMBERS, "user_id=$id", 'row');
			}
			if ($d) {
				foreach ($d as $key => $item) {
					if ($key == 'admin_options' && !empty($item)) {
						$opts = unserialize($item);
						foreach ($opts as $opt => $set) {
							$user->$opt = $set;
						}
					} else if ($key == 'user_options' && !empty($item)) {
						$opts = unserialize($item);
						foreach ($opts as $opt => $set) {
							$user->$opt = $set;
						}
					} else if ($key == 'plugin_data' && !empty($item)) {
						$pluginData      = unserialize($item);
						$this->doPlugins = false;
					} else if ($key == 'lastvisit') {
						$user->lastvisit = $item;
					} else if ($key == 'memberships') {
						$user->memberships = wp_unslash(maybe_unserialize($item));
					} else {
						$user->$key = maybe_unserialize($item);
					}
				}
			}

			# Check for new post list size
			if (!isset($user->unreadposts) || empty($user->unreadposts)) {
				$controls          = SP()->options->get('sfcontrols');
				$user->unreadposts = (empty($controls['sfunreadposts'])) ? 50 : $controls['sfunreadposts'];
			}

			# usertype for moderators
			if ($user->moderator) $user->usertype = 'Moderator';

			# check for super admins and make admin a moderator as well
			if ($user->admin || (is_multisite() && is_super_admin($id))) {
				$user->admin     = true;
				$user->moderator = true;
				$user->usertype  = 'Admin';
				$ins             = SP()->options->get('spInspect');
				if (!empty($ins) && array_key_exists($id, $ins)) {
					$user->inspect = $ins[$id];
				} else {
					$user->inspect = '';
				}
			}

			# plugins can add iterms for members...
			if ($this->doPlugins) {
				if (!$small) {
					do_action_ref_array('sph_user_class_member', array(&$pluginData));
				} else {
					do_action_ref_array('sph_user_class_member_small', array(&$pluginData));
				}
			}
		} else {
			# some basics for guests
			$user->ID              = 0;
			$user->guest           = true;
			$user->member          = 0;
			$user->admin           = false;
			$user->moderator       = false;
			$user->display_name    = 'guest';
			$user->guest_name      = '';
			$user->guest_email     = '';
			$user->usertype        = 'Guest';
			$user->offmember       = $this->check_unlogged_user();
			$user->timezone        = 0;
			$user->timezone_string = '';
			$user->posts           = 0;
			$user->avatar          = '';
			$user->user_email      = '';
			$user->auths           = SP()->options->get('sf_guest_auths');
			$user->memberships     = SP()->options->get('sf_guest_memberships');

			# plugins can add iterms for guests...
			if ($this->doPlugins) {
				if (!$small) {
					do_action_ref_array('sph_user_class_guest', array(&$pluginData));
				} else {
					do_action_ref_array('sph_user_class_guest_small', array(&$pluginData));
				}
			}
		}

		# Only perform this last section if forum is operational
		if (SP()->core->status == 'ok') {
			# Ranking
			$user->rank         = $this->forum_rank($user->usertype, $id, $user->posts);
			$user->special_rank = ($user->member) ? $this->special_ranks($id) : array();

			# if no memberships rebuild them and save
			if (empty($user->memberships)) {
				$memberships = array();
				if (!empty($id)) {
					if (!$user->admin) {
						# get the usergroup memberships for the user and save in sfmembers table
						$memberships = $this->get_memberships($id);
						SP()->memberData->update($id, 'memberships', wp_slash($memberships));
					}
				} else {
					# user is a guest or unassigned member so get the global permissions from the guest usergroup and save as option
					$value         = SP()->meta->get('default usergroup', 'sfguests');
					$memberships[] = SP()->DB->table(SPUSERGROUPS, 'usergroup_id='.$value[0]['meta_value'], 'row', '', '', ARRAY_A);
					SP()->options->update('sf_guest_memberships', wp_slash($memberships));
				}
				# put in the data
				$user->memberships = $memberships;
			}
			# if no auths rebuild them and save
			if (empty($user->auths)) $user->auths = SP()->auths->rebuild_cache($id);
		}

		$user->ip      = sp_get_ip();
		$user->trackid = -1;

		# Things to do if user is current user
		if ($current) {
			# Set up editor type
			SP()->core->forumData['editor'] = 0;

			# for a user...
			if ($user->member && !empty($user->editor)) SP()->core->forumData['editor'] = $user->editor;

			# and if not defined or is for a guest...
			if (SP()->core->forumData['editor'] == 0) {
				$defeditor = SP()->options->get('speditor');
				if (!empty($defeditor)) SP()->core->forumData['editor'] = $defeditor;
			}

			# final check to ensure selected editor type is indeed available
			if ((SP()->core->forumData['editor'] == 0) || (SP()->core->forumData['editor'] == 1 && !defined('RICHTEXT')) || (SP()->core->forumData['editor'] == 2 && !defined('HTML')) || (SP()->core->forumData['editor'] == 3 && !defined('BBCODE'))) {

				SP()->core->forumData['editor'] = PLAINTEXT;
				if (defined('BBCODE')) SP()->core->forumData['editor'] = BBCODE;
				if (defined('HTML')) SP()->core->forumData['editor'] = HTML;
				if (defined('RICHTEXT')) SP()->core->forumData['editor'] = RICHTEXT;
			}

			# Grab any notices present
			if ($user->guest && !empty($user->guest_email)) {
				$user->user_notices = SP()->DB->table(SPNOTICES, "guest_email='".$user->guest_email."'", '', $order = 'notice_id');
			} elseif ($user->member && !empty($user->user_email)) {
				$user->user_notices = SP()->DB->table(SPNOTICES, "user_id=".$user->ID, '', $order = 'notice_id');
			}

			if ($this->doPlugins) {
				# plugins can add iterms for the current user (so no small allowed here)
				do_action_ref_array('sph_current_user_class', array(&$pluginData));
			}
		}

		# Finally filter the data for display
		foreach ($includeList as $item => $filter) {
			if (property_exists($user, $item)) $user->$item = $this->filter_item($user->$item, $filter);
		}

		if ($this->doPlugins) {
			# allow plugins to add items to user class - regardless small or otherwise, current or otherwise
			do_action_ref_array('sph_user_class', array(&$pluginData));
		}

		# Add the plugn data to the user object
		foreach ($pluginData as $key => $value) {
			$user->$key = $value;
		}

		if ($this->doPlugins) {
			# add the datas back to members table
			SP()->memberData->update($user->ID, 'plugin_data', $pluginData);
		}

		# last chance for anyone to add or modify the user object
		# preference is to the the plugin_data cache for user object but if needed this hook is provided
		do_action_ref_array('sph_user_class_object', array(&$user));

		return $user;
	}

	/**
	 * This method check if the current guest user matches a registered user who is not logged in.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @return string    username of non logged in user that matched guest user
	 */
	private function check_unlogged_user() {
		if (is_user_logged_in() == true) return '';
		$sfmemberopts = SP()->options->get('sfmemberopts');
		if (isset($_COOKIE['sforum_'.COOKIEHASH]) && $sfmemberopts['sfcheckformember']) {
			# Yes it is - a user not logged in.  So grab the user name but sanitize it in case its used for anything other than a display.
			$username = SP()->displayFilters->name($_COOKIE['sforum_'.COOKIEHASH]);
			return $username;
		}

		return '';
	}

	/**
	 * This method builds a list of data to be retrieved from the WP users and usermeta tables.
	 * A desired filter the data to be retrieved is also listed.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @return array    list of WP users and usermeta data to be used and desired filters
	 */
	private function build_filter_list() {
		$includeList = array('user_login' => 'name', 'user_email' => 'email', 'user_url' => 'url', 'user_registered' => '', 'description' => 'text', 'location' => 'title', 'first_name' => 'name', 'last_name' => 'name', 'aim' => 'title', 'yim' => 'title', 'jabber' => 'title', 'msn' => 'title', 'icq' => 'title', 'skype' => 'title', 'facebook' => 'title', 'myspace' => 'title', 'twitter' => 'title', 'linkedin' => 'title', 'youtube' => 'title', 'googleplus' => 'title', 'instagram' => 'title','display_name' => 'name', 'signature' => 'signature', 'sp_change_pw' => '', 'photos' => '',);

		# allow plugins to add more usermeta class data
		$includeList = apply_filters('sph_user_class_meta', $includeList);

		return $includeList;
	}

	/**
	 * This method applies the desired filter to the WP users and usermeta data to be put into our user object.
	 *
	 * @access private
	 *
	 * @since 6.0
	 *
	 * @param string $item   item to be filtered
	 * @param string $filter name of desired filter to be applied to item
	 *
	 * @return string    filtered item
	 */
	private function filter_item($item, $filter) {
		if (is_array($item)) return $item;
		switch ($filter) {
			case 'title':
				$item = SP()->displayFilters->title($item);
				break;
			case 'email':
				$item = SP()->displayFilters->email($item);
				break;
			case 'url':
				$item = SP()->displayFilters->url($item);
				break;
			case 'text':
				$item = SP()->displayFilters->text($item);
				break;
			case 'name':
				$item = SP()->displayFilters->name($item);
				break;
			case 'signature':
				$item = SP()->displayFilters->signature($item);
				break;
		}

		return $item;
	}

	/**
	 * This method sets up the new user data row in the sfmembers table
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int  $userid  ID of the user to initialize
	 * @param bool $install true if the initial creation
	 *
	 * @return void
	 */
	public function create_data($userid, $install = false) {
		global $current_user;

		if (!$userid) return;

		if (!$install) {
			# see if member has already been created since wp multisite can fire both user creation hooks in some cases
			$user = SP()->DB->table(SPMEMBERS, "user_id=$userid", 'row');
			if ($user) return;
		}

		# Grab the data we need
		$user = SP()->DB->table(SPUSERS, "ID=$userid", 'row');

		# Display Name validation
		if (!$install) {
			$sfprofile = SP()->options->get('sfprofile');

			if ($sfprofile['nameformat']) {
				$display_name = $user->display_name;
			} else {
				$first_name = get_user_meta($userid, 'first_name', true);
				$last_name  = get_user_meta($userid, 'last_name', true);
				switch ($sfprofile['fixeddisplayformat']) {
					default:
					case '0':
						$display_name = $user->display_name;
						break;
					case '1':
						$display_name = $user->user_login;
						break;
					case '2':
						$display_name = $first_name;
						break;
					case '3':
						$display_name = $last_name;
						break;
					case '4':
						$display_name = $first_name.' '.$last_name;
						break;
					case '5':
						$display_name = $last_name.', '.$first_name;
						break;
					case '6':
						$display_name = $first_name[0].' '.$last_name;
						break;
					case '7':
						$display_name = $first_name.' '.$last_name[0];
						break;
					case '8':
						$display_name = $first_name[0].$last_name[0];
						break;
				}
			}
		} else {
			$display_name = $user->display_name;
		}

		# If the display name is empty for any reason, default to the username
		if (empty($display_name)) $display_name = $user->user_login;

		$display_name = apply_filters('sph_set_display_name', $display_name, $userid);
		$display_name = SP()->saveFilters->name($display_name);

		# now ensure it is unique
		$display_name = $this->unique_display_name($display_name, $display_name);

		if (!$install) {
			# do we need to force user to change password?
			if ($sfprofile['forcepw']) add_user_meta($userid, 'sp_change_pw', true, true);
		}

		$admin         = 0;
		$moderator     = 0;
		$avatar        = 'a:1:{s:8:"uploaded";s:0:"";}';
		$signature     = '';
		$posts         = -1;
		$lastvisit     = current_time('mysql');
		$checktime     = current_time('mysql');
		$admin_options = '';
		$newposts      = 'a:3:{s:6:"topics";a:0:{}s:6:"forums";a:0:{}s:4:"post";a:0:{}}';

		$useropts               = array();
		$useropts['hidestatus'] = 0;
		$useropts['timezone']   = get_option('gmt_offset');
		if (empty($useropts['timezone'])) $useropts['timezone'] = 0;
		$tz = get_option('timezone_string');
		if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
		$useropts['timezone_string'] = $tz;
		$useropts['editor']          = 1;
		$useropts['namesync']        = 1;

		# unread posts
		if (!$install) {
			$sfcontrols              = SP()->options->get('sfcontrols');
			$useropts['unreadposts'] = $sfcontrols['sfdefunreadposts'];
		} else {
			$useropts['unreadposts'] = 50;
		}

		$user_options = serialize($useropts);

		# generate feedkey
		$feedkey = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

		# save initial record
		$sql = 'INSERT INTO '.SPMEMBERS."
		(user_id, display_name, admin, moderator, avatar, signature, posts, lastvisit, checktime, newposts, admin_options, user_options, feedkey)
		VALUES
		($userid, '$display_name', $admin, $moderator, '$avatar', '$signature', $posts, '$lastvisit', '$checktime', '$newposts', '$admin_options', '$user_options', '$feedkey')";
		SP()->DB->execute($sql);

		if (!$install) {
			# update stats status and recent member list
			if ($this->stats_status($userid) == 0) {
				$this->push_new($userid, $display_name);
			}
		} else {
			if ($current_user->ID != $userid) {
				$ug = SP()->DB->table(SPUSERGROUPS, "usergroup_name='Members'", 'usergroup_id');
				if (!$ug) $ug = 2;
				$sql = 'INSERT INTO '.SPMEMBERSHIPS.' (user_id, usergroup_id) ';
				$sql .= "VALUES ($userid, $ug);";
				SP()->DB->execute($sql);
			}
		}

		do_action('sph_member_created', $userid);
	}

	/**
	 * This method updates up the user data row in the sfmembers table
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid ID of the user to initialize
	 *
	 * @return void
	 */
	public function update_data($userid) {
		if (!$userid) return;

		# are we syncing display names between WP and SPF?
		$member  = SP()->memberData->get($userid);
		$options = unserialize($member['user_options']);
		if ($options['namesync']) {
			$display_name = SP()->saveFilters->name(SP()->DB->table(SPUSERS, "ID=$userid", 'display_name'));
			SP()->memberData->update($userid, 'display_name', $display_name);

			# update recent members list
			$this->update_new_name($member['display_name'], $display_name);
		}
	}

	public function set_role_to_ug($userid, $role, $old_roles = '') {
		# remove any mapped memberships based on old roles
		if (!empty($old_roles)) {
			foreach ($old_roles as $old_role) {
				# remove any mapped roles
				$this->remove_role_to_ug($userid, $old_role);
			}
		}

		# check for mapped membership for this role
		$this->add_role_to_ug($userid, $role);
	}

	public function add_role_to_ug($userid, $role) {
		# see if their is a mapped membership for this role
		$ug = SP()->meta->get_value('default usergroup', $role);
		if (empty($ug)) $ug = SP()->meta->get_value('default usergroup', 'sfmembers');
		$this->add_membership($ug, $userid);
	}

	public function remove_role_to_ug($userid, $role) {
		# see if their is a mapped membership for this role
		$ug = SP()->meta->get_value('default usergroup', $role);
		if (!empty($ug)) $this->remove_membership($ug, $userid);
	}

	/**
	 * This method removes the user's data from the sfmember table and handles any post reassigning.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int  $userid        ID of the user to initialize
	 * @param bool $blog_id       true if the initial creation
	 * @param bool $delete_option true if the user's posts should be deleted, false for reassign
	 * @param int  $reassign      user ID to reassign posts to
	 *
	 * @return void
	 */
	public function delete_data($userid, $blog_id = '', $delete_option = 'spguest', $reassign = 0, $mess = '') {
		if (!$userid) return;

		global $wpdb;
		
		# Make sure we have the RIGHT sp_prefix.  $wpdb->prefix will be incorrect
		# in a multisite situation.  The global constant SP_PREFIX should be defined
		# already with the RIGHT prefix.  But if for some reason it's not we'll 
		# use the $wpdb->prefix anyway.
		if (!defined('SP_PREFIX')) {
			$dbprefix = $wpdb->prefix; 
		} else {
			$dbprefix = SP_PREFIX;
		}

		# if removing user from network site, make sure sp installed on that network site
		if (!empty($blog_id)) {
			$optionstable = SP()->DB->tableExists(SPOPTIONS);
			if (empty($optionstable)) return;
		}

		# let plugins clean up from member removal first
		do_action('sph_member_deleted', $userid);

		# remove member from core
		$option = (isset($_POST['sp_delete_option'])) ? SP()->filters->str($_POST['sp_delete_option']) : $delete_option;
		switch ($option) {
			case 'spreassign':
				$newuser = (isset($_POST['sp_reassign_user'])) ? SP()->filters->integer($_POST['sp_reassign_user']) : $reassign;

				# Set poster ID to the new user id
				$wpdb->query('UPDATE '.$dbprefix."sfposts SET user_id=$newuser WHERE user_id=$userid");
				$wpdb->query('UPDATE '.$dbprefix."sftopics SET user_id=$newuser WHERE user_id=$userid");
				break;

			case 'spdelete':
				# need to get topics for user posts to see if topic will be empty after deleting posts
				$topics = SP()->DB->select('SELECT DISTINCT topic_id, forum_id FROM '.SPPOSTS." WHERE user_id=$userid");

				# delete all the user posts
				SP()->DB->execute('DELETE FROM '.SPPOSTS." WHERE user_id=$userid");

				# if any topics are now empty of posts, lets remove the topic and update the forum
				if (!empty($topics)) {

					require_once SP_PLUGIN_DIR.'/forum/database/sp-db-management.php';
					foreach ($topics as $topic) {
						$posts = SP()->DB->table(SPPOSTS, "topic_id=$topic->topic_id");
						if (empty($posts)) {
							SP()->DB->execute('DELETE FROM '.SPTOPICS." WHERE topic_id=$topic->topic_id");
						} else {
							sp_build_post_index($topic->topic_id);
						}
						sp_build_forum_index($topic->forum_id);
					}
				}
				break;

			case 'spguest':
			default:
				# Set display name to guest and remove User ID and IP Address from all of their posts
				$guest_name = SP()->primitives->front_text('Guest');

				$sql = 'UPDATE '.$dbprefix."sfposts SET user_id=0, guest_name='$guest_name', poster_ip=''";
				if (!empty($mess)) {
					$sql .= ", post_content ='$mess'";
				}
				$sql .= " WHERE user_id=$userid";

				$wpdb->query($sql);
				# and any refereneces from the topic records
				$wpdb->query('UPDATE '.$dbprefix."sftopics SET user_id=0 WHERE user_id=$userid");
		}

		# flush and rebuild topic cache
		SP()->meta->rebuild_topic_cache();

		# remove from various core tables
		$wpdb->query('DELETE FROM '.$dbprefix."sfmembers WHERE user_id=$userid");
		$wpdb->query('DELETE FROM '.$dbprefix."sfmemberships WHERE user_id=$userid");
		$wpdb->query('DELETE FROM '.$dbprefix."sfspecialranks WHERE user_id=$userid");
		$wpdb->query('DELETE FROM '.$dbprefix."sftrack WHERE trackuserid=$userid");
		$wpdb->query('DELETE FROM '.$dbprefix."sfnotices WHERE user_id=$userid");
		$wpdb->query('DELETE FROM '.$dbprefix."sfuseractivity WHERE user_id=$userid");
		$wpdb->query('DELETE FROM '.$dbprefix."sfwaiting WHERE user_id=$userid");

		# eemove from recent members list if present
		$this->remove_new($userid);

		# check if forum moderator list needs updating
		$this->update_forum_moderators();
	}

	/**
	 * This method adds the specified membership for the user.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $usergroup_id usergroup to add membership for user
	 * @param int $user_id      user ID
	 *
	 * @return bool        true if the add was successful, false otherwise
	 */
	public function add_membership($usergroup_id, $user_id) {
		# make sure we have valid membership to set
		if (empty($usergroup_id) || empty($user_id)) return false;

		# dont allow admins to be added to user groups
		if (SP()->auths->forum_admin($user_id)) return false;
		$success = false;

		# if only one membership allowed, remove all current memberships
		$sfmemberopts = SP()->options->get('sfmemberopts');
		if (isset($sfmemberopts['sfsinglemembership']) && $sfmemberopts['sfsinglemembership']) SP()->DB->execute('DELETE FROM '.SPMEMBERSHIPS." WHERE user_id=$user_id");

		# dont add membership if it already exists
		$check = $this->check_membership($usergroup_id, $user_id);
		if (empty($check)) {
			$sql = 'INSERT INTO '.SPMEMBERSHIPS.' (user_id, usergroup_id) ';
			$sql .= "VALUES ('$user_id', '$usergroup_id');";
			$success = SP()->DB->execute($sql);

			# reset auths and memberships for added user
			$this->reset_memberships($user_id);
			SP()->auths->reset_cache($user_id);

			$this->update_moderator_flag($user_id);
		}

		return $success;
	}

	/**
	 * This method removes the specified membership for the user.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $usergroup_id usergroup to remove membership for user
	 * @param int $user_id      user ID
	 *
	 * @return bool        always true
	 */
	public function remove_membership($usergroup_id, $user_id) {
		SP()->DB->execute('DELETE FROM '.SPMEMBERSHIPS." WHERE user_id=$user_id AND usergroup_id=$usergroup_id");

		# reset auths and memberships for added user
		$this->reset_memberships($user_id);
		SP()->auths->reset_cache($user_id);

		$this->update_moderator_flag($user_id);

		return true;
	}

	/**
	 * This method checks if user has membership in the usergroup.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $usergroup_id usergroup to check membership for user
	 * @param int $user_id      user ID
	 *
	 * @return bool        true if user has membership, false otherwise
	 */
	public function check_membership($usergroup_id, $user_id) {
		if (!$usergroup_id || !$user_id) return '';

		return SP()->DB->table(SPMEMBERSHIPS, "user_id=$user_id AND usergroup_id=$usergroup_id", '', '', '', ARRAY_A);
	}

	/**
	 * This method resets (clears) all memberships for the user.
	 * If the user id is not passed, all users have their memberships reset.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $user_id user ID
	 *
	 * @return void
	 */
	public function reset_memberships($userid = '') {
		# reset all the members memberships
		$where = '';
		if (!empty($userid)) $where = ' WHERE user_id='.$userid;

		SP()->DB->execute('UPDATE '.SPMEMBERS." SET memberships=''".$where);

		# reset guest auths if global update
		if (empty($userid)) SP()->options->update('sf_guest_memberships', '');
	}

	/**
	 * This method retrieves the memberships for the user.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $user_id user ID
	 *
	 * @return array    list of user memberships
	 */
	public function get_memberships($user_id) {
		if (!$user_id) return array();

		$sql = 'SELECT '.SPMEMBERSHIPS.'.usergroup_id, usergroup_name, usergroup_desc, usergroup_badge, usergroup_join, hide_stats
			FROM '.SPMEMBERSHIPS.'
			JOIN '.SPUSERGROUPS.' ON '.SPUSERGROUPS.'.usergroup_id = '.SPMEMBERSHIPS.".usergroup_id
			WHERE user_id=$user_id";

		return SP()->DB->select($sql, 'set', ARRAY_A);
	}

	/**
	 * This method retrieves a list of forums the current user has membership on via a usergroup.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return array    list of forum ids current user has membership on
	 */
	public function get_forum_memberships() {
		if ($this->thisUser->admin) {
			$sql = 'SELECT forum_id FROM '.SPFORUMS;
		} else if ($this->thisUser->guest) {
			$value = SP()->meta->get('default usergroup', 'sfguests');
			$sql   = 'SELECT forum_id FROM '.SPPERMISSIONS." WHERE usergroup_id={$value[0]['meta_value']}";
		} else {
			$sql = 'SELECT forum_id
				FROM '.SPPERMISSIONS.'
				JOIN '.SPMEMBERSHIPS.' ON '.SPPERMISSIONS.'.usergroup_id = '.SPMEMBERSHIPS.'.usergroup_id
				WHERE user_id='.$this->thisUser->ID;
		}
		$forums = SP()->DB->select($sql);
		$fids   = array();
		if ($forums) {
			foreach ($forums as $thisForum) {
				if (SP()->auths->get('view_forum', $thisForum->forum_id) || SP()->auths->get('view_forum_lists', $thisForum->forum_id) || SP()->auths->get('view_forum_topic_lists', $thisForum->forum_id)) {
					$fids[] = $thisForum->forum_id;
				}
			}
		}

		return $fids;
	}

	/**
	 * This method updates the user moderator flag status.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $user_id user ID
	 *
	 * @return void
	 */
	public function update_moderator_flag($userid) {
		$ugs = $this->get_memberships($userid);
		if ($ugs) {
			foreach ($ugs as $ug) {
				$mod = SP()->DB->table(SPUSERGROUPS, "usergroup_id={$ug['usergroup_id']}", 'usergroup_is_moderator');
				if ($mod) {
					SP()->memberData->update($userid, 'moderator', 1);

					# see if our forum moderator list changed
					$this->update_forum_moderators();

					return;
				}
			}
		}

		# not a moderator if we get here
		SP()->memberData->update($userid, 'moderator', 0);
	}

	/**
	 * This method updates the list of forum moderators for the specified forum.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $forumid forum ID to update moderators
	 *
	 * @return void
	 */
	public function update_forum_moderators($forumid = '') {
		if (empty($forumid)) {
			$forums = SP()->DB->select('SELECT forum_id FROM '.SPFORUMS, 'col', ARRAY_A);
		} else {
			$forums = (array)$forumid;
		}
		if (empty($forums)) return;

		# udpate moderators list for each forum
		$mods = array();
		foreach ($forums as $forum) {
			$sql          = 'SELECT DISTINCT '.SPMEMBERSHIPS.'.user_id, display_name
    			FROM '.SPMEMBERSHIPS.'
    			JOIN '.SPUSERGROUPS.' ON '.SPUSERGROUPS.'.usergroup_id = '.SPMEMBERSHIPS.'.usergroup_id
    			JOIN '.SPPERMISSIONS.' ON '.SPPERMISSIONS.".forum_id = $forum AND ".SPMEMBERSHIPS.'.usergroup_id = '.SPUSERGROUPS.'.usergroup_id
    			JOIN '.SPMEMBERS.' ON '.SPMEMBERS.'.user_id = '.SPMEMBERSHIPS.'.user_id
    			WHERE usergroup_is_moderator=1';
			$mods[$forum] = SP()->DB->select($sql, 'set', ARRAY_A);
		}

		SP()->meta->add('forum_moderators', 'users', $mods);
	}

	/**
	 * This method adds the new user to the list of recent new users.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $user_id user ID of new user
	 * @param int $name    display name of new user
	 *
	 * @return void
	 */
	public function push_new($id, $name) {
		$spControls = SP()->options->get('sfcontrols');
		$num        = $spControls['shownewcount'];
		if (empty($num)) $num = 0;

		$newuserlist = SP()->options->get('spRecentMembers');
		if (is_array($newuserlist)) {
			# is this name already listed?
			foreach ($newuserlist as $user) {
				if ($user['name'] == $name) return;
			}

			# is the array full? if so pop one off
			$ccount = count($newuserlist);
			while ($ccount > ($num - 1)) {
				array_pop($newuserlist);
				$ccount--;
			}

			# add new user
			array_unshift($newuserlist, array('id' => SP()->filters->esc_sql($id), 'name' => SP()->filters->esc_sql($name)));
		} else {
			# first name nto the emoty array
			$newuserlist[0]['id']   = SP()->filters->esc_sql($id);
			$newuserlist[0]['name'] = SP()->filters->esc_sql($name);
		}
		SP()->options->update('spRecentMembers', $newuserlist);
	}

	/**
	 * This method removes a user from the list of recent new users.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $user_id user ID of new user
	 *
	 * @return void
	 */
	public function remove_new($id) {
		$newuserlist = SP()->options->get('spRecentMembers');
		if (is_array($newuserlist)) {
			# remove the user if present
			foreach ($newuserlist as $index => $user) {
				if ($user['id'] == $id) unset($newuserlist[$index]);
			}
			$newuserlist = array_values($newuserlist);
		}
		SP()->options->update('spRecentMembers', $newuserlist);
	}

	/**
	 * This method rebuilds the current list of recent new users.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function rebuild_new() {
		# how many to show...
		$spControls = SP()->options->get('sfcontrols');
		$num        = $spControls['shownewcount'];
		if (empty($num)) $num = 10;

		# select the right number...
		$query              = new stdClass();
		$query->table       = SPMEMBERS;
		$query->distinctrow = true;
		$query->fields      = SPMEMBERS.'.user_id AS id, display_name AS name';
		$query->join        = array(SPMEMBERSHIPS.' ON '.SPMEMBERS.'.user_id = '.SPMEMBERSHIPS.'.user_id', SPUSERGROUPS.' ON '.SPMEMBERSHIPS.'.usergroup_id = '.SPUSERGROUPS.'.usergroup_id');
		$query->where       = SPUSERGROUPS.'.hide_stats = 0';
		$query->orderby     = SPMEMBERS.'.user_id DESC LIMIT '.$num;
		$query->resultType  = ARRAY_A;
		$list               = SP()->DB->select($query);

		# save the resultant array
		SP()->options->update('spRecentMembers', $list);
	}

	/**
	 * This method updates the display name of a user on the list of recent new users.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $oldname current display name
	 * @param string $newname new display name
	 *
	 * @return void
	 */
	public function update_new_name($oldname, $newname) {
		$newuserlist = SP()->options->get('spRecentMembers');
		if (is_array($newuserlist)) {
			for ($x = 0; $x < count($newuserlist); $x++) {
				if ($newuserlist[$x]['name'] == $oldname) $newuserlist[$x]['name'] = $newname;
			}
		}
		SP()->options->update('spRecentMembers', $newuserlist);
	}

	/**
	 * This method updates the display names for all users on the list of recent new users.
	 * Typically fired when the profile display name options have changed.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public function update_recent() {
		$newuserlist = SP()->options->get('spRecentMembers');
		if (is_array($newuserlist)) {
			for ($x = 0; $x < count($newuserlist); $x++) {
				$newuserlist[$x]['name'] = SP()->memberData->get($newuserlist[$x]['id'], 'display_name');
			}
		}
		SP()->options->update('spRecentMembers', $newuserlist);
	}

	/**
	 * This method retrieves a list of forum IDs that the current user can view.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $view type of view to check for (title, content, etc)
	 *
	 * @return array    list of forum IDs
	 */
	public function visible_forums($view = 'forum-title') {
		if (empty($this->thisUser->auths)) return array();

		$forum_ids = array();
		foreach ($this->thisUser->auths as $forum => $forum_auth) {
			if ($forum != 'global' && SP()->auths->can_view($forum, $view)) $forum_ids[] = $forum;
		}

		return $forum_ids;
	}

	/**
	 * This method validates the user login name of a registering user against a blacklist of names.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param object $errors               current errors
	 * @param string $sanitized_user_login user login
	 * @param string $user_email           user email
	 *
	 * @return object    updated list of errors on registration
	 */
	public function validate_registration($errors, $sanitized_user_login, $user_email) {
		$blockedAccounts = SP()->options->get('account-name');
		if (!empty($blockedAccounts)) {
			$names = explode(',', $blockedAccounts);
			foreach ($names as $name) {
				if (strtolower(trim($name)) == strtolower($sanitized_user_login)) {
					$errors->add('login_blacklisted', '<strong>'.SP()->primitives->front_text('ERROR').'</strong>: '.SP()->primitives->front_text('The account name you have chosen is not allowed on this site'));
					break;
				}
			}
		}

		return $errors;
	}

	/**
	 * This method validates the display name of a registering user against a blacklist of names.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param object $errors current errors
	 * @param bool   $update not used
	 * @param object $user   user object
	 *
	 * @return object    updated list of errors on registration
	 */
	public function validate_display_name($errors, $update, $user) {
		$blockedDisplay = SP()->options->get('display-name');
		if (!empty($blockedDisplay)) {
			$names = explode(',', $blockedDisplay);
			foreach ($names as $name) {
				if (strtolower(trim($name)) == strtolower($user->display_name)) {
					$errors->add('display_name_blacklisted', '<strong>'.SP()->primitives->front_text('ERROR').'</strong>: '.SP()->primitives->front_text('The display name you have chosen is not allowed on this site'));
					break;
				}
			}
		}

		return $errors;
	}

	/**
	 * This method checks that a selected user display name is unique and not already in use.
	 * If it exists, a number is appended to the end of the display name.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $startname current display name
	 * @param string $modname   desired new display name
	 * @param int    $suffix    startng number for appending
	 *
	 * @return string    updated display name (unmodified if unique)
	 */
	public function unique_display_name($startname, $modname, $suffix = 1) {
		$check = true;
		while ($check) {
			$check = SP()->DB->table(SPMEMBERS, "display_name='$modname'");
			if ($check) {
				$modname = $startname.'_'.$suffix;
				$suffix++;
			}
		}

		return $modname;
	}

	/**
	 * This method adds our reassignment option when WP is deleting users.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $user   id of user doing the deleting of users
	 * @param int $userid user IDs being deleted
	 *
	 * @return void
	 */
	public function delete_form($user, $userids) {
		?>
        <fieldset>
			<?php
			foreach ($userids as $id) {
				if (SP()->auths->forum_admin($id)) {
					echo '<div class="error"><p>'.SP()->primitives->admin_text('Warning:  You are about to delete a Simple:Press Admin user. This could have consequences for administration of your forum. Please ensure you really want to do this.').'</p></div>';
					break;
				}
			}
			?>
            <legend><?php echo SP()->primitives->admin_text('What should be done with the user(s) forum posts?'); ?></legend>
            <ul style="list-style:none;">
                <li><label><input type="radio" id="sp_guest_option" name="sp_delete_option" value="spguest"
                                  checked="checked"/>
						<?php echo SP()->primitives->admin_text('Change all posts to be from a guest.'); ?></label>
                </li>
                <li><label><input type="radio" id="sp_delete_option" name="sp_delete_option" value="spdelete"/>
						<?php echo SP()->primitives->admin_text('Delete all the posts (warning - may take time and resources if lots of posts).'); ?>
                    </label></li>
                <li><label><input type="radio" id="sp_reassign_option" name="sp_delete_option" value="spreassign"/></label>
					<?php
					echo '<label for="sp_reassign_option">'.SP()->primitives->admin_text('Reassign all the posts to:').'</label> ';
					wp_dropdown_users(array('name' => 'sp_reassign_user', 'exclude' => array($user->ID)));
					?></li>
            </ul>
        </fieldset>
		<?php
	}

	/**
	 * This method checks whether or not a user should be shown in the forum membership stats.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int $userid      user ID to check
	 * @param int $memberships list of memberships to check, if empty all will be fetched from DB
	 *
	 * @return bool        true, if user should be hidden, false otherwise
	 */
	public function stats_status($userid, $memberships = '') {
		# do we need to fetch memberships?
		if (empty($memberships)) {
			$memberships = $this->get_memberships($userid);
			SP()->memberData->update($userid, 'memberships', wp_slash($memberships));
		}

		if ($memberships) {
			$hide = 1;
			foreach ($memberships as $membership) {
				if ($membership['hide_stats'] == 0) $hide = 0;
			}
		} else {
			$hide = 0;
		}

		return $hide;
	}

	public function special_ranks_col($userid, $rank = '') {
		$userid = (int)$userid;

		$where = ' WHERE user_id='.$userid;
		if ($rank != '') $where .= ' AND special_rank="'.$rank.'"';

		return SP()->DB->select('SELECT special_rank FROM '.SPSPECIALRANKS.$where, 'col');
	}

	public function special_ranks($userid) {
		$userRanks   = array();
		$memberRanks = $this->special_ranks_col($userid);
		$ranks_data  = SP()->meta->get_values('special_rank');
		if (empty($ranks_data) || empty($memberRanks)) return $userRanks;

		$count = 0;
		foreach ($ranks_data as $key => $rank) {
			if (is_array($memberRanks) && in_array($key, $memberRanks)) {
				$userRanks[$count]['badge'] = '';
				
				if( $rank['badge'] ) {
						$badge_icon = spa_get_saved_icon( $rank['badge'] );
						if( 'file' === $badge_icon['type'] && file_exists(SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/'.$badge_icon['icon']) ) {
							// $userRanks[$count]['badge'] = esc_url(SPRANKS.$rank['badge']);
							$userRanks[$count]['badge'] = $badge_icon;
						} elseif( 'font' === $badge_icon['type'] ) {
							$userRanks[$count]['badge'] = $badge_icon;
						}
				}
				$userRanks[$count]['name'] = $key;
				$count++;
			}
		}

		return $userRanks;
	}

	public function forum_rank($usertype, $userid, $userposts) {
		$forumRank             = array();
		$forumRank[0]['badge'] = '';

		switch ($usertype) {
			case 'Admin':
				$forumRank[0]['name'] = SP()->primitives->front_text('Admin').' ';
				break;

			case 'Moderator':
				$forumRank[0]['name'] = SP()->primitives->front_text('Moderator').' ';
				break;

			case 'User':
				$forumRank[0]['name'] = SP()->primitives->front_text('Member').' ';
				break;

			case 'Guest':
				$forumRank[0]['name'] = SP()->primitives->front_text('Guest').' ';
				break;
		}

		# check for forum rank
		$rankdata   = array();
		$ranks_data = SP()->meta->get_values('forum_rank');
		if ($usertype == 'User' && !empty($ranks_data)) {
			# put into arrays to make easy to sort
			$index = 0;
			foreach ($ranks_data as $x => $info) {
				$rankdata['title'][$index] = $x;
				$rankdata['posts'][$index] = $info['posts'];
				$rankdata['badge'][$index] = '';
				if (isset($info['badge'])) $rankdata['badge'][$index] = $info['badge'];
				$index++;
			}
			# sort rankings
			array_multisort($rankdata['posts'], SORT_ASC, $rankdata['title'], $rankdata['badge']);

			# find ranking of current user
			for ($x = 0; $x < count($rankdata['posts']); $x++) {
				if ($userposts <= $rankdata['posts'][$x]) {
					
					if( $rankdata['badge'][$x] ) {
						$badge_icon = spa_get_saved_icon( $rankdata['badge'][ $x ] );
						
						if( 'file' === $badge_icon['type'] && file_exists(SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/'.$badge_icon['icon']) ) {
							$forumRank[0]['badge'] = $badge_icon;
						} elseif( 'font' === $badge_icon['type'] ) {
							$forumRank[0]['badge'] = $badge_icon;
						}
					}
					$forumRank[0]['name'] = $rankdata['title'][$x];
					break;
				}
			}
		}

		return $forumRank;
	}

	public function name_display($userid, $username, $linkNames = 1) {
		$username = apply_filters('sph_build_name_display', $username, $userid);

		if ($userid) {
			$profile = SP()->options->get('sfprofile');

			if (SP()->auths->get('view_profiles') && ($profile['namelink'] == 2 && $linkNames == 1)) {
				# link to profile
				return sp_attach_user_profile_link($userid, $username);
			} else if ($profile['namelink'] == 3) {
				# link to website
				return sp_attach_user_web_link($userid, $username);
			} else {
				$username = apply_filters('sph_build_name_display_option', $username, $userid);
			}
		}

		# neither permission or profile/web link
		return $username;
	}
}