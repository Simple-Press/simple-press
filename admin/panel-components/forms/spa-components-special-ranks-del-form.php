<?php
/*
Simple:Press
Admin Components Special Rank Delete Member Form
$LastChangedDate: 2016-10-21 16:27:53 -0500 (Fri, 21 Oct 2016) $
$Rev: 14650 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_sr_del_members_form($rank_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfmemberdel<?php echo $rank_id; ?>', 'sfreloadfr');
    });
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL."components-loader&amp;saveform=specialranks&amp;targetaction=delmember&amp;id=$rank_id", 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmemberdel<?php echo $rank_id; ?>" name="sfmemberdel<?php echo $rank_id; ?>">
<?php
		echo sp_create_nonce('special-rank-del');
?>
					<p><?php spa_etext('Select member to add (use CONTROL for multiple members)'); ?></p>
<?php
                	$from = esc_js(spa_text('Current members'));
                	$to = esc_js(spa_text('Selected Members'));
                    $action = 'delru';
                	include_once SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
?>
					<div class="clearboth"></div>
<?php
        $loc = 'sfrankshow-'.$rank_id;
?>
		<input type="submit" class="button-primary spSpecialRankDel" id="sfmemberdel<?php echo $rank_id; ?>" name="sfmemberdel<?php echo $rank_id; ?>" data-target="#dmember_id<?php echo $rank_id; ?>" value="<?php spa_etext('Remove Members'); ?>" />
		<input type="button" class="button-primary spSpecialRankCancel" data-target="#members-<?php echo $rank_id; ?>" data-loc="<?php echo $loc; ?>" id="sfmemberdel<?php echo $rank_id; ?>" name="addmemberscancel<?php echo $rank_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>