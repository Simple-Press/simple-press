<?php
/*
Simple:Press
Admin Plugin Help
$LastChangedDate: 2014-10-20 15:38:39 +0100 (Mon, 20 Oct 2014) $
$Rev: 12009 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('plugin-tip')) die();

if (!isset($_GET['file'])) die();

$file = SP()->filters->filename($_GET['file']);

# Formatting and Display of Help Panel
$helptext = wpautop(sp_retrieve_plugin_help($file), false);

echo '<div>';
echo '<fieldset>';
echo $helptext;
echo '</fieldset>';
echo '</div>';
die();

function sp_retrieve_plugin_help($file) {
	$file = SPPLUGINDIR.$file;
	if (file_exists($file) == false) {
		return SP()->primitives->admin_text('No help file can be located');
	}
	$theData='';
	$fh = fopen($file, 'r');
	do {
		$theData.= fgets($fh);
	} while (!feof($fh));

	fclose($fh);

	return $theData;
}
