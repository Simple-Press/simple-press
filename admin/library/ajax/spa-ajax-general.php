<?php
/*
Simple:Press
Admin General Ajax file
$LastChangedDate: 2016-06-25 08:14:16 -0500 (Sat, 25 Jun 2016) $
$Rev: 14331 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (isset($_GET['targetaction']) && $_GET['targetaction'] == 'news') {
	if (!sp_nonce('remove-news')) die();
	$news = sp_get_sfmeta('news', 'news');
	if (!empty($news)) {
		$news[0]['meta_value']['show'] = 0;
		sp_update_sfmeta('news', 'news', $news[0]['meta_value'], $news[0]['meta_id'], 0);
	}
}

die();

?>