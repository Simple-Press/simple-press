<?php
/*
Simple:Press
Admin Permissions Reset Permission Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the remove all permission set form.  It is hidden until the remove all permission set link is clicked
function spa_permissions_reset_perms_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfresetpermissions', 'sfreloadpb');
    });
</script>
<?php
	spa_paint_options_init();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'permissions-loader&amp;saveform=resetperms', 'permissions-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfresetpermissions" name="sfresetpermissions">
<?php
		echo sp_create_nonce('forum-adminform_resetpermissions');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Reset All Permission'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Reset all permissions back to initial state.'), 'true', 'reset-permissions');
					echo '<p>';
					spa_etext('Warning! You are about to reset your permissions back to the install state.');
					echo '</p>';
					echo '<p>';
					spa_etext('This will delete all roles and permissions for your forums. You will have to give your users access to your forums again.');
					echo '</p>';
					echo '<p>';
					echo sprintf(spa_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the reset permissions button below to proceed');
					echo '</p>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Reset Permissions'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>