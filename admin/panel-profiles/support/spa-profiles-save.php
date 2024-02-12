<?php

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

#= Save Options Data ===============================
function spa_save_options_data() {
	check_admin_referer('forum-adminform_options', 'forum-adminform_options');
	$mess = SP()->primitives->admin_text('Profile options updated');

	$sfprofile = SP()->options->get('sfprofile');
	$old_sfprofile = $sfprofile;
	$sfprofile['nameformat'] = isset($_POST['nameformat']);
	$sfprofile['fixeddisplayformat'] = SP()->filters->integer($_POST['fixeddisplayformat']);
	$sfprofile['displaymode'] = SP()->filters->integer($_POST['displaymode']);
	$sfprofile['displaypage'] = SP()->saveFilters->cleanurl($_POST['displaypage']);
	$sfprofile['displayquery'] = SP()->saveFilters->title(trim($_POST['displayquery']));
	$sfprofile['formmode'] = SP()->filters->integer($_POST['formmode']);
	$sfprofile['formpage'] = SP()->saveFilters->cleanurl($_POST['formpage']);
	$sfprofile['formquery'] = SP()->saveFilters->title(trim($_POST['formquery']));
	$sfprofile['photosmax'] = SP()->filters->integer($_POST['photosmax']);
	$sfprofile['photoscols'] = SP()->filters->integer($_POST['photoscols']);

    $sfprofile['hideuserinfo'] = isset($_POST['hideuserinfo']) ?? false;

	$sfsigimagesize = [];
	$sfsigimagesize['sfsigwidth'] = SP()->filters->integer($_POST['sfsigwidth']);
	$sfsigimagesize['sfsigheight'] = SP()->filters->integer($_POST['sfsigheight']);
	SP()->options->update('sfsigimagesize', $sfsigimagesize);

    $sfprofile['firstvisit'] = isset($_POST['firstvisit']);
    $sfprofile['forcepw'] = isset($_POST['forcepw']);
	$sfprofile['sfprofiletext'] = SP()->saveFilters->text(trim($_POST['sfprofiletext']));

	SP()->options->update('sfprofile', $sfprofile);

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
		$user_join = SPUSERS.' ON '.SPMEMBERS.'.user_id = '.SPUSERS.'.ID';
		$first_name_join = SPUSERMETA.' a ON ('.SPUSERS.'.ID = a.user_id AND a.meta_key = \'first_name\')';
		$last_name_join = SPUSERMETA.' b ON ('.SPUSERS.'.ID = b.user_id AND b.meta_key = \'last_name\')';

		# Determine how many passes its going to take to update all users in the system
		# based on 100 users per pass.
		# -----------------------------------------------------------------------------
		$num_records = SP()->DB->count(SPMEMBERS,'');
		$passes = ceil($num_records / 100);
		$dupes = [];

		for ($i = 0; $i <= $passes; $i++) {
			$limit = 100;
			$offset = $i * $limit;

			$fields = SPMEMBERS.'.user_id, '.SPUSERS.'.user_login, '.SPUSERS.'.display_name, a.meta_value as first_name, b.meta_value as last_name';
			$join = [$user_join, $first_name_join, $last_name_join];

			$query = new stdClass();
			$query->table		= SPMEMBERS;
			$query->fields		= $fields;
			$query->left_join 	= $join;
			$query->limits		= $limit.' OFFSET '.$offset;
			$query->order		= SPMEMBERS.'.user_id';
			$query = apply_filters('sph_fixeddisplayformat_query', $query);
			$records = SP()->DB->select($query);

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
				$conflict = SP()->DB->count(SPMEMBERS, 'display_name = "'.$display_name.'" AND user_id <> '.$r->user_id);
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
            	$display_name = SP()->saveFilters->name($display_name);
				$query = 'UPDATE '.SPMEMBERS.' SET display_name = "'.$display_name.'" WHERE user_id = '.$r->user_id;
				SP()->DB->execute($query);
			}
		}

        # update the recent members in stats too
        SP()->user->update_recent();
	}

    do_action('sph_profiles_options_save');

	return $mess;
}

#= Save Profile Tabs Data ===============================
function spa_save_tabs_menus_data() {
	check_admin_referer('forum-adminform_tabsmenus', 'forum-adminform_tabsmenus');

	if (!empty($_POST['spTabsOrder'])) {
		# grab the current tabs/menus and init new tabs array
		$newTabs = [];

		# need to cycle through all the tabs
		$tabList = explode('&', sanitize_text_field($_POST['spTabsOrder']));
		foreach ($tabList as $curTab => $tab) {
			# extract the tab index from the jquery sortable mess
			$tabData = explode('=', $tab);
			$oldTab = $tabData[1];

			# now move the tab stuff (except menus) to its new location
			$newTabs[$curTab]['name'] = SP()->saveFilters->title($_POST['tab-name-'.$oldTab]);
			$newTabs[$curTab]['slug'] = SP()->saveFilters->title($_POST['tab-slug-'.$oldTab]);
			$newTabs[$curTab]['auth'] = SP()->saveFilters->title($_POST['tab-auth-'.$oldTab]);
			$newTabs[$curTab]['display'] = (isset($_POST['tab-display-'.$oldTab])) ? 1 : 0;

			# now update menus for this tab
			if (!empty($_POST['spMenusOrder'.$oldTab])) {
				$list = explode('&', sanitize_text_field($_POST['spMenusOrder'.$oldTab]));
				foreach ($list as $curMenu => $menu) {
 					# extract the menu index from the jquery sortable mess
					$menuData = explode('=', $menu);
					$thisMenu = $menuData[1];

					# extract the tab the menu came from (what a pain!)
					$junk = explode('tab', $menuData[0]);
					$stop = strpos($junk[1], '[');
					$oldMenuTab = substr($junk[1], 0, $stop);
					# copy over the menu from old location to new location
					$newTabs[$curTab]['menus'][$curMenu]['name'] = SP()->saveFilters->title($_POST['menu-name-'.$oldMenuTab.'-'.$thisMenu]);
					$newTabs[$curTab]['menus'][$curMenu]['slug'] = SP()->saveFilters->title($_POST['menu-slug-'.$oldMenuTab.'-'.$thisMenu]);
					$newTabs[$curTab]['menus'][$curMenu]['auth'] = SP()->saveFilters->title($_POST['menu-auth-'.$oldMenuTab.'-'.$thisMenu]);
					$newTabs[$curTab]['menus'][$curMenu]['display'] = (isset($_POST['menu-display-'.$oldMenuTab.'-'.$thisMenu])) ? 1 : 0;
					$form = str_replace('\\','/', sanitize_text_field($_POST['menu-form-'.$oldMenuTab.'-'.$thisMenu])); # sanitize for Win32 installs
					$form = preg_replace('|/+|','/', $form); # remove any duplicate slash
					$newTabs[$curTab]['menus'][$curMenu]['form'] = SP()->filters->str($form);
				}
			} else {
				$newTabs[$curTab]['menus'] = [];
			}
		}
		$mess = SP()->primitives->admin_text('Profile Tabs and Menus Updated!');

		SP()->meta->add('profile', 'tabs', $newTabs);
	} else {
		$mess = SP()->primitives->admin_text('No Changes to profile tabs and menus');
	}

	return $mess;
}

