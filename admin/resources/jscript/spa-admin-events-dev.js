/* ---------------------------------
Simple:Press - Version 5.0
Forum Admin Event Handlers Javascript

$LastChangedDate: 2016-06-22 19:23:07 -0700 (Wed, 22 Jun 2016) $
$Rev: 14304 $
------------------------------------ */

/*****************************
general admin event handlers
*****************************/

spa_toggle_layer = {
	init : function() {
        jQuery('.spLayerToggle').off();
		jQuery('.spLayerToggle').click( function() {
            var mydata = jQuery(this).data();
            spjToggleLayer(mydata.target);
		});
	}
};

spa_toggle_row = {
	init : function() {
        jQuery('.spToggleRow').off();
		jQuery('.spToggleRow').click( function() {
            var mydata = jQuery(this).data();
	           jQuery(mydata.target).show();
		});
	}
};

spa_delete_row = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spDeleteRow');
		jQuery('#sfmaincontainer').on('click', '.spDeleteRow', function() {
            var mydata = jQuery(this).data();
            if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
                spjDelRow(mydata.url, mydata.target);
            }
		});
	}
};

spa_delete_row_reload = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spDeleteRowReload');
		jQuery('#sfmaincontainer').on('click', '.spDeleteRowReload', function() {
            var mydata = jQuery(this).data();
            if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
                spjDelRowReload(mydata.url, mydata.reload);
            }
		});
	}
};

spa_load_form = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spLoadForm');
		jQuery('#sfmaincontainer').on('click', '.spLoadForm', function() {
            var mydata = jQuery(this).data();
            spjLoadForm(mydata.form, mydata.url, mydata.target, mydata.img, mydata.id, mydata.open);
		});
	}
};

spa_cancel_form = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spCancelForm');
		jQuery('#sfmaincontainer').on('click', '.spCancelForm', function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.target).html('');
		});
	}
};

spa_load_ajax = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spLoadAjax');
		jQuery('#sfmaincontainer').on('click', '.spLoadAjax', function() {
            var mydata = jQuery(this).data();
            spjLoadAjax(mydata.url, mydata.target, mydata.img);
		});
	}
};

spa_reload_form = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spReloadForm');
		jQuery('#sfmaincontainer').on('click', '.spReloadForm', function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.target).click();
		});
	}
};

spa_show_element = {
	init : function() {
		jQuery('#sfmaincontainer').off('click', '.spShowElement');
		jQuery('#sfmaincontainer').on('click', '.spShowElement', function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.target).show();
		});
	}
};

spa_check_all = {
	init : function() {
        jQuery('.spPruneCheckAll').off();
		jQuery('.spPruneCheckAll').click( function() {
            var mydata = jQuery(this).data();
            spjCheckAll(mydata.target);
		});
	}
};

spa_uncheck_all = {
	init : function() {
        jQuery('.spPruneUncheckAll').off();
		jQuery('.spPruneUncheckAll').click( function() {
            var mydata = jQuery(this).data();
            spjUnCheckAll(mydata.target);
		});
	}
};

spa_admin_tool = {
	init : function() {
        jQuery('.spAdminTool').off();
		jQuery('.spAdminTool').click( function() {
            var mydata = jQuery(this).data();
            spjAdminTool(mydata.url, mydata.target, mydata.img);
		});
	}
};

spa_search_tool = {
	init : function() {
        jQuery('.key-word').off();
		jQuery('.key-word').click( function() {
            var mydata = jQuery(this).data();
            spjKeywordSearch(mydata.url);
		});
	}
};

spa_admin_help = {
	init : function() {
        jQuery('.spHelpLink').off();
		jQuery('.spHelpLink').click( function() {
            var mydata = jQuery(this).data();
            spjDialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
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

spa_open_dialog = {
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

spa_troubleshooting = {
	init : function() {
        jQuery('.spTroubleshoot').off();
		jQuery('.spTroubleshoot').click( function() {
            var mydata = jQuery(this).data();
            spjTroubleshooting(mydata.url, mydata.target);
		});
	}
};

spa_accordion_load_form = {
	init : function() {
		jQuery('#sfadminmenu').off('click', '.spAccordionLoadForm');
		jQuery('#sfadminmenu').on('click', '.spAccordionLoadForm', function() {
            var mydata = jQuery(this).data();
            spjLoadForm(mydata.form, mydata.url, mydata.target, mydata.img, mydata.id, mydata.open, mydata.upgrade, mydata.admin, mydata.save, mydata.sform, mydata.reload);
		});
	}
};

/*****************************
admin multiselect event handlers
*****************************/

spa_multiselect_transfer = {
	init : function() {
		jQuery('#sfmaincontainer').on('click', '.spTransferList', function() {
            var mydata = jQuery(this).data();
            spjTransferSelectList(mydata.from, mydata.to, mydata.msg, mydata.exceed, mydata.recip);
		});
	}
};

spa_multiselect_update = {
	init : function() {
		jQuery('#sfmaincontainer').on('click', '.spUpdateList', function() {
            var mydata = jQuery(this).data();
            spjUpdateMultiSelectList(mydata.url, mydata.uid);
		});
	}
};

spa_multiselect_filter = {
	init : function() {
		jQuery('#sfmaincontainer').on('click', '.spFilterList', function() {
            var mydata = jQuery(this).data();
            spjFilterMultiSelectList(mydata.url, mydata.uid, mydata.image);
		});
	}
};

/*****************************
admin forums event handlers
*****************************/

spa_forums_expand_collapse_group = {
	init : function() {
		jQuery('.spExpandCollapseGroup').click( function() {
            var mydata = jQuery(this).data();
            spjExpCollForums(this, mydata.target);
		});
	}
};

/*****************************
admin components event handlers
*****************************/

spa_components_special_rank_add = {
	init : function() {
		jQuery('.spSpecialRankAdd, spSpecialRankDel').click( function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.target).each(function(i) {
                jQuery(this).attr('selected', 'selected');
            });
		});
	}
};

spa_components_special_rank_cancel = {
	init : function() {
		jQuery('.spSpecialRankCancel').click( function() {
            var mydata = jQuery(this).data();
            spjToggleLayer(mydata.loc);
            jQuery(mydata.target).html('');
		});
	}
};

spa_components_special_rank_show = {
	init : function() {
		jQuery('.spSpecialRankShow').click( function() {
            var mydata = jQuery(this).data();
            spjToggleRow(mydata.loc);
            spjShowMemberList(mydata.site, mydata.img, mydata.id);
		});
	}
};

spa_components_special_rank_form = {
	init : function() {
		jQuery('.spSpecialRankForm').click( function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.loc).show();
            spjLoadForm(mydata.form, mydata.base, mydata.target, mydata.img, mydata.id, 'open');
		});
	}
};

/*****************************
admin plugins event handlers
*****************************/

spa_plugins_upload = {
	init : function() {
		jQuery('.spPluginUpload').click( function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.target).attr('disabled', 'disabled');
            document.sfpluginuploadform.submit();
		});
	}
};

