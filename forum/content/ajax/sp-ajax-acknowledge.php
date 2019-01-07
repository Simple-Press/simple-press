<?php
/*
Simple:Press
Ajax call for acknowledgements
$LastChangedDate: 2016-06-25 08:14:16 -0500 (Sat, 25 Jun 2016) $
$Rev: 14331 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('spAckPopup')) die();

$theme = sp_get_current_sp_theme();

$ack = array(
	'<a href="https://github.com/jasonday/printThis">'.sp_text('printThis by Jason Day').'</a>',
	'<a href="http://sw-guide.de/">'.sp_text('Math Spam Protection based on code by Michael Woehrer').'</a>',
	'<a href="http://www.rainforestnet.com">'.sp_text('Calendar Date Picker by TengYong Ng').'</a>',
	'<a href="http://valums.com/ajax-upload/">'.sp_text('Image Uploader by Andrew Valums').'</a>',
	'<a href="http://rpxwiki.com/WordpressPlugin">'.sp_text('SPF RPX implementation uses code and ideas from Brian Ellin').'</a>',
	'<a href="http://www.isocra.com/2008/02/table-drag-and-drop-jquery-plugin/">'.sp_text('Table Drag and Drop by Isocra Consulting').'</a>',
	'<a href="http://www.brettjankord.com/2012/01/16/categorizr-a-modern-device-detection-script/">'.sp_text('Mobile Device Detection based on code by Brett Jankord').'</a>',
	'<a href="http://http://yacobi.info/">'.sp_text('CSS and JS Concatenation based on code by Ronen Yacobi').'</a>',
);
$ack = apply_filters('sph_acknowledgements', $ack);

$out = '<style type="text/css">#spAbout p a {padding:0 !important;}</style>';

$out.= '<div id="spAbout" style="padding: 0 20px;">';
$out.= '<img src="'.SFCOMMONIMAGES.'sp-full-logo.png" alt="" title="" /><br />';
$out.= '<p>&copy; 2006-'.date('Y').' '.sp_text('by').' <a href="http://www.yellowswordfish.com"><b>Andy Staines</b></a> '.sp_text('and').' <a href="http://cruisetalk.org/"><b>Steve Klasen</b></a></p>';
$out.= '<p><a href="http://twitter.com/simpleforum">'.sp_text('Follow us on Twitter').'</a> : <a href="https://www.facebook.com/simplepressforum">'.sp_text('Like us on Facebook').'</a></p>';
$out.= '<hr />';


$out.= '<p>';
$i = '';
$s = '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
foreach ($ack as $a) {
	$i.= $a.$s;
}
$out.= rtrim($i, $s);
$out.= '</p>';

$out.= '<hr />';
$out.= '<p>'.sp_text('Our thanks to all the people who have aided, abetted, coded, suggested and helped test this plugin').'</p>';
$out.= '<p>';
if (empty($theme['parent'])) {
	$out.= sp_text('This forum is using the').' <strong>'.$theme['theme'].'</strong> '.sp_text('theme');
} else {
	$out.= sp_text('This forum is using').' <strong>'.$theme['theme'].'</strong><br>'.sp_text('a child theme of').' <strong>'.$theme['parent'].'</strong> ';
}
$out.= '</p>';

$out.= '</div>';
echo $out;
die();
?>