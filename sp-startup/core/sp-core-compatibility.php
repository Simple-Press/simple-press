<?php
/**
 * Functions that support compatibility with various 3rd party plugins.
 * This file loads at core level - all page loads for admin and front
 *
 *  $LastChangedDate: 2022-09-21 16:40:00 -0600 (Wed, 21 Sept 2022) $
 *  $Rev: 15877 $
 */
if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
 * Handles deactivating the_content filter when beaver builder is active.
 *
 * @since 6.8
 *
 * @return string    current system status (Ok, Install or Upgrade)
 */
 add_action( 'sph_before_PostIndexContent', 'sp_bb_compat_before_postindexcontent' );
function sp_bb_compat_before_postindexcontent() {
	if( class_exists('FLBuilder') ) {
		remove_filter( 'the_content',  'FLBuilder::render_content' );
	}
}

/**
 * Handles reactivating the_content filter when beaver builder is active.
 *
 * @since 6.8
 *
 * @return string    current system status (Ok, Install or Upgrade)
 */
 add_action( 'sph_after_PostIndexContent', 'sp_bb_compat_after_postindexcontent' );
function sp_bb_compat_after_postindexcontent() {
	if( class_exists('FLBuilder') ) {
		add_filter( 'the_content',  'FLBuilder::render_content' );
	}
}



