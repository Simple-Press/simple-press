<?php
/*
Simple:Press
Admin plugins uploader
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_plugins_upload_form() {
    # Make sure only super admin can upload on multisite
	if (is_multisite() && !is_super_admin()) die();

	spa_paint_options_init();
	spa_paint_open_tab(SP()->primitives->admin_text('Upload Plugin').' - '.SP()->primitives->admin_text('Upload a Simple:Press Plugin'), true);
	spa_paint_open_panel();
        spa_paint_open_fieldset(SP()->primitives->admin_text('Upload Plugin'), true, 'upload-plugin');
            echo '<p>'.SP()->primitives->admin_text('Upload a Simple:Press plugin in .zip format').'</p>';
            echo '<p>'.SP()->primitives->admin_text('If you have a plugin in a .zip format, you may upload it here').'</p>';
?>
        	<form method="post" enctype="multipart/form-data" action="<?php echo self_admin_url('update.php?action=upload-sp-plugin'); ?>" id="sfpluginuploadform" name="sfpluginuploadform">
                <?php echo sp_create_nonce('forum-plugin_upload'); ?>
        		<p><input type="file" id="pluginzip" name="pluginzip" /></p>
        		<p><input type="button" class="sf-button-primary spPluginUpload" id="saveupload" name="saveupload" value="<?php SP()->primitives->admin_etext('Upload Now'); ?>" data-target="#saveupload" /></p>
        	</form>
<?php
		spa_paint_close_fieldset();

        do_action('sph_plugins_upload_panel');
	spa_paint_close_panel();
	//spa_paint_close_container();
	spa_paint_close_tab();
}
