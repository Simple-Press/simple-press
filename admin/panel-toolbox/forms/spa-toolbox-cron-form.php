<?php
/*
Simple:Press
Admin Toolbox Cron Inspector Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_cron_form() {
    $ajaxURL = wp_nonce_url(SPAJAXURL.'toolbox-loader&amp;saveform=cron', 'toolbox-loader');
?>
<script>
   	spj.loadAjaxForm('sfcronform', 'sfcron');
</script>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfcronform" name="sfcronform">
	<?php echo sp_create_nonce('forum-adminform_cron'); ?>
<?php
   	$cronData = spa_get_cron_data();

	spa_paint_options_init();
	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('CRON Inspector'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('CRON Schedules'), false);
?>
                <table class="widefat fixed striped sf-table-mobile">
                    <thead>
                        <tr>
                            <th><?php SP()->primitives->admin_etext('Name'); ?></th>
                            <th><?php SP()->primitives->admin_etext('Description'); ?></th>
                            <th><?php SP()->primitives->admin_etext('Interval'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                    foreach ($cronData->schedules as $name => $schedule) {
?>
                        <tr class='_spMobileTableData'>
                            <td data-label='<?php SP()->primitives->admin_etext('Name'); ?>'><?php echo $name; ?></td>
                            <td data-label='<?php SP()->primitives->admin_etext('Description'); ?>'><?php echo $schedule['display']; ?></td>
                            <td data-label='<?php SP()->primitives->admin_etext('Interval'); ?>'><?php echo $schedule['interval']; ?></td>
                        </tr>
<?php
                    }
?>
                    </tbody>
                </table>
<?php
			spa_paint_close_fieldset();
                spa_paint_close_panel();
        spa_paint_close_panel();
        spa_paint_close_tab();
                        
                        spa_paint_open_nohead_tab(true);

			spa_paint_open_fieldset(SP()->primitives->admin_text('Active CRON'), false);
 ?>
                <table class="widefat fixed sf-table-mobile">
                    <thead>
                        <tr>
                            <th><?php SP()->primitives->admin_etext('Next Run (date)'); ?></th>
                            <th><?php SP()->primitives->admin_etext('Next Run (timestamp)'); ?></th>
                            <th><?php SP()->primitives->admin_etext('Schedule'); ?></th>
                            <th><?php SP()->primitives->admin_etext('Hook'); ?></th>
                            <th><?php SP()->primitives->admin_etext('Arguments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                    foreach ($cronData->cron as $time => $cron) {
                        foreach ($cron as $hook => $items) {
                            foreach ($items as $item) {
?>
                                <tr class='_spMobileTableData'>
                                    <td data-label='<?php SP()->primitives->admin_etext('Next Run (date)'); ?>'><?php echo $item['date']; ?></td>
                                    <td data-label='<?php SP()->primitives->admin_etext('Next Run (timestamp)'); ?>'><?php echo $time; ?></td>
                                    <td data-label='<?php SP()->primitives->admin_etext('Schedule'); ?>'>
<?php
                                        if ($item['schedule']) {
        								    echo $cronData->schedules[$item['schedule']]['display'];
                                        } else {
        								    SP()->primitives->admin_etext('One Time');
        								}
?>
                                    </td>
                                    <td data-label='<?php SP()->primitives->admin_etext('Hook'); ?>'>
<?php
                                        $sph = strncmp('sph_', $hook, 4 );
                                        if ($sph === 0) echo '<b>';
                                        echo $hook;
                                        if ($sph === 0) echo '</b>';
?>
                                    </td>
                                    <td data-label='<?php SP()->primitives->admin_etext('Arguments'); ?>'>
<?php
                                        if (count($item['args']) > 0) {
        									foreach ($item['args'] as $arg => $value) {
        										echo $arg.':'.$value.'<br />';
                                            }
                                        } else {
                                            echo '&nbsp;';
                                        }
?>
                                    </td>
                                </tr>
<?php
                            }
                        }
                    }
?>
                    </tbody>
                </table>
<?php
  			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_top_cron_panel');
		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();

	echo '<div class="sfform-panel-spacer"></div>';

        //spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox').' - '.*/SP()->primitives->admin_text('CRON Update'), true);
	spa_paint_open_nohead_tab();
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Add CRON'), true, 'cron-add');
				spa_paint_input(SP()->primitives->admin_text('Next Run Timestamp'), 'add-timestamp', '');
				spa_paint_input(SP()->primitives->admin_text('Interval'), 'add-interval', '');
				spa_paint_input(SP()->primitives->admin_text('Hook'), 'add-hook', '');
				spa_paint_input(SP()->primitives->admin_text('Arguments'), 'add-args', '');
  			spa_paint_close_fieldset();

			spa_paint_open_fieldset(SP()->primitives->admin_text('Run CRON'), true, 'cron-run');
				spa_paint_input(SP()->primitives->admin_text('Hook to run'), 'run-hook', '');
  			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_left_cron_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Delete CRON'), true, 'cron-delete');
				spa_paint_input(SP()->primitives->admin_text('Next Run Timestamp'), 'del-timestamp', '');
				spa_paint_input(SP()->primitives->admin_text('Hook'), 'del-hook', '');
				spa_paint_input(SP()->primitives->admin_text('Arguments'), 'del-args', '');
  			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_right_cron_panel');
		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update CRON'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
