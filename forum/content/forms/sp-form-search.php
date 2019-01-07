<?php
/*
Simple:Press
Search Form Rendering
$LastChangedDate: 2016-03-23 05:06:05 -0500 (Wed, 23 Mar 2016) $
$Rev: 14074 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_inline_search_form($args) {
	global $spVars, $spThisUser, $spGlobals, $spDevice;

	extract($args, EXTR_SKIP);

	# sanitize before use
	$searchInclude	= (!empty($spVars['searchinclude'])) ? $spVars['searchinclude'] : (int) $searchIncludeDef;
	$searchScope	= ($spVars['forumid'] == 'all' || (empty($spVars['forumid']) && (int) $searchScope == 2)) ? 2 : 1;

	$submitId2   	= esc_attr($submitId2);
	$submitClass2	= esc_attr($submitClass2);
	$icon			= sanitize_file_name($icon);
	$iconClass		= esc_attr($iconClass);

	if (!empty($submitLabel)) $submitLabel = sp_filter_title_display($submitLabel);
	if (!empty($toolTip)) $toolTip = esc_attr($toolTip);
	if (!empty($labelLegend)) $labelLegend = sp_filter_title_display($labelLegend);
	if (!empty($labelScope)) $labelScope = sp_filter_title_display($labelScope);
	if (!empty($labelCurrent)) $labelCurrent  = sp_filter_title_display($labelCurrent);
	if (!empty($labelAll)) $labelAll = sp_filter_title_display($labelAll);
	if (!empty($labelMatch)) $labelMatch = sp_filter_title_display($labelMatch);
	if (!empty($labelMatchAny)) $labelMatchAny = sp_filter_title_display($labelMatchAny);
	if (!empty($labelMatchAll)) $labelMatchAll = sp_filter_title_display($labelMatchAll);
	if (!empty($labelMatchPhrase)) 	$labelMatchPhrase = sp_filter_title_display($labelMatchPhrase);
	if (!empty($labelOptions)) $labelOptions = sp_filter_title_display($labelOptions);
	if (!empty($labelPostTitles)) $labelPostTitles = sp_filter_title_display($labelPostTitles);
	if (!empty($labelPostsOnly)) $labelPostsOnly = sp_filter_title_display($labelPostsOnly);
	if (!empty($labelTitlesOnly)) $labelTitlesOnly = sp_filter_title_display($labelTitlesOnly);
	if (!empty($labelWildcards)) $labelWildcards = sp_filter_title_display($labelWildcards);
	if (!empty($labelMatchAnyChars)) $labelMatchAnyChars = sp_filter_title_display($labelMatchAnyChars);
	if (!empty($labelMatchOneChar)) $labelMatchOneChar = sp_filter_title_display($labelMatchOneChar);
	if (!empty($labelMinLength)) $labelMinLength = sp_filter_title_display($labelMinLength);
	if (!empty($labelMemberSearch)) $labelMemberSearch = sp_filter_title_display($labelMemberSearch);
	if (!empty($labelTopicsPosted)) $labelTopicsPosted = sp_filter_title_display($labelTopicsPosted);
	if (!empty($labelTopicsStarted)) $labelTopicsStarted = sp_filter_title_display($labelTopicsStarted);

	$br = '<br />';

	# all or current forum?
	$out = '';
	$out.= '<fieldset class="spSearchFormAdvanced">';
	$out.= '<legend>'.$labelLegend.'</legend>';
	$out.= '<div class="spSearchSection spSearchSectionForm">';

	$out = apply_filters('sph_SearchFormTop', $out);

	$out.= '<div class="spRadioSection spLeft">';
	$tout = '';
	$tout.= '<p class="spSearchForumScope">&mdash;&nbsp;'.$labelScope.'&nbsp;&mdash;</p><br />';
	if (!empty($spVars['forumid']) && $spVars['forumid'] != 'all') {
		$tout.= '<input type="hidden" name="forumslug" value="'.esc_attr($spVars['forumslug']).'" />';
		$tout.= '<input type="hidden" name="forumid" value="'.esc_attr($spVars['forumid']).'" />';
    	$tout.= '<input type="radio" id="sfradio1" name="searchoption" value="1"'.($searchScope == 1 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio1">'.$labelCurrent.'</label>'.$br;
	}
	$tout.= '<input type="radio" id="sfradio2" name="searchoption" value="2"'.($searchScope == 2 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio2">'.$labelAll.'</label>'.$br;
	$out.= apply_filters('sph_SearchFormForumScope', $tout);
	$out.= '</div>';

	# search type?
	$tout = '';
	$tout.= '<div class="spRadioSection spLeft">';
	$tout.= '<p class="spSearchMatch">&mdash;&nbsp;'.$labelMatch.'&nbsp;&mdash;</p><br />';
	$tout.= '<input type="radio" id="sfradio3" name="searchtype" value="1"'.($spVars['searchtype'] == 1 || empty($spVars['searchtype']) ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio3">'.$labelMatchAny.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio4" name="searchtype" value="2"'.($spVars['searchtype'] == 2 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio4">'.$labelMatchAll.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio5" name="searchtype" value="3"'.($spVars['searchtype'] == 3 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio5">'.$labelMatchPhrase.'</label>'.$br;
	$out.= apply_filters('sph_SearchFormMatch', $tout);
	$out.= '</div>';

	if ($spDevice == 'mobile') $out.= sp_InsertBreak('echo=0&spacer=12px');

	# topic title?
	$tout = '';
	$tout.= '<div class="spRadioSection spLeft">';
	$tout.= '<p class="spSearchOptions">&mdash;&nbsp;'.$labelOptions.'&nbsp;&mdash;</p><br />';
	$tout.= '<input type="radio" id="sfradio6" name="encompass" value="1"'.($searchInclude == 1 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio6">'.$labelPostsOnly.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio7" name="encompass" value="2"'.($searchInclude == 2 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio7">'.$labelTitlesOnly.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio8" name="encompass" value="3"'.($searchInclude == 3 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio8">'.$labelPostTitles.'</label>'.$br;
	$out.= apply_filters('sph_SearchFormOptions', $tout);
	$out.= '</div>';

    $out.= '<p class="spLeft spSearchDetails">'.sprintf($labelMinLength, '<b>'.SPSEARCHMIN.'</b>', '<b>'.SPSEARCHMAX.'</b>')."</p>";
	$out.= '</div>';

	$tout = '<div class="spSearchFormSubmit">';
	$tout.= "<a rel='nofollow' id='$submitId2' class='$submitClass2 spSearchSubmit' title='$toolTip' data-id='$submitId2' data-type='link' data-min='".SPSEARCHMIN."'>";
	if (!empty($icon)) {
		$tout.= sp_paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	}
	$tout.= "$submitLabel</a>";
    $tout.= '</div>';
	$out.= apply_filters('sph_SearchFormSubmit', $tout);
 	$out.= '</fieldset>';

	$out.= sp_InsertBreak('echo=0');

	$tout = '';
	if ($spThisUser->member) {
		$tout.= '<fieldset class="spSearchMember">';
		$tout.= '<legend>'.$labelMemberSearch.'</legend>';
		$tout.= '<div class="spSearchSection spSearchSectionUser">';
		$tout.= sp_paint_icon('', SPTHEMEICONSURL, 'sp_Search.png');
		$tout.= '<input type="hidden" name="userid" value="'.$spThisUser->ID.'" />';
		$tout.= '<input type="submit" class="spSubmit" name="membersearch" value="'.$labelTopicsPosted.'" />';
		$tout.= '<input type="submit" class="spSubmit" name="memberstarted" value="'.$labelTopicsStarted.'" />';
		$tout.= '</div>';
		$tout.= '</fieldset>';
	}
	$out.= apply_filters('sph_SearchFormMember', $tout);

	$out = apply_filters('sph_SearchFormBottom', $out);

	return $out;
}
?>