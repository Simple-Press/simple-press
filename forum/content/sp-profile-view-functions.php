<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2016-07-12 06:40:35 -0500 (Tue, 12 Jul 2016) $
$Rev: 14431 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	sp_ProfileEdit()
#	Display profile tabs and forms for current user
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileEdit($tabSlug='profile', $menuSlug='') {
	# is this edit for current user of admin edit of user
	global $spVars, $spGlobals, $spThisUser;

	if (!empty($spVars['member'])) {
		$userid = $spVars['member'];
	} else {
		$userid = $spThisUser->ID;
	}

	if (empty($userid) || ($spThisUser->ID != $userid && !$spThisUser->admin)) {
		sp_notify(SPFAILURE, sp_text('Invalid profile request'));
		$out = sp_render_queued_notification();
		$out.= '<div class="spMessage">';
		$out.= apply_filters('sph_ProfileErrorMsg', sp_text('Sorry, an invalid profile request was detected. Do you need to log in?'));
		$out.= '</div>';
		echo $out;
		return;
	}
	sp_SetupUserProfileData($userid);

	# display the profile tabs
	do_action('sph_profile_edit_before');

	# see if query args used to specify tab and/or menu
	if (isset($_GET['ptab'])) $tabSlug = sp_esc_str($_GET['ptab']);
	if (isset($_GET['pmenu'])) $menuSlug = sp_esc_str($_GET['pmenu']);

	$tabs = sp_profile_get_tabs();
	if (!empty($tabs)) {
		do_action('sph_profile_edit_before_tabs');
		echo '<ul id="spProfileTabs">';
		$first = true;
		$exist = false;
		foreach ($tabs as $tab) {
			# do we need an auth check?
			$authCheck = (empty($tab['auth'])) ? true : sp_get_auth($tab['auth'], '', $userid);

			# is this tab being displayed and does user have auth to see it?
			if ($authCheck && $tab['display']) {
				if ($first) $firstDisplayTab = $tab['slug']; # remember first displayed tab as fallback
				if ($tab['slug'] == $tabSlug) $exist = true; # not if selected tab exists
				$class = ($first) ? "class='current'" : '';
				$first = false;
				$ajaxURL = wp_nonce_url(SPAJAXURL.'profile&amp;tab='.$tab['slug']."&amp;user=$userid&amp;rand=".rand(), 'profile');
				if (is_ssl()) $ajaxURL = str_replace('http://', "https://", $ajaxURL);
				echo "<li><a rel='nofollow' id='spProfileTab-".esc_attr($tab['slug'])."' $class href='$ajaxURL'>".$tab['name'].'</a></li>';
			}
		}
		echo '</ul>';

		do_action('sph_profile_edit_after_tabs');

		# output the profile content area
		# dont need to fill as the js on page load will load default panel
		echo '<div id="spProfileContent">';
		echo '</div>';

		# inline js to create profile tabs
		global $firstTab, $firstMenu;
		$firstTab = ($exist) ? $tabSlug : $firstDisplayTab; # if selected tab does not exist, use first tab
		$firstMenu = $menuSlug;

		# are we forcing password change on first login?
		if (isset($spThisUser->sp_change_pw) && $spThisUser->sp_change_pw) {
			$firstTab = 'profile';
			$firstMenu = 'account-settings';
		}

		add_action('wp_footer', 'sp_ProfileEditFooter');
	}

	do_action('sph_profile_edit_after');
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileEditMobile()
#	Display profile tabs and forms for current user in mobile friendly format
#	Scope:	site
#	Version: 5.2.3
#
# --------------------------------------------------------------------------------------
function sp_ProfileEditMobile($tabSlug='profile', $menuSlug='overview') {
	# is this edit for current user of admin edit of user
	global $spVars, $spThisUser;

	if (!empty($spVars['member'])) {
		$userid = (int) $spVars['member'];
	} else {
		$userid = $spThisUser->ID;
	}

	if (empty($userid) || ($spThisUser->ID != $userid && !$spThisUser->admin)) {
		sp_notify(SPFAILURE, sp_text('Invalid profile request'));
		$out = sp_render_queued_notification();
		$out.= '<div class="spMessage">';
		$out.= apply_filters('sph_ProfileErrorMsg', sp_text('Sorry, an invalid profile request was detected. Do you need to log in?'));
		$out.= '</div>';
		echo $out;
		return;
	}

	# see if query args used to specify tab and/or menu
	if (isset($_GET['ptab'])) $tabSlug = sp_esc_str($_GET['ptab']);
	if (isset($_GET['pmenu'])) $menuSlug = sp_esc_str($_GET['pmenu']);

	# set up the profile data
	global $spProfileUser;
	sp_SetupUserProfileData($userid);

	do_action('sph_profile_edit_before');
	do_action('sph_ProfileStart');

	$tabs = sp_profile_get_tabs();
	if (!empty($tabs)) {
		do_action('sph_profile_edit_before_tabs');

		echo '<div id="spProfileAccordion">';
		echo "<div class='spProfileAccordionTab'>\n";

		$firstTab = $firstMenu = '';
		$tabSlugExist = $menuSlugExist = false;

		foreach ($tabs as $tab) {
			# do we need an auth check?
			$authCheck = (empty($tab['auth'])) ? true : sp_get_auth($tab['auth'], '', $userid);

			# is this tab being displayed and does user have auth to see it?
			if ($authCheck && $tab['display']) {
				if ($tab['slug'] == $tabSlug) $tabSlugExist = true;
				if (empty($firstTab)) $firstTab = $tab['slug'];

				echo '<h2 id="spProfileTabTitle-'.esc_attr($tab['slug']).'">'.sp_filter_title_display($tab['name'])."</h2>\n";
				echo "<div id='spProfileTab-".esc_attr($tab['slug'])."' class='spProfileAccordionPane'>\n";

				if (!empty($tab['menus'])) {
					echo "<div class='spProfileAccordionTab'>\n";
					foreach ($tab['menus'] as $menu) {
						# do we need an auth check?
						$authCheck = (empty($menu['auth'])) ? true : sp_get_auth($menu['auth'], '', $userid);

						# is this menu being displayed and does user have auth to see it?
						if ($authCheck && $menu['display']) {
							if ($menu['slug'] == $menuSlug) $menuSlugExist = true;
							if (empty($firstMenu)) $firstMenu = $menu['slug'];
							$thisSlug = $menu['slug']; # this variable is used in the form action url

							# special checking for displaying menus
							$spProfileOptions = sp_get_option('sfprofile');
							$spAvatars = sp_get_option('sfavatars');
							$noPhotos = ($menu['slug'] == 'edit-photos' && $spProfileOptions['photosmax'] < 1); # dont display edit photos if disabled
							$noAvatars = ($menu['slug'] == 'edit-avatars' && !$spAvatars['sfshowavatars']); # dont display edit avatars if disabled
							$hideMenu = ($noPhotos || $noAvatars);
							$hideMenu = apply_filters('sph_ProfileMenuHide', $hideMenu, $tab, $menu, $userid);
							if (!$hideMenu) {
								echo '<h2 id="spProfileMenuTitle-'.esc_attr($menu['slug']).'">'.sp_filter_title_display($menu['name'])."</h2>\n";
								echo "<div id='spProfileMenu-".esc_attr($menu['slug'])."' class='spProfileAccordionPane'>\n";
								if (!empty($menu['form']) && file_exists($menu['form'])) {
									echo "<div class='spProfileAccordionForm'>\n";
									include_once ($menu['form']);
									echo "</div>\n";
								} else {
									echo sp_text('Profile form could not be found').': ['.$menu['name'].']<br />';
									echo sp_text('You might try the forum - toolbox - housekeeping admin form and reset the profile tabs and menus and see if that helps');
								}
								echo "</div>\n"; # menu pane
							}
						}
					}
					echo "</div>\n"; # menu accordion
				}
				echo "</div>\n"; # tab pane
			}
		}
		echo "</div>\n"; # tab accordion
		echo '</div>'; # profile accordion

		do_action('sph_profile_edit_after_tabs');

		# inline js to create profile tabs
		global $firstTab, $firstMenu;
		$firstTab = ($tabSlugExist) ? $tabSlug : $firstTab; # if selected tab does not exist, use first tab
		$firstMenu = ($menuSlugExist) ? $menuSlug : $firstMenu; # if selected tab does not exist, use first menu in first tab

		# are we forcing password change on first login?
		if (isset($spThisUser->sp_change_pw) && $spThisUser->sp_change_pw) {
			$firstTab = 'profile';
			$firstMenu = 'account-settings';
		}

		add_action('wp_footer', 'sp_ProfileEditFooterMobile');
	}

	do_action('sph_profile_edit_after');
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowHeader()
#	Display a users profile
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowHeader($args='', $label='') {
	global $spThisUser, $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagId'			=> 'spProfileShowHeader',
				  'tagClass'		=> 'spProfileShowHeader',
				  'editClass'		=> 'spProfileShowHeaderEdit',
				  'onlineStatus'	=> 1,
				  'statusClass'		=> 'spOnlineStatus',
				  'onlineIcon'		=> 'sp_UserOnline.png',
				  'offlineIcon'		=> 'sp_UserOffline.png',
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowHeader_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$editClass		= esc_attr($editClass);
	$statusClass	= esc_attr($statusClass);
	$onlineStatus	= (int) $onlineStatus;
	$label			= str_ireplace('%USER%', $spProfileUser->display_name, $label);
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;

	# output the header
	$adminEdit = '';
	$out = "<div id='$tagId' class='$tagClass'>$label$adminEdit";
	if ($spThisUser->admin) {
		$out.= '<a href="'.sp_url('profile/'.$spProfileUser->ID.'/edit').'">';
		$out.= " <span class='$editClass'>(".sp_text('Edit User Profile').')</span>';
		if ($onlineStatus) $out.= sp_OnlineStatus("tagClass=$statusClass&onlineIcon=$onlineIcon&offlineIcon=$offlineIcon&echo=0", $spProfileUser->ID, $spProfileUser);
		$out.= '</a>';
	}
	$out.= "</div>\n";

	$out = apply_filters('sph_ProfileShowHeader', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowDisplayName()
#	Display a users profile
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowDisplayName($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowDisplayName',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowDisplayName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->display_name;

	# output display name
	$out = '';
	$out.= "<div class='$leftClass'>";
	$out.= "<p class='$tagClass'>$label:</p>";
	$out.= '</div>';
	$out.= "<div class='$middleClass'></div>";
	$out.= "<div class='$rightClass'>";
	$out.= "<p class='$tagClass'>$spProfileUser->display_name</p>";
	$out.= "</div>\n";

	$out = apply_filters('sph_ProfileShowDisplayName', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowFirstName()
#	Display a users profile
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowFirstName($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowFirstName',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowFirstName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->first_name;

	# output first name
	if (!empty($spProfileUser->first_name) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$name = (empty($spProfileUser->first_name)) ? '&nbsp;' : $spProfileUser->first_name;
		$out.= "<p class='$tagClass'>$name</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowFirstName', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowLastName()
#	Display a users profile
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowLastName($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowLastName',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLastName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->last_name;

	# output first name
	if (!empty($spProfileUser->last_name) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$name = (empty($spProfileUser->last_name)) ? '&nbsp;' : $spProfileUser->last_name;
		$out.= "<p class='$tagClass'>$name</p>";
		$out.= "</div>\n";
		$out = apply_filters('sph_ProfileShowLastName', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowWebsite()
#	Display a users website link
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowWebsite($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowWebsite',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowWebsite_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->user_url;

	# output first name
	if (!empty($spProfileUser->user_url) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		if (empty($spProfileUser->user_url)) {
			$url = '&nbsp;';
		} else {
			$url = sp_filter_display_links($spProfileUser->user_url);
			$spFilters = sp_get_option('sffilters');
			if ($spFilters['sfnofollow']) $url = sp_filter_save_nofollow($url);
			if ($spFilters['sftarget']) $url = sp_filter_save_target($url);
		}
		$out.= "<p class='$tagClass'>$url</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowWebsite', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowLocation()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowLocation($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowWebsite',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLocation_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->location;

	# output first name
	if (!empty($spProfileUser->location) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$location = (empty($spProfileUser->location)) ? '&nbsp;' : $spProfileUser->location;
		$out.= "<p class='$tagClass'>$location</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowLocation', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowBio()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowBio($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowBio',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowBio_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->description;

	# output first name
	if (!empty($spProfileUser->description) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$description = (empty($spProfileUser->description)) ? '&nbsp;' : $spProfileUser->description;
		$out.= "<div class='$tagClass'>$description</div>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowBio', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowAIM()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowAIM($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowAIM',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowAIM_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->aim;

	# output first name
	if (!empty($spProfileUser->aim) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$aim = (empty($spProfileUser->aim)) ? '&nbsp;' : $spProfileUser->aim;
		$out.= "<p class='$tagClass'>$aim</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowAIM', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowYIM()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowYIM($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowYIM',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowAIM_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->yim;

	# output first name
	if (!empty($spProfileUser->yim) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$yim = (empty($spProfileUser->yim)) ? '&nbsp;' : $spProfileUser->yim;
		$out.= "<p class='$tagClass'>$yim</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowYIM', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowYIM()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowICQ($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowICQ',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowICQ_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->icq;

	# output first name
	if (!empty($spProfileUser->icq) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$icq = (empty($spProfileUser->icq)) ? '&nbsp;' : $spProfileUser->icq;
		$out.= "<p class='$tagClass'>$icq</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowICQ', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowGoogleTalk()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowGoogleTalk($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowGoogleTalk',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowGoogleTalk_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->jabber;

	# output first name
	if (!empty($spProfileUser->jabber) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$jabber = (empty($spProfileUser->jabber)) ? '&nbsp;' : $spProfileUser->jabber;
		$out.= "<p class='$tagClass'>$jabber</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowGoogleTalk', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowMSN()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowMSN($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowMSN',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowMSN_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->msn;

	# output first name
	if (!empty($spProfileUser->msn) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$msn = (empty($spProfileUser->msn)) ? '&nbsp;' : $spProfileUser->msn;
		$out.= "<p class='$tagClass'>$msn</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowMSN', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowMySpace()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowMySpace($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowMySpace',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowMySpace_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->myspace;

	# output first name
	if (!empty($spProfileUser->myspace) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$myspace = (empty($spProfileUser->myspace)) ? '&nbsp;' : $spProfileUser->myspace;
		$out.= "<p class='$tagClass'>$myspace</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowMySpace', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowSkype()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowSkype($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowSkype',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowSkype_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->skype;

	# output first name
	if (!empty($spProfileUser->skype) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$skype = (empty($spProfileUser->skype)) ? '&nbsp;' : $spProfileUser->skype;
		$out.= "<p class='$tagClass'>$skype</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowSkype', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowFacebook()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowFacebook($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowFacebook',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowFacebook_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->facebook;

	# output first name
	if (!empty($spProfileUser->facebook) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$facebook = (empty($spProfileUser->facebook)) ? '&nbsp;' : $spProfileUser->facebook;
		$out.= "<p class='$tagClass'>$facebook</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowFacebook', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowTwitter()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowTwitter($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowTwitter',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowTwitter_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->twitter;

	# output first name
	if (!empty($spProfileUser->twitter) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$twitter = (empty($spProfileUser->twitter)) ? '&nbsp;' : $spProfileUser->twitter;
		$out.= "<p class='$tagClass'>$twitter</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowTwitter', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowLinkedIn()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowLinkedIn($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowLinkedIn',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLinkedIn_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->linkedin;

	# output first name
	if (!empty($spProfileUser->linkedin) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$linkedin = (empty($spProfileUser->linkedin)) ? '&nbsp;' : $spProfileUser->linkedin;
		$out.= "<p class='$tagClass'>$linkedin</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowLinkedIn', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowYoutube()
#	Display a users youtube account
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowYouTube($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowYouTube',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'showEmpty'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowYouTube_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$showEmpty		= (int) $showEmpty;
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->youtube;

	# output first name
	if (!empty($spProfileUser->youtube) || $showEmpty) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$youtube = (empty($spProfileUser->youtube)) ? '&nbsp;' : $spProfileUser->youtube;
		$out.= "<p class='$tagClass'>$youtube</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowYouTube', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowMemberSince()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowMemberSince($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowMemberSince',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowMemberSince_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->user_registered;

	# output first name
	$out = '';
	$out.= "<div class='$leftClass'>";
	$out.= "<p class='$tagClass'>$label:</p>";
	$out.= '</div>';
	$out.= "<div class='$middleClass'></div>";
	$out.= "<div class='$rightClass'>";
	$out.= "<p class='$tagClass'>".sp_date('d', $spProfileUser->user_registered).'</p>';
	$out.= "</div>\n";

	$out = apply_filters('sph_ProfileShowMemberSince', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowLastVisit()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowLastVisit($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowLastVisit',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLastVisit_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->lastvisit;

	# output first name
	$out = '';
	$out.= "<div class='$leftClass'>";
	$out.= "<p class='$tagClass'>$label:</p>";
	$out.= '</div>';
	$out.= "<div class='$middleClass'></div>";
	$out.= "<div class='$rightClass'>";
	$out.= "<p class='$tagClass'>".sp_date('d', $spProfileUser->lastvisit).' '.sp_date('t', $spProfileUser->lastvisit).'</p>';
	$out.= "</div>\n";

	$out = apply_filters('sph_ProfileShowLastVisit', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowUserPosts()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowUserPosts($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowUserPosts',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowUserPosts_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$count = max($spProfileUser->posts, 0);

	if ($get) return $count;

	# output first name
	$out = '';
	$out.= "<div class='$leftClass'>";
	$out.= "<p class='$tagClass'>$label:</p>";
	$out.= '</div>';
	$out.= "<div class='$middleClass'></div>";
	$out.= "<div class='$rightClass'>";
	$out.= "<p class='$tagClass'>".$count.'</p>';
	$out.= "</div>\n";

	$out = apply_filters('sph_ProfileShowUserPosts', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowSearchPosts()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
#	Version 5.5.9:
#		Addeed arguments $labelYouStarted, $labelYouPosted
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowSearchPosts($args='', $label='', $labelStarted='', $labelPosted='', $labelYouStarted='', $labelYouPosted='') {
	global $spProfileUser, $spThisUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileSearchPosts',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'linkClass'	=> 'spButton spLeft',
				  'echo'		=> 1,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowSearchPosts_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$linkClass		= esc_attr($linkClass);
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;

	if ($spProfileUser->ID == $spThisUser->ID) {
		if(empty($labelYouStarted)) $labelYouStarted = sp_text('List Topics You Started');
		if(empty($labelYouPosted))	$labelYouPosted	 = sp_text('List Topics You Have Posted To');
		$labelYouStarted = sp_filter_title_display($labelYouStarted);
		$labelYouPosted	 = sp_filter_title_display($labelYouPosted);
	} else {
		if(!empty($labelStarted)) {
			$labelStarted = str_replace('%USERNAME%', $spProfileUser->display_name, $labelStarted);
		} else {
			$labelStarted = sprintf(sp_text('List Topics %1$s Has Started'), $spProfileUser->display_name);
		}
		if(!empty($labelPosted)) {
			$labelPosted = str_replace('%USERNAME%', $spProfileUser->display_name, $labelPosted);
		} else {
			$labelPosted = sprintf(sp_text('List Topics %1$s Has Posted To'), $spProfileUser->display_name);
		}
		$labelStarted	= sp_filter_title_display($labelStarted);
		$labelPosted	= sp_filter_title_display($labelPosted);
	}

	# output first name
	$out = '';
	$out.= "<div class='$leftClass'>";
	$out.= "<p class='$tagClass'>$label:</p>";
	$out.= '</div>';
	$out.= "<div class='$middleClass'></div>";
	$out.= "<div class='$rightClass'>";
	$out.= '<form action="'.wp_nonce_url(SPAJAXURL.'search', 'search').'" method="post" id="searchposts" name="searchposts">';
	$out.= '<input type="hidden" class="sfhiddeninput" name="searchoption" id="searchoption" value="2" />';
	$out.= '<input type="hidden" class="sfhiddeninput" name="userid" id="userid" value="'.$spProfileUser->ID.'" />';
	if ($spProfileUser->ID == $spThisUser->ID) {
		$text1 = $labelYouPosted;
		$text2 = $labelYouStarted;
	} else {
		$text1 = $labelPosted;
		$text2 = $labelStarted;
	}
	$out.= '<input type="submit" class="spSubmit" name="membersearch" value="'.$text1.'" />';
	$out.= '<input type="submit" class="spSubmit" name="memberstarted" value="'.$text2.'" />';
	$out.= '</form>';
	$out.= "</div>\n";

	$out = apply_filters('sph_ProfileShowSearchPosts', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowUserPhotos()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#		Changelog:
#		5.7 - Rewritten to utilise Masonry for display
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowUserPhotos($args='') {
	global $spProfileUser, $spMobile;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spPhotoGrid',
				  'photoClass'	=> 'spPhotoItem',
				  'imageClass'	=> 'spPhotoImage',
				  'gutter'		=> 4,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowUserPhotos_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$imageClass		= esc_attr($imageClass);
	$photoClass		= esc_attr($photoClass);
	$gutter			= (int) $gutter;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->photos;

	if (!empty($spProfileUser->photos)) {
		$spProfile = sp_get_option('sfprofile');
		$numCols = $spProfile['photoscols'];
		$totalPhotos = count($spProfileUser->photos);
		if ($totalPhotos < $numCols) $numCols = $totalPhotos;
		$mobileComp = ($spMobile) ? 2 : 1;

		# Set up some stlye rules...
		?><style type="text/css">
		#spMainContainer .<?php echo($tagClass); ?> { width: 100%; }
		#spMainContainer .<?php echo($photoClass); ?> {
			float: left;
			width: <?php echo intval((100 / $numCols)-$mobileComp); ?>%;
			margin-bottom: <?php echo($gutter); ?>px;
		}
		</style><?php

		$out = '';
		$out.= "<div class='$tagClass'>";
		if (!empty($spProfileUser->photos)) {
			foreach ($spProfileUser->photos as $photo) {
				if (!empty($photo)) {
					$out.= "<div class='$photoClass'>";
					$out.= "<img class='$imageClass' src='".$photo."' />";
					$out.= "</div>";
				}
			}
		}
		$out.= '<div class="spClear"></div>';
		$out.= '</div>';

		$out = apply_filters('sph_ProfileShowUserPhotos', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}

# and the script to bring it together...
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
		jQuery('.<?php echo($tagClass); ?>').masonry({
		  itemSelector: '.<?php echo($photoClass); ?>',
		  gutter: <?php echo($gutter); ?>
		});
	});
</script>
<?php

}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowLink()
#	Display a users profile
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowLink($args='', $label='') {
	global $spProfileUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowLink',
				  'echo'		=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass	= esc_attr($tagClass);
	$label		= str_ireplace('%USER%', $spProfileUser->display_name, $label);
	$label		= sp_filter_title_display($label);
	$echo		= (int) $echo;

	# output the header
	$out = "<a rel='nofollow' class='$tagClass' href='".sp_url('profile/'.$spProfileUser->ID)."'>$label</span></a>\n";

	$out = apply_filters('sph_ProfileShowLink', $out, $spProfileUser, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowEmail()
#	Display a users profile
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowEmail($args='', $label='') {
	global $spProfileUser, $spThisUser;

	if (!sp_get_auth('view_profiles')) return;

	$defs = array('tagClass'	=> 'spProfileShowLink',
				  'leftClass'	=> 'spColumnSection spProfileLeftCol',
				  'middleClass' => 'spColumnSection spProfileSpacerCol',
				  'rightClass'	=> 'spColumnSection spProfileRightCol',
				  'adminOnly'	=> 1,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$leftClass		= esc_attr($leftClass);
	$middleClass	= esc_attr($middleClass);
	$rightClass		= esc_attr($rightClass);
	$adminOnly		= (int) $adminOnly; # this should really be bypass permission or let anyone view
	$label			= sp_filter_title_display($label);
	$echo			= (int) $echo;
	$get			= (int) $get;

	if ($get) return $spProfileUser->user_email;

	if (sp_get_auth('view_email') || !$adminOnly) {
		$out = '';
		$out.= "<div class='$leftClass'>";
		$out.= "<p class='$tagClass'>$label:</p>";
		$out.= '</div>';
		$out.= "<div class='$middleClass'></div>";
		$out.= "<div class='$rightClass'>";
		$out.= "<p class='$tagClass'>$spProfileUser->user_email</p>";
		$out.= "</div>\n";

		$out = apply_filters('sph_ProfileShowEmail', $out, $spProfileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SetupUserProfileData()
#	sets up global array spProfileUser with user profile data
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SetupUserProfileData($userid=0) {
	global $spProfileUser, $spVars, $spThisUser;

	if (empty($userid)) {
		if (!empty($spVars['member'])) {
			$userid = (int) $spVars['member'];
		} else {
			$userid = $spThisUser->ID;
		}
	}
	$spProfileUser = sp_get_user($userid);
	$spProfileUser = apply_filters('sph_profile_user_data', $spProfileUser);
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileEditFooter()
#	adds js to ProfileEdit view footer
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileEditFooter() {
	global $firstTab, $firstMenu, $spMobile;
?>
	<script type="text/javascript">
	var spfProfileFirst = true;
	jQuery(document).ready(function() {
		/* set up the profile tabs */
		jQuery("#spProfileTabs li a").click(function() {
			jQuery("#spProfileContent").html("<div><img src='<?php echo SFCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
			jQuery("#spProfileTabs li a").removeClass("current");
			jQuery(this).addClass("current");
			jQuery.ajax({async: <?php if (empty($firstMenu)) echo 'true'; else echo 'false'; ?>, url: this.href, success: function(html) {
				jQuery("#spProfileContent").html(html); }
			});
			return false;
		});

		<?php if (!empty($firstMenu)) { ?>
			jQuery('#spProfileTab-<?php echo $firstTab; ?>').click();
			jQuery("#spProfileMenu li a").off('click').click(function() {
				jQuery("#spProfileContent").html("<div><img src='<?php echo SFCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
				jQuery.ajax({async: false, url: this.href, success: function(html) {
					jQuery("#spProfileContent").html(html); }
				});
				return false;
			});

			jQuery('#spProfileMenu-<?php echo $firstMenu; ?>').click();

			jQuery("#spProfileMenu li a").off('click').click(function() {
				jQuery("#spProfileContent").html("<div><img src='<?php echo SFCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
				jQuery.ajax({async: true, url: this.href, success: function(html) {
					jQuery("#spProfileContent").html(html); }
				});
				return false;
			});
		<?php } else if (!empty($firstTab)) { ?>
			jQuery('#spProfileTab-<?php echo $firstTab; ?>').click();
		<?php } else { ?>
			<?php $tabs = sp_profile_get_tabs(); ?>
			jQuery('#spProfileTab-<?php echo $tabs[0]['slug']; ?>').click();
		<?php } ?>
	})
	</script>
<?php
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileEditFooterMobile()
#	adds js to ProfileEdit view footer for mobile devices
#	Scope:	site
#	Version: 5.3
#
# --------------------------------------------------------------------------------------
function sp_ProfileEditFooterMobile() {
	global $firstTab, $firstMenu;
?>
	<script type="text/javascript">
	var spfProfileFirst = true;
	jQuery(document).ready(function() {
		jQuery(function() {
			jQuery(".spProfileAccordionTab").tabs(
				".spProfileAccordionTab > div.spProfileAccordionPane", {
					tabs: '> h2',
					effect: 'slide',
					initialIndex: null,
					onClick: function(a, b) {
						var tabPanes = this.getPanes();
						var cPane = jQuery('#'+tabPanes[b].id);
						var cTop = cPane.offset();
						var t = (Math.round(cTop.top-29));
						window.scrollTo(0, t);
					}
				});
		});

		jQuery("#spProfileTab-<?php echo $firstTab; ?>").css("display", "block");
		jQuery("#spProfileTabTitle-<?php echo $firstTab; ?>").addClass("current");
		jQuery("#spProfileMenu-<?php echo $firstMenu; ?>").css("display", "block");
		jQuery("#spProfileMenuTitle-<?php echo $firstMenu; ?>").addClass("current");
	})
	</script>
<?php
}

# --------------------------------------------------------------------------------------
#
#		sp_SetupSigEditor()
#		figures out what editor is to be used for profile signature editor in ProfileEdit
#		Scope:	site
#		Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_SetupSigEditor($content='') {
		global $spGlobals;
		$out = '';
		$out.= do_action('sph_pre_editor_display', $spGlobals['editor']);
		$out.= apply_filters('sph_editor_textarea', $out, 'postitem', $content, $spGlobals['editor'], '');
		$out.= do_action('sph_post_editor_display', $spGlobals['editor']);
		return $out;
}
?>