/* ---------------------------------
Simple:Press
Plain Text Editor Javascript
------------------------------------ */

/* ---------------------------------------------
   Open the dropdown editor area
--------------------------------------------- */
function spjEdOpenEditor(formType) {
	if (formType == 'topic') {
		document.addtopic.spTopicTitle.focus();
    } else if (formType == 'post') {
	    document.addpost.postitem.focus();
	} else if (formType == 'edit') {
	    document.editpostform.postitem.focus();
	}
}

/* ---------------------------------------------
   Cancels editor - removes any content
--------------------------------------------- */
function spjEdCancelEditor() {
	var tx = document.getElementById('postitem');
	tx.value = '';
	jQuery('#spPostNotifications').html('');
	jQuery('#spPostNotifications').hide();
	if (document.getElementById('previewPost') != 'undefined') {
		jQuery('#previewPost').html('');
	}
	spjToggleLayer('spPostForm');
}

/* ---------------------------------------------
   Insert content as in Quoting
--------------------------------------------- */
function spjEdInsertContent(intro, content) {
	var s = String.fromCharCode(160, 160);
	document.addpost.postitem.value += '<blockquote class="spPostEmbedQuote"><strong>'+intro+'</strong>\r'+content+s+'</blockquote><br />\r\r';
	document.addpost.postitem.focus();
}

/* ---------------------------------------------
   set text
--------------------------------------------- */
function spjEdSetText(text) {
	jQuery('#postitem').val(text);
	jQuery('#postitem').focus();
}

/* ---------------------------------------------
   Insert a Smiley
--------------------------------------------- */
function spjEdInsertSmiley(file, title, path, code) {
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
}

/* ---------------------------------------------
   Insert an Attachment
--------------------------------------------- */
function spjEdInsertAttachment(file, title, path, item, width, height) {
	jQuery('#' + item).val(jQuery('#' + item).val()+'<img src="'+path+file+'" title="'+title+'" alt="'+title+'"  width="'+width+'" height="'+height+'" /><p></p>');
}

function spjEdInsertMediaAttachment(file, path, width, height) {
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

	jQuery('#postitem').val(jQuery('#postitem').val() + '<p><object width="' + width + '" height="' + height + '" type="' + mt + '" data="' + path + file + '"><param value="' + path + file + '" name="src"><param value="false" name="autoplay"></object></p><p></p>');
}

function spjEdInsertFileAttachment(file, path) {
	jQuery('#postitem').val(jQuery('#postitem').val() + '<a href="'+path+file+'">'+file+'</a>');
}

/* ---------------------------------------------
   Insert text
--------------------------------------------- */
function spjEdInsertText(text) {
	jQuery('#postitem').val(jQuery('#postitem').val() + text);
}

/* ---------------------------------------------
   Get the current content of the editor
--------------------------------------------- */
function spjEdGetEditorContent(theForm) {
	return theForm.postitem.value;
}

/* ---------------------------------------------
   Validate editor content for known failures
--------------------------------------------- */
function spjEdValidateContent(theField, errorMsg) {
	var error = '';
	if (theField.value.length === 0) {
		error = '<strong>' + errorMsg + '</strong><br />';
	}
	return error;
}

/* ---------------------------------------------
   Get the current content of the signature
--------------------------------------------- */
function spjEdGetSignature(a) {
}
