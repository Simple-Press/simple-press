/* ---------------------------------
Simple:Press - Version 5.0
Base Front-end Forum Javascript

$LastChangedDate: 2010-08-11 12:22:07 -0700 (Wed, 11 Aug 2010) $
$Rev: 4384 $
------------------------------------ */

var result;

function spjLoadTool(url, target, imageFile) {
	if (imageFile !== '') {
		document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
	}
	jQuery('#'+target).load(url);
}

function spjClearIt(target) {
    spjEdSetText('');
}


function spjSetProcessFlag(theForm) {
	sp_platform_vars.saveprocess = true;
	return true;
}

/* ----------------------------------
Validate the new post form
-------------------------------------*/
function spjValidatePostForm(theForm, guest, topic, img) {
	sp_platform_vars.saveprocess = true;
	var reason = '';
	if (guest == 1 && theForm.guestname != 'undefined') reason+= spjValidateThis(theForm.guestname, sp_forum_vars.noguestname);
	if (guest == 1 && theForm.guestemail != 'undefined') reason+= spjValidateThis(theForm.guestemail, sp_forum_vars.noguestemail);
	if (topic == 1 && theForm.newtopicname != 'undefined') reason+= spjValidateThis(theForm.newtopicname, sp_forum_vars.notopictitle);

	reason+= spjEdValidateContent(theForm.postitem, sp_forum_vars.nocontent);
	/* check for pasted content */
	var thisPost = spjEdGetEditorContent(theForm);
	var found = false;
	var checkWords = new Array();
	checkWords[0] = 'MsoPlainText';
	checkWords[1] = 'MsoNormal';
	checkWords[2] = 'mso-layout-grid-align';
	checkWords[3] = 'mso-pagination';
	checkWords[4] = 'white-space:';
	for (i=0; i<checkWords.length; i++) {
		if (thisPost.match(checkWords[i]) !== null) {
			found = true;
		}
	}
	if (found) {
		reason += "<strong>" + sp_forum_vars.rejected + "</strong><br />";
	}
	if (thisPost.match('<iframe') && sp_platform_vars.checkiframe == 'yes') {
		reason += "<strong>" + sp_forum_vars.iframe + "</strong><br />";
	}

	if (sp_platform_vars.postvalue != undefined) {
		if(document.getElementById('spPostValue') == undefined) reason+= '<strong>' + sp_forum_vars.nocaptcha + '</strong><br>';
	}

	/* any errors */
    var msg = '';
	if (reason !== '') {
		msg = sp_forum_vars.problem + '<br>' + reason;
		jQuery('#spPostNotifications').html(msg);
		jQuery('#spPostNotifications').show('slow');

		return false;
	}

	var saveBtn = document.getElementById('sfsave');
	saveBtn.value = sp_forum_vars.savingpost;
	saveBtn.disabled = 'disabled';

	msg = sp_forum_vars.savingpost + ' - ' + sp_forum_vars.wait;
	spjDisplayNotification(2, msg);
	return true;
}

/* ----------------------------------
Validatation support routines
-------------------------------------*/
function spjValidateThis(theField, errorMsg) {
	var error = '';
	if (theField.value.length === 0) {
		error = '<strong>' + errorMsg + '</strong><br>';
	}
	return error;
}

/* ----------------------------------
Validatate search text has been input
-------------------------------------*/
function spjValidateSearch(btn, subId, c, maxLen) {
	var stopSearch = false;
	var msg = '';
	var s = jQuery('#searchvalue').val();

	if(s === '') {
		msg = sp_forum_vars.nosearch;
		stopSearch = true;
	} else {
		var w = s.split(" ");
		var good = 0;
		var bad = 0;
		for (i=0; i < w.length; i++) {
			if(w[i].length < maxLen) {
			     bad++;
            } else {
                good++;
            }
		}

		if(good === 0) {
			msg = sp_forum_vars.allwordmin + ' ' + maxLen;
			stopSearch = true;
		} else if(bad !== 0) {
			msg = sp_forum_vars.somewordmin + ' ' + maxLen;
		}
	}
	if(msg !== '') {
		spjDisplayNotification(1, msg);
	}

	if(stopSearch === false && c == 'link') {
		document.sfsearch.submit();
		return;
	}
	if(stopSearch === true) {
		return false;
	} else {
		return true;
	}
}

function spjOpenEditor(editorId, formType) {
	jQuery(document).ready(function() {
		/* remove header space div if needed */
		if (sp_platform_vars.headpadding > 0) {
			jQuery('#'+editorId).before( "<div id='spHeadPad'>&nbsp;</div>");
			jQuery('#spHeadPad').css('height', sp_platform_vars.headpadding+'px');
		}
		jQuery('#'+editorId).slideDown();
		location.href = location.href + '#spEditFormAnchor';
		spjEdOpenEditor(formType);
	});
}

