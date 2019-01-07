<?php
/*
Simple:Press
Admin Components Special Rank Add Member Form
$LastChangedDate: 2016-10-21 16:27:53 -0500 (Fri, 21 Oct 2016) $
$Rev: 14650 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_sr_add_members_form($rank_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	spjAjaxForm('sfmembernew<?php echo $rank_id; ?>', 'sfreloadfr');
    });
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL."components-loader&saveform=specialranks&targetaction=addmember&id=$rank_id", 'components-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmembernew<?php echo $rank_id; ?>" name="sfmembernew<?php echo $rank_id ?>">
<?php
		echo sp_create_nonce('special-rank-add');
?>
					<p><?php spa_etext('Select members to add (use CONTROL for multiple members)') ?></p>
<?php
                	$from = esc_js(spa_text('Eligible members'));
                	$to = esc_js(spa_text('Selected members'));
                    $action = 'addru';
                	include_once SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
?>
					<div class="clearboth"></div>
<?php
        $loc = 'sfrankshow-'.$rank_id;
?>
		<input type="submit" class="button-primary spSpecialRankAdd" id="sfnewmember<?php echo $rank_id; ?>" name="sfnewmember<?php echo $rank_id; ?>" data-target="#amember_id<?php echo $rank_id; ?> option" value="<?php spa_etext('Add Members'); ?>" />
		<input type="button" class="button-primary spSpecialRankCancel" data-target="#members-<?php echo $rank_id; ?>" data-loc="<?php echo $loc; ?>" id="addmemberscancel<?php echo $rank_id; ?>" name="addmemberscancel<?php echo $rank_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>