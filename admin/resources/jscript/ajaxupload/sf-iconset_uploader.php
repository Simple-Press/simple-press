<?php
/*
Simple:Press
Iconset Uploader Script
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

// ========= THIS MIGHT NEED TO BE TURNED OFF UNTIL ALL PLUGIN ADMIN FORMS ARE CHANGED TO USE THE NEW NONCE CODE.  IT'S TURNED ON NOW BUT IF ISSUES ARISE PLEASE CHECK TO MAKE SURE FORMS ARE USING THE NEW NONCE CODE.
if (!sp_nonce('uploader')) die();
// ===================================================================================================

# only admins should be able to access this...
if (!SP()->auths->current_user_can('SPF Manage Options')) die();

require_once SP_PLUGIN_DIR.'/admin/library/spa-iconsets.php';

$sfconfig          = SP()->options->get('sfconfig');

$iconsets_base_dir = SP_STORE_DIR . '/' . $sfconfig['iconsets'] . '/';

$upload_dir = $iconsets_base_dir . '__uploads/';

if ( !file_exists($upload_dir) ) {
	@mkdir($upload_dir, 0775);
}

# Clean up file name just in case
$filename = strtolower ( SP()->saveFilters->filename( basename( $_FILES['uploadfile']['name'] ) ) );
$uploadfile = $upload_dir. $filename;

# Check the file type
/*
$file_type_check = wp_check_filetype( $filename );
if ( ( ! $file_type_check ) || ( ! in_array( $file_type_check['ext'], array('zip') ) ) ) {
	echo 'invalid';
	die;
}
*/
# the wp_check_filetype call in the above commented out section is returning the wrong thing so we're just going to check the last 4 chars of the filename instead.
if (strtolower(substr($uploadfile,-4)) <> '.zip') {
	echo 'invalid';
	die;	
}

$filename_info = pathinfo( $filename );
$iconset_id = $filename_info['filename'];
$extract_to = $iconsets_base_dir . $filename_info['filename'];

# check for existence
if ( file_exists( $uploadfile ) || file_exists( $extract_to ) ) {
	echo 'exists';
	die();
}

# try uploading the file over
if ( move_uploaded_file( $_FILES['uploadfile']['tmp_name'], $uploadfile ) ) {
	@chmod( $uploadfile, 0644 );
	
	# Now try and unzip it
	require_once ABSPATH.'wp-admin/includes/class-pclzip.php';
	$zipfile		 = $uploadfile;
	$zipfile		 = str_replace('\\', '/', $zipfile); # sanitize for Win32 installs
	$zipfile		 = preg_replace('|/+|', '/', $zipfile); # remove any duplicate slash
	$extract_to		 = str_replace('\\', '/', $extract_to); # sanitize for Win32 installs
	$extract_to		 = preg_replace('|/+|', '/', $extract_to); # remove any duplicate slash
	$archive		 = new PclZip($zipfile);
	
	$archive->extract($extract_to);
	if ($archive->error_code == 0) {
		
		$response = spa_add_iconset( array(
			'id'	=> $iconset_id,
			'active' => true
		) );
		
		if( is_wp_error( $response) ) {
			echo 'error';
			die();
		}
		
		$successExtract1 = true;
	} else {
		echo "error";
		die();
	}
	

	# Lets try and remove the zip as it seems to have worked
	@unlink($zipfile);
	
	echo "success";
} else {
	# WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
	# Otherwise onSubmit event will not be fired
	echo "error";
}

die();
