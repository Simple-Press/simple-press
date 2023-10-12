<?php

/*
  Simple:Press
  Template Function Handler
  $LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
  $Rev: 15799 $
 */

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	sp_UserNewPostFlag()
#	Display a users new post flag
#	Scope:	Specifically group/forum/topic/list views
#	Version: 5.7.2
#
# --------------------------------------------------------------------------------------

function sp_UserNewPostFlag($args = '', $view = '') {
	if (!SP()->user->thisUser->member || empty($view)) return;

	$defs = array('locationClass' => 'spLeft');

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sp_UserNewPostFlag_args', $a);
	extract($a, EXTR_SKIP);
	$locationClass = esc_attr($locationClass);

	$sfc = SP()->options->get('sfcontrols');

	$flagstext = ($sfc['flagsuse']) ? $sfc['flagstext'] : SP()->primitives->front_text('new');
	$tagClass  = 'spNewFlag '.$locationClass;

	$out = '';

	switch ($view) {

		case 'group':
			if (!$sfc['flagsuse']) return;
			if (!SP()->forum->view->thisForum->unread) return;
			if (SP()->core->device == 'desktop') {
				$out .= sp_UnreadPostsLink("tagId=spUnreadPostsInfo&tagClass=$tagClass&popup=1&echo=0&first=1&id=".SP()->forum->view->thisForum->forum_id."&targetaction=forum", $flagstext, SP()->primitives->front_text('View listing of topics with new posts'), SP()->forum->view->thisForum->forum_name);
			} else {
				$out .= sp_UnreadPostsLink("tagId=spUnreadPostsInfo&tagClass=$tagClass&popup=0&echo=0&first=1&id=".SP()->forum->view->thisForum->forum_id."&targetaction=forum", $flagstext, SP()->primitives->front_text('View listing of topics with new posts'), SP()->forum->view->thisForum->forum_name);
			}
			break;

		case 'forum':
			if (!$sfc['flagsuse']) return;
			if (!SP()->forum->view->thisTopic->unread) return;
			$idx = array_search(SP()->forum->view->thisTopic->topic_id, SP()->user->thisUser->newposts['topics']);
			if ($idx !== false) {
				$out .= "<a class='$tagClass' href='".SP()->spPermalinks->build_url(SP()->forum->view->thisForum->forum_slug, SP()->forum->view->thisTopic->topic_slug, 0, SP()->user->thisUser->newposts['post'][$idx], 0)."'>".$flagstext."</a>";
			}
			break;

		case 'topic':
			if (!SP()->forum->view->thisPost->new_post) return;
			$out .= sp_PostIndexNewPost("tagClass=$tagClass&echo=0", $flagstext);
			break;

		case 'list':
			if (!isset(SP()->forum->view->thisListTopic->new_post_count) || SP()->forum->view->thisListTopic->new_post_count == 0) return;
			if (SP()->core->device == 'desktop') {
				$out .= sp_ListNewPostButton("tagClass=$tagClass&icon=&echo=0", $flagstext.': %COUNT%', SP()->primitives->front_text('View the first new post in this topic'));
			} else {
				$out .= sp_ListNewPostButton("tagClass=$tagClass&icon=&echo=0", $flagstext.': %COUNT%', '');
			}
			break;
	}
	$out = apply_filters('sp_UserNewPostFlag', $out, $a);

	echo $out;
}