/* ----------------------------------
Open and Close of hidden divs
-------------------------------------*/
function spjToggleLayer(whichLayer, speed) {
	if (!speed) speed = 'slow';
	jQuery('#'+whichLayer).slideToggle(speed);

	var obj = document.getElementById(whichLayer);
	if (whichLayer == 'spPostForm' || whichLayer == 'sfsearchform') {
		obj.scrollIntoView();
	}
	/* remove header space div if needed */
	if (whichLayer == 'spPostForm' && sp_platform_vars.headpadding > 0) {
		jQuery('#spHeadPad').detach();
	}
}

/* ----------------------------------
Quote Post insertion
-------------------------------------*/
function spjQuotePost(postid, intro, forumid, quoteUrl) {
	quoteUrl+='&post='+postid+'&forumid='+forumid;

	jQuery('#spPostForm').show('normal', function() {
        spjOpenEditor('spPostForm', 'post');
		jQuery.ajax({url: quoteUrl}).done(function(content, b) {
			spjEdInsertContent(intro, content);
		});
	});
}

/* ----------------------------------
Enable Save buttons on Math entry
-------------------------------------*/
function spjSetPostButton(result, val1, val2, gbuttontext, bbuttontext) {
	var button = document.addpost.newpost;

	if (result.value == (val1+val2)) {
		button.disabled = false;
		button.value = gbuttontext;
	} else {
		button.disabled = true;
		button.value = bbuttontext;
	}
}

function spjSetTopicButton(result, val1, val2, gbuttontext, bbuttontext) {
	var button = document.addtopic.newtopic;

	if (result.value == (val1+val2)) {
		button.disabled = false;
		button.value = gbuttontext;
	} else {
		button.disabled = true;
		button.value = bbuttontext;
	}
}

/* ----------------------------------
Trigger redirect on drop down
-------------------------------------*/
function spjChangeURL(menuObj) {
	var i = menuObj.selectedIndex;

	if (i > 0) {
 		if (menuObj.options[i].value !== '#') {
			window.location = menuObj.options[i].value;
		}
	}
}

/* ----------------------------------
URL redirect
-------------------------------------*/
function spjReDirect(url) {
	window.location = url;
}

/* ----------------------------------
Error and Success top notification
0=Success 1=Failure 2=Wait
-------------------------------------*/
function spjDisplayNotification(t, m) {
	jQuery(document).ready(function() {
		var h = "<div id='spNotification' ";
		var i = '';
		if(t == 0) i = successImage.src;
		if(t == 1) i = failureImage.src;
		if(t == 2) i = waitImage.src;

		h += "class='spMessageSuccess'><img src='" + i + "' alt='' /><div style='clear:both'></div><p>" + m + "</p></div>";

		var c = document.getElementById('spMainContainer');
		var r = c.getBoundingClientRect();
		var o = new Number(r.left);
		var w = new Number(r.right-r.left);
		var x = new Number(0);
		if(w < 260) {
			x = Math.round((w-20)/2);
		} else {
			x = 150;
		}
		var l = Math.round(((w/2)+o)-x);

		jQuery('#spMainContainer').prepend(h);
		jQuery('#spNotification').css('left', l);
		jQuery('#spNotification').css('width', (x*2));
		jQuery('#spNotification').show();

		if (sp_platform_vars.headpadding > 0) {
			var pt = jQuery('#spNotification').offset();
			pt.top = Math.round( parseInt(sp_platform_vars.headpadding) +  parseInt(pt.top));
			jQuery('#spNotification').offset({ top: pt.top, left: pt.left });
		}

		jQuery('#spNotification').fadeOut(8000, function() {
			jQuery('#spNotification').remove();
		});
	});
}

/* ----------------------------------
Auto Updates
-------------------------------------*/
function spjAutoUpdate(url, timer) {
	var sfInterval = window.setInterval("spjPerformUpdates('" + url + "')", timer);
}

function spjPerformUpdates(url) {
    updates = url.split('%');
	for (i=0; i < updates.length; i++) {
        up = updates[i].split(',');
        var func = up[0] + "('" + up[1] + "')";
        func = func.replace(/&amp;/gi, '&');
        eval(func);
	}
}

