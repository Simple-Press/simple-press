<?php
/*
Simple:Press
Ajax call for View Member Profile
$LastChangedDate: 2016-08-24 10:56:31 -0500 (Wed, 24 Aug 2016) $
$Rev: 14521 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('profile')) die();

include_once(SF_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php');
include_once (SF_PLUGIN_DIR.'/forum/content/sp-profile-view-functions.php');

# set up some globals for theme template files (spProfilePopup in this case) to use directly
global $spGroupView, $spThisGroup, $spForumView, $spThisForum, $spThisForumSubs,
$spThisTopic, $spThisPost, $spThisPostUser, $spNewPosts, $spThisUser,
$spProfileUser, $spMembersList, $spThisMemberGroup, $spThisMember,
$spGlobals, $spDevice, $spMobile;

$userid = (isset($_GET['user'])) ? sp_esc_int($_GET['user']) : 0;
$action = (isset($_GET['targetaction'])) ? $_GET['targetaction'] : '';

do_action('sph_ProfileStart', $action);

global $spIsForum;
$spIsForum = true;
$spGlobals['editor'] = apply_filters('sph_this_editor', $spGlobals['editor']);
do_action('sph_load_editor', $spGlobals['editor']);


$out = '';

# is it a popup profile?
if ($action == 'popup') {
	if (empty($userid)) {
		sp_notify(SPFAILURE, sp_text('Invalid profile request'));
		$out.= sp_render_queued_notification();
		$out.= '<div class="sfmessagestrip">';
		$out.= apply_filters('sph_ProfileErrorMsg', sp_text('Sorry, an invalid profile request was detected'));
		$out.= '</div>';
		return $out;
	}

	sp_SetupUserProfileData($userid);

	echo '<div id="spMainContainer">';
    sp_load_template('spProfilePopupShow.php');
	echo '</div>';

	die();
}

if ($action == 'update-sig') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
    echo sp_Signature('', $spProfileUser->signature);
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
        spjSetProfileDataHeight();
	})
	</script>
<?php
	die();
}

if ($action == 'update-display-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
    echo sp_UserAvatar('tagClass=spCenter&context=user', $spProfileUser);

	die();
}

if ($action == 'update-uploaded-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	if ($spProfileUser->avatar['uploaded']) {
        $ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;user=$userid&amp;avatarremove=1", 'profile'));
		$target = 'spAvatarUpload';
		$spinner = SFCOMMONIMAGES.'working.gif';
        echo '<img src="'.esc_url(SFAVATARURL.$spProfileUser->avatar['uploaded']).'" alt="" /><br /><br />';
    	echo "<p class='spCenter'><input type='button' class='spSubmit' id='spDeleteUploadedAvatar' value='".sp_text('Remove Uploaded Avatar')."' data-url='$ajaxURL' data-target='$target' data-spinner='$spinner' /></p>";
	} else {
		echo '<p class="spCenter">'.sp_text('No avatar currently uploaded').'<br /><br /></p>';
	}

	die();
}

if ($action == 'update-pool-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	if (!empty($spProfileUser->avatar['pool'])) {
        $ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;user=$userid&amp;poolremove=1", 'profile'));
		$target = 'spAvatarPool';
		$spinner = SFCOMMONIMAGES.'working.gif';
        echo '<img src="'.esc_url(SFAVATARPOOLURL.$spProfileUser->avatar['pool']).'" alt="" /><br /><br />';
		echo "<div id='spPoolStatus'><p class='spCenter'><input type='button' class='spSubmit' id='spDeletePoolAvatar' value='".sp_text('Remove Pool Avatar')."' data-url='$ajaxURL' data-target='$target' data-spinner='$spinner' /></p></div>";
	} else {
		echo '<div id="spPoolStatus"><p class="spCenter">'.sp_text('No pool avatar currently selected').'<br /><br /></p></div>';
	}

	die();
}

if ($action == 'update-remote-avatar') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
	if (!empty($spProfileUser->avatar['remote'])) {
		echo '<img src="'.esc_url($spProfileUser->avatar['remote']).'" alt="" /><br /><br />';
	} else {
		echo '<p class="spCenter">'.sp_text('No remote avatar currently selected').'<br /><br /></p>';
	}

	die();
}

if ($action == 'update-memberships') {
	if (empty($userid)) die();

    global $spThisUser;
    $spProfileData = sp_get_user_memberships($userid);
    if ($spProfileData) {
    	$alt = 'spOdd';
    	foreach ($spProfileData as $userGroup) {
    		echo "<div class='spProfileUsergroup $alt'>";
    		echo '<div class="spColumnSection">';
    		echo '<div class="spHeaderName">'.$userGroup['usergroup_name'].'</div>';
    		echo '<div class="spHeaderDescription">'.$userGroup['usergroup_desc'].'</div>';
    		echo '</div>';
    		if ($userGroup['usergroup_join'] == 1 || $spThisUser->admin) {
    			$submit = true;
    			echo '<div class="spColumnSection spProfileMembershipsLeave">';
    			echo '<div class="spInRowLabel">';
    			echo '<input type="checkbox" name="usergroup_leave[]" id="sfusergroup_leave_'.$userGroup['usergroup_id'].'" value="'.$userGroup['usergroup_id'].'" />';
       			echo '<label for="sfusergroup_leave_'.$userGroup['usergroup_id'].'">'.sp_text('Leave Usergroup').'</label>';
    			echo '</div>';
    			echo '</div>';
    		}
    		echo '<div class="spClear"></div>';
    		echo '</div>';
    		$alt = ($alt == 'spOdd') ? 'spEven' : 'spOdd';
    	}
    } else {
    	echo '<div class="spProfileUsergroups">';
    	if ($spThisUser->admin && $spThisUser->ID == $userid) {
    		echo '<div class="spProfileUsergroup spOdd">';
    		echo '<div class="spHeaderName">'.sp_text('Administrators').'</div>';
    		echo '<div class="spHeaderDescription">'.sp_text('This pseudo Usergroup is for Adminstrators of the forum.').'</div>';
    		echo '</div>';
    	} else {
    		echo '<div class="spProfileUsergroup spOdd">';
    		echo sp_text('You are not a member of any Usergroups.');
    		echo '</div>';
    	}
    	echo '</div>';
    }
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
        spjSetProfileDataHeight();
	})
	</script>
<?php
	die();
}

if ($action == 'update-nonmemberships') {
	if (empty($userid)) die();

    global $spThisUser;
    $usergroups = spdb_table(SFUSERGROUPS, '', '', '', '', ARRAY_A);
    if ($usergroups && ($spThisUser->ID != $userid || !$spThisUser->admin)) {
    	$alt = 'spOdd';
    	$first = true;
    	foreach ($usergroups as $userGroup) {
    		if (!sp_check_membership($userGroup['usergroup_id'], $userid) && (($userGroup['usergroup_join'] == 1) || $spThisUser->admin)) {
    			$submit = true;
    			if ($first) {
    				echo '<div class="spProfileUsergroupsNonMemberships">';
    				echo '<p class="spHeaderName">'.sp_text('Non-Memberships').':</p>';
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
    			echo '<label for="sfusergroup_join_'.$userGroup['usergroup_id'].'">'.sp_text('Join Usergroup').'</label>';
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
	<script type="text/javascript">
	jQuery(document).ready(function() {
        spjSetProfileDataHeight();
	})
	</script>
<?php
	die();
}

if ($action == 'update-photos') {
	if (empty($userid)) die();

	sp_SetupUserProfileData($userid);
   	$spProfileOptions = sp_get_option('sfprofile');
    $tout = '';
	for ($x=0; $x < $spProfileOptions['photosmax']; $x++) {
    	$tout.= '<div class="spColumnSection spProfileLeftCol">';
		$tout.= '<p class="spProfileLabel">'.sp_text('Url to Photo').' '.($x+1).'</p>';
    	$tout.= '</div>';
    	$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
        $photo = (!empty($spProfileUser->photos[$x])) ? $spProfileUser->photos[$x] : '';
    	$tout.= '<div class="spColumnSection spProfileRightCol">';
		$tout.= "<p class='spProfileLabel'><input class='spControl' type='text' name='photo$x' value='$photo' /></p>";
    	$tout.= '</div>';
	}
    $out = apply_filters('sph_ProfilePhotosLoop', $tout, $userid);
    echo $out;
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
        setTimeout(function() {
            spjSetProfileDataHeight();
        }, 500);
	})
	</script>
<?php
	die();
}

# check for tab press
if (isset($_GET['tab'])) {
	# profile edit, so only admin or logged in user can view
	if (empty($userid) || ($spThisUser->ID != $userid && !$spThisUser->admin)) {
		sp_notify(SPFAILURE, sp_text('Invalid profile request'));
		$out.= sp_render_queued_notification();
		$out.= '<div class="sfmessagestrip">';
		$out.= apply_filters('sph_ProfileErrorMsg', sp_text('Sorry, an invalid profile request was detected. Do you need to log in?'));
		$out.= '</div>';
		return $out;
	}

	# set up profile for requested user
	sp_SetupUserProfileData($userid);

	# get pressed tab and menu (if pressed)
	$thisTab = sp_esc_str($_GET['tab']);
	$thisMenu = (isset($_GET['menu'])) ? sp_esc_str($_GET['menu']) : '';

	# get all the tabs meta info
	$tabs = sp_profile_get_tabs();
    if (!empty($tabs)) {
    	foreach ($tabs as $tab) {
    		# find the pressed tab in the list of tabs
    		if ($tab['slug'] == $thisTab) {
    			# now output the menu and content
    			$first = true;
    			$thisForm = '';
    			$thisName = '';
    			$thisSlug = '';
    			$out = '';
    			if (!empty($tab['menus'])) {
    				foreach ($tab['menus'] as $menu) {
    					# do we need an auth check?
    					$authCheck = (empty($menu['auth'])) ? true : sp_get_auth($menu['auth'], '', $userid);

    					# is this menu being displayed and does user have auth to see it?
    					if ($authCheck && $menu['display']) {
    						$current = '';
    						# if tab press, see if its the first
    						if ($first && empty($thisMenu)) {
    							$current = 'current';
    							$thisName = $menu['name'];
    							$thisForm = $menu['form'];
    							$thisSlug = $menu['slug'];
    							$first = false;
    						} else if (!empty($thisMenu)) {
    							# if this menu was pressed, make it the current form
    							if ($menu['slug'] == $thisMenu) {
    								$current = 'current';
    								$thisName = $menu['name'];
    								$thisForm = $menu['form'];
    								$thisSlug = $menu['slug'];
    								$thisMenu = ''; # menu press found so clear
    								$first = false;
    							}
    						}

                            # special checking for displaying menus
                        	$spProfileOptions = sp_get_option('sfprofile');
                            $spAvatars = sp_get_option('sfavatars');
                            $noPhotos = ($menu['slug'] == 'edit-photos' && $spProfileOptions['photosmax'] < 1); # dont display edit photos if disabled
                            $noAvatars = ($menu['slug'] == 'edit-avatars' && !$spAvatars['sfshowavatars']); # dont display edit avatars if disabled
                            $hideMenu = ($noPhotos || $noAvatars);
                            $hideMenu = apply_filters('sph_ProfileMenuHide', $hideMenu, $tab, $menu, $userid);
                            if (!$hideMenu) {
        						# buffer the menu list while we find the current menu item
        					    $ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;tab=$thisTab&amp;menu=".$menu['slug'].'&amp;user='.$userid.'&amp;rand='.rand(), 'profile'));
                                if (is_ssl()) $ajaxURL = str_replace('http://', "https://", $ajaxURL);
        						$out.= "<li class='spProfileMenuItem $current'>";
                                if ($current) {
                                    $out.= "<a rel='nofollow' id='spProfileMenuCurrent'>".$menu['name'].'</a>';
                                } else {
                                    $out.= "<a rel='nofollow' href='$ajaxURL' id='spProfileMenu-".esc_attr($menu['slug'])."'>".$menu['name'].'</a>';
                                }
                                $out.= '</li>';
                            }
    					}
    				}
    			}

    			# output the header area
    			echo '<div id="spProfileHeader">';
    			echo $thisName.' <small>('.sp_get_member_item($userid, 'display_name').')</small>';
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
    				include_once $thisForm;
    			} else {
    				echo sp_text('Profile form could not be found').': ['.$menu['name'].']<br />';
    				echo sp_text('You might try the forum - toolbox - housekeeping admin form and reset the profile tabs and menus and see if that helps');
    			}
    			echo '</div>';
    			echo '</div>';
    		}
    	}
    } else {
		echo sp_text('No profile tabs are defined');
    }

	$msg = sp_text('Forum rules require you to change your password in order to view forum or save your profile');
	$msg = apply_filters('sph_change_pw_msg', $msg);
	$msg = esc_attr($msg);
	$message = '<p class="spProfileFailure">'.$msg.'</p>';

	global $spMobile;
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		/* set up the profile tabs */
	    jQuery("#spProfileMenu li a").off('click').click(function() {
	        jQuery("#spProfileContent").html("<div><img src='<?php echo SFCOMMONIMAGES; ?>working.gif' alt='Loading' /></div>");
	        jQuery.ajax({async: true, url: this.href, success: function(html) {
	            jQuery("#spProfileContent").html(html); }
	    	});
	    	return false;
	    });

        /* remove the click for current menu item */
	    jQuery("#spProfileMenu li.current a").off('click');

		/* adjust height of profile content area based on the current content */
        spjSetProfileDataHeight();

        spfProfileFirst = false;

        jQuery('#spProfileContent').trigger('profilecontentloaded');

		<?php if (isset($spThisUser->sp_change_pw) && $spThisUser->sp_change_pw) { ?>
            spjDisplayNotification(1, '<?php echo $message; ?>');
		<?php } ?>
	})
	</script>
