<?php
/*
Simple:Press
Quote handing for posts
$LastChangedDate: 2016-12-03 14:06:51 -0600 (Sat, 03 Dec 2016) $
$Rev: 14745 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_ajax_support();
sp_load_editor(0,1);

if (!sp_nonce('spQuotePost')) die();

global $spThisUser;

$postid = sp_esc_int($_GET['post']);
$forumid = sp_esc_int($_GET['forumid']);
if (empty($forumid) || empty($postid)) die();

if (!sp_get_auth('reply_topics', $forumid)) die();

$post = spdb_table(SFPOSTS, "post_id=$postid", 'row');

if (!sp_get_auth('view_admin_posts', $forumid) && sp_is_forum_admin($post->user_id)) die();
if (sp_get_auth('view_own_admin_posts', $forumid) && !sp_is_forum_admin($post->user_id) && !sp_is_forum_mod($post->user_id) && $spThisUser->ID != $post->user_id) die();

$content = sp_filter_content_edit($post->post_content);
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
?>