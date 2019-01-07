<?php
/*
Simple:Press
Admin Options Email Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_email_form() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfemailform', '');
    });
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

	spa_paint_open_tab(spa_text('Options').' - '.spa_text('Email Settings'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('New User Email'), true, 'new-user-email');
				spa_paint_checkbox(spa_text('Use the Simple:Press new user email version'), 'sfusespfreg', $sfoptions['sfusespfreg']);
				echo '<p><strong>'.spa_text('The following placeholders are available: %USERNAME%, %BLOGNAME%, %SITEURL%, %LOGINURL%, %PWURL%').'</strong></p>';
				spa_paint_input(spa_text('Email subject line'), 'sfnewusersubject', $sfoptions['sfnewusersubject'], false, true);
				spa_paint_wide_textarea(spa_text('Email message (no html)'), 'sfnewusertext', $sfoptions['sfnewusertext'], $submessage = '', 4);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_email_left_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Email Address Settings'), true, 'email-address-settings');
				spa_paint_checkbox(spa_text('Use the following email settings'), 'sfmailuse', $sfoptions['sfmailuse']);
				spa_paint_input(spa_text('The senders name'), 'sfmailsender', $sfoptions['sfmailsender'], false, false);
				spa_paint_input(spa_text('The email from name'), 'sfmailfrom', $sfoptions['sfmailfrom'], false, false);
				spa_paint_input(spa_text('The email domain name'), 'sfmaildomain', $sfoptions['sfmaildomain'], false, false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_email_right_panel');

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Email Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>