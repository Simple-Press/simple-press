<?php
/*
Simple:Press
Admin User Groups Delete Member Form
$LastChangedDate: 2016-10-23 14:40:24 -0500 (Sun, 23 Oct 2016) $
$Rev: 14666 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_delete_members_form($usergroup_id) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
    	jQuery('#sfmemberdel<?php echo $usergroup_id; ?>').ajaxForm({
    		target: '#sfmsgspot',
    	});
    });
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=delmembers', 'usergroups-loader');

	$url = wp_nonce_url(SPAJAXURL.'memberships&amp;targetaction=del', 'memberships');
	$target = 'sfmsgspot';
	$smessage = esc_js(spa_text('Please Wait - Processing'));
	$emessage = esc_js(spa_text('Users Deleted/Moved'));
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmemberdel<?php echo $usergroup_id; ?>" name="sfmemberdel<?php echo $usergroup_id ?>" onsubmit="spjAddDelMembers('sfmemberdel<?php echo $usergroup_id ?>', '<?php echo $url; ?>', '<?php echo $target; ?>', '<?php echo $smessage; ?>', '<?php echo $emessage; ?>', 0, 50, '#dmid<?php echo $usergroup_id; ?>');">
<?php
		echo sp_create_nonce('forum-adminform_memberdel');
?>
		<input type="hidden" name="usergroupid" value="<?php echo $usergroup_id; ?>" />
		<p><?php spa_etext('Select members to delete/move (use CONTROL for multiple users)') ?></p>
		<p><?php spa_etext('To move members, select a new usergroup') ?></p>
		<?php spa_display_usergroup_select() ?>
<?php
		$from = esc_js(spa_text('Current Members'));
		$to = esc_js(spa_text('Selected Members'));
        $action = 'delug';
    	include_once(SF_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php');
?>
		<div class="clearboth"></div>
<?php
        do_action('sph_usergroup_delete_member_panel');
?>
		<span><input type="submit" class="button-primary" id="sfmemberdel<?php echo $usergroup_id; ?>" name="sfmemberdel<?php echo $usergroup_id; ?>" value="<?php spa_etext('Delete/Move Members'); ?>" /> <span class="button sfhidden" id='onFinish'></span>
		<input type="button" class="button-primary spCancelForm" data-target="#members-<?php echo $usergroup_id; ?>" id="sfmemberdel<?php echo $usergroup_id; ?>" name="delmemberscancel<?php echo $usergroup_id; ?>" value="<?php spa_etext('Cancel'); ?>" /></span>
		<br />
		<div class="pbar" id="progressbar"></div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>