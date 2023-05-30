<?php
/*
Simple:Press
Admin Profile Avatars Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_profiles_avatars_form() {
	$ajaxurl = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));
	$sfoptions = spa_get_avatars_data();
?>
<script>
	spj.loadAjaxForm('sfavatarsform', 'sfreloadav');
</script>

<?php
	if ($sfoptions['sfshowavatars']) {
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			$("#sfavataroptions").sortable({
				placeholder: 'sortable-placeholder',
				update: function () {
					$("input#sfavataropts").val($("#sfavataroptions").sortable('serialize'));
				}
			});

			/* hide initially unavailable priorities */
			if (<?php echo(isset($sfoptions['sfavataruploads']) && $sfoptions['sfavataruploads'] === true  ? '1' : '0'); ?> == false) $('#aitem_2').hide();
			if (<?php echo(isset($sfoptions['sfavatarpool']) && $sfoptions['sfavatarpool'] === true   ? '1' : '0'); ?> == false) $('#aitem_4').hide();
			if (<?php echo(isset($sfoptions['sfavatarremote'])  && $sfoptions['sfavatarremote'] === true  ? '1' : '0	'); ?> == false) $('#aitem_5').hide();
			if (<?php echo(isset($sfoptions['sfavatarreplace']) && $sfoptions['sfavatarreplace'] === true   ? '0' : '1'); ?> == false) $('#aitem_1').hide();

			var button = $('#sf-upload-button'), interval;
			new AjaxUpload(button,{
				action: '<?php echo $ajaxurl; ?>',
				name: 'uploadfile',
				data: {
					saveloc : '<?php echo addslashes(SP_STORE_DIR."/".SP()->plugin->storage['avatars'].'/defaults/'); ?>'
				},
				onSubmit : function(file, ext){
					/* check for valid extension */
					if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
						return false;
					}
					/* change button text, when user selects file */
					utext = '<?php echo esc_js(SP()->primitives->admin_text('Uploading')); ?>';
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
					$('#sf-upload-status').html('');
					button.text('<?php echo esc_js(SP()->primitives->admin_text('Browse')); ?>');
					window.clearInterval(interval);
					/* re-enable upload button */
					this.enable();
					/* add file to the list */
					if (response==="success"){
						$('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Avatar Uploaded!')); ?></p>');
						$('#sfreloadav').click();
					} else if (response==="invalid"){
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file has an invalid format!')); ?></p>');
					} else if (response==="exists") {
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file already exists!')); ?></p>');
					} else {
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Error uploading file!!')); ?></p>');
					}
				}
			});
		});

		spj.avatarPriority = function(avId) {
			$(avId).toggle();
		}
	}(window.spj = window.spj || {}, jQuery));
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

	spa_paint_open_tab(/*SP()->primitives->admin_text('Profiles').' - '.*/SP()->primitives->admin_text('Avatars'), !$sfoptions['sfshowavatars']);
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Avatar Options'), true, 'avatar-options');
				spa_paint_checkbox(SP()->primitives->admin_text('Display avatars'), 'sfshowavatars', $sfoptions['sfshowavatars']);

		if ($sfoptions['sfshowavatars']) {

					spa_paint_input(SP()->primitives->admin_text('Maximum avatar display width (pixels)'), 'sfavatarsize', $sfoptions['sfavatarsize'], false, false);

					$checked = isset($sfoptions['sfavataruploads']) && $sfoptions['sfavataruploads'] === true ? ' checked="checked"' : '';
					?>
					<div class="sf-form-row">
					<input type="checkbox" id="sf-sfavataruploads" name="sfavataruploads" tabindex="102" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_2" />
					<label class="wp-core-ui" for="sf-sfavataruploads"><?php echo(SP()->primitives->admin_text('Enable avatar uploading')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					spa_paint_input(SP()->primitives->admin_text('Maximum avatar upload file size (bytes)'), 'sfavatarfilesize', $sfoptions['sfavatarfilesize'], false, false);
					spa_paint_checkbox(SP()->primitives->admin_text('Auo resize avatar uploads'), 'sfavatarresize', $sfoptions['sfavatarresize']);
					spa_paint_input(SP()->primitives->admin_text('Uploaded avatar resize quality (if resizing)'), 'sfavatarresizequality', $sfoptions['sfavatarresizequality'], false, false);

					$checked = isset($sfoptions['sfavatarpool']) && $sfoptions['sfavatarpool'] === true ? ' checked="checked"' : '';
					?>
					<div class="sf-form-row">
					<input type="checkbox" id="sf-sfavatarpool" name="sfavatarpool" tabindex="106" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_4" />
					<label class="wp-core-ui" for="sf-sfavatarpool"><?php echo(SP()->primitives->admin_text('Enable avatar pool selection')); ?></label>
					<div class="clearboth"></div></div>
					<?php


					$checked = isset($sfoptions['sfavatarremote']) && $sfoptions['sfavatarremote'] === true ? ' checked="checked"' : '';
					?>
					<div class="sf-form-row">
					<input type="checkbox" id="sf-sfavatarremote" name="sfavatarremote" tabindex="105" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_5" />
					<label class="wp-core-ui" for="sf-sfavatarremote"><?php echo(SP()->primitives->front_text('Enable remote avatars')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					$values = array(SP()->primitives->admin_text('G - Suitable for all'), SP()->primitives->admin_text('PG- Suitable for 13 and above'), SP()->primitives->admin_text('R - Suitable for 17 and above'), SP()->primitives->admin_text('X - Suitable for all adults'));
					spa_paint_radiogroup(SP()->primitives->admin_text('Gravatar max rating'), 'sfgmaxrating', $values, $sfoptions['sfgmaxrating'], false, true);

					$checked = isset($sfoptions['sfavatarreplace']) && $sfoptions['sfavatarreplace'] === true ? ' checked="checked"' : '';
					?>
					<div class="sf-form-row">
					<input type="checkbox" id="sf-sfavatarreplace" name="sfavatarreplace" tabindex="111" <?php echo($checked); ?> class="spProfileAvatarUpdate" data-target="#aitem_1" />
					<label class="wp-core-ui" for="sf-sfavatarreplace"><?php echo(SP()->primitives->front_text('Replace WP avatar with SP avatar')); ?></label>
					<div class="clearboth"></div></div>
					<?php

					echo '<div class="sf-alert-block sf-info">';
					SP()->primitives->admin_etext('Warning: If you want to replace WP avatars with SP avatars, make sure you don\'t have WP avatars in your avatar priorities (have it below SP Default Avatars) or you will have a circular reference');
					echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_profiles_avatar_left_panel');
			spa_paint_tab_right_cell();

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Avatar Priorities'), true, 'avatar-priorities');
                                        echo '<div class="sf-alert-block sf-info">';
					SP()->primitives->admin_etext('Select the avatar dislay priority order by dragging and dropping the buttons below.	The top of the list is the highest priority order.	When an avatar is found for the current priority, it is used.  If none is found, the next priority is checked and so on.  An SP Default Avatar will always be found. Any avatar after the SP Default Avatar is essentially ignored');
					echo '</div>';
                                        $list = array(0 => SP()->primitives->admin_text('Gravatars'), 1 => SP()->primitives->admin_text('WP Avatars'), 2 => SP()->primitives->admin_text('Uploaded Avatar'), 3 => SP()->primitives->admin_text('SP Default Avatars'), 4 => SP()->primitives->admin_text('Avatar Pool'), 5 => SP()->primitives->admin_text('Remote Avatar'));
					$a = '';

					echo '<div>';
					echo '<ul id="sfavataroptions" class="sf-list">';

					if ($sfoptions['sfavatarpriority']) {

						foreach ($sfoptions['sfavatarpriority'] as $priority) {
							echo '<li id="aitem_'.$priority.'" class="sf-list-item sf-list-item-depth-0 sf-full-width"><span class="sf-item-name">'.$list[$priority].'</span></li>';
							$a.='aitem[]='.$priority.'&';
						}

					}
					echo '</ul>';

					echo '<input type="hidden" size="70" id="sfavataropts" name="sfavataropts" value="'.rtrim($a, '&').'" />';
					echo '</div>';

					echo '<div class="sf-alert-block sf-info">';
					SP()->primitives->admin_etext('Recommendation: If you make use of Gravatars we strongly recommend using our Gravatar Cache plugin which will boost overall performance of any view containing gravatars');
					echo '</div>';

				spa_paint_close_fieldset();
			spa_paint_close_panel();

			//echo '</div>';

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Default Avatar Upload'), true, 'avatar-default-upload');
					$loc = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/defaults/';
					echo '<div class="sf-form-row">';
					spa_paint_file(SP()->primitives->admin_text('Select avatar to upload'), 'newavatar', false, true, $loc);
					echo '</div>';
					echo '<div class="sf-alert-block sf-info">';
					SP()->primitives->admin_etext('Please be advised that Admin uploaded default avatar replacements are NOT subject to the user uploaded avatar size limits. So use caution when picking avatars');
					echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Available Default Avatars'), true, 'avatar-defaults');
					echo '<div id="avdefbox">';
					spa_paint_avatar_defaults();
					echo '</div>';
		}
		spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_avatar_right_panel');
?>
		<div class="sf-form-submit-bar">
		   <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Avatar Options'); ?>" />
		</div>
<?php
		spa_paint_close_container();
	spa_paint_close_tab();
?>
	</form>
<?php
}
