<?php
/*
Simple:Press
Admin Toolbox Toolbox Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_toolbox_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sftoolboxform', '');
    });
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

	spa_paint_open_tab(spa_text('Toolbox').' - '.spa_text('Toolbox'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Current Version/Build'), false);
            	$version = spa_text('Version:').'&nbsp;<strong>'.sp_get_option('sfversion').'</strong>';
            	$build = spa_text('Build:  ').'&nbsp;<strong>'.sp_get_option('sfbuild').'</strong>';
            	echo $version.'&nbsp;&nbsp;&nbsp;&nbsp;'.$build;
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_toolbox_left_panel');
		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Modify Build Number'), true, 'modify-build-number');
				echo '<div class="sfoptionerror">'.spa_text('WARNING: This value should not be changed unless requested by the Simple:Press team in the support forum as it may cause the install/upgrade script to be re-run.').'</div>';
				spa_paint_input(spa_text('Build number'), "sfbuild", sp_get_option('sfbuild'), false, false);
				spa_paint_checkbox(spa_text('Force upgrade to build number'), "sfforceupgrade", $sfoptions['sfforceupgrade']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_toolbox_right_panel');
		spa_paint_close_container();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Toolbox'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}

?>