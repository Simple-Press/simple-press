<?php
/*
Simple:Press
Admin Permissions Delete Permission Form
$LastChangedDate: 2016-10-22 14:02:42 -0500 (Sat, 22 Oct 2016) $
$Rev: 14657 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the delete permission set form.  It is hidden until the delete permission set link is clicked
function spa_permissions_delete_permission_form($role_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfroledel<?php echo $role_id; ?>', 'sfreloadpb');
    });
</script>
<?php
	$role = spa_get_role_row($role_id);

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'permissions-loader&amp;saveform=delperm', 'permissions-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfroledel<?php echo $role->role_id; ?>" name="sfroledel<?php echo $role->role_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_roledelete');
		spa_paint_open_tab(spa_text('Permissions')." - ".spa_text('Manage Permissions'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Delete Permission'), 'true', 'delete-master-permission-set');
?>
					<input type="hidden" name="role_id" value="<?php echo $role->role_id; ?>" />
<?php
					echo '<p>';
					spa_etext("Warning! You are about to delete a Permission!");
					echo '</p>';
					echo '<p>';
					spa_etext("This will remove the Permission and also remove it from ALL Forums that used this Permission.");
					echo '</p>';
					echo '<p>';
					echo sprintf(spa_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the Delete Permission button below to proceed.');
					echo '</p>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_perm_delete_perm_panel');
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfpermedit<?php echo $role->role_id; ?>" name="sfpermdel<?php echo $role->role_id; ?>" value="<?php spa_etext('Delete Permission'); ?>" />
		<input type="button" class="button-primary spCancelForm" data-target="#perm-<?php echo $role->role_id; ?>" id="sfpermdel<?php echo $role->role_id; ?>" name="delpermcancel<?php echo $role->role_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
		</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>