<?php
/*
Simple:Press
Admin User Groups Edit User Group Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

# function to display the edit user group form.  It is hidden until the edit user group link is clicked
function spa_usergroups_edit_usergroup_form($usergroup_id) {
?>
<script>
   	spj.loadAjaxForm('sfusergroupedit<?php echo esc_attr($usergroup_id); ?>', 'sfreloadub');
</script>
<?php
	$usergroup = spa_get_usergroups_row($usergroup_id);

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=editusergroup', 'usergroups-loader');
?>
	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfusergroupedit<?php echo esc_attr($usergroup->usergroup_id); ?>" name="sfusergroupedit<?php echo esc_attr($usergroup->usergroup_id); ?>">
<?php
		echo sp_create_nonce('forum-adminform_usergroupedit');
		spa_paint_open_tab( esc_html( SP()->primitives->admin_text('User Groups') ) . ' - ' . esc_html( SP()->primitives->admin_text('Manage User Groups') ), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset( SP()->primitives->admin_text('Edit User Group'), 'true', 'edit-user-group');
?>
					<input type="hidden" name="usergroup_id" value="<?php echo esc_attr($usergroup->usergroup_id); ?>" />
					<input type="hidden" name="ugroup_name" value="<?php echo esc_attr(SP()->displayFilters->title($usergroup->usergroup_name)); ?>" />
					<input type="hidden" name="ugroup_desc" value="<?php echo esc_attr(SP()->displayFilters->title($usergroup->usergroup_desc)); ?>" />
					<input type="hidden" name="ugroup_join" value="<?php echo esc_attr($usergroup->usergroup_join); ?>" />
					<input type="hidden" name="ugroup_ismod" value="<?php echo esc_attr($usergroup->usergroup_is_moderator); ?>" />
<?php
					spa_paint_input( SP()->primitives->admin_text('User Group Name'), 'usergroup_name', esc_attr(SP()->displayFilters->title($usergroup->usergroup_name)), false, true);
					spa_paint_input( SP()->primitives->admin_text('User Group Description'), 'usergroup_desc', esc_attr(SP()->displayFilters->title($usergroup->usergroup_desc)), false, true);
					spa_paint_select_start( SP()->primitives->admin_text('Select Badge'), 'usergroup_badge', 'usergroup_badge');
					spa_select_icon_dropdown('usergroup_badge', SP()->primitives->admin_text('Select Badge'), SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/', esc_attr($usergroup->usergroup_badge), false);
					spa_paint_select_end('<small>('. SP()->primitives->admin_text('Upload badges on the Components - Forum Ranks admin panel') .')</small>');
					spa_paint_checkbox( SP()->primitives->admin_text('Allow members to join usergroup'), 'usergroup_join', esc_attr($usergroup->usergroup_join), false, false, false, '<small>'. SP()->primitives->admin_text('(Indicates that members are allowed to choose to join this usergroup on their profile page)') .'</small>');
					spa_paint_checkbox( SP()->primitives->admin_text('Hide members from user statistics'), 'hide_stats', esc_attr($usergroup->hide_stats), false, false, false, '<small>'. SP()->primitives->admin_text('(This applies to the basic statistics optionally displayed on forum pages)') .'</small>');
					spa_paint_checkbox( SP()->primitives->admin_text('Is moderator'), 'usergroup_is_moderator', esc_attr($usergroup->usergroup_is_moderator), false, false, false, '<small>'. SP()->primitives->admin_text('(Indicates that members of this usergroup are considered Moderators)') .'</small>');
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_edit_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="sfusergroupedit<?php echo esc_attr($usergroup->usergroup_id); ?>" name="sfusergroupedit<?php echo esc_attr($usergroup->usergroup_id); ?>" value="<?php esc_attr(SP()->primitives->admin_etext('Update User Group')); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#usergroup-<?php echo esc_attr($usergroup->usergroup_id); ?>" id="sfusergroupedit<?php echo esc_attr($usergroup->usergroup_id); ?>" name="editusergroupcancel<?php echo esc_attr($usergroup->usergroup_id); ?>" value="<?php esc_attr(SP()->primitives->admin_etext('Cancel')); ?>" />
		</div>
	</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
