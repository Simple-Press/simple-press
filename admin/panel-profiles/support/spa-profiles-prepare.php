<?php
/*
Simple:Press
Admin Profiles Support Functions
$LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
$Rev: 15799 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_options_data() {
	$sfprofile = SP()->options->get('sfprofile');
	$sfsigimagesize = SP()->options->get('sfsigimagesize');
	if($sfsigimagesize){
		$sfprofile['sfsigwidth'] = isset($sfsigimagesize['sfsigwidth']) ? $sfsigimagesize['sfsigwidth'] : null;
		$sfprofile['sfsigheight'] = isset($sfsigimagesize['sfsigheight']) ? $sfsigimagesize['sfsigheight'] : null;
	}
	
	return $sfprofile;
}

function spa_get_tabsmenus_data() {
	$tabs = SP()->profile->get_tabs_menus();
	return $tabs;
}

function spa_get_avatars_data() {
	$sfavatars = SP()->options->get('sfavatars');
	if(empty($sfavatars['sfavatarpriority'])) {
		$sfavatars['sfavatarpriority'] = [0, 2, 3, 1, 4, 5];
	}
	return $sfavatars;
}

function spa_paint_avatar_pool() {
	$out = '';

	# Open avatar pool folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatar-pool'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The avatar pool folder does not exist').'</strong></td></tr></table>';
		return;
	}

	# start the table display
	$out.= '<table class="widefat sf-table-small sf-table-mobile"><thead><tr>';
	$out.= '<th>'.SP()->primitives->admin_text('Avatar').'</th>';
	$out.= '<th>'.SP()->primitives->admin_text('Filename').'</th>';
	$out.= '<th>'.SP()->primitives->admin_text('Remove').'</th>';
	$out.= '</tr></thead>';
	$out.= '<tbody>';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$out.= '<tr>';
//			ashow(SPAVATARPOOLURL);
//			ashow($file);
			$out.= '<td class="spWFBorder"><div class="sf-avatar"><img class="sfavatarpool" src="'.esc_url(SPAVATARPOOLURL.$file).'" alt="" /></div></td>';
			$out.= '<td class="spWFBorder sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td class="spWFBorder"><div class="sf-item-controls">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=delavatar&amp;file=$file", 'profiles'));
			$out.= '<span title="'.SP()->primitives->admin_text('Delete Avatar').'" class="sf-icon sf-delete spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadpool"></span>';
			$out.= '</div></td>';
			$out.= '</tr>';
		}
	}
	$out.= '</tbody></table>';
	closedir($dlist);

	echo $out;
}

function spa_paint_avatar_defaults() {
	$out = '';

	# Open avatar defaults folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['avatars'].'/defaults/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.SP()->primitives->admin_text('The avatar defaults folder does not exist').'</strong></td></tr></table>';
		return;
	}

	$def = SP()->options->get('spDefAvatars');

	# start the table display
	$out.= '<div id="av-browser">';

	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$found = false;
			$border = (in_array($file, $def) ? '2px solid red' : '2px solid lightgray');

			$out.= '<div class="av-file" style="text-align:left;border:'.$border.';margin:5px;padding:6px;float:left;">';
				$out.= '<div class="sf-form-row">';
				$out.= '<div class="sf-avatar"><img src="'.esc_url(SPAVATARURL.'defaults/'.$file).'" alt="" /></div>';
				$site = esc_url(wp_nonce_url(SPAJAXURL."profiles&amp;targetaction=deldefault&amp;file=$file", 'profiles'));
				$out.= '<span class="sf-icon sf-delete spDeleteRowReload sf-pull-right" data-url="'.$site.'" data-reload="sfreloadav"></span>';
				$out.= '</div>';

				$fileid = str_replace('.', 'z1z2z3', $file);
				$checked = ($def['admin']==$file) ? ' checked="checked" ' : '';
				if ($def['admin']==$file) $found=true;
				$out.= '<input type="radio" value="admin" class="spCheckAvatarDefaults" id="adm-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="adm-'.$fileid.'">'.SP()->primitives->admin_text('Admin').'</label><br>';

				$checked = ($def['mod']==$file) ? ' checked="checked" ' : '';
				if ($def['mod']==$file) $found=true;
				$out.= '<input type="radio" value="mod" class="spCheckAvatarDefaults" id="mod-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="mod-'.$fileid.'">'.SP()->primitives->admin_text('Moderator').'</label><br>';

				$checked = ($def['member']==$file) ? ' checked="checked" ' : '';
				if ($def['member']==$file) $found=true;
				$out.= '<input type="radio" value="member" class="spCheckAvatarDefaults" id="mem-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="mem-'.$fileid.'">'.SP()->primitives->admin_text('Member').'</label><br>';

				$checked = ($def['guest']==$file) ? ' checked="checked" ' : '';
				if ($def['guest']==$file) $found=true;
				$out.= '<input type="radio" value="guest" class="spCheckAvatarDefaults" id="gue-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="gue-'.$fileid.'">'.SP()->primitives->admin_text('Guest').'</label><br>';

				$checked = (!$found) ? ' checked="checked" ' : '';
				$out.= '<input type="radio" value="none" class="spCheckAvatarDefaults" id="non-'.$fileid.'" name="'.$fileid.'"'.$checked.'>';
				$out.= '<label for="non-'.$fileid.'">'.SP()->primitives->admin_text('None').'</label>';

				$out.= '<div class="clearboth"></div>';

			$out.= '</div>';
		}
	}

	$out.= '<div class="clearboth"></div>';
	$out.= '</div>';
	closedir($dlist);

	echo $out;
}
