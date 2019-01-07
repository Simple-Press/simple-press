<?php
/*
Simple:Press
Profile Permissions Form
$LastChangedDate: 2016-06-25 11:16:13 -0500 (Sat, 25 Jun 2016) $
$Rev: 14334 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

$out = '';
$out.= '<p>';
$msg = sp_text('Permissions are what enable you to do things on forums. For the forums you have access to, your permissions are shown below.');
$out.= apply_filters('sph_profile_permissions_header', $msg);
$out.= '</p>';
$out.= '<hr>';

# get the users profile data
# Start the 'groupView' section
# ----------------------------------------------------------------------
$out.= '<div class="spProfileUserPermissions spListSection">';

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileUserPermissionsFormTop', $out, $userid);

# Start the Group Loop
global $spThisGroup;
if (sp_has_groups()) : while (sp_loop_groups()) : sp_the_group();
	# Start the 'groupHeader' section
	$out.= '<div class="spGroupViewSection">';
	$icon = (!empty($spThisGroup->group_icon)) ? sp_paint_custom_icon('spHeaderName spLeft', SFCUSTOMURL.$spThisGroup->group_icon) : sp_paint_icon('spHeaderName spLeft', SPTHEMEICONSURL, 'sp_GroupIcon.png');
	$out.= $icon;
	$out.= "<div class='spHeaderName'>".$spThisGroup->group_name."</div>";
	$out.= "<div class='spHeaderDescription'>".$spThisGroup->group_desc."</div>";

	$out.= sp_InsertBreak('echo=0');

	# Start the Forum Loop
	global $thisAlt;
	$thisAlt = 'spOdd';
    global $spThisForum;
	if (sp_has_forums()) : while (sp_loop_forums()) : sp_the_forum();
		$out.= sp_ProfilePermissionsForum($spThisForum, $userid);

		# do subforums
		if (!empty($spThisForumSubs)) {
			foreach ($spThisForumSubs as $sub) {
				$out.= sp_ProfilePermissionsForum($sub, $userid);
			}
		}
	endwhile; else:
		sp_NoForumMessage('tagClass=spMessage', sp_text('No Forums Found in this Group'));
	endif;
	$out.= '</div>';
endwhile; else:
	sp_NoGroupMessage('tagClass=spMessage', sp_text('Access denied'), sp_text('No Groups Defined'));
endif;

$out = apply_filters('sph_ProfileUserPermissionsFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out.= '</div>';

$out = apply_filters('sph_ProfilePermissionsForm', $out);
echo $out;


# routine for outputting forum or subforum row
function sp_ProfilePermissionsForum($thisForum, $userid) {
	global $thisAlt;

	# Start the 'forum' section
	$out = "<div class='spGroupForumSection $thisAlt'>";

	# Column 1 of the forum row
	$out.= '<div class="spColumnSection spProfilePermissionIcon">';
	$icon = (!empty($thisForum->forum_icon)) ? sp_paint_custom_icon('spRowIcon', SFCUSTOMURL.$thisForum->forum_icon) : sp_paint_icon('spRowIcon', SPTHEMEICONSURL, 'sp_ForumIcon.png');
	$out.= $icon;
	$out.= '</div>';

	# Column 2 of the forum row
	$out.= '<div class="spColumnSection spProfilePermissionForum">';
	$out.= "<div class='spRowName'>".$thisForum->forum_name."</div>";
    $desc = (!empty($thisForum->forum_desc)) ? $thisForum->forum_desc : '';
	$out.= "<div class='spRowDescription'>".$desc."</div>";
	$out.= '</div>';

	# Column 3 of the forum row
	$site = wp_nonce_url(SPAJAXURL.'permissions&amp;forum='.$thisForum->forum_id.'&amp;userid='.$userid, 'permissions');
	$img = SFCOMMONIMAGES.'/working.gif';
	$out.= '<div class="spColumnSection spProfilePermissionButton">';
	$out.= "<a rel='nofollow' class='spLoadPermissions' data-url='$site' data-id='$thisForum->forum_id' data-img='$img'>";
	$out.= '<input type="submit" class="spSubmit" value="'.sp_text('View').'" />';
	$out.= '</a>';
	$out.= '</div>';

	$out.= sp_InsertBreak('echo=0');

	$out.= '</div>';

	# hidden area for the permissions for this forum
	$out.= '<div id="perm'.$thisForum->forum_id.'" class="spHiddenSection spProfilePermission"></div>';

	$thisAlt = ($thisAlt == 'spOdd') ? 'spEven' : 'spOdd';

	return $out;
}
?>