<?php
/*
Simple:Press
Admin Forums Delete Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the delete forum permission set form.  It is hidden until the delete permission set link is clicked
function spa_forums_delete_permission_form($perm_id) {
?>
<script>
   	spj.loadAjaxForm('sfpermissiondel<?php echo $perm_id; ?>', 'sfreloadfb');
</script>
<?php
	$perm = SP()->DB->table(SPPERMISSIONS, "permission_id=$perm_id", 'row');

	echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=delperm', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfpermissiondel<?php echo $perm->permission_id; ?>" name="sfpermissiondel<?php echo $perm->permission_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_permissiondelete');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Delete Permission Set'), 'true', 'delete-permission-set');
?>
					<input type="hidden" name="permission_id" value="<?php echo $perm->permission_id; ?>" />
<?php
					echo '<p>';
					SP()->primitives->admin_etext('Warning! You are about to delete a permission set');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('This will remove ALL access to this forum for this usergroup');
					echo '</p>';
					echo '<p>';
					echo sprintf(SP()->primitives->admin_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('Click on the delete permission set button below to proceed');
					echo '</p>';

				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_delete_perm_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="delperm<?php echo $perm->permission_id; ?>" name="delperm<?php echo $perm->permission_id; ?>" value="<?php SP()->primitives->admin_etext('Delete Permission Set'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#curperm-<?php echo $perm->permission_id; ?>" id="sfpermissiondel<?php echo $perm->permission_id; ?>" name="delpermcancel<?php echo $perm->permission_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
        </div>
	<?php spa_paint_close_tab(); ?>
    </form>
	<div class="sfform-panel-spacer"></div>
<?php
}
