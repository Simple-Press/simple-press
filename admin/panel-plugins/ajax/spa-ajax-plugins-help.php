<?php
/*
Simple:Press
Admin Plugin Help
$LastChangedDate: 2014-10-20 15:38:39 +0100 (Mon, 20 Oct 2014) $
$Rev: 12009 $
*/

// Todo: Can this file be removed? Looks like it's not used
if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

if (!sp_nonce('plugin-tip')) {
    die();
}

if (!isset($_GET['file'])) {
    die();
}

$file = SP()->filters->filename($_GET['file']);

# Formatting and Display of Help Panel
$helptext = wpautop(sp_retrieve_plugin_help($file), false);

echo '<div>';
echo '<fieldset>';
echo esc_html($helptext);
echo '</fieldset>';
echo '</div>';
die();

function sp_retrieve_plugin_help($file) {
    $file = SPPLUGINDIR . $file;

    if (!file_exists($file)) {
        return esc_html(SP()->primitives->admin_text('No help file can be located'));
    }

    $theData = wp_remote_get(
        set_url_scheme('file://' . wp_normalize_path($file), 'file')
    );

    if (is_wp_error($theData)) {
        return esc_html(SP()->primitives->admin_text('Error reading help file'));
    }

    return wp_remote_retrieve_body($theData);
}
