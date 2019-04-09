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
	
	$id				= isset( $set['id'] )	  ? $set['id']     : rand( 1111111, 9999999 );
	$path			= isset( $set['path'] )   ? $set['path']   : '';
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
	
	
	foreach( $active_iconsets as $set_id => $iconset ) {
		
		
		$css_path = $iconset['path'] . '/style.css';
		
		$css_path = str_replace( SP_STORE_DIR, SP_STORE_URL, $css_path );
		
		wp_enqueue_style( $set_id . '-iconset-style', $css_path );
		
	}
}