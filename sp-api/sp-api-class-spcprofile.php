<?php

/**
 * Core class used for Simple Press profiles.
 *
 * This class is used to access the profile api code within Simple:Press
 *
 * @since 6.0
 *
 * Public methods available:
 *------------------------
 * get_tabs_menus()
 * add_tab($name, $order, $display, $auth)
 * add_menu($menu, $name, $form, $order, $display, $auth)
 * delete_tab($name)
 * delete_tab_by_slug($slug)
 * delete_menu($tab, $name)
 * is_tab_active($tabslug)
 * is_menu_active($menuslug)
 *
 * $LastChangedDate: 2017-01-08 19:08:09 -0800 (Sun, 08 Jan 2017) $
 * $Rev: 15009 $
 *
 */
class spcProfile {
	/**
	 * This method returns a list of all current profile tabs and menus.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    array   list of profile tabs and menus
	 */
	public function get_tabs_menus() {
		$profile = SP()->meta->get('profile', array());
		$tabs    = (!empty($profile)) ? $profile[0]['meta_value'] : '';

		return $tabs;
	}

	/**
	 * This method returns a list of all current profile tabs and menus.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name    name of the tab
	 * @param int    $order   order of the tab (1 - N(
	 * @param int    $display is the tab active
	 * @param string $auth    auth user needs to see the profile tab
	 *
	 * @returns    bool        true if tab added, otherwise false
	 */
	public function add_tab($name, $order = 0, $display = 1, $auth = '') {
		# sanitize before use
		$name    = SP()->saveFilters->title($name);
		$slug    = sp_create_slug($name, false);
		$display = (int)$display;
		$auth    = SP()->filters->str($auth);

		# get the current tabs
		$tabs = $this->get_tabs_menus();

		# make sure the tab doesnt already exist
		if ($tabs) {
			foreach ($tabs as $tab) {
				if ($tab['name'] == $name) return false;
			}
		}

		# insert the new tab
		if (empty($order)) $order = (empty($tabs)) ? 0 : count($tabs);
		$newtab            = array();
		$newtab['name']    = $name;
		$newtab['slug']    = $slug;
		$newtab['display'] = $display;
		$newtab['auth']    = $auth;
		SP()->primitives->array_insert($tabs, $newtab, $order);

		# make sure its compact
		$tabs = array_values($tabs);

		# save the new profile tabs
		$result = SP()->meta->add('profile', 'tabs', $tabs);

		return $result;
	}

	/**
	 * This method returns a list of all current profile tabs and menus.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param void
	 *
	 * @returns    bool        true if menu added, otherwise false
	 */
	public function add_menu($menu, $name, $form, $order = 0, $display = 1, $auth = '') {
		# sanitize before use
		$menu    = SP()->saveFilters->title($menu);
		$slug    = sp_create_slug($name, false);
		$name    = SP()->saveFilters->title($name);
		$form    = str_replace('\\', '/', $form); # sanitize for Win32 installs
		$display = (int)$display;
		$auth    = SP()->filters->str($auth);

		# get profile tabs
		$tabs = $this->get_tabs_menus();
		if (empty($tabs)) return false;

		# find the requested tab
		$found = false;
		foreach ($tabs as &$tab) {
			if ($tab['name'] == $menu) {
				# make sure the menu doesnt already exist on this tab
				if (isset($tab['menus']) && $tab['menus']) {
					foreach ($tab['menus'] as $thisMenu) {
						if ($thisMenu['name'] == $name) return false;
					}
				}

				# insert the new menu
				if (empty($order)) $order = (empty($tab['menus'])) ? 0 : count($tab['menus']);
				$newtab            = array();
				$newtab['name']    = $name;
				$newtab['slug']    = $slug;
				$newtab['form']    = $form;
				$newtab['display'] = $display;
				$newtab['auth']    = $auth;
				SP()->primitives->array_insert($tab['menus'], $newtab, $order);

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
		$result = SP()->meta->add('profile', 'tabs', $tabs);

		return $result;
	}

	/**
	 * This method deletes a profile tab by the tab name.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $name name of the tab to delete
	 *
	 * @returns    bool        true if tab deleted, otherwise false
	 */
	public function delete_tab($name) {
		# sanitize before use
		$name = SP()->saveFilters->title($name);

		# get the current tabs
		$tabs = $this->get_tabs_menus();
		if (empty($tabs)) return false;

		# delete any tabs with the specified name
		foreach ($tabs as $index => $tab) {
			if ($tab['name'] == $name) unset($tabs[$index]);
		}
		$tabs = array_values($tabs);

		# reorder tabs afer removal and save
		$result = SP()->meta->add('profile', 'tabs', $tabs);

		return $result;
	}

	/**
	 * This method deletes a profile tab by the tab slag.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $slug the tab slug of the tab to delete
	 *
	 * @returns    bool        true if tab deleted, otherwise false
	 */
	public function delete_tab_by_slug($slug) {
		# sanitize before use
		$slug = SP()->saveFilters->title($slug);

		# get the current tabs
		$tabs = $this->get_tabs_menus();
		if (empty($tabs)) return false;

		# delete any tabs with the specified name
		foreach ($tabs as $index => $tab) {
			if ($tab['slug'] == $slug) unset($tabs[$index]);
		}
		$tabs = array_values($tabs);

		# reorder tabs afer removal and save
		$result = SP()->meta->add('profile', 'tabs', $tabs);

		return $result;
	}

	/**
	 * This method deletes a profile menu by name from the specified tab.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $tab  name of the tab to delete menu from
	 * @param string $name name of the menu to delete
	 *
	 * @returns    bool        true if tab deleted, otherwise false
	 */
	public function delete_menu($tab, $name) {
		# sanitize before use
		$tab  = SP()->saveFilters->title($tab);
		$name = SP()->saveFilters->title($name);

		# get the current tabs
		$tabs = $this->get_tabs_menus();
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
		$result = SP()->meta->add('profile', 'tabs', $tabs);

		return $result;
	}

	/**
	 * This method checks if the specified tab is active (displayed).
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $tabslug the tab slug of the tab to check if active
	 *
	 * @returns    bool        true if tab deleted, otherwise false
	 */
	function is_tab_active($tabslug) {
		# get the current tabs
		$tabs = $this->get_tabs_menus();
		if (empty($tabs)) return false;

		# find the requested tab
		foreach ($tabs as &$thisTab) {
			if ($thisTab['slug'] == $tabslug) return $thisTab['display'];
		}

		return false;
	}

	/**
	 * This method checks if the specified menu is active (displayed).
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $menuslug the menu slug of the menu to check if active
	 *
	 * @returns    bool        true if tab deleted, otherwise false
	 */
	function is_menu_active($menuslug) {
		# get the current tabs
		$tabs = $this->get_tabs_menus();
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
}