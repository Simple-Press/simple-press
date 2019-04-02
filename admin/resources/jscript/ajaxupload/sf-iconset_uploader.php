<?php
/*
Simple:Press
Iconset Uploader Script
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

// ========= NEEDS TO BE TURNED OFF UNTIL ALL PLUGIN ADMIN FORMS ARE CHANGED TO USE THE NEW NONCE CODE
//if (!sp_nonce('uploader')) die();
// ===================================================================================================


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
			'path' => $extract_to,
			'id'	=> $iconset_id,
			'active' => true
		) );
		
		if( is_wp_error( $response) ) {
			echo 'error';
			die();
		}
		
		//spa_get_iconset_icons($id, $iconset);
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