/* ----------------------------------
Embed a pre syntax highlight codeblock
-------------------------------------*/
function spjSelectCode(codeBlock) {
var e = document.getElementById(codeBlock);
	/* Get ID of code block
	   Not IE */
	if (window.getSelection) {
		s = window.getSelection();
		/* Safari */
		if (s.setBaseAndExtent) {
			s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
		} else {
			/* Firefox and Opera */
			r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	} else if (document.getSelection) {
		/* Some older browsers */
		s = document.getSelection();
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	} else if (document.selection) {
		/* IE */
		r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

function spjRemoveAvatar(ajaxURL, avatarTarget, spinner) {
	jQuery('#'+avatarTarget).html('<img src="' + spinner + '" />');
	jQuery('#'+avatarTarget).load(ajaxURL);
	jQuery('#spDeleteUploadedAvatar').hide();
	return;
}

function spjRemovePool(ajaxURL, avatarTarget, spinner) {
	jQuery('#'+avatarTarget).html('<img src="' + spinner + '" />');
	jQuery('#'+avatarTarget).load(ajaxURL);
	jQuery('#spDeletePoolAvatar').hide();
	return;
}

function spjRemoveNotice(ajaxURL, noticeId) {
    jQuery('#'+noticeId).slideUp(400, function() {
    	jQuery('#'+noticeId).load(ajaxURL);
        jQuery('#'+noticeId).remove();
        if (!jQuery.trim(jQuery('#spUserNotices').html()).length) {
        	jQuery('#spUserNotices').slideUp();
        }
    });
}

function spjSelAvatar(src, file, msg) {
	jQuery('#spPoolAvatar').val(file);
    text = '<img src="' + src + '" alt="" /><br /><br />';
    text = text + '<div id="spPoolStatus">' + msg + '</div';
	jQuery('#spAvatarPool').html(text);
}

function spjSpoilerToggle(id, reveal, hide) {
	spjToggleLayer('spSpoilerContent' + id, 'fast');
	cur = jQuery('#spSpoilerState' + id).val();
	if (cur == 0) {
		jQuery('#spSpoilerState' + id).val(1);
		jQuery('#spRevealLink' + id).html(hide);
	} else {
		jQuery('#spSpoilerState' + id).val(0);
		jQuery('#spRevealLink' + id).html(reveal);
	}
}

function spjSetProfileDataHeight() {
	baseHeight = Math.max(jQuery("#spProfileData").outerHeight(true) + 10, jQuery("#spProfileMenu").outerHeight(true));
   	jQuery("#spProfileContent").height(baseHeight + jQuery("#spProfileHeader").outerHeight(true));
}

function spjOpenCloseForums(target, tagId, tagClass, openIcon, closeIcon, toolTipOpen, toolTipClose) {
    var icon = '';
    var tooltip = '';
	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
		tooltip = toolTipOpen;
		jQuery.cookie(target, 'closed', {expires: 30, path: '/'});
	} else {
		jQuery('#'+target).slideDown();
		icon = closeIcon;
		tooltip = toolTipClose;
		jQuery.cookie(target, 'open', {expires: 30, path: '/'});
	}
	jQuery('#'+tagId).html('<img class="'+tagClass+'" src="'+icon+'" title="'+tooltip+'" />');
}

/* Generic Open.Close icon section */
function spjOpenCloseSection(target, tagId, tagClass, openIcon, closeIcon, toolTipOpen, toolTipClose, setCookie, asLabel, linkClass) {
    var icon = '';
    var tooltip = '';
	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
		tooltip = toolTipOpen;
		if(setCookie) {
			jQuery.cookie(target, 'closed', {expires: 30, path: '/'});
		}
	} else {
		jQuery('#'+target).slideDown();
		icon = closeIcon;
		tooltip = toolTipClose;
		if(setCookie) {
			jQuery.cookie(target, 'open', {expires: 30, path: '/'});
		}
	}
	if(asLabel == true) {
		jQuery('#'+tagId).text(tooltip);
	} else {
		jQuery('#'+tagId).html('<img class="'+tagClass+'" src="'+icon+'" title="'+tooltip+'" />');
	}
	jQuery('#'+tagId).css('cursor', 'pointer');
}

function spjInlineTopics(target, site, spinner, tagId, openIcon, closeIcon) {
    var icon = '';
   	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
	} else {
		if(jQuery('#'+target).html() === '') {
			jQuery('#'+target).html('<img src="' + spinner + '" />');
			jQuery('#'+target).slideDown();
			jQuery('#'+target).load(site, function() {
				jQuery('#'+target).slideDown();
			});
		} else {
			jQuery('#'+target).slideDown();
		}
		icon = closeIcon;
	}
	jQuery('#'+tagId).html('<img src="'+icon+'" />');
}

