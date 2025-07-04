<?php
/*
Simple:Press
Admin Forums Featured Images Form
$LastChangedDate: 2014-09-16 03:29:25 +0100 (Tue, 16 Sep 2014) $
$Rev: 11975 $
*/

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

function spa_forums_featured_image_form() {
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function(){
			spj.loadAjaxForm('sffeaturedimageform', 'sfreloadfi');

			var button = $('#sf-upload-button'), interval;
			new AjaxUpload(button,{
                action: '<?php echo esc_url_raw(SPAJAXURL . "uploader&_wpnonce=" . wp_create_nonce('uploader')); ?>',
                name: 'uploadfile',
				data: {
					saveloc : '<?php echo esc_js(SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/'); ?>'
				},
				onSubmit : function(file, ext){
					/* check for valid extension */
					if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
						$('#sf-upload-status').html('<p class="sf-upload-status-text"><?php echo esc_js(SP()->primitives->admin_text('Only JPG, PNG or GIF files are allowed')); ?></p>');
						return false;
					}
					this.disable();
				},
				onComplete: function(file, response){
					$('#sf-upload-status').html('');
					window.clearInterval(interval);
					/* re-enable upload button */
					this.enable();
					/* add file to the list */
					if (response==="success"){
						// A random number that will be used to break caching.
						break_cache = Math.random();
						break_cache = break_cache.toString();

						site = "<?php echo esc_js(SPAJAXURL.'forums'); ?>&amp;_wpnonce=<?php echo esc_js(wp_create_nonce('forums')); ?>&amp;targetaction=delimage&amp;file=" + file;
						var icount = parseInt($('#img-count').val()) + 1;
						var row_id = 'img' + icount + '-' + (Math.random() + 1).toString(36).substring(7);

						$('#sf-featured-images').append('<tr id="'+row_id+'"><td class="spWFBorder"><img class="sffeaturedimage" src="<?php echo esc_url(SPOGIMAGEURL); ?>/' + file + '?break_cache=' + break_cache + '" alt="" /></td><td class="spWFBorder">' + file + '</td><td class="spWFBorder"><span title="<?php echo esc_js(SP()->primitives->admin_text('Delete Feature Image')); ?>" class="sf-icon sf-delete spDeleteRowReload" data-url="' + site + '" data-target="' + row_id + '" data-reload="sfreloadfi"></span></td></tr>');
						$('#img-count').val(icount);
					
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
    <?php spa_paint_open_tab(esc_attr(SP()->primitives->admin_text('Featured Images')), true); ?>
        <div class="sf-panel">
            <fieldset class="sf-fieldset">
                <div class="sf-panel-body-top">
                    <h4><?php SP()->primitives->admin_etext('Forum Featured Image Upload')?></h4>
                    <?php
                        $loc = SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/';
                        spa_paint_file(esc_attr(SP()->primitives->admin_text('Select image file to upload')), 'newimagefile', false, true, $loc);
                    ?>
                    <?php spa_paint_help('featured-image-upload') ?>
                </div>
                <div class="sf-alert-block sf-info">
                    <?php SP()->primitives->admin_etext('Notice: Currently, featured images, if one exists, may be used for the Open Graph meta tag and, for this use, images are recommended to be 200px x 200px.' ); ?>
                </div>
                <div class="sf-form-row">
                    <?php spa_paint_featured_images(); ?>
                </div>
            </fieldset>
        </div>
	<?php spa_paint_close_container(); ?>
	<?php spa_paint_close_tab(); ?>
    <?php do_action('sph_forum_images_right_panel'); ?>
<?php
}
