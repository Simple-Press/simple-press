<?php
/*
Simple:Press
Admin Forums Enable Forum Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the enable forum form.  It is hidden until the enable forum link is clicked
function spa_forums_enable_forum_form($forum_id) {
?>
<script>
   	spj.loadAjaxForm('sfforumenable<?php echo $forum_id; ?>', 'sfreloadfb');
</script>
<?php
	spa_paint_options_init();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=enableforum', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumenable<?php echo $forum_id; ?>" name="sfforumenable<?php echo $forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumenable');
		spa_paint_open_tab(SP()->primitives->admin_text('Forums').' - '.SP()->primitives->admin_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Enable Forum'), 'true', 'enable-forum');
?>
					<input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>" />
<?php
					echo '<p><b>';
					SP()->primitives->admin_etext('Warning! You are about to enable this forum');
					echo '</b></p>';
					echo '<p>';
					SP()->primitives->admin_etext('This will restore the forum to the front end with permissions/memberships controlling access');
					echo '</p>';
					echo '<p>';
					SP()->primitives->admin_etext('Click on the enable forum button below to proceed');
					echo '</p>';
				spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_enable_forum_panel');
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
    		<input type="submit" class="sf-button-primary" id="sfforumenable<?php echo $forum_id; ?>" name="sfforumenable<?php echo $forum_id; ?>" value="<?php SP()->primitives->admin_etext('Enable Forum'); ?>" />
    		<input type="button" class="sf-button-primary spCancelForm" data-target="#forum-<?php echo $forum_id; ?>" id="sfforumenable<?php echo $forum_id; ?>" name="enableforumcancel<?php echo $forum_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
    	</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
