<?php
/*
Simple:Press
KSES - Alowed Forum Post Tags
$LastChangedDate: 2016-02-26 18:56:27 -0600 (Fri, 26 Feb 2016) $
$Rev: 13999 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	Creates the SP specific KSES arrays
#
# ==================================================================

# Version: 5.0
function sp_kses_array() {
	global $allowedforumtags, $allowedforumprotocols, $spVars, $spThisUser;

    $allowedforumprotocols = apply_filters('sph_allowed_protocols', array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'clsid', 'data'));
	$allowedforumtags = array(
	'address' 	 => array('class' => true),
	'a' 		 => array('class' => true, 'href' => true, 'id' => true, 'title' => true, 'rel' => true, 'rev' => true, 'name' => true, 'target' => true, 'style' => true),
	'abbr' 		 => array('class' => true, 'title' => true),
	'acronym' 	 => array('title' => true, 'class' => true),
    'article'    => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'aside'      => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'audio'		 => array('autoplay' => true, 'class' => true, 'controls' => true, 'id' => true, 'loop' => true, 'muted' => true, 'poster' => true, 'preload' => true, 'src' => true, 'style' => true),
	'b' 		 => array('class' => true),
	'big' 		 => array('class' => true),
	'blockquote' => array('id' => true, 'cite' => true, 'class' => true, 'lang' => true, 'xml:lang' => true, 'style' => true),
	'br' 		 => array('class' => true),
	'caption' 	 => array('align' => true, 'class' => true),
	'cite' 		 => array('class' => true, 'dir' => true, 'lang' => true, 'title' => true),
	'code' 		 => array('class' => true, 'style' => true),
	'dd' 		 => array('class' => true),
	'del'        => array('datetime' => true),
    'details'    => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'open' => true, 'style' => true, 'xml:lang' => true),
	'div' 		 => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'dl' 		 => array('class' => true),
	'dt' 		 => array('class' => true),
	'em' 		 => array('class' => true),
	'embed' 	 => array('height' => true, 'name' => true, 'pallette' => true, 'src' => true, 'type' => true, 'width' => true),
	'figure'     => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
    'figcaption' => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'font' 		 => array('color' => true, 'face' => true, 'size' => true),
	'footer'     => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'header'     => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'hgroup'     => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'h1' 		 => array('align' => true, 'class' => true, 'id'    => true, 'style' => true),
	'h2' 		 => array('align' => true, 'class' => true, 'id'    => true, 'style' => true),
	'h3' 		 => array('align' => true, 'class' => true, 'id'    => true, 'style' => true),
	'h4' 		 => array('align' => true, 'class' => true, 'id'    => true, 'style' => true),
	'h5' 		 => array('align' => true, 'class' => true, 'id'    => true, 'style' => true),
	'h6' 		 => array('align' => true, 'class' => true, 'id'    => true, 'style' => true),
	'hr' 		 => array('align' => true, 'class' => true, 'noshade' => true, 'size' => true, 'width' => true),
	'i' 		 => array('class' => true),
	'img' 		 => array('alt' => true, 'title' => true, 'align' => true, 'border' => true, 'class' => true, 'height' => true, 'hspace' => true, 'longdesc' => true, 'vspace' => true, 'src' => true, 'style' => true, 'width' => true, 'data-upload' => true, 'data-width' => true, 'data-height' => true),
	'ins' 		 => array('datetime' => true, 'cite' => true),
	'kbd' 		 => array('class' => true),
	'label' 	 => array('for' => true),
	'legend'     => array('align' => true),
	'li' 		 => array('align' 	=> true, 'class' => true, 'id' => true, 'style' => true),
    'menu'       => array('class' => true, 'style' => true, 'type' => true),
    'nav'        => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'object' 	 => array('classid' => true, 'codebase' => true, 'codetype' => true, 'data' => true, 'declare' => true, 'height' => true, 'name' => true, 'param' => true, 'standby' => true, 'type' => true, 'usemap' => true, 'width' => true),
	'param' 	 => array('id' => true, 'name' => true, 'type' => true, 'value' => true, 'valuetype' => true),
	'p' 		 => array('class' => true, 'align' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'pre' 		 => array('class' => true, 'style' => true, 'width' => true),
	'q' 		 => array('cite' => true),
	's' 		 => array('class' => true),
    'section'    => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
    'small'      => array('class' => true),
    'source'     => array('class' => true, 'id' => true, 'media' => true, 'src' => true, 'style' => true, 'type' => true),
 	'span' 		 => array('class' => true, 'dir' => true, 'align' => true, 'lang' => true, 'style' => true, 'title' => true, 'xml:lang' => true, 'id' => true),
	'strike' 	 => array('class' => true),
	'strong' 	 => array('class' => true),
	'sub' 		 => array('class' => true),
	'summary'    => array('align' => true, 'class' => true, 'dir' => true, 'lang' => true, 'style' => true, 'xml:lang' => true),
	'sup' 		 => array('class' => true),
	'table' 	 => array('align' => true, 'bgcolor' => true, 'border' => true, 'cellpadding' => true, 'cellspacing' => true, 'class' => true, 'dir' => true, 'id' => true, 'rules' => true, 'style' => true, 'summary' => true, 'width' => true),
	'tbody' 	 => array('align' => true, 'char' => true, 'charoff' => true, 'valign' => true),
	'td' 		 => array('abbr' => true, 'align' => true, 'axis' => true, 'bgcolor' => true, 'char' => true, 'charoff' => true, 'class' => true, 'colspan' => true, 'dir' => true, 'headers' => true, 'height' => true, 'nowrap' => true, 'rowspan' => true, 'scope' => true, 'style' => true, 'valign' => true, 'width' => true),
	'tfoot' 	 => array('align' => true, 'char' => true, 'class' => true, 'charoff' => true, 'valign' => true),
	'th' 		 => array('abbr' => true, 'align' => true, 'axis' => true, 'bgcolor' => true, 'char' => true, 'charoff' => true, 'class' => true, 'colspan' => true, 'headers' => true, 'height' => true, 'nowrap' => true, 'rowspan' => true, 'scope' => true, 'valign' => true, 'width' => true),
	'thead' 	 => array('align' => true, 'char' => true, 'charoff' => true, 'class' => true, 'valign' => true),
	'title' 	 => array('class' => true),
	'tr' 		 => array('align' => true, 'bgcolor' => true, 'char' => true, 'charoff' => true, 'class' => true, 'style' => true, 'valign' => true),
	'tt' 		 => array('class' => true),
	'u' 		 => array('class' => true),
	'ul' 		 => array('class' => true, 'style' => true, 'type' => true),
	'ol' 		 => array('class' => true, 'start' => true, 'style' => true, 'type' => true),
	'var' 		 => array('class' => true),
	'video'		 => array('autoplay' => true, 'class' => true, 'controls' => true, 'height' => true, 'id' => true, 'loop' => true, 'muted' => true, 'poster' => true, 'preload' => true, 'src' => true, 'style' => true, 'width' => true));

	$target = (isset($spVars['forumid'])) ? $spVars['forumid'] : 'global';
	if (isset($spThisUser) && sp_get_auth('can_use_iframes', $target, $spThisUser->ID)) {
		$allowedforumtags['iframe']	= array('width' => true, 'height' => true, 'frameborder' => true, 'src' => true, 'frameborder' => true, 'marginwidth' => true, 'marginheight' => true);
	}

    $allowedforumtags = apply_filters('sph_kses_allowed_tags', $allowedforumtags);
}
?>