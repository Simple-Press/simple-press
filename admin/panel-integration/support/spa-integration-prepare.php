<?php
/*
Simple:Press
Admin integration Update Global Options Support Functions
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_integration_page_data() {
	$sfoptions = array();
	$sfoptions['sfslug'] = SP()->options->get('sfslug');
	$sfoptions['sfpage'] = SP()->options->get('sfpage');
	$sfoptions['sfpermalink'] = SP()->options->get('sfpermalink');

	$sfoptions['sfuseob'] = SP()->options->get('sfuseob');
	$sfoptions['sfwplistpages'] = SP()->options->get('sfwplistpages');
	$sfoptions['sfscriptfoot'] = SP()->options->get('sfscriptfoot');

	$sfoptions['sfinloop'] = SP()->options->get('sfinloop');
	$sfoptions['sfmultiplecontent'] = SP()->options->get('sfmultiplecontent');
	$sfoptions['sfwpheadbypass'] = SP()->options->get('sfwpheadbypass');
	$sfoptions['spheaderspace'] = SP()->options->get('spheaderspace');
	$sfoptions['spwptexturize'] = SP()->options->get('spwptexturize');

	return $sfoptions;
}

function spa_get_storage_data() {
	$sfstorage = array();
	$sfstorage = SP()->options->get('sfconfig');
	return $sfstorage;
}
