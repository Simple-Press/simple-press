<?php

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('adminsearch')) {
    die();
}

include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

spa_paint_open_tab(SP()->primitives->admin_text('Forum Admin Task Glossary'));
	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(SP()->primitives->admin_text('Task Glossary'), false);
				echo '<p><b>'.SP()->primitives->front_text("Select the item to display tasks for from the list below").'</b></p>';

				$sql = "SELECT * FROM ".SPADMINKEYWORDS." ORDER BY keyword";
				$keywords = SP()->DB->select($sql);
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

	spa_paint_tab_right_cell();

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(SP()->primitives->admin_text('Associated Tasks'), false);
				echo "<div id='codex' class='codex'>".SP()->primitives->admin_text('No current selection from the Task Glossary')."</div>";
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();
spa_paint_close_tab();
die();
