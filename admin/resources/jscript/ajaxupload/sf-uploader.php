<?php
/*
Simple:Press
Image Uploader Script
$LastChangedDate: 2010-03-26 16:38:27 -0700 (Fri, 26 Mar 2010) $
$Rev: 3818 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

// ========= THIS MIGHT NEED TO BE TURNED OFF UNTIL ALL PLUGIN ADMIN FORMS ARE CHANGED TO USE THE NEW NONCE CODE.  IT'S TURNED ON NOW BUT IF ISSUES ARISE PLEASE CHECK TO MAKE SURE FORMS ARE USING THE NEW NONCE CODE.
if (!sp_nonce('uploader')) die();
// ===================================================================================================

# only admins should be able to access this...
if (!SP()->auths->current_user_can('SPF Manage Options')) die();

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

# check image file mimetype
/*
$mimetype = 0;
$mimetype = exif_imagetype($_FILES['uploadfile']['tmp_name']);
if (empty($mimetype) || $mimetype == 0 || $mimetype > 3) {
	echo 'invalid';
	die();
}
*/

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

# try uploading the file over
if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile)) {
	@chmod("$uploadfile", 0644);
	echo "success";
} else {
	# WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
	# Otherwise onSubmit event will not be fired
	echo "error";
}

die();
