/* ---------------------------------
Simple:Press - Version 5.0
Front-end Forum Event Handlers Javascript

$LastChangedDate: 2016-07-05 14:44:25 -0500 (Tue, 05 Jul 2016) $
$Rev: 14386 $
------------------------------------ */

/*****************************
common view event handlers
*****************************/

sp_quick_links_forum = {
	init : function() {
		jQuery('#spQuickLinksForumSelect').change( function() {
            spjChangeURL(this);
		});
	}
};

sp_quick_links_forum_mobile = {
	init : function() {
		jQuery('#spQLFTitle').click( function() {
            var mydata = jQuery(this).data();
            spjOpenQL(mydata.tagidlist, mydata.target, mydata.open, mydata.close);
		});
	}
};

sp_quick_links_topic = {
	init : function() {
		jQuery('#spQuickLinksTopicSelect').change( function() {
            spjChangeURL(this);
		});
	}
};

sp_quick_links_topic_mobile = {
	init : function() {
		jQuery('#spQLTitle').click( function() {
            var mydata = jQuery(this).data();
            spjOpenQL(mydata.tagidlist, mydata.target, mydata.open, mydata.close);
		});
	}
};

sp_open_close_control = {
	init : function() {
		jQuery('.spOpenClose').click( function() {
            var mydata = jQuery(this).data();
            spjOpenCloseSection(mydata.targetid, mydata.tagid, mydata.tagclass, mydata.openicon, mydata.closeicon, mydata.tipopen, mydata.tipclose, mydata.setcookie, mydata.label, mydata.linkclass);
		});
	}
};

sp_go_to_bottom = {
	init : function() {
		jQuery('.spGoBottom').click( function() {
            document.getElementById('spForumBottom').scrollIntoView(false);
		});
	}
};

sp_loginout = {
	init : function() {
		jQuery('.spLogInOut').click( function() {
            spjToggleLayer('spLoginForm');
		});
	}
};

sp_user_notice = {
	init : function() {
		jQuery('.spUserNotice').click( function() {
            var mydata = jQuery(this).data();
            spjRemoveNotice(mydata.site, mydata.nid);
		});
	}
};

sp_mark_all_read = {
	init : function() {
		jQuery('.spMarkAllRead').click( function() {
            var mydata = jQuery(this).data();
            spjMarkRead(mydata.ajaxurl);
            if (mydata.mobile == 1) {
                jQuery('#' + mydata.tagid).slideUp();
                spjResetMobileMenu();
            }
		});
	}
};

sp_unread_posts = {
	init : function() {
		jQuery('.spUnreadPostsPopup').click( function() {
            var mydata = jQuery(this).data();
            if (mydata.popup == 1) {
                spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            } else {
                spjInlineTopics(mydata.target, mydata.site, mydata.spinner, mydata.id, mydata.open, mydata.close);
            }
		});
	}
};

sp_mark_forum_read = {
	init : function() {
		jQuery('.spMarkThisForumRead').click( function() {
            var mydata = jQuery(this).data();
            spjMarkForumRead(mydata.ajaxurl, mydata.count);
            if (mydata.mobile == 1) {
                jQuery('#' + mydata.tagid).slideUp();
                spjResetMobileMenu();
            }
		});
	}
};

sp_search_form_submit = {
	init : function() {
		jQuery('.spSearchSubmit').click( function() {
            var mydata = jQuery(this).data();
            spjValidateSearch(this, mydata.id, mydata.type, mydata.min);
		});
	}
};

sp_advanced_search_form = {
	init : function() {
		jQuery('.spAdvancedSearchForm').click( function() {
            var mydata = jQuery(this).data();
            spjToggleLayer(mydata.id);
		});
	}
};

sp_close_mobile_panel = {
	init : function() {
		jQuery(document).on('click', '#spPanelClose', function() {
            spjResetMobileMenu();
		});
	}
};

sp_mobile_menu_open = {
	init : function() {
		jQuery('.spMobileMenuOpen').click( function(e) {
            var mydata = jQuery(this).data();
            spjDialogPanelHTML(this, mydata.source);
            e.preventDefault();
		});
	}
};

sp_cancel_script = {
	init : function() {
		jQuery('.spCancelScript').click( function(event) {
            event.preventDefault();
            spjCancelScript();
		});
	}
};

sp_members_usergroup_select = {
	init : function() {
		jQuery('#sp_usergroup_select').change( function() {
            spjChangeURL(this);
		});
	}
};

sp_open_dialog = {
	init : function() {
		jQuery('.spOpenDialog').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function() {
                sp_cancel_script.init();
            });
		});
	}
};

