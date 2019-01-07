<?php
/*
Simple:Press
Profile Identities Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormIdentities').ajaxForm({
        dataType: 'json',
		success: function(response) {
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
$msg = sp_text('On this panel, you may edit your Online Identities. Please enter only account names and not a URL.');
$out.= apply_filters('sph_profile_identities_header', $msg);
$out.= '</p>';
$out.= '<hr>';

# start the form
$out.= '<div class="spProfileIdentities">';

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out.= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormIdentities" id="spProfileFormIdentities" class="spProfileForm">';
$out.= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileIdentitiesFormTop', $out, $userid);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('AIM').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$aim = (!empty($spProfileUser->aim)) ? $spProfileUser->aim : '';
$tout.= '<input type="text" class="spControl" name="aim" id="aim" value="'.esc_attr($aim).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserAIM', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Yahoo IM').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$yim = (!empty($spProfileUser->yim)) ? $spProfileUser->yim : '';
$tout.= '<input type="text" class="spControl" name="yim" id="yim" value="'.esc_attr($yim).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserYahoo', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('ICQ').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$icq = (!empty($spProfileUser->icq)) ? $spProfileUser->icq : '';
$tout.= '<input type="text" class="spControl" name="icq" id="icq" value="'.esc_attr($icq).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserICQ', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Google Talk').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$jabber = (!empty($spProfileUser->jabber)) ? $spProfileUser->jabber : '';
$tout.= '<input type="text" class="spControl" name="jabber" id="aim" value="'.esc_attr($jabber).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserGoogle', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('MSN').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$msn = (!empty($spProfileUser->msn)) ? $spProfileUser->msn : '';
$tout.= '<input type="text" class="spControl" name="msn" id="msn" value="'.esc_attr($msn).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserMSN', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Skype').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$skype = (!empty($spProfileUser->skype)) ? $spProfileUser->skype : '';
$tout.= '<input type="text" class="spControl" name="skype" id="skype" value="'.esc_attr($skype).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserSkype', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('MySpace').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$myspace = (!empty($spProfileUser->myspace)) ? $spProfileUser->myspace : '';
$tout.= '<input type="text" class="spControl" name="myspace" id="myspace" value="'.esc_attr($myspace).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserMySpace', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Facebook').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$facebook = (!empty($spProfileUser->facebook)) ? $spProfileUser->facebook : '';
$tout.= '<input type="text" class="spControl" name="facebook" id="facebook" value="'.esc_attr($facebook).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserFacebook', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Twitter').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$twitter = (!empty($spProfileUser->twitter)) ? $spProfileUser->twitter : '';
$tout.= '<input type="text" class="spControl" name="twitter" id="twitter" value="'.esc_attr($twitter).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserTwitter', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('LinkedIn').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$linkedin = (!empty($spProfileUser->linkedin)) ? $spProfileUser->linkedin : '';
$tout.= '<input type="text" class="spControl" name="linkedin" id="linkedin" value="'.esc_attr($linkedin).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserLinkedIn', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('YouTube').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$youtube = (!empty($spProfileUser->youtube)) ? $spProfileUser->youtube : '';
$tout.= '<input type="text" class="spControl" name="youtube" id="youtube" value="'.esc_attr($youtube).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserYouTube', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Google Plus').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$googleplus = (!empty($spProfileUser->googleplus)) ? $spProfileUser->googleplus : '';
$tout.= '<input type="text" class="spControl" name="googleplus" id="googleplus" value="'.esc_attr($googleplus).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserGooglePlus', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfileIdentitiesFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out.= sp_InsertBreak('echo=false&spacer=10px');

$out.= '<div class="spProfileFormSubmit">';
$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Identities').'" />';
$out.= '</div>';
$out.= '</form>';

$out.= "</div>\n";

$out = apply_filters('sph_ProfileIdentitiesForm', $out, $userid);
echo $out;
?>