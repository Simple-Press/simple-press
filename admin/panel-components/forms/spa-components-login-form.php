<?php
/*
Simple:Press
Admin Components Login Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_login_form() {
?>
<script>
   	spj.loadAjaxForm('sfloginform', '');
</script>
<?php
	$sfcomps = spa_get_login_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'components-loader&amp;saveform=login', 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfloginform" name="sflogin">
	<?php echo sp_create_nonce('forum-adminform_login'); ?>
<?php
	spa_paint_options_init();

    #== LOGIN Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Components').' - '.*/SP()->primitives->admin_text('Login And Registration'));
			if (false == get_option('users_can_register')) {
				spa_paint_open_panel();
					spa_paint_open_fieldset(SP()->primitives->admin_text('Member Registrations'), true, 'no-login');
						echo '<div class="sf-alert-block sf-info">';
						SP()->primitives->admin_etext('Your site is currently not set to allow users to register. Click on the help icon for details of how to turn this on');
						echo '</div>';
					spa_paint_close_fieldset();
				spa_paint_close_panel();
			}

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Use Forum Buttons and Form'), true, 'use-forum-components');
					spa_paint_checkbox(SP()->primitives->admin_text('Use the forum registration button'), 'spshowregister', $sfcomps['spshowregister']);
					spa_paint_checkbox(SP()->primitives->admin_text('Use the forum login/logout button and form'), 'spshowlogin', $sfcomps['spshowlogin']);
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				$submessage = '';
				spa_paint_open_fieldset(SP()->primitives->admin_text('Alternate Login/Registration URLs'), true, 'alt-login-registration-urls');
					spa_paint_wide_textarea(SP()->primitives->admin_text('Alternate Registration URL'), 'spaltregisterurl', $sfcomps['spaltregisterurl'], $submessage);
					spa_paint_wide_textarea(SP()->primitives->admin_text('Alternate Login URL'), 'spaltloginurl', $sfcomps['spaltloginurl'], $submessage);
					spa_paint_wide_textarea(SP()->primitives->admin_text('Alternate Logout URL'), 'spaltlogouturl', $sfcomps['spaltlogouturl'], $submessage);
				spa_paint_close_fieldset();
			spa_paint_close_panel();


			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('Tracking Timeout'), true, 'tracking-timeout');
			        spa_paint_input(SP()->primitives->admin_text('Tracking Timeout (minutes)'), 'sptimeout', $sfcomps['sptimeout'], false, true);
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_components_login_left_panel');

		spa_paint_tab_right_cell();

			spa_paint_open_panel();
				$submessage = '';
				spa_paint_open_fieldset(SP()->primitives->admin_text('Login/Registration Redirects'), true, 'login-registration-urls');
					spa_paint_wide_textarea(SP()->primitives->admin_text('Login redirect'), 'sfloginurl', $sfcomps['sfloginurl'], $submessage);
					spa_paint_wide_textarea(SP()->primitives->admin_text('Logout redirect'), 'sflogouturl', $sfcomps['sflogouturl'], $submessage);
					spa_paint_wide_textarea(SP()->primitives->admin_text('Registration redirect'), 'sfregisterurl', $sfcomps['sfregisterurl'], $submessage);
					spa_paint_wide_textarea(SP()->primitives->admin_text('Login URL in new user email'), 'sfloginemailurl', $sfcomps['sfloginemailurl'], $submessage);
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('RPX 3rd Party Login'), true, 'rpx-login');
					spa_paint_checkbox(SP()->primitives->admin_text('Enable RPX support'), 'sfrpxenable', $sfcomps['sfrpxenable'] ?? false);
        			SP()->primitives->admin_etext('Please enter your RPX API key. If you haven\'t yet created one, please create one at');
                    echo ' <a href="https://rpxnow.com" target="_blank">Janrain</a>';
			        spa_paint_input(SP()->primitives->admin_text('RPX API key'), 'sfrpxkey', $sfcomps['sfrpxkey'] ?? '', false, true);
    				$submessage = SP()->primitives->admin_text('Force a redirect to a specific page on RPX login.  Leave blank to have SPF/RPX determine redirect location');
					spa_paint_wide_textarea(SP()->primitives->admin_text('URL to redirect to after RPX login'), 'sfrpxredirect', $sfcomps['sfrpxredirect'] ?? '', $submessage);
				spa_paint_close_fieldset();
			spa_paint_close_panel();

			spa_paint_open_panel();
				spa_paint_open_fieldset(SP()->primitives->admin_text('User Registration'), true, 'user-registration');
					spa_paint_checkbox(SP()->primitives->admin_text('Use spam tool on registration form'), 'sfregmath', $sfcomps['sfregmath']);
				spa_paint_close_fieldset();
			spa_paint_close_panel();

            do_action('sph_components_login_right_panel');

		spa_paint_close_container();
?>
    	<div class="sf-form-submit-bar">
    	   <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Login and Registration Component'); ?>" />
    	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
