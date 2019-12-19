<?php
/*
Simple:Press
Admin Forums Delete Forum Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the delete forum form.  It is hidden until the delete forum link is clicked
function spa_forums_delete_forum_form($forum_id) {
?>
<script>
   	spj.loadAjaxForm('sfforumdelete<?php echo $forum_id; ?>', 'sfreloadfb');
</script>
<?php
	$forum = SP()->DB->table(SPFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=deleteforum', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumdelete<?php echo $forum->forum_id; ?>" name="sfforumdelete<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumdelete');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Delete Forum'), 'true', 'delete-forum');
?>
					<input type="hidden" name="group_id" value="<?php echo $forum->group_id; ?>" />
					<input type="hidden" name="forum_id" value="<?php echo $forum->forum_id; ?>" />
					<input type="hidden" name="cforum_seq" value="<?php echo $forum->forum_seq; ?>" />
					<input type="hidden" name="parent" value="<?php echo $forum->parent; ?>" />
					<input type="hidden" name="children" value="<?php echo $forum->children; ?>" />
<?php
					echo '<div class="sf-alert-block sf-info"><p>';
					SP()->primitives->admin_etext('Warning! You are about to delete a forum');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('This will remove ALL topics and posts contained in this forum');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('Any Subforums will be promoted');
					echo '</p>';
					echo '<p>';
					echo sprintf(SP()->primitives->admin_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('Click on the delete forum button below to proceed');
					echo '</p>';
					echo '<p><strong>';
					SP()->primitives->admin_etext('IMPORTANT: Be patient. For busy forums this action can take some time');
					echo '</strong></p></div>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_delete_forum_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="sfforumdelete<?php echo $forum->forum_id; ?>" name="sfforumdelete<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Delete Forum'); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#forum-<?php echo $forum->forum_id; ?>" id="sfforumdelete<?php echo $forum->forum_id; ?>" name="delforumcancel<?php echo $forum->forum_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
