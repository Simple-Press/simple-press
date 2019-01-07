<?php
/**
 * Ajax Action handler - Common to admin and front
 * This file loads at core level - all page loads for admin and front
 *
 * $LastChangedDate: 2018-11-02 16:17:56 -0500 (Fri, 02 Nov 2018) $
 * $Rev: 15797 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Core AJAX actions and processing
add_action('wp_ajax_spAckPopup', 'sp_ajax_acknowledgements');
add_action('wp_ajax_nopriv_spAckPopup', 'sp_ajax_acknowledgements');
function sp_ajax_acknowledgements() {
	require_once SP_PLUGIN_DIR.'/forum/content/ajax/sp-ajax-acknowledge.php';
}

/**
 * This function is used to perform nonce checks on our ajax actions.
 * Runs for all Simple Press ajax actions processed through standard WP ajax handler.
 *
 * @since 6.0
 *
 * @param string	$ajaxTag	the nonce tag used for the ajax action
 *
 * @return bool		returns true if nonce check passes, false otherwise
 */
function sp_nonce($ajaxTag) {
	$check = check_ajax_referer($ajaxTag, false, false);
	if (!$check) {
		$m = '<div style="margin: 5px; border: 2px solid red; padding: 10px;">';
		$m .= '<p><img src="'.SPADMINIMAGES.'sp_Message.png" alt="" style="float:left; margin: -4px 10px 0 0;" />';
		$m .= '<b>'.SP()->primitives->front_text('Access denied - security check failed').'<br />';
		$m .= SP()->primitives->front_text('Unable to complete the request').'</b></p>';
		$m .= '<p><b>'.SP()->primitives->front_text('Please reload the page and retry the operation').'</b></p>';
		$m .= '</div>';

		# lets log an error
		$message = SP()->primitives->front_text('Nonce Security Alert').'<br />';
		$message .= $ajaxTag.': '.SP()->primitives->front_text('failed nonce check');
		if (!empty($_GET)) {
			$message .= '<table class="form-table" style="width:auto;">';
			foreach (array_map('sanitize_text_field', $_GET) as $key => $value) {
				$message .= "<tr><td class='sflabel'>$key</td><td class='sflabel'>$value</td></tr>";
			}
			$message .= '</table>';
		}
		SP()->error->errorWrite('security', $message);
		?>
		<script>
			(function(spj, $, undefined) {
				$(document).ready(function () {
					spj.dialogHtml('', '<?php echo($m); ?>', '<?php echo(SP()->primitives->front_text("Security Alert")); ?>', 0, 0, 'center', '');
				});
			}(window.spj = window.spj || {}, jQuery));
		</script>
		<?php
	}

	return $check;
}
