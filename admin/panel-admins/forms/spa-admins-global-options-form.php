<?php
/*
Simple:Press
Admin Admins New Admins Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_admins_global_options_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfadminoptionsform', '');
    });
</script>
<?php
	$sfoptions = spa_get_admins_global_options_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'admins-loader&amp;saveform=globaladmin', 'admins-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfadminoptionsform" name="sfadminoptions">
	<?php echo sp_create_nonce('global-admin_options'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Admins')." - ".spa_text('Global Admin Options'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Admin Options'), 'true', 'global-options');
				spa_paint_checkbox(spa_text('Display forum statistics in the dashboard'), 'sfdashboardstats', $sfoptions['sfdashboardstats']);
				spa_paint_checkbox(spa_text('Approve all posts in topic in moderation when an admin posts to the topic'), 'sfadminapprove', $sfoptions['sfadminapprove']);
				spa_paint_checkbox(spa_text('Approve all posts in topic in moderation when a moderator posts to the topic'), 'sfmoderapprove', $sfoptions['sfmoderapprove']);
				spa_paint_checkbox(spa_text('Display post/topic edit notices to users'), 'editnotice', $sfoptions['editnotice']);
				spa_paint_checkbox(spa_text('Display post/topic move notices to users'), 'movenotice', $sfoptions['movenotice']);
			spa_paint_close_fieldset();

		spa_paint_close_panel();

		do_action('sph_admins_global_left_panel');

		spa_paint_tab_right_cell();
		do_action('sph_admins_global_right_panel');

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Global Admin Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>