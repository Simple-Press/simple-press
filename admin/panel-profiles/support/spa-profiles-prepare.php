<?php
/*
Simple:Press
Admin Profiles Support Functions
$LastChangedDate: 2016-10-29 14:08:09 -0500 (Sat, 29 Oct 2016) $
$Rev: 14686 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_options_data() {
	$sfprofile = sp_get_option('sfprofile');
	$sfsigimagesize = sp_get_option('sfsigimagesize');
	$sfprofile['sfsigwidth'] = $sfsigimagesize['sfsigwidth'];
	$sfprofile['sfsigheight'] = $sfsigimagesize['sfsigheight'];
	return $sfprofile;
}

function spa_get_tabsmenus_data() {
	$tabs = sp_profile_get_tabs();
	return $tabs;
}

function spa_get_avatars_data() {
	$sfavatars = sp_get_option('sfavatars');
	if(empty($sfavatars['sfavatarpriority'])) {
		$sfavatars['sfavatarpriority'] = array(0, 2, 3, 1, 4, 5);
	}
	return $sfavatars;
}

function spa_paint_avatar_pool() {
	global $tab, $spPaths;

	$out = '';

	# Open avatar pool folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['avatar-pool'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.spa_text('The avatar pool folder does not exist').'</strong></td></tr></table>';
		return;
	}

	# start the table display
	$out.= '<table class="wp-list-table widefat"><tr>';
	$out.= '<th style="width:30%;text-align:center">'.spa_text('Avatar').'</th>';
	$out.= '<th style="width:50%;text-align:center">'.spa_text('Filename').'</th>';
	$out.= '<th style="text-align:center">'.spa_text('Remove').'</th>';
	$out.= '</tr>';

	$out.= '<tr><td colspan="3">';
	$out.= '<div id="sf-avatar-pool">';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$found = false;
			$out.= '<table style="width:100%">';
			$out.= '<tr>';
			$out.= '<td style="text-align:center;width:30%" class="spWFBorder"><img class="sfavatarpool" src="'.esc_url(SFAVATARPOOLURL.$file).'" alt="" /></td>';
			$out.= '<td style="text-align:center;width:50%" class="spWFBorder sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td style="text-align:center" class="spWFBorder">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=delavatar&amp;file=$file", 'profiles'));
			$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete Avatar').'" alt="" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadpool" />';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
		}
	}
	$out.= '</div>';
	$out.= '</td></tr></table>';
	closedir($dlist);

	echo $out;
}

function spa_paint_avatar_defaults() {
	global $spPaths;

	$out = '';
	$values = array(spa_text('None'), spa_text('Admin'), spa_text('Moderator'), spa_text('Member'), spa_text('Guest'));

	# Open avatar defaults folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['avatars'].'/defaults/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.spa_text('The avatar defaults folder does not exist').'</strong></td></tr></table>';
		return;
	}

	$def = sp_get_option('spDefAvatars');

	# start the table display
	$out.= '<div id="av-browser">';

	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$found = false;
			$border = (in_array($file, $def) ? '2px solid red' : '2px solid lightgray');

			$out.= '<div class="av-file" style="text-align:left;border:'.$border.';margin:5px;padding:6px;float:left;">';

				$out.= '<img src="'.esc_url(SFAVATARURL.'defaults/'.$file).'" alt="" />';

				$site = esc_url(wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=deldefault&amp;file=$file", 'profiles'));
				$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" alt="" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadav" style="cursor:pointer;" />';
				$out.= '<div class="clearboth"></div>';

				$fileid = str_replace('.', 'z1z2z3', $file);
				$checked = ($def['admin']==$file) ? ' checked="checked" ' : '';
				if ($def['admin']==$file) $found=true;
				$out.= '<input type="radio" value="admin" class="spCheckAvatarDefaults" id="adm-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="adm-'.$fileid.'">'.spa_text('Admin').'</label><br>';

				$checked = ($def['mod']==$file) ? ' checked="checked" ' : '';
				if ($def['mod']==$file) $found=true;
				$out.= '<input type="radio" value="mod" class="spCheckAvatarDefaults" id="mod-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="mod-'.$fileid.'">'.spa_text('Moderator').'</label><br>';

				$checked = ($def['member']==$file) ? ' checked="checked" ' : '';
				if ($def['member']==$file) $found=true;
				$out.= '<input type="radio" value="member" class="spCheckAvatarDefaults" id="mem-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="mem-'.$fileid.'">'.spa_text('Member').'</label><br>';

				$checked = ($def['guest']==$file) ? ' checked="checked" ' : '';
				if ($def['guest']==$file) $found=true;
				$out.= '<input type="radio" value="guest" class="spCheckAvatarDefaults" id="gue-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="gue-'.$fileid.'">'.spa_text('Guest').'</label><br>';

				$checked = (!$found) ? ' checked="checked" ' : '';
				$out.= '<input type="radio" value="none" class="spCheckAvatarDefaults" id="non-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="non-'.$fileid.'">'.spa_text('None').'</label>';

				$out.= '<div class="clearboth"></div>';

			$out.= '</div>';
		}
	}

	$out.= '<div class="clearboth"></div>';
	$out.= '</div>';
	closedir($dlist);

	echo $out;
}

?>