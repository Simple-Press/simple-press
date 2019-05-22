<?php
/*
Simple:Press
Admin User Groups Delete Member Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_delete_members_form($usergroup_id) {
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			$('#sfmemberdel<?php echo $usergroup_id; ?>').ajaxForm({
				target: '#sfmsgspot',
			});
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
	spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'usergroups-loader&amp;saveform=delmembers', 'usergroups-loader');

	$url = wp_nonce_url(SPAJAXURL.'memberships&amp;targetaction=del', 'memberships');
	$target = 'sfmsgspot';
	$smessage = esc_js(SP()->primitives->admin_text('Please Wait - Processing'));
	$emessage = esc_js(SP()->primitives->admin_text('Users Deleted/Moved'));
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfmemberdel<?php echo $usergroup_id; ?>" name="sfmemberdel<?php echo $usergroup_id ?>" onsubmit="spj.addDelMembers('sfmemberdel<?php echo $usergroup_id ?>', '<?php echo $url; ?>', '<?php echo $target; ?>', '<?php echo $smessage; ?>', '<?php echo $emessage; ?>', 0, 50, '#dmid<?php echo $usergroup_id; ?>');">
<?php
		echo sp_create_nonce('forum-adminform_memberdel');
?>
		<input type="hidden" name="usergroupid" value="<?php echo $usergroup_id; ?>" />
		<p><?php SP()->primitives->admin_etext('Select members to delete/move (use CONTROL for multiple users)') ?></p>
		<p><?php SP()->primitives->admin_etext('To move members, select a new usergroup') ?></p>
		<?php spa_display_usergroup_select() ?>
<?php
		$from = esc_js(SP()->primitives->admin_text('Current Members'));
		$to = esc_js(SP()->primitives->admin_text('Selected Members'));
        $action = 'delug';
        require_once SP_PLUGIN_DIR.'/admin/library/ajax/spa-ajax-multiselect.php';
?>
		<div class="clearboth"></div>
<?php
        do_action('sph_usergroup_delete_member_panel');
?>
		<span><input type="submit" class="sf-button-primary" id="sfmemberdel<?php echo $usergroup_id; ?>" name="sfmemberdel<?php echo $usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Delete/Move Members'); ?>" /> <span class="sf-button sfhidden" id='onFinish'></span>
		<input type="button" class="sf-button-primary spCancelForm" data-target="#members-<?php echo $usergroup_id; ?>" id="sfmemberdel<?php echo $usergroup_id; ?>" name="delmemberscancel<?php echo $usergroup_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" /></span>
		<br />
		<div class="pbar" id="progressbar"></div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
