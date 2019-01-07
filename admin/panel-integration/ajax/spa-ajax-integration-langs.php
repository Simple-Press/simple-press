<?php
/*
Simple:Press Admin
Ajax call for language downloads
$LastChangedDate: 2014-06-21 04:47:00 +0100 (Sat, 21 Jun 2014) $
$Rev: 11582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('integration-langs')) die();

# ----------------------------------
# Check Whether User Can Manage Integration
if (!SP()->auths->current_user_can('SPF Manage Integration')) die();

if (isset($_GET['item'])) {
	$item = $_GET['item'];
	spa_download_language_file($item);
	die();
}

function spa_download_language_file($item) {
	global $locale;

	$locale = get_locale();

	$langCode = $_GET['langcode'];
	$homeName = $_GET['textdom'];
	if(isset($_GET['name'])) $itemName = $_GET['name'];

	if($item == 'corefront' || $item == 'coreadmin') {
		$url = 'http://glotpress.simple-press.com/glotpress/projects/simple-press-core/version-'.$_GET['version'].'/'.$homeName.'/'.$langCode.'/default/export-translations?format=mo';
		$home = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp'].'/'.$homeName.'-'.$locale.'.mo';
	}

	if($item == 'theme') {
		$url = 'http://glotpress.simple-press.com/glotpress/projects/simple-press-themes/'.$itemName.'/'.$homeName.'/'.$langCode.'/default/export-translations?format=mo';
		$home = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp-themes'].'/'.$homeName.'-'.$locale.'.mo';
	}

	if($item == 'plugin') {
		$url = 'http://glotpress.simple-press.com/glotpress/projects/simple-press-plugins/'.$itemName.'/'.$homeName.'/'.$langCode.'/default/export-translations?format=mo';
		$home = SP_STORE_DIR.'/'.SP()->plugin->storage['language-sp-plugins'].'/'.$homeName.'-'.$locale.'.mo';
	}

	if(isset($_GET['remove'])) {
		$status = unlink($home);
		echo '<img src="'.SPADMINIMAGES.'sp_No.png" title="'.SP()->primitives->admin_text('Translation file removed').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.SP()->primitives->admin_text('Translation file removed');
		die();
	} else {
		$fData = file_get_contents($url);
	}

	if($fData == false) {
		$status=false;
	} else {
		$status = file_put_contents($home, $fData);
	}

	if($status) {
		echo '<img src="'.SPADMINIMAGES.'sp_Yes.png" title="'.SP()->primitives->admin_text('Translation file installed').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.SP()->primitives->admin_text('Translation file installed');
	} else {
		echo '<img src="'.SPADMINIMAGES.'sp_No.png" title="'.SP()->primitives->admin_text('Translation install failed').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.SP()->primitives->admin_text('Install failed - or there is no available translation');
	}
}

die();
