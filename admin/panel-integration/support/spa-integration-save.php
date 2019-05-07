<?php
/*
Simple:Press
Admin integration Update Support Functions
$LastChangedDate: 2018-10-15 21:45:40 -0500 (Mon, 15 Oct 2018) $
$Rev: 15753 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_integration_page_data() {
    check_admin_referer('forum-adminform_integration', 'forum-adminform_integration');

	$mess = '';
	$slugid = SP()->filters->integer($_POST['slug']);
	if ($slugid == '' || $slugid == 0) {
		$setslug = '';
		$setpage = 0;
	} else {
		$setpage = $slugid;
		$page = SP()->DB->table(SPWPPOSTS, "ID=$slugid", 'row');
		$setslug = $page->post_name;

		if ($page->post_parent) {
			$parent = $page->post_parent;
			while ($parent) {
				$thispage = SP()->DB->table(SPWPPOSTS, "ID=$parent", 'row');
				$setslug = $thispage->post_name.'/'.$setslug;
				$parent = $thispage->post_parent;
			}
		}
	}

	SP()->options->update('sfpage', $setpage);
	SP()->options->update('sfslug', $setslug);

	spa_update_check_option('sfinloop');
	spa_update_check_option('sfmultiplecontent');
	spa_update_check_option('sfwpheadbypass');
	spa_update_check_option('sfwplistpages');
	spa_update_check_option('sfscriptfoot');
	spa_update_check_option('sfuseob');
	spa_update_check_option('spwptexturize');

	SP()->options->update('spheaderspace', SP()->filters->integer($_POST['spheaderspace']));

	if (!$setpage) {
		$mess.= SP()->primitives->admin_text('Page slug missing');
		$mess.= ' - '.SP()->primitives->admin_text('Unable to determine forum permalink without it');
	} else {
		$mess.= SP()->primitives->admin_text('Forum page and slug updated');
        SP()->spPermalinks->update_permalink(true);
	}

    do_action('sph_integration_save');

	return $mess;
}

function spa_save_integration_storage_data() {
	check_admin_referer('forum-adminform_storage', 'forum-adminform_storage');

	$mess = SP()->primitives->admin_text('Storage locations updated');

	$sfstorage = array();
	$sfstorage = SP()->options->get('sfconfig');
	if (!empty($_POST['plugins'])) $sfstorage['plugins'] = trim(SP()->saveFilters->title(trim($_POST['plugins'])), '/');
	if (!empty($_POST['themes'])) $sfstorage['themes'] = trim(SP()->saveFilters->title(trim($_POST['themes'])), '/');
	if (!empty($_POST['avatars'])) $sfstorage['avatars'] = trim(SP()->saveFilters->title(trim($_POST['avatars'])), '/');
	if (!empty($_POST['avatar-pool'])) $sfstorage['avatar-pool'] = trim(SP()->saveFilters->title(trim($_POST['avatar-pool'])), '/');
	if (!empty($_POST['smileys'])) $sfstorage['smileys'] = trim(SP()->saveFilters->title(trim($_POST['smileys'])), '/');
	if (!empty($_POST['ranks'])) $sfstorage['ranks'] = trim(SP()->saveFilters->title(trim($_POST['ranks'])), '/');
	if (!empty($_POST['image-uploads'])) $sfstorage['image-uploads'] = trim(SP()->saveFilters->title(trim($_POST['image-uploads'])), '/');
	if (!empty($_POST['media-uploads'])) $sfstorage['media-uploads'] = trim(SP()->saveFilters->title(trim($_POST['media-uploads'])), '/');
	if (!empty($_POST['file-uploads'])) $sfstorage['file-uploads'] = trim(SP()->saveFilters->title(trim($_POST['file-uploads'])), '/');
	if (!empty($_POST['custom-icons'])) $sfstorage['custom-icons'] = trim(SP()->saveFilters->title(trim($_POST['custom-icons'])), '/');
	if (!empty($_POST['language-sp'])) $sfstorage['language-sp'] = trim(SP()->saveFilters->title(trim($_POST['language-sp'])), '/');
	if (!empty($_POST['language-sp-plugins'])) $sfstorage['language-sp-plugins'] = trim(SP()->saveFilters->title(trim($_POST['language-sp-plugins'])), '/');
	if (!empty($_POST['language-sp-themes'])) $sfstorage['language-sp-themes'] = trim(SP()->saveFilters->title(trim($_POST['language-sp-themes'])), '/');
	if (!empty($_POST['cache'])) $sfstorage['cache'] = trim(SP()->saveFilters->title(trim($_POST['cache'])), '/');
	if (!empty($_POST['forum-images'])) $sfstorage['forum-images'] = trim(SP()->saveFilters->title(trim($_POST['forum-images'])), '/');
	if (!empty($_POST['iconsets'])) $sfstorage['iconsets'] = trim( SP()->saveFilters->title( trim( $_POST['iconsets'] ) ), '/' );

	SP()->options->update('sfconfig', $sfstorage);

    do_action('sph_integration_storage_save');

	return $mess;
}

function spa_save_integration_language_data() {
	check_admin_referer('forum-adminform_language', 'forum-adminform_language');
	$spLang = SP()->options->get('spLang');
	if (isset($_POST['spLang'])) $spLang['spLang'] = SP()->filters->str($_POST['spLang']);
	$spLang['spRTL'] = isset($_POST['spRTL']);
	SP()->options->update('spLang', $spLang);
	$mess = SP()->primitives->admin_text('Translation settings updated');
	return $mess;
}
