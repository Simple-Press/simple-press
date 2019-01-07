<?php

/**
 * Core class used for edit filters.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 */
class spcEditFilters {
	public function content($content) {
		#save unedited content
		$original = $content;

		# 1: Convert Chars
		$content = SP()->displayFilters->chars($content);

		# 2: remove 'pre' tags (optional)
		$sffilters = SP()->options->get('sffilters');
		if ($sffilters['sffilterpre']) $content = SP()->displayFilters->pre($content);

		# 3: Format the paragraphs (p and br onlt Richtext)
		if (function_exists('sp_editor_save_linebreaks')) {
			$content = sp_editor_save_linebreaks($content, SP()->core->forumData['editor']);
		} else {
			$content = SP()->saveFilters->linebreaks($content);
		}

		if (function_exists('sp_editor_format_paragraphs_edit')) $content = sp_editor_format_paragraphs_edit($content, SP()->core->forumData['editor']);

		# 4: Parse post into appropriate editor format
		$content = $this->parser($content, SP()->core->forumData['editor']);

		# 5: Conver entities back to characters
		$content = htmlspecialchars_decode($content, ENT_COMPAT);

		$content = apply_filters('sph_edit_content_filter', $content, $original);

		return $content;
	}

	public function text($content) {
		#save unedited content
		$original = $content;

		# 1: Convert Chars
		$content = SP()->displayFilters->chars($content);

		# 2: Format the paragraphs (p and br)
		$content = SP()->displayFilters->paragraphs($content);
		$content = SP()->saveFilters->linebreaks($content);

		# 3: Parse post into appropriate editor format
		$content = $this->parser($content, SP()->core->forumData['editor']);

		# 4: remove escape slashes
		$content = SP()->displayFilters->stripslashes($content);

		# 5: finally htnl encode it for edit display
		$content = htmlentities($content, ENT_COMPAT, SPCHARSET);

		$content = apply_filters('sph_edit_text_filter', $content, $original);

		return $content;
	}

	public function parser($content, $editor) {
		#save unedited content
		$original = $content;

		if (function_exists('sp_editor_parse_for_edit')) $content = sp_editor_parse_for_edit($content, $editor);

		$content = apply_filters('sph_edit_parser_filter', $content, $original);

		return $content;
	}
}