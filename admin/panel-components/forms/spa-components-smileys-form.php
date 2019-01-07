<?php
/*
Simple:Press
Admin Components Smileys Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

define('SFUPLOADER', wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));

function spa_components_smileys_form() {
	global $spPaths;
?>
<script type= "text/javascript">/*<![CDATA[*/
	jQuery(document).ready(function(){
		spjAjaxForm('sfsmileysform', 'sfreloadsm');

		jQuery("#spSmileyListSet").sortable({
			placeholder: 'sortable-placeholder',
		});

		var button = jQuery('#sf-upload-button'), interval;
		new AjaxUpload(button,{
			action: '<?php echo SFUPLOADER; ?>',
			name: 'uploadfile',
			data: {
				saveloc : '<?php echo addslashes(SF_STORE_DIR.'/'.$spPaths['smileys'].'/'); ?>'
			},
			onSubmit : function(file, ext){
				/* check for valid extension */
				if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-text"><?php echo esc_js(spa_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
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
					jQuery('#sfmsgspot').html('<p class="sf-upload-status-success"><?php echo esc_js(spa_text('Smiley uploaded!')); ?></p>');
					jQuery('#sfmsgspot').fadeIn();
					jQuery('#sfmsgspot').fadeOut(6000);
					jQuery('#sfreloadsm').click();
				} else if (response==="invalid"){
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file has an invalid format!')); ?></p>');
				} else if (response==="exists") {
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file already exists!')); ?></p>');
				} else {
					jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Error uploading file!')); ?></p>');
				}
			}
		});
	});/*]]>*/
</script>
<?php
	$ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=smileys', 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfsmileysform" name="sfsmileys" enctype="multipart/form-data">
	<?php echo sp_create_nonce('forum-adminform_smileys'); ?>
<?php
	spa_paint_options_init();

	#== SMILEYS Tab ============================================================

	spa_paint_open_tab(spa_text('Components').' - '.spa_text('Smileys'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Custom Smiley Upload'), true, 'smiley-upload');
				$loc = SF_STORE_DIR.'/'.$spPaths['smileys'].'/';
				spa_paint_file(spa_text('Select smiley file to upload'), 'newsmileyfile', false, true, $loc);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_smileys_right_panel');

		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();

	spa_paint_open_nohead_tab(true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Custom Smileys'), true, 'custom-smileys');
				spa_paint_custom_smileys();
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="updatesmileys" name="saveit" value="<?php spa_etext('Update Smileys Component'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>