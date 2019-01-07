<?php
/*
Simple:Press
Profile Avatars Form
$LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
$Rev: 15799 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

$ajaxURL1 = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-display-avatar&user=$userid", 'profile'));
$ajaxURL2 = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-uploaded-avatar&user=$userid", 'profile'));
$ajaxURL3 = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-pool-avatar&user=$userid", 'profile'));
$ajaxURL4 = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-remote-avatar&user=$userid", 'profile'));
?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				/* ajax form and message */
				$('#spProfileForm1').ajaxForm({
					dataType: 'json',
					success: function (response) {
						$('#spProfileDisplayAvatar').load('<?php echo $ajaxURL1; ?>');
						$('#spAvatarUpload').load('<?php echo $ajaxURL2; ?>');
						if (response.type == 'success') {
							spj.displayNotification(0, response.message);
						} else {
							spj.displayNotification(1, response.message);
						}
					}
				});
				$('#spProfileForm2').ajaxForm({
					dataType: 'json',
					success: function (response) {
						$('#spProfileDisplayAvatar').load('<?php echo $ajaxURL1; ?>');
						$('#spAvatarPool').load('<?php echo $ajaxURL3; ?>');
						if (response.type == 'success') {
							spj.displayNotification(0, response.message);
						} else {
							spj.displayNotification(1, response.message);
						}
					}
				});
				$('#spProfileForm3').ajaxForm({
					dataType: 'json',
					success: function (response) {
						$('#spProfileDisplayAvatar').load('<?php echo $ajaxURL1; ?>');
						$('#spRemoteAvatar').load('<?php echo $ajaxURL4; ?>');
						if (response.type == 'success') {
							spj.displayNotification(0, response.message);
						} else {
							spj.displayNotification(1, response.message);
						}
					}
				});

				$('#avatarbutton').click(function () {
					$('#avatarupload').click();
				});
				$('#avatarupload').on('change', function () {
					$('#dummy').val($('#avatarupload').val());
				});
			});
		}(window.spj = window.spj || {}, jQuery));
    </script>
