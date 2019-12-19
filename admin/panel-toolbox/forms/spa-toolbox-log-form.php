<?php
/*
Simple:Press
Admin Toolbox Uninstall Form
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_log_form() {
	$sflog = spa_get_log_data();

    #== log Tab ==========================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Toolbox')." - ".*/SP()->primitives->admin_text('Install Log'), true);
			if (!$sflog) {
				SP()->primitives->admin_etext("There are no Install Log Entries");
				return;
			}

		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Install Log'), false);
				echo "<table class='wp-list-table widefat'><tr>";
				echo '<th>'.SP()->primitives->admin_text('Version').'</th>';
				echo "<th class='logDetail'>"."</th>";
				echo '<th>'.SP()->primitives->admin_text('Build').'</th>';
				echo "<th class='logRelease'>".SP()->primitives->admin_text('Release')."</th>";
				echo '<th>'.SP()->primitives->admin_text('Installed').'</th>';
				echo "<th class='logBy'>".SP()->primitives->admin_text('By')."</th>";
				echo '</tr>';

				foreach ($sflog as $log) {
					$idVer = 'version'.str_replace('.', '', $log['version']);
					$idQVer = str_replace('.', '-', $log['version']);

					echo '<tr>';
					echo "<td class='sflabel'>".$log['version']."</td>";
				    $site = wp_nonce_url(SPAJAXURL.'install-log&amp;log='.$idQVer, 'install-log');
					$gif = SPCOMMONIMAGES.'working.gif';
					echo '<td class="logDetail"><input type="button" class="logDetail button spLoadAjax" value="'.SP()->primitives->admin_text('Details').'" data-url="'.$site.'" data-target="'.$idVer.'" data-img="'.$gif.'" /></td>';

					echo "<td class='sflabel'>".$log['build']."</td>";
					echo "<td class='sflabel logRelease'>".$log['release_type']."</td>";
					echo "<td class='sflabel'>".SP()->dateTime->format_date('d', $log['install_date'])."</td>";
					echo "<td class='sflabel logBy'>".SP()->displayFilters->name($log['display_name'])."</td>";
					echo '</tr>';
					echo "<tr><td style='display:none;' class='sflabel' id='".$idVer."' colspan='6'></td></tr>";
				}
				echo '</table>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_install_panel');
		spa_paint_close_container();
		echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_tab();
}
