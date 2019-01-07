<?php
/*
Simple:Press
Profile Overview Form
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

# let's start off with a do action hook right at the start
do_action('sph_profile_overview_form_top', $userid);

# get the users profile data
$spProfileOptions = SP()->options->get('sfprofile');

$out = '';
$out .= '<div>'.SP()->displayFilters->text($spProfileOptions['sfprofiletext']).'</div>';
$out .= '<hr />';

# start the form
$out .= '<div class="spProfileOverview">';

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileOverviewFormTop', $out, $userid);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('User').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<p class="spProfileLabel">'.SP()->user->profileUser->display_name.'</p>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserDisplayName', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Member Since').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<p class="spProfileLabel">'.SP()->dateTime->format_date('d', SP()->user->profileUser->user_registered).'</p>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserMemberSince', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Last Visited').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<p class="spProfileLabel">'.SP()->dateTime->format_date('d', SP()->user->profileUser->lastvisit).' '.SP()->dateTime->format_date('t', SP()->user->profileUser->lastvisit).'</p>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserLastVisited', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Posts').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<p class="spProfileLabel">'.SP()->user->profileUser->posts.'</p>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserPosts', $tout, $userid, $thisSlug);

$sfrss = SP()->options->get('sfrss');
if ($sfrss['sfrssfeedkey']) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Your Feedkey').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->user->profileUser->feedkey.'</p>';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserFeedkey', $tout, $userid, $thisSlug);
}

if (empty(SP()->user->profileUser->timezone_string)) SP()->user->profileUser->timezone_string = get_option('timezone_string');
if (substr(SP()->user->profileUser->timezone_string, 0, 3) == 'UTC') SP()->user->profileUser->timezone_string = 'UTC';

date_default_timezone_set(SP()->user->profileUser->timezone_string);
$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Your Timezone').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<p class="spProfileLabel">'.SP()->user->profileUser->timezone_string.'</p>';
$tout .= '<p><small>'.SP()->primitives->front_text('Local Time').': '.SP()->dateTime->format_date('d', date(SPDATES)).' '.SP()->dateTime->format_date('t', date(SPTIMES)).'</small></p>';
$tout .= '<p><small>'.SP()->primitives->front_text('Change your timezone on options - display').'</small></p>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserTimezone', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfileOverviewFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol"></div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<form action="'.wp_nonce_url(SPAJAXURL.'search', 'search').'" method="post" id="searchposts" name="searchposts">';
$tout .= '<input type="hidden" class="sfhiddeninput" name="searchoption" id="searchoption" value="2" />';
$tout .= '<input type="hidden" class="sfhiddeninput" name="userid" id="userid" value="'.$userid.'" />';
$tout .= '<div class="spProfileFormSubmit">';
$tout .= '<input type="submit" class="spSubmit" name="membersearch" value="'.SP()->primitives->front_text('List Topics You Have Posted To').'" />';
$tout .= '<input type="submit" class="spSubmit" name="memberstarted" value="'.SP()->primitives->front_text('List Topics You Started').'" />';
$tout .= '</div>';
$tout .= '</form>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserTopicsPosted', $tout, $userid, $thisSlug);

$out .= "</div>\n";

$out = apply_filters('sph_ProfileOverviewForm', $out, $userid);

$out .= sp_InsertBreak('echo=0');

echo $out;
