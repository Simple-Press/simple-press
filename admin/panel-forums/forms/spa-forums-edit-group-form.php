<?php
/*
Simple:Press
Admin Forums Edit Group Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit group information form.  It is hidden until the edit group link is clicked
function spa_forums_edit_group_form($group_id) {
?>
<script>
   	spj.loadAjaxForm('sfgroupedit<?php echo $group_id; ?>', 'sfreloadfb');
</script>
<?php
	$group = $group = SP()->DB->table(SPGROUPS, "group_id=$group_id", 'row');

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=editgroup', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfgroupedit<?php echo $group->group_id; ?>" name="sfgroupedit<?php echo $group->group_id; ?>" class="sfinline-form">
<?php
		echo sp_create_nonce('forum-adminform_groupedit');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), false);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Edit Group'), 'true', 'edit-forum-group');
?>
					<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" />
					<input type="hidden" name="cgroup_name" value="<?php echo SP()->displayFilters->title($group->group_name); ?>" />
					<input type="hidden" name="cgroup_desc" value="<?php echo SP()->editFilters->text($group->group_desc); ?>" />
					<input type="hidden" name="cgroup_seq" value="<?php echo $group->group_seq; ?>" />
					<input type="hidden" name="cgroup_icon" value="<?php echo esc_attr($group->group_icon); ?>" />
					<input type="hidden" name="cgroup_rss" value="<?php echo $group->group_rss; ?>" />
					<input type="hidden" name="cgroup_message" value="<?php echo SP()->editFilters->text($group->group_message); ?>" />
<?php
					spa_paint_input(SP()->primitives->admin_text('Group Name'), 'group_name', SP()->displayFilters->title($group->group_name), false, true);
					spa_paint_input(SP()->primitives->admin_text('Description'), 'group_desc', SP()->editFilters->text($group->group_desc), false, true);

					
					$custom_icons =  spa_get_custom_icons();
					
					
					spa_select_iconset_icon_picker( 'group_icon', SP()->primitives->admin_text( 'Select Custom Icon' ), array( 'Custom Icons' => $custom_icons ), $group->group_icon );
					

					spa_paint_input(SP()->primitives->admin_text('Replacement external RSS URL').'<br />'.SP()->primitives->admin_text('Default').': <strong>'.SP()->spPermalinks->get_query_url(SP()->spPermalinks->build_url('', '', 0, 0, 0, 1)).'group='.$group->group_id.'</strong>', 'group_rss', SP()->displayFilters->url($group->group_rss), false, true);

					spa_paint_wide_textarea('Special group message to be displayed above forums', 'group_message', SP()->editFilters->text($group->group_message));

					do_action('sph_forums_edit_group_panel');

				spa_paint_close_fieldset();


			spa_paint_close_panel();

		spa_paint_tab_right_cell();
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Default User Group Permissions'), false);
                                        echo '<div class="sf-alert-block sf-info">';
					echo '<strong>'.SP()->primitives->admin_text('Set default usergroup permission sets for this group').'</strong><br />';
					echo SP()->primitives->admin_text('Note - This will not will add or modify any current permissions. It is only a default setting for future forums created in this group.  Existing default usergroup settings will be shown in the drop down menus');
                                        echo '</div>';
					# Permissions
					$usergroups = spa_get_usergroups_all();
					$roles = sp_get_all_roles();

					foreach ($usergroups as $usergroup) {
						echo '<input type="hidden" name="usergroup_id[]" value="'.$usergroup->usergroup_id.'" />';
						spa_paint_select_start(SP()->displayFilters->title($usergroup->usergroup_name), 'role[]', '');

						$defrole = spa_get_defpermissions_role($group->group_id, $usergroup->usergroup_id);
						if ($defrole == -1 || $defrole == '') {
							echo '<option value="-1">'.SP()->primitives->admin_text('Select permission set').'</option>';
						}
						foreach ($roles as $role) {
							$selected = '';
							if ($defrole == $role->role_id) {
								$selected = 'selected="selected" ';
							}
							echo '<option '.$selected.'value="'.$role->role_id.'">'.SP()->displayFilters->title($role->role_name).'</option>'."\n";
						}
						spa_paint_select_end();
					}

				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="groupedit<?php echo $group->group_id; ?>" name="groupedit<?php echo $group->group_id; ?>" value="<?php SP()->primitives->admin_etext('Update Group'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#group-<?php echo $group->group_id; ?>" id="sfgroupedit<?php echo $group->group_id; ?>" name="groupeditcancel<?php echo $group->group_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
