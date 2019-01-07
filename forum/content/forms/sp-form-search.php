<?php
/*
Simple:Press
Search Form Rendering
$LastChangedDate: 2017-04-24 05:56:36 -0500 (Mon, 24 Apr 2017) $
$Rev: 15372 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_inline_search_form($args) {
	extract($args, EXTR_SKIP);

	# sanitize before use
	$searchInclude	= (!empty(SP()->rewrites->pageData['searchinclude'])) ? SP()->rewrites->pageData['searchinclude'] : (int) $searchIncludeDef;
	$searchScope	= (SP()->rewrites->pageData['forumid'] == 'all' || (empty(SP()->rewrites->pageData['forumid']) && (int) $searchScope == 2)) ? 2 : 1;

	$submitId2   	= esc_attr($submitId2);
	$submitClass2	= esc_attr($submitClass2);
	$icon			= sanitize_file_name($icon);
	$iconClass		= esc_attr($iconClass);

	if (!empty($submitLabel)) $submitLabel = SP()->displayFilters->title($submitLabel);
	if (!empty($toolTip)) $toolTip = esc_attr($toolTip);
	if (!empty($labelLegend)) $labelLegend = SP()->displayFilters->title($labelLegend);
	if (!empty($labelScope)) $labelScope = SP()->displayFilters->title($labelScope);
	if (!empty($labelCurrent)) $labelCurrent  = SP()->displayFilters->title($labelCurrent);
	if (!empty($labelAll)) $labelAll = SP()->displayFilters->title($labelAll);
	if (!empty($labelMatch)) $labelMatch = SP()->displayFilters->title($labelMatch);
	if (!empty($labelMatchAny)) $labelMatchAny = SP()->displayFilters->title($labelMatchAny);
	if (!empty($labelMatchAll)) $labelMatchAll = SP()->displayFilters->title($labelMatchAll);
	if (!empty($labelMatchPhrase)) 	$labelMatchPhrase = SP()->displayFilters->title($labelMatchPhrase);
	if (!empty($labelOptions)) $labelOptions = SP()->displayFilters->title($labelOptions);
	if (!empty($labelPostTitles)) $labelPostTitles = SP()->displayFilters->title($labelPostTitles);
	if (!empty($labelPostsOnly)) $labelPostsOnly = SP()->displayFilters->title($labelPostsOnly);
	if (!empty($labelTitlesOnly)) $labelTitlesOnly = SP()->displayFilters->title($labelTitlesOnly);
	if (!empty($labelWildcards)) $labelWildcards = SP()->displayFilters->title($labelWildcards);
	if (!empty($labelMatchAnyChars)) $labelMatchAnyChars = SP()->displayFilters->title($labelMatchAnyChars);
	if (!empty($labelMatchOneChar)) $labelMatchOneChar = SP()->displayFilters->title($labelMatchOneChar);
	if (!empty($labelMinLength)) $labelMinLength = SP()->displayFilters->title($labelMinLength);
	if (!empty($labelMemberSearch)) $labelMemberSearch = SP()->displayFilters->title($labelMemberSearch);
	if (!empty($labelTopicsPosted)) $labelTopicsPosted = SP()->displayFilters->title($labelTopicsPosted);
	if (!empty($labelTopicsStarted)) $labelTopicsStarted = SP()->displayFilters->title($labelTopicsStarted);

	$br = ($lineBreak) ? '<br />' : '';

	# all or current forum?
	$out = '';
	$out.= '<fieldset class="spSearchFormAdvanced">';
	$out.= '<legend>'.$labelLegend.'</legend>';
	$out.= "<div class='spSearchSection spSearchSectionForm $searchOptionsSection'>";

	$out = apply_filters('sph_SearchFormTop', $out);

	$tout = '';
	$tout.= "<div class='$scopeSection spRadioSection spLeft'>";
	$tout.= '<span class="spSearchForumScope">'.$labelScope.'</span><br />';
	if (!empty(SP()->rewrites->pageData['forumid']) && SP()->rewrites->pageData['forumid'] != 'all') {
		$tout.= '<input type="hidden" name="forumslug" value="'.esc_attr(SP()->rewrites->pageData['forumslug']).'" />';
		$tout.= '<input type="hidden" name="forumid" value="'.esc_attr(SP()->rewrites->pageData['forumid']).'" />';
    	$tout.= '<input type="radio" id="sfradio1" name="searchoption" value="1"'.($searchScope == 1 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio1">'.$labelCurrent.'</label>'.$br;
	}
	$tout.= '<input type="radio" id="sfradio2" name="searchoption" value="2"'.($searchScope == 2 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio2">'.$labelAll.'</label>'.$br;
	$out.= apply_filters('sph_SearchFormForumScope', $tout);
	$out.= '</div>';

	# search type?
	$tout = '';
	$tout.= "<div class='$matchSection spRadioSection spLeft'>";
	$tout.= '<span class="spSearchMatch">'.$labelMatch.'</span><br />';
	$tout.= '<input type="radio" id="sfradio3" name="searchtype" value="1"'.(SP()->rewrites->pageData['searchtype'] == 1 || empty(SP()->rewrites->pageData['searchtype']) ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio3">'.$labelMatchAny.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio4" name="searchtype" value="2"'.(SP()->rewrites->pageData['searchtype'] == 2 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio4">'.$labelMatchAll.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio5" name="searchtype" value="3"'.(SP()->rewrites->pageData['searchtype'] == 3 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio5">'.$labelMatchPhrase.'</label>'.$br;
	$out.= apply_filters('sph_SearchFormMatch', $tout);
	$out.= '</div>';

//	if (SP()->core->device == 'mobile') $out.= sp_InsertBreak('echo=0&spacer=12px');

	# topic title?
	$tout = '';
	$tout.= "<div class='$optionSection spRadioSection spLeft'>";
	$tout.= '<span class="spSearchOptions">'.$labelOptions.'</span><br />';
	$tout.= '<input type="radio" id="sfradio6" name="encompass" value="1"'.($searchInclude == 1 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio6">'.$labelPostsOnly.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio7" name="encompass" value="2"'.($searchInclude == 2 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio7">'.$labelTitlesOnly.'</label>'.$br;
	$tout.= '<input type="radio" id="sfradio8" name="encompass" value="3"'.($searchInclude == 3 ? ' checked="checked"' : '').' /><label class="spLabel spRadio" for="sfradio8">'.$labelPostTitles.'</label>'.$br;
	$out.= apply_filters('sph_SearchFormOptions', $tout);
	$out.= '</div>';

    $out.= "<div class='spLeft spSearchDetails $spSearchInfo'>".sprintf($labelMinLength, '<b>'.SPSEARCHMIN.'</b>', '<b>'.SPSEARCHMAX.'</b>')."</div>";
	$out.= '</div>';

	$tout = '<div class="spSearchFormSubmit">';
	$tout.= "<a rel='nofollow' id='$submitId2' class='$submitClass spSearchSubmit' title='$toolTip' data-id='$submitId2' data-type='link' data-min='".SPSEARCHMIN."'>";
	if (!empty($icon)) {
		$tout.= SP()->theme->paint_icon($iconClass, SPTHEMEICONSURL, $icon);
	}
	$tout.= "$submitLabel</a>";
    $tout.= '</div>';
	$out.= apply_filters('sph_SearchFormSubmit', $tout);
 	$out.= '</fieldset>';

	$tout = '';
	if (SP()->user->thisUser->member) {
		$tout.= '<fieldset class="spSearchMember">';
		$tout.= '<legend>'.$labelMemberSearch.'</legend>';
		$tout.= '<div class="spSearchSection spSearchSectionUser">';
		$tout.= SP()->theme->paint_icon('', SPTHEMEICONSURL, 'sp_Search.png');
		$tout.= '<input type="hidden" name="userid" value="'.SP()->user->thisUser->ID.'" />';
		$tout.= "<input type='submit' class='spSubmit $submitClass' name='membersearch' value='".$labelTopicsPosted."' />";
		$tout.= "<input type='submit' class='spSubmit $submitClass' name='memberstarted' value='".$labelTopicsStarted."' />";
		$tout.= '</div>';
		$tout.= '</fieldset>';
	}
	$out.= apply_filters('sph_SearchFormMember', $tout);

	$out = apply_filters('sph_SearchFormBottom', $out);

	return $out;
}
