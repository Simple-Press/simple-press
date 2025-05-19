<?php
/*
Simple:Press
Admin Forums Data Prep Support Functions
$LastChangedDate: 2017-04-02 14:20:25 -0500 (Sun, 02 Apr 2017) $
$Rev: 15314 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_paint_custom_icons() {
	$out = '';

	# Open custom icons folder and get cntents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['custom-icons'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.esc_html(SP()->primitives->admin_text('The custom icons folder does not exist')).'</strong></td></tr></table>';
		return;
	}

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);
	
	#Random value that will be used to break caching.
	$breakcache=time();

	# start the table display
	$out.= '<table id="sf-custom-icons" class="wp-list-table widefat">';
	$out.= '<thead><tr>';
	$out.= '<th>'.esc_html(SP()->primitives->admin_text('Icon')).'</th>';
	$out.= '<th>'.esc_html(SP()->primitives->admin_text('Filename')).'</th>';
	$out.= '<th>'.esc_html(SP()->primitives->admin_text('Remove')).'</th>';
	$out.= '</thead></tr>';

    $row = 0;
	if ($files) {
		sort($files);
		foreach ($files as $file) {
			$out.= '<tr id="icon'.esc_attr($row).'">';
			$out.= '<td class="spWFBorder"><img class="sfcustomicon " src="'.esc_url(SPCUSTOMURL.$file.'?breakcache='.$breakcache).'" alt="" /></td>';
			$out.= '<td class="spWFBorder sflabel">';
			$out.= esc_html($file);
			$out.= '</td>';
			$out.= '<td class="spWFBorder">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."forums&amp;targetaction=delicon&amp;file=$file", 'forums'));
			$out .= '<div class="sf-item-controls">';
			$out.= '<span title="'.esc_attr(SP()->primitives->admin_text('Delete custom icon')).'" class="sf-icon sf-delete spDeleteRow" data-url="'.esc_attr($site).'" data-target="icon'.esc_attr($row).'"></span>';
			$out .= '</div>';
			$out.= '</td>';
			$out.= '</tr>';
            $row++;
		}
	}

	$out.= '</table>';
	echo '<input type="hidden" id="icon-count" name="icon-count" value="'.esc_attr($row).'" />';

	echo wp_kses(
        $out,
        [
            'table' => [
                'id' => [],
                'class' => [],
            ],
            'tr' => [
                'id' => [],
            ],
            'td' => [
                'class' => [],
            ],
            'img' => [
                'class' => [],
                'src' => [],
                'alt' => [],
            ],
            'span' => [
                'title' => [],
                'class' => [],
                'data-url' => [],
                'data-target' => [],
            ],
            'div' => [
                'class' => [],
            ],
        ]
    );
}

function spa_paint_featured_images() {
	$out = '';

	# Open forum images folder and get contents for matching
	$path = SP_STORE_DIR.'/'.SP()->plugin->storage['forum-images'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.esc_html(SP()->primitives->admin_text('The forum featured images folder does not exist')).'</strong></td></tr></table>';
		return;
	}

	$files = array();
	while (false !== ($file = readdir($dlist))) {
		if ($file != '.' && $file != '..') {
			$files[] = $file;
		}
	}
	closedir($dlist);
	
	#Random value that will be used to break caching.
	$breakcache=time();	

	# start the table display
	$out.= '<table id="sf-featured-images" class="wp-list-table widefat">';
	$out.= '<thead><tr>';
	$out.= '<th>'.esc_html(SP()->primitives->admin_text('Image')).'</th>';
	$out.= '<th>'.esc_html(SP()->primitives->admin_text('Filename')).'</th>';
	$out.= '<th>'.esc_html(SP()->primitives->admin_text('Remove')).'</th>';
	$out.= '</tr></thead>';

	$rows = 0;
	if ($files) {
		sort($files);
		foreach ($files as $file) {

			$out.= '<tr>';
			$out.= '<td class="spWFBorder"><img class="sffeaturedimage " src="'.esc_url(SPOGIMAGEURL.$file.'?breakcache='.$breakcache).'" alt="" /></td>';
			$out.= '<td class="spWFBorder sflabel">';
			$out.= esc_html($file);
			$out.= '</td>';
			$out.= '<td class="spWFBorder">';
			$out .= '<div class="sf-item-controls">';
			$site = esc_url(wp_nonce_url(SPAJAXURL."forums&amp;targetaction=delimage&amp;file=$file", 'forums'));
			$out.= '<span title="'.esc_attr(SP()->primitives->admin_text('Delete featured image')).'" class="sf-icon sf-delete spDeleteRowReload" data-url="'.esc_attr($site).'" data-target="img'.esc_attr($rows).'" data-reload="sfreloadfi"></span>';
			$out .= '</div>';
			$out.= '</td>';
			$out.= '</tr>';
			$rows++;
		}
	}
	
	$out.= '</table>';
	echo '<input type="hidden" id="img-count" name="img-count" value="'.esc_attr($rows).'" />';

	echo wp_kses(
        $out,
        [
            'table' => [
                'id' => [],
                'class' => [],
            ],
            'thead' => [],
            'tbody' => [],
            'tr' => [],
            'th' => [],
            'td' => [
                'class' => [],
            ],
            'img' => [
                'class' => [],
                'src' => [],
                'alt' => [],
            ],
            'span' => [
                'title' => [],
                'class' => [],
                'data-url' => [],
                'data-target' => [],
                'data-reload' => [],
            ],
            'div' => [
                'class' => [],
            ],
        ]
    );
}
