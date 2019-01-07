<?php
/*
Simple:Press
Help and Troubleshooting
$LastChangedDate: 2015-08-04 11:06:48 +0100 (Tue, 04 Aug 2015) $
$Rev: 13244 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('adminsearch')) die();

include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

spa_paint_open_tab(spa_text('Forum Admin Task Glossary'));
	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Task Glossary'), false);
				echo '<p><b>'.sp_text("Select the item to display tasks for from the list below").'</b></p>';

				$sql = "SELECT * FROM ".SFADMINKEYWORDS." ORDER BY keyword";
				$keywords = spdb_select('set', $sql);
				if ($keywords) {
					echo '<ul class="key-word-list">';
					foreach ($keywords as $keyword) {
						$ajaxPostURL = wp_nonce_url(SPAJAXURL.'adminkeywords&targetaction=gettasks&keyword='.urlencode($keyword->keyword).'&id='.$keyword->id, 'adminkeywords');
						echo "<li class='key-word' data-url='$ajaxPostURL'>";
						echo $keyword->keyword.'</li>';
					}
					echo '</ul>';
				}
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

	spa_paint_spacer();

	spa_paint_tab_right_cell();

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Associated Tasks'), false);
				echo "<div id='codex' class='codex'>".spa_text('No current selection from the Task Glossary')."</div>";
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();


spa_paint_spacer();
spa_paint_close_container();
spa_paint_close_tab();

die();

?>