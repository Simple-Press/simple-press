<?php
/*
Simple:Press
Admin Toolbox Uninstall Form
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_toolbox_log_form() {
	$sflog = spa_get_log_data();

    #== log Tab ==========================================================

	spa_paint_open_tab(SP()->primitives->admin_text('Install Log'), true);
			if (!$sflog) {
				SP()->primitives->admin_etext("There are no Install Log Entries");
				return;
			}

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Install Log'), false);
				echo "<table class='wp-list-table widefat'><tr>";
				echo '<th>'.esc_html(SP()->primitives->admin_text('Version')).'</th>';
				echo "<th class='logDetail'>"."</th>";
				echo '<th>'.esc_html(SP()->primitives->admin_text('Build')).'</th>';
				echo "<th class='logRelease'>".esc_html(SP()->primitives->admin_text('Release'))."</th>";
				echo '<th>'.esc_html(SP()->primitives->admin_text('Installed')).'</th>';
				echo "<th class='logBy'>".esc_html(SP()->primitives->admin_text('By'))."</th>";
				echo '</tr>';

				foreach ($sflog as $log) {
					$idVer = 'version'.str_replace('.', '', $log['version']);
					$idQVer = str_replace('.', '-', $log['version']);

					echo '<tr>';
					echo "<td class='sflabel'>".esc_html($log['version'])."</td>";
				    $site = wp_nonce_url(SPAJAXURL.'install-log&amp;log='.esc_html($idQVer), 'install-log');
					$gif = SPCOMMONIMAGES.'working.gif';
					echo '<td class="logDetail"><input type="button" class="logDetail button spLoadAjax" value="'.esc_attr(SP()->primitives->admin_text('Details')).'" data-url="'.esc_attr($site).'" data-target="'.esc_attr($idVer).'" data-img="'.esc_attr($gif).'" /></td>';

					echo "<td class='sflabel'>".esc_html($log['build'])."</td>";
					echo "<td class='sflabel logRelease'>".esc_html($log['release_type'])."</td>";
					echo "<td class='sflabel'>".esc_html(SP()->dateTime->format_date('d', $log['install_date']))."</td>";
					echo "<td class='sflabel logBy'>".esc_html(SP()->displayFilters->name($log['display_name']))."</td>";
					echo '</tr>';
					echo "<tr><td style='display:none;' class='sflabel' id='".esc_attr($idVer)."' colspan='6'></td></tr>";
				}
				echo '</table>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_install_panel');
		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}
