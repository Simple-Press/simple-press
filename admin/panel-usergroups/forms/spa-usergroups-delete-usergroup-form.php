<?php
/*
Simple:Press
Admin User Groups Delete User Group Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the delete user group form.  It is hidden until the delete user group link is clicked
function spa_usergroups_delete_usergroup_form($usergroup_id) {
?>
<script>
   	spj.loadAjaxForm('sfusergroupdel<?php echo $usergroup_id; ?>', 'sfreloadub');
</script>
<?php
	$usergroup = spa_get_usergroups_row($usergroup_id);

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=delusergroup', 'usergroups-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfusergroupdel<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupdel<?php echo $usergroup->usergroup_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_usergroupdelete');
		spa_paint_open_tab(SP()->primitives->admin_text('User Groups').' - '.SP()->primitives->admin_text('Manage User Groups'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Delete User Group'), 'true', 'delete-user-group');
?>
					<input type="hidden" name="usergroup_id" value="<?php echo $usergroup->usergroup_id; ?>" />
<?php
					echo '<div class="sf-alert-block sf-warning"><p>';
					SP()->primitives->admin_etext("Warning! You are about to delete a User Group!");
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext("This will remove the usergroup and also remove user memberships contained in this usergroup.");
					echo '</p>';
					echo '<p>';
					echo sprintf(SP()->primitives->admin_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('Click on the Delete User Group button below to proceed');
					echo '</p></div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_delete_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="sfusergroupdel<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupdel<?php echo $usergroup->usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Delete User Group'); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#usergroup-<?php echo $usergroup->usergroup_id; ?>" id="sfusergroupdel<?php echo $usergroup->usergroup_id; ?>" name="delusergroupcancel<?php echo $usergroup->usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
		</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