/*****************************
admin profiles event handlers
*****************************/

spa_profile_avatar_defaults = {
	init : function() {
		jQuery('.spCheckAvatarDefaults').click( function() {
            spjCheckAvatarDefaults(this);
		});
	}
};

spa_profile_avatar_update_priorities = {
	init : function() {
		jQuery('.spProfileAvatarUpdate').change( function() {
            var mydata = jQuery(this).data();
            spjAv(mydata.target);
		});
	}
};

/*****************************
admin themes event handlers
*****************************/

spa_themes_delete_confirm = {
	init : function() {
		jQuery('.spThemeDeleteConfirm').click( function(event) {
            var mydata = jQuery(this).data();
            if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
                return true;
            } else {
                event.preventDefault();
            }
		});
	}
};

spa_themes_upload = {
	init : function() {
		jQuery('.spThemeUpload').click( function() {
            var mydata = jQuery(this).data();
            jQuery(mydata.target).attr('disabled', 'disabled');
            document.sfthemeuploadform.submit();
		});
	}
};

/*****************************
admin usegroups event handlers
*****************************/

spa_ug_show_members = {
	init : function() {
		jQuery('.spUsergroupShowMembers').click( function() {
            var mydata = jQuery(this).data();
            spjShowMemberList(mydata.url, mydata.img, mydata.id);
		});
	}
};

/*****************************
admin forums event handlers
*****************************/

spa_set_forum_options = {
	init : function() {
		jQuery('.spForumSetOptions').change( function() {
            var mydata = jQuery(this).data();
            spjSetForumOptions(mydata.target);
		});
	}
};

spa_set_forum_sequence = {
	init : function() {
		jQuery('.spForumSetSequence').change( function() {
            spjSetForumSequence();
		});
	}
};

spa_set_forum_slug = {
	init : function() {
		jQuery('#sfmaincontainer').on('change', '.spForumSetSlug', function() {
            var mydata = jQuery(this).data();
            spjSetForumSlug(this, mydata.url, mydata.target, mydata.type);
		});
	}
};

/***********************************************
load the event handlers up on document ready
***********************************************/

jQuery(document).ready(function() {
    jQuery('#sfmaincontainer').on('adminformloaded', function() {
        spa_toggle_layer.init();
        spa_toggle_row.init();
        spa_delete_row.init();
        spa_delete_row_reload.init();
        spa_load_form.init();
        spa_cancel_form.init();
        spa_load_ajax.init();
        spa_reload_form.init();
        spa_show_element.init();
        spa_check_all.init();
        spa_uncheck_all.init();
        spa_admin_tool.init();
        spa_admin_help.init();
        spa_open_dialog.init();
        spa_troubleshooting.init();
        spa_accordion_load_form.init();
        spa_multiselect_transfer.init();
        spa_multiselect_update.init();
        spa_multiselect_filter.init();
        spa_forums_expand_collapse_group.init();
        spa_components_special_rank_add.init();
        spa_components_special_rank_cancel.init();
        spa_components_special_rank_show.init();
        spa_components_special_rank_form.init();
        spa_plugins_upload.init();
        spa_profile_avatar_defaults.init();
        spa_profile_avatar_update_priorities.init();
        spa_themes_delete_confirm.init();
        spa_themes_upload.init();
        spa_ug_show_members.init();
        spa_set_forum_options.init();
        spa_set_forum_sequence.init();
        spa_set_forum_slug.init();
    });

    jQuery('#sfmaincontainer').on('troubleshootingformloaded', function() {
        spa_search_tool.init();
    });
});
