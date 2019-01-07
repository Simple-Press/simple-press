<?php
/*
Simple:Press
Topic/Post Form Component Rendering
$LastChangedDate: 2016-11-05 14:40:56 -0500 (Sat, 05 Nov 2016) $
$Rev: 14705 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# Display the add topic/post form components inline or within the unified toolbar
#
# --------------------------------------------------------------------------------------

# ----------------------------------
# Begin the toolbar filter functions
# ----------------------------------

global $spGlobals;
$toolbar = false;
if (!empty($spGlobals['display']['editor']['toolbar'])) {
	$toolbar = $spGlobals['display']['editor']['toolbar'];
}

if ($toolbar) {
	add_filter('sph_topic_editor_toolbar_submit',	     'sp_topic_editor_submit_buttons', 1, 4);
	add_filter('sph_post_editor_toolbar_submit', 	     'sp_post_editor_submit_buttons', 1, 4);
	add_filter('sph_post_editor_edit_toolbar_submit',	 'sp_post_editor_edit_submit_buttons', 1, 4);
	add_filter('sph_topic_editor_above_toolbar', 	     'sp_topic_editor_section_math', 1, 3);
	add_filter('sph_post_editor_above_toolbar', 	     'sp_post_editor_section_math', 1, 3);
	add_filter('sph_topic_editor_toolbar_buttons',	     'sp_topic_editor_default_buttons', 1, 4);
	add_filter('sph_topic_editor_toolbar',			     'sp_topic_editor_smileys_options', 1, 4);
	add_filter('sph_post_editor_toolbar_buttons',	     'sp_post_editor_default_buttons', 1, 4);
	add_filter('sph_post_editor_toolbar',			     'sp_post_editor_smileys_options', 1, 4);
} else {
	add_filter('sp_topic_editor_inline_submit', 	     'sp_topic_editor_submit_buttons', 1, 4);
	add_filter('sp_post_editor_inline_submit', 		     'sp_post_editor_submit_buttons', 1, 4);
	add_filter('sp_post_editor_edit_inline_submit',      'sp_post_editor_edit_submit_buttons', 1, 4);
	add_filter('sph_topic_editor_submit_top', 		     'sp_topic_editor_section_math', 1, 3);
	add_filter('sph_post_editor_submit_top', 		     'sp_post_editor_section_math', 1, 3);
	add_filter('sp_topic_editor_inline_footer',		     'sp_topic_editor_smileys_options', 1, 4);
	add_filter('sp_post_editor_inline_footer',		     'sp_post_editor_smileys_options', 1, 4);
}

# ----------------------------------
# Topic Form Submit section
# ----------------------------------
function sp_topic_editor_submit_buttons($out, $spThisForum, $a, $toolbar) {
	global $spDevice, $tab;

	extract($a, EXTR_SKIP);

    # sanitize
	$controlSubmit		    = esc_attr($controlSubmit);
	$controlOrder		    = esc_attr($controlOrder);
	$labelPostButtonReady	= sp_filter_title_display($labelPostButtonReady);
	$labelPostButtonMath	= sp_filter_title_display($labelPostButtonMath);
	$labelPostCancel		= sp_filter_title_display($labelPostCancel);
	$tipSubmitButton		= esc_attr($tipSubmitButton);
	$tipCancelButton		= esc_attr($tipCancelButton);

	if (sp_get_auth('bypass_math_question', $spThisForum->forum_id) ? $usemath = false : $usemath = true);
	$cOrder = (isset($controlOrder)) ? explode('|', $controlOrder) : array('save', 'cancel');
	$enabled = ' ';
	if ($usemath) $enabled = 'disabled="disabled"';
	if ($toolbar == 'toolbar' ? $class=' spRight' :  $class='');
	$buttontext = $labelPostButtonReady;
	if ($usemath) $buttontext = $labelPostButtonMath;
    $buttontext = apply_filters('sph_topic_editor_button_text', $buttontext, $a);
    $enabled = apply_filters('sph_topic_editor_button_enable', $enabled, $a);

	if ($toolbar=='inline') $out.= '<div class="spEditorSubmit">'."\n";
	$out.= "<div class='spEditorSubmitButton$class'>\n";

	# let plugins add stuff to editor controls
	$out = apply_filters('sph_topic_editor_controls', $out, $spThisForum, $a, $toolbar);

	foreach ($cOrder as $c) {
		switch($c) {
			case 'save':
    			if ($spDevice == 'mobile' && array_key_exists('iconMobileSubmit', $a) && !empty($a['iconMobileSubmit'])) {
    				# display mobile icon
    				$out.= "<button type='submit' tabindex='".$tab++."' style='background:transparent;' class='spIcon' name='newtopic' id='sfsave' />";
    				$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileSubmit, '');
    				$out.= "</button>";
    			} else {
    				# display default button
    				$out.= "<input type='submit' tabindex='".$tab++."' $enabled class='$controlSubmit' title='$tipSubmitButton' name='newtopic' id='sfsave' value='$buttontext' />\n";
    			}
    			break;

			case 'cancel':
                if ($hide) {
        			$msg = esc_attr(sp_text('Are you sure you want to cancel?'));
        			if ($spDevice == 'mobile' && array_key_exists('iconMobileCancel', $a) && !empty($a['iconMobileCancel'])) {
        				# display mobile icon
        				$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spCancelEditor' name='cancel' id='sfcancel' data-msg='$msg'>\n";
        				$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileCancel, '');
        				$out.= "</button>";
        			} else {
        				# display default button
        				$out.= "<input type='button' tabindex='".$tab++."' class='$controlSubmit spCancelEditor' title='$tipCancelButton' id='sfcancel' name='cancel' value='$labelPostCancel' data-msg='$msg'/>\n";
        			}
                }
    			break;

            default:
		}
	}

	$out.= '</div>'."\n";
	if ($toolbar=='inline') $out.= '</div>'."\n";

	return $out;
}

# ----------------------------------
# Post Form Submit section
# ----------------------------------
function sp_post_editor_submit_buttons($out, $spThisTopic, $a, $toolbar) {
	global $tab, $spDevice;

	extract($a, EXTR_SKIP);

    # sanitize
	$controlSubmit		    = esc_attr($controlSubmit);
	$controlOrder		    = esc_attr($controlOrder);
	$labelPostButtonReady	= sp_filter_title_display($labelPostButtonReady);
	$labelPostButtonMath	= sp_filter_title_display($labelPostButtonMath);
	$labelPostCancel		= sp_filter_title_display($labelPostCancel);
	$tipSubmitButton		= esc_attr($tipSubmitButton);
	$tipCancelButton		= esc_attr($tipCancelButton);

	if (sp_get_auth('bypass_math_question', $spThisTopic->forum_id) ? $usemath = false : $usemath = true);
	$cOrder = (isset($controlOrder)) ? explode('|', $controlOrder) : array('save', 'cancel');
	$enabled = ' ';
	if ($usemath) $enabled = 'disabled="disabled"';
	if ($toolbar == 'toolbar' ? $class=' spRight' :  $class='');
	$buttontext = $labelPostButtonReady;
	if ($usemath) $buttontext = $labelPostButtonMath;
    $buttontext = apply_filters('sph_post_editor_button_text', $buttontext, $a);
    $enabled = apply_filters('sph_post_editor_button_enable', $enabled, $a);

	if ($toolbar=='inline') $out.= '<div class="spEditorSubmit">'."\n";
	$out.= "<div class='spEditorSubmitButton$class'>\n";

	# let plugins add stuff to editor controls
	$out = apply_filters('sph_post_editor_controls', $out, $spThisTopic, $a, $toolbar);

	foreach ($cOrder as $c) {
		switch($c) {
			case 'save':
    			if ($spDevice == 'mobile' && array_key_exists('iconMobileSubmit', $a) && !empty($a['iconMobileSubmit'])) {
    				# display mobile icon
    				$out.= "<button type='submit' tabindex='".$tab++."' style='background:transparent;' class='spIcon' name='newpost' id='sfsave' />";
    				$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileSubmit, '');
    				$out.= "</button>";
    			} else {
    				# display default button
    				$out.= "<input type='submit' $enabled tabindex='".$tab++."' class='$controlSubmit' title='$tipSubmitButton' name='newpost' id='sfsave' value='$buttontext' />\n";
    			}
    			break;

			case 'cancel':
                if ($hide) {
        			$msg = esc_attr(sp_text('Are you sure you want to cancel?'));
        			if ($spDevice == 'mobile' && array_key_exists('iconMobileCancel', $a) && !empty($a['iconMobileCancel'])) {
        				# display mobile icon
        				$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spCancelEditor' name='cancel' id='sfcancel' data-msg='$msg'>\n";
        				$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileCancel, '');
        				$out.= "</button>";
        			} else {
        				# display default button
        				$out.= "<input type='button' tabindex='".$tab++."' class='$controlSubmit spCancelEditor' title='$tipCancelButton' id='sfcancel' name='cancel' value='$labelPostCancel' data-msg='$msg' />\n";
        			}
                }
                break;

            default:
		}
	}

	$out.= '</div>'."\n";
	if ($toolbar == 'inline') $out.= '</div>'."\n";

	return $out;
}

# ----------------------------------
# Post Edit Form Submit section
# ----------------------------------
function sp_post_editor_edit_submit_buttons($out, $spThisTopic, $a, $toolbar) {
	global $tab, $spDevice;

	extract($a, EXTR_SKIP);

    # sanitize
	$controlSubmit		    = esc_attr($controlSubmit);
	$controlOrder		    = esc_attr($controlOrder);
	$labelPostButton       	= sp_filter_title_display($labelPostButton);
	$labelPostCancel		= sp_filter_title_display($labelPostCancel);
	$tipSubmitButton		= esc_attr($tipSubmitButton);
	$tipCancelButton		= esc_attr($tipCancelButton);

	$cOrder = (isset($controlOrder)) ? explode('|', $controlOrder) : array('save', 'cancel');
	if ($toolbar == 'toolbar' ? $class=' spRight' :  $class='');
	if ($toolbar=='inline') $out.= '<div class="spEditorSubmit">'."\n";
	$out.= "<div class='spEditorSubmitButton$class'>\n";

	# let plugins add stuff to editor controls
	$out = apply_filters('sph_post_editor_controls', $out, $spThisTopic, $a, $toolbar);

	foreach ($cOrder as $c) {
		switch($c) {
			case 'save':
    			if ($spDevice == 'mobile' && array_key_exists('iconMobileSubmit', $a) && !empty($a['iconMobileSubmit'])) {
    				# display mobile icon
    				$out.= "<button type='submit' tabindex='".$tab++."' style='background:transparent;' class='spIcon' name='editpost' id='sfsave' />";
    				$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileSubmit, '');
    				$out.= "</button>";
    			} else {
    				# display default button
    				$out.= "<input type='submit' tabindex='".$tab++."' class='$controlSubmit' title='$tipSubmitButton' name='editpost' id='sfsave' value='$labelPostButton' />\n";
    			}
    			break;

			case 'cancel':
    			$msg = esc_attr(sp_text('Are you sure you want to cancel?'));
    			if ($spDevice == 'mobile' && array_key_exists('iconMobileCancel', $a) && !empty($a['iconMobileCancel'])) {
    				# display mobile icon
    				$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spCancelEditor' name='cancel' id='sfcancel' data-msg='$msg'>\n";
    				$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileCancel, '');
    				$out.= "</button>";
    			} else {
    				# display default button
    				$out.= "<input type='button' tabindex='".$tab++."' class='$controlSubmit spProcessFlag' title='$tipCancelButton' id='sfcancel' name='cancel' value='$labelPostCancel' data-msg='$msg' />\n";
    			}
    			break;

            default:
		}
	}

	$out.= '</div>'."\n";
	if ($toolbar=='inline') $out.= '</div>'."\n";

	return $out;
}

# ----------------------------------
# Math Spam Section - Topic
# ----------------------------------
function sp_topic_editor_section_math($out, $spThisForum, $a) {
	global $tab;
	# Start Spam Measures
	if (sp_get_auth('bypass_math_question', $spThisForum->forum_id) ? $usemath = false : $usemath = true);
	if ($usemath) {
		extract($a, EXTR_SKIP);

        # sanitize
    	$controlInput		    = esc_attr($controlInput);
    	$controlSubmit		    = esc_attr($controlSubmit);
    	$labelMath		        = sp_filter_title_display($labelMath);
    	$labelMathSum		    = sp_filter_title_display($labelMathSum);
    	$labelPostButtonReady	= sp_filter_title_display($labelPostButtonReady);
    	$labelPostButtonMath	= sp_filter_title_display($labelPostButtonMath);
    	$labelPostCancel		= sp_filter_title_display($labelPostCancel);

		$out.= '<div class="spEditorSubmit">'."\n";
		$out.= '<div class="spInlineSection">'."\n";
		$out.= 'Guest URL (required)<br />'."\n";
		$out.= "<input type='text' tabindex='".$tab++."' class='$controlInput' size='30' name='url' value='' />\n";
		$out.= "</div>\n";

		$spammath = sp_math_spam_build();
		$uKey = sp_get_option('spukey');
		$uKey1 = $uKey.'1';
		$uKey2 = $uKey.'2';
		$out.= "<div class='spEditorTitle'>$labelMath</div>\n";
		$out.= "<div class='spEditorSpam'>$labelMathSum:</div>\n";
		$out.= "<div class='spEditorSpam'>$spammath[0] + $spammath[1]</div>\n";
		$out.= "<div class='spEditorSpam'>\n";
		$out.= "<input type='text' tabindex='".$tab++."' class='$controlInput spMathCheck' size='20' name='$uKey1' id='$uKey1' value='' data-type='topic' data-val1='$spammath[0]', data-val2='$spammath[1]' data-buttongood='$labelPostButtonReady' data-buttonbad='$labelPostButtonMath' />\n";
		$out.= "<input type='hidden' name='$uKey2' value='$spammath[2]' />\n";
		$out.= "</div></div>\n";
	}
	# End Spam Measures
	return $out;
}

# ----------------------------------
# Math Spam Section - Post
# ----------------------------------
function sp_post_editor_section_math($out, $spThisData, $a) {
	# Start Spam Measures
	if (sp_get_auth('bypass_math_question', $spThisData->forum_id) ? $usemath = false : $usemath = true);
	if ($usemath) {
		extract($a, EXTR_SKIP);

        # sanitize
    	$controlInput		    = esc_attr($controlInput);
    	$labelMath		        = sp_filter_title_display($labelMath);
    	$labelMathSum		    = sp_filter_title_display($labelMathSum);
    	$labelPostButtonReady	= sp_filter_title_display($labelPostButtonReady);
    	$labelPostButtonMath	= sp_filter_title_display($labelPostButtonMath);

		$out.= '<div class="spEditorSubmit">'."\n";
		$out.= '<div class="spInlineSection">'."\n";
		$out.= 'Guest URL (required)<br />'."\n";
		$out.= "<input type='text' class='$controlInput' size='30' name='url' value='' />\n";
		$out.= "</div>\n";

		$spammath = sp_math_spam_build();
		$uKey = sp_get_option('spukey');
		$uKey1 = $uKey.'1';
		$uKey2 = $uKey.'2';
		$out.= "<div class='spEditorTitle'>$labelMath</div>\n";
		$out.= "<div class='spEditorSpam'>$labelMathSum:</div>\n";
		$out.= "<div class='spEditorSpam'>$spammath[0] + $spammath[1]</div>\n";
		$out.= "<div class='spEditorSpam'>\n";
		$out.= "<input type='text' tabindex='105' class='$controlInput spMathCheck' size='20' name='$uKey1' id='$uKey1' value='' data-type='post' data-val1='$spammath[0]', data-val2='$spammath[1]' data-buttongood='$labelPostButtonReady' data-buttonbad='$labelPostButtonMath' />\n";
		$out.= "<input type='hidden' name='$uKey2' value='$spammath[2]' />\n";
		$out.= "</div></div>\n";
	}
	# End Spam Measures
	return $out;
}

# ----------------------------------
# Smileys/Options Section - Topic
# ----------------------------------
function sp_topic_editor_default_buttons($out, $spThisForum, $a, $toolbar) {
	global $tab, $spGlobals, $spThisUser, $spDevice;

	extract($a, EXTR_SKIP);

    # sanitize
	$hide			        = (int) $hide;
	$tagClass		        = esc_attr($tagClass);
	$labelSmileys		    = sp_filter_title_display($labelSmileys);
	$labelOptions		    = sp_filter_title_display($labelOptions);
	$tipSmileysButton		= esc_attr($tipSmileysButton);
	$tipOptionsButton		= esc_attr($tipOptionsButton);

	# work out what we need to display
	$display = array();
	$display['smileys'] = false;
	$display['options'] = false;

	if (sp_get_auth('can_use_smileys', $spThisForum->forum_id)) $display['smileys'] = true;
	if (sp_get_auth('lock_topics', $spThisForum->forum_id) ||
	   sp_get_auth('pin_topics', $spThisForum->forum_id) ||
	   $spThisUser->admin ||
	   $spThisUser->moderator) {
	   $display['options'] = true;
	}
    $display = apply_filters('sph_topic_editor_display_options', $display);

	if ($display['smileys']) {
		if ($spDevice == 'mobile' && array_key_exists('iconMobileSmileys', $a) && !empty($a['iconMobileSmileys'])) {
			# display mobile icon
			$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spEditorBoxOpen' name='spSmileysButton' id='spSmileysButton' data-box='spSmileysBox'>\n";
			$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileSmileys, '');
			$out.= "</button>";
		} else {
			# display default button
			$out.= "<input type='button' tabindex='".$tab++."' class='spSubmit spLeft spEditorBoxOpen' title='$tipSmileysButton' id='spSmileysButton' value='$labelSmileys' data-box='spSmileysBox' />";
		}
	}
	if ($display['options']) {
		if ($spDevice == 'mobile' && array_key_exists('iconMobileOptions', $a) && !empty($a['iconMobileOptions'])) {
			# display mobile icon
			$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spEditorBoxOpen' name='spOptionsButton' id='spOptionsButton'  data-box='spOptionsBox'>\n";
			$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileOptions, '');
			$out.= "</button>";
		} else {
			# display default button
		$out.= "<input type='button' tabindex='".$tab++."' class='spSubmit spLeft spEditorBoxOpen' title='$tipOptionsButton' id='spOptionsButton' value='$labelOptions' data-box='spOptionsBox' />";
		}
	}
	return $out;
}

function sp_topic_editor_smileys_options($out, $spThisForum, $a, $toolbar) {
	global $tab, $spGlobals, $spThisUser;

	extract($a, EXTR_SKIP);

    # sanitize
	$controlInput		    = esc_attr($controlInput);
	$labelSmileys		    = sp_filter_title_display($labelSmileys);
	$labelOptions		    = sp_filter_title_display($labelOptions);
	$labelOptionLock		= sp_filter_title_display($labelOptionLock);
	$labelOptionPin		    = sp_filter_title_display($labelOptionPin);
	$labelOptionTime		= sp_filter_title_display($labelOptionTime);

	$smileysBox = '';
	$optionsBox = '';

	# work out what we need to display
	$display = array();
	$display['smileys'] = false;
	$display['options'] = false;

	if (sp_get_auth('can_use_smileys', $spThisForum->forum_id)) $display['smileys'] = true;
	if (sp_get_auth('lock_topics', $spThisForum->forum_id) ||
	   sp_get_auth('pin_topics', $spThisForum->forum_id) ||
	   $spThisUser->admin ||
	   $spThisUser->moderator) {
	   $display['options'] = true;
	}
    $display = apply_filters('sph_topic_editor_display_options', $display);

	# Now start the displays
	$class = ($toolbar=='toolbar') ? ' spInlineSection' : '';

	if ($display['smileys'] || $display['options']) $out.= sp_InsertBreak('echo=0')."<div>\n";

	# Smileys
	if ($display['smileys']) {
		$smileysBox = apply_filters('sph_topic_smileys_display', $smileysBox, $spThisForum, $a);
		if ($display['options'] && $toolbar=='inline') {
			$smileysBox.= "<div id='spSmileysBox' class='spEditorSection spEditorSectionLeft$class'>\n";
		} else {
			$smileysBox.= "<div id='spSmileysBox' class='spEditorSection$class'>\n";
		}
		$smileysBox.= "<div class='spEditorHeading'>$labelSmileys\n";
		$smileysBox = apply_filters('sph_topic_smileys_header_add', $smileysBox, $spThisForum, $a);
		$smileysBox.= '</div>';
		$smileysBox.= '<div class="spEditorSmileys">'."\n";
		$smileysBox.= sp_render_smileys();
		$smileysBox.= '</div>';
		$smileysBox = apply_filters('sph_topic_smileys_add', $smileysBox, $spThisForum, $a);
		if ($toolbar=='toolbar') $smileysBox.= sp_InsertBreak('direction=both&spacer=6px&echo=0');
		$smileysBox.= '</div>'."\n";
	}

	# Options
	if ($display['options']) {
		$optionsBox = apply_filters('sph_topic_options_display', $optionsBox, $spThisForum, $a);
		if ($display['smileys'] && $toolbar=='inline') {
			$optionsBox.= "<div id='spOptionsBox' class='spEditorSection spEditorSectionRight$class'>\n";
		} else {
			$optionsBox.= "<div id='spOptionsBox' class='spEditorSection$class'>\n";
		}
		$optionsBox.= "<div class='spEditorHeading'>$labelOptions\n";
		$optionsBox = apply_filters('sph_topic_options_header_add', $optionsBox, $spThisForum, $a);
		$optionsBox.= '</div>';
		if (sp_get_auth('lock_topics', $spThisForum->forum_id)) {
			$optionsBox.= "<input type='checkbox' tabindex='".$tab++."' class='$controlInput' name='topiclock' id='sftopiclock' />\n";
			$optionsBox.= "<label class='spLabel spCheckbox' for='sftopiclock'>$labelOptionLock</label>\n";
			$optionsBox.= "<br />\n";
		}
		if (sp_get_auth('pin_topics', $spThisForum->forum_id)) {
			$optionsBox.= "<input type='checkbox' tabindex='".$tab++."' class='$controlInput' name='topicpin' id='sftopicpin' />\n";
			$optionsBox.= "<label class='spLabel spCheckbox' for='sftopicpin'>$labelOptionPin</label>\n";
			$optionsBox.= "<br />\n";
		}
		if ($spThisUser->admin) {
			$optionsBox.= "<input type='checkbox' class='$controlInput' tabindex='".$tab++."' id='sfeditTimestamp' name='editTimestamp' />\n";
			$optionsBox.= "<label class='spLabel spCheckbox' for='sfeditTimestamp'>$labelOptionTime</label>\n";
			$optionsBox.= "<br />\n";
		}

		if ($spThisUser->admin) {
			global $wp_locale, $month;
			$time_adj = time() + (get_option('gmt_offset') * 3600);
			$dd = gmdate( 'd', $time_adj );
			$mm = gmdate( 'm', $time_adj );
			$yy = gmdate( 'Y', $time_adj );
			$hh = gmdate( 'H', $time_adj );
			$mn = gmdate( 'i', $time_adj );
			$ss = gmdate( 's', $time_adj );

			$optionsBox.= '<div id="spHiddenTimestamp">'."\n";
			$optionsBox.= "<select class='$controlInput spEditTimestampCheckbox' tabindex='".$tab++."' name='tsMonth'>\n";
			for ($i = 1; $i < 13; $i = $i +1) {
				$optionsBox.= "\t\t\t<option value=\"$i\"";
				if ($i == $mm ) $optionsBox.= " selected='selected'";
				if (class_exists('WP_Locale')) {
					$optionsBox.= '>'.$wp_locale->get_month($i).'</option>';
				} else {
					$optionsBox.= '>'.$month[$i].'</option>';
				}
			}
			$optionsBox.= '</select> ';
			$optionsBox.= "<input class='$controlInput' tabindex='".$tab++."' type='text' id='tsDay' name='tsDay' value='$dd' size='2' maxlength='2'/> \n";
			$optionsBox.= "<input class='$controlInput' tabindex='".$tab++."' type='text' id='tsYear' name='tsYear' value='$yy' size='4' maxlength='5'/> @\n";
			$optionsBox.= "<input class='$controlInput' tabindex='".$tab++."' type='text' id='tsHour' name='tsHour' value='$hh' size='2' maxlength='2'/> :\n";
			$optionsBox.= "<input class='$controlInput' tabindex='".$tab++."' type='text' id='tsMinute' name='tsMinute' value='$mn' size='2' maxlength='2'/> \n";
			$optionsBox.= "<input class='$controlInput' tabindex='".$tab++."' type='hidden' id='tsSecond' name='tsSecond' value='$ss' /> \n";
			$optionsBox.= "</div>";
		}

		$optionsBox = apply_filters('sph_topic_options_add', $optionsBox, $spThisForum, $a);
		if ($toolbar=='toolbar') {
			$optionsBox.= sp_InsertBreak('direction=both&spacer=6px&echo=0');
		} else {
			$optionsBox.= sp_InsertBreak('echo=0');
		}
		$optionsBox.= '</div>'."\n";
	}
	if ($display['smileys'] || $display['options']) {
		$out.= $smileysBox.$optionsBox;
		$out.= '</div>';
	}

	return $out;
}

# ----------------------------------
# Smileys/Options Section - Post
# ----------------------------------

function sp_post_editor_default_buttons($out, $spThisTopic, $a, $toolbar) {
	global $tab, $spGlobals, $spVars, $spThisUser, $spDevice;

	extract($a, EXTR_SKIP);

    # sanitize
	$labelSmileys		    = sp_filter_title_display($labelSmileys);
	$labelOptions		    = sp_filter_title_display($labelOptions);
	$tipSmileysButton		= esc_attr($tipSmileysButton);
	$tipOptionsButton		= esc_attr($tipOptionsButton);

	# work out what we need to display
	$display = array();
	$display['smileys'] = false;
	$display['options'] = false;

	if (sp_get_auth('can_use_smileys', $spThisTopic->forum_id)) $display['smileys'] = true;
	if (sp_get_auth('lock_topics', $spThisTopic->forum_id) ||
		   sp_get_auth('pin_posts', $spThisTopic->forum_id) ||
		   $spThisUser->admin ||
		   $spThisUser->moderator) {
		   $display['options'] = true;
	}
	$display = apply_filters('sph_post_editor_display_options', $display);

	if ($display['smileys']) {
		if ($spDevice == 'mobile' && array_key_exists('iconMobileSmileys', $a) && !empty($a['iconMobileSmileys'])) {
			# display mobile icon
			$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spEditorBoxOpen' name='spSmileysButton' id='spSmileysButton' data-box='spSmileysBox'>\n";
			$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileSmileys, '');
			$out.= "</button>";
		} else {
			# display default button
			$out.= "<input type='button' tabindex='".$tab++."' class='spSubmit spLeft spEditorBoxOpen' title='$tipSmileysButton' id='spSmileysButton' value='$labelSmileys' data-box='spSmileysBox' />";
		}
	}

	if ($display['options']) {
		if ($spDevice == 'mobile' && array_key_exists('iconMobileOptions', $a) && !empty($a['iconMobileOptions'])) {
			# display mobile icon
			$out.= "<button type='button' tabindex='".$tab++."' style='background:transparent;' class='spIcon spEditorBoxOpen' name='spOptionsButton' id='spOptionsButton' data-box='spOptionsBox'>\n";
			$out.= sp_paint_icon('spIcon', SPTHEMEICONSURL, $iconMobileOptions, '');
			$out.= "</button>";
		} else {
			# display default button
		$out.= "<input type='button' tabindex='".$tab++."' class='spSubmit spLeft spEditorBoxOpen' title='$tipOptionsButton' id='spOptionsButton' value='$labelOptions' data-box='spOptionsBox' />";
		}
	}

	return $out;
}

function sp_post_editor_smileys_options($out, $spThisTopic, $a, $toolbar) {
	global $spGlobals, $spVars, $spThisUser;

	extract($a, EXTR_SKIP);

    # sanitize
	$controlInput		    = esc_attr($controlInput);
	$labelSmileys		    = sp_filter_title_display($labelSmileys);
	$labelOptions		    = sp_filter_title_display($labelOptions);
	$labelOptionTime		= sp_filter_title_display($labelOptionTime);

	$smileysBox = '';
	$optionsBox = '';

	# work out what we need to display
	$display = array();
	$display['smileys'] = false;
	$display['options'] = false;

	if (sp_get_auth('can_use_smileys', $spThisTopic->forum_id)) $display['smileys'] = true;
	if (((sp_get_auth('lock_topics', $spThisTopic->forum_id) || sp_get_auth('pin_posts', $spThisTopic->forum_id)) && $spVars['displaymode'] != 'edit') ||
		   $spThisUser->admin ||
		   $spThisUser->moderator) {
		   $display['options'] = true;
	}
	$display = apply_filters('sph_post_editor_display_options', $display);

	# Now start the displays
	$class = ($toolbar=='toolbar') ? ' spInlineSection' : '';

	if ($display['smileys'] || $display['options']) $out.= sp_InsertBreak('echo=0')."<div>\n";

	# Smileys
	if ($display['smileys']) {
		$smileysBox = apply_filters('sph_post_smileys_display', $smileysBox, $spThisTopic, $a);
		if ($display['options'] && $toolbar=='inline') {
			$smileysBox.= "<div id='spSmileysBox' class='spEditorSection spEditorSectionLeft$class'>\n";
		} else {
			$smileysBox.= "<div id='spSmileysBox' class='spEditorSection$class'>\n";
		}
		$smileysBox.= "<div class='spEditorHeading'>$labelSmileys\n";
		$smileysBox = apply_filters('sph_post_smileys_header_add', $smileysBox, $spThisTopic, $a);
		$smileysBox.= '</div>';
		$smileysBox.= '<div class="spEditorSmileys">'."\n";
		$smileysBox.= sp_render_smileys();
		$smileysBox.= '</div>';
		$smileysBox = apply_filters('sph_post_smileys_add', $smileysBox, $spThisTopic, $a);
		if ($toolbar=='toolbar') $smileysBox.= sp_InsertBreak('direction=both&spacer=6px&echo=0');
		$smileysBox.= '</div>'."\n";
	}

	# Options
	if ($display['options']) {
		$optionsBox = apply_filters('sph_post_options_display', $optionsBox, $spThisTopic, $a);
		if ($display['smileys'] && $toolbar=='inline') {
			$optionsBox.= "<div id='spOptionsBox' class='spEditorSection spEditorSectionRight$class'>\n";
		} else {
			$optionsBox.= "<div id='spOptionsBox' class='spEditorSection$class'>\n";
		}
		$optionsBox.= "<div class='spEditorHeading'>$labelOptions\n";
		$optionsBox = apply_filters('sph_post_options_header_add', $optionsBox, $spThisTopic, $a);
		$optionsBox.= '</div>';
		if (($spVars['displaymode'] != 'edit')) {
        	$labelOptionLock = sp_filter_title_display($labelOptionLock);
            $labelOptionPin	= sp_filter_title_display($labelOptionPin);
    		if (sp_get_auth('lock_topics', $spThisTopic->forum_id)) {
    			$optionsBox.= "<input type='checkbox' class='$controlInput' name='topiclock' id='sftopiclock' tabindex='110' />\n";
    			$optionsBox.= "<label class='spLabel spCheckbox' for='sftopiclock'>$labelOptionLock</label>\n";
    			$optionsBox.= "<br />\n";
    		}
    		if (sp_get_auth('pin_topics', $spThisTopic->forum_id)) {
    			$optionsBox.= "<input type='checkbox' class='$controlInput' name='postpin' id='sfpostpin' tabindex='111' />\n";
    			$optionsBox.= "<label class='spLabel spCheckbox' for='sfpostpin'>$labelOptionPin</label>\n";
    			$optionsBox.= "<br />\n";
    		}
        }

		if ($spThisUser->admin) {
			$optionsBox.= "<input type='checkbox' class='$controlInput' tabindex='112' id='sfeditTimestamp' name='editTimestamp' />\n";
			$optionsBox.= "<label class='spLabel spCheckbox' for='sfeditTimestamp'>$labelOptionTime</label>\n";
   			$optionsBox.= "<br />\n";
		}

		if ($spThisUser->admin) {
			global $wp_locale, $month, $spThisPost;

   			$time_adj = time() + (get_option('gmt_offset') * 3600);
			$dd = gmdate( 'd', $time_adj );
			$mm = gmdate( 'm', $time_adj );
			$yy = gmdate( 'Y', $time_adj );
			$hh = gmdate( 'H', $time_adj );
			$mn = gmdate( 'i', $time_adj );
			$ss = gmdate( 's', $time_adj );

			$optionsBox.= '<div id="spHiddenTimestamp">'."\n";
			$optionsBox.= "<select class='$controlInput' tabindex='114' name='tsMonth'>\n";
			for ($i = 1; $i < 13; $i = $i +1) {
				$optionsBox.= "\t\t\t<option value=\"$i\"";
				if ($i == $mm ) $optionsBox.= " selected='selected'";
				if (class_exists('WP_Locale')) {
					$optionsBox.= '>'.$wp_locale->get_month($i).'</option>';
				} else {
					$optionsBox.= '>'.$month[$i].'</option>';
				}
			}
			$optionsBox.= '</select> ';
			$optionsBox.= "<input class='$controlInput' tabindex='115' type='text' id='tsDay' name='tsDay' value='$dd' size='2' maxlength='2'/> \n";
			$optionsBox.= "<input class='$controlInput' tabindex='116' type='text' id='tsYear' name='tsYear' value='$yy' size='4' maxlength='5'/> @\n";
			$optionsBox.= "<input class='$controlInput' tabindex='117' type='text' id='tsHour' name='tsHour' value='$hh' size='2' maxlength='2'/> :\n";
			$optionsBox.= "<input class='$controlInput' tabindex='118' type='text' id='tsMinute' name='tsMinute' value='$mn' size='2' maxlength='2'/> \n";
			$optionsBox.= "<input class='$controlInput' tabindex='119' type='hidden' id='tsSecond' name='tsSecond' value='$ss' /> \n";
			$optionsBox.= "</div>";
		}

		if ($spVars['displaymode'] == 'edit') {
            $optionsBox = apply_filters('sph_post_edit_options_add', $optionsBox, $spThisTopic, $a);
        } else {
             $optionsBox = apply_filters('sph_post_options_add', $optionsBox, $spThisTopic, $a);
        }

		if ($toolbar=='toolbar') {
			$optionsBox.= sp_InsertBreak('direction=both&spacer=6px&echo=0');
		} else {
			$optionsBox.= sp_InsertBreak('echo=0');
		}
		$optionsBox.= '</div>'."\n";
	}

	if ($display['smileys'] || $display['options']) {
		$out.= $smileysBox.$optionsBox;
		$out.= sp_InsertBreak('echo=0');
		$out.= '</div>';
	}

	return $out;
}

?>