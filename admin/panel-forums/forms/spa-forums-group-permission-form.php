<?php
/*
Simple:Press
Admin Forums Add Group Permission Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add group permission set form.  It is hidden until the add group permission set link is clicked
function spa_forums_add_group_permission_form($group_id) {
?>
<script>
   	spj.loadAjaxForm('sfgrouppermnew<?php echo $group_id; ?>', 'sfreloadfb');
</script>
<?php
	$group = $group = SP()->DB->table(SPGROUPS, "group_id=$group_id", 'row');

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=grouppermission', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfgrouppermnew<?php echo $group->group_id; ?>" name="sfgrouppermnew<?php echo $group->group_id; ?>" class="sfinline-form">
<?php
		echo sp_create_nonce('forum-adminform_grouppermissionnew');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Add a User Group Permission Set to an Entire Group'), 'true', 'add-a-user-group-permission-set-to-an-entire-group');
?>
        <div class="sf-alert-block sf-info"><?php SP()->primitives->admin_etext('Caution:  Any current permission set for the selected usergroup for any forum in this group will be overwritten'); ?></div>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_display_usergroup_select(); ?></td>
						</tr><tr>
							<td class="sflabel"><?php spa_display_permission_select(); ?></td>
						</tr><tr>
							<td class="sflabel">
    							<input type="checkbox" id="sfadddef" name="adddef" />
    							<label for="sfadddef"><?php SP()->primitives->admin_etext('Add to group default permissions'); ?></label>
                            </td>
						</tr>
					</table>

					<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" />
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_forums_group_perm_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="groupperm<?php echo $group->group_id; ?>" name="groupperm<?php echo $group->group_id; ?>" value="<?php SP()->primitives->admin_etext('Add Group Permission'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#group-<?php echo $group->group_id; ?>" id="grouppermcancel<?php echo $group->group_id; ?>" name="grouppermcancel<?php echo $group->group_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
