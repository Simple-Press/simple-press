<?php

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}


# == PAINT ROUTINES

# ------------------------------------------------------------------
# spa_paint_options_init()
# Initializes the tab index sequence starting with 100
# ------------------------------------------------------------------
function spa_paint_options_init() {
}

function spa_paint_tab_head( $tabname, $buttons = true ) {
    $site = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL.'troubleshooting', 'troubleshooting'));
	$target = 'sfmaincontainer';

	echo "<div class='sf-panel-head'>";
        echo "<h3>" . esc_html($tabname) . "</h3>";
	echo "</div>\n";
}

function spa_paint_open_full_form() {
    echo '<div class="sf-full-form">';
}
function spa_paint_open_half_form() {
    echo '<div class="sf-half-form">';
}
function spa_paint_open_panel_body($class = '') {
    echo "<div class='sf-panel-body " . esc_attr($class) ."'>";
}

# ------------------------------------------------------------------
# spa_paint_open_tab()
# Creates the containing block around a form or main section
# ------------------------------------------------------------------
function spa_paint_open_tab( $tabname, $full=false, $info = "", $buttons = true ) {
	spa_paint_tab_head( $tabname, $buttons );
	spa_paint_open_panel_body();
	echo esc_html($info);
	if ($full) {
            spa_paint_open_full_form();
	} else {
            spa_paint_open_half_form();
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
function spa_paint_open_nohead_tab($full=false, $class="sfform-panel-nohead") {
        spa_paint_open_panel_body($class);
}

function spa_paint_tab_right_cell() {
	echo '</div>';
	spa_paint_open_half_form();
}

function spa_paint_open_panel() {
	echo '<div class="sf-panel">';
}

function spa_paint_close_panel() {
	echo '</div>';
}

function spa_paint_open_fieldset($legend, $displayhelp = false, $helpname = '', $displaylegend = true, $subTitle = '', $adminhelpfile = false) {
    if(!$adminhelpfile){
	    global $adminhelpfile;
    }
	echo "<fieldset class='sf-fieldset'>\n";
	if($displaylegend) {
		echo "<div class='sf-panel-body-top'><h4>".esc_html($legend)."</h4>";
		if($subTitle) {
			echo "<span>".esc_html($subTitle)."</span>";
		}
		if ($displayhelp) spa_paint_help($helpname, $adminhelpfile);
		echo "</div>\n";
	} else {
		if ($displayhelp) spa_paint_help($helpname, $adminhelpfile);
	}
}

function spa_paint_close_fieldset() {
	echo "</fieldset>\n";
}

function spa_paint_input($label, $name, $value, $disabled=false, $large=false, $css_classes='', $sublabel =null,  $sublabel_class='sf-sublabel sf-sublabel-small') {
	echo sprintf("<div class='sf-form-row %s'>\n", esc_attr($css_classes));

	echo sprintf("<label>%s</label>", esc_html($label));
	echo "<input type='text' class='wp-core-ui' name='".esc_attr($name)."' value='".esc_attr($value)."' ";
	if ($disabled) {
        echo "disabled='disabled' ";
    }
	echo "/>\n";
        if (!is_null($sublabel)) {
            echo "<span class='".esc_attr($sublabel_class)."'>".esc_html($sublabel)."</span>";
        }
	echo '</div>';
}

function spa_paint_single_input($name, $value, $disabled=false, $css_classes = '', $place_holder = false ) {
	echo "<input type='text' class='wp-core-ui ".esc_attr($css_classes)."' name='".esc_attr($name)."' value='".esc_attr($value)."' ";
    if ($place_holder !== false) {
        echo 'placeholder="' . esc_attr($place_holder) . '" ';
    }
	if ($disabled == true) echo "disabled='disabled' ";
	echo "/>";
}

function spa_paint_date($label, $name, $value, $disabled=false, $large=false) {
	echo "<div class='sf-form-row'>\n";
	if ($large) {
		echo "<label class='sp-label-40'>\n";
	} else {
		echo "<label class='sp-label-60'>\n";
	}
	echo esc_html($label) . "</label>";
	$c = ($large) ? 'sp-input-60' : 'sp-input-40';

	echo "<input type='date' class='wp-core-ui ".esc_attr($c)."' name='".esc_attr($name)."' value='".esc_attr($value)."' ";
	if ($disabled == true) echo "disabled='disabled' ";
	echo "/>\n";
	//echo '<div class="clearboth"></div>';
	echo '</div>';
}

function spa_paint_number($label, $name, $value, $disabled=false, $large=false) {
	echo "<div class='sf-form-row'>\n";
	if ($large) {
		echo "<label class='sp-label-40'>\n";
	} else {
		echo "<label class='sp-label-60'>\n";
	}
	echo esc_html($label) . "</label>";
	$c = ($large) ? 'sp-input-60' : 'sp-input-40';

	echo "<input type='number' class='wp-core-ui ".esc_attr($c)."' name='".esc_attr($name)."' value='".esc_attr($value)."' ";
	if ($disabled == true) echo "disabled='disabled' ";
	echo "/>\n";
	//echo '<div class="clearboth"></div>';
	echo '</div>';
}

function spa_paint_textarea($label, $name, $value, $submessage='', $rows=5, $placeholder=''): void
{
	echo "<div class='sf-form-row'>\n";
	echo "<label>\n";
	echo esc_html($label);
	echo '</label>';
	echo "<textarea rows='".esc_attr($rows)."' class='wp-core-ui' name='".esc_attr($name)."'>".esc_html($value)."</textarea>\n";
    if (!empty($submessage)) {
        echo '<span class="sf-sublabel sf-sublabel-small">' . esc_html($submessage) . '</span>';
    }
	echo '</div>';
}

function spa_paint_textarea_editor($label, $name, $value, $submessage='', $rows=5, $placeholder=''): void
{
	echo "<div class='sf-form-row html-email'>\n";
	echo "<a href='#' class='element-switcher' data-editor-id='".esc_attr($name)."'>Switch to Text Mode</a>"; // Add switcher
	echo "<label>\n";
	echo esc_html($label);
	echo '</label>';
	echo "<textarea id='".esc_attr($name)."' rows='".esc_attr($rows)."' class='wp-core-ui' name='".esc_attr($name)."'>".esc_html($value)."</textarea>\n";
    if (!empty($submessage)) {
        echo '<span class="sf-sublabel sf-sublabel-small">' . esc_html($submessage) . '</span>';
    }

	echo '</div>';
}

function spa_paint_wide_textarea_editor($label, $name, $value, $submessage='', $rows=5, $placeholder='') {
    spa_paint_textarea_editor($label, $name, $value, $submessage, $rows, $placeholder);
}

function spa_paint_wide_textarea($label, $name, $value, $submessage='', $rows=5, $placeholder='') {
    spa_paint_textarea($label, $name, $value, $submessage, $rows, $placeholder);
}

function spa_paint_thin_textarea($label, $name, $value, $submessage='', $rows=1) {
    spa_paint_textarea($label, $name, $value, $submessage, $rows, '');
}

function spa_paint_editor($label, $name, $value, $submessage='', $xrows=1) {
	echo "<div class='sf-form-row'>\n";
	echo "<label class='sp-label-50'>\n";
	echo esc_html($label);
	if (!empty($submessage)) echo "<br /><small><strong>".esc_html($submessage)."</strong></small>\n";
	echo '</label>';
	wp_editor( html_entity_decode($value), $name, array(
					'media_buttons' => false,
					'quicktags'     => true,
					'textarea_rows' => $xrows
				));
	echo '</div>';
}

function spa_paint_wide_editor($label, $name, $value, $submessage='', $xrows=1, $mediaButtons = false) {
	add_filter( 'tiny_mce_before_init', 'spa_cache_ajax_editor_settings', 11, 2 );

	echo "<div class='sf-form-row'>\n";
	echo "<label>\n";
	echo esc_html($label);
	if (!empty($submessage)) echo "<small><br /><strong>".esc_html($submessage)."</strong><br /><br /></small>\n";
	echo '</label>';
	wp_editor( html_entity_decode( $value ), $name, array(
					'media_buttons' => (bool) $mediaButtons,
					'quicktags'     => true,
					'textarea_rows' => $xrows
				));

	echo '</div>';
}

function spa_paint_wide_editor_custom($label, $name, $value, $submessage='', $xrows=1, $mediaButtons = false) {
	add_filter( 'tiny_mce_before_init', 'spa_cache_ajax_editor_settings', 11, 2 );

	echo "<div class='sf-form-row'>\n";
	echo "<label>\n";
	echo esc_html($label);
	if (!empty($submessage)) echo "<small><br /><strong>".esc_html($submessage)."</strong></small>\n";
	echo '</label>';
      if (is_array($value) || is_object($value)) {
          error_log(print_r($value, true));
      } else {
          error_log($value);
  }

	wp_editor( html_entity_decode( $value ), $name, array(
                    'textarea_name' => $name,
					'media_buttons' => (bool) $mediaButtons,
					'quicktags'     => true,
					'textarea_rows' => $xrows,
					'tinymce'       => array(
						'toolbar1'      => 'formatselect bold italic underline | bullist numlist blockquote | alignleft aligncenter alignright | link unlink | forecolor backcolor',
					),
                    'editor_height' => 300
				));

	echo '</div>';
}

function spa_paint_thin_editor($label, $name, $value, $submessage='', $xrows=1) {
	echo "<div class='sf-form-row'>\n";
	echo "<label class='sp-label-66'>\n";
	echo esc_html($label);
	if (!empty($submessage)) echo "<small><br /><strong>".esc_html($submessage)."</strong><br /><br /></small>\n";
	echo '</label>';
	wp_editor( html_entity_decode($value), $name, array(
					'media_buttons' => false,
					'quicktags'     => true,
					'textarea_rows' => $xrows
				));
	echo '</div>';
}

function spa_print_ajax_editor_settings() {
	global $spa_cache_ajax_editor_settings;

	if( !$spa_cache_ajax_editor_settings || !is_array( $spa_cache_ajax_editor_settings ) || empty( $spa_cache_ajax_editor_settings ) ) {
		return;
	}

	?>
	<script type="text/javascript">

			var spa_mceInit = <?php echo json_encode( $spa_cache_ajax_editor_settings ); ?>;

			<?php foreach( $spa_cache_ajax_editor_settings as $editor_id => $editor_setting ) { ?>

				var editor_id = '<?php echo esc_js($editor_id); ?>';

				if( !tinyMCEPreInit.mceInit.hasOwnProperty( editor_id ) ) {
					tinyMCEPreInit.mceInit[ editor_id ] = spa_mceInit[ editor_id ];
					tinyMCEPreInit.mceInit[ editor_id ].formats = <?php echo esc_js($editor_setting['formats']); ?>;
                }

			<?php } ?>

			spa_mceInit = null;

	</script>
	<?php
}

function spa_cache_ajax_editor_settings( $mceInit, $editor_id ) {

	global $spa_cache_ajax_editor_settings;

	$spa_cache_ajax_editor_settings = isset( $spa_cache_ajax_editor_settings ) && is_array( $spa_cache_ajax_editor_settings ) ? $spa_cache_ajax_editor_settings : array();

	$spa_cache_ajax_editor_settings[ $editor_id ] = $mceInit;

	return $mceInit;
}

function spa_paint_css_editor($label, $name, $value, $submessage='', $rows=10) {
	if(floatval(get_bloginfo('version')) >= 4.9) {
		spa_paint_code_editor('text/css', $label, $name, $value, $submessage, $rows);
	} else {
		spa_paint_wide_textarea($label, $name, $value, $submessage, $rows);
	}
}

function spa_paint_js_editor($label, $name, $value, $submessage='', $rows=10) {
	if(floatval(get_bloginfo('version')) >= 4.9) {
		spa_paint_code_editor('text/javascript', $label, $name, $value, $submessage, $rows);
	} else {
		spa_paint_wide_textarea($label, $name, $value, $submessage, $rows);
	}
}

function spa_paint_html_editor($label, $name, $value, $submessage='', $rows=10) {
	if(floatval(get_bloginfo('version')) >= 4.9) {
		spa_paint_code_editor('text/html', $label, $name, $value, $submessage, $rows);
	} else {
		spa_paint_wide_textarea($label, $name, $value, $submessage, $rows);
	}
}

function spa_paint_code_editor($type, $label, $name, $value, $submessage='', $rows=10) {

	spa_enqueue_codemirror();

    echo "<div class='sf-form-row'>\n";
    echo "<label>\n";
    if(mb_strlen($label)) {
        echo esc_html($label);
    }
    if (mb_strlen($submessage)) {
        echo "<small><br /><strong>".esc_html($submessage)."</strong><br /><br /></small>\n";
    }
    $id = sprintf("sp-%s-editor-%d", str_replace('/', '-', $type), 0);
    echo '</label>';
    echo sprintf("<textarea id=\"%s\" class=\"wp-core-ui sp-textarea\" rows=\"%s\" name=\"%s\">%s</textarea>", esc_attr($id), esc_attr($rows), esc_attr($name), esc_html($value));
    if(floatval(get_bloginfo('version')) >= 4.9) {
        echo "<script>";
        echo sprintf( "jQuery( function() { 
                        var instance = wp.codeEditor.initialize( '".esc_js($id)."', %s );
                        instance.codemirror.on('blur', function() {instance.codemirror.save();});                         
                    });", wp_json_encode(wp_enqueue_code_editor(array('type' => esc_html($type)))) ) ;
        echo "</script>";
    }
    echo '</div>';
}


function spa_paint_checkbox($label, $name, $value, $disabled=false, $large=false, $displayhelp=true, $msg='', $indent=false) {
	echo "<div class='sf-form-row'>\n";
	if ($indent) echo esc_html(str_repeat('&nbsp;', 7));
	echo "<input type='checkbox' name='" . esc_attr($name) ."' id='sf-" . esc_attr($name) ."' ";
	if ($value == true) echo "checked='checked' ";
	if ($disabled == true) echo "disabled='disabled' ";
	echo "/>\n";
	echo "<label for='sf-" . esc_attr($name) ."' class='wp-core-ui'>" . esc_html($label). "</label>\n";
    if ($msg) {
        echo wp_kses(
            $msg,
            [
                'span' => [
                    'class' => []
                ]
            ]
        );
    }
	echo '</div>';
}

function spa_paint_select_start($label, $name, $helpname) {
	echo "<div class='sf-form-row'>\n";
	echo "<label class='sp-label-40'>" . esc_html($label) . "</label>\n";
	echo "<select class='wp-core-ui' name='" . esc_attr($name) ."'>";
}

function spa_paint_select_end($msg='') {
	echo "</select>\n";
	if ($msg) {
        echo wp_kses(
            $msg,
            [
                'small' => [],
                'span' => [
                    'class' => true,
                ]
            ]
        );
    }
	echo '</div>';
}

function spa_paint_file($label, $name, $disabled, $large, $path) {
	echo "<div class='sf-upload sf-icon-button'>\n";
	if ($large) {
		echo "<label class='sp-label-40'>\n";
	} else {
		echo "<label class='sp-label-60'>\n";
	}
	echo esc_attr($label) . "</label>";

	echo '<div id="sf-upload-button"><span class="sf-icon sf-upload"></span></div>';
	echo '<div id="sf-upload-status">';
	if (!wp_is_writable($path)) {
		echo '<p class="sf-upload-status-fail">'.esc_html(SP()->primitives->admin_text('Sorry, uploads disabled! Storage location does not exist or is not writable. Please see forum - integration - storage locations to correct')).'</p>';
	}
	echo '</div>';
	echo '</div>';
}

function spa_paint_hidden_input($name, $value) {
	echo '<div class="sfhidden">';
	echo "<input type='hidden' name='".esc_attr($name)."' value='".esc_attr($value)."' />";
	echo '</div>';
}


function spa_paint_link($link, $label) {
	echo "<span class='wp-core-ui sp-label'>";
	echo "<a href='".esc_url($link)."'>" . esc_html($label) . "</a>\n";
	echo '</span>';
}

function spa_paint_radiogroup($label, $name, $values, $current, $large=false, $displayhelp=true, $class='') {
    $tab = 0;
	if ($class != '') $class=' class="'.$class.'" ';

	echo "<div class='sf-form-row'>\n";
	echo "<h4>" . esc_html($label) . "</h4>\n";
	echo "<div class='wp-core-ui sp-radio'>";

	foreach ($values as $key => $value) {
	    $pos = $key + 1;
		$check = '';
		if ($current == $pos) $check = ' checked="checked" ';
		echo '<input type="radio" '.esc_attr($class).'name="'.esc_attr($name).'" id="sfradio-'.esc_attr($tab).'"  tabindex="'.esc_attr($tab).'" value="'.esc_attr($pos).'" '.esc_attr($check).' />'."\n";
		echo '<label for="sfradio-'.esc_attr($tab).'" class="wp-core-ui">'.esc_html(SP()->primitives->admin_text($value)).'</label>'."\n<br />";
		$tab++;
	}
	echo '</div>';
	echo '</div>';
}

function spa_paint_spacer() { // @TODO admin design
	echo '';
}

function spa_paint_help($name, $helpfile = null, $show=true) {
    if(is_null($helpfile)) {
        global $adminhelpfile;
        $helpfile = $adminhelpfile;
    }
	$site = wp_nonce_url(SPAJAXURL."help&amp;file=$helpfile&amp;item=$name", 'help');

	$title = (!spa_saas_check() && !spa_white_label_check())
		? SP()->primitives->admin_text('Simple:Press Help')
		: SP()->primitives->admin_text('Forum Help');
    
	$out = '';

	if ($show) {
		$out.= '<a id="'.esc_attr($name).'" class="sf-icon-button sfhelplink spHelpLink" data-site="'.esc_attr($site).'" data-label="'.esc_attr($title).'" data-width="600" data-height="0" data-align="center">';
		$out.= '<span class="sf-icon sf-help"></span></a>';
	}
    
	echo wp_kses(
            $out,
            [
                'a' => [
                    'id' => true,
                    'class' => true,
                    'data-site' => true,
                    'data-label' => true,
                    'data-width' => true,
                    'data-height' => true,
                    'data-align' => true,
                ],
                'span' => [
                    'class' => true,
                ]
            ]
        );
}

/**
 * Load style and scripts for WP Code Mirror
 *
 * @return void
 */
function spa_enqueue_codemirror() {
	if(floatval(get_bloginfo('version')) >= 4.9) {
		wp_enqueue_style( 'code-editor' );
		wp_enqueue_script( 'code-editor' );
		wp_enqueue_script( 'htmlhint' );
		wp_enqueue_script( 'csslint' );
		wp_enqueue_script( 'jshint' );
	}
}
spa_enqueue_codemirror();  // @TODO: This loads the script globally which is not really what we want - ideally this would load only when its needed.
