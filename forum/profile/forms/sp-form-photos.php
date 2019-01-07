<?php
/*
Simple:Press
Profile Photos Form
$LastChangedDate: 2016-07-10 04:25:00 -0500 (Sun, 10 Jul 2016) $
$Rev: 14413 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

$ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile&targetaction=update-photos&user=$userid", 'profile'));
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormPhotos').ajaxForm({
        dataType: 'json',
		success: function(response) {
            jQuery('#spProfilePhotos').load('<?php echo $ajaxURL; ?>');
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
$msg = sp_text('On this panel, you may reference some personal photos or images that can be displayed in your profile.');
$msg.= sprintf(spa_text('There is a limit of %d photos that you can store in your profile.'), $spProfileOptions['photosmax']);
$out.= apply_filters('sph_profile_photos_header', $msg);
$out.= '</p>';
$out.= '<hr>';

$out.= '<div class="spProfilePhotos">';

if ($spProfileOptions['photosmax'] < 1) {
	$out.= '<p class="spProfileLabel">'.sp_text('Profile photos are not enabled on this forum').'</p>';
} else {
    $ajaxURL = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL."profile-save&amp;form=$thisSlug&amp;userid=$userid", 'profile-save'));
	$out.= '<form action="'.$ajaxURL.'" method="post" name="spProfileFormPhotos" id="spProfileFormPhotos" class="spProfileForm">';
	$out.= sp_create_nonce('forum-profile');

	$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
	$out = apply_filters('sph_ProfilePhotosFormTop', $out, $userid);

    $out.= '<div id="spProfilePhotos">';
    $tout = '';
	for ($x=0; $x < $spProfileOptions['photosmax']; $x++) {
    	$tout.= '<div class="spColumnSection spProfileLeftCol">';
		$tout.= '<p class="spProfileLabel">'.sp_text('Url to Photo').' '.($x+1).'</p>';
    	$tout.= '</div>';
    	$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
        $photo = (!empty($spProfileUser->photos[$x])) ? $spProfileUser->photos[$x] : '';
    	$tout.= '<div class="spColumnSection spProfileRightCol">';
		$tout.= "<p class='spProfileLabel'><input class='spControl' type='text' name='photo$x' value='$photo' /></p>";
    	$tout.= '</div>';
	}
    $out.= apply_filters('sph_ProfilePhotosLoop', $tout, $userid);
	$out.= '</div>';

	$out = apply_filters('sph_ProfilePhotosFormBottom', $out, $userid);
	$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

	$out.= sp_InsertBreak('echo=false&spacer=10px');

	$out.= '<div class="spProfileFormSubmit">';
	$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Photos').'" />';
	$out.= '</div>';
	$out.= '</form>';
}
$out.= '</div>'."\n";

$out = apply_filters('sph_ProfilePhotosForm', $out, $userid);
echo $out;
?>