<?php
/*
Simple:Press
Profile Identities Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;
?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				/* ajax form and message */
				$('#spProfileFormIdentities').ajaxForm({
					dataType: 'json',
					success: function (response) {
						if (response.type == 'success') {
							spj.displayNotification(0, response.message);
						} else {
							spj.displayNotification(1, response.message);
						}
					}
				});
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
<?php

// Set variable to control whether we should display deprecated identities...
$display_deprecated_identities = (Null != SP()->options->get('display_deprecated_identities') ? boolval(SP()->options->get('display_deprecated_identities')) : false) ;

$out = '';
$out .= '<p>';
$msg = SP()->primitives->front_text('On this panel, you may edit your Online Identities. Please enter only account names and not a URL.');
$out .= apply_filters('sph_profile_identities_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# start the form
$out .= '<div class="spProfileIdentities">';

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormIdentities" id="spProfileFormIdentities" class="spProfileForm">';
$out .= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileIdentitiesFormTop', $out, $userid);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Facebook').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$facebook = (!empty(SP()->user->profileUser->facebook)) ? SP()->user->profileUser->facebook : '';
$tout .= '<input type="text" class="spControl" name="facebook" id="facebook" value="'.esc_attr($facebook).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserFacebook', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Twitter').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$twitter = (!empty(SP()->user->profileUser->twitter)) ? SP()->user->profileUser->twitter : '';
$tout .= '<input type="text" class="spControl" name="twitter" id="twitter" value="'.esc_attr($twitter).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserTwitter', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Instagram').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$instagram = (!empty(SP()->user->profileUser->instagram)) ? SP()->user->profileUser->instagram : '';
$tout .= '<input type="text" class="spControl" name="instagram" id="instagram" value="'.esc_attr($instagram).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserInstagram', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('LinkedIn').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$linkedin = (!empty(SP()->user->profileUser->linkedin)) ? SP()->user->profileUser->linkedin : '';
$tout .= '<input type="text" class="spControl" name="linkedin" id="linkedin" value="'.esc_attr($linkedin).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserLinkedIn', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('YouTube').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$youtube = (!empty(SP()->user->profileUser->youtube)) ? SP()->user->profileUser->youtube : '';
$tout .= '<input type="text" class="spControl" name="youtube" id="youtube" value="'.esc_attr($youtube).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserYouTube', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Skype').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$skype = (!empty(SP()->user->profileUser->skype)) ? SP()->user->profileUser->skype : '';
$tout .= '<input type="text" class="spControl" name="skype" id="skype" value="'.esc_attr($skype).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserSkype', $tout, $userid, $thisSlug);

// Maybe display AIM identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('AIM').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$aim = (!empty(SP()->user->profileUser->aim)) ? SP()->user->profileUser->aim : '';
	$tout .= '<input type="text" class="spControl" name="aim" id="aim" value="'.esc_attr($aim).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserAIM', $tout, $userid, $thisSlug);
}

// Maybe display Yahoo IM identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Yahoo IM').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$yim = (!empty(SP()->user->profileUser->yim)) ? SP()->user->profileUser->yim : '';
	$tout .= '<input type="text" class="spControl" name="yim" id="yim" value="'.esc_attr($yim).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserYahoo', $tout, $userid, $thisSlug);
}

// Maybe display ICQ identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('ICQ').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$icq = (!empty(SP()->user->profileUser->icq)) ? SP()->user->profileUser->icq : '';
	$tout .= '<input type="text" class="spControl" name="icq" id="icq" value="'.esc_attr($icq).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserICQ', $tout, $userid, $thisSlug);
}

// Maybe display Google Talk identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Google Talk').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$jabber = (!empty(SP()->user->profileUser->jabber)) ? SP()->user->profileUser->jabber : '';
	$tout .= '<input type="text" class="spControl" name="jabber" id="aim" value="'.esc_attr($jabber).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserGoogle', $tout, $userid, $thisSlug);
}

// Maybe display MSN identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('MSN').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$msn = (!empty(SP()->user->profileUser->msn)) ? SP()->user->profileUser->msn : '';
	$tout .= '<input type="text" class="spControl" name="msn" id="msn" value="'.esc_attr($msn).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserMSN', $tout, $userid, $thisSlug);
}

// Maybe display MY SPACE identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('MySpace').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$myspace = (!empty(SP()->user->profileUser->myspace)) ? SP()->user->profileUser->myspace : '';
	$tout .= '<input type="text" class="spControl" name="myspace" id="myspace" value="'.esc_attr($myspace).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserMySpace', $tout, $userid, $thisSlug);
}

// Maybe display Google+ identity
if (true == $display_deprecated_identities) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Google Plus').': </p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$googleplus = (!empty(SP()->user->profileUser->googleplus)) ? SP()->user->profileUser->googleplus : '';
	$tout .= '<input type="text" class="spControl" name="googleplus" id="googleplus" value="'.esc_attr($googleplus).'" />';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserGooglePlus', $tout, $userid, $thisSlug);
}

$out = apply_filters('sph_ProfileIdentitiesFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out .= sp_InsertBreak('echo=false&spacer=10px');

$out .= '<div class="spProfileFormSubmit">';
$out .= '<input type="submit" class="spSubmit" name="formsubmit" value="'.SP()->primitives->front_text('Update Identities').'" />';
$out .= '</div>';
$out .= '</form>';

$out .= "</div>\n";

$out = apply_filters('sph_ProfileIdentitiesForm', $out, $userid);
echo $out;
