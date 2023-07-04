<?php
/*
Simple:Press
Admin Permissions Reset Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the remove all permission set form.  It is hidden until the remove all permission set link is clicked
function spa_permissions_reset_perms_form() {
?>
<script>
   	spj.loadAjaxForm('sfresetpermissions', 'sfreloadpb');
</script>
<?php
	spa_paint_options_init();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'permissions-loader&amp;saveform=resetperms', 'permissions-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfresetpermissions" name="sfresetpermissions">
<?php
		echo sp_create_nonce('forum-adminform_resetpermissions');
		spa_paint_open_tab(/*SP()->primitives->admin_text('Forums').' - '.*/SP()->primitives->admin_text('Reset All Permission'), true);
			spa_paint_open_panel();

				spa_paint_open_fieldset(SP()->primitives->admin_text('Reset all permissions back to initial state.'), 'true', 'reset-permissions');
					echo '<div class="sf-alert-block sf-warning">';
					echo '<p>';
					SP()->primitives->admin_etext('Warning! You are about to reset your permissions back to the install state.');
                    echo '<br><br>';
					SP()->primitives->admin_etext('This will delete all roles and permissions for your forums. You will have to give your users access to your forums again.');
                    echo '<br><br>';
					echo sprintf(SP()->primitives->admin_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '</div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Reset Permissions'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
