<?php
/*
Simple:Press
DESC: API Notification Routines
$LastChangedDate: 2015-08-17 01:02:43 +0100 (Mon, 17 Aug 2015) $
$Rev: 13312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#
# ==================================================================

# = SUCCESS/FAILURE/WAIT NOTIFICATIONS==============

# ------------------------------------------------------------------
# sp_notify()
#
# Version: 5.0
# Creates a notification message
#	$type: 		0 = Success : 1 = Failure : 2 = Wait
#	$text:		Message text
#	$now:		Display immediately - don't wait for next page load
#
#	Uses the SPSUCCESS, SPFAILURE and SPWAIT constants
# ------------------------------------------------------------------
function sp_notify($type, $text, $now=false) {
	global $spThisUser;

	# test for extreme condition
	if (empty($spThisUser->trackid)) return;
	$data = serialize(array($type, $text));
	if ($now) {
		$spThisUser->notification = $data;
		sp_render_queued_notification(false);
	} else {
		spdb_query('UPDATE '.SFTRACK." SET notification='$data' WHERE id=$spThisUser->trackid");
	}
}

# ------------------------------------------------------------------
# sp_render_queued_notification()
#
# Version: 5.0
# Retrieves and renders a notification message
#	$doDelete-	delete in track
# 	0 = Success, 1 = Failure, 2 = Wait
# ------------------------------------------------------------------
function sp_render_queued_notification($doDelete=true) {
	global $spStatus, $spThisUser, $spIsForumAdmin;

	if (isset($spThisUser) && $spStatus == 'ok'  && $spIsForumAdmin == false) {
		if (!empty($spThisUser->notification)) {
			$notification = $spThisUser->notification;
		} else {
			$notification = spdb_table(SFTRACK, 'id='.$spThisUser->trackid, 'notification');
		}
		if ($notification) {
			# Remove it from sftrack?
			if($doDelete) {
				spdb_query('UPDATE '.SFTRACK." SET notification='' WHERE id=$spThisUser->trackid");
			}
			# And pass it through to the js for display
			$notification = unserialize($notification);
			apply_filters('sph_queued_notification', $notification[1]);
			do_action('sph_message', $notification[0], esc_js($notification[1]));
		}
	}
}

# ------------------------------------------------------------------
# inline functions to dislay failure/success/wait messages
# ------------------------------------------------------------------
add_action('sph_message', 'sp_display_success_failure', 1, 2);
# ------------------------------------------------------------------
function sp_display_success_failure($type, $msg) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjDisplayNotification(<?php echo($type); ?>, '<?php echo($msg); ?>');
});
</script>
<?php
}

# ==================================================================









# = USER NOTICES HANDLING =====================
# Version: 5.0
function sp_add_notice($nData) {
    # see if we already have an notice here
    $notice = spdb_table(SFNOTICES, "user_id={$nData['user_id']} AND post_id={$nData['post_id']} AND message='{$nData['message']}'", 'notice_id');
    if (!empty($notice)) return;

    # create the new notice
	$spdb = new spdbComplex;
		$spdb->table	= SFNOTICES;
		$spdb->fields	= array('user_id', 'guest_email', 'post_id', 'link', 'link_text', 'message', 'expires');
		$spdb->data		= array($nData['user_id'], $nData['guest_email'], $nData['post_id'], $nData['link'], sp_filter_title_save($nData['link_text']), sp_filter_title_save($nData['message']), $nData['expires']);
	$spdb = apply_filters('sph_new_notice_data', $spdb);
	$spdb->insert();
}

# Version: 5.0
function sp_delete_notice($col, $data) {
	$sql = 'DELETE FROM '.SFNOTICES.' WHERE ';
	if (is_numeric($data)) {
		$sql.= "$col = $data";
	} else {
		$sql.= "$col = '$data'";
	}
	spdb_query($sql);
}






?>