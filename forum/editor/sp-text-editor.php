<?php
/*
Simple:Press
Plain Text Editor
$LastChangedDate: 2017-12-31 07:12:34 -0600 (Sun, 31 Dec 2017) $
$Rev: 15615 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================
# EDITOR CONSTANTS
#	Must be one of:
#	RICHTEXT 	- 1
#	HTML		- 2
#	BBCODE		- 3
#	PLAINTEXT	- 4
# ======================================

# ======================================
# CONSTANTS
# ======================================
define('SPPTDIR', dirname(__FILE__));

# ======================================
# ACTIONS/FILTERS USED BY THUS PLUGIN
# ======================================
add_action('sph_load_editor_support', 'sp_plain_load_filters', 1, 1);
add_action('sph_load_editor', 'sp_plain_load');
add_filter('sph_editor_textarea', 'sp_plain_textarea', 1, 5);

# ======================================
# CONTROL FUNCTIONS
# ======================================

# ----------------------------------------------
# Load the qt html filter file
# ----------------------------------------------
function sp_plain_load_filters($editor) {
	# set default editor if $editor parameter is empty
	if (true === empty($editor)) {
		$editor = PLAINTEXT;
	}
	if ($editor == PLAINTEXT) require_once SPPTDIR.'/sp-text-editor-filters.php';
}

# ----------------------------------------------
# Load and Initialise this Editor if needed
# ----------------------------------------------
function sp_plain_load($editor) {
	# set default editor if $editor parameter is empty
	if (true === empty($editor)) {
		$editor = PLAINTEXT;
	}
	if ($editor == PLAINTEXT) {
		$script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SP_PLUGIN_URL.'/forum/editor/sp-text-editor.js' : SP_PLUGIN_URL.'/forum/editor/sp-text-editor.min.js';
		wp_enqueue_script('speditor', $script, array('jquery'), SP_SCRIPTS_VERSION, true);
	}
}

# ----------------------------------------------
# Display Textarea Input control
# ----------------------------------------------
function sp_plain_textarea($out, $areaid, $content, $editor, $tab) {
	# set default editor if $editor parameter is empty
	if (true === empty($editor)) {
		$editor = PLAINTEXT;
	}	
	if ($editor == PLAINTEXT) $out .= '<textarea  tabindex="'.$tab.'" class="spControl spPtEditor" name="'.$areaid.'" id="'.$areaid.'" cols="80" rows="15">'.$content.'</textarea>';

	return $out;
}
