<?php
/*
Simple:Press
Admin Forums Featured Images Form
$LastChangedDate: 2014-09-16 03:29:25 +0100 (Tue, 16 Sep 2014) $
$Rev: 11975 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_forums_featured_image_form() {
	$ajaxurl = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function(){
			spj.loadAjaxForm('sffeaturedimageform', 'sfreloadfi');

			var button = $('#sf-upload-button'), interval;
			new AjaxUpload(button,{
				action: '<?php echo $ajaxurl; ?>',
				name: 'uploadfile',
				data: {
					saveloc : '<?php echo addslashes(SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/'); ?>'
				},
				onSubmit : function(file, ext){
					/* check for valid extension */
					if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
						$('#sf-upload-status').html('<p class="sf-upload-status-text"><?php echo esc_js(SP()->primitives->admin_text('Only JPG, PNG or GIF files are allowed')); ?></p>');
						return false;
					}
					/* change button text, when user selects file */
					//utext = '<?php echo esc_js(SP()->primitives->admin_text('Uploading')); ?>';
					//button.text(utext);
					/* If you want to allow uploading only 1 file at time, you can disable upload button */
					this.disable();
					/* Uploding -> Uploading. -> Uploading... */
					//interval = window.setInterval(function(){
					//	var text = button.text();
					//	if (text.length < 13){
					//		button.text(text + '.');
					//	} else {
					//		button.text(utext);
					//	}
					//}, 200);
				},
				onComplete: function(file, response){
					$('#sf-upload-status').html('');
					//button.text('<?php echo esc_js(SP()->primitives->admin_text('Browse')); ?>');
					window.clearInterval(interval);
					/* re-enable upload button */
					this.enable();
					/* add file to the list */
					if (response==="success"){
						site = "<?php echo SPAJAXURL.'forums' ?>&amp;sfnonce=<?php echo wp_create_nonce('forums'); ?>&amp;targetaction=delimage&amp;file=" + file;
						$('#sf-featured-images').append('<tr><td class="spWFBorder"><img class="sffeaturedimage" src="<?php echo SPOGIMAGEURL; ?>/' + file + '" alt="" /></td><td class="spWFBorder">' + file + '</td><td class="spWFBorder"><span title="<?php echo esc_js(SP()->primitives->admin_text('Delete Feature Image')); ?>" class="sf-icon sf-delete spDeleteRowReload" data-url="' + site + '" data-reload="sfreloadfi"></span></td></tr>');
						//$('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Featured Image uploaded!')); ?></p>');
					} else if (response==="invalid"){
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file has an invalid format!')); ?></p>');
					} else if (response==="exists") {
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file already exists!')); ?></p>');
					} else {
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Error uploading file!')); ?></p>');
					}
				}
			});
		});/*]]>*/
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
	spa_paint_options_init();

	spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Featured Images'), true);
	?>
            <div class="sf-panel-body-top">
                <div class="sf-panel-body-top-left">
                    <h4><?php echo SP()->primitives->admin_text('Forum Featured Image Upload')?></h4>
                    <span><?php echo SP()->primitives->admin_text('')?></span>
                </div>
                <div class="sf-panel-body-top-right sf-mobile-btns">
                    <?php echo spa_paint_help('featured-image-upload') ?>
                    <?php
                    $loc = SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/';
					spa_paint_file(SP()->primitives->admin_text('Select image file to upload'), 'newimagefile', false, true, $loc);
                    ?>
                </div>
            </div>
                <?php
		//spa_paint_open_panel();
		//	spa_paint_open_fieldset(SP()->primitives->admin_text('Forum Featured Image Upload'), true, 'featured-image-upload');
		//		$loc = SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/';
		//		spa_paint_file(SP()->primitives->admin_text('Select image file to upload'), 'newimagefile', false, true, $loc);
		//	spa_paint_close_fieldset();
		//spa_paint_close_panel();

		//spa_paint_open_panel();
		//	spa_paint_open_fieldset(SP()->primitives->admin_text('Forum Featured Images'), true, 'featured-images');
			spa_paint_featured_images();
		//	spa_paint_close_fieldset();
		//spa_paint_close_panel();

		do_action('sph_forum_images_right_panel');

		//spa_paint_close_container();

		//echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}
