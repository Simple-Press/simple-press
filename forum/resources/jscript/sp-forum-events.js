/* ---------------------------------
 Simple:Press - Version 5.0
 Front-end Forum Event Handlers Javascript

 $LastChangedDate: 2018-11-03 11:12:02 -0500 (Sat, 03 Nov 2018) $
 $Rev: 15799 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties
	/*****************************
	 common view event handlers
	 *****************************/
	quickLinksForum = {
		init: function() {
			$('#spQuickLinksForumSelect').change(function() {
				spj.changeUrl(this);
			});
		}
	};

	quickLinksForumMobile = {
		init: function() {
			$('#spQLFTitle').click(function() {
				var mydata = $(this).data();
				spj.openQuickLinks(mydata.tagidlist, mydata.target, mydata.open, mydata.close);
			});
		}
	};

	quickLinksTopic = {
		init: function() {
			$('#spQuickLinksTopicSelect').change(function() {
				spj.changeUrl(this);
			});
		}
	};

	quickLinksTopicMobile = {
		init: function() {
			$('#spQLTitle').click(function() {
				var mydata = $(this).data();
				spj.openQuickLinks(mydata.tagidlist, mydata.target, mydata.open, mydata.close);
			});
		}
	};

	openCloseControl = {
		init: function() {
			$('.spOpenClose').click(function() {
				var mydata = $(this).data();
				spj.openCloseSection(mydata.targetid, mydata.tagid, mydata.tagclass, mydata.openicon, mydata.closeicon, mydata.tipopen, mydata.tipclose, mydata.setcookie, mydata.label, mydata.linkclass);
			});
		}
	};

	goToBottom = {
		init: function() {
			$('.spGoBottom').click(function() {
				document.getElementById('spForumBottom').scrollIntoView(false);
			});
		}
	};

	loginout = {
		init: function() {
			$('.spLogInOut').click(function() {
				spj.toggleLayer('spLoginForm');
			});
		}
	};

	openSearch = {
		init: function() {
			$('.spOpenSearch').click(function() {
				spj.toggleLayer('spSearchContainer');
			});
		}
	};

	userNotice = {
		init: function() {
			$('.spUserNotice').click(function() {
				var mydata = $(this).data();
				spj.removeNotice(mydata.site, mydata.nid);
			});
		}
	};

	markAllRead = {
		init: function() {
			$('.spMarkAllRead').click(function() {
				var mydata = $(this).data();
				spj.markRead(mydata.ajaxurl);
				if (mydata.mobile == 1) {
					$('#' + mydata.tagid).slideUp();
					spj.resetMobileMenu();
				}
			});
		}
	};

	unreadPosts = {
		init: function() {
			$('.spUnreadPostsPopup').click(function() {
				var mydata = $(this).data();
				if (mydata.popup == 1) {
					spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				} else {
					spj.inlineTopics(mydata.target, mydata.site, mydata.spinner, mydata.id, mydata.open, mydata.close);
				}
			});
		}
	};

	markForumRead = {
		init: function() {
			$('.spMarkThisForumRead').click(function() {
				var mydata = $(this).data();
				spj.markForumRead(mydata.ajaxurl, mydata.count);
				if (mydata.mobile == 1) {
					$('#' + mydata.tagid).slideUp();
					spj.resetMobileMenu();
				}
			});
		}
	};

	searchFormSubmit = {
		init: function() {
			$('.spSearchSubmit').click(function() {
				var mydata = $(this).data();
				spj.validateSearch(this, mydata.id, mydata.type, mydata.min);
			});
		}
	};

	advancedSearchForm = {
		init: function() {
			$('.spAdvancedSearchForm').click(function() {
				var mydata = $(this).data();
				spj.toggleLayer(mydata.id);
			});
		}
	};

	closeMobilePanel = {
		init: function() {
			$(document).on('click', '#spPanelClose', function() {
				spj.resetMobileMenu();
			});
		}
	};

	mobileMenuOpen = {
		init: function() {
			$('.spMobileMenuOpen').click(function(e) {
				var mydata = $(this).data();
				spj.dialogPanelHtml(this, mydata.source);
				e.preventDefault();
			});
		}
	};

	cancelScript = {
		init: function() {
			$('.spCancelScript').click(function(event) {
				event.preventDefault();
				spj.cancelScript();
			});
		}
	};

	membersUsergroupSelect = {
		init: function() {
			$('#sp_usergroup_select').change(function() {
				spj.changeUrl(this);
			});
		}
	};

	openDialog = {
		init: function() {
			$('.spOpenDialog').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					cancelScript.init();
				});
			});
		}
	};

	/*****************************
	 group view event handlers
	 *****************************/
	groupHeaderOpen = {
		init: function() {
			$('.spGroupHeaderOpen').click(function() {
				var mydata = $(this).data();
				if (mydata.collapse == 1)
					$(mydata.id).click();
			});
		}
	};

	groupOpenClose = {
		init: function() {
			$('.spOpenCloseGroup').click(function() {
				var mydata = $(this).data();
				spj.openCloseForums(mydata.target, mydata.tag, mydata.tclass, mydata.open, mydata.close, mydata.toolopen, mydata.toolclose);
			});
		}
	};

	/*****************************
	 forum view event handlers
	 *****************************/
	forumPageJump = {
		init: function() {
			$('.spForumPageJump').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function(event) {
					$('.spJumpPage').click(function(event) {
						event.preventDefault();
						spj.pageJump();
					});
				});
			});
		}
	};

	newTopicButton = {
		init: function() {
			$('.spNewTopicButton').click(function() {
				var mydata = $(this).data();
				spj.openEditor(mydata.form, mydata.type);
			});
		}
	};

	forumTopicTools = {
		init: function() {
			$('.spForumTopicTools').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					forumToolsInit();
				});
			});
		}
	};
	/*****************************
	 topic view event handlers
	 *****************************/
	newPostButton = {
		init: function() {
			$('.spNewPostButton').click(function() {
				var mydata = $(this).data();
				spj.openEditor(mydata.form, mydata.type);
			});
		}
	};

	topicPageJump = {
		init: function() {
			$('.spTopicPageJump').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function(event) {
					$('.spJumpPage').click(function(event) {
						event.preventDefault();
						spj.pageJump();
					});
				});
			});
		}
	};

	showEditHistory = {
		init: function() {
			$('.spEditPostHistory').click(function() {
				var mydata = $(this).data();
				spj.dialogHtml(this, mydata.html, mydata.label, mydata.width, mydata.height, mydata.align);
			});
		}
	};

	printPost = {
		init: function() {
			$('.spPrintThisPost').click(function() {
				var mydata = $(this).data();
				$('#' + mydata.postid).printThis();
				return false;
			});
		}
	};

	quotePost = {
		init: function() {
			$('.spQuotePost').click(function() {
				var mydata = $(this).data();
				spj.quotePost(mydata.postid, mydata.intro, mydata.forumid, mydata.url);
			});
		}
	};

	deletePost = {
		init: function() {
			$('.spDeletePost').click(function() {
				var mydata = $(this).data();
				spj.deletePost(mydata.url, mydata.postid, mydata.topicid);
			});
		}
	};

	checkMath = {
		init: function() {
			$('.spMathCheck').keyup(function() {
				var mydata = $(this).data();
				if (mydata.type == 'topic') {
					spj.setTopicButton(this, mydata.val1, mydata.val2, mydata.buttongood, mydata.buttonbad);
				} else {
					spj.setPostButton(this, mydata.val1, mydata.val2, mydata.buttongood, mydata.buttonbad);
				}
			});
		}
	};

	forumPostTools = {
		init: function() {
			$('.spForumPostTools').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					forumToolsInit();
				});
			});
		}
	};


	/*****************************
	 api event handlers
	 *****************************/
	apiShowSpoiler = {
		init: function() {
			$('.spShowSpoiler').click(function() {
				var mydata = $(this).data();
				spj.toggleSpoiler(mydata.spoilerid, mydata.reveal, mydata.hide);
			});
		}
	};

	apiShowPopupImage = {
		init: function() {
			$('.spShowPopupImage').click(function() {
				var mydata = $(this).data();
				spj.popupImage(mydata.src, mydata.width, mydata.height, mydata.constrain);
			});
		}
	};

	apiSelectCode = {
		init: function() {
			$('.sfcodeselect').click(function() {
				var mydata = $(this).data();
				spj.selectCode(mydata.codeid);
			});
		}
	};

	/*****************************
	 forms event handlers
	 *****************************/
	formsInsertSmiley = {
		init: function() {
			$('.spEditor img.spSmiley').click(function() {
				var mydata = $(this).data();
				spj.editorInsertSmiley(mydata.url, mydata.title, mydata.path, mydata.code);
			});
		}
	};

	formsCancelEditor = {
		init: function() {
			$('.spCancelEditor').click(function() {
				var mydata = $(this).data();
				if (confirm(mydata.msg)) {
					spj.editorCancel();
				}
			});
		}
	};

	formsProcessFlag = {
		init: function() {
			$('.spProcessFlag').click(function() {
				var mydata = $(this).data();
				spj.setProcessFlag(this);
				if (confirm(mydata.msg)) {
					document.editpostform.submit();
				}
			});
		}
	};

	formsOpenEditorBox = {
		init: function() {
			$('.spEditorBoxOpen').click(function() {
				var mydata = $(this).data();
				spj.openEditorBox(mydata.box);
			});
		}
	};

	formsEditTimestamp = {
		init: function() {
			$('#sfeditTimestamp').click(function() {
				spj.toggleLayer('spHiddenTimestamp');
			});
		}
	};

	formsAddTopic = {
		init: function() {
			$('#addtopic').submit(function(event) {
				var mydata = $(this).data();
				var status = spj.validatePostForm(this, mydata.guest, 1, mydata.img);
				if (!status)
					event.preventDefault();
			});
		}
	};

	formsAddPost = {
		init: function() {
			$('#addpost').submit(function(event) {
				var mydata = $(this).data();
				var status = spj.validatePostForm(this, mydata.guest, 0, mydata.img);
				if (!status)
					event.preventDefault();
			});
		}
	};

	formsEditPost = {
		init: function() {
			$('#editpostform').submit(function() {
				spj.setProcessFlag(this);
			});
		}
	};

	/*****************************
	 profile event handlers
	 *****************************/
	profileClearSignature = {
		init: function() {
			$('.spClearSignature').on('click', function() {
				spj.clearIt('postitem');
			});
		}
	};

	profileShowPermissions = {
		init: function() {
			$('.spLoadPermissions').on('click', function() {
				var mydata = $(this).data();
				spj.loadTool(mydata.url, 'perm' + mydata.id, mydata.img);
				$('.spProfileUserPermissions').on('click', '#spClosePerms' + mydata.id, function() {
					$('#perm' + mydata.id).html('');
				});
			});
		}
	};

	profileDelUploadedAvatar = {
		init: function() {
			$('#spDeleteUploadedAvatar').on('click', function() {
				var mydata = $(this).data();
				spj.removeAvatar(mydata.url, mydata.target, mydata.spinner);
			});
		}
	};

	profileShowPoolAvatars = {
		init: function() {
			$('.spShowAvatarPool').on('click', function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					$('.spSelectPoolAvatar').on('click', function() {
						var mydata = $(this).data();
						spj.selectAvatar(mydata.src, mydata.file, mydata.text);
						if (sp_platform_vars.device == 'mobile') {
							spj.resetMobileMenu();
						} else {
							$('#dialog').dialog('close');
						}
					});
				});
			});
		}
	};

	profileDelPoolAvatar = {
		init: function() {
			$('#spDeletePoolAvatar').on('click', function() {
				var mydata = $(this).data();
				spj.removePool(mydata.url, mydata.target, mydata.spinner);
			});
		}
	};

	/*****************************
	 forum tools event handlers
	 *****************************/
	toolsViewEmail = {
		init: function() {
			$('.spToolsEmail').click(function() {
				var mydata = $(this).data();
				spj.dialogHtml(this, mydata.html, mydata.title, mydata.width, mydata.height, mydata.align);
			});
		}
	};

	toolsPinPost = {
		init: function() {
			$('.spToolsPin').click(function() {
				var mydata = $(this).data();
				spj.pinPost(mydata.url);
				if ($('#dialog').dialog("isOpen")) {
					$('#dialog').dialog('close');
				}
			});
		}
	};

	toolsSortPosts = {
		init: function() {
			$('.spToolsPostSort').click(function() {
				var mydata = $(this).data();
				spj.loadTool(mydata.url, mydata.target, '');
			});
		}
	};

	toolsEditPost = {
		init: function() {
			$('.spToolsEdit').click(function() {
				var mydata = $(this).data();
				$('#' + mydata.form).submit();
			});
		}
	};

	toolsDeletePost = {
		init: function() {
			$('.spToolsDeletePost').click(function() {
				var mydata = $(this).data();
				spj.deletePost(mydata.url, mydata.postid, mydata.topicid);
			});
		}
	};

	toolsMovePosts = {
		init: function() {
			$('.spToolsMovePosts').click(function() {
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					cancelScript.init();
					$('#movetonew').click(function() {
						$('#oldtopic').hide();
						$('#newtopic').show();
					});
					$('#movetoold').click(function() {
						$('#newtopic').hide();
						$('#oldtopic').show();
					});
				});
			});
		}
	};

	toolsSortTopics = {
		init: function() {
			$('.spToolsTopicSort').click(function() {
				var mydata = $(this).data();
				spj.loadTool(mydata.url, mydata.target, '');
			});
		}
	};

	toolsLockTopic = {
		init: function() {
			$('.spToolsLockTopic').click(function() {
				var mydata = $(this).data();
				spj.lockTopic(mydata.url);
				if ($('#dialog').dialog("isOpen")) {
					$('#dialog').dialog('close');
				}
			});
		}
	};

	toolsPinTopic = {
		init: function() {
			$('.spToolsPinTopic').click(function() {
				var mydata = $(this).data();
				spj.pinTopic(mydata.url);
				if ($('#dialog').dialog("isOpen")) {
					$('#dialog').dialog('close');
				}
			});
		}
	};

	toolsDeleteTopic = {
		init: function() {
			$('.spToolsDeleteTopic').click(function() {
				var mydata = $(this).data();
				spj.deleteTopic(mydata.url, mydata.topicid, mydata.forumid);
			});
		}
	};

	// public properties

	// public methods
	$(document).ready(function() {
		/* common view handlers */
		quickLinksForum.init();
		quickLinksForumMobile.init();
		quickLinksTopic.init();
		quickLinksTopicMobile.init();
		openCloseControl.init();
		goToBottom.init();
		loginout.init();
		openSearch.init();
		userNotice.init();
		markAllRead.init();
		unreadPosts.init();
		markForumRead.init();
		searchFormSubmit.init();
		advancedSearchForm.init();
		closeMobilePanel.init();
		mobileMenuOpen.init();
		cancelScript.init();
		membersUsergroupSelect.init();
		openDialog.init();

		/* group view handlers */
		groupHeaderOpen.init();
		groupOpenClose.init();

		/* forum view handlers */
		forumPageJump.init();
		newTopicButton.init();
		forumTopicTools.init();

		/* topic view handlers */
		newPostButton.init();
		topicPageJump.init();
		showEditHistory.init();
		printPost.init();
		quotePost.init();
		deletePost.init();
		checkMath.init();
		forumPostTools.init();

		/* api handlers */
		apiShowSpoiler.init();
		apiShowPopupImage.init();
		apiSelectCode.init();

		/* forms handlers */
		formsInsertSmiley.init();
		formsCancelEditor.init();
		formsProcessFlag.init();
		formsOpenEditorBox.init();
		formsEditTimestamp.init();
		formsAddTopic.init();
		formsAddPost.init();
		formsEditPost.init();

		/*****************************************************************
		 load the profile handlers when profile content has been loaded
		 *****************************************************************/
		$('#spProfileContent').on('profilecontentloaded', function() {
			profileClearSignature.init();
			profileShowPermissions.init();
			profileDelUploadedAvatar.init();
			profileShowPoolAvatars.init();
			profileDelPoolAvatar.init();
		});
	});

	// private methods
	function forumToolsInit() {
		openDialog.init();

		toolsViewEmail.init();
		toolsPinPost.init();
		toolsSortPosts.init();
		toolsEditPost.init();
		toolsDeletePost.init();
		toolsMovePosts.init();
		toolsSortTopics.init();
		toolsLockTopic.init();
		toolsPinTopic.init();
		toolsDeleteTopic.init();

		/* let plugins know forum tools opened */
		$('#dialog, #spMobilePanel').trigger('forum_tools_init');
	}
}(window.spj = window.spj || {}, jQuery));
