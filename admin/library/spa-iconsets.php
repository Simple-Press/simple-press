<?php

/**
 * Register a new iconset
 * 
 * @param array $set
 * 
 * @return \WP_Error|boolean
 */
function spa_add_iconset( $set = array() ) {
	
	$iconsets = spa_get_all_iconsets();
	$sfconfig          = SP()->options->get('sfconfig');
	
	$id				= isset( $set['id'] )	  ? $set['id']     : rand( 1111111, 9999999 );
	$path			= SP_STORE_DIR . '/' . $sfconfig['iconsets'] . '/' . $id;
	$prefix			= isset( $set['prefix'] ) ? $set['prefix'] : '';
	$css_path		= trailingslashit( $path ) . 'style.css';
	
	$set['active']  = isset( $set['active'] ) ? $set['active'] : false;
	$set['name']	= isset( $set['name'] ) ? $set['name']  : ucwords( str_replace( array( '-', '_' ), ' ', $id ) );
	
	$error = '';
	
	if( array_key_exists( $id, $iconsets ) ) {
		$error = 'Iconset already exist';
	}
	
	if( !( file_exists( $path ) && is_dir( $path ) ) ) {
		$error = 'Iconset path does not exist';
	}
	
	if( !file_exists( $css_path ) ) {
		$error = 'Iconset style.css file does not exist';
	}
	
	if( $error ) {
		return new WP_Error( 'iconset', $error );
	}
	
	$parse_result = spa_iconset_parse( $css_path, $prefix );
	
	$iconsets[ $id ] = array_merge( $set, $parse_result );
	
	SP()->options->update( 'iconsets', $iconsets );
	
	return true;
}


/**
 * Remove iconset
 * 
 * @param string $iconset
 * 
 * @return boolean
 */
function spa_remove_iconset( $iconset = '' ) {
	
	if( !$iconset ) {
		return false;
	}
	
	$iconsets = spa_get_all_iconsets();
	
	if( isset( $iconsets[ $iconset ] ) ) {
		unset( $iconsets[ $iconset ] );
	}
	
	SP()->options->update( 'iconsets', $iconsets );

	return	true;
}


/**
 * Return iconset setting
 * 
 * @param string $id
 * 
 * @return boolean
 */
function spa_get_icoset_config( $id ) {
	$iconsets = spa_get_all_iconsets();
	
	if( isset( $iconsets[ $id ] ) ) {
		return $iconsets[ $id ];
	}
	
	return false;
}


/**
 * Return add registered iconsets
 * 
 * @return array
 */
function spa_get_all_iconsets() {
	
	$iconsets = SP()->options->get( 'iconsets' );
	
	$iconsets = is_array( $iconsets ) ? $iconsets : array();
	
	return $iconsets;
}

/**
 * Return all active iconsets
 * 
 * @return array
 */
function spa_get_all_active_iconsets() {
	
	$all_iconsets = spa_get_all_iconsets();
	
	$iconsets = array();
	
	foreach( $all_iconsets as $id => $iconset ) {
		if( $iconset['active'] ) {
			$iconsets[ $id ] = $iconset;
		}
	}
	
	return $iconsets;
}


/**
 * Parse iconset css file
 * 
 * @param string $css_path
 * @param string $prefix
 * 
 * @return array
 */
function spa_iconset_parse( $css_path, $prefix = '' ) {
	
	$css = file_get_contents( $css_path );
	
	$icons = array();
	
	if( !$prefix ) {
		preg_match('/\[class\^=\"(.*)\"\]\,\s*\[class\*=\"\s*(.*)\"\]\s*\{/', $css, $match );

		if( $match && $match[1] && $match[1] === $match[2] ) {
			$prefix = $match[1];
		}
	}
	
	if( $prefix ) {
		
		preg_match_all( '/\.('.$prefix.'(.*))\:(before|after)\s*{/', $css, $matches );
		
		if( $matches && !empty( $matches ) ) {
			$icons = $matches[1];
		}
	}
	
	return array(
		'prefix' => $prefix,
		'icons' => $icons
	);
}


/**
 * Return icons for active iconsets
 * 
 * @return array
 */
function spa_get_all_active_iconset_icons() {
	
	$active_iconsets = spa_get_all_active_iconsets();
	
	$icons = array();
	
	foreach( $active_iconsets as $id => $iconset ) {
		$icons[$id] = $iconset['icons'];
	}
	
	return $icons;
	
}



add_action( 'wp_enqueue_scripts', 'sp_enqueue_iconsets' );
add_action( 'admin_enqueue_scripts', 'sp_enqueue_iconsets' );

/**
 * Enqueue iconset's css files
 */
function sp_enqueue_iconsets() {
	
	$active_iconsets = spa_get_all_active_iconsets();
	$sfconfig          = SP()->options->get('sfconfig');
	
	foreach( $active_iconsets as $set_id => $iconset ) {
		
		$css_url = SP_STORE_URL . '/' . $sfconfig['iconsets'] . "/{$set_id}/style.css";
		
		wp_enqueue_style( $set_id . '-iconset-style', $css_url );
		
	}
}


/**
 * Get saved icon and type
 * 
 * @param string $icon
 * 
 * @return array
 */
