<?php
/*
Simple:Press
Admin Forums Custom Icons Form
$LastChangedDate: 2016-10-21 20:37:22 -0500 (Fri, 21 Oct 2016) $
$Rev: 14651 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

define('SFUPLOADER', wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));

function spa_forums_custom_icons_form() {
	global $spPaths;
?>
<script type="text/javascript">/*<![CDATA[*/
    jQuery(document).ready(function(){
    	spjAjaxForm('sfcustomiconsform', 'sfreloadci');

    	var button = jQuery('#sf-upload-button'), interval;
    	new AjaxUpload(button,{
    		action: '<?php echo SFUPLOADER; ?>',
    		name: 'uploadfile',
    	    data: {
    		    saveloc : '<?php echo addslashes(SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/'); ?>'
    	    },
    		onSubmit : function(file, ext){
                /* check for valid extension */
    			if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-text"><?php echo esc_js(spa_text('Only JPG, PNG or GIF files are allowed')); ?></p>');
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
                    site = "<?php echo SPAJAXURL.'forums' ?>&amp;_wpnonce=<?php echo wp_create_nonce('forums'); ?>&amp;targetaction=delicon&amp;file=" + file;
    				var count = document.getElementById('icon-count');
    				var icount = parseInt(count.value) + 1;
    				jQuery('<table id="icon' + icount + '" style="width:100%"></table>').appendTo('#sf-custom-icons').html('<tr><td class="spWFBorder" style="text-align:center;width:30%"><img class="sfcustomicon" src="<?php echo SFCUSTOMURL; ?>/' + file + '" alt="" /></td><td class="spWFBorder sflabel" style="text-align:center;width:50%">' + file + '</td><td class="spWFBorder" style="text-align:center"><img src="<?php echo SFCOMMONIMAGES; ?>' + 'delete.png' + '" title="<?php echo esc_js(spa_text('Delete custom icon')); ?>" alt="" class="spDeleteRow" data-url="' + site + '" data-target="icon' + icount + '" /></td></tr>');
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(spa_text('Custom icon uploaded!')); ?></p>');
                	jQuery('.ui-tooltip').hide();
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
	spa_paint_options_init();

	spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Custom Icons'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Group/Forum Custom Icons Upload'), true, 'custom-icon-upload');
				$loc = SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/';
				spa_paint_file(spa_text('Select custom icon file to upload'), 'newiconfile', false, true, $loc);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Group/Forum Custom Icons'), true, 'custom-icons');
			spa_paint_custom_icons();
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_forum_icons_right_panel');

		spa_paint_close_container();

		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}
?>