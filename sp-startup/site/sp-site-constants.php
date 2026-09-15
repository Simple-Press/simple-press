<?php
/**
 * Site Constants
 * This file loads at site level - all page loads for front end
 *
 * $LastChangedDate: 2017-03-25 00:22:12 -0500 (Sat, 25 Mar 2017) $
 * $Rev: 15303 $
 */

global $wpdb;
if (!defined('SPBLOGID')) define('SPBLOGID', $wpdb->blogid);

# Location of forum non-theme images
if (!defined('SPFIMAGES')) define('SPFIMAGES', SP_PLUGIN_URL.'/forum/resources/images/');
