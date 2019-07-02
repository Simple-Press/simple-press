<?php
/*
Simple:Press
Admin User Groups Edit User Group Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit user group form.  It is hidden until the edit user group link is clicked
function spa_usergroups_edit_usergroup_form($usergroup_id) {
?>
<script>
   	spj.loadAjaxForm('sfusergroupedit<?php echo $usergroup_id; ?>', 'sfreloadub');
</script>
<?php
	$usergroup = spa_get_usergroups_row($usergroup_id);

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=editusergroup', 'usergroups-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_usergroupedit');
		spa_paint_open_tab(SP()->primitives->admin_text('User Groups').' - '.SP()->primitives->admin_text('Manage User Groups'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Edit User Group'), 'true', 'edit-user-group');
?>
					<input type="hidden" name="usergroup_id" value="<?php echo $usergroup->usergroup_id; ?>" />
					<input type="hidden" name="ugroup_name" value="<?php echo SP()->displayFilters->title($usergroup->usergroup_name); ?>" />
					<input type="hidden" name="ugroup_desc" value="<?php echo SP()->displayFilters->title($usergroup->usergroup_desc); ?>" />
					<input type="hidden" name="ugroup_join" value="<?php echo $usergroup->usergroup_join; ?>" />
					<input type="hidden" name="ugroup_ismod" value="<?php echo $usergroup->usergroup_is_moderator; ?>" />
<?php
					spa_paint_input(SP()->primitives->admin_text('User Group Name'), 'usergroup_name', SP()->displayFilters->title($usergroup->usergroup_name), false, true);
					spa_paint_input(SP()->primitives->admin_text('User Group Description'), 'usergroup_desc', SP()->displayFilters->title($usergroup->usergroup_desc), false, true);
					spa_paint_select_start(SP()->primitives->admin_text('Select Badge'), 'usergroup_badge', 'usergroup_badge');
					spa_select_icon_dropdown('usergroup_badge', SP()->primitives->admin_text('Select Badge'), SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/', $usergroup->usergroup_badge, false);
					spa_paint_select_end('<small>('.SP()->primitives->admin_text('Upload badges on the Components - Forum Ranks admin panel').')</small>');
					spa_paint_checkbox(SP()->primitives->admin_text('Allow members to join usergroup'), 'usergroup_join', $usergroup->usergroup_join, false, false, false, '<small>'.SP()->primitives->admin_text('(Indicates that members are allowed to choose to join this usergroup on their profile page)').'</small>');
					spa_paint_checkbox(SP()->primitives->admin_text('Hide members from user statistics'), 'hide_stats', $usergroup->hide_stats, false, false, false, '<small>'.SP()->primitives->admin_text('(This applies to the basic statistics optionally displayed on forum pages)').'</small>');
					spa_paint_checkbox(SP()->primitives->admin_text('Is moderator'), 'usergroup_is_moderator', $usergroup->usergroup_is_moderator, false, false, false, '<small>'.SP()->primitives->admin_text('(Indicates that members of this usergroup are considered Moderators)').'</small>');

				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_edit_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Update User Group'); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#usergroup-<?php echo $usergroup->usergroup_id; ?>" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="editusergroupcancel<?php echo $usergroup->usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
