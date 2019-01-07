<?php
/*
Simple:Press
Admin integration Update Global Options Support Functions
$LastChangedDate: 2015-01-19 05:45:18 -0600 (Mon, 19 Jan 2015) $
$Rev: 12369 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_integration_page_data() {
	global $wp_rewrite;

	$sfoptions = array();
	$sfoptions['sfslug'] = sp_get_option('sfslug');
	$sfoptions['sfpage'] = sp_get_option('sfpage');
	$sfoptions['sfpermalink'] = sp_get_option('sfpermalink');

	$sfoptions['sfuseob'] = sp_get_option('sfuseob');
	$sfoptions['sfwplistpages'] = sp_get_option('sfwplistpages');
	$sfoptions['sfscriptfoot'] = sp_get_option('sfscriptfoot');

	$sfoptions['sfinloop'] = sp_get_option('sfinloop');
	$sfoptions['sfmultiplecontent'] = sp_get_option('sfmultiplecontent');
	$sfoptions['sfwpheadbypass'] = sp_get_option('sfwpheadbypass');
	$sfoptions['spheaderspace'] = sp_get_option('spheaderspace');
	$sfoptions['spwptexturize'] = sp_get_option('spwptexturize');

	return $sfoptions;
}

function spa_get_storage_data() {
	$sfstorage = array();
	$sfstorage = sp_get_option('sfconfig');
	return $sfstorage;
}

?>