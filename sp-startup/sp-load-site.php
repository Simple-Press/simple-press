<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2016-06-08 02:02:32 -0500 (Wed, 08 Jun 2016) $
$Rev: 14244 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE
#	This file loads the additional core SP support needed by the site (front end) for all
#	page loads - not just for the forum. It also exposes base api files that may be needed by
#	plugins, template tags etc., and creates items needed by the header for non forum use.
#
# ==========================================================================================

# Include core api files

# Load blog script support
add_action('wp_enqueue_scripts', 'sp_load_blog_script');

# Load blog header support
add_action('wp_head', 'sp_load_blog_support');

do_action('sph_site_startup');

?>