/*****************************
group view event handlers
*****************************/

sp_group_header_open = {
	init : function() {
		jQuery('.spGroupHeaderOpen').click( function() {
            var mydata = jQuery(this).data();
            if (mydata.collapse == 1) jQuery(mydata.id).click();
		});
	}
};

sp_group_open_close = {
	init : function() {
		jQuery('.spOpenCloseGroup').click( function() {
            var mydata = jQuery(this).data();
            spjOpenCloseForums(mydata.target, mydata.tag, mydata.tclass, mydata.open, mydata.close, mydata.toolopen, mydata.toolclose);
		});
	}
};

/*****************************
forum view event handlers
*****************************/

sp_forum_page_jump = {
	init : function() {
		jQuery('.spForumPageJump').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function(event) {
        		jQuery('.spJumpPage').click( function(event) {
                    event.preventDefault();
                    spjPageJump();
        		});
            });
		});
	}
};

sp_new_topic_button = {
	init : function() {
		jQuery('.spNewTopicButton').click( function() {
            var mydata = jQuery(this).data();
            spjOpenEditor(mydata.form, mydata.type);
		});
	}
};

sp_forum_topic_tools = {
	init : function() {
		jQuery('.spForumTopicTools').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function() {
                sp_forum_tools_init();
            });
		});
	}
};
/*****************************
topic view event handlers
*****************************/

sp_new_post_button = {
	init : function() {
		jQuery('.spNewPostButton').click( function() {
            var mydata = jQuery(this).data();
            spjOpenEditor(mydata.form, mydata.type);
		});
	}
};

sp_topic_page_jump = {
	init : function() {
		jQuery('.spTopicPageJump').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function(event) {
        		jQuery('.spJumpPage').click( function(event) {
                    event.preventDefault();
                    spjPageJump();
        		});
            });
		});
	}
};

sp_show_edit_history = {
	init : function() {
		jQuery('.spEditPostHistory').click( function() {
            var mydata = jQuery(this).data();
            spjDialogHtml(this, mydata.html, mydata.label, mydata.width, mydata.height, mydata.align);
		});
	}
};

sp_print_post = {
	init : function() {
		jQuery('.spPrintThisPost').click( function() {
            var mydata = jQuery(this).data();
            jQuery('#' + mydata.postid).printThis();
            return false;
		});
	}
};

sp_quote_post = {
	init : function() {
		jQuery('.spQuotePost').click( function() {
            var mydata = jQuery(this).data();
            spjQuotePost(mydata.postid, mydata.intro, mydata.forumid, mydata.url);
		});
	}
};

sp_delete_post = {
	init : function() {
		jQuery('.spDeletePost').click( function() {
            var mydata = jQuery(this).data();
            spjDeletePost(mydata.url, mydata.postid, mydata.topicid);
		});
	}
};

sp_check_math = {
	init : function() {
		jQuery('.spMathCheck').keyup( function() {
            var mydata = jQuery(this).data();
            if (mydata.type == 'topic') {
                spjSetTopicButton(this, mydata.val1, mydata.val2, mydata.buttongood, mydata.buttonbad);
            } else {
                spjSetPostButton(this, mydata.val1, mydata.val2, mydata.buttongood, mydata.buttonbad);
            }
		});
	}
};

sp_forum_post_tools = {
	init : function() {
		jQuery('.spForumPostTools').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function() {
                sp_forum_tools_init();
            });
        });
    }
};

/*****************************
api event handlers
*****************************/

sp_api_show_spoiler = {
	init : function() {
		jQuery('.spShowSpoiler').click( function() {
            var mydata = jQuery(this).data();
            spjSpoilerToggle(mydata.spoilerid, mydata.reveal, mydata.hide);
		});
	}
};

sp_api_show_popup_image = {
	init : function() {
		jQuery('.spShowPopupImage').click( function() {
            var mydata = jQuery(this).data();
            spjPopupImage(mydata.src, mydata.width, mydata.height, mydata.constrain);
		});
	}
};

sp_api_select_code = {
	init : function() {
		jQuery('.sfcodeselect').click( function() {
            var mydata = jQuery(this).data();
            spjSelectCode(mydata.codeid);
		});
	}
};

/*****************************
forms event handlers
*****************************/

sp_forms_insert_smiley = {
	init : function() {
		jQuery('.spEditor img.spSmiley').click( function() {
            var mydata = jQuery(this).data();
            spjEdInsertSmiley(mydata.url, mydata.title, mydata.path, mydata.code);
		});
	}
};

