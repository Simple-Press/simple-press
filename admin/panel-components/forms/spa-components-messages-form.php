<?php
/*
Simple:Press
Admin Components Custom Messages Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_messages_form() {
?>
<script>
   	spj.loadAjaxForm('sfmessagesform', '');
</script>
<?php
	$sfcomps = spa_get_messages_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=messages', 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmessagesform" name="sfmessages">
	<?php echo sp_create_nonce('forum-adminform_messages'); ?>
<?php
	spa_paint_options_init();

    #== CUSTOM MESSAGES Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Components').' - '.*/SP()->primitives->admin_text('Custom Messages'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Custom Message Above Editor #1'), true, 'editor-message');
				$submessage = SP()->primitives->admin_text('Text you enter here will be displayed above the editor (new topic and/or new post)');
				spa_paint_wide_editor(SP()->primitives->admin_text('Custom message'), 'sfpostmsgtext', $sfcomps['sfpostmsgtext'], $submessage, 4);
				spa_paint_checkbox(SP()->primitives->admin_text('Display for new topic'), 'sfpostmsgtopic', $sfcomps['sfpostmsgtopic']);
				spa_paint_checkbox(SP()->primitives->admin_text('Display for new post'), 'sfpostmsgpost', $sfcomps['sfpostmsgpost']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Custom Intro Text in Editor'), true, 'editor-intro');
				$submessage = SP()->primitives->admin_text('Text you enter here will be displayed inside the editor (new topic only)');
				spa_paint_wide_editor(SP()->primitives->admin_text('Custom intro message'), 'sfeditormsg', $sfcomps['sfeditormsg'], $submessage, 4);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_messages_left_panel');

	spa_paint_tab_right_cell();
	
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Custom Message Above Editor #2'), true, 'editor-message');
				$submessage = SP()->primitives->admin_text('Text you enter here will be displayed above the editor (new topic and/or new post)');
				spa_paint_wide_editor(SP()->primitives->admin_text('Custom message'), 'sfpostmsgtext2', $sfcomps['sfpostmsgtext2'], $submessage, 4);
				spa_paint_checkbox(SP()->primitives->admin_text('Display for new topic'), 'sfpostmsgtopic2', $sfcomps['sfpostmsgtopic2']);
				spa_paint_checkbox(SP()->primitives->admin_text('Display for new post'), 'sfpostmsgpost2', $sfcomps['sfpostmsgpost2']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();	

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Sneak Peek Statement'), true, 'sneak-peek');
				$submessage = SP()->primitives->admin_text('If you are allowing guests to view forum and topic lists, but not see the actual Posts, this message is displayed to encourage them to sign up');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Sneak peek statement'), 'sfsneakpeek', $sfcomps['sfsneakpeek'], $submessage);
				$submessage = SP()->primitives->admin_text('Force a redirect to a specific page instead of displaying the sneak peek message.');
				spa_paint_wide_textarea(SP()->primitives->admin_text('URL to redirect to for sneak peek'), 'sfsneakredirect', $sfcomps['sfsneakredirect'], $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Admin view statement'), true, 'admin-view');
				$submessage = SP()->primitives->admin_text('If you are inhibiting usergroups from seeing admin posts, this message is displayed to them');
				spa_paint_wide_textarea(SP()->primitives->admin_text('Admin view statement'), 'sfadminview', $sfcomps['sfadminview'], $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('User only view statement'), true, 'user-view');
				$submessage = SP()->primitives->admin_text('If you are limiting usergroups to only seeing their posts or post from admins and moderators, this message is displayed to them');
				spa_paint_wide_textarea(SP()->primitives->admin_text('User only view statement'), 'sfuserview', $sfcomps['sfuserview'], $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_components_messages_right_panel');

		spa_paint_close_container();

?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Custom Messages Component'); ?>" />
	</div>
<?php
	spa_paint_close_tab();

	spa_print_ajax_editor_settings();
	
?>
	</form>
<?php
}
