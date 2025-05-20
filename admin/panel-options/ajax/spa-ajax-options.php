<?php
/*
Simple:Press
Options Specials
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('options')) {
    die();
}

# Check Whether User Can Manage Components
if (!SP()->auths->current_user_can('SPF Manage Options')) {
    die();
}

$action = SP()->filters->str($_GET['targetaction']);

/**
 * Remove directory files recursively
 * @param string $dir
 */

function spa_remove_dir( $dir ) {
    global $wp_filesystem;

    // Initialize WP_Filesystem if not already done
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    $dir = untrailingslashit($dir);

    $files = $wp_filesystem->dirlist($dir);

    if (is_array($files)) {
        foreach ($files as $file => $fileinfo) {
            $file_path = $dir . '/' . $file;
            if ('d' === $fileinfo['type']) {
                spa_remove_dir($file_path);
                $wp_filesystem->rmdir($file_path);
            } else {
                $wp_filesystem->delete($file_path);
            }
        }
    }
    $wp_filesystem->rmdir($dir);
}

# Handle remove iconset request via ajax
if ($action == 'deliconset') {
	$iconset_id = SP()->filters->str($_GET['iconset']);
	
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
	
	$iconsets = spa_get_all_iconsets();
	
	if( isset( $iconsets[ $iconset_id ] ) ) {
		
		$sfconfig   = SP()->options->get('sfconfig');
		$path		= SP_STORE_DIR . '/' . $sfconfig['iconsets'] . '/' . $iconset_id;
		
		$wp_filesystem->rmdir( $path );
		
		spa_remove_iconset( $iconset_id );
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
