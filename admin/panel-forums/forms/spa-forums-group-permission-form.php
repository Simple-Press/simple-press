<?php
/*
Simple:Press
Admin Forums Add Group Permission Form
$LastChangedDate: 2016-10-21 20:37:22 -0500 (Fri, 21 Oct 2016) $
$Rev: 14651 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add group permission set form.  It is hidden until the add group permission set link is clicked
function spa_forums_add_group_permission_form($group_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfgrouppermnew<?php echo $group_id; ?>', 'sfreloadfb');
    });
</script>
<?php
	$group = $group = spdb_table(SFGROUPS, "group_id=$group_id", 'row');

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=grouppermission', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfgrouppermnew<?php echo $group->group_id; ?>" name="sfgrouppermnew<?php echo $group->group_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_grouppermissionnew');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Add a User Group Permission Set to an Entire Group'), 'true', 'add-a-user-group-permission-set-to-an-entire-group');
?>
					<?php echo spa_text('Set a usergroup permission set for all forum in a group').': '.sp_filter_title_display($group->group_name); ?>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_display_usergroup_select(); ?></td>
						</tr><tr>
							<td class="sflabel"><?php spa_display_permission_select(); ?></td>
						</tr><tr>
							<td class="sflabel">
    							<input type="checkbox" id="sfadddef" name="adddef" />
    							<label for="sfadddef"><?php spa_etext('Add to group default permissions'); ?></label>
                            </td>
						</tr>
					</table>

					<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" />
					<p><?php spa_etext('Caution:  Any current permission set for the selected usergroup for any forum in this group will be overwritten'); ?></p>
<?php
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_forums_group_perm_panel');
		spa_paint_close_container();
?>
		<div class="sfform-submit-bar">
    		<input type="submit" class="button-primary" id="groupperm<?php echo $group->group_id; ?>" name="groupperm<?php echo $group->group_id; ?>" value="<?php spa_etext('Add Group Permission'); ?>" />
    		<input type="button" class="button-primary spCancelForm" data-target="#group-<?php echo $group->group_id; ?>" id="grouppermcancel<?php echo $group->group_id; ?>" name="grouppermcancel<?php echo $group->group_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
?>