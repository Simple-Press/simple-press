<?php
/*
Simple:Press
general ajax routines
$LastChangedDate: 2017-03-09 05:37:08 -0600 (Thu, 09 Mar 2017) $
$Rev: 15276 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();

# get out of here if no action specified
if (empty($_GET['targetaction'])) die();
$action = SP()->filters->str($_GET['targetaction']);

if ($action == 'page-popup') {

	if (!sp_nonce('spPageJump')) die();

	$defs = array('tagClass'		=> 'spPageJump',
				  'formClass'		=> 'spPageJumpForm',
	              'labelClass'		=> 'spLabel',
	              'controlClass'	=> 'spcontrol',
				  'buttonClass'		=> 'spSubmit'
				 );
	
	$data = array();
	if (file_exists(SPTEMPLATES.'data/popup-form-data.php')) {
		include SPTEMPLATES.'data/popup-form-data.php';
		$data = sp_page_jump_popup_data();
	}
	
	$a = wp_parse_args($data, $defs);
	extract($a, EXTR_SKIP);
	# sanitize before use
	$tagClass		= esc_attr($tagClass);
	$formClass		= esc_attr($formClass);
	$labelClass		= esc_attr($labelClass);
	$controlClass	= esc_attr($controlClass);
	$buttonClass	= esc_attr($buttonClass);

    SP()->primitives->front_text('Jump to page:');

    $permalink = trailingslashit(SP()->filters->str($_GET['url']));
    $max = SP()->filters->str($_GET['max']);

	$out = "<div id='spMainContainer' class='$tagClass'>\n";
	$out.= "<form class='$formClass' action='".SP()->spPermalinks->get_url()."' method='post' id ='pagejump' name='pagejump'>\n";
	$out.= "<input type='hidden' id='url' name='url' value='".$permalink."' />\n";
	$out.= "<input type='hidden' id='max' name='max' value='".$max."' />\n";
    $out.= "<label class='$labelClass'>".SP()->primitives->front_text('Enter page you want to go to:')."</label>\n";
	$out.= "<input class='$controlClass' type='text' size='5' id='pageNum' name='pageNum' value='' />\n";
	$out.= "<input type='submit' class='$buttonClass spJumpPage' name='submitpagejump' value='".SP()->primitives->front_text('Go')."' />\n";
	$out.= "</form></div>\n";
    echo apply_filters('sph_jump_page', $out);
}

die();
