<?php
/*
Simple:Press
Admin Profile Avatars Form
$LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
$Rev: 15799 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

function spa_profiles_avatars_pool_form(): void
{
	$ajaxurl = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			var button = $('#sf-upload-button'), interval;
			new AjaxUpload(button,{
				action: '<?php echo $ajaxurl; ?>',
				name: 'uploadfile',
				data: {
					saveloc : '<?php echo addslashes(SP_STORE_DIR."/".SP()->plugin->storage['avatar-pool'].'/'); ?>'
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
                                                $('#sfreloadpool').click();
						//site = "<?php echo SPAJAXURL.'profiles' ?>&amp;_wpnonce=<?php echo wp_create_nonce('profiles'); ?>&amp;targetaction=delavatar&amp;file=" + file;
						//$('<table style="width:100%"></table>').appendTo('#sf-avatar-pool').html('<tr><td class="spWFBorder" style="width:30%;text-align:center"><img class="sfavatarpool" src="<?php echo SPAVATARPOOLURL; ?>' + file + '" alt="" /></td><td class="spWFBorder" style="text-align:center;width:50%">' + file + '</td><td class="spWFBorder"><span title="<?php echo esc_js(SP()->primitives->admin_text('Delete Avatar')); ?>" class="sf-icon sf-delete spDeleteRowReload" data-url="' + site + '" data-reload="sfreloadpool"></span></td></tr>');
						//$('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Avatar Uploaded!')); ?></p>');
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
	}(window.spj = window.spj || {}, jQuery));
</script>

<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'profiles-loader&amp;saveform=avatars', 'profiles-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfavatarsform" name="sfavatars">
	<?php echo sp_create_nonce('forum-adminform_avatars'); ?>
<?php
	spa_paint_options_init();

    #== PROFILE OPTIONS Tab ============================================================

	spa_paint_open_tab(SP()->primitives->admin_text('Avatar Pool'));
		spa_paint_open_panel(); ?>
			<fieldset class="sf-fieldset">
                <div class="sf-panel-body-top">
                <h4><?php echo SP()->primitives->admin_text('Avatar Pool Upload'); ?></h4>
                <?php echo spa_paint_help('avatar-pool-upload') ?>

                <?php $loc = SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/'; ?>
				<?php spa_paint_file(SP()->primitives->admin_text('Select avatar to upload'), 'newavatar', false, true, $loc); ?>
                </div>
				<div class="sf-alert-block sf-info">
                    <?php SP()->primitives->admin_etext('Please be advised that Admin uploaded avatars for the avatar pool are NOT subject to the user uploaded avatar size limits.  So use caution when picking avatars for your avatar pool'); ?>
                </div>
            </fieldset>

		<?php spa_paint_close_panel();

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Avatar Pool'), true, 'avatar-pool');
				spa_paint_avatar_pool();
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_avatar_right_panel');
		spa_paint_close_container();

	spa_paint_close_tab();
?>
	</form>
<?php
}
