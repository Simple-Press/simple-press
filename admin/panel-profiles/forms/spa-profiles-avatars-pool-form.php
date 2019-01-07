<?php
/*
Simple:Press
Admin Profile Avatars Form
$LastChangedDate: 2016-10-22 15:46:40 -0500 (Sat, 22 Oct 2016) $
$Rev: 14660 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

define('SFUPLOADER', wp_nonce_url(SPAJAXURL.'uploader', 'uploader'));

function spa_profiles_avatars_pool_form() {
	global $spPaths;
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	var button = jQuery('#sf-upload-button'), interval;
    	new AjaxUpload(button,{
    		action: '<?php echo SFUPLOADER; ?>',
    		name: 'uploadfile',
    	    data: {
    		    saveloc : '<?php echo addslashes(SF_STORE_DIR."/".$spPaths['avatar-pool'].'/'); ?>'
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
                    site = "<?php echo SPAJAXURL.'profiles' ?>&amp;_wpnonce=<?php echo wp_create_nonce('profiles'); ?>&amp;targetaction=delavatar&amp;file=" + file;
    				jQuery('<table style="width:100%"></table>').appendTo('#sf-avatar-pool').html('<tr><td style="width:60%;text-align:center"><img class="sfavatarpool" src="<?php echo SFAVATARPOOLURL; ?>/' + file + '" alt="" /></td><td class="sflabel" style="text-align:center;width:30%">' + file + '</td><td class="sflabel" style="text-align:center;width:9%"><img src="<?php echo SFCOMMONIMAGES; ?>' + 'delete.png' + '" title="<?php echo esc_js(spa_text('Delete Avatar')); ?>" alt="" class="spDeleteRowReload" data-url="' + site + '" data-reload="sfreloadpool" /></td></tr>');
    				jQuery('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(spa_text('Avatar Uploaded!')); ?></p>');
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
</script>

<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'profiles-loader&amp;saveform=avatars', 'profiles-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfavatarsform" name="sfavatars">
	<?php echo sp_create_nonce('forum-adminform_avatars'); ?>
<?php
	spa_paint_options_init();

    #== PROFILE OPTIONS Tab ============================================================

	spa_paint_open_tab(spa_text('Profiles').' - '.spa_text('Avatar Pool'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Pool Upload'), true, 'avatar-pool-upload');
				$loc = SF_STORE_DIR.'/'.$spPaths['avatar-pool'].'/';
				spa_paint_file(spa_text('Select avatar to upload'), 'newavatar', false, true, $loc);
				echo '<table><tr>';
				echo '<td class="sflabel"><small>';
				spa_etext('Please be advised that Admin uploaded avatars for the avatar pool are NOT subject to the user uploaded avatar size limits.  So use caution when picking avatars for your avatar pool');
				echo '</small></td>';
				echo '</tr></table>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Pool'), true, 'avatar-pool');
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
?>