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

include_once SP_PLUGIN_DIR.'/admin/library/spa-tab-support.php';

# do we show first install panel?
if (!empty($_GET['install'])) {
	spa_paint_open_tab(SP()->primitives->admin_text('What Happens Next'));

	spa_paint_open_panel();
	echo '<div class="helpAndFAQ">';
	spa_paint_open_fieldset(SP()->primitives->admin_text('Welcome to Simple:Press'), false, '', false);
	require 'spa-post-install-1.php';
	spa_paint_close_fieldset();
	echo '</div>';
	echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_panel();

	spa_paint_tab_right_cell();

	spa_paint_open_panel();
	echo '<div class="helpAndFAQ">';
	spa_paint_open_fieldset(SP()->primitives->admin_text('If you want to...'), false, '', false);
	require 'spa-post-install-2.php';
	spa_paint_close_fieldset();
	echo '</div>';
	echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_panel();

	echo '<div class="clearboth"></div>';

	spa_paint_spacer();
	//spa_paint_close_container();
	spa_paint_close_tab();

	echo '<div class="sfform-panel-spacer"></div>';
}

spa_paint_open_tab(SP()->primitives->admin_text('Help and Troubleshooting'));

spa_paint_open_panel();
echo '<div class="helpAndFAQ">';
spa_paint_open_fieldset(SP()->primitives->admin_text('Troubleshooting FAQs'), false);
require 'spa-faq-troubleshooting.php';
spa_paint_close_fieldset();
echo '</div>';
spa_paint_close_panel();

spa_paint_tab_right_cell();

spa_paint_open_panel();
echo '<div class="helpAndFAQ">';
spa_paint_open_fieldset(SP()->primitives->admin_text('How To Articles'), false);
require 'spa-codex-links.php';
spa_paint_close_fieldset();
echo '</div>';
spa_paint_close_panel();

spa_paint_spacer();
//spa_paint_close_container();
spa_paint_close_tab();

echo '<div class="sfform-panel-spacer"></div>';

spa_paint_open_tab(SP()->primitives->admin_text('Themes and Plugins'));

spa_paint_open_panel();
echo '<div class="helpAndFAQ">';
spa_paint_open_fieldset(SP()->primitives->admin_text('Available Themes'), false);
require 'spa-theme-links.php';
spa_paint_close_fieldset();
echo '</div>';
spa_paint_close_panel();

spa_paint_tab_right_cell();

spa_paint_open_panel();
echo '<div class="helpAndFAQ">';
spa_paint_open_fieldset(SP()->primitives->admin_text('Available Plugins'), false);
require 'spa-plugin-links.php';
spa_paint_close_fieldset();
echo '</div>';
spa_paint_close_panel();

spa_paint_spacer();
//spa_paint_close_container();
spa_paint_close_tab();

echo '<div class="sfform-panel-spacer"></div>';

spa_paint_open_tab(SP()->primitives->admin_text('Support and Customization'));

spa_paint_open_panel();
echo '<div class="helpAndFAQ">';
spa_paint_open_fieldset(SP()->primitives->admin_text('Premium Support'), false);
require 'spa-support-links.php';
spa_paint_close_fieldset();
echo '</div>';
spa_paint_close_panel();

spa_paint_tab_right_cell();

spa_paint_open_panel();
echo '<div class="helpAndFAQ">';
spa_paint_open_fieldset(SP()->primitives->admin_text('Customization Service'), false);
require 'spa-custom-links.php';
spa_paint_close_fieldset();
echo '</div>';
spa_paint_close_panel();

spa_paint_spacer();
//spa_paint_close_container();
spa_paint_close_tab();

die();
