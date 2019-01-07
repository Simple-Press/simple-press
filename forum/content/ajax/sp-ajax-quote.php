<?php
/*
Simple:Press
Quote handing for posts
$LastChangedDate: 2017-02-11 15:35:37 -0600 (Sat, 11 Feb 2017) $
$Rev: 15187 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();
sp_load_editor(0,1);

if (!sp_nonce('spQuotePost')) die();

$postid = SP()->filters->integer($_GET['post']);
$forumid = SP()->filters->integer($_GET['forumid']);
if (empty($forumid) || empty($postid)) die();

if (!SP()->auths->get('reply_topics', $forumid)) die();

$post = SP()->DB->table(SPPOSTS, "post_id=$postid", 'row');

if (!SP()->auths->get('view_admin_posts', $forumid) && SP()->auths->forum_admin($post->user_id)) die();
if (SP()->auths->get('view_own_admin_posts', $forumid) && !SP()->auths->forum_admin($post->user_id) && !SP()->auths->forum_mod($post->user_id) && SP()->user->thisUser->ID != $post->user_id) die();

$content = SP()->editFilters->content($post->post_content);
$original = $content;

# remove old blockquote if exists...
$bqStartPos = strpos($content, 'blockquote class="spPostEmbedQuote">');
$bqEndPos = strpos($content, chr(194).chr(160).chr(194).chr(160)."</blockquote>");

if ($bqStartPos && $bqEndPos) {
	$bqStartPos -=1;
	$bqEndPos += 17;
	$len = $bqEndPos - $bqStartPos;
	$content = substr_replace($content, '', $bqStartPos, $len);
}

$content = apply_filters('sph_quote_content', $content, $original);

echo $content;

die();
