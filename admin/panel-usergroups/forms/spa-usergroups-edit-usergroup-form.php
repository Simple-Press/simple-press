<?php
/*
Simple:Press
Admin User Groups Edit User Group Form
$LastChangedDate: 2016-10-23 14:40:24 -0500 (Sun, 23 Oct 2016) $
$Rev: 14666 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit user group form.  It is hidden until the edit user group link is clicked
function spa_usergroups_edit_usergroup_form($usergroup_id) {
    global $spPaths;
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfusergroupedit<?php echo $usergroup_id; ?>', 'sfreloadub');
    });
</script>
<?php
	$usergroup = spa_get_usergroups_row($usergroup_id);

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=editusergroup', 'usergroups-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_usergroupedit');
		spa_paint_open_tab(spa_text('User Groups').' - '.spa_text('Manage User Groups'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Edit User Group'), 'true', 'edit-user-group');
?>
					<input type="hidden" name="usergroup_id" value="<?php echo $usergroup->usergroup_id; ?>" />
					<input type="hidden" name="ugroup_name" value="<?php echo sp_filter_title_display($usergroup->usergroup_name); ?>" />
					<input type="hidden" name="ugroup_desc" value="<?php echo sp_filter_title_display($usergroup->usergroup_desc); ?>" />
					<input type="hidden" name="ugroup_join" value="<?php echo $usergroup->usergroup_join; ?>" />
					<input type="hidden" name="ugroup_ismod" value="<?php echo $usergroup->usergroup_is_moderator; ?>" />
<?php
					spa_paint_input(spa_text('User Group Name'), 'usergroup_name', sp_filter_title_display($usergroup->usergroup_name), false, true);
					spa_paint_input(spa_text('User Group Description'), 'usergroup_desc', sp_filter_title_display($usergroup->usergroup_desc), false, true);
					spa_paint_select_start(spa_text('Select Badge'), 'usergroup_badge', 'usergroup_badge');
					spa_select_icon_dropdown('usergroup_badge', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', $usergroup->usergroup_badge, false);
					spa_paint_select_end('<small>('.spa_text('Upload badges on the Components - Forum Ranks admin panel').')</small>');
					spa_paint_checkbox(spa_text('Allow members to join usergroup'), 'usergroup_join', $usergroup->usergroup_join, false, false, false, '<small>'.spa_text('(Indicates that members are allowed to choose to join this usergroup on their profile page)').'</small>');
					spa_paint_checkbox(spa_text('Hide members from user statistics'), 'hide_stats', $usergroup->hide_stats, false, false, false, '<small>'.spa_text('(This applies to the basic statistics optionally displayed on forum pages)').'</small>');
					spa_paint_checkbox(spa_text('Is moderator'), 'usergroup_is_moderator', $usergroup->usergroup_is_moderator, false, false, false, '<small>'.spa_text('(Indicates that members of this usergroup are considered Moderators)').'</small>');

				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_usergroup_edit_panel');
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" value="<?php spa_etext('Update User Group'); ?>" />
		<input type="button" class="button-primary spCancelForm" data-target="#usergroup-<?php echo $usergroup->usergroup_id; ?>" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="editusergroupcancel<?php echo $usergroup->usergroup_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>
	<?php spa_paint_close_tab(); ?>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>