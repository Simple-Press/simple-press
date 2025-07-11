<?php
/*
Simple:Press
Admin Components Special Rank Delete Member Form
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_components_sr_del_members_form($rank_id) {
?>
<script>
   	spj.loadAjaxForm('sfmemberdel<?php echo esc_js($rank_id); ?>', 'sfreloadfr');
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL."components-loader&amp;saveform=specialranks&amp;targetaction=delmember&amp;id=" . esc_attr($rank_id), 'components-loader');
?>
	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfmemberdel<?php echo esc_attr($rank_id); ?>" name="sfmemberdel<?php echo esc_attr($rank_id); ?>">
<?php
                spa_paint_open_nohead_tab(true, '');
		sp_echo_create_nonce('special-rank-del');
?>
					<!--<p><?php SP()->primitives->admin_etext('Select member to add (use CONTROL for multiple members)'); ?></p>-->
<?php
                	$from = esc_js(SP()->primitives->admin_text('Current members'));
                	$to = esc_js(SP()->primitives->admin_text('Selected Members'));
                    $action = 'delru';
                    require_once SP_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
?>
					<div class="clearboth"></div>
<?php
        $loc = 'sfrankshow-' . $rank_id;
?>
            <div class="sf-controls">
		<input type="submit" class="sf-button-primary spSpecialRankDel" id="sfmemberdel<?php echo esc_attr($rank_id); ?>" name="sfmemberdel<?php echo esc_attr($rank_id); ?>" data-target="#dmember_id<?php echo esc_attr($rank_id); ?>" value="<?php SP()->primitives->admin_etext('Remove Members'); ?>" />
		<input type="button" class="sf-button-primary spCancelForm" data-target="#members-<?php echo esc_attr($rank_id); ?>" data-loc="<?php echo esc_attr($loc); ?>" id="sfmemberdel<?php echo esc_attr($rank_id); ?>" name="addmemberscancel<?php echo esc_attr($rank_id); ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
            </div>
	 <?php
        spa_paint_close_container();
        spa_paint_close_tab();
        ?>
        </form>

	<div class="sfform-panel-spacer"></div>
<?php
}
