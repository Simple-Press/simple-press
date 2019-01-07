<?php
/*
Simple:Press
Admin Toolbox Uninstall Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_housekeeping_form() {
?>
<script>
	spj.loadAjaxForm('sfindexes', 'sfreloadhk');
	spj.loadAjaxForm('sfnewpostcleanup', 'sfreloadhk');
	spj.loadAjaxForm('sftransientcleanup', 'sfreloadhk');
	spj.loadAjaxForm('sfpostcountcleanup', 'sfreloadhk');
	spj.loadAjaxForm('sfresetprofiletabs', 'sfreloadhk');
	spj.loadAjaxForm('sfresetauths', 'sfreloadhk');
	spj.loadAjaxForm('sfresetplugdata', 'sfreloadhk');
	spj.loadAjaxForm('sfresetcombined', 'sfreloadhk');
	spj.loadAjaxForm('sfflushcache', 'sfreloadhk');
	spj.loadAjaxForm('sfflushxml', 'sfreloadhk');
</script>
<?php
    $ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=housekeeping', 'toolbox-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfhousekeepingform" name="sfhousekeeping">
	</form>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(SP()->primitives->admin_text('Toolbox').' - '.SP()->primitives->admin_text('Housekeeping'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Rebuild Indexes'), true, 'rebuild-indexes');
				echo '<p class="sublabel">'.SP()->primitives->admin_text("You shouldn't need to rebuild your indexes unless asked to by Simple:Press Support.").'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfindexes" name="sfindexes">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<p class="sublabel"><?php SP()->primitives->admin_etext('Select forum to have its indexes rebuilt') ?>:<br /><br /></p>
				<select class="wp-core-ui" name="forum_id" >
					<?php echo sp_render_group_forum_select(false, false, false, true, '', '', 'wp-core-ui', 20); ?>
				</select>
                <br /><br />
				<input type="submit" class="button-primary spShowElement" id="saveit1" name="rebuild-fidx" value="<?php SP()->primitives->admin_etext('Rebuild Forum Indexes'); ?>" data-target="#riimg" />
				<img class="sfhidden" id="riimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
				echo '<p class="sublabel">'.SP()->primitives->admin_text('Note: Rebuilding the forum indexes may take some time if you have a large number of topics or posts.').'</p>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('New Post Cleanup'), true, 'newpost-cleanup');
				echo '<p class="sublabel">'.SP()->primitives->admin_text('This will reset the New Posts list for users who haven not visited the forum in the specified number of days.').'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfnewpostcleanup" name="sfnewpostcleanup">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>

				<span>Number of Days Since User's Last Visit:
				<input class="wp-core-ui" type="text" value="30" name="sfdays" /></span>
				<br />
				<input type="submit" class="button-primary spShowElement" id="saveit2" name="clean-newposts" value="<?php SP()->primitives->admin_etext('Clean New Posts List'); ?>" data-target="#npcimg" />
				<img class="sfhidden" id="npcimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
				echo '<p>'.SP()->primitives->admin_text('Note: Cleaning up the New Post Lists may take some time if you have a large number of users that meet the criteria.').'</p>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('User Post Count Cleanup'), true, 'post-count-cleanup');
				echo '<p class="sublabel">'.SP()->primitives->admin_text('This will go through the users and posts database tables and recalculate post counts for all users based on existing posts.').'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfpostcountcleanup" name="sfpostcountcleanup">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?><br />
				<input type="submit" class="button-primary spShowElement" id="saveit3" name="postcount-cleanup" value="<?php SP()->primitives->admin_etext('Clean Up Post Counts'); ?>" data-target="#pcimg" />
				<img class="sfhidden" id="pcimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
				echo '<p class="sublabel">'.SP()->primitives->admin_text('Note: Recalculating user post counts may take some time if you have a large number of users and cannot be reversed.').'</p>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Transient Cleanup'), true, 'transient-cleanup');
				echo '<p class="sublabel">'.SP()->primitives->admin_text('This will clean up expired WP Transients from the WP options table and any expired SP user notices.').'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sftransientcleanup" name="sftransientcleanup">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?><br />
				<input type="submit" class="button-primary spShowElement" id="saveit4" name="transient-cleanup" value="<?php SP()->primitives->admin_etext('Clean Up Transients'); ?>" data-target="#tcimg" />
				<img class="sfhidden" id="tcimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_housekeeping_left_panel');
		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Rebuild Default Profile Tabs'), true, 'reset-tabs');
				echo '<p class="sublabel">'.SP()->primitives->admin_text('This will remove all Profile Tabs and restore to default state.').'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfresetprofiletabs" name="sfresetprofiletabs">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<input type="submit" class="button-primary spShowElement" id="saveit5" name="reset-tabs" value="<?php SP()->primitives->admin_etext('Reset Profile Tabs'); ?>" data-target="#rdptimg" />
				<img class="sfhidden" id="rdptimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Reset the Auths Cache'), true, 'reset-auths');
				echo '<p class="sublabel">'.SP()->primitives->admin_text("This will force a rebuild of each user's auth cache. It does not change any permissions.").'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfresetauths" name="sfresetauths">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<input type="submit" class="button-primary spShowElement" id="saveit6" name="reset-auths" value="<?php SP()->primitives->admin_etext('Reset Auths Cache'); ?>" data-target="#rtacimg" />
				<img class="sfhidden" id="rtacimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();


		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Reset Users Plugin Data Cache'), true, 'reset-plugin-data');
				echo '<p class="sublabel">'.SP()->primitives->admin_text("This will force each user's plugin data cache to be cleared.").'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfresetplugdata" name="sfresetplugdata">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<input type="submit" class="button-primary spShowElement" id="saveit10" name="reset-plugin-data" value="<?php SP()->primitives->admin_etext('Reset Users Plugin Data'); ?>" data-target="#rrpdimg" />
				<img class="sfhidden" id="rrpdimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();




		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Reset combined CSS/JS'), true, 'reset-combined');
				echo '<p class="sublabel">'.SP()->primitives->admin_text('This will force a rebuild of the combined CSS and JS cache files.').'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfresetcombined" name="sfresetcombined">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<input type="submit" class="button-primary spShowElement" id="saveit7" name="reset-combinedcss" value="<?php SP()->primitives->admin_etext('Reset Combined CSS Cache'); ?>" data-target="#rtccimg" />
				<input type="submit" class="button-primary spShowElement" id="saveit8" name="reset-combinedjs" value="<?php SP()->primitives->admin_etext('Reset Combined Script Cache'); ?>" data-target="#rtccimg" />
				<img class="sfhidden" id="rtccimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Flush general cache'), true, 'flush-cache');
				echo '<p class="sublabel">'.SP()->primitives->admin_text("This will force a flushing of the general cache.").'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfflushcache" name="sfflushcache">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<input type="submit" class="button-primary spShowElement" id="saveit9" name="flushcache" value="<?php SP()->primitives->admin_etext('Flush General Cache'); ?>" data-target="#fcacheimg" />
				<img class="sfhidden" id="fcacheimg" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Flush XML cache'), true, 'flush-xml');
				echo '<p class="sublabel">'.SP()->primitives->admin_text("This will force a flushing of the xml api cache.").'</p>';
?>
				<form action="<?php echo $ajaxURL; ?>" method="post" id="sfflushxml" name="sfflushxml">
				<?php echo sp_create_nonce('forum-adminform_housekeeping'); ?>
				<input type="submit" class="button-primary spShowElement" id="saveit10" name="flushxmlcache" value="<?php SP()->primitives->admin_etext('Flush XML API Cache'); ?>" data-target="#fcachexml" />
				<img class="sfhidden" id="fcachexml" src="<?php echo SPCOMMONIMAGES.'working.gif'; ?>" alt=""/>
				</form>
<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_housekeeping_right_panel');

		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}
