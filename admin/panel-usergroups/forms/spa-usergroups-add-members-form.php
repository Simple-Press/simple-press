<?php
/*
Simple:Press
Admin User Groups Add Member Form
$LastChangedDate: 2016-10-23 14:40:24 -0500 (Sun, 23 Oct 2016) $
$Rev: 14666 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_add_members_form($usergroup_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	jQuery('#sfmembernew<?php echo $usergroup_id; ?>').ajaxForm({
    		target: '#sfmsgspot',
    	});
    });
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=addmembers', 'usergroups-loader');
	$url = wp_nonce_url(SPAJAXURL.'memberships&amp;targetaction=add', 'memberships');
	$target = 'sfmsgspot';
	$smessage = esc_js(spa_text('Please Wait - Processing'));
	$emessage = esc_js(spa_text('Users added'));
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmembernew<?php echo $usergroup_id; ?>" name="sfmembernew<?php echo $usergroup_id ?>" onsubmit="spjAddDelMembers('sfmembernew<?php echo $usergroup_id ?>', '<?php echo $url; ?>', '<?php echo $target; ?>', '<?php echo $smessage; ?>', '<?php echo($emessage); ?>', 0, 50, '#amid<?php echo $usergroup_id; ?>');">
<?php
		echo sp_create_nonce('forum-adminform_membernew');

		$sfmemberopts = array();
		$sfmemberopts = sp_get_option('sfmemberopts');

		if (!isset($sfmemberopts['sfsinglemembership'])) {
			$singleGrp = false;
		} else {
			$singleGrp = $sfmemberopts['sfsinglemembership'];
		}
		$singleOpt = ($singleGrp) ? spa_text('On') : spa_text('Off');
		$singleMsg = ($singleGrp) ? spa_text('Any members moved will be deleted from current user group memberships') : spa_text('Any members moved will be retained in current user group memberships');
?>
		<input type="hidden" name="usergroup_id" value="<?php echo $usergroup_id; ?>" />
		<p><?php spa_etext('Select members to add (use CONTROL for multiple members)'); ?></p>
		<p><br /><?php spa_etext('The Option'); ?> <b><?php spa_etext('Users are limited to single usergroup membership'); ?></b> <?php echo sprintf(spa_text("is turned %s"), $singleOpt); ?></b><br /><?php echo $singleMsg; ?></p>
<?php
    	$from = esc_js(spa_text('Eligible Members'));
    	$to = esc_js(spa_text('Selected Members'));
        $action = 'addug';
    	include_once SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
?>
		<div class="clearboth"></div>
<?php
        do_action('sph_usergroup_add_member_panel');
?>
		<span><input type="submit" class="button-primary" id="sfmembernew<?php echo $usergroup_id; ?>" name="sfmembernew<?php echo $usergroup_id; ?>" value="<?php spa_etext('Add Members'); ?>" /> <span class="button sfhidden" id='onFinish'></span>
		<input type="button" class="button-primary spCancelForm" data-target="#members-<?php echo $usergroup_id; ?>" id="sfmembernew<?php echo $usergroup_id; ?>" name="addmemberscancel<?php echo $usergroup_id; ?>" value="<?php spa_etext('Cancel'); ?>" /></span>
		<br />
		<div class="pbar" id="progressbar"></div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>