sp_forms_cancel_editor = {
	init : function() {
		jQuery('.spCancelEditor').click( function() {
            var mydata = jQuery(this).data();
            if (confirm(mydata.msg)) {
                spjEdCancelEditor();
            }
		});
	}
};

sp_forms_process_flag = {
	init : function() {
		jQuery('.spProcessFlag').click( function() {
            var mydata = jQuery(this).data();
            spjSetProcessFlag(this);
            if (confirm(mydata.msg)) {
                document.editpostform.submit();
            }
        });
	}
};

sp_forms_open_editor_box = {
	init : function() {
		jQuery('.spEditorBoxOpen').click( function() {
            var mydata = jQuery(this).data();
            spjOpenEditorBox(mydata.box);
        });
	}
};

sp_forms_edit_timestamp = {
	init : function() {
		jQuery('#sfeditTimestamp').click( function() {
            spjToggleLayer('spHiddenTimestamp');
        });
	}
};

sp_forms_add_topic = {
	init : function() {
		jQuery('#addtopic').submit( function(event) {
            var mydata = jQuery(this).data();
            var status = spjValidatePostForm(this, mydata.guest, 1, mydata.img);
            if (!status) event.preventDefault();
        });
	}
};

sp_forms_add_post = {
	init : function() {
		jQuery('#addpost').submit( function(event) {
            var mydata = jQuery(this).data();
            var status = spjValidatePostForm(this, mydata.guest, 0, mydata.img);
            if (!status) event.preventDefault();
        });
	}
};

sp_forms_edit_post = {
	init : function() {
		jQuery('#editpostform').submit( function() {
            spjSetProcessFlag(this);
        });
	}
};

/*****************************
profile event handlers
*****************************/

sp_profile_clear_signature = {
	init : function() {
   		jQuery('.spClearSignature').on('click', function() {
            spjClearIt('postitem');
		});
	}
};

sp_profile_show_permissions = {
	init : function() {
		jQuery('.spLoadPermissions').on ('click', function() {
            var mydata = jQuery(this).data();
            spjLoadTool(mydata.url, 'perm' + mydata.id, mydata.img);
       		jQuery('.spProfileUserPermissions').on('click', '#spClosePerms' + mydata.id, function() {
                jQuery('#perm' + mydata.id).html('');
            });
		});
	}
};

sp_profile_del_uploaded_avatar = {
	init : function() {
   		jQuery('#spDeleteUploadedAvatar').on('click', function() {
            var mydata = jQuery(this).data();
            spjRemoveAvatar(mydata.url, mydata.target, mydata.spinner);
		});
	}
};

sp_profile_show_pool_avatars = {
	init : function() {
   		jQuery('.spShowAvatarPool').on('click', function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function() {
           		jQuery('.spSelectPoolAvatar').on('click', function() {
                    var mydata = jQuery(this).data();
                    spjSelAvatar(mydata.src, mydata.file, mydata.text);
                    if (sp_platform_vars.device == 'mobile') {
                        spjResetMobileMenu();
                    } else {
                        jQuery('#dialog').dialog('close');
                    }
        		});
            });
		});
	}
};

sp_profile_del_pool_avatar = {
	init : function() {
   		jQuery('#spDeletePoolAvatar').on('click', function() {
            var mydata = jQuery(this).data();
            spjRemovePool(mydata.url, mydata.target, mydata.spinner);
		});
	}
};

/*****************************
forum tools event handlers
*****************************/

sp_tools_view_email = {
	init : function() {
		jQuery('.spToolsEmail').click( function() {
            var mydata = jQuery(this).data();
            spjDialogHtml(this, mydata.html, mydata.title, mydata.width, mydata.height, mydata.align);
		});
	}
};

sp_tools_pin_post = {
	init : function() {
		jQuery('.spToolsPin').click( function() {
            var mydata = jQuery(this).data();
            spjPinPost(mydata.url);
			if (jQuery('#dialog').dialog("isOpen")) {
				jQuery('#dialog').dialog('close');
			}
		});
	}
};

sp_tools_sort_posts = {
	init : function() {
		jQuery('.spToolsPostSort').click( function() {
            var mydata = jQuery(this).data();
            spjLoadTool(mydata.url, mydata.target, '');
		});
	}
};

sp_tools_edit_post = {
	init : function() {
		jQuery('.spToolsEdit').click( function() {
            var mydata = jQuery(this).data();
            jQuery('#' + mydata.form).submit();
		});
	}
};

sp_tools_delete_post = {
	init : function() {
		jQuery('.spToolsDeletePost').click( function() {
            var mydata = jQuery(this).data();
            spjDeletePost(mydata.url, mydata.postid, mydata.topicid);
		});
	}
};

