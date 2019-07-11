<?php
/*
Simple:Press
Admin Toolbox Error Log Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_errorlog_form() {
?>
<script>
   	spj.loadAjaxForm('sfclearlog', 'sfreloadel');
</script>
<?php
	$sflog = spa_get_errorlog_data();

	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('Error Log'), true);
		spa_paint_open_fieldset(SP()->primitives->admin_text('Error Log'), false);
			echo '<p>'.SP()->primitives->admin_text('Error Logging can be disabled in the Global Options panel').'<br /></p>';

			echo "<table class='sfhelptext'><tr>";

			echo "<td class='spaErrError spaErrCell'><b>".SP()->primitives->admin_text('Error').'</b></td>';
			echo "<td class='spaErrWarning spaErrCell'><b>".SP()->primitives->admin_text('Warning').'</b></td>';
			echo "<td class='spaErrNotice spaErrCell'><b>".SP()->primitives->admin_text('Notice').'</b></td>';
			echo "<td class='spaErrStrict spaErrCell'><b>".SP()->primitives->admin_text('Strict').'</b></td>';
			echo "<td class='spaSecNotice spaErrCell'><b>".SP()->primitives->admin_text('Security').'</b></td>';
			echo "</tr><tr>";
			echo "<td class='spaErrCellDesc'>".SP()->primitives->admin_text('Errors should be reported to Simple:Press support as they may effect the proper behaviour of your forum.').'</td>';
			echo "<td class='spaErrCellDesc'>".SP()->primitives->admin_text('Warnings suggest a code conflict of some type that should be investigated but which will not stop Simple:Press execution.').'</td>';
			echo "<td class='spaErrCellDesc'>".SP()->primitives->admin_text('Notices are generally non-important and have no effect on Simple:Press execution. We make every effort to clear these when we are informed of them.').'</td>';
			echo "<td class='spaErrCellDesc'>".SP()->primitives->admin_text('If you receive any Strict entries they are non-urgent but please inform Simple:Press support so we can deal with them.').'</td>';
			echo "<td class='spaErrCellDesc'>".SP()->primitives->admin_text('Security notices show up from nonce check failures. Generally non-urgent, but could be of use to Simple:Press support staff if you get lots of them.').'</td>';

			echo "</tr></table><p>&nbsp;</p>";

			if (!$sflog) {
				echo '<p>'.SP()->primitives->admin_text('There are no Error Log Entries').'</p>';
			} else {

				echo "<table class='wp-list-table widefat'>";

				foreach ($sflog as $log)
				{
					echo '<tr>';
					echo "<td class='sferror ".$log['error_cat']."'>".SP()->dateTime->format_date('d', $log['error_date']).' '.SP()->dateTime->format_date('t', $log['error_date']).' | '.$log['error_cat'].' | '.$log['error_count'].' | '.$log['error_type'].'<hr />';
					echo $log['error_text'].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
		spa_paint_close_fieldset();
		do_action('sph_toolbox_error_panel');
		spa_paint_close_container();

    $ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=sfclearlog', 'toolbox-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfclearlog" name="sfclearlog">
	<?php echo sp_create_nonce('forum-adminform_clearlog'); ?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Empty Error Log'); ?>" />
	<input type="button" class="sf-button-primary spReloadForm" id="reloadit" name="reloadit" value="<?php SP()->primitives->admin_etext('Reload Error Log'); ?>" data-target="#sfreloadel" />
	</div>
	</form>
<?php
	spa_paint_close_tab();
}
