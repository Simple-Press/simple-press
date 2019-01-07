<?php
/*
Simple:Press
Admin Profile Avatars Form
$LastChangedDate: 2016-11-04 19:36:49 -0500 (Fri, 04 Nov 2016) $
$Rev: 14701 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

define('SFUPLOADER', wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));

function spa_profiles_avatars_form() {
	global $spPaths;

	$sfoptions = spa_get_avatars_data();

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		spjAjaxForm('sfavatarsform', 'sfreloadav');
	});
</script>

<?php
	if ($sfoptions['sfshowavatars']) {
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#sfavataroptions").sortable({
			placeholder: 'sortable-placeholder',
			update: function () {
				jQuery("input#sfavataropts").val(jQuery("#sfavataroptions").sortable('serialize'));
			}
		});

		/* hide initially unavailable priorities */
		if (<?php echo($sfoptions['sfavataruploads'] ? '1' : '0'); ?> == false) jQuery('#aitem_2').hide();
		if (<?php echo($sfoptions['sfavatarpool'] ? '1' : '0'); ?> == false) jQuery('#aitem_4').hide();
		if (<?php echo($sfoptions['sfavatarremote'] ? '1' : '0'); ?> == false) jQuery('#aitem_5').hide();
		if (<?php echo($sfoptions['sfavatarreplace'] ? '0' : '1'); ?> == false) jQuery('#aitem_1').hide();

		var button = jQuery('#sf-upload-button'), interval;
		new AjaxUpload(button,{
			action: '<?php echo SFUPLOADER; ?>',
			name: 'uploadfile',
			data: {
				saveloc : '<?php echo addslashes(SF_STORE_DIR."/".$spPaths['avatars'].'/defaults/'); ?>'
			},
			onSubmit : function(file, ext){
				/* check for valid extension */
				if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
					return false;
				}
				/* change button text, when user selects file */
				utext = '<?php echo esc_js(spa_text('Uploading')); ?>';
				button.text(utext);
				/* If you want to allow uploading only 1 file at time, you can disable upload button */
				this.disable();
				/* Uploding -> Uploading. -> Uploading... */
				interval = window.setInterval(function(){
					var text = button.text();
					if (text.length < 13){
						button.text(text + '.');
					} else {
						button.text(utext);
					}
				}, 200);
			},
			onComplete: function(file, response){
				jQuery('#sf-upload-status').html('');
				button.text('<?php echo esc_js(spa_text('Browse')); ?>');
				window.clearInterval(interval);
				/* re-enable upload button */
				this.enable();
				/* add file to the list */
				if (response==="success"){
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(spa_text('Avatar Uploaded!')); ?></p>');
					jQuery('#sfreloadav').click();
				} else if (response==="invalid"){
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file has an invalid format!')); ?></p>');
				} else if (response==="exists") {
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file already exists!')); ?></p>');
				} else {
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Error uploading file!!')); ?></p>');
				}
			}
		});
	});

	function spjAv(avId) {
		jQuery(avId).toggle();
	}

