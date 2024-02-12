<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
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
function sp_ProfileEdit($tabSlug = 'profile', $menuSlug = '') {
	if (!empty(SP()->rewrites->pageData['member'])) {
		$userid = SP()->rewrites->pageData['member'];
	} else {
		$userid = SP()->user->thisUser->ID;
	}

	if (empty($userid) || (SP()->user->thisUser->ID != $userid && !SP()->user->thisUser->admin)) {
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid profile request'));
		$out = SP()->notifications->render_queued();
		$out .= '<div class="spMessage">';
		$out .= apply_filters('sph_ProfileErrorMsg', SP()->primitives->front_text('Sorry, an invalid profile request was detected. Do you need to log in?'));
		$out .= '</div>';
		echo $out;

		return;
	}
	sp_SetupUserProfileData($userid);

	# display the profile tabs
	do_action('sph_profile_edit_before');

	# see if query args used to specify tab and/or menu
	if (isset($_GET['ptab'])) $tabSlug = SP()->filters->str($_GET['ptab']);
	if (isset($_GET['pmenu'])) $menuSlug = SP()->filters->str($_GET['pmenu']);

	$tabs = SP()->profile->get_tabs_menus();
	if (!empty($tabs)) {
		do_action('sph_profile_edit_before_tabs');
		echo '<ul id="spProfileTabs">';
		$first = true;
		$exist = false;
		foreach ($tabs as $tab) {
			# do we need an auth check?
			$authCheck = (empty($tab['auth'])) ? true : SP()->auths->get($tab['auth'], '', $userid);

			# is this tab being displayed and does user have auth to see it?
			if ($authCheck && $tab['display']) {
				if ($first) $firstDisplayTab = $tab['slug']; # remember first displayed tab as fallback
				if ($tab['slug'] == $tabSlug) $exist = true; # not if selected tab exists
				$class   = ($first) ? "class='current'" : '';
				$first   = false;
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
		$firstTab  = ($exist) ? $tabSlug : $firstDisplayTab; # if selected tab does not exist, use first tab
		$firstMenu = $menuSlug;

		# are we forcing password change on first login?
		if (isset(SP()->user->thisUser->sp_change_pw) && SP()->user->thisUser->sp_change_pw) {
			$firstTab  = 'profile';
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
function sp_ProfileEditMobile($tabSlug = 'profile', $menuSlug = 'overview') {
	if (!empty(SP()->rewrites->pageData['member'])) {
		$userid = (int) SP()->rewrites->pageData['member'];
	} else {
		$userid = SP()->user->thisUser->ID;
	}

	if (empty($userid) || (SP()->user->thisUser->ID != $userid && !SP()->user->thisUser->admin)) {
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid profile request'));
		$out = SP()->notifications->render_queued();
		$out .= '<div class="spMessage">';
		$out .= apply_filters('sph_ProfileErrorMsg', SP()->primitives->front_text('Sorry, an invalid profile request was detected. Do you need to log in?'));
		$out .= '</div>';
		echo $out;

		return;
	}

	# see if query args used to specify tab and/or menu
	if (isset($_GET['ptab'])) $tabSlug = SP()->filters->str($_GET['ptab']);
	if (isset($_GET['pmenu'])) $menuSlug = SP()->filters->str($_GET['pmenu']);

	# set up the profile data
	sp_SetupUserProfileData($userid);

	do_action('sph_profile_edit_before');
	do_action('sph_ProfileStart');

	$tabs = SP()->profile->get_tabs_menus();
	if (!empty($tabs)) {
		do_action('sph_profile_edit_before_tabs');

		echo '<div id="spProfileAccordion">';
		echo "<div class='spProfileAccordionTab'>\n";

		$firstTab     = $firstMenu = '';
		$tabSlugExist = $menuSlugExist = false;

		foreach ($tabs as $tab) {
			# do we need an auth check?
			$authCheck = (empty($tab['auth'])) ? true : SP()->auths->get($tab['auth'], '', $userid);

			# is this tab being displayed and does user have auth to see it?
			if ($authCheck && $tab['display']) {
				if ($tab['slug'] == $tabSlug) $tabSlugExist = true;
				if (empty($firstTab)) $firstTab = $tab['slug'];

				echo '<h2 id="spProfileTabTitle-'.esc_attr($tab['slug']).'">'.SP()->displayFilters->title($tab['name'])."</h2>\n";
				echo "<div id='spProfileTab-".esc_attr($tab['slug'])."' class='spProfileAccordionPane'>\n";

				if (!empty($tab['menus'])) {
					echo "<div class='spProfileAccordionTab'>\n";
					foreach ($tab['menus'] as $menu) {
						# do we need an auth check?
						$authCheck = (empty($menu['auth'])) ? true : SP()->auths->get($menu['auth'], '', $userid);

						# is this menu being displayed and does user have auth to see it?
						if ($authCheck && $menu['display']) {
							if ($menu['slug'] == $menuSlug) $menuSlugExist = true;
							if (empty($firstMenu)) $firstMenu = $menu['slug'];
							$thisSlug = $menu['slug']; # this variable is used in the form action url

							# special checking for displaying menus
							$spProfileOptions = SP()->options->get('sfprofile');
							$spAvatars        = SP()->options->get('sfavatars');
							$noPhotos         = ($menu['slug'] == 'edit-photos' && $spProfileOptions['photosmax'] < 1); # dont display edit photos if disabled
							$noAvatars        = ($menu['slug'] == 'edit-avatars' && !$spAvatars['sfshowavatars']); # dont display edit avatars if disabled
							$hideMenu         = ($noPhotos || $noAvatars);
							$hideMenu         = apply_filters('sph_ProfileMenuHide', $hideMenu, $tab, $menu, $userid);
							if (!$hideMenu) {
								echo '<h2 id="spProfileMenuTitle-'.esc_attr($menu['slug']).'">'.SP()->displayFilters->title($menu['name'])."</h2>\n";
								echo "<div id='spProfileMenu-".esc_attr($menu['slug'])."' class='spProfileAccordionPane'>\n";
								if (!empty($menu['form']) && file_exists($menu['form'])) {
									echo "<div class='spProfileAccordionForm'>\n";
									require_once $menu['form'];
									echo "</div>\n";
								} else {
									echo SP()->primitives->front_text('Profile form could not be found').': ['.$menu['name'].']<br />';
									echo SP()->primitives->front_text('You might try the forum - toolbox - housekeeping admin form and reset the profile tabs and menus and see if that helps');
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
		$firstTab  = ($tabSlugExist) ? $tabSlug : $firstTab; # if selected tab does not exist, use first tab
		$firstMenu = ($menuSlugExist) ? $menuSlug : $firstMenu; # if selected tab does not exist, use first menu in first tab

		# are we forcing password change on first login?
		if (isset(SP()->user->thisUser->sp_change_pw) && SP()->user->thisUser->sp_change_pw) {
			$firstTab  = 'profile';
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
function sp_ProfileShowHeader($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagId'        => 'spProfileShowHeader',
	              'tagClass'     => 'spProfileShowHeader',
	              'editClass'    => 'spProfileShowHeaderEdit',
	              'onlineStatus' => 1,
	              'statusClass'  => 'spOnlineStatus',
	              'onlineIcon'   => 'sp_UserOnline.png',
	              'offlineIcon'  => 'sp_UserOffline.png',
	              'echo'         => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ProfileShowHeader_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$editClass    = esc_attr($editClass);
	$statusClass  = esc_attr($statusClass);
	$onlineStatus = (int) $onlineStatus;
	$label        = str_ireplace('%USER%', SP()->user->profileUser->display_name, $label);
	$label        = SP()->displayFilters->title($label);
	$echo         = (int) $echo;

	# output the header
	$adminEdit = '';
	$out       = "<div id='$tagId' class='$tagClass'>$label$adminEdit";
	if (SP()->user->thisUser->admin) {
		$out .= '<a href="'.SP()->spPermalinks->get_url('profile/'.SP()->user->profileUser->ID.'/edit').'">';
		$out .= " <span class='$editClass'>(".SP()->primitives->front_text('Edit User Profile').')</span>';
		if ($onlineStatus) $out .= sp_OnlineStatus("tagClass=$statusClass&onlineIcon=$onlineIcon&offlineIcon=$offlineIcon&echo=0", SP()->user->profileUser->ID, SP()->user->profileUser);
		$out .= '</a>';
	}
	$out .= "</div>\n";

	$out = apply_filters('sph_ProfileShowHeader', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowDisplayName($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowDisplayName',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ProfileShowDisplayName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->display_name;

	# output display name
	$out = '';
	$out .= "<div class='$leftClass'>";
	$out .= "<p class='$tagClass'>$label:</p>";
	$out .= '</div>';
	$out .= "<div class='$middleClass'></div>";
	$out .= "<div class='$rightClass'>";
	$out .= "<p class='$tagClass'>";
	$out .= SP()->user->profileUser->display_name;
	$out .= "</p>";
	$out .= "</div>\n";

	$out = apply_filters('sph_ProfileShowDisplayName', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowFirstName($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

    // If option to hide firstname and lastname is set, do not return any data
    $profileOptions = SP()->options->get('sfprofile');
    if ($profileOptions['hideuserinfo']) {
        return;
    }

	$defs = array('tagClass'    => 'spProfileShowFirstName',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowFirstName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->first_name;

	# output first name
	if (!empty(SP()->user->profileUser->first_name) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$name = (empty(SP()->user->profileUser->first_name)) ? '&nbsp;' : SP()->user->profileUser->first_name;
		$out .= "<p class='$tagClass'>$name</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowFirstName', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowLastName($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

    // If option to hide firstname and lastname is set, do not return any data
    $profileOptions = SP()->options->get('sfprofile');
    if ($profileOptions['hideuserinfo']) {
        return;
    }

	$defs = array('tagClass'    => 'spProfileShowLastName',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLastName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->last_name;

	# output first name
	if (!empty(SP()->user->profileUser->last_name) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$name = (empty(SP()->user->profileUser->last_name)) ? '&nbsp;' : SP()->user->profileUser->last_name;
		$out .= "<p class='$tagClass'>$name</p>";
		$out .= "</div>\n";
		$out = apply_filters('sph_ProfileShowLastName', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowWebsite($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowWebsite',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowWebsite_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->user_url;

	# output first name
	if (!empty(SP()->user->profileUser->user_url) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		if (empty(SP()->user->profileUser->user_url)) {
			$url = '&nbsp;';
		} else {
			$url       = SP()->displayFilters->links(SP()->user->profileUser->user_url);
			$spFilters = SP()->options->get('sffilters');
			if ($spFilters['sfnofollow']) $url = SP()->saveFilters->nofollow($url);
			if ($spFilters['sftarget']) $url = SP()->saveFilters->target($url);
		}
		$out .= "<p class='$tagClass'>$url</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowWebsite', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowLocation($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowWebsite',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLocation_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->location;

	# output first name
	if (!empty(SP()->user->profileUser->location) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$location = (empty(SP()->user->profileUser->location)) ? '&nbsp;' : SP()->user->profileUser->location;
		$out .= "<p class='$tagClass'>$location</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowLocation', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowBio($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowBio',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowBio_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->description;

	# output first name
	if (!empty(SP()->user->profileUser->description) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$description = (empty(SP()->user->profileUser->description)) ? '&nbsp;' : SP()->user->profileUser->description;
		$out .= "<div class='$tagClass'>$description</div>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowBio', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowAIM($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;
	
	# Set variable to control whether we should display deprecated identities.  Check it and exit if we're not allowed to show deprecated identities
	$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;	
	if (false == $display_deprecated_identities) return ;

	$defs = array('tagClass'    => 'spProfileShowAIM',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowAIM_args', $a);
	extract($a, EXTR_SKIP);
	
	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->aim;

	# output first name
	if (!empty(SP()->user->profileUser->aim) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$aim = (empty(SP()->user->profileUser->aim)) ? '&nbsp;' : SP()->user->profileUser->aim;
		$out .= "<p class='$tagClass'>$aim</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowAIM', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowYIM($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	# Set variable to control whether we should display deprecated identities.  Check it and exit if we're not allowed to show deprecated identities
	$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;	
	if (false == $display_deprecated_identities) return ;
	
	$defs = array('tagClass'    => 'spProfileShowYIM',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowAIM_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->yim;

	# output first name
	if (!empty(SP()->user->profileUser->yim) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$yim = (empty(SP()->user->profileUser->yim)) ? '&nbsp;' : SP()->user->profileUser->yim;
		$out .= "<p class='$tagClass'>$yim</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowYIM', $out, SP()->user->profileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowICQ()
#	Display a users location
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowICQ($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;
	
	# Set variable to control whether we should display deprecated identities.  Check it and exit if we're not allowed to show deprecated identities
	$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;	
	if (false == $display_deprecated_identities) return ;

	$defs = array('tagClass'    => 'spProfileShowICQ',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowICQ_args', $a);
	extract($a, EXTR_SKIP);
	
	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->icq;

	# output first name
	if (!empty(SP()->user->profileUser->icq) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$icq = (empty(SP()->user->profileUser->icq)) ? '&nbsp;' : SP()->user->profileUser->icq;
		$out .= "<p class='$tagClass'>$icq</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowICQ', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowGoogleTalk($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	# Set variable to control whether we should display deprecated identities.  Check it and exit if we're not allowed to show deprecated identities
	$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;	
	if (false == $display_deprecated_identities) return ;
	
	$defs = array('tagClass'    => 'spProfileShowGoogleTalk',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowGoogleTalk_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->jabber;

	# output first name
	if (!empty(SP()->user->profileUser->jabber) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$jabber = (empty(SP()->user->profileUser->jabber)) ? '&nbsp;' : SP()->user->profileUser->jabber;
		$out .= "<p class='$tagClass'>$jabber</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowGoogleTalk', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowMSN($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	# Set variable to control whether we should display deprecated identities.  Check it and exit if we're not allowed to show deprecated identities
	$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;	
	if (false == $display_deprecated_identities) return ;
	
	$defs = array('tagClass'    => 'spProfileShowMSN',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowMSN_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->msn;

	# output first name
	if (!empty(SP()->user->profileUser->msn) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$msn = (empty(SP()->user->profileUser->msn)) ? '&nbsp;' : SP()->user->profileUser->msn;
		$out .= "<p class='$tagClass'>$msn</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowMSN', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowMySpace($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;
	
	# Set variable to control whether we should display deprecated identities.  Check it and exit if we're not allowed to show deprecated identities
	$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;	
	if (false == $display_deprecated_identities) return ;

	$defs = array('tagClass'    => 'spProfileShowMySpace',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowMySpace_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->myspace;

	# output first name
	if (!empty(SP()->user->profileUser->myspace) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$myspace = (empty(SP()->user->profileUser->myspace)) ? '&nbsp;' : SP()->user->profileUser->myspace;
		$out .= "<p class='$tagClass'>$myspace</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowMySpace', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowSkype($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowSkype',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowSkype_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->skype;

	# output first name
	if (!empty(SP()->user->profileUser->skype) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$skype = (empty(SP()->user->profileUser->skype)) ? '&nbsp;' : SP()->user->profileUser->skype;
		$out .= "<p class='$tagClass'>$skype</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowSkype', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowFacebook($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowFacebook',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowFacebook_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->facebook;

	# output first name
	if (!empty(SP()->user->profileUser->facebook) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$facebook = (empty(SP()->user->profileUser->facebook)) ? '&nbsp;' : SP()->user->profileUser->facebook;
		$out .= "<p class='$tagClass'>$facebook</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowFacebook', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowTwitter($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowTwitter',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowTwitter_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->twitter;

	# output first name
	if (!empty(SP()->user->profileUser->twitter) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$twitter = (empty(SP()->user->profileUser->twitter)) ? '&nbsp;' : SP()->user->profileUser->twitter;
		$out .= "<p class='$tagClass'>$twitter</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowTwitter', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowLinkedIn($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowLinkedIn',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLinkedIn_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->linkedin;

	# output first name
	if (!empty(SP()->user->profileUser->linkedin) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$linkedin = (empty(SP()->user->profileUser->linkedin)) ? '&nbsp;' : SP()->user->profileUser->linkedin;
		$out .= "<p class='$tagClass'>$linkedin</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowLinkedIn', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowYouTube($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowYouTube',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowYouTube_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->youtube;

	# output first name
	if (!empty(SP()->user->profileUser->youtube) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$youtube = (empty(SP()->user->profileUser->youtube)) ? '&nbsp;' : SP()->user->profileUser->youtube;
		$out .= "<p class='$tagClass'>$youtube</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowYouTube', $out, SP()->user->profileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileShowInstagram()
#	Display a users instagram account
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowInstagram($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowInstagram',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'showEmpty'   => 0,
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowInstagram_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$showEmpty   = (int) $showEmpty;
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->instagram;

	# output first name
	if (!empty(SP()->user->profileUser->instagram) || $showEmpty) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$instagram = (empty(SP()->user->profileUser->instagram)) ? '&nbsp;' : SP()->user->profileUser->instagram;
		$out .= "<p class='$tagClass'>$instagram</p>";
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowInstagram', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowMemberSince($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowMemberSince',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowMemberSince_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->user_registered;

	# output first name
	$out = '';
	$out .= "<div class='$leftClass'>";
	$out .= "<p class='$tagClass'>$label:</p>";
	$out .= '</div>';
	$out .= "<div class='$middleClass'></div>";
	$out .= "<div class='$rightClass'>";
	$out .= "<p class='$tagClass'>".SP()->dateTime->format_date('d', SP()->user->profileUser->user_registered).'</p>';
	$out .= "</div>\n";

	$out = apply_filters('sph_ProfileShowMemberSince', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowLastVisit($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowLastVisit',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowLastVisit_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->lastvisit;

	# output first name
	$out = '';
	$out .= "<div class='$leftClass'>";
	$out .= "<p class='$tagClass'>$label:</p>";
	$out .= '</div>';
	$out .= "<div class='$middleClass'></div>";
	$out .= "<div class='$rightClass'>";
	$out .= "<p class='$tagClass'>".SP()->dateTime->format_date('d', SP()->user->profileUser->lastvisit).' '.SP()->dateTime->format_date('t', SP()->user->profileUser->lastvisit).'</p>';
	$out .= "</div>\n";

	$out = apply_filters('sph_ProfileShowLastVisit', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowUserPosts($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileShowUserPosts',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'echo'        => 1,
	              'get'         => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowUserPosts_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	$count = max(SP()->user->profileUser->posts, 0);

	if ($get) return $count;

	# output first name
	$out = '';
	$out .= "<div class='$leftClass'>";
	$out .= "<p class='$tagClass'>$label:</p>";
	$out .= '</div>';
	$out .= "<div class='$middleClass'></div>";
	$out .= "<div class='$rightClass'>";
	$out .= "<p class='$tagClass'>".$count.'</p>';
	$out .= "</div>\n";

	$out = apply_filters('sph_ProfileShowUserPosts', $out, SP()->user->profileUser, $a);

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
#		Added arguments $labelYouStarted, $labelYouPosted
#	Version 6.5.0
#		Added argument $stackButtons - used on mobile themes to insert a line break between the two links/buttons.
#
# --------------------------------------------------------------------------------------
function sp_ProfileShowSearchPosts($args = '', $label = '', $labelStarted = '', $labelPosted = '', $labelYouStarted = '', $labelYouPosted = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'    => 'spProfileSearchPosts',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'linkClass'   => 'spButton spLeft',
				  'stackButtons'=> 0,
	              'echo'        => 1,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowSearchPosts_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$linkClass   = esc_attr($linkClass);
	$label       = SP()->displayFilters->title($label);
	$stackButtons= (int) $stackButtons;
	$echo        = (int) $echo;

	($stackButtons ? $stackBreak = '<br />' : $stackBreak = ' ');	
	
	if (SP()->user->profileUser->ID == SP()->user->thisUser->ID) {
		if (empty($labelYouStarted)) $labelYouStarted = SP()->primitives->front_text('List Topics You Started');
		if (empty($labelYouPosted)) $labelYouPosted = SP()->primitives->front_text('List Topics You Have Posted To');
		$labelYouStarted = SP()->displayFilters->title($labelYouStarted);
		$labelYouPosted  = SP()->displayFilters->title($labelYouPosted);
	} else {
		if (!empty($labelStarted)) {
			$labelStarted = str_replace('%USERNAME%', SP()->user->profileUser->display_name, $labelStarted);
		} else {
			$labelStarted = sprintf(SP()->primitives->front_text('List Topics %1$s Has Started'), SP()->user->profileUser->display_name);
		}
		if (!empty($labelPosted)) {
			$labelPosted = str_replace('%USERNAME%', SP()->user->profileUser->display_name, $labelPosted);
		} else {
			$labelPosted = sprintf(SP()->primitives->front_text('List Topics %1$s Has Posted To'), SP()->user->profileUser->display_name);
		}
		$labelStarted = SP()->displayFilters->title($labelStarted);
		$labelPosted  = SP()->displayFilters->title($labelPosted);
	}

	# output first name
	$out = '';
	$out .= "<div class='$leftClass'>";
	if (!empty($label)) {
		$out .= "<p class='$tagClass'>$label:</p>";
	}
	$out .= '</div>';
	$out .= "<div class='$middleClass'></div>";
	$out .= "<div class='$rightClass'>";
	$out .= '<form action="'.wp_nonce_url(SPAJAXURL.'search', 'search').'" method="post" id="searchposts" name="searchposts">';
	$out .= '<input type="hidden" class="sfhiddeninput" name="searchoption" id="searchoption" value="2" />';
	$out .= '<input type="hidden" class="sfhiddeninput" name="userid" id="userid" value="'.SP()->user->profileUser->ID.'" />';
	if (SP()->user->profileUser->ID == SP()->user->thisUser->ID) {
		$text1 = $labelYouPosted;
		$text2 = $labelYouStarted;
	} else {
		$text1 = $labelPosted;
		$text2 = $labelStarted;
	}
	$out .= $stackBreak . '<input type="submit" class="spSubmit sf-button-primary" name="membersearch" value="'.$text1.'" />' . $stackBreak;
	$out .= '<input type="submit" class="spSubmit sf-button-primary" name="memberstarted" value="'.$text2.'" />';
	$out .= '</form>';
	$out .= "</div>\n";

	$out = apply_filters('sph_ProfileShowSearchPosts', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowUserPhotos($args = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass'   => 'spPhotoGrid',
	              'photoClass' => 'spPhotoItem',
	              'imageClass' => 'spPhotoImage',
	              'gutter'     => 4,
	              'echo'       => 1,
	              'get'        => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ProfileShowUserPhotos_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass   = esc_attr($tagClass);
	$imageClass = esc_attr($imageClass);
	$photoClass = esc_attr($photoClass);
	$gutter     = (int) $gutter;
	$echo       = (int) $echo;
	$get        = (int) $get;

	if ($get) return SP()->user->profileUser->photos;

	if (!empty(SP()->user->profileUser->photos)) {
		$spProfile   = SP()->options->get('sfprofile');
		$numCols     = $spProfile['photoscols'];
		$totalPhotos = count(SP()->user->profileUser->photos);
		if ($totalPhotos < $numCols) $numCols = $totalPhotos;
		$mobileComp = (SP()->core->mobile) ? 2 : 1;

		# Set up some stlye rules...
		?>
        <style>
        #spMainContainer .<?php echo($tagClass); ?> {
            width: 100%;
        }

        #spMainContainer .<?php echo($photoClass); ?> {
            float: left;
            width: <?php echo intval((100 / $numCols)-$mobileComp); ?>%;
            margin-bottom: <?php echo($gutter); ?>px;
        }
        </style><?php

		$out = '';
		$out .= "<div class='$tagClass'>";
		if (!empty(SP()->user->profileUser->photos)) {
			foreach (SP()->user->profileUser->photos as $photo) {
				if (!empty($photo)) {
					$out .= "<div class='$photoClass'>";
					$out .= "<img class='$imageClass' src='".$photo."' />";
					$out .= "</div>";
				}
			}
		}
		$out .= '<div class="spClear"></div>';
		$out .= '</div>';

		$out = apply_filters('sph_ProfileShowUserPhotos', $out, SP()->user->profileUser, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}

	# and the script to bring it together...
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				$('.<?php echo($tagClass); ?>').masonry({
					itemSelector: '.<?php echo($photoClass); ?>',
					gutter: <?php echo($gutter); ?>
				});
			});
		}(window.spj = window.spj || {}, jQuery));
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
function sp_ProfileShowLink($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

	$defs = array('tagClass' => 'spProfileShowLink',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ProfileShowLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	$label    = str_ireplace('%USER%', SP()->user->profileUser->display_name, $label);
	$label    = SP()->displayFilters->title($label);
	$echo     = (int) $echo;

	# output the header
	$out = "<a rel='nofollow' class='$tagClass' href='".SP()->spPermalinks->get_url('profile/'.SP()->user->profileUser->ID)."'>$label</span></a>\n";

	$out = apply_filters('sph_ProfileShowLink', $out, SP()->user->profileUser, $a);

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
function sp_ProfileShowEmail($args = '', $label = '') {
	if (!SP()->auths->get('view_profiles')) return;

    // If option to hide firstname and lastname is set, do not return any data
    $profileOptions = SP()->options->get('sfprofile');
    if ($profileOptions['hideuserinfo']) {
        return;
    }

    $defs = array('tagClass'    => 'spProfileShowLink',
	              'leftClass'   => 'spColumnSection spProfileLeftCol',
	              'middleClass' => 'spColumnSection spProfileSpacerCol',
	              'rightClass'  => 'spColumnSection spProfileRightCol',
	              'adminOnly'   => 1,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ProfileShowLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$leftClass   = esc_attr($leftClass);
	$middleClass = esc_attr($middleClass);
	$rightClass  = esc_attr($rightClass);
	$adminOnly   = (int) $adminOnly; # this should really be bypass permission or let anyone view
	$label       = SP()->displayFilters->title($label);
	$echo        = (int) $echo;
	$get         = (int) $get;

	if ($get) return SP()->user->profileUser->user_email;

	if (SP()->auths->get('view_email') || !$adminOnly) {
		$out = '';
		$out .= "<div class='$leftClass'>";
		$out .= "<p class='$tagClass'>$label:</p>";
		$out .= '</div>';
		$out .= "<div class='$middleClass'></div>";
		$out .= "<div class='$rightClass'>";
		$out .= "<p class='$tagClass'>".SP()->user->profileUser->user_email.'</p>';
		$out .= "</div>\n";

		$out = apply_filters('sph_ProfileShowEmail', $out, SP()->user->profileUser, $a);

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
function sp_SetupUserProfileData($userid = 0) {
	if (empty($userid)) {
		if (!empty(SP()->rewrites->pageData['member'])) {
			$userid = (int) SP()->rewrites->pageData['member'];
		} else {
			$userid = SP()->user->thisUser->ID;
		}
	}
	SP()->user->profileUser = SP()->user->get($userid);
	SP()->user->profileUser = apply_filters('sph_profile_user_data', SP()->user->profileUser);
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
	global $firstTab, $firstMenu;
	?>
    <script>
		(function(spj, $, undefined) {
			var spfProfileFirst = true;
			$(document).ready(function () {
				/* set up the profile tabs */
				$("#spProfileTabs li a").click(function () {
					$("#spProfileContent").html("<div><img src='<?php echo SPCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
					$("#spProfileTabs li a").removeClass("current");
					$(this).addClass("current");
					$.ajax({
						async: <?php if (empty($firstMenu)) echo 'true'; else echo 'false'; ?>,
						url: this.href,
						success: function (html) {
							$("#spProfileContent").html(html);
						}
					});
					return false;
				});

				<?php if (!empty($firstMenu)) { ?>
				$('#spProfileTab-<?php echo $firstTab; ?>').click();
				$("#spProfileMenu li a").off('click').click(function () {
					$("#spProfileContent").html("<div><img src='<?php echo SPCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
					$.ajax({
						async: false, url: this.href, success: function (html) {
							$("#spProfileContent").html(html);
						}
					});
					return false;
				});

				$('#spProfileMenu-<?php echo $firstMenu; ?>').click();

				$("#spProfileMenu li a").off('click').click(function () {
					$("#spProfileContent").html("<div><img src='<?php echo SPCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
					$.ajax({
						async: true, url: this.href, success: function (html) {
							$("#spProfileContent").html(html);
						}
					});
					return false;
				});
				<?php } else if (!empty($firstTab)) { ?>
				$('#spProfileTab-<?php echo $firstTab; ?>').click();
				<?php } else { ?>
				<?php $tabs = SP()->profile->get_tabs_menus(); ?>
				$('#spProfileTab-<?php echo $tabs[0]['slug']; ?>').click();
				<?php } ?>
			})
		}(window.spj = window.spj || {}, jQuery));
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
    <script>
		(function(spj, $, undefined) {
			var spfProfileFirst = true;
			$(document).ready(function () {
				$(function () {
					$(".spProfileAccordionTab").tabs(
						".spProfileAccordionTab > div.spProfileAccordionPane", {
							tabs: '> h2',
							effect: 'slide',
							initialIndex: null,
							onClick: function (a, b) {
								var tabPanes = this.getPanes();
								var cPane = $('#' + tabPanes[b].id);
								var cTop = cPane.offset();
								var t = (Math.round(cTop.top - 29));
								window.scrollTo(0, t);
							}
						});
				});

				$("#spProfileTab-<?php echo $firstTab; ?>").css("display", "block");
				$("#spProfileTabTitle-<?php echo $firstTab; ?>").addClass("current");
				$("#spProfileMenu-<?php echo $firstMenu; ?>").css("display", "block");
				$("#spProfileMenuTitle-<?php echo $firstMenu; ?>").addClass("current");
			})
		}(window.spj = window.spj || {}, jQuery));
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
function sp_SetupSigEditor($content = '') {
	$out = '';
	$out .= do_action('sph_pre_editor_display', SP()->core->forumData['editor']);
	$out .= apply_filters('sph_editor_textarea', $out, 'postitem', $content, SP()->core->forumData['editor'], '');
	$out .= do_action('sph_post_editor_display', SP()->core->forumData['editor']);

	return $out;
}
