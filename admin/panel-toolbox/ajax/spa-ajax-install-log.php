<?php
/*
Simple:Press Admin
Ajax form loader - Toolbox Install Log Extra Section Details
$LastChangedDate: 2012-11-18 18:04:10 +0000 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('install-log')) die();

if (SP()->core->status != 'ok') {
	echo SP()->core->status;
	die();
}

$log = 0;
if (isset($_GET['log'])) $log = SP()->filters->str($_GET['log']);
if ($log > 0) {
	$log = str_replace('-', '.', $log);
	$details = SP()->DB->table(SPLOGMETA, "version='$log'", '', 'id DESC');
	if ($details) {
		echo '<p>'.SP()->primitives->admin_text('Version').': '.$log.'</p>';
		foreach ($details as $d) {
			$section = unserialize($d->log_data);
			echo '<p>'.SP()->primitives->admin_text('Section').': '.$section['section'].'<br />';
			echo SP()->primitives->admin_text('Status').':  '.$section['status'].'<br />';
			echo SP()->primitives->admin_text('Response').': '.$section['response'].'<br /></p>';
		}
	} else {
		echo SP()->primitives->admin_text('Not Recorded');
	}
}

die();
