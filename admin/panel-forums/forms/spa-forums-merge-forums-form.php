<?php
/*
Simple:Press
Admin Forums Merge Forums Form
$LastChangedDate: 2011-09-09 20:28:24 +0100 (Fri, 09 Sep 2011) $
$Rev: 7034 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the merge forums form.
function spa_forums_merge_form() {

?>
<script>
   	spj.loadAjaxForm('sfmergeforums', 'sfreloadmf');
</script>
<?php
	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=mergeforums', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmergeforums" name="sfmergeforums">
<?php
		echo sp_create_nonce('forum-adminform_mergeforums');
		spa_paint_open_tab(/*SP()->primitives->admin_text('Forums').' - '.*/SP()->primitives->admin_text('Merge Forums'));

			spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Select Source Forum to Merge From'), false);
?>
				<div class="sf-alert-block sf-info">	
					<?php SP()->primitives->admin_etext('The source forum selected here will have all sub-forums, topics, posts and references transferred to the forum selected as the target for the merge. It will then be deleted.'); ?>
				</div>
                                <div id="forumselect1" class="sf-select-wrap">				
                                    <select name="source">
						<?php echo sp_render_group_forum_select(false, false, false, true, SP()->primitives->admin_text('Select Source Forum to Merge From')); ?>
                                    </select>
				</div>
<?php
			spa_paint_close_fieldset();
			spa_paint_close_panel();

		spa_paint_tab_right_cell();

			spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Select Target Forum to Merge To'), true, 'merge-forums');
?>
				<div class="sf-alert-block sf-info">
					<?php SP()->primitives->admin_etext('The target forum selected here will inherit all sub-forums, topics, posts and references from the source forum. Current permissions for this forum will be retained.'); ?>
				</div>
				<div id="forumselect2" class="sf-select-wrap">	
                                    <select name="target">
						<?php echo sp_render_group_forum_select(false, false, false, true, SP()->primitives->admin_text('Select Target Forum to Merge To')); ?>
                                    </select>
				</div>
<?php
			spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_forums_merge_forums_panel');

		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Perform Forum Merge'); ?>" />
		</div>
	<?php spa_paint_close_tab(); ?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}
