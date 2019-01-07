<?php
/*
Simple:Press
Profile Overview Form
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
				$('#spProfileFormAccount').ajaxForm({
					dataType: 'json',
					success: function (response) {
						if (response.type == 'success') {
							spj.displayNotification(0, response.message);
							window.location.reload();
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
$msg = SP()->primitives->front_text('On this panel, you may edit your Account Settings. Please note, you cannot change your Login Name.');
$out .= apply_filters('sph_profile_account_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# start the form
$out .= '<div class="spProfileAccount">';

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormAccount" id="spProfileFormAccount" class="spProfileForm">';
$out .= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileAccountForm_top', $out, $userid);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Login Name').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="text" disabled="disabled" class="spControl" name="login" value="'.esc_attr(SP()->user->profileUser->user_login).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileAccountLoginName', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Email Address').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="hidden" class="spControl" name="curemail" id="curemail" value="'.esc_attr(SP()->user->profileUser->user_email).'" />';
$tout .= '<input type="text" class="spControl" name="email" id="email" value="'.esc_attr(SP()->user->profileUser->user_email).'" />';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserEmailAddress', $tout, $userid, $thisSlug);

$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('New Password').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="password" autocomplete="off" class="spControl" name="pass1" id="pass1" value="" />';
$tout .= '</div>';

$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Confirm New Password').': </p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$tout .= '<input type="password" autocomplete="off" class="spControl" name="pass2" id="pass2" value="" />';
$tout .= '<p class="description indicator-hint">'.SP()->primitives->front_text('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp;').'.</p>';
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserNewPassword', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfileAccountForm_bottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out .= sp_InsertBreak('echo=false&spacer=10px');

$out.= SP()->primitives->front_text('Note: Updating your account settings will force the profile page to reload.');

$out .= '<div class="spProfileFormSubmit">';
$out .= '<input type="submit" class="spSubmit" name="formsubmit" value="'.SP()->primitives->front_text('Update Account').'" />';
$out .= '</div>';
$out .= '</form>';

$out .= "</div>\n";

$out = apply_filters('sph_ProfileAccountForm', $out, $userid);
echo $out;
