<?php
/*
Simple:Press
Admin Forums Edit Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit forum permission set form.  It is hidden until the edit permission set link is clicked
function spa_forums_edit_permission_form($perm_id) {
?>
<script>
   	spj.loadAjaxForm('sfpermissionnedit<?php echo $perm_id; ?>', 'sfreloadfb');
</script>
<?php
	$perm = SP()->DB->table(SPPERMISSIONS, "permission_id=$perm_id", 'row');

	echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=editperm', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfpermissionnedit<?php echo $perm->permission_id; ?>" name="sfpermissionedit<?php echo $perm->permission_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_permissionedit');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Edit Permission Set'), 'true', 'edit-permission-set');
?>
					<input type="hidden" name="permission_id" value="<?php echo $perm->permission_id; ?>" />
					<input type="hidden" name="ugroup_perm" value="<?php echo $perm->permission_role; ?>" />
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_display_permission_select($perm->permission_role); ?></td>
						</tr>
					</table>
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_edit_perm_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="editperm<?php echo $perm->permission_id; ?>" name="editperm<?php echo $perm->permission_id; ?>" value="<?php SP()->primitives->admin_etext('Update Permission Set'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#curperm-<?php echo $perm->permission_id; ?>" id="sfpermissionnedit<?php echo $perm->permission_id; ?>" name="editpermcancel<?php echo $perm->permission_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