# --------------------------------------------------------------------------------------
#
#	sp_UserAvatar()
#	Display a users avatar
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_UserAvatar($args = '', $contextData = '') {
	$defs = array('tagClass' => 'spAvatar',
	              'imgClass' => 'spAvatar',
	              'size'     => '',
	              'link'     => 'profile',
	              'context'  => 'current',
	              'wp'       => '',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_Avatar_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	$imgClass = esc_attr($imgClass);
	$link     = esc_attr($link);
	$size     = (int) $size;
	$echo     = (int) $echo;
	$get      = (int) $get;
	$wp       = esc_attr($wp);

	# init some vars
	$forceWidth = false;

	# make sure we are displaying avatars
	$spAvatars = SP()->options->get('sfavatars');
	if ($spAvatars['sfshowavatars'] == true) {
		$avatarData            = new stdClass();
		$avatarData->object    = false;
		$avatarData->userId    = 0; # need user id OR email
		$avatarData->email     = '';
		$avatarData->avatar    = '';
		$avatarData->admin     = '';
		$avatarData->moderator = '';

		# determine avatar size
		$avatarData->size = (!empty($size)) ? $size : $spAvatars['sfavatarsize'];

		# get the appropriate user id and email address
		switch ($context) {
			case 'current':
				# we want the avatar for the current user
				$avatarData->userId = SP()->user->thisUser->ID;
				$avatarData->email  = (!empty($avatarData->userId)) ? SP()->user->thisUser->user_email : '';
				if (isset(SP()->user->thisUser->avatar)) $avatarData->avatar = SP()->user->thisUser->avatar;
				break;

			case 'user':
				# determine if we have user object, id or email address
				if (is_object($contextData)) {
					# sp user object passed in
					# can contain anything, but must contain id or email, avatar array and admin flag
					$avatarData->object    = true;
					$avatarData->userId    = $contextData->ID;
					$avatarData->avatar    = $contextData->avatar;
					$avatarData->admin     = $contextData->admin;
					$avatarData->moderator = SP()->auths->forum_mod($contextData->ID);

					# get email address handling sp user objects with type of guest
					if ($contextData instanceof spcUser && $contextData->guest) {
						$avatarData->email = $contextData->guest_email;
					} else {
						$avatarData->email = $contextData->user_email;
					}
				} else {
					if (is_numeric($contextData)) {
						# user id passed in
						$user = get_userdata((int) $contextData);
					} else {
						# email address passed in
						$user = get_user_by('email', SP()->filters->str($contextData));
					}
					if ($user) {
						$avatarData->userId = $user->ID;
						$avatarData->email  = $user->user_email;
						$avatarData->avatar = SP()->memberData->get($user->ID, 'avatar');
					}
				}
				break;

			default:
				# allow themes/plugins to add new avatar user types
				$avatarData = apply_filters('sph_Avatar_'.$context, $avatarData, $a);
				break;
		}

		# loop through priorities until we find an avatar to use
		foreach ($spAvatars['sfavatarpriority'] as $priority) {
			switch ($priority) {
				case 0: # Gravatars
					if (function_exists('sp_get_gravatar_cache_url')) {
						$avatarData->url = sp_get_gravatar_cache_url(strtolower($avatarData->email), $avatarData->size, $avatarData->userId, $avatarData->avatar);
						if (empty($avatarData->url)) {
							$gravatar = false;
						} else {
							$gravatar   = true;
							$forceWidth = true; # force width to request since we only cache one size
						}
					} else {
						$rating = $spAvatars['sfgmaxrating'];
						switch ($rating) {
							case 1:
								$grating = 'g';
								break;
							case 2:
								$grating = 'pg';
								break;
							case 3:
								$grating = 'r';
								break;
							case 4:
							default:
								$grating = 'x';
								break;
						}

						$avatarData->url = 'https://www.gravatar.com/avatar/'.md5(strtolower($avatarData->email))."?d=404&size=$avatarData->size&rating=$grating";

						# Is there an gravatar?
						$headers  = wp_get_http_headers($avatarData->url);
						$gravatar = (!empty($headers['Content-Disposition']));
					}

					# ignore gravatar blank images
					if ($gravatar == true) {
						break 2; # if actual gravatar image found, show it
					}
					break;

				case 1: # WP avatars
					# if wp avatars being used, handle slightly different since we get image tags
					$avatar = "<div class='$tagClass'>";
					if (!empty($wp)) {
						$avatar .= sp_build_avatar_display($avatarData->userId, $wp, $link);
					} else {
						if ($avatarData->userId) $avatarData->email = $avatarData->userId;
						$avatar .= sp_build_avatar_display($avatarData->userId, get_avatar($avatarData->email, $avatarData->size), $link);
					}
					$avatar .= '</div>';

					if ($get) return $avatarData;

					# for wp avatars, we need to display/return and bail
					if (empty($echo)) {
						return $avatar;
					} else {
						echo $avatar."";

						return;
					}

				case 2: # Uploaded avatars
					$userAvatar = $avatarData->avatar;
					if (empty($userAvatar) && !empty($avatarData->userId)) {
						$userAvatar = (isset(SP()->user->thisUser) && $avatarData->userId == SP()->user->thisUser->ID) ? SP()->user->thisUser->avatar : SP()->memberData->get($avatarData->userId, 'avatar');
					}

					if (!empty($userAvatar['uploaded'])) {
						$avfile          = $userAvatar['uploaded'];
						$avatarData->url = SPAVATARURL.$avfile;
						if (file_exists(SPAVATARDIR.$avfile)) {
							$avatarData->path = SPAVATARDIR.$avfile;
							break 2; # if uploaded avatar exists, show it
						}
					}
					break;

				case 3: # SP default avatars
				default:
					if (empty($avatarData->userId)) {
						$image = SP()->core->forumData['defAvatars']['guest'];
					} else {
						if ($avatarData->object) {
							if ($avatarData->admin) {
								$image = SP()->core->forumData['defAvatars']['admin'];
							} elseif ($avatarData->moderator) {
								$image = SP()->core->forumData['defAvatars']['mod'];
							} else {
								$image = SP()->core->forumData['defAvatars']['member'];
							}
						} else {
							if (SP()->auths->forum_admin($avatarData->userId)) {
								$image = SP()->core->forumData['defAvatars']['admin'];
							} elseif (SP()->auths->forum_mod($avatarData->userId)) {
								$image = SP()->core->forumData['defAvatars']['mod'];
							} else {
								$image = SP()->core->forumData['defAvatars']['member'];
							}
						}
					}
					$avatarData->url  = SPAVATARURL.'defaults/'.$image;
					$avatarData->path = SPAVATARDIR.'defaults/'.$image;
					break 2; # defaults, so show it

				case 4: # Pool avatars
					$userAvatar = $avatarData->avatar;
					if (empty($userAvatar) && !empty($avatarData->userId) && isset(SP()->user->thisUser)) {
						$userAvatar = ($avatarData->userId == SP()->user->thisUser->ID) ? SP()->user->thisUser->avatar : SP()->memberData->get($avatarData->userId, 'avatar');
					}

					if (!empty($userAvatar['pool'])) {
						$pavfile         = $userAvatar['pool'];
						$avatarData->url = SPAVATARPOOLURL.$pavfile;
						if (file_exists(SPAVATARPOOLDIR.$pavfile)) {
							$avatarData->path = SPAVATARPOOLDIR.$pavfile;
							break 2; # if pool avatar exists, show it
						}
					}
					break;

				case 5: # Remote avatars
					$userAvatar = $avatarData->avatar;
					if (empty($userAvatar) && !empty($avatarData->userId) && isset(SP()->user->thisUser)) {
						$userAvatar = ($avatarData->userId == SP()->user->thisUser->ID) ? SP()->user->thisUser->avatar : SP()->memberData->get($avatarData->userId, 'avatar');
					}

					if (!empty($userAvatar['remote'])) {
						$ravfile         = $userAvatar['remote'];
						$avatarData->url = $ravfile;
						# see if file exists
						$response = wp_remote_get($avatarData->url);
						if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
							$avatarData->path = $avatarData->url;
							break 2; # if remote avatar exists, show it
						}
					}
					break;
			}
		}

		# allow themes/plugins to filter the final avatar data
		$avatarData = apply_filters('sph_Avatar', $avatarData, $a);

		if ($get) return $avatarData;

		# now display the avatar
		$width    = ($forceWidth) ? " width='$avatarData->size'" : "";
		$maxwidth = ($avatarData->size > 0) ? " style='max-width: {$avatarData->size}px'" : '';

		$avatar = sp_build_avatar_display($avatarData->userId, "<img src='".esc_url($avatarData->url)."' class='$imgClass'$width$maxwidth alt='".SP()->primitives->front_text('Avatar')."' />", $link);

		$avatar = "<div class='$tagClass'>$avatar</div>";

		if ($echo) {
			echo $avatar;
		} else {
			return $avatar;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UserForumRank()
#	Display a users forum ranks
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_UserForumRank($args = '', $ranks = null) {
	$defs = array('titleClass' => 'spForumRank',
	              'badgeClass' => 'spForumRank',
	              'showTitle'  => 1,
	              'showBadge'  => 1,
	              'echo'       => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$titleClass = esc_attr($titleClass);
	$badgeClass = esc_attr($badgeClass);
	$showTitle  = (int) $showTitle;
	$showBadge  = (int) $showBadge;
	$echo       = (int) $echo;

	if (!$showTitle && !$showBadge) return;

	# the forum rank and title based on specified options
	$out = '';
	if (!empty($ranks)) {
		foreach ($ranks as $rank) {
			if ($rank['badge'] && $showBadge) {
				if(is_array( $rank['badge'] ) ) {
					$out .= SP()->theme->sp_paint_iconset_icon( $rank['badge'], $badgeClass );
				} else {
					$out .= "<img src='".$rank['badge']."' class='$badgeClass' title='".esc_attr($rank['name'])."' />";
				}
			}
			if ($showTitle) {
				$out .= "<p class='$titleClass'>".$rank['name'].'</p>';
			}
		}
	}
	$out = apply_filters('sph_ForumRank', $out, $ranks, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UserSpecialRank()
#	Display a users special ranks
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_UserSpecialRank($args = '', $ranks = null) {
	$defs = array('titleClass' => 'spSpecialRank',
	              'badgeClass' => 'spSpecialRank',
	              'showTitle'  => 1,
	              'showBadge'  => 1,
	              'echo'       => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_SpecialRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$titleClass = esc_attr($titleClass);
	$badgeClass = esc_attr($badgeClass);
	$showTitle  = (int) $showTitle;
	$showBadge  = (int) $showBadge;
	$echo       = (int) $echo;

	if (!$showTitle && !$showBadge) return;

	# the forum rank and title based on specified options
	$out = '';
	if (!empty($ranks)) {
		foreach ($ranks as $rank) {
			
			if ($rank['badge'] && $showBadge) {
				if(is_array( $rank['badge'] ) ) {
					$out .= SP()->theme->sp_paint_iconset_icon( $rank['badge'], $badgeClass );
				} else {
					$out .= "<img src='".$rank['badge']."' class='$badgeClass' title='".esc_attr($rank['name'])."' />";
				}
			}
			
			if ($showTitle) {
				$out .= "<p class='$titleClass'>".$rank['name'].'</p>';
			}
		}
	}
	$out = apply_filters('sph_SpecialRank', $out, $ranks, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UserMembership()
#	Display a users usergroup memberships
#	Scope:	Site
#	Version: 5.5.7
#
# --------------------------------------------------------------------------------------

function sp_UserMembership($args = '', $memberships = null) {
	$defs = array('titleClass' => 'spUserMemberships',
	              'badgeClass' => 'spUserMemberships',
	              'showTitle'  => 1,
	              'showBadge'  => 1,
	              'echo'       => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UserMemberships_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$showTitle = (int) $showTitle;
	$showBadge = (int) $showBadge;
	$echo      = (int) $echo;

	if (!$showTitle && !$showBadge) return;

	# the forum rank and title based on specified options
	$out = '';
	if (!empty($memberships)) {
		foreach ($memberships as $membership) {
			if ($membership['usergroup_badge'] && $showBadge) $out .= "<img src='".SP_STORE_URL.'/'.SP()->plugin->storage['ranks'].'/'.$membership['usergroup_badge']."' class='$badgeClass' title='".esc_attr($membership['usergroup_name'])."' />";
			if ($showTitle) {
				$out .= "<p class='$titleClass'>".$membership['usergroup_name'].'</p>';
			}
		}
	}
	$out = apply_filters('sph_UserMemberships', $out, $memberships, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_LoggedInOutLabel()
#	Display current users logged in/out status message
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_LoggedInOutLabel($args = '', $inLabel = '', $outLabel = '', $outLabelMember = '') {
	$defs = array('tagId'    => 'spLoggedInOutLabel',
	              'tagClass' => 'spLabel',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_LoggedInOutLabel_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;

	if (is_user_logged_in() == true) {
		$label = SP()->displayFilters->title(str_replace('%USERNAME%', SP()->user->thisUser->display_name, $inLabel));
	} elseif (SP()->user->thisUser->offmember) {
		$label = SP()->displayFilters->title(str_replace('%USERNAME%', SP()->user->thisUser->offmember, $outLabelMember));
	} else {
		# if they can not register then nothing to display
		if (get_option('users_can_register') == false) return;
		if (!empty(SP()->user->guest_cookie->display_name)) $outLabel .= ' ('.SP()->user->guest_cookie->display_name.')';
		$label = SP()->displayFilters->title($outLabel);
	}
	$out = "<div id='$tagId' class='$tagClass'>$label</div>";
	$out = apply_filters('sph_LoggedInOutLabel', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_LoginOutButton()
#	Display current users Login/Logout Button
#	Scope:	Forum
#	Version: 5.0
#		5.2 - mobileMenu arg added
#
# --------------------------------------------------------------------------------------

function sp_LogInOutButton($args = '', $inLabel = '', $outLabel = '', $toolTip = '') {
	$splogin = SP()->options->get('sflogin');
	if ($splogin['spshowlogin'] == false) return;

	$defs = array('tagId'      => 'spLogInOutButton',
	              'tagClass'   => 'spButton',
	              'logInLink'  => '',
	              'logOutLink' => esc_url(wp_logout_url()),
	              'logInIcon'  => 'sp_LogInOut.png',
	              'logOutIcon' => 'sp_LogInOut.png',
	              'iconClass'  => 'spIcon',
	              'mobileMenu' => 0,
	              'echo'       => 1,);

	# check if alt urls in options and change before
	if (empty($defs['logInLink']) && !empty($splogin['spaltloginurl'])) {
		$defs['logInLink'] = esc_url($splogin['spaltloginurl']);
	}

	if ($defs['logOutLink'] == esc_url(wp_login_url() && !empty($splogin['spaltlogouturl']))) {
		$defs['logOutLink'] = esc_url($splogin['spaltlogouturl']);
	}

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_LogInOutButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$logInLink  = esc_url($logInLink);
	$logOutLink = esc_url($logOutLink);
	$logInIcon  = sanitize_file_name($logInIcon);
	$logOutIcon = sanitize_file_name($logOutIcon);
	$iconClass  = esc_attr($iconClass);
	$toolTip    = esc_attr($toolTip);
	$mobileMenu = (int) $mobileMenu;
	$echo       = (int) $echo;

	$br  = ($mobileMenu) ? '<br />' : '';
	$out = '';

	if (is_user_logged_in() == true) {
		if ($mobileMenu) $out .= sp_open_grid_cell();
		$out .= "<a class='$tagClass' id='$tagId' title='$toolTip' ";
		$out .= "href='$logOutLink'>";
		if (!empty($logOutIcon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $logOutIcon).$br;
		if (!empty($outLabel)) $out .= SP()->displayFilters->title($outLabel);
		$out .= "</a>";
		if ($mobileMenu) $out .= sp_close_grid_cell();
	} else {
		if ($mobileMenu) $out .= sp_open_grid_cell();

		# add classname for event listener
		if (empty($logInLink)) $tagClass .= ' spLogInOut';
		$out .= "<a class='$tagClass' id='$tagId' title='$toolTip' ";
		if (!empty($logInLink)) {
			$out .= "href='$logInLink'>";
		} else {
			if (!$mobileMenu) {
				$out .= " >";
			} else {
				$out .= "href='#spLoginForm'>";
			}
		}

		if (!empty($logInIcon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $logInIcon).$br;
		if (!empty($inLabel)) $out .= SP()->displayFilters->title($inLabel);
		$out .= "</a>";
		if ($mobileMenu) $out .= sp_close_grid_cell();
	}
	$out = apply_filters('sph_LogInOutButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_LoginForm()
#	Display inline drop down login form
#	Scope:	Forum
#	Version: 5.0
#	Change Log:
#		5.5.2	Argument 'separator' added
#
# --------------------------------------------------------------------------------------

function sp_LoginForm($args = '') {
	# no form if logged in
	if (is_user_logged_in() == true) return;

	$splogin = SP()->options->get('sflogin');
	if ($splogin['spshowlogin'] == false) return;

	$defs = array('tagId'           => 'spLoginForm',
	              'tagClass'        => 'spForm',
				  'labelClass'		=> '',
				  'titleClass'		=> 'spLoginFormTitle',
	              'controlFieldset' => 'spControl',
	              'controlInput'    => 'spControl',
	              'controlSubmit'   => 'spSubmit',
	              'controlIcon'     => 'spIcon',
	              'controlLink'     => 'spLink',
	              'iconName'        => 'sp_LogInOut.png',
				  'title'           => '',
	              'labelUserName'   => '',
	              'labelPassword'   => '',
	              'labelRemember'   => '',
	              'labelRegister'   => '',
	              'labelLostPass'   => '',
	              'labelSubmit'     => '',
	              'showRegister'    => 1,
	              'showLostPass'    => 1,
	              'loginLink'       => esc_url(wp_login_url()),
	              'registerLink'    => esc_url(wp_registration_url()),
	              'passwordLink'    => esc_url(wp_lostpassword_url()),
	              'separator'       => ' | ',
	              'echo'            => 1);

	# check if alt urls in options and change before
	if ($defs['loginLink'] == esc_url(wp_login_url()) && !empty($splogin['spaltloginurl'])) {
		$defs['loginLink'] = esc_url($splogin['spaltloginurl']);
	}
	if ($defs['registerLink'] == esc_url(wp_registration_url()) && !empty($splogin['spaltregisterurl'])) {
		$defs['registerLink'] = esc_url($splogin['spaltregisterurl']);
	}

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_LoginForm_args', $a);

	# sanitize before use
	$a['tagId']           = esc_attr($a['tagId']);
	$a['labelClass']	  = esc_attr($a['labelClass']);
	$a['tagClass']        = esc_attr($a['tagClass']);
	$a['titleClass']      = esc_attr($a['titleClass']);
	$a['controlFieldset'] = esc_attr($a['controlFieldset']);
	$a['controlInput']    = esc_attr($a['controlInput']);
	$a['controlSubmit']   = esc_attr($a['controlSubmit']);
	$a['controlIcon']     = esc_attr($a['controlIcon']);
	$a['controlLink']     = esc_attr($a['controlLink']);
	$a['title']           = esc_attr($a['title']);
	$a['iconName']        = sanitize_file_name($a['iconName']);
	$a['showRegister']    = (int) $a['showRegister'];
	$a['showLostPass']    = (int) $a['showLostPass'];
	$a['labelUserName']   = SP()->displayFilters->title($a['labelUserName']);
	$a['labelPassword']   = SP()->displayFilters->title($a['labelPassword']);
	$a['labelRemember']   = SP()->displayFilters->title($a['labelRemember']);
	$a['labelRegister']   = SP()->displayFilters->title($a['labelRegister']);
	$a['labelLostPass']   = SP()->displayFilters->title($a['labelLostPass']);
	$a['labelSubmit']     = SP()->displayFilters->title($a['labelSubmit']);
	$a['loginLink']       = esc_url($a['loginLink']);
	$a['registerLink']    = esc_url($a['registerLink']);
	$a['passwordLink']    = esc_url($a['passwordLink']);
	$a['separator']       = esc_attr($a['separator']);
	$a['echo']            = (int) $a['echo'];

	$a = apply_filters('sph_LoginFormAttributes', $a);	

	$out = "<div id='".$a['tagId']."' class='".$a['tagClass']."'>";
	$out .= sp_inline_login_form($a);
	$out .= "</div>";

	$out = apply_filters('sph_LoginForm', $out, $a);

	if ($a['echo']) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_RegisterButton()
#	Display registration button link for guests
#	Scope:	Forum
#	Version: 5.0
#		5.2 - mobileMenu arg added
#
# --------------------------------------------------------------------------------------

function sp_RegisterButton($args = '', $label = '', $toolTip = '') {
	$splogin = SP()->options->get('sflogin');
	if ($splogin['spshowregister'] == false) return;

	# should we show the register button?
	if (is_user_logged_in() == true || get_option('users_can_register') == false || SP()->core->forumData['lockdown'] == true) return;

	$defs = array('tagId'      => 'spRegisterButton',
	              'tagClass'   => 'spButton',
	              'link'       => esc_url(wp_registration_url()),
	              'icon'       => 'sp_Registration.png',
	              'iconClass'  => 'spIcon',
	              'mobileMenu' => 0,
	              'echo'       => 1,);

	# check if alt urls in options and change before
	if ($defs['link'] == esc_url(wp_registration_url()) && !empty($splogin['spaltregisterurl'])) {
		$defs['link'] = esc_url($splogin['spaltregisterurl']);
	}

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_RegisterButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$link       = esc_url($link);
	$icon       = sanitize_file_name($icon);
	$iconClass  = esc_attr($iconClass);
	$toolTip    = esc_attr($toolTip);
	$mobileMenu = (int) $mobileMenu;
	$echo       = (int) $echo;

	$br = ($mobileMenu) ? '<br />' : '';

	$out = '';
	if ($mobileMenu) $out .= sp_open_grid_cell();
	$out .= "<a class='$tagClass' id='$tagId' title='$toolTip' href='$link'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon).$br;
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	if ($mobileMenu) $out .= sp_close_grid_cell();

	$out = apply_filters('sph_RegisterButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ProfileButton()
#	Display profile button link for users
#	Scope:	Site
#	Version: 5.0
#		5.2 - mobileMenu arg added
#
# --------------------------------------------------------------------------------------

function sp_ProfileEditButton($args = '', $label = '', $toolTip = '') {
	if (!is_user_logged_in()) return;

	$defs = array('tagId'      => 'spProfileEditButton',
	              'tagClass'   => 'spButton',
	              'link'       => sp_build_profile_formlink(SP()->user->thisUser->ID),
	              'icon'       => 'sp_ProfileForm.png',
	              'iconClass'  => 'spIcon',
	              'mobileMenu' => 0,
	              'echo'       => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ProfileEditButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$link       = esc_url($link);
	$icon       = sanitize_file_name($icon);
	$iconClass  = esc_attr($iconClass);
	$toolTip    = esc_attr($toolTip);
	$mobileMenu = (int) $mobileMenu;
	$echo       = (int) $echo;

	$br  = ($mobileMenu) ? '<br />' : '';
	$out = '';

	if ($mobileMenu) $out .= sp_open_grid_cell();
	if ($mobileMenu) $tagClass = '';
	$out .= "<a rel='nofollow' class='$tagClass' id='$tagId' title='$toolTip' href='$link'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon).$br;
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	if ($mobileMenu) $out .= sp_close_grid_cell();

	$out = apply_filters('sph_ProfileEditButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberButton()
#	Display members list button link for users
#	Scope:	Site
#	Version: 5.0
#		5.2 - mobileMenu arg added
#
# --------------------------------------------------------------------------------------

function sp_MemberButton($args = '', $label = '', $toolTip = '') {
	if (!SP()->auths->get('view_members_list', SP()->rewrites->pageData['forumid'])) return;

	$defs = array('tagId'      => 'spMemberButton',
	              'tagClass'   => 'spButton',
	              'link'       => SPMEMBERLIST,
	              'icon'       => 'sp_MemberList.png',
	              'iconClass'  => 'spIcon',
	              'mobileMenu' => 0,
	              'echo'       => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MemberButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$link       = esc_url($link);
	$icon       = sanitize_file_name($icon);
	$iconClass  = esc_attr($iconClass);
	$toolTip    = esc_attr($toolTip);
	$mobileMenu = (int) $mobileMenu;
	$echo       = (int) $echo;

	$br  = ($mobileMenu) ? '<br />' : '';
	$out = '';

	if ($mobileMenu) $out .= sp_open_grid_cell();
	$out .= "<a class='$tagClass' id='$tagId' title='$toolTip' href='$link'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon).$br;
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= '</a>';
	if ($mobileMenu) $out .= sp_close_grid_cell();

	$out = apply_filters('sph_MemberButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_LastVisitLabel()
#	Display last visited user message
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_LastVisitLabel($args = '', $label = '') {
	# should we show the last visit label?
	if (empty(SP()->user->thisUser->lastvisit)) return;

	$defs = array('tagId'    => 'spLastVisitLabel',
	              'tagClass' => 'spLabelSmall',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_LastVisitLabel_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	if ($get) return SP()->user->thisUser->lastvisit;

	$label = SP()->displayFilters->title(str_replace('%LASTVISIT%', SP()->dateTime->format_date('d', SP()->user->thisUser->lastvisit), $label));

	$out = "<span id='$tagId' class='$tagClass'>$label</span>";
	$out = apply_filters('sph_LastVisitLabel', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_QuickLinksForum()
#	Display QuickLinks forum dropdown
#	Scope:	Site
#	Version: 5.0
#
#	Change log
#		5.5.6:	Added new argument - showSubs
# --------------------------------------------------------------------------------------

function sp_QuickLinksForum($args = '', $label = '') {
	$defs = array('tagId'    => 'spQuickLinksForum',
	              'tagClass' => 'spControl',
	              'length'   => 40,
	              'showSubs' => 1,
	              'echo'     => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_QuickLinksForum_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$length   = (int) $length;
	$showSubs = (int) $showSubs;
	$echo     = (int) $echo;

	# load data and check if empty or denied
	$groups = new spcGroupView('', false);
	if ($groups->groupViewStatus == 'no access' || $groups->groupViewStatus == 'no data') return;

	$out = "<div class='spQuickLinks $tagClass' id='$tagId'>";

	if (!empty($label)) {
		$label  = SP()->displayFilters->title($label);
//		$indent = '&nbsp;&nbsp;';
		$indent = '';
	}
	if (empty($length)) $length = 40;
	$level = 0;

	if ($groups->pageData) {
		$out .= "<select id='spQuickLinksForumSelect' class='quick-links $tagClass' name='spQuickLinksForumSelect'>";

		if ($label) $out .= '<option>'.$label.'</option>'."";
		foreach ($groups->pageData as $group) {
			$out .= '<optgroup class="spList" label="'.esc_attr($indent.SP()->primitives->create_name_extract($group->group_name)).'">'."";
			if ($group->forums) {
				foreach ($group->forums as $forum) {
					$out .= '<option value="'.$forum->forum_permalink.'">';
					$out .= str_repeat($indent, $level).SP()->primitives->create_name_extract($forum->forum_name, $length).'</option>'."";
					if (!empty($forum->subforums) && $showSubs) $out .= sp_compile_forums($forum->subforums, $forum->forum_id, 1, true);
				}
			}
			$out .= "</optgroup>";
		}
		$out .= "</select>";
	}

	$out .= "</div>";
	$out = apply_filters('sph_QuickLinksForum', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_QuickLinksTopic()
#	Display QuickLinks new topics dropdown
#	Scope:	Site
#	Version: 5.0
#	ChangeLog:
#		5.7.3: Creates placeholder and is populated later in footer by sp_PopulateQuickLinksTopic()
#
# --------------------------------------------------------------------------------------

function sp_QuickLinksTopic($args = '', $label = '') {
	$defs = array('tagClass' => 'spControl',
	              'length'   => 40,
	              'show'     => 20,
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_QuickLinksTopic_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr('spQuickLinksTopic');
	$tagClass = esc_attr($tagClass);
	$length   = (int) $length;
	$show     = (int) $show;
	$echo     = (int) $echo;

	SP()->rewrites->pageData['QuickLinks']['length'] = $length;
	SP()->rewrites->pageData['QuickLinks']['show']   = $show;
	SP()->rewrites->pageData['QuickLinks']['label']  = $label;

	$out = '';
	$out .= "<div class='spQuickLinks $tagClass' id='$tagId'>";
	$out .= "</div>";

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_PopulateQuickLinksTopic()
#	Display QuickLinks new topics dropdown
#	Scope:	Site
#	Version: 5.0
#	ChangeLog:
#		5.7.3: Creates placeholder and is populated later in footer
#		by sp_PopulateQuickLinksTopic() - called in template footer action
#
# --------------------------------------------------------------------------------------

function sp_PopulateQuickLinksTopic() {
	$length = SP()->rewrites->pageData['QuickLinks']['length'];
	$show   = SP()->rewrites->pageData['QuickLinks']['show'];
	$label  = SP()->rewrites->pageData['QuickLinks']['label'];

	$out = '';

	if (!empty(SP()->user->thisUser->newposts['topics'])) {
		$spList = new spcTopicList(array_slice(SP()->user->thisUser->newposts['topics'], 0, $show, true), $show, true, '', 0, 1, 'topic quick links');
	} else {
		$spList = new spcTopicList('', $show, true, '', 0, 1, 'topic quick links');
	}
	if (!empty($spList->listData)) {
		$out .= "<select class='quick-inks' id='spQuickLinksTopicSelect'>";
		$out .= "<option>$label</option>";
		$thisForum = 0;
		$group     = false;
		foreach ($spList->listData as $spPost) {
			if ($spPost->forum_id != $thisForum) {
				if ($group) $out .= '</optgroup>';
				$out .= "<optgroup class='spList' label='".esc_attr(SP()->primitives->create_name_extract($spPost->forum_name, $length))."'>";
				$thisForum = $spPost->forum_id;
				$group     = true;
			}
			$class = 'spPostRead';
			$title = "title='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, "sp_QLBalloonNone.png")."'";
			if ($spPost->post_status != 0) {
				$class = 'spPostMod';
				$title = "title='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, "sp_QLBalloonRed.png")."'";
			} elseif (sp_is_in_users_newposts($spPost->topic_id)) {
				$class = 'spPostNew';
				$title = "title='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, "sp_QLBalloonBlue.png")."'";
			}
			$out .= "<option class='$class' $title value='$spPost->post_permalink'>".SP()->primitives->create_name_extract($spPost->topic_name, $length)."</option>";
		}
		$out .= "</optgroup>";
		$out .= "</select>";
	}

	$out = apply_filters('sph_PopulateQuickLinksTopic', $out);

	return $out;
}

# --------------------------------------------------------------------------------------
#
#	sp_QuickLinksForumMobile()
#	Display QuickLinks forums list for mobile display
#	Scope:	Site
#	Version: 5.5.7
#
# --------------------------------------------------------------------------------------

function sp_QuickLinksForumMobile($args = '', $label = '') {
	$defs = array('tagIdControl'  => 'spQuickLinksForumMobile',
	              'tagClass'      => 'spControl',
	              'tagIdList'     => 'spQuickLinksMobileForumList',
	              'listClass'     => 'spQuickLinksList',
	              'listDataClass' => 'spQuickLinksGroup',
	              'length'        => 40,
	              'showSubs'      => 1,
	              'openIcon'      => 'sp_GroupOpen.png',
	              'closeIcon'     => 'sp_GroupClose.png',
	              'echo'          => 1);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_QuickLinksForumMobile_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagIdControl  = esc_attr($tagIdControl);
	$tagClass      = esc_attr($tagClass);
	$tagIdList     = esc_attr($tagIdList);
	$listClass     = esc_attr($listClass);
	$listDataClass = esc_attr($listDataClass);
	$openIcon      = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($openIcon));
	$closeIcon     = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($closeIcon));
	$showSubs      = (int) $showSubs;
	$length        = (int) $length;
	$echo          = (int) $echo;

	# load data and check if empty or denied
	$groups = new spcGroupView('', false);
	if ($groups->groupViewStatus == 'no access' || $groups->groupViewStatus == 'no data') return;

	if (!empty($label)) $label = SP()->displayFilters->title($label);
	if (empty($length)) $length = 40;
	$indent = '&nbsp;&nbsp;';

	$out = '';
	if ($groups->pageData) {
		$out .= "<div class='$tagClass' id='$tagIdControl'>";
		$out .= "<p id='spQLFTitle' data-tagidlist='$tagIdList' data-target='spQLFOpener' data-open='$openIcon' data-close='$closeIcon'>$label<span id='spQLFOpener'><img src='$openIcon' /></span></p>";
		$out .= "</div>";

		$out .= sp_InsertBreak('echo=false');

		$out .= "<div id='$tagIdList' class='$listClass' style='display:none'>";
		foreach ($groups->pageData as $group) {
			$out .= "<div class='$listDataClass'><div>".esc_attr($indent.SP()->primitives->create_name_extract($group->group_name))."</div>";
			if ($group->forums) {
				foreach ($group->forums as $forum) {
					$out .= '<p><a href="'.$forum->forum_permalink.'">';
					$out .= SP()->primitives->create_name_extract($forum->forum_name, $length).'</a></p>'."";
					if (!empty($forum->subforums) && $showSubs) $out .= sp_compile_forums_mobile($forum->subforums, $forum->forum_id, 1, true);
				}
			}
			$out .= "</div>";
		}
		$out .= "</div>";
	}

	$out = apply_filters('sph_QuickLinksForumMobile', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_QuickLinksTopicMobile()
#	Display QuickLinks new topics list for mobile display
#	Scope:	Site
#	Version: 5.2
#
# --------------------------------------------------------------------------------------

function sp_QuickLinksTopicMobile($args = '', $label = '') {
	$defs = array('tagIdControl'  => 'spQuickLinksTopicMobile',
	              'tagClass'      => 'spControl',
	              'tagIdList'     => 'spQuickLinksMobileList',
	              'listClass'     => 'spQuickLinksList',
	              'listDataClass' => 'spQuickLinksGroup',
	              'openIcon'      => 'sp_GroupOpen.png',
	              'closeIcon'     => 'sp_GroupClose.png',
	              'length'        => 40,
	              'show'          => 20,
	              'echo'          => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_QuickLinksTopicMobile_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagIdControl  = esc_attr($tagIdControl);
	$tagClass      = esc_attr($tagClass);
	$tagIdList     = esc_attr($tagIdList);
	$listClass     = esc_attr($listClass);
	$listDataClass = esc_attr($listDataClass);
	$openIcon      = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($openIcon));
	$closeIcon     = SP()->theme->paint_file_icon(SPTHEMEICONSURL, sanitize_file_name($closeIcon));
	$length        = (int) $length;
	$show          = (int) $show;
	$echo          = (int) $echo;

	if (!empty($label)) $label = SP()->displayFilters->title($label);
	if (empty($length)) $length = 40;

	$out = '';
	if (!empty(SP()->user->thisUser->newposts['topics'])) {
		$spList = new spcTopicList(array_slice(SP()->user->thisUser->newposts['topics'], 0, $show, true), $show, true, '', 0, 1, 'topic quick links mobile');
	} else {
		$spList = new spcTopicList('', $show, true, '', 0, 1, 'topic quick links mobile');
	}

	if (!empty($spList->listData)) {
		$out .= "<div class='$tagClass' id='$tagIdControl'>";
		$out .= "<p id='spQLTitle'  data-tagidlist='$tagIdList' data-target='pQLFOpener' data-open='$openIcon' data-close='$closeIcon'>$label<span id='spQLOpener'><img src='$openIcon' /></span></p>";
		$out .= "</div>";

		$out .= sp_InsertBreak('echo=false');

		$out .= "<div id='$tagIdList' class='$listClass' style='display:none'>";
		$thisForum = 0;
		$group     = false;
		foreach ($spList->listData as $spPost) {
			if ($spPost->forum_id != $thisForum) {
				if ($group) $out .= '</div>';
				$out .= "<div class='$listDataClass'><p>".SP()->primitives->create_name_extract($spPost->forum_name, $length)."</p>";
				$thisForum = $spPost->forum_id;
				$group     = true;
			}
			$class = 'spPostRead';
			$image = "<img src='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, "sp_QLBalloonNone.png")."' alt='' />";
			if ($spPost->post_status != 0) {
				$class = 'spPostMod';
				$image = "<img src='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, "sp_QLBalloonRed.png")."' alt='' />";
			} elseif (sp_is_in_users_newposts($spPost->topic_id)) {
				$class = 'spPostNew';
				$image = "<img src='".SP()->theme->paint_file_icon(SPTHEMEICONSURL, "sp_QLBalloonBlue.png")."' alt='' />";
			}
			$out .= "<p><a class='$class' href='$spPost->post_permalink'>$image&nbsp;&nbsp;".SP()->primitives->create_name_extract($spPost->topic_name, $length)."</a></p>";
		}
		$out .= "</div></div>";
	}

	$out = apply_filters('sph_QuickLinksTopicMobile', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_BreadCrumbs()
#	Display Breadcrumbs
#	Scope:	Forum
#	Version: 5.0
#	Version: 5.5.1 Add curClass to current breadcrumb
#
# --------------------------------------------------------------------------------------

function sp_BreadCrumbs($args = '', $homeLabel = '') {
	$defs = array('tagId'         => 'spBreadCrumbs',
	              'tagClass'      => 'spBreadCrumbs',
	              'spanClass'     => 'spBreadCrumbs',
	              'linkClass'     => 'spLink',
	              'curClass'      => 'spCurrentBreadcrumb',
	              'homeLink'      => user_trailingslashit(SPSITEURL),
	              'groupLink'     => 0,
	              'tree'          => 0,
	              'truncate'      => 0,
	              'icon'          => 'sp_ArrowRight.png',
	              'iconClass'     => 'spIcon',
	              'iconText'      => '',
	              'homeIcon'      => 'sp_ArrowRight.png',
	              'homeIconClass' => 'spIcon',
	              'homeText'      => '',
	              'echo'          => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_BreadCrumbs_args', $a);
	extract($a, EXTR_SKIP);

	global $post;

	# sanitize before use
	$tagId         = esc_attr($tagId);
	$tagClass      = esc_attr($tagClass);
	$spanClass     = esc_attr($spanClass);
	$linkClass     = esc_attr($linkClass);
	$curClass      = esc_attr($curClass);
	$homeLink      = esc_url($homeLink);
	$groupLink     = (int) $groupLink;
	$tree          = (int) $tree;
	$truncate      = (int) $truncate;
	$icon          = sanitize_file_name($icon);
	$iconClass     = esc_attr($iconClass);
	$iconText      = SP()->saveFilters->kses($iconText);
	$homeIcon      = sanitize_file_name($homeIcon);
	$homeIconClass = esc_attr($homeIconClass);
	$homeText      = SP()->saveFilters->kses($homeText);
	$echo          = (int) $echo;
	if (!empty($homeLabel)) $homeLabel = SP()->displayFilters->title($homeLabel);

	# init some vars
	$breadCrumbs = '';
	$treeCount   = 0;
	$crumbEnd    = ($tree) ? '<br />' : '';
	$crumbSpace  = ($tree) ? "<span class='$spanClass'></span>" : '';

	if (!empty($icon)) {
		$icon = SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	} else {
		if (!empty($iconText)) $icon = $iconText;
	}
	$firstIcon = $icon;

	# set up the home and breadcrumb separators - can be text or icon
	# to get text, must clear icon first
	if (!empty($homeIcon)) {
		$homeIcon = SP()->theme->paint_icon($homeIconClass, SPTHEMEICONSURL, $homeIcon);
	} else {
		if (!empty($homeText)) $homeIcon = $homeText;
	}
	if (empty($homeIcon)) $firstIcon = '';

	# main container for breadcrumbs
	$breadCrumbs .= "<div id='$tagId' class='$tagClass'>";

	# home link
	if (!empty($homeLink) && !empty($homeLabel) && !(get_option('page_on_front') == SP()->options->get('sfpage') && get_option('show_on_front') == 'page')) {
		$breadCrumbs .= "<a class='$linkClass' href='$homeLink'>".$homeIcon.$homeLabel."</a>";
		$treeCount++;
	}

	# wp page link for forum
	$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount)."<a class='$linkClass' href='".SP()->spPermalinks->get_url()."'>$firstIcon$post->post_title</a>";
	$treeCount++;

	if ($groupLink) {
		if (isset($_GET['group'])) {
			$groupId = SP()->filters->integer($_GET['group']);
			$group   = SP()->DB->table(SPGROUPS, "group_id=$groupId", "row");
		} elseif (isset(SP()->rewrites->pageData['forumslug'])) {
			$group = sp_get_group_record_from_slug(SP()->rewrites->pageData['forumslug']);
		}
		if ($group) {
			$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='".add_query_arg(array('group' => $group->group_id), SP()->spPermalinks->get_url())."'>".SP()->primitives->truncate_name(SP()->displayFilters->title($group->group_name), $truncate).'</a>';
			$treeCount++;
		}
	}

	# parent forum links if current forum is a sub-forum
	if (isset(SP()->rewrites->pageData['parentforumid'])) {
		$forumNames = array_reverse(SP()->rewrites->pageData['parentforumname']);
		$forumSlugs = array_reverse(SP()->rewrites->pageData['parentforumslug']);
		for ($x = 0; $x < count($forumNames); $x++) {
			$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='".SP()->spPermalinks->build_url($forumSlugs[$x], '', 0, 0)."'>".SP()->primitives->truncate_name(SP()->displayFilters->title($forumNames[$x]), $truncate).'</a>';
			$treeCount++;
		}
	}

	# forum link (parent or child forum)
	if (!empty(SP()->rewrites->pageData['forumslug']) && (SP()->rewrites->pageData['forumslug'] != 'all') && (!empty(SP()->rewrites->pageData['forumname']))) {
		# Always show page 1 on traversing
		$returnPage =  1 ;
		$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='".SP()->spPermalinks->build_url(SP()->rewrites->pageData['forumslug'], '', $returnPage, 0)."'>".SP()->primitives->truncate_name(SP()->displayFilters->title(SP()->rewrites->pageData['forumname']), $truncate).'</a>';
		$treeCount++;
	}

	# topic link
	if (!empty(SP()->rewrites->pageData['topicslug']) && !empty(SP()->rewrites->pageData['topicname'])) {
		$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount)."$icon<a class='$linkClass' href='".SP()->spPermalinks->build_url(SP()->rewrites->pageData['forumslug'], SP()->rewrites->pageData['topicslug'], SP()->rewrites->pageData['page'], 0)."'>".SP()->primitives->truncate_name(SP()->displayFilters->title(SP()->rewrites->pageData['topicname']), $truncate).'</a>';
	}

	# profile link
	if (!empty(SP()->rewrites->pageData['profile'])) {
		$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='".SP()->spPermalinks->get_url('profile')."'>".SP()->primitives->front_text('Profile').'</a>';
	}

	# profile link
	if (!empty(SP()->rewrites->pageData['members']) && SP()->rewrites->pageData['members'] == 'list') {
		$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='".SP()->spPermalinks->get_url('members')."'>".SP()->primitives->front_text('Members List').'</a>';
	}

	# recent post list (as page)
	if (!empty(SP()->rewrites->pageData['pageview']) && SP()->rewrites->pageData['pageview'] == 'newposts') {
		$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='".SP()->spPermalinks->get_url('newposts')."'>".SP()->primitives->front_text('Recent Posts').'</a>';
	}

	# search results - no link
	if (!empty(SP()->rewrites->pageData['searchpage']) && SP()->rewrites->pageData['searchpage'] > 0) {
		$breadCrumbs .= $crumbEnd.str_repeat($crumbSpace, $treeCount).$icon."<a class='$linkClass' href='#'>".SP()->primitives->front_text('Search Results').'</a>';
	}

	# allow plugins/themes to filter the breadcrumbs
	$breadCrumbs = apply_filters('sph_BreadCrumbs', $breadCrumbs, $a, $crumbEnd, $crumbSpace, $treeCount);

	# close the breadcrumb container
	$breadCrumbs .= '</div>';

	$breadCrumbs .= '
		<script>
			(function(spj, $, undefined) {
				$(document).ready(function() {
					$("#'.$tagId.' a:last-child").addClass("'.$curClass.'");
				});
			}(window.spj = window.spj || {}, jQuery));
		</script>
	';

	if ($echo) {
		echo $breadCrumbs;
	} else {
		return $breadCrumbs;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_BreadCrumbsMobile()
#	Display Breadcrumbs on a mobile device
#	Scope:	Forum
#	Version: 5.2
#	Version: 5.5.1 Add curClass to current breadcrumb
#
# --------------------------------------------------------------------------------------

function sp_BreadCrumbsMobile($args = '', $forumLabel = '') {
	$defs = array('tagId'    => 'spBreadCrumbsMobile',
	              'tagClass' => 'spButton',
	              'curClass' => 'spCurrentBreadcrumb',
	              'truncate' => 0,
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_BreadCrumbsMobile_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$curClass = esc_attr($curClass);
	$truncate = (int) $truncate;
	$echo     = (int) $echo;

	# init some vars
	$breadCrumbs = '';
	if (!empty($forumLabel)) $forumLabel = SP()->displayFilters->title($forumLabel);

	# main container for breadcrumbs
	$breadCrumbs .= "<div id='$tagId'>";

	# wp page link for forum
	$breadCrumbs .= "<a class='$tagClass' href='".SP()->spPermalinks->get_url()."'>$forumLabel</a>";

	# parent forum links if current forum is a sub-forum
	if (isset(SP()->rewrites->pageData['parentforumid'])) {
		$forumNames = array_reverse(SP()->rewrites->pageData['parentforumname']);
		$forumSlugs = array_reverse(SP()->rewrites->pageData['parentforumslug']);
		for ($x = 0; $x < count($forumNames); $x++) {
			$breadCrumbs .= "<a class='$tagClass $curClass' href='".SP()->spPermalinks->build_url($forumSlugs[$x], '', 0, 0)."'>".SP()->primitives->truncate_name(SP()->displayFilters->title($forumNames[$x]), $truncate)."</a>";
		}
	}

	# forum link (parent or child forum)
	if (!empty(SP()->rewrites->pageData['forumslug']) && (SP()->rewrites->pageData['forumslug'] != 'all') && (!empty(SP()->rewrites->pageData['forumname']))) {
		# if showing a topic then check the return page of forum in transient store
		$returnPage = (empty(SP()->rewrites->pageData['topicslug'])) ? 1 : sp_pop_topic_page(SP()->rewrites->pageData['forumid']);
		$breadCrumbs .= "<a class='$tagClass $curClass' href='".SP()->spPermalinks->build_url(SP()->rewrites->pageData['forumslug'], '', $returnPage, 0)."'>".SP()->primitives->truncate_name(SP()->displayFilters->title(SP()->rewrites->pageData['forumname']), $truncate)."</a>";
	}

	# profile link
	if (!empty(SP()->rewrites->pageData['profile'])) {
		$breadCrumbs .= "<a class='$tagClass $curClass' href='".SP()->spPermalinks->get_url('profile')."'>".SP()->primitives->front_text('Profile')."</a>";
	}

	# recent post list (as page)
	if (!empty(SP()->rewrites->pageData['pageview']) && SP()->rewrites->pageData['pageview'] == 'newposts') {
		$breadCrumbs .= "<a class='$tagClass $curClass' href='".SP()->spPermalinks->get_url('newposts')."'>".SP()->primitives->front_text('Recent Posts').'</a>';
	}

	# allow plugins/themes to filter the breadcrumbs
	$breadCrumbs = apply_filters('sph_BreadCrumbsMobile', $breadCrumbs, $a);

	# close the breadcrumb container
	$breadCrumbs .= '</div>';

	$breadCrumbs .= '
		<script>
			(function(spj, $, undefined) {
				$(document).ready(function() {
					$("#'.$tagId.' a:last-child").addClass("'.$curClass.'");
				}(window.spj = window.spj || {}, jQuery));
			});
		</script>
	';

	if ($echo) {
		echo $breadCrumbs;
	} else {
		return $breadCrumbs;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UserNotices()
#	Display user Notices
#	Scope:	Global
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_UserNotices($args = '', $label = '') {
	$defs = array('tagId'     => 'spUserNotices',
	              'tagClass'  => 'spMessage',
	              'textClass' => 'spNoticeText',
	              'linkClass' => 'spNoticeLink',
	              'echo'      => 1,
	              'get'       => 0,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_UserNotices_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$textClass = esc_attr($textClass);
	$linkClass = esc_attr($linkClass);
	$echo      = (int) $echo;
	$get       = (int) $get;
	$m         = '';

	if (!empty(SP()->user->thisUser->user_notices)) {
		foreach (SP()->user->thisUser->user_notices as $notice) {
			$site = wp_nonce_url(SPAJAXURL.'spUserNotice&amp;notice='.$notice->notice_id, 'spUserNotice');
			$nid  = 'noticeid-'.$notice->notice_id;
			$m .= "<div id='$nid'>";
			$m .= "<p class='$textClass'>".SP()->displayFilters->title($notice->message)." ";
			if (!empty($notice->link_text)) $m .= "<a class='$linkClass' href='".esc_url($notice->link)."'>".SP()->displayFilters->title($notice->link_text)."</a>";
			if (!empty($label)) $m .= "&nbsp;&nbsp;<a class='spLabelSmall spUserNotice' data-site='$site' data-nid='$nid'>".SP()->displayFilters->title($label)."</a>";
			$m .= "</p></div>";
		}
	}

	$m = apply_filters('sph_UserNotices_Custom', $m, $a);

	if ($get) return $m;

	if (!empty($m)) {
		$out = "<div id='$tagId' class='$tagClass'>".$m."</div>";
		$out = apply_filters('sph_UserNotices', $out, $a);

		if ($echo) {
			echo $out;
		} else {
			return $out;
		}
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UnreadPostsInfo()
#	Display Unread Posts Info
#	Scope:	Forum
#	Version: 5.0
#		5.2 - mobileMenu arg added
#		5.3.1 - count added
#		5.5.1 - order added (T/text - L/link - M/mark)
#
# --------------------------------------------------------------------------------------

function sp_UnreadPostsInfo($args = '', $label = '', $unreadToolTip = '', $markToolTip = '', $popupLabel = '') {
	if (!SP()->user->thisUser->member) return;# only valid for members

	$defs = array('tagId'        => 'spUnreadPostsInfo',
	              'tagClass'     => 'spUnreadPostsInfo',
	              'markId'       => 'spMarkRead',
	              'unreadLinkId' => 'spUnreadPostsLink',
	              'unreadIcon'   => 'sp_UnRead.png',
	              'markIcon'     => 'sp_markRead.png',
	              'spanClass'    => 'spLabel',
	              'iconClass'    => 'spIcon',
	              'order'        => 'TLM',
	              'popup'        => 1,
	              'count'        => 0,
	              'first'        => 0,
	              'group'        => 1,
	              'mobileMenu'   => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UnreadPostsInfo_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$markId       = esc_attr($markId);
	$unreadLinkId = esc_attr($unreadLinkId);
	$unreadIcon   = sanitize_file_name($unreadIcon);
	$markIcon     = sanitize_file_name($markIcon);
	$spanClass    = esc_attr($spanClass);
	$iconClass    = esc_attr($iconClass);
	$order        = esc_attr($order);
	$popup        = (int) $popup;
	$count        = (int) $count;
	$first        = (int) $first;
	$group        = (int) $group;
	$mobileMenu   = (int) $mobileMenu;
	$echo         = (int) $echo;
	$get          = (int) $get;
	if (!empty($unreadToolTip)) $unreadToolTip = esc_attr($unreadToolTip);
	if (!empty($markToolTip)) $markToolTip = esc_attr($markToolTip);
	if (!empty($popupLabel)) {
		$popupLabel = esc_attr($popupLabel);
	} else {
		$popupLabel = $unreadToolTip; # backwards compat for when $popupLabel did not exist and $popuplabel was used
	}

	# Mark all as read
	$unreads = (empty(SP()->user->thisUser->newposts['topics'])) ? 0 : count(SP()->user->thisUser->newposts['topics']);
	$label   = str_ireplace('%COUNT%', '<span id="spUnreadCount">'.$unreads.'</span>', $label);
	if (!empty($label)) $label = SP()->displayFilters->title($label);

	if ($get) return $unreads;

	$br = ($mobileMenu) ? '<br />' : '';
	if ($mobileMenu) $label = str_replace(' (', '<br />(', $label);
	$out = '';

	$ajaxUrl = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=mark-read", 'spUnreadPostsPopup');
	if ($mobileMenu) {
		# Run as page
		if ($unreads > 0) {
			if ($mobileMenu) $out .= sp_open_grid_cell();
			$out .= "<a href='#$markId'>";
			if ($mobileMenu) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $markIcon, $markToolTip).$br;
			if (!empty($markToolTip)) $out .= $markToolTip;
			$out .= "</a>";
			if ($mobileMenu) $out .= sp_close_grid_cell();

			$args          = array();
			$args['first'] = $first;
			$args['group'] = $group;
			$args['count'] = $count;
			$url           = add_query_arg($args, SP()->spPermalinks->get_url('newposts'));
			if ($mobileMenu) $out .= sp_open_grid_cell();
			$out .= "<a rel='nofollow' id='$unreadLinkId' href='$url'>";
			if ($mobileMenu) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $unreadIcon, $unreadToolTip).$br;
			$out .= "$label</a>";
			if ($mobileMenu) $out .= sp_close_grid_cell();
		}
	} else {
		$out .= "<div id='$tagId' class='$tagClass'>";

		for ($x = 0; $x < strlen($order); $x++) {
			$item = substr($order, $x, 1);

			if ($item == 'T') {
				$out .= "<span class='$spanClass'>$label</span>";
			}

			if ($unreads > 0 && $item != 'T') {
				if ($item == 'L') {
					if ($popup) {
						$site = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=all&amp;first=$first&amp;group=$group&amp;count=$count", 'spUnreadPostsPopup');
						$out .= "<a rel='nofollow' id='$unreadLinkId' class='spUnreadPostsPopup' data-popup='1' data-site='$site' data-label='$popupLabel' data-width='700' data-height='500' data-align='center'>";
					} else {
						$args          = array();
						$args['first'] = $first;
						$args['group'] = $group;
						$args['count'] = $count;
						$url           = add_query_arg($args, SP()->spPermalinks->get_url('newposts'));
						$out .= "<a rel='nofollow' id='$unreadLinkId' href='$url'>";
					}
					$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $unreadIcon, $unreadToolTip)."</a>";
				}
				if ($item == 'M') {
					$out .= "<a class='spMarkAllRead' data-ajaxurl='$ajaxUrl' data-mobile='0'>".SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $markIcon, $markToolTip)."</a>";
				}
			}
		}

		$out .= "</div>";
	}
	$out = apply_filters('sph_UnreadPostsInfo', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UnreadPostsLink()
#	Display Unread Posts Info as simple textual link
#	Scope:	Forum
#	Version: 5.5.9
#
#	Changelog:
#		5.7.2: Added arg 'id' to allow for passing a single forum id
#		5.7.2: Added targetaction to allow for it to be overwritten at call time
#
# --------------------------------------------------------------------------------------

function sp_UnreadPostsLink($args = '', $label = '', $unreadToolTip = '', $popupLabel = '') {
	if (!SP()->user->thisUser->member) return;# only valid for members

	$defs = array('tagId'        => 'spUnreadPostsInfo',
	              'tagClass'     => 'spLink',
	              'popup'        => 1,
	              'count'        => 0,
	              'first'        => 0,
	              'group'        => 1,
	              'id'           => 0,
	              'targetaction' => 'all',
	              'mobileMenu'   => 0,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UnreadPostsLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId        = esc_attr($tagId);
	$tagClass     = esc_attr($tagClass);
	$popup        = (int) $popup;
	$count        = (int) $count;
	$first        = (int) $first;
	$group        = (int) $group;
	$id           = (int) $id;
	$targetaction = esc_attr($targetaction);
	$echo         = (int) $echo;
	$get          = (int) $get;

	if (!empty($unreadToolTip)) $unreadToolTip = esc_attr($unreadToolTip);
	if (!empty($popupLabel)) {
		$popupLabel = esc_attr($popupLabel);
	} else {
		$popupLabel = $unreadToolTip; # backwards compat for when $popupLabel did not exist and $popuplabel was used
	}

	$unreads = (empty(SP()->user->thisUser->newposts['topics'])) ? 0 : count(SP()->user->thisUser->newposts['topics']);
	$label   = str_ireplace('%COUNT%', '<span id="spUnreadCount" class="badge">'.$unreads.'</span>', $label);
	if (!empty($label)) $label = SP()->displayFilters->title($label);

	if ($get) return $unreads;
	$out = '';

	if ($popup) {
		$site = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=$targetaction&amp;first=$first&amp;group=$group&amp;id=$id&amp;count=$count", 'spUnreadPostsPopup');
		$out .= "<a rel='nofollow' id='$tagId' class='$tagClass spUnreadPostsPopup' title='$unreadToolTip' data-popup='1' data-site='$site' data-label='$popupLabel' data-width='700' data-height='500' data-align='center'>$label</a>";
	} else {
		$args                 = array();
		$args['first']        = $first;
		$args['group']        = $group;
		$args['count']        = $count;
		$args['targetaction'] = $targetaction;
		$args['id']           = $id;
		$url                  = add_query_arg($args, SP()->spPermalinks->get_url('newposts'));
		$out .= "<a rel='nofollow' id='$tagId' class='$tagClass' title='$unreadToolTip' href='$url'>$label</a>";
	}

	$out = apply_filters('sph_UnreadPostsLink', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MarkReadLink()
#	Display Mark all Read as simple textual link
#	Scope:	Forum
#	Version: 5.5.9
#
# --------------------------------------------------------------------------------------

function sp_MarkReadLink($args = '', $label = '', $markToolTip = '') {
	if (!SP()->user->thisUser->member) return;# only valid for members

	$defs = array('tagId'    => 'spMarkRead',
	              'tagClass' => 'spLink',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MarkReadLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;

	if (!empty($markToolTip)) $markToolTip = esc_attr($markToolTip);

	if (empty($label) || empty(SP()->user->thisUser->newposts['topics'])) return;

	$out     = '';
	$ajaxUrl = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=mark-read", 'spUnreadPostsPopup');

	$out .= "<a id='$tagId' class='$tagClass spMarkAllRead' title='$markToolTip' data-ajaxurl='$ajaxUrl' data-mobile='0'>$label</a>";

	$out = apply_filters('sph_MarkReadLink', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MarkReadMobile()
#	Display Mark all Read as simple textual link in mobile menu list
#	Scope:	Forum
#	Version: 5.5.9
#
# --------------------------------------------------------------------------------------

function sp_MarkReadMobile($args = '', $label = '', $text = '') {
	$defs = array('tagId'       => 'spMarkRead',
	              'buttonClass' => 'spButton',);

	$a = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$buttonClass = esc_attr($buttonClass);
	$label       = SP()->displayFilters->title($label);
	$text        = SP()->displayFilters->title($text);

	$out = '';
	$out .= "<div id='$tagId'>";

	if (SP()->core->device == 'mobile') {
		$out .= "<div class='spRight'>";
		$out .= "<a id='spPanelClose' href='#'></a>";
		$out .= "</div>";
	}

	if (!empty($text)) $out .= "<p>$text</p>";

	$ajaxUrl = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=mark-read", 'spUnreadPostsPopup');
	$out .= "<p><a class='$buttonClass spMarkAllRead' data-ajaxurl='$ajaxUrl' data-mobile='1' data-tagid='$tagId'>$label</a></p>";

	$out .= '</div>';

	echo $out;
}

# --------------------------------------------------------------------------------------
#
#	sp_MarkForumReadMobile()
#	Display Mark all Read as simple textual link in mobile menu list
#	Scope:	Forum
#	Version: 5.6
#
# --------------------------------------------------------------------------------------

function sp_MarkForumReadMobile($args = '', $label = '', $text = '') {
	$defs = array('tagId'       => 'spMarkReadForum',
	              'buttonClass' => 'spButton',);

	$a = wp_parse_args($args, $defs);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId       = esc_attr($tagId);
	$buttonClass = esc_attr($buttonClass);
	$label       = SP()->displayFilters->title($label);
	$text        = SP()->displayFilters->title($text);

	$forum_unreads = (empty(SP()->user->thisUser->newposts['forums'])) ? '' : array_keys(SP()->user->thisUser->newposts['forums'], SP()->rewrites->pageData['forumid']);
	if (empty($forum_unreads)) return;
	$count = count($forum_unreads);

	$out = '';
	$out .= "<div id='$tagId'>";

	if (SP()->core->device == 'mobile') {
		$out .= "<div class='spRight'>";
		$out .= "<a id='spPanelClose' href='#'></a>";
		$out .= "</div>";
	}

	if (!empty($text)) $out .= "<p>$text</p>";

	$ajaxUrl = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=mark-forum-read&amp;forum=".SP()->rewrites->pageData['forumid'], 'spUnreadPostsPopup');
	$out .= "<p><a class='$buttonClass spMarkThisForumRead' data-ajaxurl='$ajaxUrl' data-count='$count' data-mobile='1' data-tagid='$tagId'>$label</a></p>";

	$out .= '</div>';

	echo $out;
}

# --------------------------------------------------------------------------------------
#
#	sp_MarkForumRead()
#	"Display Mark all Read" with an icon or a simple textual link
#	Scope:	Forum
#	Version: 5.6
#
# --------------------------------------------------------------------------------------

function sp_MarkForumRead($args = '', $label = '', $markToolTip = '') {
	if (!SP()->user->thisUser->member) return;# only valid for members
	if (SP()->rewrites->pageData['pageview'] != 'forum' && SP()->rewrites->pageData['pageview'] != 'topic') return;# only display on forum and topic view

	$defs = array('tagId'      => 'spMarkForumRead',
	              'tagClass'   => 'spMarkForumRead',
	              'iconClass'  => 'spIcon',
	              'markIcon'   => 'sp_MarkForumRead.png',
	              'mobileMenu' => 0,
	              'echo'       => 1,
	              'get'        => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MarkForumRead_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$iconClass  = esc_attr($iconClass);
	$markIcon   = sanitize_file_name($markIcon);
	$mobileMenu = (int) $mobileMenu;
	$echo       = (int) $echo;
	$get        = (int) $get;

	if (!empty($markToolTip)) $markToolTip = esc_attr($markToolTip);

	$forum_unreads = (empty(SP()->user->thisUser->newposts['forums'])) ? '' : array_keys(SP()->user->thisUser->newposts['forums'], SP()->rewrites->pageData['forumid']);

	if ($get) return $forum_unreads;
	if (empty($forum_unreads)) return;

	$br      = ($mobileMenu) ? '<br />' : '';
	$out     = '';
	$ajaxUrl = wp_nonce_url(SPAJAXURL."spUnreadPostsPopup&amp;targetaction=mark-forum-read&amp;forum=".SP()->rewrites->pageData['forumid'], 'spUnreadPostsPopup');

	if ($mobileMenu) {
		$out .= sp_open_grid_cell();
		$out .= "<div id='$tagId'>";
	} else {
		$out .= "<div id='$tagId' class='$tagClass'>";
	}
	$count = count($forum_unreads);
	if (!empty($markIcon)) {
		if (!$mobileMenu) $label = '';
		$out .= "<a class='spMarkThisForumRead' data-ajaxurl='$ajaxUrl' data-count='$count' data-mobile='$mobileMenu' data-tagid='$tagId'>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $markIcon, $markToolTip).$br;
		$out .= $label.'</a>';
	} else {
		$out .= "<a class='spMarkThisForumRead' data-ajaxurl='$ajaxUrl' data-count='$count' data-mobile='$mobileMenu' data-tagid='$tagId'>";
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $markIcon, $markToolTip).$br;
		$out .= $label.'</a>';
	}
	$out .= '</div>';
	if ($mobileMenu) $out .= sp_close_grid_cell();

	$out = apply_filters('sph_MarkForumRead', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MobileMenuSearch()
#	Adds search link to Mobile Menu
#	Scope:	Forum
#	Version: 5.2
#
# --------------------------------------------------------------------------------------

function sp_MobileMenuSearch($args = '', $label = '') {
	$defs = array('searchTagId' => 'spSearchForm',
	              'icon'        => 'sp_Search.png',
	              'iconClass'   => 'spIcon',
	              'echo'        => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MobileMenuSearch_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$searchTagId = esc_attr($searchTagId);
	$icon        = sanitize_file_name($icon);
	$iconClass   = esc_attr($iconClass);
	$echo        = (int) $echo;
	if (!empty($label)) $label = SP()->displayFilters->text($label);
	$br  = '<br />';
	$out = '';

	$out .= sp_open_grid_cell();
	$out .= "<a href='#$searchTagId'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon).$br;
	if (!empty($label)) $out .= $label;
	$out .= "</a>";
	$out .= sp_close_grid_cell();

	$out = apply_filters('sph_MobileMenuSearch', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}



# --------------------------------------------------------------------------------------
#
#	sp_SearchToggleButton()
#	Display simple search Button
#	Scope:	Forum
#	Version: 6.0
#
# --------------------------------------------------------------------------------------

function sp_SearchToggleButton($args = '', $label = '',$toolTip = '') {

	$defs = array('tagId'      => 'spSearchToggleButton',
	              'tagClass'   => 'spButton',
	              'icon'	   => 'sp_Search.png',
	              'iconClass'  => 'spIcon',
	              'echo'       => 1,);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SearchToggleButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass  = esc_attr($iconClass);
	$toolTip    = esc_attr($toolTip);
	$echo       = (int) $echo;

	$out = '';

	$out .= "<a class='$tagClass spOpenSearch' id='$tagId' title='$toolTip' >";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";

	$out = apply_filters('sph_SearchToggleButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_SearchForm()
#	Display Search Form Basic
#	Scope:	Forum
#	Version: 5.0
#
#	Change log
#		5.3.1:	Added mobile display support (close button)
#		5.3.1:	Added missing 'Match' label. Note that I have left this for translation
#				in this 'sp' domain so people do not suddenly lose it now it is theme
#				That should really be removed in the future.
# --------------------------------------------------------------------------------------

function sp_SearchForm($args = '') {
	$defs = array('containerClass'	   => '',
				  'tagId'              => 'spSearchForm',
	              'tagClass'           => 'spSearchSection',
	              'icon'               => 'sp_Search.png',
	              'iconClass'          => 'spIcon',
	              'inputClass'         => 'spControl',
	              'inputWidth'         => 20,
	              'submitId'           => 'spSearchButton',
	              'submitId2'          => 'spSearchButton2',
	              'submitClass'        => 'spButton',
	              'submitClass2'       => 'spButton',
	              'advSearchLinkClass' => 'spLink',
				  'lastSearchLinkClass' => 'spLink',
				  'searchOptionsSection' => '',
	              'advSearchLink'      => '',
	              'advSearchId'        => 'spSearchFormAdvanced',
	              'advSearchClass'     => 'spSearchFormAdvanced',
	              'searchIncludeDef'   => 1,
	              'searchScope'        => 1,
	              'submitLabel'        => '',
	              'placeHolder'        => SP()->primitives->front_text('Search'),
	              'advancedLabel'      => '',
	              'lastSearchLabel'    => '',
	              'toolTip'            => '',
	              'labelLegend'        => '',
				  'scopeSection'	   => '',
				  'matchSection'	   => '',
				  'optionSection'	   => '',
	              'labelScope'         => '',
	              'labelCurrent'       => '',
	              'labelAll'           => '',
	              'labelMatch'         => SP()->primitives->front_text('Match'),
	              'labelMatchAny'      => '',
	              'labelMatchAll'      => '',
	              'labelMatchPhrase'   => '',
	              'labelOptions'       => '',
	              'labelPostTitles'    => '',
	              'labelPostsOnly'     => '',
	              'labelTitlesOnly'    => '',
	              'labelWildcards'     => '',
	              'labelMatchAnyChars' => '',
	              'labelMatchOneChar'  => '',
	              'labelMinLength'     => '',
	              'labelMemberSearch'  => '',
	              'labelTopicsPosted'  => '',
	              'labelTopicsStarted' => '',
				  'spSearchInfo'	   => '',
				  'lineBreak'		   => 1,
				  'useSeperator'	   => 1,
	              'echo'               => 1
				);

	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_SearchForm_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$containerClass		= esc_attr($containerClass);
	$tagId              = esc_attr($tagId);
	$tagClass           = esc_attr($tagClass);
	$icon               = sanitize_file_name($icon);
	$iconClass          = esc_attr($iconClass);
	$inputClass         = esc_attr($inputClass);
	$inputWidth         = (int) $inputWidth;
	$submitId           = esc_attr($submitId);
	$submitId2          = esc_attr($submitId2);
	$submitClass        = esc_attr($submitClass);
	$advSearchLinkClass = esc_attr($advSearchLinkClass);
	$searchOptionsSection = esc_attr($searchOptionsSection);
	$advSearchLink      = esc_url($advSearchLink);
	$advSearchId        = esc_attr($advSearchId);
	$advSearchClass     = esc_attr($advSearchClass);
	$scopeSection		= esc_attr($scopeSection);
	$matchSection		= esc_attr($matchSection);
	$optionSection		= esc_attr($optionSection);
	$searchIncludeDef   = (int) $searchIncludeDef;
	$searchScope        = (int) $searchScope;
	$placeHolder        = esc_attr($placeHolder);
	$useSeperator		= (int) $useSeperator;
	$echo               = (int) $echo;

	if (!empty($submitLabel)) $submitLabel = SP()->displayFilters->title($submitLabel);
	if (!empty($advancedLabel)) $advancedLabel = SP()->displayFilters->title($advancedLabel);
	if (!empty($lastSearchLabel)) $lastSearchLabel = SP()->displayFilters->title($lastSearchLabel);
	if (!empty($toolTip)) $toolTip = esc_attr($toolTip);

	$out = '';

	# start full search form outer container
	$out.= "<div id='spSearchContainer' class='$containerClass'>";

	# render the search form and advanced link
	$out .= "<form id='$tagId' class='spSubmitSearchForm' action='".wp_nonce_url(SPAJAXURL.'search', 'search')."' method='post' name='sfsearch' data-id='' data-type='form' data-min='".SPSEARCHMIN."'>";
	$out .= "<div class='$tagClass'>";

	# Add a close button if using a mobile phone
	if (SP()->core->device == 'mobile') {
		$out .= "<div class='spRight'>";
		$out .= "<a id='spPanelClose' href='#'></a>";
		$out .= "</div>";
	}

	$terms = (isset(SP()->rewrites->pageData['searchvalue']) && SP()->rewrites->pageData['searchtype'] != 4 && SP()->rewrites->pageData['searchtype'] != 5) ? SP()->rewrites->pageData['searchvalue'] : '';
	$out .= "<input type='text' id='searchvalue' class='$inputClass' size='$inputWidth' name='searchvalue' value='$terms' placeholder='$placeHolder...' />";
	$out .= "<a rel='nofollow' id='$submitId' class='$submitClass spSearchSubmit' title='$toolTip' data-id='$submitId' data-type='link' data-min='".SPSEARCHMIN."'>";
	if (!empty($icon)) {
		$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	}
	$out .= "$submitLabel</a>";

	$out .= sp_InsertBreak('echo=0');

	$out .= "<a class='$advSearchLinkClass spAdvancedSearchForm' ";
	if (!empty($advSearchLink)) {
		$out .= "href='$advSearchLink'>";
	} else {
		$out .= " data-id='$advSearchId'>";
	}
	$out .= "$advancedLabel</a>";

	# are there search results we can return to?
	if (!isset($_GET['search']) && !empty($lastSearchLabel)) {
		$r = SP()->cache->get('search');
		if ($r) {
			$p   = $r[0]['page'];
			$url = $r[0]['url']."&amp;search=$p";
			if($useSeperator) $out .= "<span class='spSearchLinkSep'>|</span>";
			$out .= "<a class='$lastSearchLinkClass' rel='nofollow' href='$url'>$lastSearchLabel</a>";
		}
	}

	$out .= "</div>";
	$out .= sp_InsertBreak('echo=0');
	$out .= "<div id='$advSearchId' class='$advSearchClass'>".sp_inline_search_form($a).'</div>';
	$out .= "</form>";

	# finish it up
	$out = apply_filters('sph_SearchForm', $out, $a);

	# end full search form outer container
	$out .= '</div>';

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GoToTop()
#	Displays link to top of forum page
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_GoToTop($args = '', $label = '', $toolTip = '') {
	$defs = array('tagClass'  => 'spGoToTop',
	              'icon'      => 'sp_ArrowUp.png',
	              'iconClass' => 'spGoToTop',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GoToTop_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass  = esc_attr($tagClass);
	$iconClass = esc_attr($iconClass);
	$icon      = sanitize_file_name($icon);
	$echo      = (int) $echo;
	if (!empty($label)) $label = SP()->displayFilters->title($label);
	if (!empty($toolTip)) $toolTip = esc_attr($toolTip);

	# render the go to bottom link
	$out = "<div class='$tagClass'>";
	$out .= "<a class='$tagClass' href='#spForumTop'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon, $toolTip);
	$out .= "$label</a>";
	$out .= "</div>";

	$out = apply_filters('sph_GoToTop', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GoToBottom()
#	Displays link to bottom of forum page
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_GoToBottom($args = '', $label = '', $toolTip = '') {
	$defs = array('tagClass'  => 'spGoToBottom',
	              'icon'      => 'sp_ArrowDown.png',
	              'iconClass' => 'spGoToBottom',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_GoToBottom_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass  = esc_attr($tagClass);
	$iconClass = esc_attr($iconClass);
	$icon      = sanitize_file_name($icon);
	$echo      = (int) $echo;

	if (!empty($label)) $label = SP()->displayFilters->title($label);
	if (!empty($toolTip)) $toolTip = esc_attr($toolTip);

	# render the go to bottom link
	$out = "<div class='$tagClass'>";
	$out .= "<a class='$tagClass spGoBottom'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon, $toolTip);
	$out .= "$label</a>";
	$out .= "</div>";

	$out = apply_filters('sph_GoToBottom', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_AllRSSButton()
#	Display All Forum RSS Button
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_AllRSSButton($args = '', $label = '', $toolTip = '') {
	if (!SP()->auths->get('view_forum')) return;

	$defs = array('tagId'     => 'spAllRSSButton',
	              'tagClass'  => 'spLink',
	              'icon'      => 'sp_Feed.png',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_AllRSSButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$toolTip   = esc_attr($toolTip);
	$echo      = (int) $echo;

	# only display all rss feed if at least one forum has rss on
	$forums = SP()->DB->table(SPFORUMS, 'forum_rss_private=0');
	if ($forums) {
		$rssUrl = SP()->options->get('sfallRSSurl');
		if (empty($rssUrl)) {
			$rssopt = SP()->options->get('sfrss');
			if ($rssopt['sfrssfeedkey'] && isset(SP()->user->thisUser->feedkey)) {
				$rssUrl = trailingslashit(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).user_trailingslashit(SP()->user->thisUser->feedkey);
			} else {
				$rssUrl = SP()->spPermalinks->build_url('', '', 0, 0, 0, 1);
			}
		}
	} else {
		return;
	}

	$out = "<a class='$tagClass' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	$out = apply_filters('sph_AllRSSButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumLockdown()
#	Display Message when complete Forum	 is locked down
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_ForumLockdown($args = '', $Message = '') {
	if (SP()->core->forumData['lockdown'] == false) return;

	$defs = array('tagId'    => 'spForumLockdown',
	              'tagClass' => 'spMessage',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumLockdown_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId    = esc_attr($tagId);
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;

	$out = "<div id='$tagId' class='$tagClass'>".SP()->displayFilters->title($Message)."</div>";
	$out = apply_filters('sph_ForumLockdown', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_RecentPostList()
#	Displays the recent post list (as used on front page by default)
#	Scope:	Forum
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_RecentPostList($args = '', $label = '') {
	# check if group view is set as this may be called from elsewhere
	if (isset(SP()->forum->view->groups) && SP()->forum->view->groups->groupViewStatus == 'no access') return;

	$defs = array('tagId'      => 'spRecentPostList',
	              'tagClass'   => 'spRecentPostSection',
	              'labelClass' => 'spMessage',
	              'template'   => 'spListView.php',
	              'show'       => 20,
	              'group'      => 0,
	              'admins'     => 0,
	              'mods'       => 1,
	              'first'      => 0,
	              'get'        => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_RecentPostList_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$template   = sanitize_file_name($template);
	$show       = (int) $show;
	$group      = (int) $group;
	$admins     = (int) $admins;
	$mods       = (int) $mods;
	$first      = (int) $first;
	$label      = SP()->displayFilters->title($label);
	$get        = (int) $get;

	if ((!$admins && SP()->user->thisUser->admin) || (!$mods && SP()->user->thisUser->moderator)) return;

	echo "<div id='$tagId' class='$tagClass'>";
	echo "<div class='$labelClass'>$label</div>";
	$topics = (!empty(SP()->user->thisUser->newposts['topics'])) ? SP()->user->thisUser->newposts['topics'] : '';

	if ($get) return $topics;

	SP()->forum->view->listTopics = new spcTopicList($topics, $show, $group, '', $first, 1, 'recent posts');

	# special filter for list view result set
	do_action_ref_array('sph_RecentPostListResults', array(&SP()->forum->view->listTopics));

	sp_load_template($template);
	echo '</div>';
}

# --------------------------------------------------------------------------------------
#
#	sp_Acknowledgements()
#	Display Forum acknowledgements popup and url links
#	Scope:	Site
#	Version: 5.0
#		5.2 - showPopup added to stop popup list link
#
# --------------------------------------------------------------------------------------

function sp_Acknowledgements($args = '', $label = '', $toolTip = '', $siteToolTip = '') {
	$defs = array('tagId'     => 'spAck',
	              'tagClass'  => 'spAck',
	              'icon'      => 'sp_Information.png',
	              'iconClass' => 'spIcon',
	              'linkClass' => 'spLink',
	              'showPopup' => 1,
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_AcknowledgementsLink_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$icon      = sanitize_file_name($icon);
	$iconClass = esc_attr($iconClass);
	$showPopup = (int) $showPopup;
	$echo      = (int) $echo;

	if (!empty($label)) $label = SP()->displayFilters->title($label);
	if (!empty($toolTip)) $toolTip = esc_attr($toolTip);
	if (!empty($siteToolTip)) $siteToolTip = esc_attr($siteToolTip);

	# build acknowledgements url and render link to SP and popup
	$out = "<div id='$tagId' class='$tagClass'>";
	$out .= "&copy; <a class='spLink' title='$siteToolTip' href='https://simple-press.com' target='_blank'>Simple:Press</a>";
	if ($showPopup) {
		$site = wp_nonce_url(SPAJAXURL.'spAckPopup', 'spAckPopup');
		$out .= "&nbsp;&mdash;<a rel='nofollow' class='$linkClass spOpenDialog' title='$toolTip' data-site='$site' data-label='$toolTip' data-width='600' data-height='0' data-align='center'>";
		if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
		$out .= "$label</a>";
	}
	$out .= "</div>";
	if ($showPopup) {
		$out = apply_filters('sph_AcknowledgementsLink', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumTimeZone()
#	Display the timezone of the forum
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_ForumTimeZone($args = '', $label = '') {
	$defs = array('tagClass' => 'spForumTimeZone',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumTimeZone_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# render the forum timezone
	$tz = get_option('timezone_string');
	if (empty($tz)) $tz = 'UTC '.get_option('gmt_offset');

	if ($get) return $tz;

	$out = "<div class='$tagClass'>";
	if (!empty($label)) $out .= '<span class="spTimeZoneLabel">'.SP()->displayFilters->title($label).'</span>';
	$out .= $tz;
	$out .= '</div>';
	$out = apply_filters('sph_ForumTimeZone', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UserTimeZone()
#	Display the timezone of the forum
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_UserTimeZone($args = '', $label = '') {
	if (SP()->user->thisUser->guest) return;

	$defs = array('tagClass' => 'spUserTimeZone',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UserTimeZone_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;

	# render the user timezone
	$tz = (!empty(SP()->user->thisUser->timezone_string)) ? SP()->user->thisUser->timezone_string : get_option('timezone_string');

	if ($get) return $tz;

	$out = "<div class='$tagClass'>";
	if (!empty($label)) $out .= '<span class="spTimeZoneLabel">'.SP()->displayFilters->title($label).'</span>';
	$out .= $tz;
	$out .= '</div>';
	$out = apply_filters('sph_UserTimeZone', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_OnlineStats()
#	Display the online stats
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_OnlineStats($args = '', $mostLabel = '', $currentLabel = '', $browsingLabel = '', $guestLabel = '') {
	$defs = array('pMostClass'     => 'spMostOnline',
	              'pCurrentClass'  => 'spCurrentOnline',
	              'pBrowsingClass' => 'spCurrentBrowsing',
				  'pGuestsClass' 	=> 'spGuestsOnline',
	              'linkNames'      => 1,
	              'usersOnly'      => 0,
				  'stack'          => 0,
	              'echo'           => 1,
	              'get'            => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_OnlineStats_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$pMostClass     = esc_attr($pMostClass);
	$pCurrentClass  = esc_attr($pCurrentClass);
	$pBrowsingClass = esc_attr($pBrowsingClass);
	$pGuestsClass 	= esc_attr($pGuestsClass);
	$linkNames      = (int) $linkNames;
	$usersOnly      = (int) $usersOnly;
	$stack			= (int) $stack;
	$echo           = (int) $echo;
	$get            = (int) $get;
	if (!empty($mostLabel)) $mostLabel = SP()->displayFilters->title($mostLabel);
	if (!empty($currentLabel)) $currentLabel = SP()->displayFilters->title($currentLabel);
	if (!empty($browsingLabel)) $browsingLabel = SP()->displayFilters->title($browsingLabel);
	if (!empty($guestLabel)) $guestLabel = SP()->displayFilters->title($guestLabel);
	
	# Stack labels and data on top of each other?
	($stack ? $stackAtt = '<br />' : $stackAtt = ' ');

	# grab most online stat and update if new most
	$max    = SP()->options->get('spMostOnline');
	$online = SP()->DB->count(SPTRACK);
	if ($online > $max) {
		$max = $online;
		SP()->options->update('spMostOnline', $max);
	}

	require_once SP_PLUGIN_DIR.'/forum/database/sp-db-statistics.php';
	$members = sp_get_members_online();

	if ($get) {
		$getData          = new stdClass();
		$getData->max     = $max;
		$getData->members = $members;

		return $getData;
	}

	# render the max online stats
	$out = "<div class='$pMostClass'><span class='spMostClassLabel'>$mostLabel $stackAtt</span><span class='spMostClassData'>$max</span></div>";

	# render the current online stats
	$browse = '';
	$out .= "<div class='$pCurrentClass'><span class='spCurrentClassLabel'>$currentLabel $stackAtt</span>";

	# members online
	if ($members) {
		$firstOnline   = true;
		$firstBrowsing = true;
		$memberCount = 0;
		$spMemberOpts  = SP()->options->get('sfmemberopts');
		foreach ($members as $user) {
			$userOpts = unserialize($user->user_options);
			if (!isset($userOpts['hidestatus'])) $userOpts['hidestatus'] = false;
			if (SP()->user->thisUser->admin || !$spMemberOpts['sfhidestatus'] || !$userOpts['hidestatus']) {
				if (!$firstOnline) $out .= ', ';
				$out .= "<span class='spOnlineUser ".$user->display."'>";
				$out .= SP()->user->name_display($user->trackuserid, SP()->displayFilters->name($user->display_name), $linkNames);
				$out .= '</span>';
				$firstOnline = false;
				$memberCount++;

				# Set up the members browsing current item list while here
				# Check that pageview is  set as this might be called from outside of the forum
				if (!empty(SP()->rewrites->pageData['pageview'])) {
					if ((SP()->rewrites->pageData['pageview'] == 'forum' && $user->forum_id == SP()->rewrites->pageData['forumid']) || (SP()->rewrites->pageData['pageview'] == 'topic' && $user->topic_id == SP()->rewrites->pageData['topicid'])) {
						if (!$firstBrowsing) $browse .= ', ';
						$browse .= "<span class='spOnlineUser ".$user->display."'>";
						$browse .= SP()->user->name_display($user->trackuserid, SP()->displayFilters->name($user->display_name), $linkNames);
						$browse .= '</span>';
						$firstBrowsing = false;
					}
				}
			}
		}
	}

	# guests online
	if (!$usersOnly && $online && ($online > count($members))) {
		$guests = ($online - count($members));
		//$out .= "<br />$guests <div class='spOnlineUser spType-Guest'>$guestLabel $stackAtt</div>";
		$out .= "<div class='$pGuestsClass'><span class='spGuestsClassLabel'>$guestLabel $stackAtt</span><span class='spGuestsClassData'>$guests</span></div>";		
	}
	$out .= '</div>';

	# Members and guests browsing
	$out .= "<div class='$pBrowsingClass'>";
	$guestBrowsing = sp_guests_browsing();
	if ($browse || $guestBrowsing) $out .= "<span class='spCurrentBrowsingLabel'>$browsingLabel $stackAtt</span>";
	if ($browse) $out .= $browse;
	if (!$usersOnly && $guestBrowsing != 0) $out .= "<br />$guestBrowsing <span class='spOnlineUser spType-Guest'>$guestLabel</span>";
	$out .= "</div>";

	# finish it up
	$out = apply_filters('sph_OnlineStats', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_DeviceStats()
#	Display the device being used stats
#	Scope:	Site
#	Version: 5.3
#
# --------------------------------------------------------------------------------------

function sp_DeviceStats($args = '', $statLabel = '', $phoneLabel = '', $tabletLabel = '', $desktopLabel = '') {
	$defs = array('tagClass' => 'spDeviceStats',
	              'echo'     => 1,
	              'get'      => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_DeviceStats_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;
	$get      = (int) $get;
	if (!empty($statLabel)) $statLabel = SP()->displayFilters->title($statLabel);
	if (!empty($phoneLabel)) $phoneLabel = SP()->displayFilters->title($phoneLabel);
	if (!empty($tabletLabel)) $tabletLabel = SP()->displayFilters->title($tabletLabel);
	if (!empty($desktopLabel)) $desktopLabel = SP()->displayFilters->title($desktopLabel);

	# grab device stats data
	$device = SP()->DB->select('SELECT device, COUNT(device) AS total FROM '.SPTRACK.' GROUP BY device');
	if (empty($device)) return;
	if ($get) return $device;

	# render the device stats
	$out   = "<p class='$tagClass'><span>$statLabel</span>";
	$first = true;
	foreach ($device as $d) {
		if (!$first) $out .= ', ';
		$first = false;

		switch ($d->device) {
			case 'D':
				$out .= "$desktopLabel (".$d->total.")";
				break;
			case 'M':
				$out .= "$phoneLabel (".$d->total.")";
				break;
			case 'T':
				$out .= "$tabletLabel (".$d->total.")";
				break;
		}
	}
	$out .= "</p>";

	# finish it up
	$out = apply_filters('sph_DeviceStats', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumStats()
#	Display the forum stats section
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_ForumStats($args = '', $titleLabel = '', $groupsLabel = '', $forumsLabel = '', $topicsLabel = '', $postsLabel = '') {
	$defs = array('pTitleClass'  => 'spForumStatsTitle',
	              'pGroupsClass' => 'spGroupsStats',
	              'pForumsClass' => 'spForumsStats',
	              'pTopicsClass' => 'spTopicsStats',
	              'pPostsClass'  => 'spPostsStats',
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ForumStats_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$pTitleClass  = esc_attr($pTitleClass);
	$pGroupsClass = esc_attr($pGroupsClass);
	$pForumsClass = esc_attr($pForumsClass);
	$pTopicsClass = esc_attr($pTopicsClass);
	$pPostsClass  = esc_attr($pPostsClass);
	$echo         = (int) $echo;
	$get          = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);
	if (!empty($groupsLabel)) $groupsLabel = SP()->displayFilters->title($groupsLabel);
	if (!empty($forumsLabel)) $forumsLabel = SP()->displayFilters->title($forumsLabel);
	if (!empty($topicsLabel)) $topicsLabel = SP()->displayFilters->title($topicsLabel);
	if (!empty($postsLabel)) $postsLabel = SP()->displayFilters->title($postsLabel);

	# get stats for forum stats
	$counts = SP()->options->get('spForumStats');

	if ($get) return $counts;

	# render the forum stats
	$out = "<div class='$pTitleClass'>$titleLabel</div>";
	$out .= "<div class='$pGroupsClass'>".$groupsLabel . ($counts->groups ?? 0) .'</div>';
	$out .= "<div class='$pForumsClass'>".$forumsLabel . ($counts->forums ?? 0) .'</div>';
	$out .= "<div class='$pTopicsClass'>".$topicsLabel . ($counts->topics ?? 0) .'</div>';
	$out .= "<div class='$pPostsClass'>".$postsLabel . ($counts->posts ?? 0) ."</div>";

	# finish it up
	$out = apply_filters('sph_ForumStats', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MembershipStats()
#	Display the membership stats section
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_MembershipStats($args = '', $titleLabel = '', $membersLabel = '', $guestsLabel = '', $modsLabel = '', $adminsLabel = '') {
	$defs = array('pTitleClass'   => 'spMembershipStatsTitle',
	              'pMembersClass' => 'spMemberStats',
	              'pGuestsClass'  => 'spGuestsStats',
	              'pModsClass'    => 'spModsStats',
	              'pAdminsClass'  => 'spAdminsStats',
	              'echo'          => 1,
	              'get'           => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_MembershipStats_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$pTitleClass   = esc_attr($pTitleClass);
	$pMembersClass = esc_attr($pMembersClass);
	$pGuestsClass  = esc_attr($pGuestsClass);
	$pModsClass    = esc_attr($pModsClass);
	$pAdminsClass  = esc_attr($pAdminsClass);
	$echo          = (int) $echo;
	$get           = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);

	# get stats for membership stats
	$stats = SP()->options->get('spMembershipStats');

	if ($get) return $stats;

	if (!empty($guestsLabel)) $guestsLabel = SP()->displayFilters->title(str_replace('%COUNT%', $stats['guests'] ?? '', $guestsLabel));
	if (!empty($membersLabel)) $membersLabel = SP()->displayFilters->title(str_replace('%COUNT%', $stats['members'] ?? 0, $membersLabel));
	if (!empty($modsLabel)) $modsLabel = SP()->displayFilters->title(str_replace('%COUNT%', $stats['mods'] ?? '', $modsLabel));
	if (!empty($adminsLabel)) $adminsLabel = SP()->displayFilters->title(str_replace('%COUNT%', $stats['admins'] ?? '', $adminsLabel));

	# render the forum stats
	$out = "<div class='$pTitleClass'>$titleLabel</div>";
	$out .= "<div class='$pGuestsClass'>$guestsLabel</div>";
	$out .= "<div class='$pMembersClass'>$membersLabel</div>";
	$out .= "<div class='$pModsClass'>$modsLabel</div>";
	$out .= "<div class='$pAdminsClass'>$adminsLabel</div>";

	# finish it up
	$out = apply_filters('sph_MembershipStats', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_TopPostersStats()
#	Display the top poster stats section
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_TopPostersStats($args = '', $titleLabel = '') {
	$defs = array('pTitleClass'  => 'spTopPosterStatsTitle',
	              'pPosterClass' => 'spPosterStats',
	              'linkNames'    => 1,
	              'echo'         => 1,
	              'get'          => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_TopStats_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$pTitleClass  = esc_attr($pTitleClass);
	$pPosterClass = esc_attr($pPosterClass);
	$linkNames    = (int) $linkNames;
	$echo         = (int) $echo;
	$get          = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);

	# get stats for top poster stats
	$topPosters = SP()->options->get('spPosterStats');

	if ($get) return $topPosters;

	# render the forum stats
	$out = "<div class='$pTitleClass'>$titleLabel</div>";
	if ($topPosters) {
		foreach ($topPosters as $poster) {
			if ($poster->posts > 0) $out .= "<div class='$pPosterClass'>".SP()->user->name_display($poster->user_id, SP()->displayFilters->name($poster->display_name), $linkNames).': '.$poster->posts.'</div>';
		}
	}

	# finish it up
	$out = apply_filters('sph_TopStats', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NewMembers()
#	Display the latest new members
#	Scope:	Site
#	Version: 5.0
#		5.5.1	-	added 'list' argument
#		5.5.1	-	added 'pPosterClass' argument
#
# --------------------------------------------------------------------------------------

function sp_NewMembers($args = '', $titleLabel = '') {
	$defs = array('tagClass'     => 'spNewMembers',
	              'pTitleClass'  => 'spNewMembersTitle',
	              'pPosterClass' => 'spPosterStats',
	              'spanClass'    => 'spNewMembersList',
	              'linkNames'    => 1,
	              'list'         => 0,
	              'echo'         => 1,
	              'get'          => 0);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_NewMembers_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass     = esc_attr($tagClass);
	$pTitleClass  = esc_attr($pTitleClass);
	$pPosterClass = esc_attr($pPosterClass);
	$spanClass    = esc_attr($spanClass);
	$linkNames    = (int) $linkNames;
	$list         = (int) $list;
	$echo         = (int) $echo;
	$get          = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);

	# render the forum stats
	$out = '';
	if (!$list) $out .= "<div class='$tagClass'>";
	$out .= "<div class='$pTitleClass'><span class='$pTitleClass'>$titleLabel</span></div>";

	$newMemberList = SP()->options->get('spRecentMembers');

	if ($get) return $newMemberList;

	if ($newMemberList) {
		$first = true;
		if (!$list) $out .= "<span class='$spanClass'>";
		foreach ($newMemberList as $member) {
			$comma = (!$first && !$list) ? ', ' : '';
			if ($list) $out .= "<div class='$pPosterClass'>";
			$out .= SP()->user->name_display($member['id'], $comma.SP()->displayFilters->name($member['name']), $linkNames);
			if ($list) $out .= '</div>';
			$first = false;
		}
		if (!$list) $out .= '</span>';
	}

	# finish it up
	if (!$list) $out .= "</div>";
	$out = apply_filters('sph_NewMembers', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ModsList()
#	Display the list of moderators
#	Scope:	Site
#	Version: 5.0
#		5.5.1	-	Added 'postCount'
#
# --------------------------------------------------------------------------------------

function sp_ModsList($args = '', $titleLabel = '') {

	# get stats for moderator stats
	$mods = SP()->options->get('spModStats');
	if (empty($mods)) return;

	$defs = array('tagClass'    => 'spModerators',
	              'pTitleClass' => 'spModeratorsTitle',
	              'spanClass'   => 'spModeratorList',
	              'linkNames'   => 1,
	              'postCount'   => 1,
	              'stack'       => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_ModsList_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$pTitleClass = esc_attr($pTitleClass);
	$spanClass   = esc_attr($spanClass);
	$linkNames   = (int) $linkNames;
	$postCount   = (int) $postCount;
	$stack       = (int) $stack;
	$echo        = (int) $echo;
	$get         = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);

	if ($get) return $mods;

	$delim = ($stack) ? '<br />' : ', ';

	# render the moderators list
	$out = "<div class='$tagClass'>";
	$out .= "<div class='$pTitleClass'><span class='$pTitleClass'>$titleLabel</span>";
	if ($mods) {
		$first = true;
		if ($stack) $out .= $delim;
		$out .= "<span class='$spanClass'>";
		foreach ($mods as $mod) {
			$comma = (!$first) ? $delim : '';
			if ($mod['posts'] < 0) $mod['posts'] = 0;
			$userPosts = ($postCount) ? ': '.$mod['posts'] : '';
			$out .= SP()->user->name_display($mod['user_id'], $comma.SP()->displayFilters->name($mod['display_name']).$userPosts, $linkNames);
			$first = false;
		}
		$out .= '</span>';
	}
	$out .= '</div>';

	# finish it up
	$out .= "</div>";
	$out = apply_filters('sph_ModsList', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_AdminsList()
#	Display the list of administrators
#	Scope:	Site
#	Version: 5.0
#		5.5.1	-	Added 'postCount'
# --------------------------------------------------------------------------------------

function sp_AdminsList($args = '', $titleLabel = '') {
	$defs = array('tagClass'    => 'spAdministrators',
	              'pTitleClass' => 'spAdministratorsTitle',
	              'spanClass'   => 'spAdministratorsList',
	              'linkNames'   => 1,
	              'postCount'   => 1,
	              'stack'       => 0,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_AdminsList_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$pTitleClass = esc_attr($pTitleClass);
	$spanClass   = esc_attr($spanClass);
	$linkNames   = (int) $linkNames;
	$postCount   = (int) $postCount;
	$stack       = (int) $stack;
	$echo        = (int) $echo;
	$get         = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);

	# get stats for admin stats
	$admins = SP()->options->get('spAdminStats');

	if ($get) return $admins;

	$delim = ($stack) ? '<br />' : ', ';

	# render the admins list
	$out = "<div class='$tagClass'>";
	$out .= "<div class='$pTitleClass'><span class='$pTitleClass'>$titleLabel</span>";
	if ($admins) {
		$first = true;
		if ($stack) $out .= $delim;
		$out .= "<span class='$spanClass'>";
		foreach ($admins as $admin) {
			$comma = (!$first) ? $delim : '';
			if ($admin['posts'] < 0) $admin['posts'] = 0;
			$userPosts = ($postCount) ? ': '.$admin['posts'] : '';
			$out .= SP()->user->name_display($admin['user_id'], $comma.SP()->displayFilters->name($admin['display_name']).$userPosts, $linkNames);
			$first = false;
		}
		$out .= '</span>';
	}
	$out .= '</div>';

	# finish it up
	$out .= "</div>";
	$out = apply_filters('sph_AdminsList', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UserGroupList()
#	Display member list of specified user group in row beneath stats
#	Scope:	Site
#	Version: 5.5.1
#
# --------------------------------------------------------------------------------------

function sp_UserGroupList($args = '', $titleLabel = '', $userGroup = 0) {
	if (!$userGroup) return;

	$defs = array('tagClass'    => 'spUserGroupList',
	              'pTitleClass' => 'spUserGroupListTitle',
	              'spanClass'   => 'spUserGroupListList',
	              'postCount'   => 1,
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UserGroupList_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass    = esc_attr($tagClass);
	$pTitleClass = esc_attr($pTitleClass);
	$spanClass   = esc_attr($spanClass);
	$postCount   = (int) $postCount;
	$echo        = (int) $echo;
	$userGroup   = (int) $userGroup;
	$get         = (int) $get;
	if (!empty($titleLabel)) $titleLabel = SP()->displayFilters->title($titleLabel);

	# get user group member list
	$sql     = "SELECT ".SPMEMBERSHIPS.".user_id, display_name, posts
			FROM ".SPMEMBERSHIPS."
			JOIN ".SPMEMBERS." ON ".SPMEMBERS.".user_id = ".SPMEMBERSHIPS.".user_id
			WHERE ".SPMEMBERSHIPS.".usergroup_id=".$userGroup."
			ORDER BY display_name";
	$members = SP()->DB->select($sql);

	if ($get) return $members;

	# render the members list
	$out = "<div class='$tagClass'>";
	$out .= "<p class='$pTitleClass'><span class='$pTitleClass'>$titleLabel</span>";
	if ($members) {
		$first = true;
		$out .= "<span class='$spanClass'>";
		foreach ($members as $member) {
			$comma = (!$first) ? ', ' : '';
			if ($member->posts < 0) $member->posts = 0;
			$userPosts = ($postCount) ? ': '.$member->posts : '';
			$out .= SP()->user->name_display($member->user_id, $comma.SP()->displayFilters->name($member->display_name).$userPosts);
			$first = false;
		}
		$out .= '</span>';
	}
	$out .= '</p>';

	# finish it up
	$out .= "</div>";
	$out = apply_filters('sph_UserGroupList', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_Signature()
#	Display a specified signature
#	Scope:	Site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_Signature($args, $sig) {
	$defs = array('tagClass' => 'spSignature',
	              'echo'     => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_Signature_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass = esc_attr($tagClass);
	$echo     = (int) $echo;

	# force sig to have no follow in links and follow size limits
	$sig = SP()->saveFilters->nofollow($sig);

	# render the signature
	$out = "<div class='$tagClass'>";
	$out .= $sig;
	$out .= '</div>'."";

	$out = apply_filters('sph_Signature', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_OnlineStatus()
#	Display a users online status
#	Scope:	site
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_OnlineStatus($args = '', $user = null, $userProfile = '') {
	$defs = array('tagClass'    => 'spOnlineStatus',
	              'onlineIcon'  => 'sp_UserOnline.png',
	              'offlineIcon' => 'sp_UserOffline.png',
	              'echo'        => 1,
	              'get'         => 0,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_OnlineStatus_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$onlineIcon  = sanitize_file_name($onlineIcon);
	$offlineIcon = sanitize_file_name($offlineIcon);
	$user        = (int) $user;
	$echo        = (int) $echo;
	$get         = (int) $get;

	# output display name
	$out = '';
	if (empty($userProfile)) $userProfile = SP()->user->get($user);

	$spMemberOpts = SP()->options->get('sfmemberopts');
	if ((SP()->user->thisUser->admin || (!$spMemberOpts['sfhidestatus'] || !$userProfile->hidestatus)) && sp_is_online($user)) {
		$icon = SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($onlineIcon));
		if ($get) return true;
	} else {
		$icon = SP()->theme->paint_icon('', SPTHEMEICONSURL, sanitize_file_name($offlineIcon));
		if ($get) return false;
	}

	$out .= $icon;
	$out = apply_filters('sph_OnlineStatus', $out, $user, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_AddButton()
#	Display a user defined button
#	Scope:	site
#	Version: 5.5.1
# --------------------------------------------------------------------------------------

function sp_AddButton($args = '', $label = '', $toolTip = '', $perm = '', $buttonID = '') {
	$defs = array('tagId'      => $buttonID,
	              'tagClass'   => 'spButton',
	              'link'       => SP()->spPermalinks->get_url(),
	              'icon'       => '',
	              'iconClass'  => 'spIcon',
	              'mobileMenu' => 0,
	              'echo'       => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_AddButton_args', $a);
	extract($a, EXTR_SKIP);

	# verify optional 'simple' permission check
	if (!empty($perm) && !SP()->auths->get($perm)) return;

	# allow for complex permission checking
	$auth = apply_filters('sph_add_button_auth_check', true, $buttonID, $a);
	if (!$auth) return;

	# sanitize before use
	$tagId      = esc_attr($tagId);
	$tagClass   = esc_attr($tagClass);
	$link       = esc_url($link);
	$icon       = sanitize_file_name($icon);
	$iconClass  = esc_attr($iconClass);
	$mobileMenu = (int) $mobileMenu;
	$echo       = (int) $echo;

	$toolTip = esc_attr($toolTip);

	$br  = ($mobileMenu) ? '<br />' : '';
	$out = '';

	if ($mobileMenu) if ($mobileMenu) $out .= sp_open_grid_cell();
	$out .= "<a class='$tagClass' id='$tagId' title='$toolTip' href='$link'>";
	if (!empty($icon)) $out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon).$br;
	if (!empty($label)) $out .= SP()->displayFilters->title($label);
	$out .= "</a>";
	if ($mobileMenu) $out .= sp_close_grid_cell();

	$out = apply_filters('sph_AddButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_page_prev()
#	sp_page_next()
#	sp_page_url()
#
#	Internally used page link processing - can not be called directly
#	Version: 5.0
#
# --------------------------------------------------------------------------------------

function sp_page_prev($curPage, $pnShow, $baseUrl, $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search = '', $ug = '') {
	$start = max($curPage - $pnShow, 1);
	$end   = $curPage - 1;
	$out   = '';

	if ($start > 1) {
		$out .= sp_page_url(1, $baseUrl, 'none', $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
		$out .= sp_page_url($curPage - 1, $baseUrl, 'prev', $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
	}

	if ($end > 0) {
		for ($i = $start; $i <= $end; $i++) {
			$out .= sp_page_url($i, $baseUrl, 'none', $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
		}
	}

	return $out;
}

function sp_page_next($curPage, $totalPages, $pnShow, $baseUrl, $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search = '', $ug = '') {
	$start = $curPage + 1;
	$end   = min($curPage + $pnShow, $totalPages);
	$out   = '';

	if ($start <= $totalPages) {
		for ($i = $start; $i <= $end; $i++) {
			$out .= sp_page_url($i, $baseUrl, 'none', $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
		}
		if ($end < $totalPages) {
			$out .= sp_page_url($curPage + 1, $baseUrl, 'next', $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
			$out .= sp_page_url($totalPages, $baseUrl, 'none', $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
		}
	}

	return $out;
}

function sp_page_url($thisPage, $baseUrl, $iconType, $linkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug) {
	$toolTip = str_ireplace('%PAGE%', $thisPage, $toolTip);

	$out = "<a href='";
	if (is_int($search)) { # normal forum search puts page number in search query arg
		$out .= user_trailingslashit($baseUrl.'&amp;search='.$thisPage);
	} else {
		$url = ($thisPage > 1) ? trailingslashit($baseUrl).'page-'.$thisPage : $baseUrl;
		$url = user_trailingslashit($url);
		$url = apply_filters('sph_page_link', $url, $thisPage);
		if (!empty($search)) { # members list search
			$param['msearch'] = $search;
			$url              = add_query_arg($param, esc_url($url));
			$url              = SP()->filters->ampersand($url);
		}
		if (!empty($ug)) { # members list usergroup
			$param['ug'] = $ug;
			$url         = add_query_arg($param, esc_url($url));
			$url         = SP()->filters->ampersand($url);
		}
		$out .= $url;
	}

	Switch ($iconType) {
		case 'none':
			$out .= "' class='$linkClass' title='$toolTip'>$thisPage</a>";
			break;
		case 'prev':
			if (!empty($prevIcon)) {
				$out .= "' class='$linkClass $iconClass'>$prevIcon</a>";
			} else {
				$out = " ... ";
			}
			break;
		case 'next':
			if (!empty($nextIcon)) {
				$out .= "' class='$linkClass $iconClass'>$nextIcon</a>";
			} else {
				$out = "<span class='spHSpacer'>&#8230;</span>";
			}
			break;
	}

	return $out;
}

# --------------------------------------------------------------------------------------
#
#	sp_UniversalTitle()
#	Displays a defined word / title
#	Scope:	Site
#	Version: 5.5.2
#
# --------------------------------------------------------------------------------------

function sp_UniversalTitle($args = '', $label = '') {
	$defs = array('tagClass'   => 'spUniversalLabel',
	              'labelClass' => 'spInRowLabel',
	              'echo'       => 1);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_UniversalTitle_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagClass   = esc_attr($tagClass);
	$labelClass = esc_attr($labelClass);
	$echo       = (int) $echo;

	$out = "<div class='$tagClass'>";
	$out .= "<span class='$labelClass'>".SP()->displayFilters->title($label)."</span>";
	$out .= "</div>";
	$out = apply_filters('sph_UniversalTitle', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#	sp_AddIcon()
#	Display a user defined icon (icon must be in image in sp theme)
#	Scope:	site
#	Version: 5.5.5
# --------------------------------------------------------------------------------------

function sp_AddIcon($args = '', $toolTip = '') {
	$defs = array('tagId'     => '',
	              'tagClass'  => '',
	              'icon'      => '',
	              'iconClass' => 'spIcon',
	              'echo'      => 1,);
	$a    = wp_parse_args($args, $defs);
	$a    = apply_filters('sph_AddIcon_args', $a);
	extract($a, EXTR_SKIP);

	# must have an icon passed
	$icon = sanitize_file_name($icon);
	if (empty($icon)) return '';

	# sanitize before use
	$tagId     = esc_attr($tagId);
	$tagClass  = esc_attr($tagClass);
	$iconClass = esc_attr($iconClass);
	$echo      = (int) $echo;

	$toolTip = esc_attr($toolTip);

	$out = "<div id='$tagId' class='$tagClass' title='$toolTip'>";
	$out .= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	$out .= "</div>";

	$out = apply_filters('sph_AddIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ------------------------------------------------------------------
# sp_build_avatar_display()
#
# Version: 5.0
# Will attach profile, website or nothing to avatar
#	userid:		id of the user
#	avatar:		Avatar display code
#   link:       attachment to make (profile, website, none)
# ------------------------------------------------------------------

function sp_build_avatar_display($userid, $avatar, $link) {
	switch ($link) {
		case 'profile':
			# for profiles, do we have a user and can current user view a profile?
			$forumid = (!empty(SP()->rewrites->pageData['forumid'])) ? SP()->rewrites->pageData['forumid'] : '';
			if (!empty($userid) && SP()->auths->get('view_profiles', $forumid)) $avatar = sp_attach_user_profile_link($userid, $avatar);
			break;

		case 'website':
			# for website, do we have a user?
			if (!empty($userid)) $avatar = sp_attach_user_web_link($userid, $avatar);
			break;

		default:
			# fire action for plugins that might add other display type
			$avatar = apply_filters('sph_BuildAvatarDisplay_'.$link, $avatar, $userid);
			break;
	}

	$avatar = apply_filters('sph_BuildAvatarDisplay', $avatar, $userid);

	return $avatar;
}

# ------------------------------------------------------------------
# sp_attach_user_web_link()
#
# Version: 5.0
# Create a link to a users website if they have entered one in their
# profile record.
#	userid:		id of the user
#	targetitem:	user name, avatar or web icon - sent as code
#	returnitem:	return targetitem if nothing found
# ------------------------------------------------------------------

function sp_attach_user_web_link($userid, $targetitem, $returnitem = true) {
	global $session_weblink;

	# is the website url cached?
	$webSite = (empty($session_weblink[$userid])) ? $webSite = SP()->DB->table(SPUSERS, "ID=$userid", 'user_url') : $session_weblink[$userid];
	if (empty($webSite)) $webSite = '#';

	# update cache (may be same)
	$session_weblink[$userid] = $webSite;

	# now attach the website url - ignoring if not defined
	if ($webSite != '#') {
		$webSite = SP()->primitives->check_url($webSite);
		if (!empty($webSite)) {
			$content   = "<a href='$webSite' class='spLink spWebLink' title=''>$targetitem</a>";
			$sffilters = SP()->options->get('sffilters');
			if ($sffilters['sftarget']) $content = SP()->saveFilters->target($content);
			if ($sffilters['sfnofollow']) $content = SP()->saveFilters->nofollow($content);

			return $content;
		}
	}

	# No website link exists
	if ($returnitem) {
		return $targetitem;
	} else {
		return '';
	}
}

# ------------------------------------------------------------------
# sp_attach_user_profile_link()
#
# Version: 5.0
# Create a link to a users profile using the global profile display
# settings
#	userid:		id of the user
#	targetitem:	user name, avatar or web icon - sent as code
# ------------------------------------------------------------------

function sp_attach_user_profile_link($userid, $targetitem) {
	if (!SP()->auths->get('view_profiles')) return $targetitem;

	$title = esc_attr(SP()->primitives->front_text('Profile'));

	$sfprofile = SP()->options->get('sfprofile');
	$mode      = $sfprofile['displaymode'];

	# if display mode is BP or Mingle but they are not active, switch back to popup profile
	require_once ABSPATH.'wp-admin/includes/plugin.php';
	if (($mode == 3 && !is_plugin_active('buddypress/bp-loader.php')) || ($mode == 6 && !is_plugin_active('mingle/mingle.php'))) {
		$mode = 1;
	}

	# for mobiles force a new page if popup is preferred
	if (SP()->core->device == 'mobile' && $mode == 1) $mode = 2;

	switch ($mode) {
		case 1:
			# SF Popup profile
			$site     = wp_nonce_url(SPAJAXURL."profile&amp;targetaction=popup&amp;user=$userid", 'profile');
			$position = 'center';

			return "<a rel='nofollow' class='spLink spOpenDialog' title='$title' data-site='$site' data-label='$title' data-width='100%' data-height='0' data-align='$position'>$targetitem</a>";

		case 2:
			# SF Profile page
			$site = SP()->spPermalinks->get_url('profile/'.$userid);

			return "<a href='$site' class='spLink spProfilePage' title='$title'>$targetitem</a>";

		case 3:
			# BuddyPress profile page
			$user = new WP_User($userid);

			# try to handle BP switches between username and login ussge
			$username = bp_is_username_compatibility_mode() ? $user->user_login : $user->user_nicename;
			if (strstr($username, ' ')) {
				$username = $user->user_nicename;
			} else {
				$username = urlencode($username);
			}

			# build BP user profile based on bp options
			$bp      = get_option('bp-pages');
			$baseurl = get_permalink($bp['members']);

			$site = user_trailingslashit($baseurl.str_replace(' ', '', $username).'/profile');
			$site = apply_filters('sph_buddypress_profile', $site, $user);

			return "<a href='$site' class='spLink spBPProfile' title='$title'>$targetitem</a>";

		case 4:
			# WordPress authors page
			$userkey = SP()->DB->table(SPUSERS, "ID=$userid", 'user_nicename');
			if ($userkey) {
				$site = SPSITEURL.user_trailingslashit('author/'.$userkey);

				return "<a href='$site' class='spLink spWPProfile' title='$title'>$targetitem</a>";
			} else {
				return $targetitem;
			}

		case 5:
			# Handoff to user specified page
			if ($sfprofile['displaypage']) {
				$title = esc_attr(SP()->primitives->front_text('Profile'));
				$out   = "<a href='".$sfprofile['displaypage'];
				if ($sfprofile['displayquery']) $out .= '?'.SP()->displayFilters->title($sfprofile['displayquery']).'='.$userid;
				$out .= "' class='spLink spUserDefinedProfile' title='$title'>$targetitem</a>";
			} else {
				$out = $targetitem;
			}

			return $out;

		case 6:
			# Mingle profile page
			$user = new WP_User($userid);
			$site = SPSITEURL.user_trailingslashit(urlencode($user->user_login));
			$site = apply_filters('sph_mingle_profile', $site, $user);

			return "<a href='$site' class='spLink spMingleProfile' title='$title'>$targetitem</a>";

		default:
			# plugins offering new type?
			$targetitem = apply_filters('AttachUserProfileLink_'.$sfprofile['displaymode'], $targetitem, $userid);

			return $targetitem;
	}
}

# ------------------------------------------------------------------
# sp_build_profile_formlink()
#
# Version: 5.0
# Create a link to the profile form preferred
#	$userid:		id of the user
# ------------------------------------------------------------------

function sp_build_profile_formlink($userid) {
	$sfprofile = SP()->options->get('sfprofile');
	$mode      = $sfprofile['formmode'];

	# if profile mode is BP or Mingle but they are not active, switch back to popup profile
	require_once ABSPATH.'wp-admin/includes/plugin.php';
	if (($mode == 3 && !is_plugin_active('buddypress/bp-loader.php')) || ($mode == 5 && !is_plugin_active('mingle/mingle.php'))) {
		$mode = 1;
	}

	switch ($mode) {
		case 1:
			# SPF form
			$edit = '';
			if ($userid != SP()->user->thisUser->ID) {
				$user = new WP_User($userid);
				$edit = $user->ID.'/edit';
			}
			$site = SP()->spPermalinks->get_url('profile/'.$edit);

			return $site;

		case 2:
			# WordPress form
			return SPHOMEURL.'wp-admin/user-edit.php?user_id='.$userid;

		case 3:
			# BuddyPress profile page
			$user = new WP_User($userid);

			# try to handle BP switches between username and login ussge
			$username = bp_is_username_compatibility_mode() ? $user->user_login : $user->user_nicename;
			if (strstr($username, ' ')) {
				$username = $user->user_nicename;
			} else {
				$username = urlencode($username);
			}

			# build BP user profile based on bp options
			$bp      = get_option('bp-pages');
			$baseurl = get_permalink($bp['members']);

			$site = user_trailingslashit($baseurl.str_replace(' ', '', $username).'/profile');
			$site = apply_filters('sph_buddypress_profile', $site, $user);

			return $site;

		case 4:
			# Handoff to user specified form
			if ($sfprofile['formpage']) {
				$out = $sfprofile['formpage'];
				if ($sfprofile['formquery']) $out .= '?'.SP()->displayFilters->title($sfprofile['formquery']).'='.$userid;
			} else {
				$out = '';
			}

			return $out;

		case 5:
			# Mingle account page
			$user = new WP_User($userid);
			$site = SPSITEURL.user_trailingslashit('account');
			$site = apply_filters('sph_mingle_profile', $site, $user);

			return $site;
	}
}

# --------------------------------------------------------------------------------------
#	Delay population of quick links new topics - mainly for topic view - so that if
#	loading an unread topic it will shw in quick links as read
#	Uses the theme 'sph_FooterEnd' action hook
#	Scope:	forum
#	Version: 5.7.3
# --------------------------------------------------------------------------------------
add_action('sph_FooterEnd', 'populate_quicklinks_script');

function populate_quicklinks_script() {
	if (SP()->core->device == 'mobile' && !current_theme_supports('sp-mobile-quicklinks-off')) {
		return;
	}

	$out = '<div id="qlContent">';
	$out .= sp_PopulateQuickLinksTopic();
	$out .= '</div>';
	echo $out;
}
