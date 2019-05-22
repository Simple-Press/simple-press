<?php
/*
Simple:Press
Admin Options Global Display Form
$LastChangedDate: 2016-06-25 11:55:17 +0100 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_newposts_form() {
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			spj.loadAjaxForm('sfnewpostsform', '');
			$('#color-background').farbtastic('#flag-background');
			$('#color-text').farbtastic('#flag-color');
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
	$sfoptions = spa_get_newposts_data();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'options-loader&amp;saveform=newposts', 'options-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfnewpostsform" name="sfnewposts">
	<?php echo sp_create_nonce('forum-adminform_newposts'); ?>
<?php
	spa_paint_options_init();

    #== GLOBAL Tab ============================================================

	spa_paint_open_tab(SP()->primitives->admin_text('Options').' - '.SP()->primitives->admin_text('User New Posts Handling'));

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('New Posts/Topics Cache'), true, 'topic-cache');
				spa_paint_input(SP()->primitives->admin_text('How many new posts to keep in cache list'), 'topiccache', $sfoptions['topiccache']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Users List of Unread Posts'), true, 'unread-posts');
				spa_paint_input(SP()->primitives->admin_text('Default number of unread posts for users'), 'sfdefunreadposts', $sfoptions['sfdefunreadposts']);
				spa_paint_checkbox(SP()->primitives->admin_text('Allow users to set number of unread posts in profile'), 'sfusersunread', $sfoptions['sfusersunread']);
				spa_paint_input(SP()->primitives->admin_text('Max number of unread posts allowed to be set by users'), 'sfmaxunreadposts', $sfoptions['sfmaxunreadposts']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('New/Unread Posts Flag Display'), true, 'new-post-flags');
				spa_paint_checkbox(SP()->primitives->admin_text('Display new post flags'), 'flagsuse', $sfoptions['flagsuse']);
				spa_paint_input(SP()->primitives->admin_text('Text to use in flags'), 'flagstext', $sfoptions['flagstext']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_newposts_left_panel');
	spa_paint_tab_right_cell();

    	spa_paint_open_panel();
    		spa_paint_open_fieldset(__('New Posts Flag Display', 'sp-polls'), true, 'flag-display');
?>
				<div>
					<span class="sfalignleft"><?php echo SP()->primitives->admin_text('New Post Flag background color'); ?>:</span>
				</div>
				<div>
					<input id="flag-background" type="text" value="#<?php echo $sfoptions['flagsbground']; ?>" name="flagsbground" style="width:100%;font-weight:bold;" />
					<div id="color-background" style="margin: 0 auto; width: 195px;"></div>
				</div>
				<div>
					<span class="sfalignleft"><?php echo SP()->primitives->admin_text('New Post Flag text color'); ?>:</span>
				</div>
				<div>
					<input id="flag-color" type="text" value="#<?php echo $sfoptions['flagscolor']; ?>" name="flagscolor" style="width:100%;font-weight:bold;" />
					<div id="color-text" style="margin: 0 auto; width: 195px;"></div>
				</div>
<?php
    		spa_paint_close_fieldset();
    	spa_paint_close_panel();

		do_action('sph_options_newposts_right_panel');

		spa_paint_close_container();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update New Post Handling'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
