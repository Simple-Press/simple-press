<?php
/*
Simple:Press
Admin Panels - Options/Components Tab Rendering Support
$LastChangedDate: 2016-10-21 13:40:36 -0500 (Fri, 21 Oct 2016) $
$Rev: 14649 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# == PAINT ROUTINES

# ------------------------------------------------------------------
# spa_paint_options_init()
# Initializes the tab index sequence starting with 100
# ------------------------------------------------------------------
function spa_paint_options_init() {
	global $tab;
	$tab = 100;
}

# ------------------------------------------------------------------
# spa_paint_open_tab()
# Creates the containing block around a form or main section
# ------------------------------------------------------------------
function spa_paint_open_tab($tabname, $full=false) {
	echo "<div class='sfform-panel'>";
	echo "<div class='sfform-panel-head'><span class='sftitlebar'>$tabname</span></div>\n";

	if ($full) {
		echo '<div class="sp-full-form">';
	} else {
		echo '<div class="sp-half-form">';
	}
}

# ------------------------------------------------------------------
# spa_paint_close_container();
# Closes the containing block around a form or main section
# ------------------------------------------------------------------
function spa_paint_close_container() {
	echo '</div>';
}

# ------------------------------------------------------------------
# spa_paint_close_tab()
# Closes the whole containing block
# ------------------------------------------------------------------
function spa_paint_close_tab() {
	echo '</div>';
}

# ------------------------------------------------------------------
# spa_paint_open_nohead_tab()
# Creates the containing block around a form or main section/no heading
# ------------------------------------------------------------------
function spa_paint_open_nohead_tab($full=false) {
	echo "<div class='sfform-panel-nohead'>";

	if ($full) {
		echo '<div class="sp-full-form">';
	} else {
		echo '<div class="sp-half-form">';
	}
}

function spa_paint_tab_right_cell() {
	echo '</div>';
	echo '<div class="sp-half-form">';
}

function spa_paint_open_panel() {
	echo '<div>';
}

function spa_paint_close_panel() {
	echo '</div>';
}

function spa_paint_open_fieldset($legend, $displayhelp=false, $helpname='', $displaylegend=true) {
	global $adminhelpfile;

	echo "<fieldset class='sffieldset'>\n";
	if($displaylegend) {
		echo "<legend><strong>$legend</strong></legend>\n";
	}
	if ($displayhelp) echo spa_paint_help($helpname, $adminhelpfile);
}

function spa_paint_close_fieldset() {
	echo "</fieldset>\n";
}

function spa_paint_input($label, $name, $value, $disabled=false, $large=false) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	if ($large) {
		echo "<div class='wp-core-ui sflabel sp-label-40'>\n";
	} else {
		echo "<div class='wp-core-ui sflabel sp-label-60'>\n";
	}
	echo "$label:</div>";
	$c = ($large) ? 'sp-input-60' : 'sp-input-40';

	echo "<input type='text' class='wp-core-ui $c' tabindex='$tab' name='$name' value='".esc_attr($value)."' ";
	if ($disabled == true) echo "disabled='disabled' ";
	echo "/>\n";
	echo '<div class="clearboth"></div>';
	echo '</div>';
	$tab++;
}

function spa_paint_textarea($label, $name, $value, $submessage='', $rows=1) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	echo "<div class='wp-core-ui sflabel sp-label-50'>\n";
	echo "$label:";
	if (!empty($submessage)) echo "<br /><small><strong>".esc_html($submessage)."</strong></small>\n";
	echo '</div>';
	echo "<textarea rows='$rows' cols='80' class='wp-core-ui sp-textarea-50' tabindex='$tab' name='$name'>".esc_html($value)."</textarea>\n";
	echo '<div class="clearboth"></div>';
	echo '</div>';

	$tab++;
}

function spa_paint_wide_textarea($label, $name, $value, $submessage='', $xrows=1) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	echo "<div class='wp-core-ui sflabel sp-label'>\n";
	echo "$label:";
	if (!empty($submessage)) echo "<small><br /><strong>$submessage</strong><br /><br /></small>\n";
	echo '</div>';
	echo "<textarea rows='$xrows' cols='80' class='wp-core-ui sp-textarea' tabindex='$tab' name='$name'>".esc_attr($value)."</textarea>\n";
	echo '<div class="clearboth"></div>';
	echo '</div>';

	$tab++;
}

function spa_paint_thin_textarea($label, $name, $value, $submessage='', $xrows=1) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	echo "<div class='wp-core-ui sflabel sp-label-66'>\n";
	echo "$label:";
	if (!empty($submessage)) echo "<small><br /><strong>$submessage</strong><br /><br /></small>\n";
	echo '</div>';
	echo "<textarea rows='$xrows' cols='80' class='wp-core-ui sp-textarea-33' tabindex='$tab' name='$name'>".esc_attr($value)."</textarea>\n";
	echo '<div class="clearboth"></div>';
	echo '</div>';

	$tab++;
}

function spa_paint_checkbox($label, $name, $value, $disabled=false, $large=false, $displayhelp=true, $msg='', $indent=false) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	if ($indent) echo str_repeat('&nbsp;', 7);
	echo "<input type='checkbox' tabindex='$tab' name='$name' id='sf-$name' ";
	if ($value == true) echo "checked='checked' ";
	if ($disabled == true) echo "disabled='disabled' ";
	echo "/>\n";
	echo "<label for='sf-$name' class='wp-core-ui'>$label</label>\n";
	echo '<div class="clearboth"></div>';
	if ($msg) echo $msg;
	echo '</div>';
	$tab++;
}

function spa_paint_select_start($label, $name, $helpname) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	echo "<div class='wp-core-ui sflabel sp-label-40'>$label:</div>\n";
	echo "<select class='wp-core-ui  sp-input-60' tabindex='$tab' name='$name'>";
	$tab++;
}

function spa_paint_select_end($msg='') {
	echo "</select>\n";
	echo '<div class="clearboth"></div>';
	if ($msg) echo $msg;
	echo '</div>';
}

function spa_paint_file($label, $name, $disabled, $large, $path) {
	global $tab;

	echo "<div class='sp-form-row'>\n";
	if ($large) {
		echo "<div class='wp-core-ui sflabel sp-label-40'>\n";
	} else {
		echo "<div class='wp-core-ui sflabel sp-label-60'>\n";
	}
	echo "$label:</div>";

	if (is_writable($path)) {
		echo '<div id="sf-upload-button" class="button-primary">'.spa_text('Browse').'</div>';
		echo '<div id="sf-upload-status"></div>';
	} else {
		echo '<div id="sf-upload-button" class="button-primary sfhidden"></div>';
		echo '<div id="sf-upload-status">';
		echo '<p class="sf-upload-status-fail">'.spa_text('Sorry, uploads disabled! Storage location does not exist or is not writable. Please see forum - integration - storage locations to correct').'</p>';
		echo '</div>';
	}
	echo '<div class="clearboth"></div>';
	echo '</div>';
	$tab++;
}

function spa_paint_hidden_input($name, $value) {
	echo '<div class="sfhidden">';
	echo "<input type='hidden' name='$name' value='".esc_attr($value)."' />";
	echo '</div>';
}


function spa_paint_link($link, $label) {
	echo "<span class='wp-core-ui sp-label'>";
	echo "<a href='".esc_url($link)."'>$label</a>\n";
	echo '</span>';
	echo '<div class="clearboth"></div>';
}

function spa_paint_radiogroup($label, $name, $values, $current, $large=false, $displayhelp=true, $class='') {
	global $tab;

	if ($class != '') $class=' class="'.$class.'" ';

	echo "<div class='sp-form-row'>\n";
	echo "<div class='wp-core-ui'><b>$label</b>:</div>\n";
	echo "<div class='wp-core-ui sp-radio'>";

	foreach ($values as $key => $value) {
	    $pos = $key + 1;
		$check = '';
		if ($current == $pos) $check = ' checked="checked" ';
		echo '<input type="radio" '.$class.'name="'.$name.'" id="sfradio-'.$tab.'"  tabindex="'.$tab.'" value="'.$pos.'" '.$check.' />'."\n";
		echo '<label for="sfradio-'.$tab.'" class="wp-core-ui">'.esc_html(spa_text($value)).'</label>'."\n<br />";
		$tab++;
	}
	echo '</div>';
	echo '<div class="clearboth"></div>';
	echo '</div>';
	$tab++;
}

function spa_paint_spacer() {
	echo '<br /><div class="clearboth"></div>';
}

function spa_paint_help($name, $helpfile, $show=true) {
	$site = wp_nonce_url(SPAJAXURL."help&amp;file=$helpfile&amp;item=$name", 'help');
	$title = spa_text('Simple:Press Help');
	$out = '';

	$out.= '<div class="sfhelplink">';
	if ($show) {
		$out.= '<a id="'.$name.'" class="button-secondary sfhelplink spHelpLink" data-site="'.$site.'" data-label="'.$title.'" data-width="600" data-height="0" data-align="center">';
		$out.= spa_text('Help').'</a>';
	}
	$out.= '</div>';
	return $out;
}

?>