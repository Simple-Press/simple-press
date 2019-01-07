<?php
/*
Simple:Press
Component Specials
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ajax_support();

if (!sp_nonce('components')) die();

# Check Whether User Can Manage Components
if (!sp_current_user_can('SPF Manage Components')) die();

global $spPaths;

$action = $_GET['targetaction'];

if ($action == 'del_rank') {
	$key = sp_esc_int($_GET['key']);

	# remove the forum rank
	$sql = 'DELETE FROM '.SFMETA." WHERE meta_type='forum_rank' AND meta_id='$key'";
	spdb_query($sql);
}

if ($action == 'del_specialrank') {
	$key = sp_esc_int($_GET['key']);
	$specialRank = sp_get_sfmeta('special_rank', false, $key);

    # remove members rank first
	spdb_query('DELETE FROM '.SFSPECIALRANKS.' WHERE special_rank="'.$specialRank[0]['meta_key'].'"');

	# remove the forum rank
	$sql = 'DELETE FROM '.SFMETA." WHERE meta_type='special_rank' AND meta_id='$key'";
	spdb_query($sql);
}

if ($action == 'show') {
    $key = sp_esc_int($_GET['key']);
    $specialRank = sp_get_sfmeta('special_rank', false, $key);

	$users = spdb_select('col', 'SELECT display_name
						  FROM '.SFSPECIALRANKS.'
						  JOIN '.SFMEMBERS.' ON '.SFSPECIALRANKS.'.user_id = '.SFMEMBERS.'.user_id
						  WHERE special_rank = "'.$specialRank[0]['meta_key'].'"
						  ORDER BY display_name');

    echo '<fieldset class="sfsubfieldset">';
    echo '<legend>'.spa_text('Special Rank Members').'</legend>';
    if ($users) {
    	echo '<ul class="memberlist">';
    	for ($x = 0; $x < count($users); $x++) {
    		echo '<li>'.sp_filter_name_display($users[$x]).'</li>';
    	}
    	echo '</ul>';
    } else {
    	spa_etext('No users with this special rank');
    }

    echo '</fieldset>';
}

if ($action == 'delsmiley') {
	$file = sp_esc_str($_GET['file']);
	$path = SF_STORE_DIR.'/'.$spPaths['smileys'].'/'.$file;
	@unlink($path);

	# load smiles from sfmeta
	$meta = sp_get_sfmeta('smileys', 'smileys');

	# now cycle through to remove this entry and resave
	if (!empty($meta[0]['meta_value'])) {
		$newsmileys = array();
		foreach ($meta[0]['meta_value'] as $name => $info) {
			if ($info[0] != $file) {
				$newsmileys[$name][0] = sp_filter_title_save($info[0]);
				$newsmileys[$name][1] = sp_filter_name_save($info[1]);
				$newsmileys[$name][2] = sp_filter_name_save($info[2]);
				$newsmileys[$name][3] = $info[3];
				$newsmileys[$name][4] = $info[4];
			}
		}
		sp_update_sfmeta('smileys', 'smileys', $newsmileys, $meta[0]['meta_id'], true);
	}

	echo '1';
}

if ($action == 'delbadge') {
	$file = sp_esc_str($_GET['file']);
	$path = SF_STORE_DIR.'/'.$spPaths['ranks'].'/'.$file;
	@unlink($path);
	echo '1';
}

die();

?>