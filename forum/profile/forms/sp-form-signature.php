<?php
/*
Simple:Press
Profile Signature Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-sig&user=$userid", 'profile'));
?>
    <script>
		(function(spj, $, undefined) {
			$(document).ready(function () {
				/* ajax form and message */
				$('#spProfileFormSignature').ajaxForm({
					dataType: 'json',
					beforeSerialize: spj.editorGetSignature,
					success: function (response) {
						$('#spProfileSignaturePreview').load('<?php echo $ajaxURL; ?>');
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

sp_load_editor();

$out = '';
$out .= '<p>';
$msg = SP()->primitives->front_text('On this panel, you may edit your Signature.');
$out .= apply_filters('sph_profile_signature_header', $msg);
$out .= '</p>';
$out .= '<hr>';

$out .= '<div class="spProfileSignature">';

$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save'));
$out .= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormSignature" id="spProfileFormSignature" class="spProfileForm">';
$out .= sp_create_nonce('forum-profile');

$out .= '<div class="spEditor">';
$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileSignatureFormTop', $out, $userid);

# Signature Set
$out .= '<div class="spEditorSection">';
$out .= '<div class="spColumnSection spCenter">';
$out .= '<div class="spEditorTitle">'.SP()->primitives->front_text('Set up Your Signature').':</div><br />';
$out .= '</div>';
$out .= '</div>';

$out .= '<div id="spEditorContent">';
$value = SP()->editFilters->content(SP()->user->profileUser->signature);
$out .= sp_SetupSigEditor($value);

$spSigImageSize = SP()->options->get('sfsigimagesize');
$sigWidth       = SP()->primitives->front_text('width - none specified').', ';
$sigHeight      = SP()->primitives->front_text('height - none specified');
if ($spSigImageSize['sfsigwidth'] > 0) $sigWidth = SP()->primitives->front_text('width').' - '.$spSigImageSize['sfsigwidth'].', ';
if ($spSigImageSize['sfsigheight'] > 0) $sigHeight = SP()->primitives->front_text('height').' - '.$spSigImageSize['sfsigheight'];
$out .= '<p class="spCenter">'.SP()->primitives->front_text('Signature Image Size Limits (pixels)').': '.$sigWidth.$sigHeight.'</p>';
$out .= '<p class="spCenter">'.SP()->primitives->front_text('If you reset your signature, be sure to save it').'</p>';

$out .= '<div class="spProfileFormSubmit">';
# reset signature - plugins need to filter this input and provide their own with click listener to their js
$tout = '<input type="button" class="spSubmit spClearSignature" name="reset" value="'.SP()->primitives->front_text('Reset Signature').'" />';
$out .= apply_filters('sph_ProfileSignatureReset', $tout);
$out .= '<input type="submit" class="spSubmit" name="formsubmit" value="'.SP()->primitives->front_text('Update Signature').'" />';
$out .= '</div>';
$out .= '</div>';

$out = apply_filters('sph_SignaturesFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);
$out .= '</div>';
$out .= '</form>';

$out .= '<div class="spColumnSection spCenter">';
$out .= '<p class="spTextLeft"><br />'.SP()->primitives->front_text('Preview of Your Signature (update to see changes)').':</p><br />';
$out .= '<div id="spProfileSignaturePreview">';
$out .= sp_Signature('echo=0', SP()->user->profileUser->signature);
$out .= '</div>';
$out .= '</div>';

$out .= '</div>'."\n";

$out = apply_filters('sph_ProfileSignatureForm', $out, $userid);
echo $out;

?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function () {
			spj.editorOpen('postitem', 1);
			setTimeout(function () {
				spj.setProfileDataHeight();
			}, 750);
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
