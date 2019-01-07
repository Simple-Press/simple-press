<?php
/*
Simple:Press
Profile API Routines
$LastChangedDate: 2014-11-27 10:40:54 -0600 (Thu, 27 Nov 2014) $
$Rev: 12110 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	SP Profile Handling
#
# ==========================================================================================

/**
* This function returns the current profile tabs and menus
*/
# Version: 5.0
function sp_profile_get_tabs() {
	$profile = sp_get_sfmeta('profile', array());
	$tabs = (!empty($profile)) ? $profile[0]['meta_value'] : '';
	return $tabs;
}

/**
* This function adds a new tab to the user profile form
* order 1 - n. pass order=0 to put at end
*/
# Version: 5.0
function sp_profile_add_tab($name, $order=0, $display=1, $auth='') {
	# sanitize before use
	$name = sp_filter_title_save($name);
	$slug = sp_create_slug($name, false);
	$display = (int) $display;
	$auth = sp_esc_str($auth);

	# get the current tabs
	$tabs = sp_profile_get_tabs();

	# make sure the tab doesnt already exist
	if ($tabs) {
		foreach ($tabs as $tab) {
			if ($tab['name'] == $name) return -1;
		}
	}

	# insert the new tab
	if (empty($order)) $order = (empty($tabs)) ? 0 : count($tabs);
	$newtab = array();
	$newtab['name'] = $name;
	$newtab['slug'] = $slug;
	$newtab['display'] = $display;
	$newtab['auth'] = $auth;
	sp_array_insert($tabs, $newtab, $order);

	# make sure its compact
	$tabs = array_values($tabs);

	# save the new profile tabs
	$result = sp_add_sfmeta('profile', 'tabs', $tabs);
	return $result;
}

/**
* This function adds a new menu to an existing tab
* order 1 - n. pass order=0 to put at end
*/
# Version: 5.0
function sp_profile_add_menu($menu, $name, $form, $order=0, $display=1, $auth='') {
	# sanitize before use
	$menu 		= sp_filter_title_save($menu);
	$slug 		=  sp_create_slug($name, false);
	$name 		= sp_filter_title_save($name);
	$form 		= str_replace('\\', '/', $form); # sanitize for Win32 installs
	$display 	= (int) $display;
	$auth 		= sp_esc_str($auth);

	# get profile tabs
	$tabs = sp_profile_get_tabs();
	if (empty($tabs)) return false;

	# find the requested tab
	foreach ($tabs as &$tab) {
		$found = false;
		if ($tab['name'] == $menu) {
			# make sure the menu doesnt already exist on this tab
			if (isset($tab['menus'] ) && $tab['menus']) {
			foreach ($tab['menus'] as $thisMenu) {
					if ($thisMenu['name'] == $name) return -1;
				}
			}

			# insert the new menu
			if (empty($order)) $order = (empty($tab['menus'])) ? 0 : count($tab['menus']);
			$newtab = array();
			$newtab['name'] = $name;
			$newtab['slug'] = $slug;
			$newtab['form'] = $form;
			$newtab['display'] = $display;
			$newtab['auth'] = $auth;
			sp_array_insert($tab['menus'], $newtab, $order);

			# make sure its compact
			$tab['menus'] = array_values($tab['menus']);

			# menu added so break out
			$found = true;
			break;
		}
	}

	# if tab wasnt found bail
	if (!$found) return false;

	# save the new profile tabs
	$result = sp_add_sfmeta('profile', 'tabs', $tabs);
	return $result;
}

/**
* This function deletes a tab and all menus under it
*/
# Version: 5.0
function sp_profile_delete_tab($name) {
	# sanitize before use
	$name 	= sp_filter_title_save($name);

	# get the current tabs
	$tabs = sp_profile_get_tabs();
	if (empty($tabs)) return false;

	# delete any tabs with the specified name
	foreach ($tabs as $index => $tab) {
		if ($tab['name'] == $name) unset($tabs[$index]);
	}
	$tabs = array_values($tabs);

	# reorder tabs afer removal and save
	$newtabs = serialize(array_values($tabs));
	$result = sp_add_sfmeta('profile', 'tabs', $tabs);
	return $result;
}

/**
* This function deletes a tab (based on slug) and all menus under it
*/
# Version: 5.0
function sp_profile_delete_tab_by_slug($slug) {
	# sanitize before use
	$slug 	= sp_filter_title_save($slug);

	# get the current tabs
	$tabs = sp_profile_get_tabs();
	if (empty($tabs)) return false;

	# delete any tabs with the specified name
	foreach ($tabs as $index => $tab) {
		if ($tab['slug'] == $slug) unset($tabs[$index]);
	}
	$tabs = array_values($tabs);

	# reorder tabs afer removal and save
	$newtabs = serialize(array_values($tabs));
	$result = sp_add_sfmeta('profile', 'tabs', $tabs);
	return $result;
}

/**
* This function deletes a menu from a tab
*/
# Version: 5.0
function sp_profile_delete_menu($tab, $name) {
	# sanitize before use
	$tab 	= sp_filter_title_save($tab);
	$name 	= sp_filter_title_save($name);

	# get the current tabs
	$tabs = sp_profile_get_tabs();
	if (empty($tabs)) return false;

	# find the requested tab
	foreach ($tabs as &$thisTab) {
		if ($thisTab['name'] == $tab) {
			# make sure the menu doesnt already exist on this tab
			if ($thisTab['menus']) {
				foreach ($thisTab['menus'] as $index => $menu) {
					if ($menu['name'] == $name) unset($thisTab['menus'][$index]);
				}
				$thisTab['menus'] = array_values($thisTab['menus']);
			}
		}
	}

	# reorder tabs afer removal and save
	$newtabs = serialize(array_values($tabs));
	$result = sp_add_sfmeta('profile', 'tabs', $tabs);
	return $result;
}

/**
* This function checks if tab is active
*/
# Version: 5.5.3
function sp_profile_tab_active($tabslug) {
	# get the current tabs
	$tabs = sp_profile_get_tabs();
	if (empty($tabs)) return false;

	# find the requested tab
	foreach ($tabs as &$thisTab) {
		if ($thisTab['slug'] == $tabslug) return $thisTab['display'];
	}

	return false;
}

/**
* This function checks if menu is active
*/
# Version: 5.5.3
function sp_profile_menu_active($menuslug) {
	# get the current tabs
	$tabs = sp_profile_get_tabs();
	if (empty($tabs)) return false;

	# find the requested tab
	foreach ($tabs as &$thisTab) {
        if (!empty($thisTab['menus'])) {
    		foreach ($thisTab['menus'] as $thisMenu) {
    			if ($thisMenu['slug'] == $menuslug) return $thisMenu['display'];
    		}
        }
	}
	return false;
}

?>