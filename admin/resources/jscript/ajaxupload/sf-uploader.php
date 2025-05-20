<?php
/*
Simple:Press
Image Uploader Script
$LastChangedDate: 2010-03-26 16:38:27 -0700 (Fri, 26 Mar 2010) $
$Rev: 3818 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

if (!sp_nonce('uploader')) {
    die('Could not find nonce');
}
// ===================================================================================================

# only admins should be able to access this...
if (!SP()->auths->current_user_can('SPF Manage Options')) {
    die('Not enough permissions');
}

# workaround function for php installs without exif.  leave original function since this is slower.
if (!function_exists('exif_imagetype')) {
    function exif_imagetype($filename) {
        if ((list($width, $height, $type, $attr) = @getimagesize(str_replace(' ', '%20', $filename))) !== false) return $type;
    	return false;
    }
}

$uploaddir = SP()->filters->str($_POST['saveloc']);

# Clean up file name just in case
$uploadfile = $uploaddir.SP()->saveFilters->filename(basename($_FILES['uploadfile']['name']));

# Check the file type
$file_type_check = wp_check_filetype( $uploadfile );
if ( ( ! $file_type_check ) || ( ! in_array( $file_type_check['ext'], array('gif', 'png', 'jpg', 'jpeg') ) ) ) {
	echo 'invalid';
	die;
}

# check for existence
if (file_exists($uploadfile)) {
	echo 'exists';
	die();
}

# check file size against limit if provided
if (isset($_POST['size'])) {
	if ($_FILES['uploadfile']['size'] > $_POST['size']) {
		echo 'size';
		die();
	}
}

require_once(ABSPATH . 'wp-admin/includes/file.php');

# Try uploading the file using WordPress default function
$movefile = wp_handle_upload($_FILES['uploadfile'], array('test_form' => false));

$filename = basename($movefile['file']);


if ($movefile && !isset($movefile['error'])) {
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    $destination = $uploaddir . $filename;
    if ($wp_filesystem->move($movefile['file'], $destination)) {
        // File is successfully uploaded.
        echo "success";
    } else {
        // Upload failed.
        // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
        // Otherwise onSubmit event will not be fired
        echo "error";
    }
} else {
    // Upload failed.
    // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
    // Otherwise onSubmit event will not be fired
    echo "error";
}
  

die();