<?php
	die();
}

if (isset($_GET['avatarremove']) && ($spThisUser->ID == $userid || $spThisUser->admin)) {
	if (empty($userid)) die();

	# clear avatar db record
	$avatar = sp_get_member_item($userid, 'avatar');
	$avatar['uploaded'] = '';
	$avatar['default'] = 0;
	sp_update_member_item($userid, 'avatar', $avatar);
	echo '<strong>'.sp_text('Uploaded Avatar Removed').'</strong>';
    $ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-display-avatar&user=$userid", 'profile'));
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
        jQuery('#spProfileDisplayAvatar').load('<?php echo $ajaxURL; ?>');
	})
	</script>
<?php
	die();
}

if ($action == 'avatarpool') {
	global $spPaths;

	# Open avatar pool folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['avatar-pool'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
        echo '<strong>'.sp_text('The avatar pool folder does not exist').'</strong>';
        die();
	}

	# start the table display
	echo '<p style="text-align:center;">'.sp_text('Avatar Pool').'</p>';
	echo '<div>';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
            $text = esc_attr("<p class='spCenter'>".sp_text('Avatar selected. Please save pool avatar').'</p>');
			echo "<img class='spAvatarPool spSelectPoolAvatar' src='".esc_url(SFAVATARPOOLURL.'/'.$file)."' alt='' data-src='".esc_attr(SFAVATARPOOLURL.'/'.$file)."' data-file='$file' data-text='$text' />&nbsp;&nbsp;";
		}
	}
	echo '</div>';
	closedir($dlist);

	die();
}

if (isset($_GET['poolremove']) && ($spThisUser->ID == $userid || $spThisUser->admin)) {
	if (empty($userid)) die();

	$avatar = sp_get_member_item($userid, 'avatar');
	$avatar['pool'] = '';
	sp_update_member_item($userid, 'avatar', $avatar);
	echo '<div id="spPoolStatus"><p class="spCenter"><strong>'.sp_text('No pool avatar currently selected').'</strong></div>';
    $ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-display-avatar&user=$userid", 'profile'));
?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
        jQuery('#spProfileDisplayAvatar').load('<?php echo $ajaxURL; ?>');
	})
	</script>
<?php
	die();
}

die();
?>