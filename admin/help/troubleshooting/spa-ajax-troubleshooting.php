<?php
/*
Simple:Press
Help and Troubleshooting
$LastChangedDate: 2015-08-04 11:06:48 +0100 (Tue, 04 Aug 2015) $
$Rev: 13244 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('troubleshooting')) die();

include_once SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

# do we show first install panel?
if(!empty($_GET['install'])) {
	spa_paint_open_tab(spa_text('What Happens Next'));

		spa_paint_open_panel();
			echo '<div class="helpAndFAQ">';
				spa_paint_open_fieldset(spa_text('Welcome to Simple:Press'), false, '', false);
					include('spa-post-install-1.php');
				spa_paint_close_fieldset();
			echo '</div>';
			echo '<div class="sfform-panel-spacer"></div>';
		spa_paint_close_panel();

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			echo '<div class="helpAndFAQ">';
				spa_paint_open_fieldset(spa_text('If you want to...'), false, '', false);
					include('spa-post-install-2.php');
				spa_paint_close_fieldset();
			echo '</div>';
			echo '<div class="sfform-panel-spacer"></div>';
		spa_paint_close_panel();

	echo '<div class="clearboth"></div>';

	spa_paint_spacer();
	spa_paint_close_container();
	spa_paint_close_tab();

	echo '<div class="sfform-panel-spacer"></div>';
}

spa_paint_open_tab(spa_text('Help and Troubleshooting'));

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Troubleshooting FAQs'), false);
				include('spa-faq-troubleshooting.php');
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

	spa_paint_tab_right_cell();

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('How To Articles'), false);
				include('spa-codex-links.php');
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

spa_paint_spacer();
spa_paint_close_container();
spa_paint_close_tab();

echo '<div class="sfform-panel-spacer"></div>';

spa_paint_open_tab(spa_text('Themes and Plugins'));

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Available Themes'), false);
				include('spa-theme-links.php');
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

	spa_paint_tab_right_cell();

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Available Plugins'), false);
				include('spa-plugin-links.php');
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

spa_paint_spacer();
spa_paint_close_container();
spa_paint_close_tab();

echo '<div class="sfform-panel-spacer"></div>';

spa_paint_open_tab(spa_text('Support and Customisation'));

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Premium Support'), false);
				include('spa-support-links.php');
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

	spa_paint_tab_right_cell();

	spa_paint_open_panel();
		echo '<div class="helpAndFAQ">';
			spa_paint_open_fieldset(spa_text('Customisation Service'), false);
				include('spa-custom-links.php');
			spa_paint_close_fieldset();
		echo '</div>';
	spa_paint_close_panel();

spa_paint_spacer();
spa_paint_close_container();
spa_paint_close_tab();

die();
?>