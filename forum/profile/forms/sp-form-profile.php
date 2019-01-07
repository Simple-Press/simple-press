<?php
/*
Simple:Press
Profile Profile Form
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
				$('#spProfileFormProfile').ajaxForm({
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
$out = '';
$out .= '<p>';
$msg = SP()->primitives->front_text('On this panel, you may edit your Profile. Please note, you cannot change your Login Name.');
$out .= apply_filters('sph_profile_profile_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# start the form
$out .= '<div class="spProfileProfile">';

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormProfile" id="spProfileFormProfile" class="spProfileForm">';
$out .= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileProfileFormTop', $out, $userid);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Login Name').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="text" disabled="disabled" class="spControl" name="login" value="'.esc_attr(SP()->user->profileUser->user_login).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserLoginName', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Display Name').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="hidden" class="spControl" name="oldname" id="oldname" value="'.SP()->displayFilters->name(SP()->user->profileUser->display_name).'" />';

# Check to see if the display name is allowed to be edited by the user.
# If it is not, disable the field.
$disabled_text = ($spProfileOptions['nameformat'] || SP()->user->thisUser->admin) ? '' : 'disabled="disabled" ';

$tout .= '<input type="text" '.$disabled_text.'class="spControl" name="display_name" id="display_name" value="'.SP()->displayFilters->name(SP()->user->profileUser->display_name).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserDisplayName', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('First Name').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="text" class="spControl" name="first_name" id="first_name" value="'.SP()->displayFilters->name(SP()->user->profileUser->first_name).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserFirstName', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Last Name').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="text" class="spControl" name="last_name" id="last_name" value="'.SP()->displayFilters->name(SP()->user->profileUser->last_name).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserLastName', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Website').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="text" class="spControl" name="website" id="website" value="'.SP()->displayFilters->url(SP()->user->profileUser->user_url).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserWebsite', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Location').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="text" class="spControl" name="location" id="location" value="'.SP()->displayFilters->title(SP()->user->profileUser->location).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserLocation', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Short Biography').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<textarea class="spControl" name="description" rows="4">'.SP()->editFilters->text(SP()->user->profileUser->description).'</textarea>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserBiography', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfileProfileFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out .= sp_InsertBreak('echo=false&spacer=10px');

$out .= '<div class="spProfileFormSubmit">';
$out .= '<input type="submit" class="spSubmit" name="formsubmit" value="'.SP()->primitives->front_text('Update Profile').'" />';
$out .= '</div>';
$out .= '</form>';

$out .= "</div>\n";

$out = apply_filters('sph_ProfileProfileForm', $out, $userid);
echo $out;