</script>
<?php
}
	$ajaxURL = wp_nonce_url(SPAJAXURL.'profiles-loader&amp;saveform=avatars', 'profiles-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfavatarsform" name="sfavatars">
	<?php echo sp_create_nonce('forum-adminform_avatars'); ?>
<?php
	spa_paint_options_init();

	#== PROFILE OPTIONS Tab ============================================================

	spa_paint_open_tab(spa_text('Profiles').' - '.spa_text('Avatars'), !$sfoptions['sfshowavatars']);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Options'), true, 'avatar-options');
				spa_paint_checkbox(spa_text('Display avatars'), 'sfshowavatars', $sfoptions['sfshowavatars']);

		if ($sfoptions['sfshowavatars']) {

					spa_paint_input(spa_text('Maximum avatar display width (pixels)'), 'sfavatarsize', $sfoptions['sfavatarsize'], false, false);

					$checked = ($sfoptions['sfavataruploads']) ? ' checked="checked"' : '';
					?>
					<div class="sp-form-row">
					<input type="checkbox" id="sf-sfavataruploads" name="sfavataruploads" tabindex="102" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_2" />
					<label class="wp-core-ui" for="sf-sfavataruploads"><?php echo(spa_text('Enable avatar uploading')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					spa_paint_input(spa_text('Maximum avatar upload file size (bytes)'), 'sfavatarfilesize', $sfoptions['sfavatarfilesize'], false, false);
					spa_paint_checkbox(spa_text('Auo resize avatar uploads'), 'sfavatarresize', $sfoptions['sfavatarresize']);
					spa_paint_input(spa_text('Uploaded avatar resize quality (if resizing)'), 'sfavatarresizequality', $sfoptions['sfavatarresizequality'], false, false);

					$checked = ($sfoptions['sfavatarpool']) ? ' checked="checked"' : '';
					?>
					<div class="sp-form-row">
					<input type="checkbox" id="sf-sfavatarpool" name="sfavatarpool" tabindex="106" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_4" />
					<label class="wp-core-ui" for="sf-sfavatarpool"><?php echo(spa_text('Enable avatar pool selection')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					$checked = ($sfoptions['sfavatarremote']) ? ' checked="checked"' : '';
					?>
					<div class="sp-form-row">
					<input type="checkbox" id="sf-sfavatarremote" name="sfavatarremote" tabindex="105" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_5" />
					<label class="wp-core-ui" for="sf-sfavatarremote"><?php echo(sp_text('Enable remote avatars')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					$values = array(spa_text('G - Suitable for all'), spa_text('PG- Suitable for 13 and above'), spa_text('R - Suitable for 17 and above'), spa_text('X - Suitable for all adults'));
					spa_paint_radiogroup(spa_text('Gravatar max rating'), 'sfgmaxrating', $values, $sfoptions['sfgmaxrating'], false, true);

					$checked = ($sfoptions['sfavatarreplace']) ? ' checked="checked"' : '';
					?>
					<div class="sp-form-row">
					<input type="checkbox" id="sf-sfavatarreplace" name="sfavatarreplace" tabindex="111" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_1" />
					<label class="wp-core-ui" for="sf-sfavatarreplace"><?php echo(sp_text('Replace WP avatar with SP avatar')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					echo '<br /><div class="sfoptionerror">';
					spa_etext('Warning: If you want to replace WP avatars with SP avatars, make sure you dont have WP avatars in your avatar priorities (have it below SP Default Avatars) or you will have a circular reference');
					echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_profiles_avatar_left_panel');
			spa_paint_tab_right_cell();

			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Avatar Priorities'), true, 'avatar-priorities');

					spa_etext('Select the avatar dislay priority order by dragging and dropping the buttons below.	The top of the list is the highest priority order.	When an avatar is found for the current priority, it is used.  If none is found, the next priority is checked and so on.  An SP Default Avatar will always be found. Any avatar after the SP Default Avatar is essentially ignored');
					$list = array(0 => spa_text('Gravatars'), 1 => spa_text('WP Avatars'), 2 => spa_text('Uploaded Avatar'), 3 => spa_text('SP Default Avatars'), 4 => spa_text('Avatar Pool'), 5 => spa_text('Remote Avatar'));
					$a = '';

					echo '<div>';
					echo '<ul id="sfavataroptions" class="menu">';

					if ($sfoptions['sfavatarpriority']) {

						foreach ($sfoptions['sfavatarpriority'] as $priority) {
							echo '<li id="aitem_'.$priority.'" class="menu-item menu-item-depth-0"><span class="item-name">'.$list[$priority].'</span></li>';
							$a.='aitem[]='.$priority.'&';
						}

					}
					echo '</ul>';

					echo '<input type="text" class="inline_edit" size="70" id="sfavataropts" name="sfavataropts" value="'.rtrim($a, '&').'" />';
					echo '</div>';

					echo '<br /><div class="sfoptionerror">';
					spa_etext('Recommendation: If you make use of Gravatars we strongly recommend using our Gravatar Cache plugin which will boost overall performance of any view containing gravatars');
					echo '</div>';

				spa_paint_close_fieldset();
			spa_paint_close_panel();

			echo '</div>';

			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Default Avatar Upload'), true, 'avatar-default-upload');
					$loc = SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/';
					spa_paint_file(spa_text('Select avatar to upload'), 'newavatar', false, true, $loc);
					echo '<table><tr>';
					echo '<td class="sflabel"><small>';
					spa_etext('Please be advised that Admin uploaded default avatar replacements are NOT subject to the user uploaded avatar size limits. So use caution when picking avatars');
					echo '</small></td>';
					echo '</tr></table>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Available Default Avatars'), true, 'avatar-defaults');
					echo '<div id="avdefbox">';
					spa_paint_avatar_defaults();
					echo '</div>';
		}
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_avatar_right_panel');
?>
		<div class="sfform-submit-bar">
		   <input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Avatar Options'); ?>" />
		</div>
<?php
		spa_paint_close_container();
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>