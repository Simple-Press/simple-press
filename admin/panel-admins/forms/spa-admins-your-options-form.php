<?php
/*
Simple:Press
Admin Admins Your Options Form
$LastChangedDate: 2016-06-25 05:55:17 -0500 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_admins_your_options_form() {
	global $spThisUser;
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfmyadminoptionsform', 'sfreloadao');
    });
</script>
<?php
	$sfadminsettings = spa_get_admins_your_options_data();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'admins-loader&amp;saveform=youradmin', 'admins-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmyadminoptionsform" name="sfmyadminoptions">
	<?php echo sp_create_nonce('my-admin_options'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Admins').' - '.spa_text('Your Admin Options'), true);

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Your Admin/Moderator Options'), 'true', 'your-admin-options');

				if ($spThisUser->admin) {
					echo '<br /><div class="sfoptionerror"><strong>';
					spa_etext('The following options are personal - each admin and moderator needs to visit this panel to set their own options');
					echo '</strong><br />';
					spa_etext('Alternatively you can check the option below to apply to all moderators and when you update this panel they will inherit the same option settings');
					echo '</div><br />';
				}

				spa_paint_checkbox(spa_text('Receive email notification on new topic/post'), 'sfnotify', $sfadminsettings['sfnotify']);
				spa_paint_checkbox(spa_text('Receive notification (within forum - not email) on topic/post edits'), 'notify-edited', $sfadminsettings['notify-edited']);
				spa_paint_checkbox(spa_text('Bypass the Simple Press logout redirect'), 'bypasslogout', $sfadminsettings['bypasslogout']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_admins_options_top_panel');

		if ($spThisUser->admin) {
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Set Your Moderator Options'), 'true', 'set-moderator-options');
					spa_paint_checkbox(spa_text('Grant all moderators the same option settings as above'), 'setmods', $sfadminsettings['setmods']);

					echo '<br /><div class="sfoptionerror"><strong>';
					spa_etext('If you check this option so that all your moderators inherit the settings above - note that after you update this panel this checkbox will return to an unchecked state');
					echo '</strong><br />';
					spa_etext('Inheritance ONLY takes place when this box is checked and the panel updated. Any updated changes you make with the box iunchecked ONLY apply to you');
					echo '</div><br />';

				spa_paint_close_fieldset();
			spa_paint_close_panel();
		}
		do_action('sph_admins_options_bottom_panel');
		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Your Admin Options'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
?>