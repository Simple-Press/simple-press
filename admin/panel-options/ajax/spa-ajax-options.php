<?php
/*
Simple:Press
Options Specials
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');



spa_admin_ajax_support();

if (!sp_nonce('options')) die();

# Check Whether User Can Manage Components
if (!SP()->auths->current_user_can('SPF Manage Options')) die();

$action = SP()->filters->str($_GET['targetaction']);

/**
 * Remove directory files recursively
 * @param string $dir
 */
function spa_remove_dir( $dir ) {
    foreach ( glob($dir) as $file ) {
        if (is_dir($file)) { 
            spa_remove_dir("$file/*");
            rmdir($file);
        } else {
            unlink($file);
        }
    }
}

# Handle remove iconset request via ajax
if ($action == 'deliconset') {
	$iconset_id = SP()->filters->str($_GET['iconset']);
	
	
	$iconsets = spa_get_all_iconsets();
	
	
	if( isset( $iconsets[ $iconset_id ] ) ) {
		
		
		$path = $iconsets[ $iconset_id ]['path'];
		
		spa_remove_dir( $path . '/*');
		
		rmdir($path);

		unset( $iconsets[ $iconset_id ] );
		
		
		
		SP()->options->update( 'iconsets', $iconsets );
	}
	
	echo '1';
}


# Handle toggle Enable/Disable iconset request via ajax
if ( $action == 'disableiconset' || $action == 'enableiconset' ) {
	$iconset_id = SP()->filters->str($_GET['iconset']);
	
	
	$iconsets = spa_get_all_iconsets();
	
	
	if( isset( $iconsets[ $iconset_id ] ) ) {
		
		$active = $action === 'enableiconset' ? true : false;
		
		$iconsets[ $iconset_id ]['active'] = $active;
		
		SP()->options->update( 'iconsets', $iconsets );
	}
	
	echo '1';
}





die();
