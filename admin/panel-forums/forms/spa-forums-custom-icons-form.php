<?php
/*
Simple:Press
Admin Forums Custom Icons Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

function spa_forums_custom_icons_form(): void
{
	$ajaxurl = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function(){
			spj.loadAjaxForm('sfcustomiconsform', 'sfreloadci');

			var button = $('#sf-upload-button'), interval;
			new AjaxUpload(button,{
				action: '<?php echo $ajaxurl; ?>',
				name: 'uploadfile',
				data: {
					saveloc : '<?php echo addslashes(SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/'); ?>'
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
							//button.text(text + '.');
					//	} else {
							//button.text(utext);
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
						
						// A random number that will be used to break caching.
						break_cache = Math.random();
						break_cache = break_cache.toString();
						
						site = "<?php echo SPAJAXURL.'forums' ?>&amp;_wpnonce=<?php echo wp_create_nonce('forums'); ?>&amp;targetaction=delicon&amp;file=" + file;
						//var count = document.getElementById('icon-count');
					    var icount = parseInt($('#icon-count').val()) + 1;

						var row_id = 'icon' + icount + '-' + (Math.random() + 1).toString(36).substring(7);
						$('#sf-custom-icons').append('<tr id="'+row_id+'"><td class="spWFBorder"><img class="sfcustomicon" src="<?php echo SPCUSTOMURL; ?>/' + file + '?break_cache=' + break_cache + '" alt="" /></td><td class="spWFBorder sflabel">' + file + '</td><td class="spWFBorder"><span title="<?php echo esc_js(SP()->primitives->admin_text('Delete custom icon')); ?>" class="sf-icon sf-delete spDeleteRow" data-url="' + site + '" data-target="' + row_id + '"></span></td></tr>');
						//$('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Custom icon uploaded!')); ?></p>');
						$('.ui-tooltip').hide();

						$('#icon-count').val(icount);
						window.spj.deleteRow.init();

					} else if (response==="invalid"){
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file has an invalid format!')); ?></p>');
					} else if (response==="exists") {
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file already exists!')); ?></p>');
					} else {
						$('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Error uploading file!')); ?></p>');
					}
				}
			});
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(SP()->primitives->admin_text('Custom Icons'), true);
		?>
            <div class="sf-panel">
                <fieldset class="sf-fieldset">
                    <div class="sf-panel-body-top">
                        <h4><?php echo SP()->primitives->admin_text('Group/Forum Custom Icons Upload') ?></h4>
                        <?php $loc = SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/'; ?>
                        <?php spa_paint_file(SP()->primitives->admin_text('Select custom icon file to upload'), 'newiconfile', false, true, $loc); ?>
                        <?php echo spa_paint_help('custom-icon-upload') ?>
                    </div>
                    <div class="sf-form-row">
                        <?php spa_paint_custom_icons(); ?>
                    </div>
                </fieldset>
            </div>

    <?php
	do_action('sph_forum_icons_right_panel');
	spa_paint_close_tab();
}
