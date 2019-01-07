<?php
/*
Simple:Press
Profile Posting Options Form
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
				$('#spProfileFormPosting').ajaxForm({
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
$msg = SP()->primitives->front_text('On this panel, you may set your Posting Options preferences.');
$out .= apply_filters('sph_profile_posting_options_header', $msg);
$out .= '</p>';
$out .= '<hr>';

# start the form
$out .= '<div class="spProfilePostingOptions">';

$ajaxURL = wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save');
$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormPosting" id="spProfileFormPosting" class="spProfileForm">';
$out .= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfilePostingOptionsFormTop', $out, $userid);

# special section for editor selection at top
$tout = '';
$tout .= '<div class="spColumnSection spProfileLeftCol">';
$tout .= '<p class="spProfileLabel">'.SP()->primitives->front_text('Preferred Editor').':</p>';
$tout .= '</div>';
$tout .= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout .= '<div class="spColumnSection spProfileRightCol">';
$checked = (SP()->user->profileUser->editor == PLAINTEXT) ? $checked = 'checked="checked" ' : '';
$tout .= '<p class="spProfileLabel"><input type="radio" '.$checked.'name="editor" id="sf-plaintext" value="'.PLAINTEXT.'"/><label for="sf-plaintext"><span>'.SP()->primitives->front_text('Plain Textarea').'</span></label></p>';
$tout = apply_filters('sph_ProfilePostingOptionsFormEditors', $tout, SP()->user->profileUser);
$tout .= '</div>';
$out .= apply_filters('sph_ProfileUserEditor', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfilePostingOptionsFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out .= sp_InsertBreak('echo=false&spacer=10px');

$out .= '<div class="spProfileFormSubmit">';
$out .= '<input type="submit" class="spSubmit" name="formsubmit" value="'.SP()->primitives->front_text('Update Posting Options').'" />';
$out .= '</div>';
$out .= '</form>';

$out .= "</div>\n";

$out = apply_filters('sph_ProfilePostingOptionsForm', $out, $userid);
echo $out;