function spa_save_avatars_data() {
	check_admin_referer('forum-adminform_avatars', 'forum-adminform_avatars');
	$mess = '';

	$sfavatars = [];
    $sfavatars['sfshowavatars'] = isset($_POST['sfshowavatars']);
    $sfavatars['sfavataruploads'] = isset($_POST['sfavataruploads']);
    $sfavatars['sfavatarpool'] = isset($_POST['sfavatarpool']);
    $sfavatars['sfavatarremote'] = isset($_POST['sfavatarremote']);
    $sfavatars['sfavatarreplace'] = isset($_POST['sfavatarreplace']);
    $sfavatars['sfavatarresize'] = isset($_POST['sfavatarresize']);
	if (empty($sfavatars['sfavatarsize']) || $sfavatars['sfavatarsize'] == 0) {
        $sfavatars['sfavatarsize'] = 50;
    }
	if (empty($sfavatars['sfavatarfilesize']) || $sfavatars['sfavatarfilesize'] == 0) {
        $sfavatars['sfavatarfilesize'] = 10240;
    }

	if (!isset($_POST['sfgmaxrating'])) {
		$sfavatars['sfgmaxrating'] = 1;
	} else {
		$sfavatars['sfgmaxrating'] = SP()->filters->integer($_POST['sfgmaxrating']);
	}

	$sfavatars['sfavatarsize'] = (empty($_POST['sfavatarsize'])) ? 50 : SP()->filters->integer($_POST['sfavatarsize']);
	$sfavatars['sfavatarresizequality'] = (empty($_POST['sfavatarresizequality'])) ? 90 : SP()->filters->integer($_POST['sfavatarresizequality']);
	$sfavatars['sfavatarfilesize'] = (empty($_POST['sfavatarfilesize'])) ? 10240 : SP()->filters->integer($_POST['sfavatarfilesize']);


	$current = [];
	$current = SP()->options->get('sfavatars');

	if (!empty($_POST['sfavataropts']) && $_POST['sfavataropts']) {

		$list = explode('&', sanitize_text_field($_POST['sfavataropts']));
		$newarray = [];
		foreach ($list as $item) {
			$thisone = explode('=', $item);
			$add = true;
			if ($thisone[1] == 1 && $sfavatars['sfavatarreplace'] == true) $add = false;
			if ($thisone[1] == 2 && $sfavatars['sfavataruploads'] == false) $add = false;
			if ($thisone[1] == 4 && $sfavatars['sfavatarpool'] == false) $add = false;
			if ($thisone[1] == 5 && $sfavatars['sfavatarremote'] == false) $add = false;
			if ($add) {
				$newarray[] = SP()->filters->str($thisone[1]);
			}
		}

		foreach ($list as $item) {
			$thisone = explode('=', $item);
			$add = false;
			if ($thisone[1] == 1 && $sfavatars['sfavatarreplace'] == true) $add = true;
			if ($thisone[1] == 2 && $sfavatars['sfavataruploads'] == false) $add = true;
			if ($thisone[1] == 4 && $sfavatars['sfavatarpool'] == false) $add = true;
			if ($thisone[1] == 5 && $sfavatars['sfavatarremote'] == false) $add = true;
			if ($add) {
				$newarray[] = SP()->filters->str($thisone[1]);
			}
		}


		$sfavatars['sfavatarpriority'] = $newarray;
	} else {
		$sfavatars['sfavatarpriority'] = $current['sfavatarpriority'];
	}

    do_action('sph_profiles_avatars_save');

	SP()->options->update('sfavatars', $sfavatars);

	# now to save the avatar defaults
	$defs = SP()->options->get('spDefAvatars');
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/defaults/';
	$dlist = @opendir($path);

	if ($dlist) {
		while (false !== ($file = readdir($dlist))) {
			if ($file != "." && $file != "..") {
				$thisFile = str_replace('.', 'z1z2z3', $file);
				$index = SP()->filters->str($_POST[$thisFile]);
				if (isset($index) && $index != 'none') {
                    $defs[$index] = $file;
                }
			}
		}
	}
	SP()->options->update('spDefAvatars', $defs);

	$mess .= SP()->primitives->admin_text('Avatars updated');
	return $mess;
}
