<?php
/*
Simple:Press
Admin Forums Custom Icons Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_forums_custom_icons_form() {
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
						site = "<?php echo SPAJAXURL.'forums' ?>&amp;_wpnonce=<?php echo wp_create_nonce('forums'); ?>&amp;targetaction=delicon&amp;file=" + file;
						var count = document.getElementById('icon-count');
						var icount = parseInt(count.value) + 1;
						$('#sf-custom-icons').append('<tr><td class="spWFBorder"><img class="sfcustomicon" src="<?php echo SPCUSTOMURL; ?>/' + file + '" alt="" /></td><td class="spWFBorder sflabel">' + file + '</td><td class="spWFBorder"><span title="<?php echo esc_js(SP()->primitives->admin_text('Delete custom icon')); ?>" class="sf-icon sf-delete spDeleteRow" data-url="' + site + '" data-target="icon' + icount + '"></span></td></tr>');
						//$('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Custom icon uploaded!')); ?></p>');
						$('.ui-tooltip').hide();
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

	spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Custom Icons'), true);
		?>
            <div class="sf-panel-body-top">
                <div class="sf-panel-body-top-left">
                    <h4><?php echo SP()->primitives->admin_text('Group/Forum Custom Icons Upload') ?></h4>
                </div>
                <div class="sf-panel-body-top-right sf-mobile-btns">
                    <?php echo spa_paint_help('custom-icon-upload') ?>
                    <?php
                    $loc = SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/';
					spa_paint_file(SP()->primitives->admin_text('Select custom icon file to upload'), 'newiconfile', false, true, $loc);
                    ?>
                </div>
            </div>
                <?php
	
		//spa_paint_open_panel();
		//	spa_paint_open_fieldset(SP()->primitives->admin_text('Group/Forum Custom Icons Upload'), true, 'custom-icon-upload');
		//		$loc = SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/';
		//		spa_paint_file(SP()->primitives->admin_text('Select custom icon file to upload'), 'newiconfile', false, true, $loc);
		//	spa_paint_close_fieldset();
		//spa_paint_close_panel();

		//spa_paint_open_panel();
		//	spa_paint_open_fieldset(SP()->primitives->admin_text('Group/Forum Custom Icons'), true, 'custom-icons');
			spa_paint_custom_icons();
		//	spa_paint_close_fieldset();
		//spa_paint_close_panel();

		do_action('sph_forum_icons_right_panel');

		//spa_paint_close_container();

		//echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}
