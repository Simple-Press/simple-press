<?php
/*
Simple:Press
Admin Forums Delete Group Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

/* function to display the delete group form.  It is hidden until the delete group link is clicked */
function spa_forums_delete_group_form($group_id) {
?>
<script>
   	spj.loadAjaxForm('sfgroupdel<?php echo esc_attr($group_id); ?>', 'sfreloadfb');
</script>
<?php
	$group = SP()->DB->table(SPGROUPS, "group_id=" . intval($group_id), 'row');

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=deletegroup', 'forums-loader');
?>
	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfgroupdel<?php echo esc_attr($group->group_id); ?>" name="sfgroupdel<?php echo esc_attr($group->group_id); ?>">
<?php
		sp_echo_create_nonce('forum-adminform_groupdelete');
		//spa_paint_open_tab(esc_html(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums')), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Delete Group'), 'true', 'delete-forum-group');
?>
					<input type="hidden" name="group_id" value="<?php echo esc_attr($group->group_id); ?>" />
					<input type="hidden" name="cgroup_seq" value="<?php echo esc_attr($group->group_seq); ?>" />
<?php
					echo '<div class="sf-alert-block sf-info"><p>';
					SP()->primitives->admin_etext('Warning! You are about to delete a group');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('This will remove ALL forums, topics and posts contained in this group');
					echo '</p>';
					echo '<p>';
					echo sprintf(esc_html(SP()->primitives->admin_text('Please note that this action %s can NOT be reversed %s')), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('Click on the delete group button below to proceed');
					echo '</p></div>';

				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_delete_group_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="groupdel<?php echo esc_attr($group->group_id); ?>" name="groupdel<?php echo esc_attr($group->group_id); ?>" value="<?php SP()->primitives->admin_etext('Delete Group'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#group-<?php echo esc_attr($group->group_id); ?>" id="sfgroupdel<?php echo esc_attr($group->group_id); ?>" name="groupdelcancel<?php echo esc_attr($group->group_id); ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
