<?php
/*
Simple:Press
User Class
$LastChangedDate: 2016-01-01 18:15:53 -0600 (Fri, 01 Jan 2016) $
$Rev: 13756 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	User Class
#
#	Version: 5.0
#	Pass in a user ID. 0 or null denotes a guest
#	Pass in the user login as an alternative
#
#	This class should NOT be instantiated directly. All calls to create a new
#	user object should be routed through the sp_get_user() function to allow
#	for user object caching.
#
#--------------------------------------------------------------------------------------

class spUser {

	# ------------------------------------------
	#	spUser
	#	$ident		user id or user login
	#	$current	set to true for $spThisUser
	# ------------------------------------------

	var $doPlugins = true;

	function __construct($ident=0, $current=false, $small=false) {
		global $spStatus, $spGlobals;

		$pluginData = new stdClass();

		$id = 0;
		if (is_numeric($ident)) {
			$w = "ID=$ident";
		} else if ($ident != false) {
			$w = "user_login='$ident'";
		}
		if ($ident) {
			# Users data
			$d = spdb_table(SFUSERS, $w, 'row');
			if ($d) {
				$this->ID = $d->ID;
				$id = $d->ID;
			}
		}

		$pluginData->ID = $id;

		$includeList = spUser_build_filter_list();
		if ($id) {
			# Others
			$this->member = true;
			$this->guest = 0;
			$this->guest_name = '';
			$this->guest_email = '';
			$this->offmember = false;
			$this->usertype = 'User';

			# Users data
			foreach ($d as $key => $item) {
				if (array_key_exists($key, $includeList)) {
					$this->$key = $item;
				}
			}
			$this->user_registered = sp_member_registration_to_server_tz($this->user_registered);

			# usermeta data - initialise our own ones first
			$this->location = '';
			$this->msn = '';
			$this->icq = '';
			$this->skype = '';
			$this->facebook = '';
			$this->myspace = '';
			$this->twitter = '';
			$this->linkedin = '';
			$this->youtube = '';
			$this->googleplus = '';

			$d = spdb_table(SFUSERMETA, "user_id=$id");
			if ($d) {
				foreach ( $d as $m) {
					$t = $m->meta_key;
					if (array_key_exists($t, $includeList)) {
						$this->$t = maybe_unserialize($m->meta_value);
					}
				}
			}

			# If awaiting installation then dive out now to avoid errors
			if ($spStatus == 'Install') return;

			# sfmembers data
			$d = spdb_table(SFMEMBERS, "user_id=$id", 'row');
			#check for ghost user
			if (empty($d)) {
				#create the member
				sp_create_member_data($id);
				$d = spdb_table(SFMEMBERS, "user_id=$id", 'row');
			}
			if ($d) {
				foreach ($d as $key => $item) {
					if ($key == 'admin_options' && !empty($item)) {
						$opts = unserialize($item);
						foreach ($opts as $opt => $set) {
							$this->$opt = $set;
						}
					} else if ($key == 'user_options' && !empty($item)) {
						$opts = unserialize($item);
						foreach ($opts as $opt => $set) {
							$this->$opt = $set;
						}
					} else if ($key == 'plugin_data' && !empty($item)) {
						$pluginData = unserialize($item);
						$this->doPlugins = false;
					} else if ($key == 'lastvisit') {
						$this->lastvisit = $item;
					} else if ($key == 'memberships') {
						$this->memberships = wp_unslash(maybe_unserialize($item));
					} else {
						$this->$key = maybe_unserialize($item);
					}
				}
			}

			# Check for new post list size
			if (!isset($this->unreadposts) || empty($this->unreadposts)) {
				$controls = sp_get_option('sfcontrols');
				$this->unreadposts = (empty($controls['sfunreadposts'])) ? 50 : $controls['sfunreadposts'];
			}

			# usertype for moderators
			if ($this->moderator) $this->usertype = 'Moderator';

			# check for super admins and make admin a moderator as well
			if ($this->admin || (is_multisite() && is_super_admin($id))) {
				$this->admin = true;
				$this->moderator = true;
				$this->usertype = 'Admin';
				$ins = sp_get_option('spInspect');
				if (!empty($ins) && array_key_exists($id, $ins)) {
					$this->inspect = $ins[$id];
				} else {
					$this->inspect = '';
				}
			}

			# plugins can add iterms for members...
			if($this->doPlugins) {
				if (!$small) {
					do_action_ref_array('sph_user_class_member', array(&$pluginData));
				} else {
					do_action_ref_array('sph_user_class_member_small', array(&$pluginData));
				}
			}
		} else {
			# some basics for guests
			$this->ID = 0;
			$this->guest = true;
			$this->member = 0;
			$this->admin = false;
			$this->moderator = false;
			$this->display_name = 'guest';
			$this->guest_name = '';
			$this->guest_email = '';
			$this->usertype = 'Guest';
			$this->offmember = sp_check_unlogged_user();
			$this->timezone = 0;
			$this->timezone_string = '';
			$this->posts = 0;
			$this->avatar = '';
			$this->user_email = '';
			$this->auths = sp_get_option('sf_guest_auths');
			$this->memberships = sp_get_option('sf_guest_memberships');

			# plugins can add iterms for guests...
			if($this->doPlugins) {
				if (!$small) {
					do_action_ref_array('sph_user_class_guest', array(&$pluginData));
				} else {
					do_action_ref_array('sph_user_class_guest_small', array(&$pluginData));
				}
			}
		}

		# Only perform this last section if forum is operational
		if ($spStatus == 'ok') {
			# Ranking
			$this->rank = sp_get_user_forum_rank($this->usertype, $id, $this->posts);
			$this->special_rank = ($this->member) ? sp_get_user_special_ranks($id) : array();

			# if no memberships rebuild them and save
			if (empty($this->memberships)) {
				$memberships = array();
				if (!empty($id)) {
					if (!$this->admin) {
						# get the usergroup memberships for the user and save in sfmembers table
						$memberships = sp_get_user_memberships($id);
						sp_update_member_item($id, 'memberships', wp_slash($memberships));
					}
				} else {
					# user is a guest or unassigned member so get the global permissions from the guest usergroup and save as option
					$value = sp_get_sfmeta('default usergroup', 'sfguests');
					$memberships[] = spdb_table(SFUSERGROUPS, 'usergroup_id='.$value[0]['meta_value'], 'row', '', '', ARRAY_A);
					sp_update_option('sf_guest_memberships', wp_slash($memberships));
				}
				# put in the data
				$this->memberships = $memberships;
			}
			# if no auths rebuild them and save
			if (empty($this->auths)) $this->auths = sp_rebuild_user_auths($id);
		}

		$this->ip = sp_get_ip();
		$this->trackid = -1;

		# Things to do if user is current user
		if ($current) {
			# Set up editor type
			$spGlobals['editor'] = 0;

			# for a user...
			if ($this->member && !empty($this->editor)) $spGlobals['editor'] = $this->editor;

			# and if not defined or is for a guest...
			if ($spGlobals['editor'] == 0) {
				$defeditor = sp_get_option('speditor');
				if (!empty($defeditor)) $spGlobals['editor'] = $defeditor;
			}

			# final check to ensure selected editor type is indeed available
			if (($spGlobals['editor'] == 0) ||
				($spGlobals['editor'] == 1 && !defined('RICHTEXT')) ||
				($spGlobals['editor'] == 2 && !defined('HTML')) ||
				($spGlobals['editor'] == 3 && !defined('BBCODE'))) {

				$spGlobals['editor'] = PLAINTEXT;
				if (defined('BBCODE'))		$spGlobals['editor'] = BBCODE;
				if (defined('HTML'))		$spGlobals['editor'] = HTML;
				if (defined('RICHTEXT'))	$spGlobals['editor'] = RICHTEXT;
			}

			# Grab any notices present
			if ($this->guest && !empty($this->guest_email)) {
				$this->user_notices = spdb_table(SFNOTICES, "guest_email='".$this->guest_email."'", '', $order='notice_id');
			} elseif ($this->member && !empty($this->user_email)) {
				$this->user_notices = spdb_table(SFNOTICES, "user_id=".$this->ID, '', $order='notice_id');
			}

			if($this->doPlugins) {
				# plugins can add iterms for the current user (so no small allowed here)
				do_action_ref_array('sph_current_user_class', array(&$pluginData));
			}
		}

		# Finally filter the data for display
		foreach ($includeList as $item => $filter) {
			if (property_exists($this, $item)) $this->$item = spUser_filter_item($this->$item, $filter);
		}

		if($this->doPlugins) {
			# allow plugins to add items to user class - regardless small or otherwise, current or otherwise
			do_action_ref_array('sph_user_class', array(&$pluginData));
		}

		# Add the plugn data to the user object
		foreach($pluginData as $key => $value) {
			$this->$key = $value;
		}

		if($this->doPlugins) {
			# add the datas back to members table
			sp_update_member_item($this->ID, 'plugin_data', $pluginData);
		}

        # last chance for anyone to add or modify the user object
        # preference is to the the plugin_data cache for user object but if needed this hook is provided
    	do_action_ref_array('sph_user_class_object', array(&$this));
	}
}

# ==========================================
# Support Functions

# ------------------------------------------
#	spUser_build_filter_list()
#	Master list of data that is retrieved
#	from users and usermeta tables along
#	with the filter to apply
# ------------------------------------------
function spUser_build_filter_list() {
	$includeList = array(
		'user_login'		=> 'name',
		'user_email'		=> 'email',
		'user_url'			=> 'url',
		'user_registered'	=> '',
		'description'		=> 'text',
		'location'			=> 'title',
		'first_name'		=> 'name',
		'last_name'			=> 'name',
		'aim'				=> 'title',
		'yim'				=> 'title',
		'jabber'			=> 'title',
		'msn'				=> 'title',
		'icq'				=> 'title',
		'skype'				=> 'title',
		'facebook'			=> 'title',
		'myspace'			=> 'title',
		'twitter'			=> 'title',
		'linkedin'			=> 'title',
		'youtube'			=> 'title',
		'googleplus'		=> 'title',
		'display_name'		=> 'name',
		'signature'			=> 'signature',
		'sp_change_pw'		=> '',
		'photos'			=> '',
	);

	# allow plugins to add more usermeta class data
	$includeList = apply_filters('sph_user_class_meta', $includeList);
	return $includeList;
}

# ------------------------------------------
#	spUser_filter_item()
#	The display filter calls based upon
#	the array of user entered data and
#	filters to apply
# ------------------------------------------
function spUser_filter_item($item, $filter) {
	if (is_array($item)) return $item;
	switch ($filter) {
		case 'title':
			$item = sp_filter_title_display($item);
			break;
		case 'email':
			$item = sp_filter_email_display($item);
			break;
		case 'url':
			$item = sp_filter_url_display($item);
			break;
		case 'text':
			$item = sp_filter_text_display($item);
			break;
		case 'name':
			$item = sp_filter_name_display($item);
			break;
		case 'signature':
			$item = sp_filter_signature_display($item);
			break;
	}
	return $item;
}

# ==========================================
# Main Entry Point

# ------------------------------------------------------------------
# sp_get_user()
#
# Version: 5.1.3
# The main call to create a new user data object. This routine
# caches users into an array and checks the cache before creating
# a new object
#
#	$userid:	user id to return
#	$current:	set to true if user is the current system user
#	$small:		(5.5.1) Allows for a smaller subset to be created
# ------------------------------------------------------------------
function sp_get_user($userid=0, $current=false, $small=false) {
	static $USERS = array();

    # dont cache guest users since they all have same ID
	if (!array_key_exists($userid, $USERS)) $USERS[$userid] = new spUser($userid, $current, $small);

	return (object) $USERS[$userid];
}

?>
