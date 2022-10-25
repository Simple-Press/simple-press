/* ---------------------------------
 Simple:Press - Version 5.0
 Base Front-end Forum Javascript

 $LastChangedDate: 2010-08-11 12:22:07 -0700 (Wed, 11 Aug 2010) $
 $Rev: 4384 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties
	var result;

	// public properties

	// public methods
	spj.loadTool = function(url, target, imageFile) {
		if (imageFile !== '') {
			document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
		}
		$('#' + target).load(url);
	};

	spj.clearIt = function(target) {
		spj.editorSetText('');
	};


	spj.setProcessFlag = function(theForm) {
		sp_platform_vars.saveprocess = true;
		return true;
	};

	spj.validatePostForm = function(theForm, guest, topic, img) {
		sp_platform_vars.saveprocess = true;
		var reason = '';
		if (guest == 1 && theForm.guestname != 'undefined')
			reason += validateThis(theForm.guestname, sp_forum_vars.noguestname);
		if (guest == 1 && theForm.guestemail != 'undefined')
			reason += validateThis(theForm.guestemail, sp_forum_vars.noguestemail);
		if (topic == 1 && theForm.newtopicname != 'undefined')
			reason += validateThis(theForm.newtopicname, sp_forum_vars.notopictitle);

		reason += spj.editorValidateContent(theForm.postitem, sp_forum_vars.nocontent);
		/* check for pasted content */
		var thisPost = spj.editorGetContent(theForm);
		var found = false;
		var checkWords = new Array();
		checkWords[0] = 'MsoPlainText';
		checkWords[1] = 'MsoNormal';
		checkWords[2] = 'mso-layout-grid-align';
		checkWords[3] = 'mso-pagination';
		checkWords[4] = 'white-space:';
		for (i = 0; i < checkWords.length; i++) {
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
		if (thisPost.match('<object') && sp_platform_vars.checkiframe == 'yes') {
			reason += "<strong>" + sp_forum_vars.object_tag + "</strong><br />";
		}
		if (thisPost.match('<embed') && sp_platform_vars.checkiframe == 'yes') {
			reason += "<strong>" + sp_forum_vars.embed_tag + "</strong><br />";
		}			

		if (sp_platform_vars.postvalue != undefined) {
			if (document.getElementById('spPostValue') == undefined)
				reason += '<strong>' + sp_forum_vars.nocaptcha + '</strong><br>';
		}

		/* any errors */
		var msg = '';
		if (reason !== '') {
			msg = sp_forum_vars.problem + '<br>' + reason;
			$('#spPostNotifications').html(msg);
			$('#spPostNotifications').show('slow');

			return false;
		}

		var saveBtn = document.getElementById('sfsave');
		saveBtn.value = sp_forum_vars.savingpost;
		saveBtn.disabled = 'disabled';

		msg = sp_forum_vars.savingpost + ' - ' + sp_forum_vars.wait;
		spj.displayNotification(2, msg);
		return true;
	};

	spj.validateSearch = function(btn, subId, c, maxLen) {
		var stopSearch = false;
		var msg = '';
		var s = $('#searchvalue').val();

		if (s === '') {
			msg = sp_forum_vars.nosearch;
			stopSearch = true;
		} else {
			var w = s.split(" ");
			var good = 0;
			var bad = 0;
			for (i = 0; i < w.length; i++) {
				if (w[i].length < maxLen) {
					bad++;
				} else {
					good++;
				}
			}

			if (good === 0) {
				msg = sp_forum_vars.allwordmin + ' ' + maxLen;
				stopSearch = true;
			} else if (bad !== 0) {
				msg = sp_forum_vars.somewordmin + ' ' + maxLen;
			}
		}
		if (msg !== '') {
			spj.displayNotification(1, msg);
		}

		if (stopSearch === false && c == 'link') {
			document.sfsearch.submit();
			return;
		}
		if (stopSearch === true) {
			return false;
		} else {
			return true;
		}
	};

	spj.openEditor = function(editorId, formType) {
		$(document).ready(function() {
			/* remove header space div if needed */
			if (!$('#spHeadPad').length && sp_platform_vars.headpadding > 0) {
				$('#' + editorId).before("<div id='spHeadPad'>&nbsp;</div>");
				$('#spHeadPad').css('height', sp_platform_vars.headpadding + 'px');
			}
			$('#' + editorId).slideDown();
			if (!window.location.hash) location.href = location.href + '#spEditFormAnchor';
			spj.editorOpen(formType);
			$('html, body').animate({scrollTop: $('#' + editorId).offset().top - sp_platform_vars.headpadding}, 500);
		});
	};

	spj.openEditorBox = function(id) {
		$('#' + id).slideToggle();
	};

	spj.toggleLayer = function(whichLayer, speed) {
		if (!speed)
			speed = 'slow';
		$('#' + whichLayer).slideToggle(speed);

		var obj = document.getElementById(whichLayer);
		if (whichLayer == 'spPostForm' || whichLayer == 'sfsearchform') {
			obj.scrollIntoView();
		}
		/* remove header space div if needed */
		if (whichLayer == 'spPostForm' && sp_platform_vars.headpadding > 0) {
			$('#spHeadPad').detach();
		}
	};

	spj.quotePost = function(postid, intro, forumid, quoteUrl) {
		quoteUrl += '&post=' + postid + '&forumid=' + forumid;

		$('#spPostForm').show('normal', function() {
			spj.openEditor('spPostForm', 'post');
			$.ajax({url: quoteUrl}).done(function(content, b) {
				spj.editorInsertContent(intro, content);
			});
		});
	};

	spj.setPostButton = function(result, val1, val2, gbuttontext, bbuttontext) {
		var button = document.addpost.newpost;

		if (result.value == (val1 + val2)) {
			button.disabled = false;
			button.value = gbuttontext;
		} else {
			button.disabled = true;
			button.value = bbuttontext;
		}
	};

	spj.setTopicButton = function(result, val1, val2, gbuttontext, bbuttontext) {
		var button = document.addtopic.newtopic;

		if (result.value == (val1 + val2)) {
			button.disabled = false;
			button.value = gbuttontext;
		} else {
			button.disabled = true;
			button.value = bbuttontext;
		}
	};

	spj.changeUrl = function(menuObj) {
		var i = menuObj.selectedIndex;

		if (i > 0) {
			if (menuObj.options[i].value !== '#') {
				window.location = menuObj.options[i].value;
			}
		}
	};

	spj.redirect = function(url) {
		window.location = url;
	};

	spj.displayNotification = function(t, m) {
		$(document).ready(function() {
			var h = "<div id='spNotification' ";
			var i = '';
			var c = '';
			if (t == 0) {
				i = successImage.src;
				c = 'spMessageSuccess';
			}
			if (t == 1) {
				i = failureImage.src;
				c = 'spMessageFailure';
			}
			if (t == 2) {
				i = waitImage.src;
				c = 'spMessageWait';
			}

			h += "class='" + c + "'><img src='" + i + "' alt='' /><div style='clear:both'></div><p>" + m + "</p></div>";

			c = document.getElementById('spMainContainer');
			var r = c.getBoundingClientRect();
			var o = new Number(r.left);
			var w = new Number(r.right - r.left);
			var x = new Number(0);
			if (w < 260) {
				x = Math.round((w - 20) / 2);
			} else {
				x = 150;
			}
			var l = Math.round(((w / 2) + o) - x);

			$('#spMainContainer').prepend(h);
			$('#spNotification').css('left', l);
			$('#spNotification').css('width', (x * 2));
			$('#spNotification').show();

			if (sp_platform_vars.headpadding > 0) {
				var pt = $('#spNotification').offset();
				pt.top = Math.round(parseInt(sp_platform_vars.headpadding) + parseInt(pt.top));
				$('#spNotification').offset({top: pt.top, left: pt.left});
			}

			$('#spNotification').fadeOut(8000, function() {
				$('#spNotification').remove();
			});
		});
	};

	spj.autoUpdate = function(url, timer) {
		var sfInterval = window.setInterval("spj.performUpdates('" + url + "')", timer);
	};

	spj.performUpdates = function(url) {
		updates = url.split('%');
		for (i = 0; i < updates.length; i++) {
			up = updates[i].split(',');
			var func = up[0] + "('" + up[1] + "')";
			func = func.replace(/&amp;/gi, '&');
			eval(func);
		}
	};

	spj.selectCode = function(codeBlock) {
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
	};

	spj.removeAvatar = function(ajaxURL, avatarTarget, spinner) {
		$('#' + avatarTarget).html('<img src="' + spinner + '" />');
		$('#' + avatarTarget).load(ajaxURL);
		$('#spDeleteUploadedAvatar').hide();
		return;
	};

	spj.removePool = function(ajaxURL, avatarTarget, spinner) {
		$('#' + avatarTarget).html('<img src="' + spinner + '" />');
		$('#' + avatarTarget).load(ajaxURL);
		$('#spDeletePoolAvatar').hide();
		return;
	};

	spj.selectAvatar = function(src, file, msg) {
		$('#spPoolAvatar').val(file);
		text = '<img src="' + src + '" alt="" /><br /><br />';
		text = text + '<div id="spPoolStatus">' + msg + '</div';
		$('#spAvatarPool').html(text);
	};

	spj.removeNotice = function(ajaxURL, noticeId) {
		$('#' + noticeId).slideUp(400, function() {
			$('#' + noticeId).load(ajaxURL);
			$('#' + noticeId).remove();
			if (!$.trim($('#spUserNotices').html()).length) {
				$('#spUserNotices').slideUp();
			}
		});
	};

	spj.toggleSpoiler = function(id, reveal, hide) {
		spj.toggleLayer('spSpoilerContent' + id, 'fast');
		cur = $('#spSpoilerState' + id).val();
		if (cur == 0) {
			$('#spSpoilerState' + id).val(1);
			$('#spRevealLink' + id).html(hide);
		} else {
			$('#spSpoilerState' + id).val(0);
			$('#spRevealLink' + id).html(reveal);
		}
	};

	spj.setProfileDataHeight = function() {
		baseHeight = Math.max($("#spProfileData").outerHeight(true) + 10, $("#spProfileMenu").outerHeight(true));
		$("#spProfileContent").height(baseHeight + $("#spProfileHeader").outerHeight(true));
	};

	spj.openCloseForums = function(target, tagId, tagClass, openIcon, closeIcon, toolTipOpen, toolTipClose) {
		var icon = '';
		var tooltip = '';
		var c = $('#' + target).css('display');
		if (c == 'block') {
			$('#' + target).slideUp();
			icon = openIcon;
			tooltip = toolTipOpen;
			spj.cookie(target, 'closed', {expires: 30, path: '/'});
		} else {
			$('#' + target).slideDown();
			icon = closeIcon;
			tooltip = toolTipClose;
			spj.cookie(target, 'open', {expires: 30, path: '/'});
		}
		$('#' + tagId).html('<img class="' + tagClass + '" src="' + icon + '" title="' + tooltip + '" />');
	};

	spj.openCloseSection = function(target, tagId, tagClass, openIcon, closeIcon, toolTipOpen, toolTipClose, setCookie, asLabel, linkClass) {
		var icon = '';
		var tooltip = '';
		var c = $('#' + target).css('display');
		if (c == 'block') {
			$('#' + target).slideUp();
			icon = openIcon;
			tooltip = toolTipOpen;
			if (setCookie) {
				spj.cookie(target, 'closed', {expires: 30, path: '/'});
			}
		} else {
			$('#' + target).slideDown();
			icon = closeIcon;
			tooltip = toolTipClose;
			if (setCookie) {
				spj.cookie(target, 'open', {expires: 30, path: '/'});
			}
		}
		if (asLabel == true) {
			$('#' + tagId).text(tooltip);
		} else {
			$('#' + tagId).html('<img class="' + tagClass + '" src="' + icon + '" title="' + tooltip + '" />');
		}
		$('#' + tagId).css('cursor', 'pointer');
	};

	spj.inlineTopics = function(target, site, spinner, tagId, openIcon, closeIcon) {
		var icon = '';
		var c = $('#' + target).css('display');
		if (c == 'block') {
			$('#' + target).slideUp();
			icon = openIcon;
		} else {
			if ($('#' + target).html() === '') {
				$('#' + target).html('<img src="' + spinner + '" />');
				$('#' + target).slideDown();
				$('#' + target).load(site, function() {
					$('#' + target).slideDown();
				});
			} else {
				$('#' + target).slideDown();
			}
			icon = closeIcon;
		}
		$('#' + tagId).html('<img src="' + icon + '" />');
	};

	spj.popupImage = function(source, iWidth, iHeight, limitSize) {
		/* we might need to resize it */
		var r = 0;
		var aWidth = (window.innerWidth - 75);
		var aHeight = (window.innerHeight - 75);

		var autoWidth = iWidth;
		var autoHeight = iHeight;

		if (limitSize) {
			/* width first */
			if (iWidth > aWidth) {
				r = (aWidth / iWidth) * 100;
				iWidth = Math.round(r * iWidth) / 100;
				iHeight = Math.round(r * iHeight) / 100;
			}
			/* now recheck height */
			if (iHeight > aHeight) {
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
		$(imgSource).dialog({
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
	};

	spj.deletePost = function(url, pid, tid) {
		if (confirm(sp_forum_vars.deletepost)) {
			$('#dialog').dialog('close');
			var count = $('#postlist' + tid + ' > div.spTopicPostSection:not([style*="display: none"])').length;
			$.ajax({
				type: 'GET',
				url: url + '&count=' + count,
				cache: false,
				success: function(html) {
					$('#eachPost' + pid).slideUp(function() {
						spj.displayNotification(0, sp_forum_vars.postdeleted);
						if (html != '')
							window.location = html;
					});
				}
			});
		}
	};

	spj.deleteTopic = function(url, tid, fid) {
		if (confirm(sp_forum_vars.deletetopic)) {
			$('#dialog').dialog('close');
			var count = $('#topiclist' + fid + ' > div.spForumTopicSection:not([style*="display: none"])').length;
			$.ajax({
				type: 'GET',
				url: url + '&count=' + count,
				cache: false,
				success: function(html) {

					$('#eachTopic' + tid).slideUp(function() {
						spj.displayNotification(0, sp_forum_vars.topicdeleted);
						var url = html != '' ? html : window.location.href;
						window.location.href = url;
						
					});
				}
			});
		}
	};

	spj.markRead = function(url) {
		$.ajax({
			type: 'GET',
			url: url,
			cache: false,
			success: function(html) {
				$('#spUnreadCount').html('0');
				spj.displayNotification(0, sp_forum_vars.markread);
			}
		});
	};

	spj.markForumRead = function(url, count) {
		$.ajax({
			type: 'GET',
			url: url,
			cache: false,
			success: function(html) {
				var unreadcount = parseInt($('#spUnreadCount').html());
				unreadcount = unreadcount - count;
				$('#spMarkForumRead').hide();
				$('#spUnreadCount').html(unreadcount);
				spj.displayNotification(0, sp_forum_vars.markforumread);
			}
		});
	};

	spj.pinPost = function(url) {
		$.ajax({
			type: 'GET',
			url: url,
			cache: false,
			success: function(html) {
				spj.displayNotification(0, sp_forum_vars.pinpost);
			}
		});
	};

	spj.pinTopic = function(url) {
		$.ajax({
			type: 'GET',
			url: url,
			cache: false,
			success: function(html) {
				spj.displayNotification(0, sp_forum_vars.pintopic);
			}
		});
	};

	spj.lockTopic = function(url) {
		$.ajax({
			type: 'GET',
			url: url,
			cache: false,
			success: function(html) {
				spj.displayNotification(0, sp_forum_vars.locktopic);
			}
		});
	};

	spj.pageJump = function() {
		url = $('#url').val();
		page = $('#pageNum').val();
		max = $('#max').val();
		if (url == '' || page == '' || max == '') {
			page = 1;
		}
		page = Math.max(page, 1);
		page = Math.min(page, max);
		jumpUrl = url + 'page-' + page;
		window.location = jumpUrl;
	};

	spj.openQuickLinks = function(target, tagId, openIcon, closeIcon) {
		var icon = '';
		var c = $('#' + target).css('display');
		if (c == 'block') {
			$('#' + target).slideUp();
			icon = openIcon;
		} else {
			$('#' + target).slideDown();
			icon = closeIcon;
		}
		$('#' + tagId).html('<img src="' + icon + '" />');
	};

	spj.resetMobileMenu = function() {
		$('#spMobilePanel').hide('slide', {direction: 'down'});
	};

	spj.cancelScript = function() {
		if (sp_platform_vars.device == 'mobile') {
			$('#spMobilePanel').hide('slide', {direction: 'down'});
		} else {
			$('#dialog').dialog('close');
		}
	};

	// private methods
	function validateThis(theField, errorMsg) {
		var error = '';
		if (theField.value.length === 0) {
			error = '<strong>' + errorMsg + '</strong><br>';
		}
		return error;
	}
}(window.spj = window.spj || {}, jQuery));
