<?php
/*
Simple:Press
Admin Forums Edit Group Form
$LastChangedDate: 2016-10-21 20:37:22 -0500 (Fri, 21 Oct 2016) $
$Rev: 14651 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit group information form.  It is hidden until the edit group link is clicked
function spa_forums_edit_group_form($group_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfgroupedit<?php echo $group_id; ?>', 'sfreloadfb');
    });
</script>
<?php
	global $spPaths;

	$group = $group = spdb_table(SFGROUPS, "group_id=$group_id", 'row');

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=editgroup', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfgroupedit<?php echo $group->group_id; ?>" name="sfgroupedit<?php echo $group->group_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_groupedit');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), false);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Edit Group'), 'true', 'edit-forum-group');
?>
					<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" />
					<input type="hidden" name="cgroup_name" value="<?php echo sp_filter_title_display($group->group_name); ?>" />
					<input type="hidden" name="cgroup_desc" value="<?php echo sp_filter_text_edit($group->group_desc); ?>" />
					<input type="hidden" name="cgroup_seq" value="<?php echo $group->group_seq; ?>" />
					<input type="hidden" name="cgroup_icon" value="<?php echo esc_attr($group->group_icon); ?>" />
					<input type="hidden" name="cgroup_rss" value="<?php echo $group->group_rss; ?>" />
					<input type="hidden" name="cgroup_message" value="<?php echo sp_filter_text_edit($group->group_message); ?>" />
<?php
					spa_paint_input(spa_text('Group Name'), 'group_name', sp_filter_title_display($group->group_name), false, true);
					spa_paint_input(spa_text('Description'), 'group_desc', sp_filter_text_edit($group->group_desc), false, true);

					spa_paint_select_start(spa_text('Select Custom Icon'), 'group_icon', '');
					spa_select_icon_dropdown('group_icon', spa_text('Select Custom Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $group->group_icon, false);
					spa_paint_select_end();

					spa_paint_input(spa_text('Replacement external RSS URL').'<br />'.spa_text('Default').': <strong>'.sp_get_sfqurl(sp_build_url('', '', 0, 0, 0, 1)).'group='.$group->group_id.'</strong>', 'group_rss', sp_filter_url_display($group->group_rss), false, true);

					spa_paint_wide_textarea('Special group message to be displayed above forums', 'group_message', sp_filter_text_edit($group->group_message));

					do_action('sph_forums_edit_group_panel');

				spa_paint_close_fieldset();

			echo '<div class="sfoptionerror spaceabove">';
			echo sprintf(sp_text('To re-order your Groups, Forums and SubForums use the %s Order Groups and Forums %s option from the Forums Menu'), '<b>', '</b>');
			echo '</div>';

			spa_paint_close_panel();

		spa_paint_tab_right_cell();
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Default User Group Permissions'), false);

					echo '<strong>'.spa_text('Set default usergroup permission sets for this group').'</strong><br />';
					echo spa_text('Note - This will not will add or modify any current permissions. It is only a default setting for future forums created in this group.  Existing default usergroup settings will be shown in the drop down menus');

					# Permissions
					$usergroups = spa_get_usergroups_all();
					$roles = sp_get_all_roles();

					foreach ($usergroups as $usergroup) {
						echo '<input type="hidden" name="usergroup_id[]" value="'.$usergroup->usergroup_id.'" />';
						spa_paint_select_start(sp_filter_title_display($usergroup->usergroup_name), 'role[]', '');

						$defrole = spa_get_defpermissions_role($group->group_id, $usergroup->usergroup_id);
						if ($defrole == -1 || $defrole == '') {
							echo '<option value="-1">'.spa_text('Select permission set').'</option>';
						}
						foreach ($roles as $role) {
							$selected = '';
							if ($defrole == $role->role_id) {
								$selected = 'selected="selected" ';
							}
							echo '<option '.$selected.'value="'.$role->role_id.'">'.sp_filter_title_display($role->role_name).'</option>'."\n";
						}
						spa_paint_select_end();
					}

				spa_paint_close_fieldset();
			spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
    		<input type="submit" class="button-primary" id="groupedit<?php echo $group->group_id; ?>" name="groupedit<?php echo $group->group_id; ?>" value="<?php spa_etext('Update Group'); ?>" />
    		<input type="button" class="button-primary spCancelForm" data-target="#group-<?php echo $group->group_id; ?>" id="sfgroupedit<?php echo $group->group_id; ?>" name="groupeditcancel<?php echo $group->group_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>