<?php
/*
Simple:Press
Admin Toolbox Uninstall Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_uninstall_form() {
?>
<script>
   	spj.loadAjaxForm('sfuninstallform', '');
</script>
<?php
	$sfoptions = spa_get_uninstall_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=uninstall', 'toolbox-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfuninstallform" name="sfuninstall">
	<?php echo sp_create_nonce('forum-adminform_uninstall'); ?>
<?php

	spa_paint_options_init();

    #== UNINSTALL Tab ==========================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('Uninstall'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Removing Simple:Press'), true, 'uninstall');
				echo '<div class="sf-alert-block sf-warning">';
				SP()->primitives->admin_etext('Should you, at any time, decide to remove Simple:Press, check the uninstall option below and then deactivate the Simple Press plugin in the standard WP fashion');
				echo '.<br />';
				SP()->primitives->admin_etext('If you have initiated uninstall, but changed your mind prior to Simple Press plugin deactivation, you can uncheck the uninstall option and it will be reversed');
				echo '.<br />';
				echo '<br />';
				SP()->primitives->admin_etext('UNINSTALLING SIMPLE PRESS WILL REMOVE ALL FORUM DATA FROM YOUR DATABASE');
				echo '!<br />';
				echo '<br />';
				SP()->primitives->admin_etext('UNINSTALLING SIMPLE PRESS WILL REMOVE ALL STORAGE LOCATIONS IF YOU ALSO ENABLE THE STORAGE LOCATUON REMOVAL OPTION');
				echo '!<br />';
				echo '<br />';
				SP()->primitives->admin_etext('ONCE YOU ENABLE UNINSTALL AND DEACTIVATE THE SIMPLE PRESS PLUGIN, THIS ACTION CAN NOT BE REVERSED');
				echo '!<br />';
				echo '<br />';
				SP()->primitives->admin_etext('Please note that you will still need to remove the Simple:Press core plugin files manually or use the wp plugin deletion functionalty');
				echo '.<br />';
				echo '</div>';
				spa_paint_checkbox(SP()->primitives->admin_text('Uninstall Simple Press - Requires plugin deactivation after enabling option (this will completely remove Simple:Press database entries)'), 'sfuninstall', $sfoptions['sfuninstall']);
				spa_paint_checkbox(SP()->primitives->admin_text('When uninstalling, completely remove Simple:Press storage locations'), 'removestorage', $sfoptions['removestorage']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_toolbox_uninstall_panel');
		spa_paint_close_container();

?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Uninstall'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