/*--------------------------------------------------------------
spjPopupImage:  Opens a popup imnage dialog (enlargement)
	source:		The image source path
*/
function spjPopupImage(source, iWidth, iHeight, limitSize) {
	/* we might need to resize it */
	var r = 0;
	var aWidth = (window.innerWidth-75);
	var aHeight = (window.innerHeight-75);

    var autoWidth = iWidth;
    var autoHeight = iHeight;

	if(limitSize) {
		/* width first */
		if(iWidth > aWidth) {
			r = (aWidth / iWidth) * 100;
			iWidth = Math.round(r * iWidth) / 100;
			iHeight = Math.round(r * iHeight) / 100;
		}
		/* now recheck height */
		if(iHeight > aHeight) {
			r = (aHeight / iHeight) * 100;
			iWidth = Math.round(r * iWidth) / 100;
			iHeight = Math.round(r * iHeight) / 100;
		}
	}

    iWidth = (autoWidth == 'auto') ? autoWidth : iWidth;
    iHeight = (autoHeight == 'auto') ? autoHeight : iHeight;

	imgSource = '<div><a href="' + source + '" target="_blank"><img class="spPopupImg" src="' + source + '" width="' + iWidth + '" height="' + iHeight + '" /></a></div>';

	/* add some to container for title bar and border */
	if (iWidth != 'auto') {
		iWidth = (Math.abs(iWidth) + 10);
	}
	if (iHeight != 'auto') {
		iHeight = (Math.abs(iHeight) + 60.8);
	}

    var filename = source.replace(/^.*[\\\/]/, '');
	jQuery(imgSource).dialog({
		show: true,
		hide: 'clip',
		draggable: true,
		resizable: false,
		closeText: '',
		modal: true,
		closeOnEscape: true,
		width: iWidth,
		height: iHeight,
		autoOpen: true,
        title: filename
	});
}

/* Opens up sections from editor toolbar */
function spjOpenEditorBox(id) {
	jQuery('#'+id).slideToggle();
}

function spjDeletePost(url, pid, tid) {
    if (confirm(sp_forum_vars.deletepost)) {
        jQuery('#dialog').dialog('close');
        var count = jQuery('#postlist' + tid + ' > div.spTopicPostSection:not([style*="display: none"])').length;
        jQuery.ajax({
            type: 'GET',
            url: url + '&count=' + count,
            cache: false,
            success: function(html) {
                jQuery('#post' + pid).slideUp(function() {
                	spjDisplayNotification(0, sp_forum_vars.postdeleted);
                    if (html != '') window.location = html;
                });
            }
        });
    }
}

function spjDeleteTopic(url, tid, fid) {
    if (confirm(sp_forum_vars.deletetopic)) {
        jQuery('#dialog').dialog('close');
        var count = jQuery('#topiclist' + fid + ' > div.spForumTopicSection:not([style*="display: none"])').length;
        jQuery.ajax({
            type: 'GET',
            url: url + '&count=' + count,
            cache: false,
            success: function(html) {
                jQuery('#topic' + tid).slideUp(function() {
                	spjDisplayNotification(0, sp_forum_vars.topicdeleted);
                    if (html != '') window.location = html;
                });
            }
        });
    }
}

function spjMarkRead(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
            jQuery('#spUnreadCount').html('0');
        	spjDisplayNotification(0, sp_forum_vars.markread);
        }
    });
}

function spjMarkForumRead(url, count) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
            var unreadcount = parseInt(jQuery('#spUnreadCount').html());
            unreadcount = unreadcount - count;
            jQuery('#spMarkForumRead').hide();
            jQuery('#spUnreadCount').html(unreadcount);
        	spjDisplayNotification(0, sp_forum_vars.markforumread);
        }
    });
}

function spjPinPost(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
        	spjDisplayNotification(0, sp_forum_vars.pinpost);
        }
    });
}

function spjPinTopic(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
        	spjDisplayNotification(0, sp_forum_vars.pintopic);
        }
    });
}

function spjLockTopic(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
        	spjDisplayNotification(0, sp_forum_vars.locktopic);
        }
    });
}

function spjPageJump() {
    url = jQuery('#url').val();
    page = jQuery('#page').val();
    max = jQuery('#max').val();

    if (url == '' || page == '' || max =='') {
        page = 1;
    }
    page = Math.max(page, 1);
    page = Math.min(page, max);

    jumpUrl = url + 'page-' + page;
	window.location = jumpUrl;
}

function spjOpenQL(target, tagId, openIcon, closeIcon) {
    var icon = '';
	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
	} else {
		jQuery('#'+target).slideDown();
		icon = closeIcon;
	}
	jQuery('#'+tagId).html('<img src="'+icon+'" />');
}

function spjResetMobileMenu() {
	jQuery('#spMobilePanel').hide('slide', {direction: 'down'});
}

function spjCancelScript() {
	if (sp_platform_vars.device == 'mobile') {
		jQuery('#spMobilePanel').hide('slide', {direction: 'down'});
	} else {
		jQuery('#dialog').dialog('close');
	}
}