function spa_get_saved_icon( $icon ) {
	
	$icon_args = array();

	$icon_args['icon'] = $icon;
	$icon_args['color'] = '';
	$icon_args['size']		= '';
	$icon_args['size_type'] = '';
	
	if( !empty( $icon ) && is_array( json_decode( $icon, true ) ) && ( json_last_error() == JSON_ERROR_NONE ) ) {
		
		$ar_icon = json_decode( $icon, true );
		
		$icon_args['icon'] = isset( $ar_icon['i'] ) ? $ar_icon['i'] : '';
		$icon_args['color'] = isset( $ar_icon['c'] ) ? $ar_icon['c'] : '';
		$size_ar = isset( $ar_icon['s'] ) ? spa_iconset_parse_size( $ar_icon['s'] ) : array();
		
		if( !empty( $size_ar ) ) {
			$icon_args['size']		= isset( $size_ar['size'] )		 ? $size_ar['size']		 : '';
			$icon_args['size_type'] = isset( $size_ar['size_type'] ) ? $size_ar['size_type'] : '';
		}
		
	} 
	// else {
	// 	$icon_args['icon'] = $icon;
	// 	$icon_args['color'] = '';
	// 	$icon_args['size']		= '';
	// 	$icon_args['size_type'] = '';
	// }
	
	
	if( $icon_args['size'] ) {
		$icon_args['font_size'] = $icon_args['size'] . $icon_args['size_type'];
	}
	
	$icon_args['type'] = 'file';
	
	if( !empty( $icon_args['icon'] ) ) {
	
		$supported_image = array( 'gif', 'jpg', 'jpeg', 'png' );

		$ext = strtolower( pathinfo( $icon_args['icon'], PATHINFO_EXTENSION ) );

		$icon_args['type'] = 'font';

		if ( in_array( $ext, $supported_image ) ) {
			$icon_args['icon'] = sanitize_file_name( $icon_args['icon'] );
			$icon_args['type'] = 'file';
		}
	}
	
	return $icon_args;
}

/**
 * Get saved icon and type
 * 
 * @param string $jsonIcon
 * @param string $location - 'forum' for forum icons/images located in the sp-resources/forum-custom-icons folder, 'ranks' for badges/images located in the sp-resources/forum-badges folder.
 * @param string $title [optional]
 * @param string $defaultFileUrl [optional]
 * 
 * @return string html of icon
 */
function spa_get_saved_icon_html($jsonIcon, $location = 'forum', $title = '', $defaultFileUrl = '') {
	
	# Set url and file location vars based on the $location parameter.
	$imgdir = '';
	$imgurl = '';
	switch ($location) {
		case 'forum':
			$imgdir = SPCUSTOMDIR;
			$imgurl = SPCUSTOMURL;
			break;
		case 'ranks':
			$imgdir = SPRANKSIMGDIR;
			$imgurl = SPRANKSIMGURL;
			break;
	}
	
    $out = '';
    if ($jsonIcon) {
        $arr_icon = spa_get_saved_icon($jsonIcon);
    } else if ($defaultFileUrl) {
        $arr_icon = array(
            'type' => 'file',
            'icon' => $defaultFileUrl,
        );
    }
    if (empty($arr_icon)) {
        return $out;
    }
    if ('file' === $arr_icon['type']) {
        if (empty($arr_icon['icon']) || !file_exists($imgdir . $arr_icon['icon'])) {
            $arr_icon['icon'] = $defaultFileUrl;
        } else {
            $arr_icon['icon'] = esc_url($imgurl . $arr_icon['icon']);
        }
    }
    if ('file' === $arr_icon['type']) {
        $out .= '<img src="' . $arr_icon['icon'] . '" alt="" title="' . $title . '" />';
    } else {
        $out .= '<i class="' . $arr_icon['icon'] . '"';
        if (!empty($arr_icon['color'])) {
            $out .= ' style="color:' . $arr_icon['color'] . '"';
        }
        $out .= '></i>';
    }
    return $out;
}

/**
 * Return size value and unit from string
 * 
 * @param string $size
 * 
 * @return array
 */
function spa_iconset_parse_size( $size ) {
				
	$default_size_units = spa_iconset_icon_size_units();
	preg_match('/([\d.]+)(px|rem)/', $size, $output );

	if( !empty( $output ) && $output[1] && $output[2] ) {
		return array(
			'size' => $output[1],
			'size_type' => $output[2]
		);
	}

	return array();
}

/**
 * Return iconset font size units
 * 
 * @return array
 */
function spa_iconset_icon_size_units() {
	return array(
		'px',
		'rem'
	);
}

/**
 * Return dropdown field for font size
 * 
 * @param string $current
 * 
 * @return string
 */
function spa_iconset_size_type_field( $current = '' ) {
		
	$size_units = spa_iconset_icon_size_units();

	$field = '<div class="sf-select-wrap"><select class="font-style-size_type">';
	foreach( $size_units as $unit )  {
		$selected = $current == $unit ? ' selected="selected"' : '';
		$field .= sprintf( '<option value="%s"%s>%s</option>', $unit, $selected, $unit );
	}
	$field .= '</select></div>';

	return $field;
}
