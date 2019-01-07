/* ---------------------------------
Simple:Press
Plain Text Editor Javascript
------------------------------------ */

(function(spj, $, undefined) {
	/* ---------------------------------------------
	   Open the dropdown editor area
	--------------------------------------------- */
	spj.editorOpen = function(formType, ajax) {
		if (formType == 'topic') {
			document.addtopic.spTopicTitle.focus();
		} else if (formType == 'post') {
			document.addpost.postitem.focus();
		} else if (formType == 'edit') {
			document.editpostform.postitem.focus();
		}
	};

	/* ---------------------------------------------
	   Cancels editor - removes any content
	--------------------------------------------- */
	spj.editorCancel = function() {
		var tx = document.getElementById('postitem');
		tx.value = '';
		$('#spPostNotifications').html('');
		$('#spPostNotifications').hide();
		if (document.getElementById('previewPost') != 'undefined') {
			$('#previewPost').html('');
		}
		spj.toggleLayer('spPostForm');
	};

	/* ---------------------------------------------
	   Insert content as in Quoting
	--------------------------------------------- */
	spj.editorInsertContent = function(intro, content) {
		var s = String.fromCharCode(160, 160);
		document.addpost.postitem.value += '<blockquote class="spPostEmbedQuote"><strong>'+intro+'</strong>\r'+content+s+'</blockquote><br />\r\r';
		document.addpost.postitem.focus();
	};

	/* ---------------------------------------------
	   set text
	--------------------------------------------- */
	spj.editorSetText = function(text) {
		$('#postitem').val(text);
		$('#postitem').focus();
	};

	/* ---------------------------------------------
	   Insert a Smiley
	--------------------------------------------- */
	spj.editorInsertSmiley = function(file, title, path, code) {
		var postField = document.getElementById('postitem');
		var html = '<img src="'+path+file+'" title="'+title+'" alt="'+title+'" />';

		/* IE support */
		if (document.selection) {
			postField.focus();
			sel = document.selection.createRange();
			sel.text = html;
			postField.focus();
		} 	else if (postField.selectionStart || postField.selectionStart == '0') {
			/* MOZILLA/NETSCAPE support */
			var startPos = postField.selectionStart;
			var endPos = postField.selectionEnd;
			postField.value = postField.value.substring(0, startPos)
					  + html
					  + postField.value.substring(endPos, postField.value.length);
			postField.focus();
			postField.selectionStart = startPos + html.length;
			postField.selectionEnd = startPos + html.length;
		} else {
			postField.value += html;
			postField.focus();
		}
	};

	/* ---------------------------------------------
	   Insert an Attachment
	--------------------------------------------- */
	spj.editorInsertAttachment = function(file, title, path, item, width, height, twidth, theight) {
		$('#' + item).val($('#' + item).val()+'<img src="'+path+file+'" title="'+title+'" alt="'+title+'"  width="'+width+'" height="'+height+'" /><p></p>');
	};

	spj.editorInsertMediaAttachment = function(file, path, width, height) {
		ext = file.split('.').pop();
		if (ext == 'swf' || ext == 'flv' || ext == 'fla') {
			mt = 'application/x-shockwave-flash';
		} else if (ext == 'wma' || ext == 'wmv') {
			mt = 'application/x-mplayer2';
		} else if (ext == 'rm' || ext == 'rma' || ext == 'ra' || ext == 'rpm') {
			mt = 'audio/x-pn-realaudio-plugin';
		} else {
			mt = 'video/quicktime';
		}

		$('#postitem').val($('#postitem').val() + '<p><object width="' + width + '" height="' + height + '" type="' + mt + '" data="' + path + file + '"><param value="' + path + file + '" name="src"><param value="false" name="autoplay"></object></p><p></p>');
	};

	spj.editorInsertFileAttachment = function(file, path) {
		$('#postitem').val($('#postitem').val() + '<a href="'+path+file+'">'+file+'</a>');
	};

	/* ---------------------------------------------
	   Insert text
	--------------------------------------------- */
	spj.editorInsertText = function(text) {
		$('#postitem').val($('#postitem').val() + text);
	};

	/* ---------------------------------------------
	   Get the current content of the editor
	--------------------------------------------- */
	spj.editorGetContent = function(theForm) {
		return theForm.postitem.value;
	};

	/* ---------------------------------------------
	   Validate editor content for known failures
	--------------------------------------------- */
	spj.editorValidateContent = function(theField, errorMsg) {
		var error = '';
		if (theField.value.length === 0) {
			error = '<strong>' + errorMsg + '</strong><br />';
		}
		return error;
	};

	/* ---------------------------------------------
	   Get the current content of the signature
	--------------------------------------------- */
	spj.editorGetSignature = function(a) {
	};
}(window.spj = window.spj || {}, jQuery));
