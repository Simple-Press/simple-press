<?php
/*
Simple:Press
Profile Global Options Form
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
				$('#spProfileFormGlobal').ajaxForm({
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
$msg = SP()->primitives->front_text('On this panel, you may set your Global Options preferences.');
$out .= apply_filters('sph_profile_global_options_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# start the form
$out .= '<div class="spProfileGlobalOptions">';

$spProfileOptions = SP()->options->get('sfprofile');

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormGlobal" id="spProfileFormGlobal" class="spProfileForm">';
$out .= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileGlobalOptionsFormTop', $out, $userid);

$opts = SP()->options->get('sfmemberopts');
if ($opts['sfhidestatus']) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Hide Online Status').':</p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$checked = (SP()->user->profileUser->hidestatus) ? 'checked="checked" ' : '';
	$tout .= '<p class="spProfileLabel"><input type="checkbox" '.$checked.'name="hidestatus" id="sf-hidestatus" /><label for="sf-hidestatus"></label></p>';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserOnlineStatus', $tout, $userid, $thisSlug);
}

if ($spProfileOptions['nameformat']) {
	$tout = '';
	$tout .= '<div class="spColumnSection spProfileLeftCol">';
	$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Sync Forum and WP Display Name').':</p>';
	$tout .= '</div>';
	$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
	$tout .= '<div class="spColumnSection spProfileRightCol">';
	$checked = (SP()->user->profileUser->namesync) ? $checked = 'checked="checked" ' : '';
	$tout .= '<p class="spProfileLabel"><input type="checkbox" '.$checked.'name="namesync" id="sf-namesync" /><label for="sf-namesync"></label></p>';
	$tout .= '</div>';
	$out .= apply_filters('sph_ProfileUserSyncName', $tout, $userid, $thisSlug);
}

$out = apply_filters('sph_ProfileGlobalOptionsFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out .= sp_InsertBreak('echo=false&spacer=10px');

$out .= '<div class="spProfileFormSubmit">';
$out .= '<input type="submit" class="spSubmit" name="formsubmit" value="'.SP()->primitives->front_text('Update Global Options').'" />';
$out .= '</div>';
$out .= '</form>';

$out .= "</div>\n";

$out = apply_filters('sph_ProfileGlobalOptionsForm', $out, $userid);
echo $out;
