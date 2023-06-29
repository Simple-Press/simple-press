/* ---------------------------------
 Simple:Press - Version 5.0
 Forum Admin Event Handlers Javascript

 $LastChangedDate: 2016-06-22 19:23:07 -0700 (Wed, 22 Jun 2016) $
 $Rev: 14304 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties
	/*****************************
	 general admin event handlers
	 *****************************/

	toggleLayer = {
            init: function () {
                $('.spLayerToggle').off();
                $('.spLayerToggle').click(function () {
                    var $el = $(this), $li = $el.closest('li');
                    if ($li.length) {
                        var $elInlineEdit = $li.find('.sf-inline-edit:first');
                        if ($elInlineEdit.length) {
                            if ($elInlineEdit.is(':visible')) {
                                $li.removeClass('sf-open');
                            } else {
                                $li.addClass('sf-open');
                            }
                        }
                    }
                    //var mydata = $el.data();
                    //spj.toggleLayer(mydata.target);
                });
            }
	};

	/*toggleRow = {
		init: function() {
			$('.spToggleRow').off();
			$('.spToggleRow').click(function() {
				var mydata = $(this).data();
				$(mydata.target).show();
			});
		}
	};*/

	spj.deleteRow = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spDeleteRow');
			$('#sfmaincontainer').on('click', '.spDeleteRow', function() {
				var mydata = $(this).data();
				if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
					spj.delRow(mydata.url, mydata.target);
				}
			});
		},
	};

	deleteRowReload = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spDeleteRowReload');
			$('#sfmaincontainer').on('click', '.spDeleteRowReload', function() {
				var mydata = $(this).data();
				if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
					spj.delRowReload(mydata.url, mydata.reload);
				}
			});
		}
	};
        
        hideRow = {
            init: function() {
			$('#sfmaincontainer').off('click', '.spHideRow');
			$('#sfmaincontainer').on('click', '.spHideRow', function() {
				$(this).closest('tr').hide();
                        });
		}
        };

	loadForm = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spLoadForm');
			$('#sfmaincontainer').on('click', '.spLoadForm', function() {
				var mydata = $(this).data();
				spj.loadForm(mydata.form, mydata.url, mydata.target, mydata.img, mydata.id, mydata.open);
			});
		}
	};

	cancelForm = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spCancelForm');
			$('#sfmaincontainer').on('click', '.spCancelForm', function() {
				//var mydata = $(this).data();
				//$(mydata.target).html('');
				if ($(this).closest('.inline-form-container').hasClass('isOpen')) {
					$(this).closest('.inline-form-container').removeClass('isOpen');
				}
				$(this).closest('form').remove();
			});
		}
	};

	loadAjax = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spLoadAjax');
			$('#sfmaincontainer').on('click', '.spLoadAjax', function() {
				var mydata = $(this).data();
				spj.loadAjax(mydata.url, mydata.target, mydata.img, mydata );
			});
		}
	};

	reloadForm = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spReloadForm');
			$('#sfmaincontainer').on('click', '.spReloadForm', function() {
				var mydata = $(this).data();
				$(mydata.target).click();
			});
		}
	};

	showElement = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spShowElement');
			$('#sfmaincontainer').on('click', '.spShowElement', function() {
				var mydata = $(this).data();
				$(mydata.target).show();
			});
		}
	};

	checkAll = {
		init: function() {
			$('.spPruneCheckAll').off();
			$('.spPruneCheckAll').click(function() {
				var mydata = $(this).data();
				spj.checkAll(mydata.target);
			});
		}
	};

	uncheckAll = {
		init: function() {
			$('.spPruneUncheckAll').off();
			$('.spPruneUncheckAll').click(function() {
				var mydata = $(this).data();
				spj.uncheckAll(mydata.target);
			});
		}
	};

	adminTool = {
		init: function() {
			$('.spAdminTool').off();
			$('.spAdminTool').click(function() {
				var mydata = $(this).data();
				spj.adminTool(mydata.url, mydata.target, mydata.img);
			});
		}
	};

	searchTool = {
		init: function() {
			$('.key-word').off();
			$('.key-word').click(function() {
				var mydata = $(this).data();
				spj.keywordSearch(mydata.url);
			});
		}
	};

	adminHelp = {
                init: function() {
			$('#sfmaincontainer').off('click', '.spHelpLink');
                        $('#sfmaincontainer').on('click', '.spHelpLink', function(eventObject) {
                        
				eventObject.preventDefault();
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
			});
		}
	};

	spCancelScript = {
		init: function() {
			$('.spCancelScript').click(function(event) {
				event.preventDefault();
				spj.cancelScript();
			});
		}
	};

	openDialog = {
		init: function() {
                        
                        
                        $('#sfmaincontainer').off('click', '.spOpenDialog');
                        $('#sfmaincontainer').on('click', '.spOpenDialog', function() {
                        
				var mydata = $(this).data();
				spj.dialogAjax(this, mydata.site, mydata.label, mydata.width, mydata.height, mydata.align);
				$('#dialog, #spMobilePanel').one('opened', function() {
					spCancelScript.init();
				});
			});
		}
	};

	troubleshooting = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spTroubleshoot');
                        $('#sfmaincontainer').on('click', '.spTroubleshoot', function() {
                        
				var mydata = $(this).data();
				spj.troubleshooting(mydata.url, mydata.target);
			});
		}
	};

	accordionLoadForm = {
		init: function() {
			$('#sfadminmenu').off('click', '.spAccordionLoadForm');
                        
                        var _this = this;
			$('#sfadminmenu').on('click', '.spAccordionLoadForm', function() {
				var mydata = $(this).data();
				spj.loadForm(mydata.form, mydata.url, mydata.target, mydata.img, mydata.id, mydata.open, mydata.upgrade, mydata.admin, mydata.save, mydata.sform, mydata.reload);
                                $('#sfmaincontainer').on('adminformloaded', _this.scrollPage );
                                
			});
		},
                
                
                scrollPage: function() {
                        $('html, body').animate({
                                scrollTop: $("#sfmaincontainer").offset().top - 50
                        }, 500);
                        
                        $('#sfmaincontainer').off('adminformloaded', this.scrollPage );
                }
                
	};

	/*****************************
	 admin multiselect event handlers
	 *****************************/

	multiselectTransfer = {
		init: function() {
                        $('#sfmaincontainer').off('click', '.spTransferList');
			$('#sfmaincontainer').on('click', '.spTransferList', function() {
				var mydata = $(this).data();
				spj.transferMultiSelectList(mydata.from, mydata.to, mydata.msg, mydata.exceed, mydata.recip);
			});
		}
	};

	multiselectUpdate = {
		init: function() {
                        $('#sfmaincontainer').off('click', '.spUpdateList');
			$('#sfmaincontainer').on('click', '.spUpdateList', function() {
				var mydata = $(this).data();
				spj.updateMultiSelectList(mydata.url, mydata.uid);
			});
		}
	};

	multiselectFilter = {
		init: function() {
                        $('#sfmaincontainer').off('click', '.spFilterList');
			$('#sfmaincontainer').on('click', '.spFilterList', function() {
				var mydata = $(this).data();
				spj.filterMultiSelectList(mydata.url, mydata.uid, mydata.image);
			});
                        
                        
                        $("#sfmaincontainer").off('keydown', '.sf-filter-auto [type="search"]');
                        
                        $("#sfmaincontainer").on('keydown', '.sf-filter-auto [type="search"]' ,function (e) {
                                
                                if (e.keyCode === 13) {
                                        e.preventDefault();
                                        return false;
                               }
                                
                        });
                        
                        
                        $("#sfmaincontainer").off('keyup', '.sf-filter-auto [type="search"]');
                        
                        $("#sfmaincontainer").on('keyup', '.sf-filter-auto [type="search"]' ,function (e) {
                                e.preventDefault();
                                if (e.keyCode === 13) {
                                        $(this).closest('.sf-filter-auto').find('.spFilterList').click();
                                }
                                
                                
                        });
                        
		}
	};

	/*****************************
	 admin forums event handlers
	 *****************************/

	forumsExpandCollapseGroup = {
		init: function() {
			$('.spExpandCollapseGroup').click(function() {
				var mydata = $(this).data();
				spj.expandCollapseForums(this, mydata.target);
			});
		}
	};

	/*****************************
	 admin components event handlers
	 *****************************/

	componentsSpecialRankAdd = {
		init: function() {
			$('.spSpecialRankAdd, spSpecialRankDel').click(function() {
				var mydata = $(this).data();
				$(mydata.target).each(function(i) {
					$(this).attr('selected', 'selected');
				});
			});
		}
	};

	componentsSpecialRankCancel = {
		init: function() {
			$('.spSpecialRankCancel').click(function() {
				var mydata = $(this).data();
				spj.toggleLayer(mydata.loc);
				$(mydata.target).html('');
			});
		}
	};

	componentsSpecialRankShow = {
		init: function() {
			$('#sfmaincontainer').off('click', '.spSpecialRankShow');
                        $('#sfmaincontainer').on('click', '.spSpecialRankShow', function() {
                        
				var mydata = $(this).data();
				spj.toggleRow(mydata.loc);
				spj.showMemberList(mydata.site, mydata.img, mydata.id, mydata.target);
			});
		}
	};

	componentsSpecialRankForm = {
		init: function() {
                        
                        $('#sfmaincontainer').off('click', '.spSpecialRankForm');
                        
                        $('#sfmaincontainer').on('click', '.spSpecialRankForm', function() {
                                
                        
				var mydata = $(this).data();
				$(mydata.loc).show();
				spj.loadForm(mydata.form, mydata.base, mydata.target, mydata.img, mydata.id, 'open');
			});
		}
	};

	/*****************************
	 admin plugins event handlers
	 *****************************/

	pluginsUpload = {
		init: function() {
                        
                        $('#sfmaincontainer').off('click', '.spPluginUpload');
                        
                        $('#sfmaincontainer').on('click', '.spPluginUpload', function() {
				var mydata = $(this).data();
				$(mydata.target).attr('disabled', 'disabled');
				document.sfpluginuploadform.submit();
                        });
                        
		}
	};

	/*****************************
	 admin profiles event handlers
	 *****************************/

	profileAvatarDefaults = {
		init: function() {
			$('.spCheckAvatarDefaults').click(function() {
				spj.checkAvatarDefaults(this);
			});
		}
	};

	profileAvatarUpdatePriorities = {
		init: function() {
			$('.spProfileAvatarUpdate').change(function() {
				var mydata = $(this).data();
				spj.avatarPriority(mydata.target);
			});
		}
	};

	/*****************************
	 admin themes event handlers
	 *****************************/

	themesDeleteConfirm = {
		init: function() {
			$('.spThemeDeleteConfirm').click(function(event) {
				var mydata = $(this).data();
				if (typeof mydata.msg == 'undefined' || confirm(mydata.msg)) {
					return true;
				} else {
					event.preventDefault();
				}
			});
		}
	};

	themesUpload = {
		init: function() {
			$('.spThemeUpload').click(function() {
				var mydata = $(this).data();
				$(mydata.target).attr('disabled', 'disabled');
				document.sfthemeuploadform.submit();
			});
		}
	};
        
	/*****************************
	 admin usegroups event handlers
	 *****************************/

	ugShowMembers = {
		init: function() {
                        $('body').undelegate( ".spUsergroupShowMembers", "click" );
                        $('body').delegate('.spUsergroupShowMembers', 'click', function() {
                        
				var mydata = $(this).data();
				spj.showMemberList(mydata.url, mydata.img, mydata.id, mydata.target);
			});
		}
	};

	/*****************************
	 admin forums event handlers
	 *****************************/

	setForumOptions = {
		init: function() {
                        
                        $('#sfmaincontainer').off('change', '.spForumSetOptions');
			$('#sfmaincontainer').on('change', '.spForumSetOptions', function() {
                                var mydata = $(this).data();
				spj.setForumOptions(mydata.target);
                        });
		}
	};

	setForumSequence = {
		init: function() {
                        
                        
                        $('#sfmaincontainer').off('change', '.spForumSetSequence');
			$('#sfmaincontainer').on('change', '.spForumSetSequence', function() {
                                spj.setForumSequence();
                        });
		}
	};

	setForumSlug = {
		init: function() {
                        $('#sfmaincontainer').off('change', '.spForumSetSlug');
			$('#sfmaincontainer').on('change', '.spForumSetSlug', function() {
				var mydata = $(this).data();
				spj.setForumSlug(this, mydata.url, mydata.target, mydata.type);
			});
		}
	};
        
        prepareEditors = {
		init: function() {
                        
                        $( '#sfmaincontainer' ).find('.wp-editor-wrap').each( function () {
                                spj.prepareAjaxEditor( $(this) );
                        });
                        
		}
	};
        
        prepareDatePicker = {
                init : function() {
                        $('.sp-datepicker-field').datepicker({
                                beforeShow: function(input, inst) {
                                        $("#ui-datepicker-div").addClass("sp-datepicker");
                                }
                        });
                }
        };
        
        filtering = {
                init : function() {
                    $('#sfmaincontainer').off('blur', '[data-filter-url][data-target]');
                    $('#sfmaincontainer').on('blur', '[data-filter-url][data-target]', function() {
                        var $el = $(this);
                        $($el.data('target')).load(
                                $el.data('filterUrl') 
                                + '&filter=' + encodeURIComponent($el.val())+ '&rnd='
                                + new Date().getTime()
                                );
                    });
                    $('#sfmaincontainer').off('click', '.sf-filtering .sf-alphabet button');
                    $('#sfmaincontainer').on('click', '.sf-filtering .sf-alphabet button', function(e) {
                        e.preventDefault();
                        var $el = $(this), $filtering = $el.closest('.sf-filtering');
                        $filtering.find('[data-filter-url][data-target]').val($el.val()).blur();
                        $filtering.find('.sf-alphabet button.sf-active').removeClass('sf-active');
                        $el.addClass('sf-active');
                    });
					$('#sfmaincontainer').off('click', '.sf-filtering button.sf-button-secondary');
					$('#sfmaincontainer').on('click', '.sf-filtering button.sf-button-secondary', function(e) {
						e.preventDefault();

						let $el = $(this), $filtering = $el.closest('.sf-filtering');
						let value = $filtering.find('[data-filter-url][data-target]');

						$filtering.find('[data-filter-url][data-target]').val(value.val()).blur();

					});
                }
        };
        
        tableCheckUncheckCb = {
                init : function() {
                    $('#sfmaincontainer').off('click', 'table [data-bind-cb]');
                    $('#sfmaincontainer').on('click', 'table [data-bind-cb]', function() {
                        var $el = $(this);
                        $el.closest('table').find($el.data('bindCb')).prop('checked', $el.prop('checked'));
                    });
                }
        };

        opener = {
                init : function() {
                    $('#sfmaincontainer').off('click', '.sf-opener-button-open');
                    $('#sfmaincontainer').on('click', '.sf-opener-button-open', function() {
                        var $el = $(this), $root = $el.closest('.sf-opener');
                        if($root.length) {
                            var $target = $root.find('.sf-opener-target');
                            if($target.length) {
                                $root[$target.is(':visible') ? 'removeClass' : 'addClass']('sf-open');
                            }
                        }
                    }); $('#sfmaincontainer').off('click', '.sf-opener-button-open');
                    $('#sfmaincontainer').on('click', '.sf-opener-button-open', function() {
                        var $el = $(this), $root = $el.closest('.sf-opener');
                        if($root.length) {
                            var $target = $root.find('.sf-opener-target');
                            if($target.length) {
                                $root[$target.is(':visible') ? 'removeClass' : 'addClass']('sf-open');
                            }
                        }
                    });
                    $('#sfmaincontainer').off('click', '.sf-opener-button-close');
                    $('#sfmaincontainer').on('click', '.sf-opener-button-close', function() {
                        var $el = $(this), $root = $el.closest('.sf-opener');
                        if($root.length) {
                            var $target = $root.find('.sf-opener-target');
                            if($target.length) {
                                $root.removeClass('sf-open');
                            }
                        }
                    });
                }
        };

	// public properties

	// public methods
	$(document).ready(function() {
                $('#sfmaincontainer').off('adminformloaded');
		$('#sfmaincontainer').on('adminformloaded', function() {
			opener.init();
			filtering.init();
			tableCheckUncheckCb.init();
			toggleLayer.init();
			//toggleRow.init();
			spj.deleteRow.init();
			deleteRowReload.init();
			hideRow.init();
			loadForm.init();
			cancelForm.init();
			loadAjax.init();
			reloadForm.init();
			showElement.init();
			checkAll.init();
			uncheckAll.init();
			adminTool.init();
			adminHelp.init();
			openDialog.init();
			troubleshooting.init();
			accordionLoadForm.init();
			multiselectTransfer.init();
			multiselectUpdate.init();
			multiselectFilter.init();
			forumsExpandCollapseGroup.init();
			componentsSpecialRankAdd.init();
			componentsSpecialRankCancel.init();
			componentsSpecialRankShow.init();
			componentsSpecialRankForm.init();
			pluginsUpload.init();
			profileAvatarDefaults.init();
			profileAvatarUpdatePriorities.init();
			themesDeleteConfirm.init();
			themesUpload.init();
			ugShowMembers.init();
			setForumOptions.init();
			setForumSequence.init();
			setForumSlug.init();
			prepareEditors.init();
			prepareDatePicker.init();
		});

		$('#sfmaincontainer').off('troubleshootingformloaded');
		$('#sfmaincontainer').on('troubleshootingformloaded', function() {
			searchTool.init();
                        troubleshooting.init();
		});
		
		// Add theme or plugin file name when selecting new file to upload
		$('body').delegate( '#themezip, #pluginzip', 'change', function(e) {
				$(this).closest('form').find('.sf-upload-file-name label').html( e.target.files[0].name );
		});		
	});

	// private methods
}(window.spj = window.spj || {}, jQuery));
