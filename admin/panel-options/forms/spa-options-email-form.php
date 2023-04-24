<?php
/*
Simple:Press
Admin Options Email Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_email_form() {
?>
<script>
   	spj.loadAjaxForm('sfemailform', '');
</script>
<?php
	$sfoptions = spa_get_email_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'options-loader&amp;saveform=email', 'options-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfemailform" name="sfemail">
	<?php echo sp_create_nonce('forum-adminform_email'); ?>
<?php
	spa_paint_options_init();

    #== EMAIL Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Options').' - '.*/SP()->primitives->admin_text('Email Settings'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('New User Email'), true, 'new-user-email');
				spa_paint_checkbox(SP()->primitives->admin_text('Use the Simple:Press new user email version'), 'sfusespfreg', $sfoptions['sfusespfreg']);
                echo '<div class="sf-form-row">';
				echo SP()->primitives->admin_text('The following placeholders are available: ') ;
                echo '<code>%USERNAME%</code>,<code>%BLOGNAME%</code>,<code>%SITEURL%</code>,<code>%LOGINURL%</code>,<code>%PWURL%</code>';
                echo '</div>';
				spa_paint_input(SP()->primitives->admin_text('Email subject line'), 'sfnewusersubject', $sfoptions['sfnewusersubject'], false, true);
				spa_paint_wide_textarea(SP()->primitives->admin_text('Email message (no html)'), 'sfnewusertext', $sfoptions['sfnewusertext'], $submessage = '', 4);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_email_left_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Email Address Settings'), true, 'email-address-settings');
				spa_paint_checkbox(SP()->primitives->admin_text('Use the following email settings'), 'sfmailuse', $sfoptions['sfmailuse']);
				spa_paint_input(SP()->primitives->admin_text('The senders name'), 'sfmailsender', $sfoptions['sfmailsender'], false, false);
				spa_paint_input(SP()->primitives->admin_text('The email from name'), 'sfmailfrom', $sfoptions['sfmailfrom'], false, false);
				spa_paint_input(SP()->primitives->admin_text('The email domain name'), 'sfmaildomain', $sfoptions['sfmaildomain'], false, false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_email_right_panel');

		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Email Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
