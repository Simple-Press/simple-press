<?php
/*
Simple:Press
Admin Components Smileys Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_smileys_form() {
	$ajaxurl = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function(){
			spj.loadAjaxForm('sfsmileysform', 'sfreloadsm');

			$("#spSmileyListSet").sortable({
				placeholder: 'sortable-placeholder',
			});

			var button = $('#sf-upload-button'), interval;
			new AjaxUpload(button,{
				action: '<?php echo $ajaxurl; ?>',
				name: 'uploadfile',
				data: {
					saveloc : '<?php echo addslashes(SP_STORE_DIR.'/'.SP()->plugin->storage['smileys'].'/'); ?>'
				},
				onSubmit : function(file, ext){
					/* check for valid extension */
					if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
						$('#sf-upload-status').html('<p class="sf-upload-status-text"><?php echo esc_js(SP()->primitives->admin_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
						return false;
					}
					/* change button text, when user selects file */
					utext = '<?php echo esc_js(SP()->primitives->admin_text('Uploading')); ?>';
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
						$('#sfmsgspot').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Smiley uploaded!')); ?></p>');
						$('#sfmsgspot').fadeIn();
						$('#sfmsgspot').fadeOut(6000);
						$('#sfreloadsm').click();
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
<?php $ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=smileys', 'components-loader'); ?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfsmileysform" name="sfsmileys" enctype="multipart/form-data">
	<?php echo sp_create_nonce('forum-adminform_smileys'); ?>
    <?php spa_paint_open_tab(SP()->primitives->admin_text('Smileys'), true); ?>
        <div class="sf-panel">
            <fieldset class="sf-fieldset">
                <div class="sf-panel-body-top">
                    <h4><?php echo SP()->primitives->admin_text('Manage Custom Smileys') ?></h4>
                    <?php $loc = SP_STORE_DIR.'/'.SP()->plugin->storage['smileys'].'/'; ?>
                    <?php spa_paint_file(SP()->primitives->admin_text('Select smiley file to upload'), 'newsmileyfile', false, true, $loc); ?>
                    <?php echo spa_paint_help('custom-smileys') ?>
                </div>
                <div class="sf-form-row">
                    <span><?php echo SP()->primitives->admin_text('Re-order your Smileys by dragging and dropping the buttons below. To edit - click on the open control to the right') ?>.</span>
                </div>
                <div class="sf-form-row">
                    <?php do_action('sph_components_smileys_right_panel'); ?>
                    <?php spa_paint_custom_smileys(); ?>
                </div>
                <div class="sf-form-row">
                    <input type="submit" class="sf-button-primary" id="updatesmileys" name="saveit" value="<?php SP()->primitives->admin_etext('Update Smileys Component'); ?>" />
                </div>

            </fieldset>
        </div>
    <?php spa_paint_close_container(); ?>
	<?php spa_paint_close_tab(); ?>
	</form>
<?php
}
