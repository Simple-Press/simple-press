<?php
/*
Simple:Press
Ajax call for View Member Profile
$LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
$Rev: 15799 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('profile')) die();

require_once SP_PLUGIN_DIR.'/forum/content/sp-common-control-functions.php';
require_once SP_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php';
require_once SP_PLUGIN_DIR.'/forum/content/sp-profile-view-functions.php';

$userid = (isset($_GET['user'])) ? SP()->filters->integer($_GET['user']) : 0;
$action = (isset($_GET['targetaction'])) ? $_GET['targetaction'] : '';

$out = '';

# is it a popup profile?
if ($action == 'popup' || $action == 'spa_popup' ) {
	if (empty($userid)) {
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid profile request'));
		$out .= SP()->notifications->render_queued();
		$out .= '<div class="sfmessagestrip">';
		$out .= apply_filters('sph_ProfileErrorMsg', SP()->primitives->front_text('Sorry, an invalid profile request was detected'));
		$out .= '</div>';

		return $out;
	}

	sp_SetupUserProfileData($userid);

	echo '<div id="spMainContainer">';
	
	if ($action == 'spa_popup') {
		include 'profile-popup.php';
	} else {
		sp_load_template('spProfilePopupShow.php');
	}
	
	
	echo '</div>';

	die();
}

do_action('sph_ProfileStart', $action);

SP()->isForum = true;
SP()->core->forumData['editor'] = apply_filters('sph_this_editor', SP()->core->forumData['editor']);
do_action('sph_load_editor', SP()->core->forumData['editor']);

if ($action == 'update-sig') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	echo sp_Signature('', SP()->user->profileUser->signature);
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				spj.setProfileDataHeight();
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

if ($action == 'update-display-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	echo sp_UserAvatar('tagClass=spCenter&context=user', SP()->user->profileUser);

	die();
}

if ($action == 'update-uploaded-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	if (SP()->user->profileUser->avatar['uploaded']) {
		$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;user=$userid&amp;avatarremove=1", 'profile'));
		$target  = 'spAvatarUpload';
		$spinner = SPCOMMONIMAGES.'working.gif';
		echo '<img src="'.esc_url(SPAVATARURL.SP()->user->profileUser->avatar['uploaded']).'" alt="" /><br /><br />';
		echo "<p class='spCenter'><input type='button' class='spSubmit' id='spDeleteUploadedAvatar' value='".SP()->primitives->front_text('Remove Uploaded Avatar')."' data-url='$ajaxURL' data-target='$target' data-spinner='$spinner' /></p>";
	} else {
		echo '<p class="spCenter">'.SP()->primitives->front_text('No avatar currently uploaded').'<br /><br /></p>';
	}

	die();
}

if ($action == 'update-pool-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	if (!empty(SP()->user->profileUser->avatar['pool'])) {
		$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;user=$userid&amp;poolremove=1", 'profile'));
		$target  = 'spAvatarPool';
		$spinner = SPCOMMONIMAGES.'working.gif';
		echo '<img src="'.esc_url(SPAVATARPOOLURL.SP()->user->profileUser->avatar['pool']).'" alt="" /><br /><br />';
		echo "<div id='spPoolStatus'><p class='spCenter'><input type='button' class='spSubmit' id='spDeletePoolAvatar' value='".SP()->primitives->front_text('Remove Pool Avatar')."' data-url='$ajaxURL' data-target='$target' data-spinner='$spinner' /></p></div>";
	} else {
		echo '<div id="spPoolStatus"><p class="spCenter">'.SP()->primitives->front_text('No pool avatar currently selected').'<br /><br /></p></div>';
	}

	die();
}

if ($action == 'update-remote-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	if (!empty(SP()->user->profileUser->avatar['remote'])) {
		echo '<img src="'.esc_url(SP()->user->profileUser->avatar['remote']).'" alt="" /><br /><br />';
	} else {
		echo '<p class="spCenter">'.SP()->primitives->front_text('No remote avatar currently selected').'<br /><br /></p>';
	}

	die();
}

if ($action == 'update-memberships') {
	if (empty($userid)) die();

	$spProfileData = SP()->user->get_memberships($userid);
	if ($spProfileData) {
		$alt = 'spOdd';
		foreach ($spProfileData as $userGroup) {
			echo "<div class='spProfileUsergroup $alt'>";
			echo '<div class="spColumnSection">';
			echo '<div class="spHeaderName">'.$userGroup['usergroup_name'].'</div>';
			echo '<div class="spHeaderDescription">'.$userGroup['usergroup_desc'].'</div>';
			echo '</div>';
			if ($userGroup['usergroup_join'] == 1 || SP()->user->thisUser->admin) {
				$submit = true;
				echo '<div class="spColumnSection spProfileMembershipsLeave">';
				echo '<div class="spInRowLabel">';
				echo '<input type="checkbox" name="usergroup_leave[]" id="sfusergroup_leave_'.$userGroup['usergroup_id'].'" value="'.$userGroup['usergroup_id'].'" />';
				echo '<label for="sfusergroup_leave_'.$userGroup['usergroup_id'].'">'.SP()->primitives->front_text('Leave Usergroup').'</label>';
				echo '</div>';
				echo '</div>';
			}
			echo '<div class="spClear"></div>';
			echo '</div>';
			$alt = ($alt == 'spOdd') ? 'spEven' : 'spOdd';
		}
	} else {
		echo '<div class="spProfileUsergroups">';
		if (SP()->user->thisUser->admin && SP()->user->thisUser->ID == $userid) {
			echo '<div class="spProfileUsergroup spOdd">';
			echo '<div class="spHeaderName">'.SP()->primitives->front_text('Administrators').'</div>';
			echo '<div class="spHeaderDescription">'.SP()->primitives->front_text('This pseudo Usergroup is for Adminstrators of the forum.').'</div>';
			echo '</div>';
		} else {
			echo '<div class="spProfileUsergroup spOdd">';
			echo SP()->primitives->front_text('You are not a member of any Usergroups.');
			echo '</div>';
		}
		echo '</div>';
	}
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				spj.setProfileDataHeight();
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

if ($action == 'update-nonmemberships') {
	if (empty($userid)) die();

	$usergroups = SP()->DB->table(SPUSERGROUPS, '', '', '', '', ARRAY_A);
	if ($usergroups && (SP()->user->thisUser->ID != $userid || !SP()->user->thisUser->admin)) {
		$alt   = 'spOdd';
		$first = true;
		foreach ($usergroups as $userGroup) {
			if (!SP()->user->check_membership($userGroup['usergroup_id'], $userid) && (($userGroup['usergroup_join'] == 1) || SP()->user->thisUser->admin)) {
				$submit = true;
				if ($first) {
					echo '<div class="spProfileUsergroupsNonMemberships">';
					echo '<p class="spHeaderName">'.SP()->primitives->front_text('Non-Memberships').':</p>';
					$first = false;
				}
				echo "<div class='spProfileUsergroup $alt'>";
				echo '<div class="spColumnSection">';
				echo '<div class="spHeaderName">'.$userGroup['usergroup_name'].'</div>';
				echo '<div class="spHeaderDescription">'.$userGroup['usergroup_desc'].'</div>';
				echo '</div>';
				echo '<div class="spColumnSection spProfileMembershipsJoin">';
				echo '<div class="spInRowLabel">';
				echo '<input type="checkbox" name="usergroup_join[]" id="sfusergroup_join_'.$userGroup['usergroup_id'].'" value="'.$userGroup['usergroup_id'].'" />';
				echo '<label for="sfusergroup_join_'.$userGroup['usergroup_id'].'">'.SP()->primitives->front_text('Join Usergroup').'</label>';
				echo '</div>';
				echo '</div>';
				echo '<div class="spClear"></div>';
				echo '</div>';
				$alt = ($alt == 'spOdd') ? 'spEven' : 'spOdd';
			}
		}
		if (!$first) {
			echo '</div>';
		}
	}
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				spj.setProfileDataHeight();
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

if ($action == 'update-photos') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	$spProfileOptions = SP()->options->get('sfprofile');
	$tout             = '';
	for ($x = 0; $x < $spProfileOptions['photosmax']; $x++) {
		$tout .= '<div class="spColumnSection spProfileLeftCol">';
		$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Url to Photo').' '.($x + 1).'</p>';
		$tout .= '</div>';
		$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
		$photo = (!empty(SP()->user->profileUser->photos[$x])) ? SP()->user->profileUser->photos[$x] : '';
		$tout .= '<div class="spColumnSection spProfileRightCol">';
		$tout .= "<p class='spProfileLabel'><input class='spControl' type='text' name='photo$x' value='$photo' /></p>";
		$tout .= '</div>';
	}
	$out = apply_filters('sph_ProfilePhotosLoop', $tout, $userid);
	echo $out;
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				setTimeout(function () {
					spj.setProfileDataHeight();
				}, 500);
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

# check for tab press
if (isset($_GET['tab'])) {
	# profile edit, so only admin or logged in user can view
	if (empty($userid) || (SP()->user->thisUser->ID != $userid && !SP()->user->thisUser->admin)) {
		SP()->notifications->message(SPFAILURE, SP()->primitives->front_text('Invalid profile request'));
		$out .= SP()->notifications->render_queued();
		$out .= '<div class="sfmessagestrip">';
		$out .= apply_filters('sph_ProfileErrorMsg', SP()->primitives->front_text('Sorry, an invalid profile request was detected. Do you need to log in?'));
		$out .= '</div>';

		return $out;
	}

	# set up profile for requested user
	sp_SetupUserProfileData($userid);

	# get pressed tab and menu (if pressed)
	$thisTab  = SP()->filters->str($_GET['tab']);
	$thisMenu = (isset($_GET['menu'])) ? SP()->filters->str($_GET['menu']) : '';

	# get all the tabs meta info
	$tabs = SP()->profile->get_tabs_menus();
	if (!empty($tabs)) {
		foreach ($tabs as $tab) {
			# find the pressed tab in the list of tabs
			if ($tab['slug'] == $thisTab) {
				# now output the menu and content
				$first    = true;
				$thisForm = '';
				$thisName = '';
				$thisSlug = '';
				$out      = '';
				if (!empty($tab['menus'])) {
					foreach ($tab['menus'] as $menu) {
						# do we need an auth check?
						$authCheck = (empty($menu['auth'])) ? true : SP()->auths->get($menu['auth'], '', $userid);

						# is this menu being displayed and does user have auth to see it?
						if ($authCheck && $menu['display']) {
							$current = '';
							# if tab press, see if its the first
							if ($first && empty($thisMenu)) {
								$current  = 'current';
								$thisName = $menu['name'];
								$thisForm = $menu['form'];
								$thisSlug = $menu['slug'];
								$first    = false;
							} else if (!empty($thisMenu)) {
								# if this menu was pressed, make it the current form
								if ($menu['slug'] == $thisMenu) {
									$current  = 'current';
									$thisName = $menu['name'];
									$thisForm = $menu['form'];
									$thisSlug = $menu['slug'];
									$thisMenu = ''; # menu press found so clear
									$first    = false;
								}
							}

							# special checking for displaying menus
							$spProfileOptions = SP()->options->get('sfprofile');
							$spAvatars        = SP()->options->get('sfavatars');
							$noPhotos         = ($menu['slug'] == 'edit-photos' && $spProfileOptions['photosmax'] < 1); # dont display edit photos if disabled
							$noAvatars        = ($menu['slug'] == 'edit-avatars' && !$spAvatars['sfshowavatars']); # dont display edit avatars if disabled
							$hideMenu         = ($noPhotos || $noAvatars);
							$hideMenu         = apply_filters('sph_ProfileMenuHide', $hideMenu, $tab, $menu, $userid);
							if (!$hideMenu) {
								# buffer the menu list while we find the current menu item
								$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;tab=$thisTab&amp;menu=".$menu['slug'].'&amp;user='.$userid.'&amp;rand='.rand(), 'profile'));
								if (is_ssl()) $ajaxURL = str_replace('http://', "https://", $ajaxURL);
								$out .= "<li class='spProfileMenuItem $current'>";
								if ($current) {
									$out .= "<a rel='nofollow' id='spProfileMenuCurrent'>".$menu['name'].'</a>';
								} else {
									$out .= "<a rel='nofollow' href='$ajaxURL' id='spProfileMenu-".esc_attr($menu['slug'])."'>".$menu['name'].'</a>';
								}
								$out .= '</li>';
							}
						}
					}
				}

				# output the header area
				echo '<div id="spProfileHeader">';
				echo $thisName.' <small>('.SP()->memberData->get($userid, 'display_name').')</small>';
				echo '</div>';

				# build the menus
				echo '<div id="spProfileMenu">';
				echo '<ul class="spProfileMenuGroup">';
				echo $out; # output buffered menu list
				echo '</ul>';
				echo '</div>';

				# build the form
				echo '<div id="spProfileData">';
				echo '<div id="spProfileFormPanel">';
				if (!empty($thisForm) && file_exists($thisForm)) {
					require_once $thisForm;
				} else {
					echo SP()->primitives->front_text('Profile form could not be found').': ['.$menu['name'].']<br />';
					echo SP()->primitives->front_text('You might try the forum - toolbox - housekeeping admin form and reset the profile tabs and menus and see if that helps');
				}
				echo '</div>';
				echo '</div>';
			}
		}
	} else {
		echo SP()->primitives->front_text('No profile tabs are defined');
	}

	$msg     = SP()->primitives->front_text('Forum rules require you to change your password in order to view forum or save your profile');
	$msg     = apply_filters('sph_change_pw_msg', $msg);
	$msg     = esc_attr($msg);
	$message = '<p class="spProfileFailure">'.$msg.'</p>';
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				/* set up the profile tabs */
				$("#spProfileMenu li a").off('click').click(function () {
					$("#spProfileContent").html("<div><img src='<?php echo SPCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
					$.ajax({
						async: true, url: this.href, success: function (html) {
							$("#spProfileContent").html(html);
						}
					});
					return false;
				});

				/* remove the click for current menu item */
				$("#spProfileMenu li.current a").off('click');

				/* adjust height of profile content area based on the current content */
				spj.setProfileDataHeight();

				spfProfileFirst = false;

				$('#spProfileContent').trigger('profilecontentloaded');

				<?php if (isset(SP()->user->thisUser->sp_change_pw) && SP()->user->thisUser->sp_change_pw) { ?>
				spj.displayNotification(1, '<?php echo $message; ?>');
				<?php } ?>
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

if (isset($_GET['avatarremove']) && (SP()->user->thisUser->ID == $userid || SP()->user->thisUser->admin)) {
	if (empty($userid)) die();

	# clear avatar db record
	$avatar             = SP()->memberData->get($userid, 'avatar');
	$avatar['uploaded'] = '';
	$avatar['default']  = 0;
	SP()->memberData->update($userid, 'avatar', $avatar);
	echo '<strong>'.SP()->primitives->front_text('Uploaded Avatar Removed').'</strong>';
	$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-display-avatar&user=$userid", 'profile'));
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				$('#spProfileDisplayAvatar').load('<?php echo $ajaxURL; ?>');
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

if ($action == 'avatarpool') {
	# Open avatar pool folder and get cntents for matching
	$path  = SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<strong>'.SP()->primitives->front_text('The avatar pool folder does not exist').'</strong>';
		die();
	}

	# start the table display
	echo '<p style="text-align:center;">'.SP()->primitives->front_text('Avatar Pool').'</p>';
	echo '<div>';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$text = esc_attr("<p class='spCenter'>".SP()->primitives->front_text('Avatar selected. Please save pool avatar').'</p>');
			echo "<img class='spAvatarPool spSelectPoolAvatar' src='".esc_url(SPAVATARPOOLURL.'/'.$file)."' alt='' data-src='".esc_attr(SPAVATARPOOLURL.'/'.$file)."' data-file='$file' data-text='$text' />&nbsp;&nbsp;";
		}
	}
	echo '</div>';
	closedir($dlist);

	die();
}

if (isset($_GET['poolremove']) && (SP()->user->thisUser->ID == $userid || SP()->user->thisUser->admin)) {
	if (empty($userid)) die();

	$avatar         = SP()->memberData->get($userid, 'avatar');
	$avatar['pool'] = '';
	SP()->memberData->update($userid, 'avatar', $avatar);
	echo '<div id="spPoolStatus"><p class="spCenter"><strong>'.SP()->primitives->front_text('No pool avatar currently selected').'</strong></div>';
	$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-display-avatar&user=$userid", 'profile'));
	?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				$('#spProfileDisplayAvatar').load('<?php echo $ajaxURL; ?>');
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
	<?php
	die();
}

die();
