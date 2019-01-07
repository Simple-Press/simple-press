<?php
/*
Simple:Press
Profile Memberships Form
$LastChangedDate: 2017-03-18 11:38:28 -0500 (Sat, 18 Mar 2017) $
$Rev: 15288 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

global $spThisUser;

$ajaxURL1 = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-memberships&user=$userid", 'profile'));
$ajaxURL2 = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-nonmemberships&user=$userid", 'profile'));
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormMemberships').ajaxForm({
        dataType: 'json',
		success: function(response) {
            jQuery('#spProfileUsergroupsMemberships').load('<?php echo $ajaxURL1; ?>');
            jQuery('#spProfileUsergroupsNonMemberships').load('<?php echo $ajaxURL2; ?>');
            if (response.type == 'success') {
        	   spjDisplayNotification(0, response.message);
            } else {
        	   spjDisplayNotification(1, response.message);
            }
		}
	});
})
</script>
<?php
$out = '';
$out.= '<p>';
$msg = sp_text('Usergroups enable forum admins to better control permissions and administer users. If the forum administrator has allowed it, you may also be able to join or leave open Usergroups. Your Usergroup memberships are shown below.');
$out.= apply_filters('sph_profile_membership_header', $msg);
$out.= '</p>';
$out.= '<hr>';

# get the users profile data
$spProfileData = sp_get_user_memberships($userid);

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out.= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormMemberships" id="spProfileFormMemberships" class="spProfileForm">';
$out.= sp_create_nonce('forum-profile');

# show usergroup memberships
$out.= '<div class="spProfileUsergroupsMemberships">';

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileUsergroupsMembershipsFormTop', $out, $userid);

$out.= '<p class="spHeaderName">'.sp_text('Memberships').':</p>';
$submit = false; # flag to indicate if any membership joins/leaves are available

$out.= '<div id="spProfileUsergroupsMemberships">';
if ($spProfileData) {
	$alt = 'spOdd';
	foreach ($spProfileData as $userGroup) {
		$out.= "<div class='spProfileUsergroup $alt'>";
		$out.= '<div class="spColumnSection">';
		$out.= '<div class="spHeaderName">'.$userGroup['usergroup_name'].'</div>';
		$out.= '<div class="spHeaderDescription">'.$userGroup['usergroup_desc'].'</div>';
		$out.= '</div>';
		if ($userGroup['usergroup_join'] == 1 || $spThisUser->admin) {
			$submit = true;
			$out.= '<div class="spColumnSection spProfileMembershipsLeave">';
			$out.= '<div class="spInRowLabel">';
			$out.= '<input type="checkbox" name="usergroup_leave[]" id="sfusergroup_leave_'.$userGroup['usergroup_id'].'" value="'.$userGroup['usergroup_id'].'" />';
			$out.= '<label for="sfusergroup_leave_'.$userGroup['usergroup_id'].'">'.sp_text('Leave Usergroup').'</label>';
			$out.= '</div>';
			$out.= '</div>';
		}
		$out.= '<div class="spClear"></div>';
		$out.= '</div>';
		$alt = ($alt == 'spOdd') ? 'spEven' : 'spOdd';
	}
} else {
	$out.= '<div class="spProfileUsergroups">';
	if ($spProfileUser->admin || ($spThisUser->admin && $spThisUser->ID == $userid)) {
		$out.= '<div class="spProfileUsergroup spOdd">';
		$out.= '<div class="spHeaderName">'.sp_text('Administrators').'</div>';
		$out.= '<div class="spHeaderDescription">'.sp_text('This pseudo Usergroup is for Adminstrators of the forum.').'</div>';
		$out.= '</div>';
	} else {
		$out.= '<div class="spProfileUsergroup spOdd">';
		$out.= sp_text('You are not a member of any Usergroups.');
		$out.= '</div>';
	}
	$out.= '</div>';
}
$out.= '</div>';

$out = apply_filters('sph_ProfileUsergroupsMembershipsFormBottom', $out, $userid);
$out.= '</div>';

# get all usergroups
$usergroups = spdb_table(SFUSERGROUPS, '', '', '', '', ARRAY_A);

# now show usergroups not a member of that can be joined
$out.= '<div id="spProfileUsergroupsNonMemberships">';
if ($usergroups && ($spThisUser->ID != $userid || !$spThisUser->admin)) {
	$alt = 'spOdd';
	$first = true;
	foreach ($usergroups as $userGroup) {
		if ((!sp_check_membership($userGroup['usergroup_id'], $userid) && (($userGroup['usergroup_join'] == 1) || $spThisUser->admin)) && !$spProfileUser->admin) {
			$submit = true;
			if ($first) {
				$out.= '<div class="spProfileUsergroupsNonMemberships">';
				$out.= '<p class="spHeaderName">'.sp_text('Non-Memberships').':</p>';
				$first = false;
			}
			$out.= "<div class='spProfileUsergroup $alt'>";
			$out.= '<div class="spColumnSection">';
			$out.= '<div class="spHeaderName">'.$userGroup['usergroup_name'].'</div>';
			$out.= '<div class="spHeaderDescription">'.$userGroup['usergroup_desc'].'</div>';
			$out.= '</div>';
			$out.= '<div class="spColumnSection spProfileMembershipsJoin">';
			$out.= '<div class="spInRowLabel">';
			$out.= '<input type="checkbox" name="usergroup_join[]" id="sfusergroup_join_'.$userGroup['usergroup_id'].'" value="'.$userGroup['usergroup_id'].'" />';
			$out.= '<label for="sfusergroup_join_'.$userGroup['usergroup_id'].'">'.sp_text('Join Usergroup').'</label>';
			$out.= '</div>';
			$out.= '</div>';
			$out.= '<div class="spClear"></div>';
			$out.= '</div>';
			$alt = ($alt == 'spOdd') ? 'spEven' : 'spOdd';
		}
	}
	if (!$first) {
		$out.= '</div>';
	}
}
$out.= '</div>';

# any changes allowed?
if ($submit) {
	$out.= '<div class="spProfileFormSubmit">';
	$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Memberships').'" />';
	$out.= '</div>';
}

$out = apply_filters('sph_ProfileUsergroupsMembershipsFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out.= '</form>';

$out = apply_filters('sph_ProfileUsergroupsMemberships', $out, $userid);
echo $out;
?>