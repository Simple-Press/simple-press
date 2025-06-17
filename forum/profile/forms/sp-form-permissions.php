<?php
/*
Simple:Press
Profile Permissions Form
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}


# double check we have a user
if (empty($userid)) {
    return;
}

 $out = '';
$out .= '<p>';
$msg = SP()->primitives->front_text('Permissions are what enable you to do things on forums. For the forums you have access to, your permissions are shown below.');
$out .= apply_filters('sph_profile_permissions_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# get the users profile data
# Start the 'groupView' section
# ----------------------------------------------------------------------
$out .= '<div class="spProfileUserPermissions spListSection">';

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileUserPermissionsFormTop', $out, $userid);

# Start the Group Loop
SP()->forum->view = new spcView();
if (SP()->forum->view->has_groups()) :
    while (SP()->forum->view->loop_groups()) : SP()->forum->view->the_group();
        # Start the 'groupHeader' section
        $out .= '<div class="spGroupViewSection">';

        $icon = '';

        $group_icon = spa_get_saved_icon( SP()->forum->view->thisGroup->group_icon );

        if( !empty( $group_icon['icon'] ) ) {
            if( 'file' === $group_icon['type'] ) {
                $icon = SP()->theme->paint_custom_icon('spHeaderName spLeft', SPCUSTOMURL . $group_icon['icon'] );
            } else {
                $icon = SP()->theme->sp_paint_iconset_icon( $group_icon, 'spHeaderName spLeft' );
            }
        } else {
            $icon = SP()->theme->paint_icon('spHeaderName spLeft', SPTHEMEICONSURL, 'sp_GroupIcon.png');
        }

        $out .= $icon;
        $out .= "<div class='spHeaderName'>".SP()->forum->view->thisGroup->group_name."</div>";
        $out .= "<div class='spHeaderDescription'>".SP()->forum->view->thisGroup->group_desc."</div>";

        $out .= sp_InsertBreak('echo=0');

        # Start the Forum Loop
        global $thisAlt; # alternates row class for forums
        $thisAlt = 'spOdd'; # initialize row class
        if (SP()->forum->view->has_forums()) : # check for forums in group
            while (SP()->forum->view->loop_forums()) : # loop each forum in group
                SP()->forum->view->the_forum(); # set up current forum
                $out .= sp_ProfilePermissionsForum(SP()->forum->view->thisForum, $userid); # render forum permissions row

                # do subforums
                if (!empty(SP()->forum->view->thisForumSubs)) { # check for subforums
                    foreach (SP()->forum->view->thisForumSubs as $sub) { # loop subforums
                        $out .= sp_ProfilePermissionsForum($sub, $userid); # render subforum permissions
                    }
                }
            endwhile;
        else:
            # no forums exist in this group
            sp_NoForumMessage('tagClass=spMessage', SP()->primitives->front_text('No Forums Found in this Group'));
        endif;
        $out .= '</div>'; # close group forum section
    endwhile;
else:
    sp_NoGroupMessage('tagClass=spMessage', SP()->primitives->front_text('Access denied'), SP()->primitives->front_text('No Groups Defined'));
endif;

$out = apply_filters('sph_ProfileUserPermissionsFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom',  $out, $userid, $thisSlug);

$out .= '</div>';

$out = apply_filters('sph_ProfilePermissionsForm', $out);

$allowed_html = array(
    'p'      => array(),
    'hr'     => array(),
    'div'    => array(
        'class' => array(),
        'id'    => array(),
    ),
    'a'      => array(
        'rel'      => array(),
        'class'    => array(),
        'href'     => array(),
        'data-url' => array(),
        'data-id'  => array(),
        'data-img' => array(),
    ),
    'input'  => array(
        'type'  => array(),
        'class' => array(),
        'value' => array(),
    ),
    'span'   => array(
        'class' => array(),
    ),
);
echo wp_kses($out,$allowed_html);

# routine for outputting forum or subforum row
function sp_ProfilePermissionsForum($thisForum, $userid) {
    global $thisAlt;

    # Start the 'forum' section
    $out = "<div class='spGroupForumSection $thisAlt'>";

    # Column 1 of the forum row
    $out .= '<div class="spColumnSection spProfilePermissionIcon">';

    $forum_icon = spa_get_saved_icon( $thisForum->forum_icon );

    if( !empty( $forum_icon['icon'] ) ) {
        if( 'file' === $forum_icon['type'] ) {
            $icon = SP()->theme->paint_custom_icon('spRowIcon', SPCUSTOMURL . $forum_icon['icon'] );
        } else {
            $icon = SP()->theme->sp_paint_iconset_icon( $forum_icon, 'spRowIcon' );
        }
    } else {
        $icon = SP()->theme->paint_icon('spRowIcon', SPTHEMEICONSURL, 'sp_GroupIcon.png');
    }

    $out .= $icon;
    $out .= '</div>';

    # Column 2 of the forum row
    $out .= '<div class="spColumnSection spProfilePermissionForum">';
    $out .= "<div class='spRowName'>".$thisForum->forum_name."</div>";
    $desc = (!empty($thisForum->forum_desc)) ? $thisForum->forum_desc : '';
    $out .= "<div class='spRowDescription'>".$desc."</div>";
    $out .= '</div>';

    # Column 3 of the forum row
    $site = wp_nonce_url(SPAJAXURL.'permissions&amp;forum='.$thisForum->forum_id.'&amp;userid='.$userid, 'permissions');
    $img  = SPCOMMONIMAGES.'/working.gif';
    $out .= '<div class="spColumnSection spProfilePermissionButton">';
    $out .= "<a rel='nofollow' class='spLoadPermissions' data-url='$site' data-id='$thisForum->forum_id' data-img='$img'>";
    $out .= '<input type="submit" class="spSubmit" value="'.SP()->primitives->front_text('View').'" />';
    $out .= '</a>';
    $out .= '</div>';

    $out .= sp_InsertBreak('echo=0');

    $out .= '</div>';

    # hidden area for the permissions for this forum
    $out .= '<div id="perm'.$thisForum->forum_id.'" class="spHiddenSection spProfilePermission"></div>';

    $thisAlt = ($thisAlt == 'spOdd') ? 'spEven' : 'spOdd';

    return $out;
}


