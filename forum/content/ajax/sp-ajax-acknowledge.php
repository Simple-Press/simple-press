<?php
/*
Simple:Press
Ajax call for acknowledgements
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

if (!sp_nonce('spAckPopup')) die();

$theme = SP()->theme->get_current();

$ack = array(
	'<a href="https://github.com/jasonday/printThis">'.SP()->primitives->front_text('printThis by Jason Day').'</a>',
	'<a href="http://sw-guide.de/">'.SP()->primitives->front_text('Math Spam Protection based on code by Michael Woehrer').'</a>',
	'<a href="http://www.rainforestnet.com">'.SP()->primitives->front_text('Calendar Date Picker by TengYong Ng').'</a>',
	'<a href="http://valums.com/ajax-upload/">'.SP()->primitives->front_text('Image Uploader by Andrew Valums').'</a>',
	'<a href="http://rpxwiki.com/WordpressPlugin">'.SP()->primitives->front_text('SPF RPX implementation uses code and ideas from Brian Ellin').'</a>',
	'<a href="http://www.isocra.com/2008/02/table-drag-and-drop-jquery-plugin/">'.SP()->primitives->front_text('Table Drag and Drop by Isocra Consulting').'</a>',
	'<a href="http://www.brettjankord.com/2012/01/16/categorizr-a-modern-device-detection-script/">'.SP()->primitives->front_text('Mobile Device Detection based on code by Brett Jankord').'</a>',
	'<a href="http://http://yacobi.info/">'.SP()->primitives->front_text('CSS and JS Concatenation based on code by Ronen Yacobi').'</a>',
	'<a href="https://codeb.it/fonticonpicker/">'.SP()->primitives->front_text('Jquery FontIcon Picker by Alessandro Benoit and Swashata Ghosh').'</a>',
);
$ack = apply_filters('sph_acknowledgements', $ack);

$out = '<style>#spAbout p a {padding:0 !important;}</style>';

$out.= '<div id="spAbout" style="padding: 0 20px;">';
$out.= '<img src="'.SPCOMMONIMAGES.'sp-full-logo.png" alt="" title="" /><br />';
$out.= '<p>&copy; 2006-'.date('Y').' '.SP()->primitives->front_text('by').' <a href="https://simple-press.com"><b>Simple:Press/SMI</b></a> ';
$out.= '<p>'.SP()->primitives->front_text('A heartfelt and sincere THANK YOU to the original developers of Simple:Press who poured a lot of blood, sweat and tears into the plugin for more than 12 years - ').' <a href="http://www.yellowswordfish.com"><b>Andy Staines</b></a> '.SP()->primitives->front_text('and').' <a href="http://cruisetalk.org/"><b>Steve Klasen</b></a></p>';
$out.= '<p><a href="http://twitter.com/simpleforum">'.SP()->primitives->front_text('Follow us on Twitter').'</a> : <a href="https://www.facebook.com/simplepressforum">'.SP()->primitives->front_text('Like us on Facebook').'</a></p>';
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
$out.= '<p>'.SP()->primitives->front_text('Our thanks to all the people who have aided, abetted, coded, suggested and helped test this plugin').'</p>';
$out.= '<p>';
if (empty($theme['parent'])) {
	$out.= SP()->primitives->front_text('This forum is using the').' <strong>'.$theme['theme'].'</strong> '.SP()->primitives->front_text('theme');
} else {
	$out.= SP()->primitives->front_text('This forum is using').' <strong>'.$theme['theme'].'</strong><br>'.SP()->primitives->front_text('a child theme of').' <strong>'.$theme['parent'].'</strong> ';
}
$out.= '</p>';

$out.= '</div>';
echo $out;
die();