sp_tools_move_posts = {
	init : function() {
		jQuery('.spToolsMovePosts').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
            jQuery('#dialog, #spMobilePanel').one('opened', function() {
                sp_cancel_script.init();
        		jQuery('#movetonew').click( function() {
                    jQuery('#oldtopic').hide();
                    jQuery('#newtopic').show();
        		});
        		jQuery('#movetoold').click( function() {
                    jQuery('#newtopic').hide();
                    jQuery('#oldtopic').show();
        		});
            });
		});
	}
};

sp_tools_sort_topics = {
	init : function() {
		jQuery('.spToolsTopicSort').click( function() {
            var mydata = jQuery(this).data();
            spjLoadTool(mydata.url, mydata.target, '');
		});
	}
};

sp_tools_lock_topic = {
	init : function() {
		jQuery('.spToolsLockTopic').click( function() {
            var mydata = jQuery(this).data();
            spjLockTopic(mydata.url);
			if (jQuery('#dialog').dialog("isOpen")) {
				jQuery('#dialog').dialog('close');
			}
		});
	}
};

sp_tools_pin_topic = {
	init : function() {
		jQuery('.spToolsPinTopic').click( function() {
            var mydata = jQuery(this).data();
            spjPinTopic(mydata.url);
			if (jQuery('#dialog').dialog("isOpen")) {
				jQuery('#dialog').dialog('close');
			}
		});
	}
};

sp_tools_delete_topic = {
	init : function() {
		jQuery('.spToolsDeleteTopic').click( function() {
            var mydata = jQuery(this).data();
            spjDeleteTopic(mydata.url, mydata.topicid, mydata.forumid);
		});
	}
};

/***********************************************
load the event handlers up on document ready
***********************************************/

jQuery(document).ready(function() {
    /* common view handlers */
    sp_quick_links_forum.init();
    sp_quick_links_forum_mobile.init();
    sp_quick_links_topic.init();
    sp_quick_links_topic_mobile.init();
    sp_open_close_control.init();
    sp_go_to_bottom.init();
    sp_loginout.init();
    sp_user_notice.init();
    sp_mark_all_read.init();
    sp_unread_posts.init();
    sp_mark_forum_read.init();
    sp_search_form_submit.init();
    sp_advanced_search_form.init();
    sp_close_mobile_panel.init();
    sp_mobile_menu_open.init();
    sp_cancel_script.init();
    sp_members_usergroup_select.init();
    sp_open_dialog.init();

    /* group view handlers */
    sp_group_header_open.init();
    sp_group_open_close.init();

    /* forum view handlers */
    sp_forum_page_jump.init();
    sp_new_topic_button.init();
    sp_forum_topic_tools.init();

    /* topic view handlers */
    sp_new_post_button.init();
    sp_topic_page_jump.init();
    sp_show_edit_history.init();
    sp_print_post.init();
    sp_quote_post.init();
    sp_delete_post.init();
    sp_check_math.init();
    sp_forum_post_tools.init();

    /* api handlers */
    sp_api_show_spoiler.init();
    sp_api_show_popup_image.init();
    sp_api_select_code.init();

    /* forms handlers */
    sp_forms_insert_smiley.init();
    sp_forms_cancel_editor.init();
    sp_forms_process_flag.init();
    sp_forms_open_editor_box.init();
    sp_forms_edit_timestamp.init();
    sp_forms_add_topic.init();
    sp_forms_add_post.init();
    sp_forms_edit_post.init();

    /*****************************************************************
    load the profile handlers when profile content has been loaded
    *****************************************************************/

    jQuery('#spProfileContent').on('profilecontentloaded', function() {
        sp_profile_clear_signature.init();
        sp_profile_show_permissions.init();
        sp_profile_del_uploaded_avatar.init();
        sp_profile_show_pool_avatars.init();
        sp_profile_del_pool_avatar.init();
    });
});

/***********************************************************************
load the forum tools handlers when forum tools dialog has been opened
***********************************************************************/

function sp_forum_tools_init() {
    sp_open_dialog.init();

    sp_tools_view_email.init();
    sp_tools_pin_post.init();
    sp_tools_sort_posts.init();
    sp_tools_edit_post.init();
    sp_tools_delete_post.init();
    sp_tools_move_posts.init();
    sp_tools_sort_topics.init();
    sp_tools_lock_topic.init();
    sp_tools_pin_topic.init();
    sp_tools_delete_topic.init();

    /* let plugins know forum tools opened */
    jQuery('#dialog, #spMobilePanel').trigger('forum_tools_init');
}