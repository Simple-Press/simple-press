<?php
/*
Simple:Press
Ajax Action handler - Common to front/back end
$LastChangedDate: 2016-11-09 03:28:03 -0600 (Wed, 09 Nov 2016) $
$Rev: 14717 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# SITE - This file loads at core level - all page loads
# handles all AJAX calls via WP Ajax - or -  index.php and WP parse hook
#
# ==========================================================================================

# Our AJAX actions and processing

function sp_ajax_acknowledgements() {
	include SF_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-acknowledge.php';
}
add_action('wp_ajax_spAckPopup', 'sp_ajax_acknowledgements');
add_action('wp_ajax_nopriv_spAckPopup', 'sp_ajax_acknowledgements');

# ------------------------------
# verify nonce (SPAJAXURL calls)
# ------------------------------
function sp_nonce($ajaxTag) {
	$check = check_ajax_referer($ajaxTag, false, false);
	if (!$check) {
		$m = '<div style="margin: 5px; border: 2px solid red; padding: 10px;">';
		$m.= '<p><img src="'.SFADMINIMAGES.'sp_Message.png" alt="" style="float:left; margin: -4px 10px 0 0;" />';
		$m.= '<b>'.sp_text('Access denied - security check failed').'<br />';
		$m.= sp_text('Unable to complete the request').'</b></p>';
		$m.= '<p><b>'.sp_text('Please reload the page and retry the operation').'</b></p>';
		$m.= '</div>';

        # lets log an error
        $message = sp_text('Nonce Security Alert').'<br />';
        $message.= $ajaxTag.': '.sp_text('failed nonce check');
        if (!empty($_GET)) {
			$message.= '<table class="form-table" style="width:auto;">';
        	foreach($_GET as $key=>$value) {
				$message.= "<tr><td class='sflabel'>$key</td><td class='sflabel'>$value</td></tr>";
        	}
			$message.= '</table>';
        }
        sp_write_error('security', $message);
?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				spjDialogHtml('', '<?php echo($m); ?>', '<?php echo(sp_text("Security Alert")); ?>', 0, 0, 'center', '');
			});
		</script>
<?php
	}
	return $check;
}

?>