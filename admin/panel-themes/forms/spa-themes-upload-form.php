<?php
/*
Simple:Press
Admin themes uploader
$LastChangedDate: 2017-08-05 17:36:04 -0500 (Sat, 05 Aug 2017) $
$Rev: 15488 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_upload_form() {
    # Make sure only super admin can upload on multisite
	if (is_multisite() && !is_super_admin()) die();

	spa_paint_options_init();
	spa_paint_open_tab(/*SP()->primitives->admin_text('Upload Theme').' - '.*/SP()->primitives->admin_text('Upload a Simple:Press Theme'), true);
	spa_paint_open_panel();
        spa_paint_open_fieldset(SP()->primitives->admin_text('Upload Theme'), true, 'upload-theme');
?>
            <div class="sf-alert-block sf-info">
                <p><?php echo SP()->primitives->admin_text('Upload a Simple:Press theme in .zip format') ?></p>
                <p><?php echo SP()->primitives->admin_text('If you have a theme in a .zip format, you may upload it here') ?></p>
            </div>
            <form method="post" enctype="multipart/form-data" action="<?php echo self_admin_url('update.php?action=upload-sp-theme'); ?>" id="sfthemeuploadform" name="sfthemeuploadform">
                <?php echo sp_create_nonce('forum-theme_upload'); ?>
				<div class="clear"></div>
				<div class="sf-upload-file-name"><label class="sp-label"></label></div>
				<div class="clear"></div>			
                <label class="sf-button-primary sf-upload-button">
                    <input type="file" id="themezip" name="themezip" class="sf-hidden-important" />
                    <span class="sf-icon sf-icon-button sf-white sf-upload"></span>
                    <?php echo SP()->primitives->admin_text('Select file') ?>
                </label>
                <input type="button" class="sf-button-primary spThemeUpload" id="saveupload" name="sa sf-uppercaseveupload" value="<?php SP()->primitives->admin_etext('Upload Now'); ?>" onclick="sfloader()" data-target="#saveupload" />
                
                <div id="sf-loader-gif" style="display: none;">
                    <img src="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>" alt="loading" />
                </div>
            </form>
            <script>
                function sfloader(){
                    document.getElementById("sf-loader-gif").style.display = "block";
                }
            </script>
<?php
		spa_paint_close_fieldset();

        do_action('sph_themes_upload_panel');
	spa_paint_close_panel();
	spa_paint_close_container();
	spa_paint_close_tab();
}
