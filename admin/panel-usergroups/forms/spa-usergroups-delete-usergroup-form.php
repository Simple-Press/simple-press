<?php
/*
Simple:Press
Admin User Groups Delete User Group Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

# function to display the delete user group form.  It is hidden until the delete user group link is clicked
function spa_usergroups_delete_usergroup_form($usergroup_id) {
?>
<script>
   	spj.loadAjaxForm('sfusergroupdel<?php echo esc_attr($usergroup_id); ?>', 'sfreloadub');
</script>
<?php
	$usergroup = spa_get_usergroups_row($usergroup_id);

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=delusergroup', 'usergroups-loader');
?>
	<form action="<?php echo esc_url( $ajaxURL ); ?>" method="post" id="sfusergroupdel<?php echo esc_attr( $usergroup->usergroup_id ); ?>" name="sfusergroupdel<?php echo esc_attr( $usergroup->usergroup_id ); ?>">
<?php
		sp_echo_create_nonce('forum-adminform_usergroupdelete');
		spa_paint_open_tab( esc_html( SP()->primitives->admin_text('User Groups') ) . ' - ' . esc_html( SP()->primitives->admin_text('Manage User Groups') ), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset( SP()->primitives->admin_text('Delete User Group'), 'true', 'delete-user-group');
?>
					<input type="hidden" name="usergroup_id" value="<?php echo esc_attr( $usergroup->usergroup_id ); ?>" />
<?php
					echo '<div class="sf-alert-block sf-warning"><p>';
					esc_html(SP()->primitives->admin_etext("Warning! You are about to delete a User Group!") );
					echo '</p>';
					echo '<p>';
					esc_html(SP()->primitives->admin_etext("This will remove the usergroup and also remove user memberships contained in this usergroup.") );
					echo '</p>';
					echo '<p>';
					sprintf(esc_html(SP()->primitives->admin_text('Please note that this action %s can NOT be reversed %s') ), '<strong>', '</strong>' );
					echo '</p>';
					echo '<p>';
					esc_html(SP()->primitives->admin_etext('Click on the Delete User Group button below to proceed') );
					echo '</p></div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_delete_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="sfusergroupdel<?php echo esc_attr( $usergroup->usergroup_id ); ?>" name="sfusergroupdel<?php echo esc_attr( $usergroup->usergroup_id ); ?>" value="<?php esc_attr(SP()->primitives->admin_etext('Delete User Group') ); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#usergroup-<?php echo esc_attr( $usergroup->usergroup_id ); ?>" id="sfusergroupdel<?php echo esc_attr( $usergroup->usergroup_id ); ?>" name="delusergroupcancel<?php echo esc_attr( $usergroup->usergroup_id ); ?>" value="<?php esc_attr(SP()->primitives->admin_etext('Cancel') ); ?>" />
		</div>
		</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
