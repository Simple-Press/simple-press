<?php
/*
Simple:Press
Admin Toolbox Toolbox Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_toolbox_form() {
?>
<script>
   	spj.loadAjaxForm('sftoolboxform', '');
</script>
<?php
	$sfoptions = spa_get_toolbox_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=toolbox', 'toolbox-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sftoolboxform" name="sftoolbox">
	<?php echo sp_create_nonce('forum-adminform_toolbox'); ?>
<?php
	spa_paint_options_init();

    #== TOOLBOX Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('Toolbox'));
		spa_paint_open_panel();#
			spa_paint_open_fieldset(SP()->primitives->admin_text('Current Version/Build'), false);
                echo '<div class="sf-form-row">';
            	$version = SP()->primitives->admin_text('Version:').'&nbsp;<strong>'.SP()->options->get('sfversion').'</strong>';
            	$build = SP()->primitives->admin_text('Build:  ').'&nbsp;<strong>'.SP()->options->get('sfbuild').'</strong>';
            	echo $version.'<br>'.$build;
                echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_toolbox_left_panel');
		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Modify Build Number'), true, 'modify-build-number');
				echo '<div class="sf-alert-block sf-warning">'.SP()->primitives->admin_text('WARNING: This value should not be changed unless requested by the Simple:Press team in the support forum as it may cause the install/upgrade script to be re-run.').'</div>';
				spa_paint_input(SP()->primitives->admin_text('Build number'), "sfbuild", SP()->options->get('sfbuild'), false, false);
				spa_paint_checkbox(SP()->primitives->admin_text('Force upgrade to build number'), "sfforceupgrade", $sfoptions['sfforceupgrade']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_toolbox_right_panel');
		spa_paint_close_container();

?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Toolbox'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
