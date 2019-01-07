<?php
/*
Simple:Press
Admin Forums Data Prep Support Functions
$LastChangedDate: 2016-10-29 14:08:09 -0500 (Sat, 29 Oct 2016) $
$Rev: 14686 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_paint_custom_icons() {
	global $spPaths;

	$out = '';

	# Open custom icons folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.spa_text('The custom icons folder does not exist').'</strong></td></tr></table>';
		return;
	}

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);

	# start the table display
	$out.= '<table class="wp-list-table widefat"><tr>';
	$out.= '<th style="width:30%;text-align:center">'.spa_text('Icon').'</th>';
	$out.= '<th style="width:50%;text-align:center">'.spa_text('Filename').'</th>';
	$out.= '<th style="text-align:center">'.spa_text('Remove').'</th>';
	$out.= '</tr>';

    $out.= '<tr><td colspan="3">';
    $out.= '<div id="sf-custom-icons">';

    $row = 0;
	if ($files) {
		sort($files);
		foreach ($files as $file) {
		    $out.= '<table id="icon'.$row.'" style="width:100%">';
			$out.= '<tr>';
			$out.= '<td style="text-align:center;width:30%" class="spWFBorder"><img class="sfcustomicon " src="'.esc_url(SFCUSTOMURL.$file).'" alt="" /></td>';
			$out.= '<td style="text-align:center;width:50%"  class="spWFBorder sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td style="text-align:center"  class="spWFBorder">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."forums&amp;targetaction=delicon&amp;file=$file", 'forums'));
			$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete custom icon').'" alt="" class="spDeleteRow" data-url="'.$site.'" data-target="icon'.$row.'" />';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
            $row++;
		}
	}
	$out.= '</div>';
	$out.= '</td></tr></table>';
	echo '<input type="hidden" id="icon-count" name="icon-count" value="'.$row.'" />';

	echo $out;
}

function spa_paint_featured_images() {
	global $spPaths;

	$out = '';

	# Open forum images folder and get contents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['forum-images'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.spa_text('The forum feauted images folder does not exist').'</strong></td></tr></table>';
		return;
	}

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);

	# start the table display
	$out.= '<table class="wp-list-table widefat"><tr>';
	$out.= '<th style="width:30%;text-align:center">'.spa_text('Image').'</th>';
	$out.= '<th style="width:50%;text-align:center">'.spa_text('Filename').'</th>';
	$out.= '<th style="text-align:center">'.spa_text('Remove').'</th>';
	$out.= '</tr>';

    $out.= '<tr><td colspan="3">';
    $out.= '<div id="sf-featured-images">';
	if ($files) {
		sort($files);
		foreach ($files as $file) {
		    $out.= '<table style="width:100%">';
			$out.= '<tr>';
			$out.= '<td style="text-align:center;width:30%" class="spWFBorder"><img class="sffeaturedimage " src="'.esc_url(SFFEATUREDURL.$file).'" alt="" /></td>';
			$out.= '<td style="text-align:center;width:50%"  class="spWFBorder sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td style="text-align:center"  class="spWFBorder">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."forums&amp;targetaction=delimage&amp;file=$file", 'forums'));
			$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete featured image').'" alt="" class="spDeleteRowReload" data-url="'.$site.'" data-reload="sfreloadfi" />';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
		}
	}
	$out.= '</div>';
	$out.= '</td></tr></table>';

	echo $out;
}

?>