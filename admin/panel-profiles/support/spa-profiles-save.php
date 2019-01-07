<?php
/*
Simple:Press
Admin Profile Update Support Functions
$LastChangedDate: 2016-07-12 06:40:35 -0500 (Tue, 12 Jul 2016) $
$Rev: 14431 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

#= Save Options Data ===============================
function spa_save_options_data() {
	check_admin_referer('forum-adminform_options', 'forum-adminform_options');
	$mess = spa_text('Profile options updated');

	$sfprofile = sp_get_option('sfprofile');
	$old_sfprofile = $sfprofile;
	$sfprofile['nameformat'] = isset($_POST['nameformat']);
	$sfprofile['fixeddisplayformat'] = sp_esc_int($_POST['fixeddisplayformat']);
	$sfprofile['displaymode'] = sp_esc_int($_POST['displaymode']);
	$sfprofile['displaypage'] = sp_filter_save_cleanurl($_POST['displaypage']);
	$sfprofile['displayquery'] = sp_filter_title_save(trim($_POST['displayquery']));
	$sfprofile['formmode'] = sp_esc_int($_POST['formmode']);
	$sfprofile['formpage'] = sp_filter_save_cleanurl($_POST['formpage']);
	$sfprofile['formquery'] = sp_filter_title_save(trim($_POST['formquery']));
	$sfprofile['photosmax'] = sp_esc_int($_POST['photosmax']);
	$sfprofile['photoscols'] = sp_esc_int($_POST['photoscols']);

	$sfsigimagesize = array();
	$sfsigimagesize['sfsigwidth'] = sp_esc_int($_POST['sfsigwidth']);
	$sfsigimagesize['sfsigheight'] = sp_esc_int($_POST['sfsigheight']);
	sp_update_option('sfsigimagesize', $sfsigimagesize);

    $sfprofile['firstvisit'] = isset($_POST['firstvisit']);
    $sfprofile['forcepw'] = isset($_POST['forcepw']);
	$sfprofile['sfprofiletext'] = sp_filter_text_save(trim($_POST['sfprofiletext']));

	sp_update_option('sfprofile', $sfprofile);

    # if changed force pw from true to false, remove any users waiting for pw change
    if ($old_sfprofile['forcepw'] && !$sfprofile['forcepw']) {
        delete_metadata('user', 0, 'sp_change_pw', '', true);
    }

	# If the name format changes from dynamic to fixed, we need to update
	# the display_name field for all users based on the selection from the dropdown
	# If there is a conflict between display names, a numeric value will be added to the
	# end of the display name to make them unique.
	# ----------------------------------------------------------------------------------

	if (($old_sfprofile['nameformat'] != $sfprofile['nameformat'] && empty($sfprofile['nameformat'])) || ($old_sfprofile['fixeddisplayformat'] != $sfprofile['fixeddisplayformat'] && empty($sfprofile['nameformat']))) {
		# The display format determines the WHERE clause and the tables to join.
		# ----------------------------------------------------------------------
		$fields = '';
		$user_join = SFUSERS.' ON '.SFMEMBERS.'.user_id = '.SFUSERS.'.ID';
		$first_name_join = SFUSERMETA.' a ON ('.SFUSERS.'.ID = a.user_id AND a.meta_key = \'first_name\')';
		$last_name_join = SFUSERMETA.' b ON ('.SFUSERS.'.ID = b.user_id AND b.meta_key = \'last_name\')';

		# Determine how many passes its going to take to update all users in the system
		# based on 100 users per pass.
		# -----------------------------------------------------------------------------
		$num_records = spdb_count(SFMEMBERS,'');
		$passes = ceil($num_records / 100);
		$dupes = array();

		for ($i = 0; $i <= $passes; $i++) {
			$limit = 100;
			$offset = $i * $limit;

			$fields = SFMEMBERS.'.user_id, '.SFUSERS.'.user_login, '.SFUSERS.'.display_name, a.meta_value as first_name, b.meta_value as last_name';
			$join = array($user_join, $first_name_join, $last_name_join);
			$spdb = new spdbComplex;
			$spdb->table		= SFMEMBERS;
			$spdb->fields		= $fields;
			$spdb->left_join 	= $join;
			$spdb->limits		= $limit.' OFFSET '.$offset;
			$spdb->order		= SFMEMBERS.'.user_id';
			$spdb = apply_filters('sph_fixeddisplayformat_query', $spdb);
			$records = $spdb->select();

			foreach ($records as $r) {
				switch ($sfprofile['fixeddisplayformat']) {
					default:
					case '0':
						$display_name = $r->display_name;
						break;

					case '1':
						$display_name = $r->user_login;
						break;

					case '2':
						$display_name = $r->first_name;
						break;

					case '3':
						$display_name = $r->last_name;
						break;

					case '4':
						$display_name = $r->first_name.' '.$r->last_name;
						break;

					case '5':
						$display_name = $r->last_name.', '.$r->first_name;
						break;

					case '6':
						$display_name = $r->first_name[0].' '.$r->last_name;
						break;

					case '7':
						$display_name = $r->first_name.' '.$r->last_name[0];
						break;

					case '8':
						$display_name = $r->first_name[0].$r->last_name[0];
						break;
				}

				# If the display name is empty for any reason, default to the user login name
				$display_name = trim($display_name);
				if (empty($display_name)) $display_name = $r->user_login;

				# Check to see if there are any matching users with this display name.  If so
				# assign a random number to the end to eliminate the duplicate
				# ----------------------------------------------------------------------------
				$conflict = spdb_count(SFMEMBERS, 'display_name = "'.$display_name.'" AND user_id <> '.$r->user_id);
				if ($conflict > 0) {
					if (array_key_exists($display_name, $dupes)) {
						$dupes[$display_name]++;
					} else {
						$dupes[$display_name]=1;
					}
					$display_name = $display_name.$dupes[$display_name];
				}

				# Now Update the member record
				# ----------------------------
            	$display_name = sp_filter_name_save($display_name);
				$query = 'UPDATE '.SFMEMBERS.' SET display_name = "'.$display_name.'" WHERE user_id = '.$r->user_id;
				$result = spdb_query($query);
			}
		}

        # update the recent members in stats too
        sp_update_recent_members();
	}

    do_action('sph_profiles_options_save');

	return $mess;
}

#= Save Profile Tabs Data ===============================
function spa_save_tabs_menus_data() {
	check_admin_referer('forum-adminform_tabsmenus', 'forum-adminform_tabsmenus');

	if (!empty($_POST['spTabsOrder'])) {
		# grab the current tabs/menus and init new tabs array
		$newTabs = array();
		$curTabs = sp_profile_get_tabs();

		# need to cycle through all the tabs
		$tabList = explode('&', $_POST['spTabsOrder']);
		foreach ($tabList as $curTab => $tab) {
            $tab = sp_esc_str($tab);
			# extract the tab index from the jquery sortable mess
			$tabData = explode('=', $tab);
			$oldTab = $tabData[1];

			# now move the tab stuff (except menus) to its new location
			$newTabs[$curTab]['name'] = sp_filter_title_save($_POST['tab-name-'.$oldTab]);
			$newTabs[$curTab]['slug'] = sp_filter_title_save($_POST['tab-slug-'.$oldTab]);
			$newTabs[$curTab]['auth'] = sp_filter_title_save($_POST['tab-auth-'.$oldTab]);
			$newTabs[$curTab]['display'] = (isset($_POST['tab-display-'.$oldTab])) ? 1 : 0;

			# now update menus for this tab
			if (!empty($_POST['spMenusOrder'.$oldTab])) {
				$menuList = explode('&', $_POST['spMenusOrder'.$oldTab]);
				foreach ($menuList as $curMenu => $menu) {
                    $menu = sp_esc_str($menu);
					# extract the menu index from the jquery sortable mess
					$menuData = explode('=', $menu);
					$thisMenu = $menuData[1];

					# extract the tab the menu came from (what a pain!)
					$junk = explode('tab', $menuData[0]);
					$stop = strpos($junk[1], '[');
					$oldMenuTab = substr($junk[1], 0, $stop);
					# copy over the menu from old location to new location
					$newTabs[$curTab]['menus'][$curMenu]['name'] = sp_filter_title_save($_POST['menu-name-'.$oldMenuTab.'-'.$thisMenu]);
					$newTabs[$curTab]['menus'][$curMenu]['slug'] = sp_filter_title_save($_POST['menu-slug-'.$oldMenuTab.'-'.$thisMenu]);
					$newTabs[$curTab]['menus'][$curMenu]['auth'] = sp_filter_title_save($_POST['menu-auth-'.$oldMenuTab.'-'.$thisMenu]);
					$newTabs[$curTab]['menus'][$curMenu]['display'] = (isset($_POST['menu-display-'.$oldMenuTab.'-'.$thisMenu])) ? 1 : 0;
					$form = str_replace('\\','/', $_POST['menu-form-'.$oldMenuTab.'-'.$thisMenu]); # sanitize for Win32 installs
					$form = preg_replace('|/+|','/', $form); # remove any duplicate slash
					$newTabs[$curTab]['menus'][$curMenu]['form'] = sp_esc_str($form);
				}
			} else {
				$newTabs[$curTab]['menus'] = array();
			}
		}
		$mess = spa_text('Profile Tabs and Menus Updated!');

		sp_add_sfmeta('profile', 'tabs', $newTabs);
	} else {
		$mess = spa_text('No Changes to profile tabs and menus');
	}

	return $mess;
}

function spa_save_avatars_data() {
	global $spPaths;

	check_admin_referer('forum-adminform_avatars', 'forum-adminform_avatars');
	$mess = '';

	$sfavatars = array();
    $sfavatars['sfshowavatars'] = isset($_POST['sfshowavatars']);
    $sfavatars['sfavataruploads'] = isset($_POST['sfavataruploads']);
    $sfavatars['sfavatarpool'] = isset($_POST['sfavatarpool']);
    $sfavatars['sfavatarremote'] = isset($_POST['sfavatarremote']);
    $sfavatars['sfavatarreplace'] = isset($_POST['sfavatarreplace']);
    $sfavatars['sfavatarresize'] = isset($_POST['sfavatarresize']);
	if (empty($sfavatars['sfavatarsize']) || $sfavatars['sfavatarsize'] == 0) $sfavatars['sfavatarsize'] = 50;
	if (empty($sfavatars['sfavatarfilesize']) || $sfavatars['sfavatarfilesize'] == 0) $sfavatars['sfavatarfilesize'] = 10240;

	if(!isset($_POST['sfgmaxrating'])) {
		$sfavatars['sfgmaxrating'] = 1;
	} else {
		$sfavatars['sfgmaxrating'] = sp_esc_int($_POST['sfgmaxrating']);
	}

	$sfavatars['sfavatarsize'] = (empty($_POST['sfavatarsize'])) ? 50 : sp_esc_int($_POST['sfavatarsize']);
	$sfavatars['sfavatarresizequality'] = (empty($_POST['sfavatarresizequality'])) ? 90 : sp_esc_int($_POST['sfavatarresizequality']);
	$sfavatars['sfavatarfilesize'] = (empty($_POST['sfavatarfilesize'])) ? 10240 : sp_esc_int($_POST['sfavatarfilesize']);


	$current = array();
	$current = sp_get_option('sfavatars');

	if (!empty($_POST['sfavataropts']) && $_POST['sfavataropts']) {

		$list = explode('&', $_POST['sfavataropts']);
		$newarray = array();
		foreach ($list as $item) {
			$thisone = explode('=', $item);
			$add = true;
			if($thisone[1] == 1 && $sfavatars['sfavatarreplace'] == true) $add = false;
			if($thisone[1] == 2 && $sfavatars['sfavataruploads'] == false) $add = false;
			if($thisone[1] == 4 && $sfavatars['sfavatarpool'] == false) $add = false;
			if($thisone[1] == 5 && $sfavatars['sfavatarremote'] == false) $add = false;
			if($add) {
				$newarray[] = sp_esc_str($thisone[1]);
			}
		}

		foreach ($list as $item) {
			$thisone = explode('=', $item);
			$add = false;
			if($thisone[1] == 1 && $sfavatars['sfavatarreplace'] == true) $add = true;
			if($thisone[1] == 2 && $sfavatars['sfavataruploads'] == false) $add = true;
			if($thisone[1] == 4 && $sfavatars['sfavatarpool'] == false) $add = true;
			if($thisone[1] == 5 && $sfavatars['sfavatarremote'] == false) $add = true;
			if($add) {
				$newarray[] = sp_esc_str($thisone[1]);
			}
		}


		$sfavatars['sfavatarpriority'] = $newarray;
	} else {
		$sfavatars['sfavatarpriority'] = $current['sfavatarpriority'];
	}

    do_action('sph_profiles_avatars_save');

	sp_update_option('sfavatars', $sfavatars);

	# now to save the avatar defaults
	$defs = sp_get_option('spDefAvatars');
	$path = SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/';
	$dlist = @opendir($path);

	if ($dlist) {
		while (false !== ($file = readdir($dlist))) {
			if ($file != "." && $file != "..") {
				$thisFile = str_replace('.', 'z1z2z3', $file);
				if(isset($_POST[$thisFile])) {
					if($_POST[$thisFile] != 'none') {
						$defs[$_POST[$thisFile]] = $file;
					}
				}
			}
		}
	}
	sp_update_option('spDefAvatars', $defs);

	$mess .= spa_text('Avatars updated');
	return $mess;
}
?>