<?php
$out = '';
$out .= '<p>';
$msg = SP()->primitives->front_text('On this panel, you may update your Avatar. Depending on Forum Admin settings, you may have multiple ways to select an Avatar.');
$out .= apply_filters('sph_profile_avatar_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# start the form
$out .= '<div class="spProfileAvatar">';

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileAvatarFormTop', $out, $userid);

# display avatar priorities
$out .= '<fieldset>';
$out .= '<legend>'.SP()->primitives->front_text('Current Displayed Avatar').'</legend>';
$out .= '<div class="spColumnSection spProfileLeftHalf">';
$list = array(0 => SP()->primitives->front_text('From gravatar.com'), 1 => SP()->primitives->front_text('WordPress Avatar Setting'), 2 => SP()->primitives->front_text('Uploaded Avatar'), 3 => SP()->primitives->front_text('Forum Default Avatars'), 4 => SP()->primitives->front_text('Forum Avatar Pool'), 5 => SP()->primitives->front_text('Remote Avatar'));
$out .= '<p>'.SP()->primitives->front_text('This forum searches and selects a member avatar in the following priority sequence until one is found').':</p><br />';
$out .= '<ol>';
foreach ($spAvatars['sfavatarpriority'] as $priority) {
	$out .= '<li>'.$list[$priority].'</li>';
	if ($priority == 3) break; # done with priorities if we reach default avatars since others are inactive then
}
$out .= '</ol>';
$out .= '</div>';

$out .= '<div class="spColumnSection spProfileSpacerCol"></div>';

# Avatar currently used by forum
$out .= '<div class="spColumnSection spProfileRightHalf">';
$out .= '<p class="spCenter">'.SP()->primitives->front_text('Current Displayed Avatar').':<br /><br />';
$out .= '<div id="spProfileDisplayAvatar">';
$out .= sp_UserAvatar('tagClass=spCenter&context=user&echo=0', SP()->user->profileUser);
$out .= '</div>';
$out .= '</p>';
$out = apply_filters('sph_ProfileAvatarDisplay', $out, SP()->user->profileUser);
$out .= '</div>';
$out .= '</fieldset>';

# message about avatar selection
$out .= '<p><br />'.SP()->primitives->front_text('You may update your avatar from the choices below.').'</p>';
$out .= '<hr>';

foreach ($spAvatars['sfavatarpriority'] as $priority) {
	switch ($priority) {
		case 0: # gravatar
			break;

		case 1: # wp avatar
			$out .= '<fieldset><legend>'.SP()->primitives->front_text('WordPress Avatar').'</legend>';
			$out .= '<p>'.SP()->primitives->front_text('Select your avatar').' <a href="'.admin_url('profile.php').'">'.SP()->primitives->front_text('with your WordPress profile').'</a>.</p>';
			$out .= '</fieldset>';
			break;

		case 2: # avatar uploading
			if (($spAvatars['sfavataruploads'] && SP()->auths->get('upload_avatars', '', $userid)) || (SP()->user->thisUser->admin)) {
				$out .= '<fieldset><legend>'.SP()->primitives->front_text('Upload An Avatar').'</legend>';
				$out .= '<div class="spColumnSection spProfileLeftHalf">';
				if (is_writable(SP_STORE_DIR."/".SP()->plugin->storage['avatars']."/")) {
					$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile-save&amp;form=avatar-upload&amp;userid=".$userid, 'profile-save'));
					$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileForm1" id="spProfileForm1" class="spProfileForm" enctype="multipart/form-data">';
					$out .= sp_create_nonce('forum-profile');
					$out .= '<div class="spProfileFormSubmit">';
					$out .= '<input type="text" class="spControl" name="dummy" id="dummy" value="" readonly="readonly" />';
					$out .= '<input id="avatarbutton" class="spSubmit" type="button" value="'.SP()->primitives->front_text('Browse').'" />';
					$out .= '<input id="avatarupload" type="file" style="visibility: hidden;" name="avatar-upload" />';
					$out .= '</div>';
					$out .= '<p class="spCenter">';
					$out .= SP()->primitives->front_text('Files accepted: GIF, PNG, JPG and JPEG').'<br />';
					$out .= SP()->primitives->front_text('Maximum width displayed').': '.$spAvatars['sfavatarsize'].' '.SP()->primitives->front_text('pixels').'<br />';
					$out .= SP()->primitives->front_text('Maximum filesize').': '.$spAvatars['sfavatarfilesize'].' '.SP()->primitives->front_text('bytes');
					$out .= '</p>';
					$out .= '<div class="spProfileFormSubmit">';
					$out .= '<input type="submit" class="spSubmit" name="formsubmit1" value="'.SP()->primitives->front_text('Upload Avatar').'" />';
					$out .= '</div>';
					$out .= '</form>';
				} else {
					$out .= '<div id="sf-upload-status">';
					$out .= '<p class="sf-upload-status-fail">'.SP()->primitives->front_text('Sorry, uploads disabled! Storage location does not exist or is not writable. Please contact a forum Admin.').'</p>';
					$out .= '</div>';
				}
				$out .= '</div>';

				$out .= '<div class="spColumnSection spProfileSpacerCol"></div>';

				# display current uploaded avatar if there is one
				$out .= '<div class="spColumnSection spProfileRightHalf">';
				$out .= '<p class="spCenter">'.SP()->primitives->front_text('Current Uploaded Avatar').':<br /><br /></p>';
				$out .= '<div id="spAvatarUpload" class="spCenter">';
				if (!empty(SP()->user->profileUser->avatar['uploaded'])) {
					$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;user=$userid&amp;avatarremove=1", 'profile'));
					$target  = 'spAvatarUpload';
					$spinner = SPCOMMONIMAGES.'working.gif';
					$out .= '<img src="'.esc_url(SPAVATARURL.SP()->user->profileUser->avatar['uploaded']).'" alt="" /><br /><br />';
					$out .= "<p class='spCenter'><input type='button' class='spSubmit' id='spDeleteUploadedAvatar' value='".SP()->primitives->front_text('Remove Uploaded Avatar')."' data-url='$ajaxURL' data-target='$target' data-spinner='$spinner' /></p>";
				} else {
					$out .= '<p class="spCenter">'.SP()->primitives->front_text('No avatar currently uploaded').'<br /><br /></p>';
				}
				$out .= '</div>';
				$out .= '</div>';
				$out .= '</fieldset>';
			}
			break;

		case 3: #default
			break 2; # stop displaying avatar options since none can be used after this one

		case 4: # avatar pool
			if ($spAvatars['sfavatarpool']) {
				$out .= '<fieldset><legend>'.SP()->primitives->front_text('Select Avatar From Pool').'</legend>';
				$out .= '<div class="spColumnSection spProfileLeftHalf">';
				$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile-save&amp;form=avatar-pool&amp;userid=".$userid, 'profile-save'));
				$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileForm2" id="spProfileForm2" class="spProfileForm">';
				$out .= sp_create_nonce('forum-profile');
				$out .= '<p class="spProfileLabel spCenter"><input class="spControl" type="text" name="spPoolAvatar" id="spPoolAvatar" value="" /></p>';
				$site     = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;targetaction=avatarpool&amp;user=".$userid, 'profile'));
				$title    = esc_attr(SP()->primitives->front_text('Avatar Pool'));
				$position = 'center';

				$out .= "<p class='spCenter'>";
				$out .= "<input type='button' id='spavpool' class='spSubmit spShowAvatarPool' data-site='$site' data-label='$title' data-width='500' data-height='0' data-align='$position' value='".SP()->primitives->front_text('Browse Avatar Pool')."' />";
				$out .= '<br /><br />'.SP()->primitives->front_text('Select the browse button above to select from the available avatars in the avatar pool').'</p>';
				$out .= '<div class="spProfileFormSubmit">';

				$out .= '<input type="submit" class="spSubmit" name="formsubmit2" value="'.SP()->primitives->front_text('Save Pool Avatar').'" />';

				$out .= '</div>';
				$out .= '</form>';
				$out .= '</div>';

				$out .= '<div class="spColumnSection spProfileSpacerCol"></div>';

				# display current selected pool avatar if there is one
				$out .= '<div class="spColumnSection spProfileRightHalf">';
				$out .= '<p class="spCenter">'.SP()->primitives->front_text('Current Pool Avatar').':<br /><br /></p>';
				$out .= '<div id="spAvatarPool" class="spCenter">';
				if (!empty(SP()->user->profileUser->avatar['pool'])) {
					$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&amp;user=$userid&amp;poolremove=1", 'profile'));
					$target  = 'spAvatarPool';
					$spinner = SPCOMMONIMAGES.'working.gif';
					$out .= '<img src="'.esc_url(SPAVATARPOOLURL.SP()->user->profileUser->avatar['pool']).'" alt="" /><br /><br />';
					$out .= "<div id='spPoolStatus'><p class='spCenter'><input type='button' class='spSubmit' id='spDeletePoolAvatar' value='".SP()->primitives->front_text('Remove Pool Avatar')."' data-url='$ajaxURL' data-target='$target' data-spinner='$spinner' /></p></div>";
				} else {
					$out .= '<div id="spPoolStatus"><p class="spCenter">'.SP()->primitives->front_text('No pool avatar currently selected').'<br /><br /></p></div>';
				}
				$out .= '</div>';
				$out .= '</div>';
				$out .= '</fieldset>';
			}
			break;

		case 5: # remote avatar
			if ($spAvatars['sfavatarremote']) {
				$out .= '<fieldset><legend>'.SP()->primitives->front_text('Select Remote Avatar').'</legend>';
				$out .= '<div class="spColumnSection spProfileLeftHalf">';
				$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile-save&amp;form=avatar-remote&amp;userid=".$userid, 'profile-save'));
				$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileForm3" id="spProfileForm3" class="spProfileForm">';
				$out .= sp_create_nonce('forum-profile');
				$out .= '<p class="spCenter">'.SP()->primitives->front_text('Enter the URL for the remote avatar.');
				$avatar = (!empty(SP()->user->profileUser->avatar['remote'])) ? esc_url(SP()->user->profileUser->avatar['remote']) : '';
				$out .= '<p class="spProfileLabel spCenter"><input class="spControl" type="text" name="spAvatarRemote" id="spAvatarRemote" value="'.$avatar.'" /></p>';
				$out .= '<br /><p class="spCenter">'.SP()->primitives->front_text('To remove a remote avatar, empty URL input field and save').'</p>';
				$out .= '<div class="spProfileFormSubmit">';
				$out .= '<input type="submit" class="spSubmit" name="formsubmit3" value="'.SP()->primitives->front_text('Save Remote Avatar').'" />';
				$out .= '</div>';
				$out .= '</form>';
				$out .= '</div>';

				$out .= '<div class="spColumnSection spProfileSpacerCol"></div>';

				# display current selected remote avatar if there is one
				$out .= '<div class="spColumnSection spProfileRightHalf">';
				$out .= '<p class="spCenter">'.SP()->primitives->front_text('Current Remote Avatar').':<br /><br /></p>';
				$out .= '<div id="spRemoteAvatar" class="spCenter">';
				if (!empty(SP()->user->profileUser->avatar['remote'])) {
					$out .= '<img src="'.esc_url(SP()->user->profileUser->avatar['remote']).'" alt="" /><br /><br />';
				} else {
					$out .= '<p class="spCenter">'.SP()->primitives->front_text('No remote avatar currently selected').'<br /><br /></p>';
				}
				$out .= '</div>';
				$out .= '</div>';
				$out .= '</fieldset>';
			}
			break;
	}
}

$out = apply_filters('sph_ProfileAvatarFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out .= "</div>\n";

$out = apply_filters('sph_ProfileAvatarForm', $out, $userid);
echo $out;
